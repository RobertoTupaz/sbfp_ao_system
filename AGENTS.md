# AGENTS.md

This file provides guidance to Codex and other coding agents when working in this repository.

## Project Overview

This is a School-Based Feeding Program (SBFP) monitoring system for the Department of Education (DepEd) Philippines. It manages pupil enrollment and nutritional records, selects feeding-program beneficiaries, tracks beneficiary changes, and generates official SBFP forms and reports.

## Technology Stack

- PHP 8.1+
- Laravel 10.49
- Livewire 3 with Volt
- Laravel Breeze authentication
- Tailwind CSS 3 and Vite 5
- SweetAlert2 and Livewire Alert
- Maatwebsite/Laravel-Excel for Excel import and export
- MySQL
- PHPUnit 10

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Start local development
php artisan serve
npm run dev

# Build production assets
npm run build

# Database setup
php artisan migrate
php artisan db:seed

# Run tests
php artisan test

# Run one test file or test method
php artisan test tests/Feature/Auth/AuthenticationTest.php
php artisan test --filter test_users_can_authenticate_using_the_login_screen

# Format PHP code
vendor/bin/pint

# Clear Laravel caches
php artisan optimize:clear
```

On Windows PowerShell, use `npm.cmd` instead of `npm` if script execution policy blocks `npm.ps1`.

## Main Application Workflow

1. A user signs in and links the account to a school.
2. Pupil data is imported from a DepEd SF1 Excel file or entered manually.
3. Height, weight, BMI-for-age, and height-for-age data are recorded or calculated.
4. The system classifies pupils by nutritional status.
5. Primary and secondary beneficiary rules are configured.
6. The requested number of SBFP beneficiaries is selected automatically.
7. Users can review, edit, or swap beneficiaries with an audit reason.
8. The system produces analytics, DepEd SBFP forms, and monitoring reports.

## Roles and Access

The `users.role` field is used for role-based navigation and dashboards:

- `user` — school-level user
- `ao` — Area Officer
- `focal` — focal person who manages or reviews schools
- `admin` — administrator

Check both route middleware and Blade navigation conditions before changing role access. Much of the current role restriction is implemented in views rather than dedicated authorization policies.

## Important Routes

| Route | Purpose |
| --- | --- |
| `/` | Login page |
| `/dashboard` | Role-based dashboard and pupil import |
| `/track/enrollees` | Browse and update pupils by grade and section |
| `/edit_beneficiaries` | Configure, select, review, and swap beneficiaries |
| `/generate_forms` | Generate official SBFP Excel forms |
| `/generate/reports` | Generate baseline, midline, and summary reports |
| `/analytics` | Pupil and beneficiary statistics |
| `/school-profile` | School head, focal person, and contact details |
| `/profile` | User account settings |

The nutritional lookup endpoints are in `routes/api.php`:

- `/api/get-hfa` — height-for-age classification
- `/api/get-bmi` — BMI-for-age classification

## Code Organization

### Livewire

Interactive behavior lives under `app/Livewire/`, with matching Blade views under `resources/views/livewire/`.

Important component groups:

- `Dashboard/` — dashboards, school selection, SF1 import, and manual pupil entry
- `TrackEnrollees/` — pupil browsing, measurement updates, and recalculation
- `EditBeneficiaries/` — beneficiary criteria, automatic selection, editing, and swaps
- `GenerateForms/` — school-level SBFP form exports
- `GenerateReports/` — consolidated and monitoring report exports
- `Analytics/` — pupil and beneficiary summaries
- `SchoolProfile/` — school profile maintenance

Follow the existing Livewire pattern:

- Load initial data in `mount()`.
- Keep public component properties serializable.
- Use `$this->dispatch()` and `#[On(...)]` for cross-component events.
- Put validation close to the action that writes data.
- Keep component class and Blade view names aligned.

### Key Models

- `NutritionalStatus` — primary pupil and nutritional-status record
- `Form_1` — school-year and survey-state pupil records used by reports
- `Beneficiaries` — configured beneficiary count
- `PrimarySecondaryBeneficiaries` — beneficiary-selection criteria
- `SwappedPupils` — beneficiary replacement audit records
- `School`, `AllSchool`, `District`, and `SchoolsDistrict` — school hierarchy
- `SchoolProfile` — school head, focal person, and email
- `SchoolYear` — reporting school year
- `State` and `CurrentState` — survey/application state
- `HfaSimplifiedVersion` and `BmiVersionSimplefied` — nutritional reference tables

Some historical names contain inconsistent spelling, capitalization, or singular/plural forms. Do not rename models, tables, columns, routes, or views without checking all references and providing a migration when required.

## Data and Domain Conventions

- Grades are stored as `k`, strings `1` through `12`, or `non_graded`.
- Sex values may exist in multiple forms, including `m`, `f`, `male`, and `female`.
- Nutritional status values include `severely wasted`, `wasted`, `normal`, `overweight`, and `obese`.
- Height-for-age values include `severely stunted`, `stunted`, `normal`, and `tall`.
- Boolean demographic fields include `_4ps`, `ip`, `pardo`, `dewormed`, `parent_consent_milk`, and `sbfp_previous_beneficiary`.
- `isBeneficiary` marks the currently selected SBFP beneficiaries.
- Height is generally stored in centimeters. BMI calculations must convert height to meters first.
- Excel templates depend on fixed row and column positions. Treat template-coordinate changes as high-risk and verify generated files.

## Editing Guidelines

- Preserve user changes already present in the working tree.
- Make focused changes and avoid unrelated cleanup.
- Prefer Eloquent and existing model conventions over raw SQL, except where report aggregation already requires SQL expressions.
- Wrap multi-record beneficiary changes and swaps in database transactions.
- Avoid loading unbounded pupil collections into Livewire when pagination or targeted queries are practical.
- Do not expose credentials, pupil personal data, or `.env` values in logs, documentation, tests, or responses.
- Do not add public maintenance, migration, or seeder routes. Use Artisan commands for operational tasks.
- Validate uploaded file type, size, expected columns, and row structure before persisting imported data.
- Preserve swap history and reporting compatibility when modifying beneficiary logic.

## Verification

Choose verification proportional to the change:

- PHP or Livewire logic: run the relevant tests, then `php artisan test` when practical.
- Routes or configuration: run `php artisan route:list` and `php artisan optimize:clear`.
- Blade, Tailwind, or JavaScript: run `npm run build`.
- Formatting: run `vendor/bin/pint --test` or format touched PHP files.
- Database changes: inspect generated SQL or migrate in a safe local database.
- Excel import/export changes: test with representative SF1/template files and inspect the generated workbook.

When no automated test covers changed business logic, add a focused test where practical and clearly report any manual verification still required.

## Known Areas Requiring Care

- Beneficiary selection has priority rules and a configured maximum count; ordering changes can alter who is selected.
- Pupil swaps update two beneficiary records and create an audit entry.
- BMI and height-for-age calculations depend on age, sex, measurement units, and reference-table coverage.
- Reports contain repeated spreadsheet-coordinate logic and may silently produce incorrect cells even when generation succeeds.
- Role visibility is partly enforced in Blade templates, so hiding a navigation item does not by itself secure a route.
- The `/run-nutritional-seeder` web route performs an operational action and should not be exposed in production.

