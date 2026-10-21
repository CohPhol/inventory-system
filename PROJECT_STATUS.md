# ShuttleStock Project Status

## Project Goal

Build ShuttleStock as a personal badminton shuttlecock inventory and financial management application. Planned areas include product catalog, auditable inventory movements, purchasing, sales and payments, borrowing and returns, badminton queue sessions and consumption, and financial reporting.

## Intended Stack and Version Evidence

| Component | Intended version | Current project evidence |
| --- | --- | --- |
| Laravel | 13 | `composer.lock` resolves `laravel/framework` to v13.37.0. |
| PHP | 8.4 | Sail build context and image in `docker-compose.yml` use the `8.4` runtime; `composer.json` and `composer.lock` require `^8.3`. |
| Livewire | 4 | `composer.lock` resolves `livewire/livewire` to v4.4.7. |
| Tailwind CSS | 4 | `package.json` declares `^4.0.0`; `package-lock.json` resolves Tailwind CSS and `@tailwindcss/vite` to 4.3.3. |
| MySQL | 8 | `docker-compose.yml` declares `mysql/mysql-server:8.0`. |
| Laravel Sail | 1 | `composer.lock` resolves `laravel/sail` to v1.69.0. |
| Laravel Boost | 2 | `composer.lock` resolves `laravel/boost` to v2.10.3; `boost.json` enables MCP and `.mcp.json` declares the Sail Boost MCP command. |

These versions describe project declarations and lockfile resolutions. Runtime health and successful application behavior require separate verification.

## Current Phase

Phase 1: Environment Setup.

## Completed Work and Verification Results

- Root project guidance now retains the existing Laravel Boost guidelines and includes ShuttleStock purpose, intended stack, planned features, and workflow rules.
- This status file records the current repository evidence and remaining setup checks.
- `composer.lock` and `package-lock.json` are present and contain the version resolutions listed above.
- `vendor/` and `node_modules/` directories are present. Their presence alone does not verify a healthy application, database connection, frontend build, or MCP startup.
- The repository currently contains Laravel's default welcome route and starter migrations for users, sessions, cache, and jobs. No ShuttleStock inventory or finance implementation is evidenced in the inspected application structure.
- The database directory contains a zero-byte `database/database.sqlite`; it does not provide evidence that migrations ran against MySQL.
- No application tests, database connectivity check, frontend build, or Boost MCP startup were run for this documentation update.
- The prior read-only environment check found Docker and Compose installed but could not verify daemon access because access to the Docker socket was denied. Recheck before relying on Docker being usable.
- A pre-existing untracked `package-lock.json` was present before this update and was preserved.

## Pending Tasks

- Verify Docker daemon access and the current Compose command on the host.
- Verify Sail can build and run the PHP 8.4 application and MySQL 8 service; do not assume services are running based on configuration alone.
- Verify that the frontend dependency tree is usable and that Vite can build the assets.
- Verify Laravel can connect to the configured MySQL database.
- Verify that Laravel Boost's MCP server starts through the configured Sail command.
- Confirm application behavior and run relevant tests when an application change is authorized.
- Begin implementing the planned ShuttleStock features only as individual, explicitly approved tasks.

## Known Blockers and Configuration Issues

- The last recorded Docker daemon check failed with a socket permission error; Docker service accessibility remains unverified.
- `.env` currently configures MySQL with database `laravel` and username `root`. These values do not match the intended `shuttlestock` database and dedicated application user.
- `docker-compose.yml` has fallback database and username values (`vizop` and `sail`) that differ from `.env` when the corresponding variables are unset. Current environment values and first-initialization behavior must be considered before changing database configuration.
- `.env.example` defaults to SQLite, so it does not currently describe the intended MySQL development setup.
- Environment files contain credentials; keep their values private and redact them from reports and command output.

## Important Architectural Decisions

