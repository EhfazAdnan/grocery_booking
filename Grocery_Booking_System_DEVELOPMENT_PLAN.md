# Grocery Booking System — Development Task Plan

## Purpose

This document is the **single development plan and implementation guide** for the Grocery Booking System Laravel assessment.

The project must be developed **task-by-task**, not all at once.

GitHub Copilot should use this document as the project-level development specification. For each task:

1. Implement **only the current task**.
2. Do not prematurely implement future tasks.
3. Follow the architecture and conventions defined in this document.
4. Keep the implementation production-quality and maintainable.
5. Run the relevant tests/checks before considering the task complete.
6. Do not introduce unnecessary packages or abstractions.
7. Update documentation when the current task materially changes project setup or architecture.
8. At the end of each task, report:
   - files created/changed
   - what was implemented
   - commands used for verification
   - tests added/updated
   - any assumptions or decisions
   - anything that should be done in the next task

---

# 1. Assignment Scope

Build a small but complete **Grocery Booking System** using Laravel.

The assessment has two roles:

- **Admin** — manages grocery catalogue and stock.
- **User** — browses available grocery items, places orders/bookings, and views their own order history.

The assignment evaluates architecture and engineering judgment, not only CRUD functionality.

Required areas:

- JWT authentication
- Role-based access control
- Repository Design Pattern
- Laravel/Eloquent
- MySQL preferred
- Grocery item management
- Inventory/stock management
- Multi-item orders
- Transaction-safe stock deduction
- Blade frontend
- AJAX interaction
- Automated tests
- README/documentation

Bonus areas:

- Docker
- English/Bangla localization

---

# 2. Important Engineering Principles

## 2.1 Task-by-task implementation

Do not implement the entire application in one step.

The expected workflow is:

```text
Task
  ↓
Implement
  ↓
Run tests/checks
  ↓
Review
  ↓
Developer manually commits
  ↓
Next Task
```

Each task should leave the project in a working state whenever reasonably possible.

### Git Commit Workflow

Git commits are **manual developer actions** and are not part of the Copilot implementation tasks.

After each task is completed and verified:

1. Review the changes.
2. Run the relevant tests/checks.
3. Commit the completed task manually.
4. Use a short, professional Conventional Commit-style message.
5. Do not ask Copilot to create the commit unless explicitly requested.

Preferred commit message format:

```text
<type>: <short imperative description>
```

Recommended types:

```text
feat      New functionality
fix       Bug fix
test      Tests
refactor  Code restructuring without behavior change
docs      Documentation
chore     Tooling/configuration/maintenance
```

Examples:

```text
feat: add JWT authentication
feat: add role-based authorization
feat: implement repository contracts
feat: add grocery item management
feat: implement order placement
fix: prevent inventory overselling
test: add order transaction tests
docs: document API architecture
chore: configure Docker environment
```

Commit messages should be:

- short
- specific
- written in imperative style
- focused on the completed task
- free of unnecessary ticket numbers or implementation details unless required

Do not use vague messages such as:

```text
update
changes
fix stuff
done
final
work
```

When reporting a completed task, provide **one recommended commit message** at the end of the task summary.

## 2.2 Do not over-engineer

Use Laravel's conventions.

Avoid:

- unnecessary design patterns
- unnecessary packages
- unnecessary interfaces
- generic "BaseRepository" abstractions without a real benefit
- excessive helper classes
- premature microservice-style architecture

The Repository Pattern is required specifically for the domain repositories described below.

## 2.3 Business logic location

Keep controllers thin.

Preferred flow:

```text
Route
  ↓
Middleware
  ↓
Controller
  ↓
Service
  ↓
Repository Interface
  ↓
Eloquent Repository
  ↓
Database
```

Controllers should coordinate HTTP concerns.

Services should contain business/use-case logic.

Repositories should handle persistence/data-access concerns.

## 2.4 Security

Never trust client-submitted:

- price
- subtotal
- total
- stock
- role
- ownership

Important business values must be calculated or verified server-side.

## 2.5 Database integrity

