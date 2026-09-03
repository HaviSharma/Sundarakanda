# Sundarakanda Donor Portal — Setup Guide

A PHP + MySQL donor portal that sits alongside your existing static site.
Donors can register, log in, record donations, request a blessing, and
manage their email preferences. Non-donors can subscribe to a daily
devotional email. Admins can queue devotionals, record offline donations,
and send one-off announcements (e.g. new events) to everyone subscribed.

## What's inside

```
portal/
  config.php              <- fill in your real values before uploading
  index.php, login.php, signup.php, dashboard.php, donate.php,
  blessings.php, subscribe.php, unsubscribe.php, events.php,
  forgot-password.php, reset-password.php, logout.php
  admin/                  <- password-gated staff tools
  cron/send_daily_devotional.php
  sql/schema.sql          <- matches the `donors` database already set up
  includes/                <- shared PHP (db, auth, mail, header/footer)
  assets/                  <- portal-specific CSS on top of your main site's design
```

## 1. Fill in `config.php`

Open `config.php` and replace:
- `DB_PASS` — the password for your `donors_app` MySQL user
- `ADMIN_PASSWORD` — a strong password for `/admin`
- `APP_SECRET` — any random 64-character string
- `SITE_URL` — where this folder will live, e.g. `https://www.sundarakanda.com/portal`

## 2. Database

Your `donors` database and tables already match what this app expects. If
you ever need to recreate them from scratch, `sql/schema.sql` has the exact
`CREATE TABLE` statements — import it once via phpMyAdmin on an empty
database.

**New in this update:** import `sql/migrations/002_campaign_platform.sql`
via phpMyAdmin (Run SQL tab, paste and go) **once**, after `schema.sql`.
It's additive-only — it adds a `role` column to `users`, adds new tables for
the 1 Crore Parayanam campaign (`campaigns`, `participation_entries`,
`campaign_adjustments`, `audit_log`, `site_settings`), and seeds the
campaign row. It does not touch or delete any existing donor/donation data.
It's safe to re-run — it won't create duplicates.

### Giving a staff member campaign-management access

The shared `ADMIN_PASSWORD` login can view the campaign dashboard and
approve/reject individual entries, but **cannot** add historical (pre-2024)
counts — that action needs to be traceable to a real person, not a shared
password. To let someone add historical counts: have them create a normal
donor account at `/portal/signup.php`, then in phpMyAdmin run:

```sql
UPDATE users SET role='operations_manager' WHERE email='their@email.com';
```

(use `role='admin'` for full access). They can then sign in normally and
use `/portal/admin/campaign.php`.

## 3. Upload

Upload the whole `portal` folder into the same directory as your existing
site files (so `portal/` sits next to `index.html`, `img/`, etc. — the
portal reuses your `img/` folder and links back to your main pages).

## 4. Set up the daily devotional cron job

In cPanel → **Cron Jobs**, add a job that runs once a day (e.g. 6:00 AM):

```
php /home/YOUR_CPANEL_USERNAME/public_html/portal/cron/send_daily_devotional.php
```

Ask your host for the exact path if you're unsure — it's usually
`/home/<cpanel-username>/public_html/portal/cron/send_daily_devotional.php`.
This only sends an email when a devotional has been queued in
`/admin/devotionals.php` for that date and hasn't already been sent.

## 5. Try it

- Visit `/portal/` — create a test donor account, record a test pledge, and
  request a blessing.
- Visit `/portal/admin/login.php` and sign in with your `ADMIN_PASSWORD` to
  queue a devotional, record a donation, or send an announcement.

## How donations work (no payment processor)

This is a **record-only** flow, by design: a donor enters an amount and
picks how they'll send it (check, Zelle, bank transfer, or in person), and
the pledge is saved to their history immediately. No card numbers are
collected anywhere. Once you actually receive the gift, you (or the donor)
can note it as confirmed — the simplest way is editing the donation's
`notes` field directly in phpMyAdmin, or recording a fresh entry from
`/portal/admin/donations.php` if it's easier to track that way.

If you'd like real card payments later (Stripe or PayPal), that's a
separate integration — let your developer know and it can be added without
changing the rest of the portal.

## Security notes already built in

- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never stored
  in plain text.
- All database queries use prepared statements.
- Every form checks a CSRF token.
- Admin access is a single shared password, kept out of the `users` table.
- `sql/` and `cron/` are blocked from direct web access via `.htaccess`.

## Tax-deductibility

The donation page intentionally does **not** claim tax-deductible status or
show an EIN, since that wasn't confirmed. If Sundarakanda is a registered
501(c)(3), you can add that language and your EIN to `donate.php` yourself
(or ask for a follow-up update).
