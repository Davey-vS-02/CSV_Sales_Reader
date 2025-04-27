# Laravel Project

This is a Laravel-based web application using MariaDB for dbm.
The application comes with unit tests that can be run via the Laravel testing suite, and migration files that need to be applied to set up the database.
The user from this company can upload a sales records CSV file. 
The web-application will then upload the CSV, validate each row individually and save each row in an appropriate database table.
The invalid rows can then be displayed after uploads.

## What Constitutes an invalid row?
- Any null or empty values in the row, accept for the COMMENTS column.
- Any cancalled orders.
- Any format errors like date/time format errors.
- Values that do not fit into obvious enum patterns, like "YES"/"NO" or "X"/"N/A".

This definition of an invalid row is subject to change. This is my own personal interpretation of what an invalid row should be.
I have consulted someone on the exact details of this issue, and the code could possibly change depending on what the answer is.

## Requirements

- PHP >= 8.0
- Composer >= 2.8
- MariaDB >= 10.3
- Laravel 12 or later

## Installation
### 1. Clone the repository

Clone the repository to your local machine and navigate into the project directory:

git clone https://github.com/Davey-vS-02/CSV_Sales_Reader
cd CSV_FILE_READER

### 2. Install dependencies
Run Composer to install the project's dependencies:
**composer install**

### 3. Configure the environment
Inside the root folder of the project, find the .env file.
Now, configure your database connection in the .env file to the MariaDB database. Update the following variables to match your local MariaDB credentials:

DB_CONNECTION=mariadb **When using a mysql db instead of mariadb, change this to - mysql**
DB_HOST=127.0.0.1 **Most common local host port**
DB_PORT=3307 **Ensure to change port to correct db port**
DB_DATABASE=sales_csv_processor **Name the database this. This schema will be created during migrations.**
DB_USERNAME=root
DB_PASSWORD=addYourDBPasswordHere

### 4. Run migrations
After setting up db connection, set up your database schema, run the migrations with the following command:
php artisan migrate

When prompted with:
**WARN  The database 'sales_csv_processor' does not exist on the 'mariadb' connection.
Would you like to create it? (yes/no) [yes]**

### 5. Start the Development Server
To start the development server, you can use Laravel's built-in artisan serve command:
**php artisan serve**
This will start the application, and you can access it in your browser at http://127.0.0.1:8000 or whatever location the serve command provides in the terminal.

### 6. Starting queue worker before using web-application or running unit tests.
**Queue Worker Setup**
For this project, the queue worker is activated manually.
You can run the worker manually during development or set it up to run automatically in a production environment.
To start the queue worker manually, use the following command:
php artisan queue:work

Later, for a production ready setup, this can be replaced with a setup that uses Supervisor as process manager, which will automatically run and restart workers.

### 7. Using the web application:
Now, the web application can be access. On the landing page, a CSV file can be uploaded.
If queue worker is running, the CSV file will automatically start processing row by row.
Each row is now validated and saved to an appropriate database table.
After the CSV file is processed, the invalid files can be displayed and navigated through, to see current issues with the file.

### 8. Running Unit Tests
To run the unit tests for the application, use the following command:
php artisan test
This will run all tests located in the tests directory.
The tests only test the row validation logic.

**Troubleshooting**
Database connection issues: Ensure that MariaDB or MySQL is running, and that the .env file contains the correct credentials.
CSV file not loading: Ensure worker is started with **php artisan queue:work**

Permissions issues: Ensure that your storage and bootstrap/cache directories are writable by the web server:

**Refresh your database: If you need to reset your database and re-run all migrations, you can use the following command:**
php artisan migrate:fresh
This will drop all tables, re-run all migrations, and apply fresh database schema changes.