Use:

- foreign keys
- appropriate indexes
- appropriate data types
- database transactions
- row-level locking where required
- constraints that prevent invalid states where practical

---

# 3. Target Architecture

Use a conventional Laravel application.

Recommended structure:

```text
app/
├── Contracts/
│   └── Repositories/
│       ├── GroceryItemRepositoryInterface.php
│       └── OrderRepositoryInterface.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   ├── Admin/
│   │   │   └── User/
│   │   └── Web/
│   │
│   ├── Middleware/
│   └── Requests/
│
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── GroceryItem.php
│   ├── Order.php
│   └── OrderItem.php
│
├── Repositories/
│   ├── GroceryItemRepository.php
│   └── OrderRepository.php
│
├── Services/
│   ├── AuthService.php
│   ├── GroceryItemService.php
│   └── OrderService.php
│
└── Providers/
    └── RepositoryServiceProvider.php
```

The exact folder organization may be adjusted to fit the installed Laravel version, but the architectural responsibilities should remain clear.

---

# 4. Database Model

Required entities:

```text
Role
User
GroceryItem
Order
OrderItem
```

Recommended relationships:

```text
Role
  └── hasMany Users

User
  ├── belongsTo Role
  └── hasMany Orders

GroceryItem
  └── hasMany OrderItems

Order
  ├── belongsTo User
  └── hasMany OrderItems

OrderItem
  ├── belongsTo Order
  └── belongsTo GroceryItem
```

## 4.1 Roles

Use at least:

```text
admin
user
```

For this assessment, prefer a simple role relationship rather than introducing a full permissions package unless there is a strong technical reason.

Recommended:

```text
users.role_id → roles.id
```

## 4.2 Grocery Items

Recommended fields:

```text
id
name
description
price
stock
is_active
created_at
updated_at
```

Consider appropriate decimal precision for monetary values.

Do not use floating-point types for money.

## 4.3 Orders

Recommended fields:

```text
id
user_id
status
total_amount
created_at
updated_at
```

The order status should be implemented using a controlled set of values.

Keep the initial status model simple unless the assignment requires more.

## 4.4 Order Items

Recommended fields:

```text
id
order_id
grocery_item_id
quantity
unit_price
subtotal
created_at
updated_at
```

`unit_price` must be stored on the order item.

Reason:

If a product's current price changes after an order is placed, historical orders must still retain the price used when that order was created.

---

# 5. API Design

Use a clear API structure.

## Authentication

```text
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh
```

## User

```text
GET  /api/grocery-items
POST /api/orders
GET  /api/orders
GET  /api/orders/{order}
```

## Admin

```text
GET    /api/admin/grocery-items
POST   /api/admin/grocery-items
GET    /api/admin/grocery-items/{groceryItem}
PUT    /api/admin/grocery-items/{groceryItem}
PATCH  /api/admin/grocery-items/{groceryItem}/stock
DELETE /api/admin/grocery-items/{groceryItem}
```

Route naming may be adjusted to Laravel conventions, but the API must remain clear and RESTful.

---

# 6. Development Phases

---

# Phase 1 — Project Initialization

## Task 01 — Initialize Laravel Project

### Objective

Create the Laravel application foundation.

### Requirements

- Create/configure Laravel project.
- Configure `.env`.
- Configure MySQL connection.
- Verify application boots correctly.
- Do not implement authentication, grocery items, orders, or frontend features yet.

### Acceptance Criteria

- Laravel application starts successfully.
- Database connection works.
- Environment configuration is valid.
- No unnecessary packages are installed.

---

# Phase 2 — Authentication and Roles

---

## Task 03 — Install and Configure JWT Authentication

### Objective

Implement JWT-based authentication.

### Requirements

Use `tymon/jwt-auth` or the appropriate JWT package compatible with the Laravel version.

Configure:

- authentication guard
- User JWT implementation
- JWT secret
- token configuration

Do not implement role authorization yet.

### Acceptance Criteria

The application can generate and validate JWT tokens.

---

## Task 04 — Create Role Model and Database Structure

