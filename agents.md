# Grocery Booking System - Development Plan

**Status:** Phase 1 ✅ COMPLETED  
**Framework:** Laravel 12 | **Database:** MySQL | **Auth:** JWT  
**Architecture:** Repository Pattern | Service Layer | Middleware-based Authorization

---

## Phase 1 — Project Initialization ✅ COMPLETED

### Task 01 — Initialize Laravel Project ✅
- Laravel 12.12.2 initialized
- MySQL database (grocery_db) created
- .env configured for MySQL connectivity
- Base migrations executed (users, cache, jobs tables)
- Application verified to boot successfully

**Commit:** `feat: Initialize Laravel 12 project with MySQL configuration`

---

## Phase 2 — Authentication & JWT Setup ✅ COMPLETED

### Task 02 — Install & Configure Laravel Sanctum/JWT ✅
- [x] Install `tymon/jwt-auth` package
- [x] Publish JWT config
- [x] Create JWT secrets in .env
- [x] Configure `config/auth.php` for JWT guard
- [x] Test JWT token generation

### Task 03 — Create User Model & Database ✅
- [x] User migration (email, password, role)
- [x] Role enum (admin, customer)
- [x] User factory for testing
- [x] Hash password in model mutator

### Task 04 — Build Auth Controller & Routes ✅
- [x] Register endpoint (email validation, password hashing)
- [x] Login endpoint (JWT token generation)
- [x] Logout endpoint
- [x] Refresh token endpoint
- [x] Protected route middleware test
- [x] Add API routes

### Task 05 — Create Auth Tests ✅
- [x] Test successful registration
- [x] Test login with valid credentials
- [x] Test login with invalid credentials
- [x] Test protected route access
- [x] Test token refresh

**Completed:** Phase 2 authentication flow with JWT and role support
**Verification:** `php artisan test tests/Feature/AuthTest.php` → 12 passed (37 assertions)

---

## Phase 3 — Role-Based Access Control (PENDING)

### Task 06 — Create Roles & Permissions
- [ ] Create roles table (admin, customer)
- [ ] Middleware: CheckRole
- [ ] Middleware: CheckPermission
- [ ] Seed default roles

### Task 07 — Admin Authorization Middleware
- [ ] Verify admin role in protected routes
- [ ] Deny customer access to admin endpoints
- [ ] Test role-based restrictions

---

## Phase 4 — Domain Models (PENDING)

### Task 08 — Create Category Model & Migration
- [ ] Category model (name, description, is_active)
- [ ] Category factory & seeder
- [ ] Routes: GET /categories, POST /categories (admin only)

### Task 09 — Create Product Model & Migration
- [ ] Product model (category_id, name, price, description, stock)
- [ ] Product factory & seeder
- [ ] Price & stock validation (cannot be negative)
- [ ] Routes: GET /products, POST /products (admin only)

### Task 10 — Create Order & OrderItem Models
- [ ] Order model (user_id, total_price, status, created_at)
- [ ] OrderItem model (order_id, product_id, quantity, unit_price)
- [ ] Relationships: Order hasMany OrderItem
- [ ] Relationships: Product hasMany OrderItem

---

## Phase 5 — Admin Features (PENDING)

### Task 11 — Admin Dashboard - View Products
- [ ] GET /admin/products (list all products with filters)
- [ ] Pagination support
- [ ] Search by name/category
- [ ] Response includes category info, stock status

### Task 12 — Admin Dashboard - Create/Update Products
- [ ] POST /admin/products (create new product)
- [ ] PUT /admin/products/{id} (update product)
- [ ] Validate: name, price, stock not negative
- [ ] Server-side validation ALWAYS
- [ ] Return validation errors (422) on failure

### Task 13 — Admin Dashboard - Delete Product
- [ ] DELETE /admin/products/{id}
- [ ] Soft delete implementation (is_archived flag)
- [ ] Cannot delete if orders exist (business rule)

### Task 14 — Admin Dashboard - View Orders
- [ ] GET /admin/orders (list all orders with items)
- [ ] Pagination & date range filters
- [ ] Response includes order items, customer email, totals

---

## Phase 6 — Customer Features (PENDING)

### Task 15 — Customer Dashboard - Browse Products
- [ ] GET /customer/products (paginated list)
- [ ] Filter by category, price range
- [ ] Display stock status (in stock, low stock, out of stock)
- [ ] Publicly available (no auth required for viewing)