- Inventory changes must be represented by auditable movements rather than unexplained edits to a stock total.
- Sales revenue, amounts collected, inventory valuation, and profit are separate concepts.
- Borrowed tubes and their returns are not sales.
- Follow existing Laravel, Livewire, and frontend conventions and applicable `.ai/rules` instructions before implementation.
- Keep dependency changes and destructive database or Docker-volume operations behind explicit authorization for the task.

## Unresolved Questions

- Is the Docker daemon now accessible to the development user?
- Are the existing MySQL credentials and database intended to be replaced with `shuttlestock` and a dedicated application user, and is there any existing database volume to preserve?
- Should opening inventory, purchase history, or another workflow define the initial inventory baseline?
- What business rules should govern borrowed tubes, queue sessions, payment allocation, and profit calculation?

## Next Recommended Action

Complete a read-only environment readiness check: confirm Docker access, inspect the effective (redacted) database configuration and installed dependency state, and verify whether the application, frontend build, database connection, and Boost MCP server are usable. Resolve the database configuration plan before any migration or database modification.

## Latest Read-Only Inspection — 2026-10-21

- Repository remains a Laravel starter skeleton. The current Git worktree includes the pre-existing modified `AGENTS.md` and untracked `PROJECT_STATUS.md` and `package-lock.json`; these were preserved.
- Through Sail with `APP_SERVICE=laravel`, Laravel 13.37.0 and the locked PHP dependencies are available. Direct dependency versions include Livewire 4.4.7, Boost 2.10.3, Sail 1.69.0, and PHPUnit 12.5.40.
- The configured MySQL connection is active and `artisan migrate:status` reports only the three default users, cache, and jobs migrations as ran. No ShuttleStock tables or models exist.
- Installed frontend packages match the lockfile (Vite 8.3.4, Tailwind 4.3.3, Laravel Vite plugin 3.2.0, and Tailwind Vite plugin 4.3.3). The production build fails because Vite cannot write its temporary config under `node_modules/.vite-temp`: the mounted directory is mode 755 and owned by `nobody`, while Sail runs npm as its `sail` user. The existing ignored `public/build` contains a zero-byte JavaScript asset and is not evidence of a successful current build.
- Compose names the application service `laravel`, while Sail defaults `APP_SERVICE` to `laravel.test`; commands currently require `APP_SERVICE=laravel` to target this Compose service.
- The effective connection uses MySQL, host `mysql`, database `laravel`, and username `root`. Compose emits a warning because `DB_ROOT_PASSWORD` is unset. No database configuration or data was changed.
- `boost.json` enables MCP and `.mcp.json` registers `vendor/bin/sail artisan boost:mcp`. Boost/MCP Artisan commands are registered. The MCP server itself was not launched in this inspection.
- No applicable `.ai/rules/index.md` or `.ai/rules` files are present. No feature code, schema, dependencies, or tests were changed.

### Next Pending Task

Review and correct the Sail service-name and mounted `node_modules` write-permission setup, then rerun the frontend production build. Keep database configuration and migrations unchanged until their plan is explicitly reviewed.

## Environment Troubleshooting — 2026-10-21

- Current Git status remains `AGENTS.md` modified and `PROJECT_STATUS.md` plus `package-lock.json` untracked. These changes were preserved.
- Compose currently has services `laravel`, `mysql`, and `phpmyadmin`. Sail defaults to `APP_SERVICE=laravel.test`; setting `APP_SERVICE=laravel` is required for this Compose file.
- The actual Sail npm identity is `sail` (UID/GID 1000). From that identity, `node_modules/.vite-temp`, `public/build`, and `public/build/assets` are not writable: they appear owned by root with mode 755 inside the application container. This confirms the Vite build failure is a mounted-directory ownership/permissions issue.
- MySQL is running and reports healthy. The Laravel container is running; Compose does not define a healthcheck for it. Sail Artisan commands work, including read-only database configuration and migration status. The three default migrations remain applied, unchanged.
- Compose's `DB_ROOT_PASSWORD` warning comes from the MySQL healthcheck's direct `${DB_ROOT_PASSWORD}` interpolation without a fallback. The MySQL root-password environment setting has a fallback expression, so the warning is emitted separately by the healthcheck interpolation. No secret values were printed.
- A narrowly scoped ownership change for the three blocked directories would require running `chown` as root in the application container. Per the task restriction, that privileged change was not executed; the production build therefore remains unverified and blocked.
- Database configuration, credentials, containers, dependencies, and data were not changed. A dedicated ShuttleStock database/user should be added alongside the existing database after preserving the current volume, then the app connection can be switched by an approved `.env` update. Compose's initialization variables do not retrofit users/databases into an already initialized data volume, so provisioning must be an explicit additive database operation rather than relying on container restart.

