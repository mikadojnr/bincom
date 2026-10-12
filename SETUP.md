# Bincom Election Results (Laravel 12)

A small Laravel 12 app that answers the three Bincom test questions using the
`bincom_test.sql` dump (Delta State, 2011 elections).

## Pages

| Page | Route | What it does |
| --- | --- | --- |
| Polling Unit Result | `/polling-unit` | **Q1** – chained LGA → Ward → Polling Unit selectors, then shows the result announced at that polling unit. |
| LGA Summed Result | `/lga-summary` | **Q2** – sums every polling unit result under a selected LGA. It never reads `announced_lga_results`. |
| New Polling Unit | `/new-polling-unit` | **Q3** – creates a new polling unit and stores a score for every party in one form. |

## Setup

1. Requirements: PHP 8.2+, Composer, MySQL / MariaDB.
2. Create the database and import the dump (the schema already exists, so **no migrations are needed**):
   ```bash
   mysql -u root -p -e "CREATE DATABASE bincom_test CHARACTER SET utf8mb4;"
   mysql -u root -p bincom_test < database/bincom_test.sql
   ```
3. Copy the environment file and set your database credentials:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env` as needed.
4. Run the app:
   ```bash
   php artisan serve
   ```
   Open http://127.0.0.1:8000.

> This working copy is configured in `.env` for a local MariaDB on port **3307**
> (`DB_DATABASE=bincom_test`, user `root`, empty password). Change `DB_PORT` to
> `3306` for a standard XAMPP/MySQL install.

## How the tables link together

- `polling_unit.lga_id` → `lga.lga_id`
- `polling_unit.uniquewardid` → `ward.uniqueid`
- `announced_pu_results.polling_unit_uniqueid` → `polling_unit.uniqueid`

Each polling unit result is stored as one row per party, so scores are obtained
with `SUM(...) GROUP BY party_abbreviation`.

## Main files

- `routes/web.php` – routes (pages + chained combo box JSON endpoints)
- `app/Http/Controllers/ResultController.php` – all queries and the create/store logic
- `app/Models/` – thin Eloquent models (`Lga`, `Ward`, `PollingUnit`, `AnnouncedPuResult`, `Party`)
- `resources/views/` – layout and the three pages
