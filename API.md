# Grocery Booking System — API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

All protected endpoints require a JWT token in the `Authorization` header:

```
Authorization: Bearer <your-jwt-token>
```

### Obtaining a Token

Send a `POST` request to `/api/auth/login` with valid credentials. The response includes an `access_token` that must be used for subsequent protected requests.

### Token Refresh

Send a `POST` request to `/api/auth/refresh` with a valid token to get a new token.

---

## Endpoints

### Authentication

#### POST /api/auth/register

Register a new customer account.

**Request:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
}
```

**Response (201):**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "customer"
    }
}
```

**Validation Rules:**
- `name`: required, string, max 255 chars
- `email`: required, valid email, unique, max 255 chars
- `password`: required, min 8 chars, must contain uppercase letter and number, confirmed

---

#### POST /api/auth/login

Login and receive a JWT token.

**Request:**
```json
{
    "email": "john@example.com",
    "password": "Password123"
}
```

**Response (200):**
```json
{
    "message": "Authentication successful",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "token_type": "Bearer",
    "expires_in": 3600
}
```

---

#### POST /api/auth/logout

Logout and invalidate the current token. **Requires authentication.**

**Response (200):**
```json
{
    "message": "User logged out successfully"
}
```

---

#### POST /api/auth/refresh

Refresh the JWT token. **Requires authentication.**

**Response (200):**
```json
{
    "message": "Authentication successful",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "token_type": "Bearer",
    "expires_in": 3600
}
```

---

#### GET /api/auth/me

Get the authenticated user's details. **Requires authentication.**

**Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "customer"
    }
}
```

---

### Customer Endpoints

#### GET /api/customer/products

Browse available grocery products. **Public endpoint.**

**Query Parameters:**
- `search` (optional): Filter by product name
- `min_price` (optional): Minimum price filter
- `max_price` (optional): Maximum price filter
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Fresh Red Apples",
            "description": "Crisp and sweet red apples",
            "price": "3.99",
            "stock": 50,
            "is_active": true
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "total": 15
    }
}
```

---

#### POST /api/customer/orders

Place a new order. **Requires customer authentication.**

**Rate Limit:** 10 requests per minute.

**Request:**
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 3
        },
        {
            "product_id": 5,
            "quantity": 2
        }
    ]
}
```

**Response (201):**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "status": "pending",
        "total_amount": "25.95",
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "quantity": 3,
                "unit_price": "3.99",
                "subtotal": "11.97"
            }
        ]
    }
}
```

**Validation Rules:**
- `items`: required array, at least 1 item
- `items.*.product_id`: required, integer, must exist in grocery_items
- `items.*.quantity`: required, integer, minimum 1

---

#### GET /api/customer/orders

Get the authenticated user's order history. **Requires customer authentication.**

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "status": "pending",
            "total_amount": "25.95",
            "items": [...]
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "total": 5
    }
}
```

---

#### GET /api/customer/orders/{order}

Get details of a specific order. **Requires customer authentication.** Users can only access their own orders.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "status": "pending",
        "total_amount": "25.95",
        "status_changed_at": null,
        "created_at": "2026-09-01T10:00:00.000000Z",
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "product_name": "Fresh Red Apples",
                "quantity": 3,
                "unit_price": "3.99",
                "subtotal": "11.97"
            }
        ]
    }
}
```

---

#### GET /api/customer/profile

Get the authenticated user's profile. **Requires customer authentication.**

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "customer"
    }
}
```

---

#### PUT /api/customer/profile

Update the authenticated user's profile. **Requires customer authentication.**

**Request:**
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "password": "NewPassword123",
    "password_confirmation": "NewPassword123"
}
```

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "John Updated",
        "email": "john.updated@example.com",
        "role": "customer"
    }
}
```

---

### Admin Endpoints

All admin endpoints require admin authentication.

#### GET /api/admin/grocery-items

List all grocery items with pagination. **Requires admin authentication.**

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Response (200):**
```json
{
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 2,
        "total": 20
    }
}
```

---

#### POST /api/admin/grocery-items

Create a new grocery item. **Requires admin authentication.**

**Request:**
```json
{
    "name": "Fresh Oranges",
    "description": "Juicy oranges from Florida",
    "price": 4.99,
    "stock": 50,
    "is_active": true
}
```

**Response (201):**
```json
{
    "data": {
        "id": 16,
        "name": "Fresh Oranges",
        "description": "Juicy oranges from Florida",
        "price": "4.99",
        "stock": 50,
        "is_active": true
    }
}
```

**Validation Rules:**
- `name`: required, string, max 255 chars
- `description`: optional, string, max 1000 chars
- `price`: required, numeric, > 0, max 2 decimal places
- `stock`: required, integer, min 0
- `is_active`: optional, boolean

---

#### GET /api/admin/grocery-items/{groceryItem}

Get a specific grocery item. **Requires admin authentication.**

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Fresh Red Apples",
        "description": "Crisp and sweet red apples",
        "price": "3.99",
        "stock": 50,
        "is_active": true
    }
}
```