### Next Pending Task

After approval for the required privileged permission change, run `APP_SERVICE=laravel ./vendor/bin/sail exec -u root -T laravel chown sail:sail node_modules/.vite-temp public/build public/build/assets`, then rerun `APP_SERVICE=laravel ./vendor/bin/sail npm run build` and verify `public/build/manifest.json`. Separately review and approve the additive database/user provisioning and environment switch before changing database configuration.

## Frontend Permission Fix and Verification — 2026-10-21

- Ran the explicitly authorized, non-recursive ownership command for only `node_modules/.vite-temp`, `public/build`, and `public/build/assets`. It exited successfully; all three directories now belong to `sail:sail` (UID/GID 1000) with mode 755.
- `APP_SERVICE=laravel ./vendor/bin/sail npm run build` exited 0 using the existing installed dependencies. Vite generated the production manifest and assets. Vite reported that optional `fontaine` is absent for optimized font fallbacks; this was a warning and did not prevent the build.
- `public/build/manifest.json` parses as valid JSON and references nine assets; all nine files exist. Eight have nonzero size. The referenced JavaScript bundle is 0 bytes because the existing `resources/js/app.js` entry contains only a comment. No application code was changed to alter that output.
- `APP_SERVICE=laravel ./vendor/bin/sail artisan --version` succeeds and reports Laravel 13.37.0. A fresh read-only `migrate:status` check still reports only the same three default migrations as ran.
- Git status remains the pre-existing modified `AGENTS.md` and untracked `PROJECT_STATUS.md` and `package-lock.json`. `public/build` remains ignored. No database configuration, credentials, data, migrations, dependencies, or application source were changed.

### Remaining Environment Work

- The frontend production build now succeeds. The JavaScript asset is empty until the existing JavaScript entry has application code; this does not prevent Vite from building.
- Decide and approve the additive dedicated ShuttleStock database/user provisioning and Laravel environment switch before changing database configuration. The current database and migrations remain untouched.

## Database Provisioning Plan Review — 2026-10-21

- The MySQL service uses the persistent named volume `inventory-system_sail-mysql` mounted at `/var/lib/mysql`. No project initialization scripts were found; the optional Sail testing-database script mount is commented out.
- Current Laravel configuration selects MySQL at `mysql`, database `laravel`, username `root`. `DB_URL` is not set in `.env`; Laravel uses the individual `DB_*` settings. Session, cache, and queue drivers currently use the database, and the default Laravel migrations are present in the existing database.
- `DB_ROOT_PASSWORD` is currently present and nonempty in `.env` (value not read or printed), and current `sail ps` emitted no warning. The earlier warning is explained by the healthcheck's direct `${DB_ROOT_PASSWORD}` interpolation, which has no fallback; the `MYSQL_ROOT_PASSWORD` setting uses a separate fallback expression. No root authentication changes are recommended.
- The volume is already initialized, so MySQL image initialization variables and init scripts are not a safe way to add the new database/user. Plan an additive SQL provisioning operation against the running instance after verifying the target database/account do not already exist and securely backing up the existing `laravel` database.
- No database, user, credential, Compose, Laravel configuration, migration, or volume changes were made during this review.

