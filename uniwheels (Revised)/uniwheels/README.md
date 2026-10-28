# UniWheels Motors

Showroom website for a pre-owned car dealer, with a simple staff dashboard and WordPress.

- **Website** (`index.php`): the cars for sale, filters, Messenger inquiries, trade-in info.
- **Staff dashboard** (`admin-dashboard/`): add, edit, reserve, sell and remove cars; yearly sales download.
- **WordPress** (`wp-admin/`): every car is published there automatically as a post, and the
  About, Contact, Terms and Privacy pages are written there and shown on the website through `page.php`.

## Setup (XAMPP on Windows)

1. Put this folder at `C:\xampp\htdocs\uniwheels` (the folder name must be `uniwheels`).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Create the database:
   open http://localhost/phpmyadmin, click **New**, name it `uniwheels`, click **Create**,
   then **Import** the file `database/uniwheels.sql`.
4. Create `wp-config.php` (it is not on GitHub because it holds secret keys):
   - copy `wp-config-sample.php` and name the copy `wp-config.php`
   - set `DB_NAME` to `uniwheels`, `DB_USER` to `root`, `DB_PASSWORD` to `''` (empty)
   - replace the "put your unique phrase here" lines with fresh keys from
     https://api.wordpress.org/secret-key/1.1/salt/
5. Open the site:
   - Website: http://localhost/uniwheels/
   - Staff dashboard: http://localhost/uniwheels/admin-dashboard/
   - WordPress: http://localhost/uniwheels/wp-admin/

## Keeping the database file up to date

Cars and WordPress content live in MySQL, not in these files. Before pushing to GitHub, export the
database again (phpMyAdmin > uniwheels > Export > SQL) and save it over `database/uniwheels.sql`.