---

#### PUT /api/admin/grocery-items/{groceryItem}

Update a grocery item. **Requires admin authentication.**

**Request:**
```json
{
    "name": "Premium Red Apples",
    "price": "5.99",
    "stock": 75
}
```

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Premium Red Apples",
        "price": "5.99",
        "stock": 75,
        "is_active": true
    }
}
```

---

#### PATCH /api/admin/grocery-items/{groceryItem}/stock

Update a grocery item's stock. **Requires admin authentication.**

**Request:**
```json
{
    "stock": 100
}
```

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Fresh Red Apples",
        "stock": 100
    }
}
```

---

#### DELETE /api/admin/grocery-items/{groceryItem}

Soft-delete a grocery item. **Requires admin authentication.**

**Response (200):**
```json
{
    "message": "Grocery item deleted successfully"
}
```

---

#### GET /api/admin/orders

List all orders with pagination. **Requires admin authentication.**

**Query Parameters:**
- `per_page` (optional): Items per page (default: 10)
- `page` (optional): Page number

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "status": "pending",
            "total_amount": "25.95",
            "user": {
                "email": "john@example.com"
            },
            "items": [...]
        }
    ],
    "pagination": {
        "total": 10,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

#### PUT /api/admin/orders/{order}/status

Change an order's status. **Requires admin authentication.**

**Request:**
```json
{
    "status": "confirmed"
}
```

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "status": "confirmed",
        "status_changed_at": "2026-09-01T11:00:00.000000Z"
    }
}
```

**Valid Status Values:**
- `pending`
- `confirmed`
- `delivered`
- `cancelled`

**Business Rules:**
- Cannot change status of delivered orders (except to cancelled)
- Cannot change status of cancelled orders

---

#### GET /api/admin/analytics/revenue

Get revenue analytics. **Requires admin authentication.**

**Query Parameters:**
- `start_date` (optional): Filter by start date (YYYY-MM-DD)
- `end_date` (optional): Filter by end date (YYYY-MM-DD)

**Response (200):**
```json
{
    "data": {
        "total_revenue": 1250.50,
        "order_count": 45,
        "average_order_value": "27.79",
        "date_range": {
            "start": "2026-01-01",
            "end": "2026-12-31"
        }
    }
}
```

---

#### GET /api/admin/analytics/top-products

Get top products by order count. **Requires admin authentication.**

**Query Parameters:**
- `limit` (optional): Number of products to return (default: 10)
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date

**Response (200):**
```json
{
    "data": [
        {
            "product_id": 1,
            "product_name": "Fresh Red Apples",
            "order_count": 25,
            "total_quantity": 150,
            "total_revenue": "598.50"
        }
    ]
}
```

---

#### GET /api/admin/analytics/order-count

Get order count by time period. **Requires admin authentication.**

**Query Parameters:**
- `period` (optional): `daily`, `weekly`, or `monthly` (default: daily)
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date

**Response (200):**
```json
{
    "data": [
        {
            "period": "2026-09-01",
            "order_count": 5,
            "revenue": "125.50"
        }
    ]
}
```

---

## Error Responses

All error responses follow a consistent format:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {}
}
```

The `errors` field is only present for validation errors (422).

### Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | OK — Request successful |
| 201 | Created — Resource created successfully |
| 401 | Unauthorized — Missing or invalid token |
| 403 | Forbidden — Insufficient permissions |
| 404 | Not Found — Resource not found |
| 422 | Unprocessable Entity — Validation failed |
| 429 | Too Many Requests — Rate limit exceeded |
| 500 | Internal Server Error — Server error |

### Common Error Examples

**401 Unauthorized:**
```json
{
    "success": false,
    "message": "Unauthenticated. Please provide a valid token."
}
```

**403 Forbidden:**
```json
{
    "success": false,
    "message": "Forbidden. You do not have permission to access this resource."
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

**429 Rate Limit:**
```json
{
    "success": false,
    "message": "Too many requests. Please try again later."
}
```

---

## Rate Limiting

| Endpoint | Limit |
|----------|-------|
| POST /api/auth/register | 5 requests/minute |
| POST /api/auth/login | 5 requests/minute |
| POST /api/customer/orders | 10 requests/minute |

Rate limit headers are included in responses:
- `Retry-After`: Seconds until the rate limit resets