### Next Pending Task

Review and approve the additive provisioning plan and account privilege split, then separately authorize the protected backup, database/user creation, and `.env` connection switch. Preserve `laravel` and `sail-mysql`; do not use MySQL initialization variables to retrofit an initialized volume.

## Dedicated Database Planning Review — 2026-10-21

- The current Compose services are `laravel`, `mysql`, and `phpmyadmin`; the MySQL container's actual named volume is `inventory-system_sail-mysql`, mounted at `/var/lib/mysql`. No project-provided MySQL initialization scripts are present. The commented Sail testing database script is not mounted.
- MySQL receives `MYSQL_ROOT_PASSWORD` from `DB_ROOT_PASSWORD` with a Compose fallback, and its first-initialization settings for database/user come from `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. These are initialization inputs, not a mechanism to alter an already initialized volume. The Oracle MySQL image documentation states initialization environment variables leave an existing data directory unchanged.
- Laravel currently selects the MySQL connection at host `mysql`, database `laravel`, username `root`; `DB_URL` is absent. Session, cache, and queue drivers are all database-backed, and the existing three default migrations are applied in `laravel`.
- The Compose warning's source is the MySQL healthcheck's direct `${DB_ROOT_PASSWORD}` interpolation, which has no fallback. The root-password environment setting uses a separate fallback expression. In the current `.env`, `DB_ROOT_PASSWORD` is present and nonempty (value not read or printed), and the latest `sail ps` did not emit the warning. No authentication change is warranted. If removing the warning is desired, the healthcheck can use `mysqladmin ping` without a password; MySQL documents that `ping` succeeds whenever the server is running, even when access is denied.
- Recommended future provisioning: preserve and back up the existing volume/database; check that the target schema and account names are unused; create `shuttlestock` and schema-scoped accounts explicitly against the running server; keep runtime DML separate from migration DDL where practical; then update only Laravel's `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` values in the ignored `.env` (with `DB_CONNECTION=mysql`, `DB_HOST=mysql`, and `DB_PORT=3306` retained). No schema/user, credential, Compose, Laravel config, migration, or volume change was made in this planning review.

### Next Pending Task

Wait for approval of the account split and the additive provisioning/configuration sequence. Do not create the database/user or switch `.env` before that approval; retain the existing `laravel` database and named volume.

## Revised Database and Network Plan — 2026-10-21

- The user revised the database decision: keep the existing `laravel` database and persistent volume; add ShuttleStock tables later through separately approved Laravel migrations. Preserve all existing tables and records.
- Use one dedicated application MySQL account for normal Laravel operation and separately approved migrations. Do not create a separate migration account. Its privileges should be scoped to `laravel.*`; DML privileges plus the schema DDL privileges required by migrations are a deliberate compromise because this account will also be used at runtime.
- The current Compose MySQL mapping is `'${FORWARD_DB_PORT:-3306}:3306'`, which publishes to wildcard host interfaces. The proposed minimal edit is `'127.0.0.1:${FORWARD_DB_PORT:-3306}:3306'`; leave Laravel's internal `DB_HOST=mysql` and `DB_PORT=3306` unchanged. The live Compose status showed MySQL published on `0.0.0.0:3306` and `[::]:3306`; Laravel and phpMyAdmin communicate over the existing `sail` network. No other service in this Compose project publishes host port 3306.
- Read-only `APP_SERVICE=laravel ./vendor/bin/sail artisan migrate:status` succeeded and reports only the three default users, cache, and jobs migrations as applied. This confirms the configured database connection worked at check time. No migration or database write was run.
- A host listener check was blocked by sandbox netlink permissions, and listing all Docker containers was blocked by Docker socket permissions; unrelated host services/other Compose projects could not be independently enumerated. The existing published MySQL binding is the only port-3306 publisher visible through this project's `sail ps`.
- No account, credential, Compose, Laravel environment, database, migration, or volume change was made. Existing Git changes (`AGENTS.md` modified; `PROJECT_STATUS.md` and `package-lock.json` untracked) were preserved.

### Next Pending Task

After separate approval, securely back up the current `laravel` database and protected configuration, provision one dedicated account with database-scoped application and migration privileges after confirming an appropriate Docker source-host pattern, update only Laravel's database username/password while keeping database `laravel` and internal host/port `mysql:3306`, and bind the published MySQL port to `127.0.0.1`. Verify the account grants, Laravel connection, migration status, host binding, and unchanged volume/data. Do not run ShuttleStock migrations until they are separately approved.

## Database Preparation — Partial Completion — 2026-10-21

- Preflight found 951 GB available on the filesystem used by `/tmp` and the project. MySQL was healthy; the `laravel` database had nine tables and approximately 246 KB of data/index allocation. The existing three default Laravel migrations were applied.
- Created protected backups outside the repository in `/tmp/shuttlestock-dbprep-20261021-01`: `.env.backup`, `docker-compose.yml.backup`, and `laravel.sql.gz`. The directory is mode 700 and backup files are mode 600. The compressed dump passed `gzip -t` and contains a database declaration, nine table definitions, and three insert sections. The temporary generated-password file was removed after updating `.env`.
- Inspected the Compose network: `inventory-system_sail` uses `172.20.0.0/16`, and the Laravel container was `172.20.0.4`. Created `shuttlestock_app` for host pattern `172.20.0.0/16`; `SHOW GRANTS` confirmed only the default global `USAGE` and `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES` on `laravel`.*. The account password is not recorded here.
- Changed only the MySQL published port mapping to `'127.0.0.1:${FORWARD_DB_PORT:-3306}:3306'`. Updated only `DB_USERNAME` and `DB_PASSWORD` in `.env`; confirmed all other `.env` bytes match the protected pre-change copy after normalizing those two keys. `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, and `DB_DATABASE` were preserved. Laravel configuration cache was absent, so no cache-clear command was needed.
- Recreated only the MySQL service. It returned healthy, publishes only `127.0.0.1:3306`, and still mounts `inventory-system_sail-mysql` at `/var/lib/mysql`. Laravel's active connection reported `shuttlestock_app@172.20.0.0/16` on schema `laravel`; `migrate:status` still succeeds with only the same three default migrations applied. No ShuttleStock migrations ran.
- Laravel reports database-backed session, cache, and queue drivers. A transactional cache put/get probe passed. The session probe did not produce the expected row after `session->save()`, so the transaction was rolled back and verification stopped as instructed. The queue probe did not run. No existing rows or jobs were intentionally modified; a post-change row-by-row comparison against the backup was not completed.
- The `.env` file remains mode 644, unchanged from before this task. This leaves the new credential readable to local users with filesystem access; the protected private backup does not change that. No credentials are recorded in this status file.
- Git changes preserved: pre-existing modified `AGENTS.md` and untracked `PROJECT_STATUS.md` and `package-lock.json`; `docker-compose.yml` is now modified for the approved binding.

