# Grocery Booking System — Setup Instructions

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Node.js 18+ (for frontend assets)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/EhfazAdnan/grocery_booking.git
cd grocery_booking
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 5. Configure Database

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Generate JWT Secret

```bash
php artisan jwt:secret
```

This will update your `.env` file with a `JWT_SECRET` value.

### 7. Run Migrations

```bash
php artisan migrate
```

This creates all necessary tables in the database.

### 8. Seed Demo Data (Optional)

```bash
php artisan db:seed
```

This creates demo users and products:

**Admin Account:**
- Email: `admin@grocery.com`
- Password: `Password123`

**Customer Accounts:**
- Email: `alice@example.com`, `bob@example.com`, `carol@example.com`, `david@example.com`, `eva@example.com`
- Password: `Password123` (for all)

### 9. Build Frontend Assets (Optional)

The frontend loads Tailwind CSS and htmx from a CDN, so this step is **not required** to run the application.

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

### 10. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

---

## Running Tests

Run the full test suite:

```bash
php artisan test
```

Run tests with detailed output:

```bash
php artisan test --testdox
```

Run specific test files:

```bash
php artisan test tests/Feature/AuthTest.php
```

---

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | Laravel |
| `APP_ENV` | Environment (local/production) | local |
| `APP_DEBUG` | Debug mode | true |
| `APP_URL` | Application URL | http://localhost |
| `DB_CONNECTION` | Database driver | mysql |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_DATABASE` | Database name | grocery_db |
| `DB_USERNAME` | Database username | root |
| `DB_PASSWORD` | Database password | (empty) |
| `JWT_SECRET` | JWT signing secret | (generated) |
| `JWT_TTL` | Token lifetime (minutes) | 60 |
| `JWT_REFRESH_TTL` | Refresh window (minutes) | 20160 |

---

## Troubleshooting

### Common Issues

**1. "JWT Secret not set"**
Run `php artisan jwt:secret` to generate a secret (the `jwt:generate` command was removed in `tymon/jwt-auth` v2).

**2. "Database connection failed"**
Verify your `.env` database credentials and ensure MySQL is running.

**3. "Class not found" errors**
Run `composer dump-autoload` to refresh the autoloader.

**4. Frontend assets not loading**
Run `npm run build` to compile assets, or `npm run dev` for development.

**5. Tests failing with database errors**
Ensure your test database is configured. The default configuration uses SQLite in-memory for tests.

---

## Docker Setup (Optional)

See [Docker](#docker) section below for containerized deployment.

---

## API Documentation

See [API.md](API.md) for complete API documentation including endpoints, request/response examples, and error codes.

---

## Docker

### Using Docker Compose

> **Before starting:** the containers bind-mount your project directory, so a local `.env` file is required — `docker compose` only reads `.env.docker`, which contains no `APP_KEY` or `JWT_SECRET`.
>
> ```bash
> cp .env.example .env
> php artisan key:generate
> php artisan jwt:secret
> ```
>
> On Linux/macOS, also ensure the web server can write to the storage directories (the bind mount overrides the permission setup done in the `Dockerfile`):
>
> ```bash
> chmod -R 775 storage bootstrap/cache
> ```

Build and start the containers:

```bash
docker compose up -d
```

Run migrations:

```bash
docker compose exec app php artisan migrate
```

Seed demo data:

```bash
docker compose exec app php artisan db:seed
```

View logs:

```bash
docker compose logs -f app
```

Stop containers:

```bash
docker compose down
```

### Docker Services

| Service | Port | Description |
|---------|------|-------------|
| webserver | 8000 | Nginx web server (entry point at http://localhost:8000) |
| app | 9000 | PHP 8.2-FPM + Laravel application (internal PHP-FPM; not exposed to the host) |
| mysql | 3306 | MySQL 8.0 database |
| redis | 6379 | Redis cache |
