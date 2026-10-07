<?php

namespace App\Models;

use App\Support\SurveyState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NutritionalStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nutritional_statuses';

    protected $fillable = [
        'survey_state',
        'pupil_key',
        'full_name',
        'last_name',
        'first_name',
        'suffix_name',
        'birthday',
        'sex',
        'weight',
        'height',
        'age_years',
        'age_months',
        'bmi',
        'nutritional_status',
        'height_for_age',
        'date_of_weighing',
        'grade',
        'section',
        '_4ps',
        'ip',
        'pardo',
        'dewormed',
        'parent_consent_milk',
        'sbfp_previous_beneficiary',
        'isBeneficiary',
        'deleted_by',
    ];

    protected $casts = [
        'birthday' => 'date',
        'date_of_weighing' => 'date',
        'weight' => 'float',
        'height' => 'float',
        'bmi' => 'float',
        'age_years' => 'integer',
        'age_months' => 'integer',
        '_4ps' => 'boolean',
        'ip' => 'boolean',
        'pardo' => 'boolean',
        'dewormed' => 'boolean',
        'parent_consent_milk' => 'boolean',
        'sbfp_previous_beneficiary' => 'boolean',
        'isBeneficiary' => 'boolean',
    ];

    /**
     * Baseline, midline and endline hold the same pupils: a pupil has one row per state and only the
     * measurements differ. These are the details shared by all of a pupil's rows; everything else
     * (weight, height, BMI, statuses, date of weighing, age) belongs to the state.
     */
    public const ROSTER_FIELDS = [
        'full_name', 'last_name', 'first_name', 'suffix_name', 'birthday', 'sex', 'grade', 'section',
        '_4ps', 'ip', 'pardo', 'dewormed', 'parent_consent_milk', 'sbfp_previous_beneficiary', 'isBeneficiary',
    ];

    /** True while a bulk operation runs and the roster is synced once at the end instead of per pupil. */
    private static bool $rosterSyncPaused = false;

    protected static function booted(): void
    {
        // Every query is limited to the survey state (baseline/midline/endline) chosen in the nav
        static::addGlobalScope('survey_state', function (Builder $builder) {
            $builder->where('nutritional_statuses.survey_state', SurveyState::current());
        });

        static::creating(function (NutritionalStatus $pupil) {
            if (! $pupil->survey_state) {
                $pupil->survey_state = SurveyState::current();
            }
            if (! $pupil->pupil_key) {
                $pupil->pupil_key = (string) Str::uuid();
            }
        });

        // A new pupil exists in every state, with no measurements yet in the other states.
        static::created(function (NutritionalStatus $pupil) {
            if (! static::$rosterSyncPaused) {
                static::createMissingStates($pupil);
            }
        });

        // Roster details edited in one state are mirrored to the pupil's rows in the other states.
        static::saved(function (NutritionalStatus $pupil) {
            $changes = Arr::only($pupil->getChanges(), self::ROSTER_FIELDS);

            if ($pupil->pupil_key && $changes && ! static::$rosterSyncPaused) {
                static::otherStates($pupil)->update($changes);
            }
        });

        // Deleting, restoring or permanently deleting a pupil does the same in every state.
        static::deleted(function (NutritionalStatus $pupil) {
            if ($pupil->pupil_key && ! static::$rosterSyncPaused) {
                if ($pupil->isForceDeleting()) {
                    static::otherStates($pupil)->withTrashed()->forceDelete();
                } else {
                    static::otherStates($pupil)->update(['deleted_by' => $pupil->deleted_by]);
                    static::otherStates($pupil)->delete();
                }
            }
        });

        static::restored(function (NutritionalStatus $pupil) {
            if ($pupil->pupil_key && ! static::$rosterSyncPaused) {
                static::otherStates($pupil)->onlyTrashed()->update(['deleted_by' => null]);
                static::otherStates($pupil)->onlyTrashed()->restore();
            }
        });

        static::deleting(function (NutritionalStatus $pupil) {
            if (! $pupil->isForceDeleting() && ! $pupil->deleted_by && auth()->check()) {
                $pupil->deleted_by = auth()->id();
                $pupil->saveQuietly();
            }
        });
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /** The same pupil's rows in the other states (the survey state filter is lifted, deleted rows stay hidden). */
    private static function otherStates(NutritionalStatus $pupil): Builder
    {
        return static::withoutGlobalScope('survey_state')
            ->where('pupil_key', $pupil->pupil_key)
            ->where('survey_state', '!=', $pupil->survey_state);
    }

    /** Gives a pupil a row (without measurements) in each state that does not have one yet. */
    private static function createMissingStates(NutritionalStatus $pupil): void
    {
        if (! $pupil->pupil_key) {
            return;
        }

        $present = DB::table($pupil->getTable())->where('pupil_key', $pupil->pupil_key)->pluck('survey_state')->all();
        $missing = array_diff(SurveyState::STATES, $present);

        if (! $missing) {
            return;
        }

        $details = Arr::only($pupil->getAttributes(), self::ROSTER_FIELDS);
        $now = now();

        DB::table($pupil->getTable())->insert(array_map(
            fn ($state) => $details + ['pupil_key' => $pupil->pupil_key, 'survey_state' => $state, 'created_at' => $now, 'updated_at' => $now],
            array_values($missing),
        ));
    }

    /**
     * Makes sure every pupil has a row in each state, copying the details from the pupil's existing row.
     * Pupils that were deleted in any state are left alone. Returns the number of rows added.
     */
    public static function syncRoster(): int
    {
        $table = (new static)->getTable();
        $columns = array_merge(['pupil_key', 'survey_state', 'deleted_at'], self::ROSTER_FIELDS);
        $now = now();
        $rows = [];

        DB::table($table)->whereNotNull('pupil_key')->orderBy('id')->get($columns)->groupBy('pupil_key')
            ->each(function ($group, $key) use (&$rows, $now) {
                if ($group->contains(fn ($row) => $row->deleted_at !== null)) {
                    return;
                }

                $details = Arr::only((array) $group->first(), self::ROSTER_FIELDS);

                foreach (array_diff(SurveyState::STATES, $group->pluck('survey_state')->all()) as $state) {
                    $rows[] = $details + ['pupil_key' => $key, 'survey_state' => $state, 'created_at' => $now, 'updated_at' => $now];
                }
            });

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table($table)->insert($chunk);
        }

        return count($rows);
    }

    /** Runs a bulk operation (seeding, importing) and syncs the roster once afterwards instead of per pupil. */
    public static function withoutRosterSync(callable $callback): mixed
    {
        static::$rosterSyncPaused = true;

        try {
            $result = $callback();
        } finally {
            static::$rosterSyncPaused = false;
        }

        static::syncRoster();

        return $result;
    }
}