### Next Pending Task

Investigate the database session handler/schema mismatch using read-only inspection, then obtain approval before retrying any session or queue write probes. Do not proceed with feature migrations until database-backed sessions, cache, and queue verification is complete.

## Session Probe Investigation — 2026-10-21

- Read-only effective Laravel configuration confirms MySQL database `laravel`; session driver `database`, session connection unset (therefore the default DB connection), and session table `sessions`; queue driver `database`, queue connection unset (default DB connection), table `jobs`, batches table `job_batches`, failed table `failed_jobs`; cache driver `database`, default DB connection, table `cache`.
- Live schema checks confirm `sessions`, `jobs`, `job_batches`, `failed_jobs`, and `cache` exist. The `sessions` columns are `id`, `user_id`, `ip_address`, `user_agent`, `payload`, and `last_activity`, matching the existing Laravel migration. Configuration cache is absent.
- The prior session probe's failure is explained by its test ID: `bin2hex(random_bytes(16))` yields 32 alphanumeric characters, while the installed Laravel `Store` requires exactly 40 characters. `Store::setId()` silently generated a different 40-character ID; `save()` used that generated ID, while the probe queried by the original 32-character value. The ID mismatch, not evidence of an application/session-handler failure, explains the missing row. The probe bootstrapped the app in Tinker and did call `start()` and `save()`, but checked the wrong ID. Its outer transaction was rolled back after the assertion failed.
- The installed `DatabaseSessionHandler` uses the configured table and connection, which match the live schema/default connection. The installed `DatabaseConnector` gives `DatabaseQueue` the configured/default Laravel DB connection. A safe future queue probe can use a unique queue name and an uncommitted transaction on that same connection, inspect only its own inserted job, and roll back without running a worker.
- `.env` is owned by UID/GID 1000 and mode 644. The PHP process in the Laravel container runs as UID/GID 1000 (`sail`), so mode 600 would remain readable by the application owner. No permission change was made.
- A read-only current dump comparison was attempted, but `mysqldump` failed with MySQL error 1045 (`root`@`localhost`, password rejected). The resulting empty-stream hash comparison is not a valid data comparison; no database mismatch can be inferred. A safe comparison method is a `--single-transaction` logical dump authenticated as the application account using a protected temporary client option file, with dump metadata normalized before comparing to the existing backup; do not restore the backup.
- This investigation made no application, configuration, permission, service, volume, or database-record changes. The queue probe remains unrun. Existing Git changes remain preserved.

