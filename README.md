# APN107-Walkie-Talkie-Redesign-API

This is api for Walkie-Talkie Redesign App.

Installation
=============

1. Clone the repository.

2. Run `composer install`

3. Run `cp .env.example .env`

4. Update database configuration

5. Run `php artisan key:generate`

6. Run `php artisan migrate`

7. Run `php artisan db:seed`

8. Run `php artisan serve`

9. Run `php artisan schedule-monitor:sync` (To monitor the scheduler)

Laravel Permission in storage
=============================

1. Run `sudo chown -R $USER:www-data storage`

2. Run `sudo chown -R $USER:www-data bootstrap/cache`

3. Run `chmod -R 775 storage`

4. Run `chmod -R 775 bootstrap/cache`

Version 1.0
=============

Release Date: N/A
