# Technical Support Management System (Help Desk) — Laravel 12 / PHP 8.2 / MySQL

## التشغيل
```
composer install
php artisan migrate
php artisan db:seed        # أول مرة فقط
php artisan serve
```
Configure the database in `.env`.
## Workflow
An employee creates a ticket → The General Manager of Systems and Information approves it (assigning a technical specialist) or rejects it → The technical specialist begins the work, issues any necessary supplies, and closes the ticket → The requester evaluates the solution or reopens the ticket.
Every step is recorded in the tracking log, and both visual and audible notifications are triggered (every 15 seconds).

## Features
- Tickets: Conversations and comments, reassignment, reopening, solution rating, WhatsApp sharing, and deletion (With Permissions).
- Target resolution time based on priority (configured via `sla_hours` in `config/helpdesk.php`), with an "Overdue" status indicator.
- Dashboard featuring metrics and charts; reports on SLA compliance and technical performance; export functionality for tickets and hardware (to Excel).
- Company hardware and ERP system (integrated table or card view, with Excel import/export support); organizational structure (building/floor/room).
- Sectors: Sector heads oversee multiple departments, with visibility into their tickets and notifications for new submissions.
- QR labels for hardware: Scanning a label opens a new ticket pre-linked to that specific device (labels can be printed from the company hardware page).
- Activity log (tracking who performed which action and when) and automated daily database backups.
- Customizable roles and permissions, with granular access control for individual users.
- "My Account" page for updating passwords and phone numbers.
- Darkmode

## Device maintenance history
Open any company device (the device pop-up → "Maintenance history") to see all its tickets, repair/supplies counts, average resolution time, a 12-month failure chart and the supplies issued. A device is flagged **frequent failures** when it has `device_alert.repairs` repair tickets within `device_alert.days` days (see `config/helpdesk.php`). The device list can be sorted by most failures or filtered to flagged devices.

## QR labels
Set `HELPDESK_PUBLIC_URL` in `.env` to the address employees use (e.g. `http://192.168.1.10`), otherwise labels embed the address of the page they were printed from. The QR encoder is bundled (`public/vendor/qr`), no internet needed.

## Offline use (no internet)
Run once on a machine with internet, then deploy the `public/vendor` folder:
```
php artisan assets:install
```
Until those files exist the pages fall back to the CDN.

## Code structure (tickets)
`TicketController` (HTTP only) → `app/Http/Requests/*` (validation/authorization) → `app/Policies/TicketPolicy` → `app/Services/TicketWorkflow` (state changes, log, notifications).

## Notes
- Deleting a user = disabling the account (to preserve the ticket history); a disabled user is immediately logged out of their session.
- Country code for WhatsApp numbers: `country_code` in `config/helpdesk.php`.

## Backup and Scheduling
- Manual Backup: Performed via the "Backup" screen or by running `php artisan backup:db`. Backups are stored in `storage/app/private/backups` (retaining the 14 most recent versions).
- Automated Backup (daily at 2:00 AM): Requires the scheduler to run every minute. On Windows, use Task Scheduler to create a task that executes `php artisan schedule:run` from the project directory every minute. For testing purposes, you can use `php artisan schedule:work`.
- Restoration: Import the `.sql` (or `.sql.gz`) file into phpMyAdmin, or use the command: `gunzip < backup.sql.gz | mysql -u USER -p DBNAME`.
- Activity Log: Entries older than one year are automatically deleted (processed weekly via the same scheduler).

## New Permissions
`audit.view` and `backup.manage` are intended for the system administrator only, but they can be granted to any role or user.