### Requirements

Create:

- `roles` table
- Role model
- relationship from User → Role
- role seeder

Seed:

```text
admin
user
```

Update users appropriately.

### Acceptance Criteria

A user can be associated with exactly the intended application role.

---

## Task 05 — Implement Registration

### Endpoint

```text
POST /api/auth/register
```

### Requirements

- Validate registration input.
- Create user.
- Assign default `user` role.
- Never allow public registration to assign `admin`.
- Return appropriate response.

### Acceptance Criteria

A newly registered user receives the `user` role automatically.

---

## Task 06 — Implement Login

### Endpoint

```text
POST /api/auth/login
```

### Requirements

- Validate credentials.
- Generate JWT.
- Return authenticated user information and token.

Do not expose sensitive fields.

---

## Task 07 — Implement Logout

### Endpoint

```text
POST /api/auth/logout
```

### Requirements

- Require authentication.
- Invalidate/logout the current JWT according to the chosen JWT package.

---

## Task 08 — Implement Token Refresh

### Endpoint

```text
POST /api/auth/refresh
```

### Requirements

- Implement JWT refresh.
- Return the refreshed token.
- Follow the selected JWT package's recommended lifecycle.

---

## Task 09 — Implement Authentication Middleware

### Requirements

Create/configure middleware so protected API routes require valid JWT authentication.

Do not manually authenticate inside every controller.

Expected flow:

```text
Request
 ↓
JWT middleware
 ↓
Authenticated controller
```

---

## Task 10 — Implement Role Middleware

### Requirements

Create middleware for role-based access.

Example:

```text
role:admin
role:user
```

Authorization must be enforced at middleware/route level.

Do NOT write this repeatedly inside controllers:

```php
if ($user->role !== 'admin') {
    abort(403);
}
```

Instead:

```text
Route
 ↓
auth middleware
 ↓
role middleware
 ↓
controller
```

### Acceptance Criteria

- Unauthenticated users receive appropriate authentication response.
- Normal users cannot access admin endpoints.
- Admin users can access admin endpoints.
- Controllers do not contain repetitive role checks.

---

# Phase 3 — Repository Architecture

---

## Task 11 — Create Repository Contracts

Create:

```text
GroceryItemRepositoryInterface
OrderRepositoryInterface
```

Define methods around actual use cases.

Avoid creating methods that are not currently needed.

---

## Task 12 — Implement Eloquent Repositories

Create:

```text
GroceryItemRepository
OrderRepository
```

Repositories must implement their respective interfaces.

Use Eloquent for persistence.

Keep business decisions out of repositories when they belong to the service layer.

---

## Task 13 — Create Repository Service Provider

Bind:

```text
GroceryItemRepositoryInterface
        ↓
GroceryItemRepository

OrderRepositoryInterface
        ↓
OrderRepository
```

Register the provider correctly.

### Acceptance Criteria

Laravel dependency injection resolves repository interfaces to their Eloquent implementations.

---

## Task 14 — Create Service Layer

Create services where business logic belongs.

Recommended:

```text
GroceryItemService
OrderService
AuthService
```

Do not force every trivial operation into a service merely for the sake of abstraction.

The order placement workflow must use a service.

---

# Phase 4 — Database and Grocery Domain

---

## Task 15 — Create GroceryItem Migration and Model

### Requirements

Create the grocery item database structure.

Important:

- monetary values must use decimal-compatible storage
- stock must not become negative
- add useful indexes
- add `is_active`

Create Eloquent model and casts where appropriate.

---

## Task 16 — Create Order and OrderItem Migrations/Models

Create:

```text
orders
order_items
```

Add foreign keys.

Create relationships.

Ensure the schema supports multiple grocery items in one order.

---

## Task 17 — Create Factories and Seeders

Create:

- RoleSeeder
- User factory
- GroceryItem factory
- sample grocery data
- admin test user
- normal test user

Ensure:

```bash
php artisan migrate:fresh --seed
```

works correctly.

Document test credentials if appropriate for local development.