### Next Pending Task

Complete the authorized final verification phase; do not begin ShuttleStock migrations or feature work until separately approved.

## Final Environment Verification — 2026-10-21

- Changed only `.env` mode from 644 to 600. Ownership remains UID/GID 1000:1000, matching the PHP runtime user `sail`. A Tinker process running as `sail` confirmed it could read the environment, connect to MySQL as `shuttlestock_app@172.20.0.0/16`, and use the existing `laravel` database.
- MySQL reports healthy and its published port is `127.0.0.1:3306`. The existing Laravel, MySQL, and phpMyAdmin services remain running. No volume was removed or replaced.
- `php artisan migrate:status` succeeded as `sail`; the three original Laravel migrations remain applied. No ShuttleStock migrations ran.
- A uniquely named database cache put/get probe passed, and cleanup removed only its exact probe key.
- The unique queue probe passed. It confirmed the Laravel database queue and explicit transaction shared the same connection, observed the uniquely tagged inserted row inside the transaction, rolled back, and confirmed that exact row was absent. No worker ran and no existing queue rows were targeted.
- The session probe used a valid 40-character alphanumeric ID, started and saved through Laravel's database session handler, and attempted to verify the row by the store's actual ID. Its assertion failed before the store read-back because it searched the raw `payload` column for the plaintext marker. The installed handler base64-encodes session data before storage and decodes it on read, so this raw substring assertion is invalid. The probe's `finally` block deleted only the generated session ID. Since read-back was not reached and the assertion combined row existence, ID equality, and plaintext search, the session round trip remains unverified; this result does not establish an application failure.
- The current logical dump comparison did not run to completion. The protected temporary MySQL option file was used without exposing credentials, but `mysqldump` failed with MySQL error 1227 because it attempted `FLUSH TABLES`, which `shuttlestock_app` is not granted. The temporary option file and current dump were removed. No extra privileges were added. The prior backup is gzip-valid and contains nine table definitions, but current database equality with that backup is not established.
- No application tables, existing records, or jobs were intentionally modified. The session probe's exact row was cleaned up, the queue probe transaction rolled back, and the cache probe's exact key was removed. No restore, migration, worker, or destructive command ran.
- Git changes were preserved: modified `AGENTS.md` and `docker-compose.yml`, plus untracked `PROJECT_STATUS.md` and `package-lock.json`. The Compose localhost binding remains the approved change. No credentials or backup contents are recorded here.

