# Frame Codex

A fan-made Warframe catalog built with PHP, HTML, CSS, Bootstrap 5.3.8, JavaScript, and MariaDB. It is a portfolio practice project and is not affiliated with Digital Extremes.

## What it does

- Lists base Warframes, Primes, and Protoframes on separate tabs
- Searches by name and filters by role with a normal form
- Expands a card to show rank-30 stats, the passive, and four abilities
- Lets a visitor register, log in, and save favorites
- Lets a logged-in user build a squad of four Warframes, save it, and read how their roles and ability mechanics fit together

Ability descriptions are short original summaries. Stats are the usual rank-30 values. Each card shows a local portrait in `images/{slug}.png`, mirrored from the usual in-game render. Replacing that file with your own `.jpg`, `.png`, or `.webp` of the same name changes the portrait.

## Run it locally

PHP 8.1+ with PDO MySQL, and MySQL or MariaDB.

1. Create the database and load the catalog:

   ```bash
   mysql -u root -p < sql/schema.sql
   mysql -u root -p frame_codex < sql/seed.sql
   mysql -u root -p frame_codex < sql/mechanics.sql
   ```

   `sql/schema.sql` already creates the squad tables. `sql/squads.sql` is only for an older database that does not have them yet. `sql/mechanics.sql` fills the synergy data. If its `ADD COLUMN` line reports that `mechanics` already exists, run the `UPDATE` statements in that file.

2. Copy the database config and edit it if your credentials differ:

   ```bash
   cp config/db.local.example.php config/db.local.php
   ```

   The example matches a local MAMP install (`root` / `root`, database `frame_codex`, port `3306`). This file is git-ignored.

3. Put the folder in your web server's document root and open it. On MAMP that is often `http://localhost/frame-codex/` or `http://localhost:8888/frame-codex/`.

Importing `sql/schema.sql` again deletes the tables, including accounts. Import `sql/seed.sql` again to refill the catalog. That second import does not delete user accounts, but it does clear favorites because the Warframe rows are replaced.

## Files to know

- `index.php` is the catalog.
- `includes/card.php` is the card markup used by the catalog and the favorites page.
- `includes/frames.php` is the SQL that loads the cards.
- `register.php`, `login.php`, and `logout.php` handle accounts.
- `favorite-toggle.php` adds or removes one favorite, then sends the browser back to the same list.
- `squad.php` is the squad builder. `includes/squads.php` saves the team, writes the role notes, and runs `evaluateSynergies`.
- `sql/mechanics.sql` adds each Warframe's `mechanics` data. Import it once if the column is missing.
- `js/catalog.js` only closes the other open cards. Bootstrap opens the one you clicked.
- `js/squad.js` updates the role coverage as you change the four picks. The written notes come from PHP.
- `sql/schema.sql` creates the tables.
- `sql/seed.sql` fills in the roster.

Passwords are stored with `password_hash()` and checked with `password_verify()`.