Never commit real credentials.

---

# Phase 5 — Admin Grocery Management

---

## Task 18 — Admin Grocery Item Listing

### Endpoint

```text
GET /api/admin/grocery-items
```

### Requirements

- Admin authentication.
- Admin role middleware.
- Return grocery items.
- Use repository/service architecture.
- Support pagination.

---

## Task 19 — Create Grocery Item

### Endpoint

```text
POST /api/admin/grocery-items
```

### Requirements

Validate:

- name
- description
- price
- stock
- active state

Use Form Request validation where appropriate.

Do not accept trusted values without validation.

---

## Task 20 — View Grocery Item

### Endpoint

```text
GET /api/admin/grocery-items/{groceryItem}
```

Return the requested item.

Handle missing records correctly.

---

## Task 21 — Update Grocery Item

### Endpoint

```text
PUT/PATCH /api/admin/grocery-items/{groceryItem}
```

Update appropriate product information.

Do not silently overwrite unrelated fields.

---

## Task 22 — Delete Grocery Item

### Endpoint

```text
DELETE /api/admin/grocery-items/{groceryItem}
```

Decide carefully whether deleting an item referenced by historical order items should be allowed.

Prefer preserving historical order integrity.

If a hard delete conflicts with existing order history, use an appropriate strategy such as deactivation/soft deletion, provided it remains consistent with the assignment and database design.

Document the decision.

---

## Task 23 — Stock Management

### Endpoint

```text
PATCH /api/admin/grocery-items/{groceryItem}/stock
```

### Requirements

- Admin only.
- Validate stock.
- Never allow negative stock.
- Keep stock update logic in the appropriate service/repository layer.
- Do not confuse administrative stock updates with order stock deduction.

---

# Phase 6 — User Grocery Browsing

---

## Task 24 — User Grocery Listing

### Endpoint

```text
GET /api/grocery-items
```

### Requirements

Return available/active grocery items.

Do not expose inactive products in the normal user catalogue.

Include useful information:

```text
id
name
description
price
stock/availability
```

Do not expose internal implementation details.

---

## Task 25 — Search/Pagination

Add lightweight functionality if useful:

```text
GET /api/grocery-items?page=1
GET /api/grocery-items?search=apple
```

Keep filtering simple and maintainable.

Do not build unnecessary advanced search infrastructure.

---

# Phase 7 — Order Placement

---

## Task 26 — Define Order Request Contract

Expected request shape:

```json
{
    "items": [
        {
            "grocery_item_id": 1,
            "quantity": 3
        },
        {
            "grocery_item_id": 5,
            "quantity": 2
        }
    ]
}
```

Validate:

- items array exists
- at least one item
- product IDs exist
- quantities are integers
- quantities are greater than zero

Do not accept:

```text
price
unit_price
subtotal
total
```

from the client as trusted business values.

---

## Task 27 — Implement Order Creation Service

Create the order placement use case in `OrderService`.

The service must:

1. Validate/order-process requested items.
2. Retrieve current product information.
3. Verify availability.
4. Calculate unit prices server-side.
5. Calculate item subtotals server-side.
6. Calculate total server-side.
7. Create order.
8. Create order items.
9. Deduct stock.
10. Commit only when the complete operation succeeds.

---

# Phase 8 — Transaction-Safe Inventory ⭐

This is a critical assessment requirement.

---

## Task 28 — Implement Database Transaction

Order placement must execute atomically.

Use a database transaction around:

```text
Order creation
Order item creation
Stock deduction
```

If any operation fails:

```text
Order = rolled back
Order items = rolled back
Stock changes = rolled back
```

---

## Task 29 — Implement Row-Level Locking

Prevent overselling under concurrent requests.

Use an appropriate database locking strategy, such as:

```php
lockForUpdate()
```

inside the transaction when retrieving stock rows.

Conceptually:

```text
Request A
    ↓
BEGIN TRANSACTION
    ↓
Lock product row
    ↓
Read current stock
    ↓
Validate stock
    ↓
Deduct stock
    ↓
Create order
    ↓
COMMIT

Request B
    ↓
Wait for locked row
    ↓
Read latest stock
    ↓
Validate again
    ↓
Continue or fail
```