### Remaining Verification

Use a corrected session probe that lets Laravel's handler decode its base64 payload and verifies the marker through the store, then clean only its unique ID. To compare with the backup, use a read-only logical export method that works with the account's existing grants, or separately review whether a temporary narrowly scoped export privilege is acceptable. Do not change grants, restore data, or run ShuttleStock migrations without approval.

## Follow-up Session and Backup Verification — 2026-10-21

- The corrected Laravel session probe passed. It generated a 40-character alphanumeric ID, started and saved a session with a unique marker, confirmed the exact row existed, then read the marker through a fresh Laravel session store (which decoded the stored payload). Cleanup deleted and verified absence of only that exact probe ID.
- The earlier dump command used a protected client option file and `mysqldump` with `--single-transaction --skip-lock-tables --no-tablespaces --databases laravel`. Retrying with documented `--set-gtid-purged=OFF` still failed with MySQL error 1227 (`FLUSH TABLES` requires `RELOAD` or `FLUSH_TABLES`). The installed client is MySQL 8.0.32; read-only checks show `gtid_mode=OFF` and all nine database tables use InnoDB. MySQL's 8.0 documentation describes this privilege requirement for the `--single-transaction`/GTID-on/GTID-purged-on-or-auto combination, which does not match the observed settings. The reason the client still issued `FLUSH TABLES` remains unexplained; no further dump workaround or privilege change was attempted.
- The protected backup itself passed a fresh gzip CRC and structural check (database declaration and nine table definitions). The current dump did not complete, so its stream was not validated and no comparison was performed. No equality or mismatch with the current database can be inferred.
- No migrations, grant changes, restores, database configuration changes, or changes to existing records were made. The temporary dump and client option files were removed by their cleanup traps. Existing Git changes remain preserved.

### Remaining Verification

Session round-trip verification is complete. Current database equality with the protected backup remains unverified; use a fixed client through an approved execution path or assess a different read-only comparison method that needs no additional privileges. Do not change grants, restore data, or run ShuttleStock migrations as part of that investigation.

## mysqldump Privilege Error Investigation — 2026-10-21

