# Grocery Booking System

A complete grocery booking system built with **Laravel 12**, featuring JWT authentication, role-based access control, transaction-safe inventory management, and a Blade + AJAX frontend.

## Features

- **JWT Authentication** — Secure token-based authentication using `tymon/jwt-auth`
- **Role-Based Access Control** — Admin and Customer roles with middleware enforcement
- **Repository Pattern** — Clean separation of data access logic
- **Service Layer** — Business logic isolated from controllers
- **Transaction-Safe Orders** — Atomic order placement with row-level locking to prevent overselling
- **Order Analytics** — Revenue, top products, and order count analytics
- **Blade + AJAX Frontend** — Responsive Tailwind CSS UI with AJAX interactions
- **Comprehensive Testing** — 155+ automated tests covering all features
- **Rate Limiting** — Protection against brute force and abuse
- **Docker Support** — Containerized deployment with Docker Compose

## Architecture

The application follows a clean layered architecture:

```
Route → Middleware → Controller → Service → Repository Interface → Eloquent Repository → Database
```

### Key Components

| Layer | Location | Responsibility |
|-------|----------|----------------|
| Controllers | `app/Http/Controllers/Api/` | HTTP request/response handling |
| Services | `app/Services/` | Business logic and orchestration |
| Repositories | `app/Repositories/` | Data access and persistence |
| Contracts | `app/Contracts/Repositories/` | Repository interfaces |
| Models | `app/Models/` | Eloquent models and relationships |
| Middleware | `app/Http/Middleware/` | Authentication and authorization |

### Transaction Safety

Order placement uses `DB::transaction()` with `lockForUpdate()` to prevent overselling under concurrent requests:

```php
DB::transaction(function () use ($user, $items) {
    foreach ($items as $entry) {
        $product = GroceryItem::query()->lockForUpdate()->findOrFail($entry['product_id']);
        // Validate stock, calculate totals, create order
    }
}, attempts: 3);
```

## Quick Start

```bash
# Clone and install
git clone https://github.com/EhfazAdnan/grocery_booking.git
cd grocery_booking
composer install
npm install

# Configure
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Database
php artisan migrate
php artisan db:seed

# Run
php artisan serve
```

See [SETUP.md](SETUP.md) for detailed setup instructions.

## Demo Credentials

After running `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@grocery.com` | `Password123` |
| Customer | `alice@example.com` | `Password123` |
| Customer | `bob@example.com` | `Password123` |

## API Endpoints

### Authentication
- `POST /api/auth/register` — Register a new customer
- `POST /api/auth/login` — Login and receive JWT token
- `POST /api/auth/logout` — Logout (invalidate token)
- `POST /api/auth/refresh` — Refresh JWT token
- `GET /api/auth/me` — Get authenticated user

### Customer
- `GET /api/customer/products` — Browse products (public)
- `POST /api/customer/orders` — Place order
- `GET /api/customer/orders` — Order history
- `GET /api/customer/orders/{id}` — Order details
- `GET /api/customer/profile` — Get profile
- `PUT /api/customer/profile` — Update profile

### Admin
- `GET/POST /api/admin/grocery-items` — List/create products
- `GET/PUT/DELETE /api/admin/grocery-items/{id}` — Product CRUD
- `PATCH /api/admin/grocery-items/{id}/stock` — Update stock
- `GET /api/admin/orders` — List all orders
- `PUT /api/admin/orders/{id}/status` — Change order status
- `GET /api/admin/analytics/revenue` — Revenue analytics
- `GET /api/admin/analytics/top-products` — Top products
- `GET /api/admin/analytics/order-count` — Order counts

See [API.md](API.md) for complete API documentation.

## Running Tests

```bash
php artisan test
```

The test suite includes 155+ tests covering:
- Authentication (register, login, logout, refresh)
- Authorization (role-based access control)
- Order placement and validation
- Concurrency and transaction safety
- Analytics endpoints
- API contract and error handling
- Validation and security

## Docker

```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

The application will be available at `http://localhost:8000`.

## Technology Stack

- **Backend:** Laravel 12, PHP 8.2
- **Database:** MySQL 8.0
- **Authentication:** tymon/jwt-auth
- **Frontend:** Blade templates, Tailwind CSS, vanilla JavaScript (AJAX)
- **Testing:** PHPUnit 11
- **Containerization:** Docker, Docker Compose

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