Do not rely only on application-level checks such as:

```php
if ($product->stock >= $quantity)
```

without concurrency protection.

---

## Task 30 — Handle Insufficient Stock

When requested quantity exceeds available stock:

- do not create the order
- do not partially deduct stock
- return an appropriate business error
- keep database state unchanged

Consider an appropriate HTTP status such as `409 Conflict` for stock conflicts.

---

## Task 31 — Prevent Duplicate Product Lines

Decide how the API should behave if the same grocery item appears multiple times in the request.

Preferred approach:

Normalize/merge duplicate product IDs before processing, or reject duplicate lines explicitly.

Document the decision.

---

# Phase 9 — Order History

---

## Task 32 — User Order History

### Endpoint

```text
GET /api/orders
```

Return only orders belonging to the authenticated user.

Support pagination.

---

## Task 33 — Order Details

### Endpoint

```text
GET /api/orders/{order}
```

Return:

```text
order
 ├── status
 ├── total
 └── items
      ├── grocery item
      ├── quantity
      ├── unit price
      └── subtotal
```

---

## Task 34 — Enforce Ownership

A user must never be able to access another user's order.

Do not trust a user-provided order ID.

Use authenticated-user scoping at the query/service/repository level.

Test:

```text
User A requests User B's order
        ↓
403/404 according to chosen policy
```

Document the chosen behavior.

---

# Phase 10 — API Validation and Error Handling

---

## Task 35 — Form Request Validation

Create dedicated Form Requests for complex API inputs.

Examples:

```text
RegisterRequest
LoginRequest
StoreGroceryItemRequest
UpdateGroceryItemRequest
UpdateStockRequest
StoreOrderRequest
```

Use Laravel validation conventions.

---

## Task 36 — Standardize API Responses

Use a consistent response structure.

Example success:

```json
{
    "success": true,
    "message": "Order placed successfully.",
    "data": {}
}
```

Example error:

```json
{
    "success": false,
    "message": "Insufficient stock."
}
```

Do not unnecessarily wrap every Laravel exception manually.

Keep error handling consistent and maintainable.

---

## Task 37 — HTTP Status Codes

Use appropriate HTTP status codes.

Examples:

```text
200 OK
201 Created
204 No Content
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
```

Do not return `200` for every failure.

---

# Phase 11 — Blade Frontend

The assignment specifically requires the user-facing browsing and booking flow to use **Blade + AJAX**.

---

## Task 38 — Create Blade Layout

Create:

- main layout
- navigation
- authentication-aware UI
- flash/error messages
- reusable page structure

Keep frontend simple and professional.

Do not introduce React/Vue unless explicitly required.

---

## Task 39 — Grocery Catalogue Page

Create a user-facing page:

```text
/grocery-items
```

Display:

```text
Product name
Description
Price
Availability
Quantity selector
Add to Order
```

---

## Task 40 — Client-Side Cart/Order State

Allow users to select multiple products before submitting one order.

Example:

```text
Apple × 2
Banana × 3
Milk × 1
----------------
Total
```

Keep the implementation simple.

The server remains the source of truth.

---

## Task 41 — AJAX Interaction

At least one user interaction must happen without a full page reload.

Recommended:

```text
Add to Order
```

Use AJAX/fetch to update the order/cart interaction.

Alternative acceptable interaction:

```text
Live stock check
```

The implementation must clearly demonstrate AJAX usage.

---

## Task 42 — Order Confirmation

Create a checkout/confirmation section.

Show:

- selected products
- quantities
- unit prices
- subtotals
- total

Submit the final order to the backend.

Never trust the frontend total.

---

## Task 43 — AJAX Error/Success Handling

Display useful feedback for:

- successful order
- validation errors
- insufficient stock
- authentication failures
- unexpected errors

Do not reload the entire page just to show basic order interaction feedback.

---

# Phase 12 — Automated Testing