### Task 16 — Customer Dashboard - View Order History
- [ ] GET /customer/orders (user's orders only)
- [ ] Include order items with product details
- [ ] Pagination with date filters
- [ ] Response: order date, items, total, status

### Task 17 — Customer Profile
- [ ] GET /customer/profile (user's own data)
- [ ] PUT /customer/profile (update email, password)
- [ ] Validate password confirmation
- [ ] Cannot change role via API

---

## Phase 7 — Order Placement & Validation (PENDING)

### Task 18 — Build Cart/Order Request Validation
- [ ] POST /customer/orders (place order)
- [ ] Request body: array of {product_id, quantity}
- [ ] Validate: product exists, quantity > 0, stock available
- [ ] Calculate total (product price × quantity)
- [ ] Return detailed errors if validation fails (422)

### Task 19 — Order Service Layer
- [ ] OrderService: placeOrder(userId, items)
- [ ] Service validates stock before creating order
- [ ] Service calculates correct total
- [ ] Service throws exception on validation failure
- [ ] Controller passes service response to client

---

## Phase 8 — Transaction Safety & Concurrency (PENDING)

### Task 20 — Implement Database Transactions
- [ ] Use DB::transaction() for order placement
- [ ] Atomic: validate stock + create order + update inventory
- [ ] Rollback entire operation on any failure
- [ ] Log transaction errors

### Task 21 — Row-Level Locking for Inventory
- [ ] Use SELECT...FOR UPDATE to lock product rows
- [ ] Lock only during stock validation & update
- [ ] Prevent race conditions (overselling protection)
- [ ] Test concurrent orders on low-stock product

### Task 22 — Inventory Update Service
- [ ] InventoryService: decrementStock(productId, quantity)
- [ ] Verify stock before decrement
- [ ] Atomic update: (quantity >= 0 after decrement)
- [ ] Throw exception if insufficient stock
- [ ] Log all stock changes

---

## Phase 9 — Order History & Analytics (PENDING)

### Task 23 — Order Status Management
- [ ] Order status enum (pending, confirmed, delivered, cancelled)
- [ ] Admin route: PUT /admin/orders/{id}/status (change status)
- [ ] Customer cannot change order status
- [ ] Log status change timestamp

### Task 24 — Customer Order Details Endpoint
- [ ] GET /customer/orders/{id}
- [ ] Include all order items with product info
- [ ] Include calculated subtotal, tax (if applicable), total
- [ ] Return 404 if order not belongs to user

### Task 25 — Admin Analytics Endpoints
- [ ] GET /admin/analytics/revenue (total, by date range)
- [ ] GET /admin/analytics/top-products (most ordered)
- [ ] GET /admin/analytics/order-count (daily/weekly/monthly)

---

## Phase 10 — Frontend - Blade Templates (PENDING)

### Task 26 — Create Base Layout & Navigation
- [ ] resources/views/layouts/app.blade.php
- [ ] Navigation bar with logout link
- [ ] Role-based menu (Admin vs Customer)
- [ ] Include Bootstrap or Tailwind CSS

### Task 27 — Admin Dashboard - Product Management UI
- [ ] Product list table (name, price, stock, actions)
- [ ] Create/Edit product form with validation
- [ ] Delete button with confirmation modal
- [ ] Show success/error messages

### Task 28 — Admin Dashboard - Orders UI
- [ ] Orders list table (order ID, customer, date, total, status)
- [ ] Order details modal (items, totals)
- [ ] Status dropdown to change order status
- [ ] Export orders to CSV (bonus)

### Task 29 — Customer Dashboard - Browse & Cart
- [ ] Product gallery/list with images (if available)
- [ ] Filter by category & price range
- [ ] Stock status indicator
- [ ] Add to cart (store in session or localStorage)

### Task 30 — Customer Dashboard - Checkout
- [ ] Cart summary (items, quantities, totals)
- [ ] Place order button (API call)
- [ ] Loading state & error handling
- [ ] Redirect to order confirmation on success

---

## Phase 11 — Frontend - AJAX Interactions (PENDING)

### Task 31 — Product AJAX Load
- [ ] Fetch products without page reload
- [ ] Dynamic filter/search via AJAX
- [ ] Pagination via AJAX
- [ ] Loading spinner during fetch

### Task 32 — Add to Cart AJAX
- [ ] Add product to cart without reload
- [ ] Update cart count in header
- [ ] Show toast notification (success/error)
- [ ] Handle out-of-stock products gracefully

### Task 33 — Order Placement AJAX
- [ ] Submit order via AJAX (POST /customer/orders)
- [ ] Display validation errors in form
- [ ] Show loading spinner
- [ ] Redirect to order confirmation page on success

### Task 34 — Admin Order Status Update AJAX
- [ ] Change order status via dropdown (no reload)
- [ ] Show toast on success/failure
- [ ] Update UI immediately
- [ ] Handle permission errors (403)

---

## Phase 12 — Advanced Testing (PENDING)

### Task 35 — Feature Tests - Authentication
- [ ] Test register endpoint (valid/invalid data)
- [ ] Test login/logout flow
- [ ] Test token refresh
- [ ] Test protected routes without token

### Task 36 — Feature Tests - Authorization
- [ ] Test admin routes deny customer access
- [ ] Test customer routes deny admin access (where applicable)
- [ ] Test user can only access own orders

### Task 37 — Feature Tests - Order Placement
- [ ] Test successful order placement
- [ ] Test validation errors (negative quantity, non-existent product)
- [ ] Test insufficient stock rejection
- [ ] Test order total calculation accuracy

### Task 38 — Feature Tests - Concurrency & Transactions
- [ ] Test race condition: two orders on same low-stock product
- [ ] Verify only one order succeeds, other rejected
- [ ] Test transaction rollback on validation failure
- [ ] Verify no partial orders created

### Task 39 — Unit Tests - Models & Services
- [ ] Test User model relationships
- [ ] Test Order/OrderItem relationships
- [ ] Test OrderService.placeOrder() logic
- [ ] Test InventoryService.decrementStock() logic

### Task 40 — API Documentation Tests
- [ ] Verify all endpoints return correct status codes
- [ ] Test error response format consistency
- [ ] Test pagination metadata
- [ ] Test filtering & search parameters

---

## Phase 13 — Error Handling & Logging (PENDING)

### Task 41 — Global Exception Handler
- [ ] Create custom exception classes
- [ ] Handle AuthenticationException (401)
- [ ] Handle AuthorizationException (403)
- [ ] Handle ValidationException (422)
- [ ] Handle ResourceNotFoundException (404)

### Task 42 — API Error Response Format
- [ ] Consistent JSON error responses
- [ ] Include error code, message, validation details
- [ ] Log all exceptions with context
- [ ] No sensitive data in error messages

### Task 43 — Logging & Monitoring
- [ ] Log all order placements (success/failure)
- [ ] Log inventory changes with reason
- [ ] Log authentication events
- [ ] Log admin actions (product CRUD)

---

## Phase 14 — Validation & Security (PENDING)

### Task 44 — Input Validation Rules
- [ ] Email validation (unique for registration)
- [ ] Password rules: min 8 chars, uppercase, number
- [ ] Product name/description length limits
- [ ] Price validation: decimal 2 places, > 0
- [ ] Quantity validation: integer > 0

### Task 45 — SQL Injection Prevention
- [ ] All queries use parameterized bindings (Eloquent)
- [ ] No raw SQL concatenation
- [ ] Test with malicious input

### Task 46 — CSRF Protection
- [ ] All POST/PUT/DELETE endpoints protected
- [ ] Include CSRF token in forms
- [ ] Test CSRF token validation

### Task 47 — Rate Limiting
- [ ] Rate limit auth endpoints (5 attempts/minute)
- [ ] Rate limit order placement (10/minute per user)
- [ ] Return 429 status on rate limit exceeded
- [ ] Include retry-after header

---

## Phase 15 — Seeding & Demo Data (PENDING)

### Task 48 — Create Seeders
- [ ] UserSeeder (1 admin, 5 customers)
- [ ] CategorySeeder (5 categories: fruits, vegetables, etc.)
- [ ] ProductSeeder (15 products with realistic data)
- [ ] Run: `php artisan db:seed`

### Task 49 — Create Factories
- [ ] UserFactory with role support
- [ ] CategoryFactory
- [ ] ProductFactory with stock variations
- [ ] Use for testing

---

## Phase 16 — Documentation & DevOps (PENDING)

### Task 50 — API Documentation
- [ ] Create API.md with all endpoints
- [ ] Document request/response examples
- [ ] Include authentication instructions
- [ ] Document error codes

### Task 51 — Setup Instructions
- [ ] Create SETUP.md (installation steps)
- [ ] .env.example with all required keys
- [ ] Database & migration instructions
- [ ] Test user credentials for demo

### Task 52 — Create Docker Support (Bonus)
- [ ] Dockerfile for PHP + Laravel
- [ ] docker-compose.yml (Laravel + MySQL + Redis)
- [ ] Volume mounts for development
- [ ] Test Docker build & run

### Task 53 — GitHub Actions CI/CD (Bonus)
- [ ] Run tests on push
- [ ] Run linting (Pint)
- [ ] Run static analysis
- [ ] Notification on failures

---

## Phase 17 — Internationalization & Polish (Pending)

### Task 54 — Laravel Localization
- [ ] Set up i18n (en, es, fr)
- [ ] Translate common messages
- [ ] Use in validation messages
- [ ] Add locale switcher (bonus)

### Task 55 — Performance Optimization
- [ ] Add database indexes (category_id, user_id, etc.)
- [ ] Eager load relationships (with())
- [ ] Cache frequently accessed data
- [ ] Run tests to verify improvements

### Task 56 — Security Audit
- [ ] Review all routes for authorization
- [ ] Verify no sensitive data in logs
- [ ] Check password hashing strength
- [ ] Test input validation edge cases

---

## Summary

**Total Tasks:** 56  
**Completed:** 1 (Task 01)  
**Remaining:** 55  

**Next Phase:** Phase 2 — Authentication & JWT Setup

---

## Notes for Developer

- Each task should be completed independently before moving to the next
- All endpoints must include server-side validation
- Business logic lives in Services, not Controllers
- Relationships are tested before feature tests
- Security is checked at every phase boundary
- No feature is "done" until tests pass

