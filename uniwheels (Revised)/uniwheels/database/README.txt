UniWheels database (web access to this folder is blocked by .htaccess)

uniwheels.sql   The whole `uniwheels` database: cars, employees (staff sign-in)
                and the WordPress tables (wp_...). Exported Oct 28, 2026.

To set it up on a new computer
  1. Open http://localhost/phpmyadmin
  2. Click New, name the database  uniwheels  and click Create
  3. Open the new database, click Import, choose uniwheels.sql, click Import

To update this file after changes (so GitHub has the latest cars)
  phpMyAdmin > uniwheels > Export > Quick > SQL > Export,
  then save it here as uniwheels.sql, replacing the old one.