Testing is an important part of demonstrating engineering quality.

---

## Task 44 — Authentication Tests

Test:

- registration
- default user role
- successful login
- invalid login
- logout
- token refresh
- protected endpoints

---

## Task 45 — Authorization Tests

Test:

```text
Unauthenticated → protected endpoint → rejected

User → admin endpoint → rejected

Admin → admin endpoint → allowed
```

Verify authorization is enforced through middleware/route configuration.

---

## Task 46 — Grocery CRUD Tests

Test:

- create
- list
- show
- update
- delete/deactivate behavior
- validation
- stock update
- negative stock rejection

---

## Task 47 — Order Tests

Test:

```text
single-item order
multiple-item order
invalid product
invalid quantity
inactive product
insufficient stock
correct subtotal
correct total
correct stock deduction
```

---

## Task 48 — Transaction Rollback Test

Force a failure during order creation and verify:

```text
order is not persisted
order items are not persisted
stock is not changed
```

This test is important.

---

## Task 49 — Ownership Tests

Test:

```text
User A → own order → allowed

User A → User B order → rejected
```

---

## Task 50 — Concurrency/Stock Safety Tests

Add the strongest practical automated test for concurrent stock deduction supported by the chosen Laravel/database testing environment.

Scenario:

```text
Initial stock = 5

Request A wants = 4
Request B wants = 4
```

Expected result:

```text
Only one order can consume the available stock
Final stock cannot become negative
Total successfully ordered quantity cannot exceed original stock
```

If the exact concurrency test is difficult to execute reliably in the test environment, document the limitation and supplement it with transaction/locking-focused tests.

---

# Phase 13 — Docker Bonus

Only start this after the core application works.

---

## Task 51 — Dockerize Laravel Application

Create:

```text
Dockerfile
docker-compose.yml
```

---

## Task 52 — Docker Database

Configure MySQL through Docker Compose.

Expected basic services:

```text
app/php
database/mysql
```

Nginx may be added if useful for a production-like setup.

---

## Task 53 — Verify Clean Docker Setup

Verify the application can be started from a clean environment.

Document:

```bash
docker compose up -d
```

and required setup commands.

---

# Phase 14 — Localization Bonus

---

## Task 54 — English Localization

Create user-facing English translation strings.

---

## Task 55 — Bangla Localization

Create Bangla translations.

Use Laravel localization conventions.

---

## Task 56 — Language Switcher

Allow the user to switch between:

```text
English
বাংলা
```

Only user-facing Blade text needs to be localized for this assignment.

---

# Phase 15 — Documentation

---

## Task 57 — API Documentation

Document all endpoints.

For every endpoint include:

```text
Method
URL
Authentication
Role
Request body
Validation
Success response
Error responses
```

---

## Task 58 — Architecture Documentation

README must explain:

### Repository Pattern

Why:

```text
Interface
   ↓
Eloquent Repository
```

was used.

### Service Layer

Explain why business logic is kept outside controllers.

### Middleware

Explain how role-based authorization is enforced.

### Transaction Safety

Explain:

```text
DB transaction
+
row-level locking
+
stock validation
```

and how that prevents overselling.

---

## Task 59 — Setup Documentation

README should contain:

```text
Project overview
Requirements
Installation
Environment configuration
Database setup
Migrations
Seeders
JWT setup
Running the application
Running tests
API endpoints
Frontend usage
Docker setup
Localization
Architecture decisions
```

---

# Phase 16 — Final Code Review

---

## Task 60 — Laravel Code Quality Review

Review the complete project for:

- PSR compliance
- Laravel conventions
- naming
- unnecessary duplication
- unused imports
- dead code
- unnecessary abstractions
- controller complexity
- service responsibilities
- repository responsibilities
- validation
- authorization
- exception handling

---

## Task 61 — Security Review

Check:

- JWT configuration
- authentication
- authorization
- mass assignment protection
- request validation
- ownership checks
- SQL injection safety
- client-controlled prices
- client-controlled totals
- negative quantities
- negative stock
- sensitive data exposure
- `.env` not committed