- The MySQL container uses `/usr/bin/mysqldump`, MySQL Community Server 8.0.32. The effective command used a protected `[client]` defaults file containing only connection credentials, `--host=mysql --protocol=tcp --port=3306`, `--single-transaction --skip-lock-tables --no-tablespaces --databases laravel`; a retry added `--set-gtid-purged=OFF`. An isolated retry used `--defaults-file` instead of `--defaults-extra-file` and still failed with the same privilege error.
- Inspected mysqldump's option-file search paths, filtered its effective defaults to dump/flush/locking/GTID options, checked relevant MySQL environment-variable presence, and inspected available option files without displaying their contents. No relevant default behavior options or environment overrides were found. `MYSQL_UNIX_PORT` is set, but the dump explicitly uses TCP. The client therefore has no hidden `--source-data`, `--lock-all-tables`, or flush option explaining the request.
- The exact trigger is the MySQL 8.0.32 `mysqldump --single-transaction` regression: that client issues `FLUSH TABLES WITH READ LOCK` even when GTID is off and `--set-gtid-purged=OFF`. `--skip-lock-tables` disables the separate `LOCK TABLES` behavior and does not avoid this client regression. MySQL fixed the regression in 8.0.33; this is client-version behavior, not a Laravel or MySQL account configuration issue. See the [MySQL 8.0.33 release notes](https://dev.mysql.com/doc/relnotes/mysql/8.0/en/news-8-0-33.html) and [MySQL Bug #109701](https://bugs.mysql.com/bug.php?id=109701).
- Read-only checks still show server version 8.0.32, `gtid_mode=OFF`, and nine InnoDB base tables, with no views, triggers, routines, or events. The existing app account has table-level SELECT but no `RELOAD`, `FLUSH_TABLES`, or `LOCK TABLES`. A fixed mysqldump client (8.0.33 or later) using `--single-transaction --set-gtid-purged=OFF --no-tablespaces` can use an InnoDB consistent snapshot without those global privileges; MySQL documents SELECT as the required privilege for these tables when the other object types are absent.
- Existing tooling includes MySQL Shell and mysqlpump 8.0.32 inside the MySQL service; the phpMyAdmin service has no dump client. MySQL Shell's consistent dump utility requires RELOAD or LOCK TABLES, which this account does not have. The host has mysqldump/mysqlpump 8.0.46, but it was not used because project instructions require commands to run through Sail; no 8.0.33+ dump client is available inside the existing Sail services.
- The protected backup passes gzip CRC and structural validation. All attempted current dumps failed before validation, so no schema/row comparison was made and equality with the backup remains unestablished. No extra privileges, credentials, configuration, or database contents were changed.

### Remaining Verification

Session verification is complete. To finish the database comparison, use an approved Sail-accessible mysqldump client version 8.0.33 or later with a protected option file, or explicitly approve a read-only comparison using the already-installed host client and confirm its connection matches the account's allowed host pattern. Validate both streams before normalization and comparison; do not infer equality from prior failed dumps.

## Phase 1 Database Foundation — 2026-10-21

- Added three additive migrations for catalog/contact records, purchases and purchase-item receipt layers, and inventory events, used-tube identities, movements, and cost allocations. Existing Laravel users, sessions, cache, jobs, and other starter tables were not edited.
- Added Eloquent models and typed relationships for `Brand`, `Product`, `Supplier`, `Buyer`, `Purchase`, `PurchaseItem`, `InventoryEvent`, `UsedTube`, `InventoryMovement`, and `InventoryCostAllocation`.
- Products have nullable brand/SKU, unique brand names and non-null SKUs, enumerated speed values 75–79, positive tube counts, and active status. No universal price or stock balance is stored on products. MySQL check constraints and equivalent SQLite test triggers enforce positive tube counts and quantity/cost consistency.
- Purchases distinguish normal purchases from opening inventory. Each purchase item is a separate FIFO receipt layer, so a purchase can retain multiple same-product lines at different costs. Known costs use decimal PHP amounts and generated line totals; unresolved historical costs remain null and explicit rather than becoming zero. Cost allocations also carry a known/unresolved state.
- Inventory movement quantities distinguish whole new tubes from individual used shuttlecocks. Movements reference inventory events and optional individual used tubes; allocations link outgoing movements to purchase-item layers. No independently editable product balance, queue workflow, or posting service was added.
- Draft and posted history uses restrictive foreign keys; optional user actor references are nullable on user deletion. Product references and transaction records are preserved for later deactivation/adjustment workflows.
- The focused database foundation tests passed after the final schema changes (11 tests); the complete PHPUnit suite passed (13 tests, 32 assertions), using the PHPUnit configuration's isolated in-memory SQLite database. Laravel Pint passed on changed PHP files.
- No development database migration, seeder, credential change, environment change, or infrastructure change was run. Test writes were confined to the disposable in-memory SQLite database; no fake business data was added to the development database.
- Remaining implementation work includes purchase posting and FIFO allocation transactions, queue sessions/consumptions, a queue-session link for used tubes, used-tube remaining-count and carrying-value derivation, sales/payments/returns, and reports. The current phase provides schema relationships only; posting validation must enforce cross-table rules such as normal purchases requiring known cost, allocations matching the source layer's resolution status, and allocations not exceeding available receipt-layer quantity. Those checks are deferred to posting transactions rather than MySQL triggers, so no extra `TRIGGER` privilege is required.

### Next Pending Task

Review the Phase 1 database foundation and approve the next feature slice. Do not run the new migrations against the existing development database until separately approved.
