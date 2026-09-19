# Setup

1. **Database**
   - Create the DB by running `schema.sql` in phpMyAdmin or the MySQL CLI:
     `mysql -u root -p < schema.sql`
   - This creates the `asd_journal` database and one admin user (username `admin`).

2. **Fix the admin password**
   The seeded password hash in `schema.sql` is a placeholder and won't work.
   Run this once in a PHP file (or `php -a`) to generate a real hash:
   ```php
   echo password_hash('your-real-password', PASSWORD_DEFAULT);
   ```
   Then update it in the `users` table:
   ```sql
   UPDATE users SET password_hash = 'paste-hash-here' WHERE username = 'admin';
   ```
   Also update the email in that row to your real one.

3. **Config**
   Edit `config/config.php`:
   - `DB_USER` / `DB_PASS` to match your MySQL setup (XAMPP default is user `root`, empty password)
   - `BASE_URL` once you know your real domain/path

4. **Web server**
   Point your web server's document root at the `htdocs/` folder only.
   - **XAMPP**: put this whole `asd-journal` folder inside `htdocs/` — actually,
     since your web server's root IS called `htdocs`, place the *contents* of
     this project's `htdocs/` folder directly into XAMPP's `htdocs/`, and keep
     `app/`, `config/`, `storage/` as sibling folders one level up (outside
     XAMPP's web-facing `htdocs/`). That keeps your PHP source and DB
     credentials unreachable by URL.
   - Enable `mod_rewrite` in Apache for the `.htaccess` clean URLs to work.

5. **Log in as admin**, then use "New entry" in the header to write your first post.

# Notes

- Posts are public to all visitors by default (or save as "Draft" to keep private).
- Anyone can register for free ("Subscribe") — that's what unlocks commenting.
- Likes work for everyone, including anonymous visitors, tracked via a cookie
  so the same visitor can't like a post repeatedly.