---

## Task 62 — Database Review

Check:

- foreign keys
- indexes
- decimal money fields
- nullable fields
- default values
- relationships
- cascading behavior
- order history integrity
- transaction boundaries
- locking strategy

---

## Task 63 — API Review

Check:

- endpoint naming
- status codes
- validation responses
- authentication responses
- authorization responses
- pagination
- consistent response structure

---

## Task 64 — Frontend Review

Check:

- Blade structure
- AJAX functionality
- validation feedback
- stock feedback
- order summary
- no unnecessary reload
- responsive/basic usability

---

## Task 65 — Full Test Run

Run the complete automated test suite.

Example:

```bash
php artisan test
```

All tests should pass before submission.

---

# Phase 17 — Submission Preparation

---

## Task 66 — Final README

Finalize README according to the assignment requirements.

It must include:

- setup steps
- endpoint list
- architectural decisions

---

## Task 67 — Git History Cleanup

Make sure commits are meaningful.

Prefer commits such as:

```text
chore: initialize laravel application
feat: add jwt authentication
feat: add role based authorization
feat: implement repository architecture
feat: implement grocery item management
feat: implement inventory management
feat: implement order placement
fix: make stock deduction transaction safe
feat: add order history
feat: add blade grocery booking flow
test: add authentication and authorization tests
test: add order and inventory tests
feat: dockerize application
feat: add bangla localization
docs: finalize project documentation
```

Avoid commits such as:

```text
update
fix
changes
test
asdf
final
final-final
```

---

## Task 68 — Final Submission Verification

Before submission verify:

### Application

- [ ] Application boots
- [ ] Database connects
- [ ] Migrations work
- [ ] Seeders work
- [ ] JWT works
- [ ] Admin works
- [ ] User works
- [ ] Grocery CRUD works
- [ ] Stock management works
- [ ] Multi-item order works
- [ ] Stock deduction is transaction-safe
- [ ] Order history works
- [ ] Blade frontend works
- [ ] AJAX interaction works

### Testing

- [ ] Authentication tests pass
- [ ] Authorization tests pass
- [ ] Grocery tests pass
- [ ] Order tests pass
- [ ] Transaction rollback test passes
- [ ] Ownership tests pass
- [ ] Stock/concurrency tests pass as far as the test environment supports

### Documentation

- [ ] README complete
- [ ] Setup documented
- [ ] API endpoints documented
- [ ] Architecture documented
- [ ] Transaction strategy documented
- [ ] Docker documented if implemented
- [ ] Localization documented if implemented

### GitHub

- [ ] No `.env`
- [ ] No secrets
- [ ] No unnecessary files
- [ ] Meaningful commit history
- [ ] Repository is accessible to the evaluator

---

# 7. Definition of Done

A task is considered complete only when:

1. The implementation satisfies the task requirements.
2. Existing functionality still works.
3. Relevant automated tests exist or have been updated.
4. Relevant manual verification has been performed.
5. No unnecessary code was introduced.
6. Code follows Laravel conventions.
7. Architecture remains consistent with this document.
8. The task is documented if it changes setup or architecture.
9. The changes are ready for the developer's manual commit.

---

# 8. Copilot Rules

When GitHub Copilot is asked to implement a task from this document, follow these rules.

## Rule 1 — Implement only the requested task

If the current instruction says:

```text
Implement Task 18
```

do not implement Tasks 19–68.

You may create minimal supporting code required by Task 18, but do not implement future business functionality.

## Rule 2 — Inspect existing code first

Before changing code:

- inspect the relevant models
- inspect routes
- inspect existing services
- inspect repositories
- inspect migrations
- inspect tests

Do not blindly create duplicate classes.

## Rule 3 — Preserve existing architecture

Do not bypass:

```text
Controller
 ↓
Service
 ↓
Repository Interface
 ↓
Repository
```

for domain operations where this architecture has already been established.

## Rule 4 — Prefer Laravel conventions

Use:

