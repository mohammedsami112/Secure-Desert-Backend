# Secure Desert API
This is a minimal, yet powerful API for Secure Desert. It is written in PHP and uses the Laravel framework.

## Installation

### Requirements
- PHP 8.0+
- Composer
- MySQL 8.0+ (or MariaDB 10.5+)

### Setup
1. Clone the repository
2. Run `composer install`
3. Run `php artisan key:generate`
4. Create a new database
5. Run the migrations `php artisan migrate --seed`
6. Run the server `php artisan serve`

## Tech Stack
As of now, the API is built using the following tech stack:
- [Laravel 9](https://laravel.com/docs/9.x/)
- [Laravel Sanctum](https://laravel.com/docs/9.x/sanctum)
- [Laravel Pint](https://laravel.com/docs/9.x/pint)
- [Spatie Laravel Image Optimizer](https://github.com/spatie/laravel-image-optimizer)
