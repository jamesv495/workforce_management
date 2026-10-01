# Workforce Management — PHP + XAMPP + MySQL

## Requirements
- XAMPP with Apache, MySQL/MariaDB, and PHP 8.1+.
- A browser such as Chrome or Edge.

## 1. Copy the project
Place the `Workforce Management` folder in:

`C:\xampp\htdocs\Workforce Management`

## 2. Start XAMPP
Start **Apache** and **MySQL** from the XAMPP Control Panel.

## 3. Create/import the database
Open:

`http://localhost/phpmyadmin/`

Then use **Import** and select:

`workforce.sql`

The SQL file now creates and selects the `workforce_management` database automatically.

## 4. Check the local database connection
The default connection in `api/config/database.php` is:

- Host: `localhost`
- Database: `workforce_management`
- User: `root`
- Password: empty

Change these values if your XAMPP MySQL setup uses another username/password.

## 5. Open the application
Use:

`http://localhost/Workforce%20Management/login.php`

The web application now uses PHP pages for all former HTML pages.

## 6. Gmail OTP
The login flow can require a six-digit Gmail OTP on a new device. Configure `api/config/mail.local.php` for local XAMPP testing, or set the `WFM_MAIL_*` environment variables on the server.

## 7. Important
For production/free hosting, do not expose database passwords, Gmail app passwords, or other secrets in source control. Use the hosting provider's environment/configuration facility where available.
