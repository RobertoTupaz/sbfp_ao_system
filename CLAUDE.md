# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

School-Based Food Programme (SBFP) monitoring system for DepEd Philippines. Tracks pupil nutritional status, manages beneficiaries, and generates reports for school feeding programs.

## Tech Stack

- **Laravel 10** with **Livewire 3** (+ Volt 1.0) and **Breeze** auth scaffolding
- **Tailwind CSS 3** via Vite, **SweetAlert2** for notifications
- **Maatwebsite/Excel** for SF1 spreadsheet imports
- **MySQL** database (`sbfp_db_5` by default)
- PHP 8.1+

## Common Commands

```bash
# Start dev servers (run both in parallel)
php artisan serve
npm run dev

# Build assets for production
npm run build

# Run migrations and seed
php artisan migrate
php artisan db:seed

# Run all tests
php artisan test

# Run a single test file
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Run a specific test method
php artisan test --filter test_users_can_authenticate_using_the_login_screen

# Clear caches after config/route changes
php artisan optimize:clear
```

## Architecture

### Roles & Access

Users have a `role` column with four values: `user`, `ao` (Area Officer), `focal`, `admin`. Each role sees a different dashboard view served from the same `/dashboard` route.

### Livewire Components (`app/Livewire/`)

All interactive UI is built as Livewire components. Key groupings:

- **`Dashboard/Base.php`** — AO dashboard; handles Excel SF1 upload and triggers `excel-uploaded` event
- **`Dashboard/Focal.php`** — Focal person dashboard; lists schools managed by focal user
- **`Dashboard/Pupils/UploadSF1.php`** — Parses uploaded SF1 Excel file (skips first 13 header rows, converts Excel date serials to PHP dates, creates `Form_1` records)
- **`Dashboard/Pupils/Add.php`** / **`AddedLast.php`** — Manual pupil add and recent-additions view
- **`Dashboard/SetSchool/Setschoolid.php`** — School context selector used to scope data entry
- **`EditBeneficiaries/`** — Multi-step workflow for editing beneficiary lists
- **`GenerateForms/Buttons.php`** / **`GenerateReports/Generate.php`** — Form and report generation

Components follow the pattern: `mount()` loads initial data, properties are reactive, cross-component communication uses `$this->dispatch('event-name')` / `#[On('event-name')]`.

### API Endpoints (`routes/api.php`)

Two lookup APIs using simplified WHO reference tables:

- `GET /api/get-hfa` — Height-for-Age assessment (params: `age`, `height`, `gender`)
- `GET /api/get-bmi` — BMI-for-Age assessment (params: `age`, `bmi`, `gender`)

Both query `HfaSimplifiedVersion` / `BmiVersionSimplefied` models and return a nutritional classification string.

### Key Models

- **`Form_1`** — Central pupil record: school, name, sex, grade/section, DOB, weight, height, BMI status, parent consent, 4Ps flag, SBFP beneficiary flag
- **`NutritionalStatus`** — Comprehensive nutritional tracking records (seeded with 3000+ records)
- **`Schools`** / **`Districts`** / **`AllSchool`** — Geographic hierarchy for scoping data
- **`SchoolYear`** — Academic year tracking; used to scope all form/report data
- **`HfaSimplifiedVersion`** / **`BmiVersionSimplefied`** — WHO reference lookup tables
- **`SwappedPupils`** — Tracks pupil transfers/swaps between schools
- **`State`** / **`CurrentState`** — Application-level state management

### Routes (`routes/web.php`)

| Path | Component | Auth |
|------|-----------|------|
| `/` | `pages.auth.login` (Volt) | Guest |
| `/dashboard` | Dashboard (role-based view) | Required |
| `/edit_beneficiaries` | EditBeneficiaries workflow | Required |
| `/generate_forms` | GenerateForms | Required |
| `/track/enrollees` | TrackEnrollees | Required |
| `/generate/reports` | GenerateReports | Required |
| `/profile` | Profile | Required |

### Database

MySQL, configured in `.env` as `DB_DATABASE=sbfp_db_5`. The `database/seeders/` directory includes a nutritional status seeder. Test credentials (from `.env`): all `@deped.gov.ph` emails with password `123456789`.

For local email testing, Mailpit is configured (`MAIL_HOST=127.0.0.1`, port `1025`).
