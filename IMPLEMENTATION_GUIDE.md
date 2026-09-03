# Sundarakanda — implementation notes

Supersedes the earlier guide, which described the events feature as complete
when it had not been run against a database and did not work.

## Local development

```bash
brew install php mariadb
brew services start mariadb

mysql -e "CREATE DATABASE donors CHARACTER SET utf8mb4;
          CREATE USER 'donors_app'@'localhost' IDENTIFIED BY 'localdevpass';
          GRANT ALL ON donors.* TO 'donors_app'@'localhost';"

cd portal/sql
mysql donors < schema.sql
for m in migrations/00*.sql; do mysql donors < "$m"; done
mysql donors < seeds/events_september.sql

cd ../..
php -S 127.0.0.1:8000 -t .
```

`portal/config.local.php` holds the local database credentials and overrides
`config.php`. **It must not be uploaded to the live host** — production values
stay in `config.php`.

## Structure

```
inc/header.php, inc/footer.php   shared chrome for all 9 public pages
*.php                            public site (was .html, converted)
assets/                          single copy of site.css / portal.css / site.js
portal/                          member area
portal/admin/                    admin + operations manager area
portal/uploads/events/           uploaded banners (.htaccess denies execution)
portal/sql/migrations/           001-005, additive, re-runnable
```

## Roles

| Role | Can |
|------|-----|
| `participant` | Sign in, donate, RSVP, propose an event |
| `operations_manager` | The above, plus the admin dashboard, events, campaign, members |
| `admin` | Everything, including devotionals and announcements |

The shared `ADMIN_PASSWORD` login remains as a break-glass fallback and is
treated as the `admin` role.

## Event lifecycle

```
member proposes  ->  status = pending   (hidden everywhere public)
admin approves   ->  status = upcoming  (live on site + homepage)
admin declines   ->  status = rejected  (stays hidden; member sees why)
date passes      ->  listed under Past Events
admin archives   ->  status = archived  (hidden)
```

`status` is authoritative: only `upcoming` and `past` are ever public. Every
create, edit, approve, decline, archive, and RSVP writes an `audit_log` row.

## Migrations

| File | Adds |
|------|------|
| `schema.sql` | users, donations, subscribers, devotionals, email_log |
| `002` | RBAC role column, campaigns, participation, audit_log, site_settings |
| `003` | members (free membership) |
| `004` | events, event_rsvps |
| `005` | pending/rejected statuses, requested_by, reviewed_by, reviewed_at |

All are additive and safe to re-run.