- Form Requests
- Policies where appropriate
- Middleware
- Eloquent relationships
- API Resources where useful
- database transactions
- factories
- feature tests

Do not create custom frameworks inside Laravel.

## Rule 5 — Business logic must be server-side

Never trust frontend calculations.

For orders:

```text
Product price → database
Stock → database
Subtotal → server calculation
Total → server calculation
```

## Rule 6 — Keep controllers thin

Avoid putting large business workflows inside controllers.

Bad:

```text
Controller
 ├── validate
 ├── query products
 ├── check stock
 ├── calculate totals
 ├── create order
 ├── update stock
 └── send response
```

Preferred:

```text
Controller
   ↓
OrderService
   ↓
Repository
```

## Rule 7 — Never weaken concurrency safety

Do not replace transaction + locking logic with a simple application-level stock check.

Stock deduction must remain safe under concurrent requests.

## Rule 8 — Test before moving forward

Do not start the next task if the current task leaves failing tests without documenting the reason.

## Rule 9 — Explain architectural decisions

When making a non-trivial decision, explain:

```text
Decision
Reason
Alternative considered
Why this approach was selected
```

Keep explanations concise.

## Rule 10 — Do not invent requirements

If a requirement is not specified in this document or the original assignment, do not add unnecessary functionality.

If a decision is genuinely ambiguous and materially affects architecture or business behavior, flag it for review instead of silently inventing complex behavior.

---

# 9. Recommended Execution Order

The recommended implementation sequence is:

```text
01  Project initialization

02  JWT configuration
03  Role database structure
04  Registration
05  Login
06  Logout
07  Token refresh
08  Authentication middleware
09  Role middleware

10  Repository contracts
11  Eloquent repositories
12  Repository service provider
13  Service layer

14  Grocery item model/migration
15  Order/order-item model/migration
16  Factories/seeders

17  Admin grocery listing
18  Create grocery item
19  Show grocery item
20  Update grocery item
21  Delete/deactivate grocery item
22  Stock management

23  User grocery listing
24  Search/pagination

25  Order request contract
26  Order service
27  Database transaction
28  Row-level locking
29  Insufficient stock handling
30  Duplicate product handling

31  Order history
32  Order details
33  Order ownership

34  Form Requests
35  API responses
36  HTTP status/error handling

37  Blade layout
38  Grocery catalogue
39  Client-side cart
40  AJAX interaction
41  Order confirmation
42  AJAX feedback

43  Authentication tests
44  Authorization tests
45  Grocery tests
46  Order tests
47  Rollback test
48  Ownership tests
49  Concurrency/stock tests

50  Docker
51  Docker database
52  Docker verification

53  English localization
54  Bangla localization
55  Language switcher

56  API documentation
57  Architecture documentation
58  Setup documentation

59  Code quality review
60  Security review
61  Database review
62  API review
63  Frontend review
64  Full test run

65  Final README
66  Final submission verification
```

After every completed task, the developer manually commits the changes using the professional commit-message convention defined in Section 2.1.

---

# 10. Final Instruction to GitHub Copilot

Treat this document as the **project-level implementation specification**.

Do not attempt to build the entire system from this document in one response.

The developer will explicitly provide the task number to implement.

For example:

```text
Implement Task 18 from DEVELOPMENT_PLAN.md.
```

When that happens:

1. Read the current project state.
2. Read this development plan.
3. Identify the exact requirements for that task.
4. Inspect existing related implementation.
5. Implement only that task.
6. Add/update tests.
7. Run relevant checks.
8. Provide one recommended short professional Git commit message for the completed task.
9. Do not create or perform the Git commit unless explicitly requested.
10. Do not move automatically to the next task.

The goal is a clean, maintainable, production-quality Laravel application that demonstrates strong understanding of:

- Laravel architecture
- REST APIs
- JWT authentication
- middleware-based authorization
- Repository Pattern
- Service Layer
- Eloquent
- relational database design
- transactions
- row-level locking
- concurrency
- inventory management
- automated testing
- Blade
- AJAX
- Docker
- localization
- technical documentation
