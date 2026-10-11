<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Local Development

This is a Laravel + Blade application styled with Tailwind CSS and bundled by Vite.

```sh
composer install
npm install

cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

composer dev          # runs php artisan dev (server + Vite + queue)
```

The app is then available at <http://localhost:8000>.

## Docker

The stack runs nginx in front of PHP-FPM, plus a queue worker. Frontend assets are compiled in a Node build stage, so the runtime image contains `public/build`.

```sh
docker compose up -d --build
```

- App: <http://localhost:8080> (override with `APP_PORT`)
- The `app` container runs migrations and creates the storage link on boot
- `vendor` and `node_modules` are kept in named volumes, so the bind-mounted source stays authoritative

Common commands:

```sh
docker compose exec app php artisan test      # run the test suite
docker compose exec app php artisan tinker    # console
docker compose logs -f app                    # application log
docker compose down                           # stop (add -v to drop the vendor volume)
```

### Frontend hot reload

```sh
docker compose --profile dev up -d node
```

This starts the Vite dev server on <http://localhost:5173> and writes `public/hot`, so Blade's `@vite` points at the dev server. Stop it with `docker compose --profile dev stop node`; delete `public/hot` to fall back to the compiled assets.

### Optional services

MySQL and Redis are available through compose profiles. The app's dashboard caches its query results for one minute. To try that cache with Redis, set these values in `.env` (when using the Compose app, use `REDIS_HOST=redis`; when running PHP directly on your machine, use `REDIS_HOST=127.0.0.1`):

```dotenv
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
```

Then rebuild the PHP image so it has the Redis PHP extension, and start the Redis profile:

```sh
docker compose build app
docker compose --profile redis up -d
```

Open the dashboard twice within a minute: the first request runs the database queries and writes the result to Redis; the next request can use the cached result. Inspect Redis with `docker compose exec redis redis-cli scan 0 match '*dashboard*'`. The cache key includes the user ID, and expires after one minute. To try the database cache again, set `CACHE_STORE=database` and clear the cached configuration with `php artisan config:clear` (or rebuild/restart the app container after changing `.env`).

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
