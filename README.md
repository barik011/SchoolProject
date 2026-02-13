# School Website + Admin CMS (PHP + MySQL)

Modern, responsive school website with dynamic frontend content and a secure admin panel.

## Stack
- HTML5
- Bootstrap 5
- Custom CSS
- PHP 7.4+
- MySQL 8+

## Features
- Public pages: Home, About, Facilities, Infrastructure, Gallery, Admission Inquiry, Contact
- Secure admission inquiry form (server/client validation + MySQL storage)
- Admin authentication with hashed passwords
- CMS for section content:
  - Home
  - About
  - Facilities
  - Infrastructure
- Homepage full-width banner carousel with multiple slides
- Admin banner management (add/edit/delete/show-hide/order)
- Gallery upload/visibility/delete
- Admission inquiry management:
  - View
  - Status updates
  - Delete
  - CSV export
- Theme controls:
  - Primary color
  - Default Light/Dark mode
- Frontend Light/Dark toggle stored in `localStorage`

## Project Structure
```text
/
├── admin/
├── assets/
│   ├── css/
│   └── js/
├── config/
├── database/
├── includes/
├── uploads/
├── index.php
├── about.php
├── facilities.php
├── infrastructure.php
├── gallery.php
├── admission.php
└── contact.php
```

## Setup
1. Create database + tables:
   - Import `database/schema.sql` into MySQL.
2. Configure DB credentials:
   - Edit `config/config.php` (or set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` as env vars).
3. Serve project in Apache/Nginx with PHP enabled.
4. Create first admin user:
   - Open `/admin/setup.php`.
5. Log in:
   - `/admin/login.php`.

## Security Notes
- All DB writes use prepared statements.
- CSRF token validation on admin and form actions.
- Passwords are stored using `password_hash()`.
- Image uploads validate MIME type and size.
- Upload directory blocks script execution (`uploads/.htaccess`).

## Recommended Production Hardening
- Enforce HTTPS.
- Move admin behind IP allow-list if possible.
- Add rate limiting for login.
- Rotate DB credentials and set least-privilege DB user.
- Add automated backups for MySQL and uploads.
