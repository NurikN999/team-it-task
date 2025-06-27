# Task Management API Documentation

## Overview

This API provides endpoints for user authentication and task management with JWT token authentication.

## Base URL

```
http://localhost:8000
```

## Authentication

All protected endpoints require a JWT token in the Authorization header:

```
Authorization: Bearer <your_jwt_token>
```

## Endpoints

### Authentication

#### Register User
```http
POST /api/v1/auth/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (201):**
```json
{
    "message": "User registered successfully",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "user": {
            "id": 1,
            "email": "john@example.com",
            "first_name": "John",
            "last_name": "Doe"
        }
    }
}
```

#### Login User
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "user": {
            "id": 1,
            "email": "john@example.com",
            "first_name": "John",
            "last_name": "Doe"
        }
    }
}
```

### Tasks

#### Get Paginated Tasks
```http
GET /api/v1/tasks?page=1&limit=10&status=pending&sort_by=createdAt&sort_order=DESC
Authorization: Bearer <your_jwt_token>
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page, max 100 (default: 10)
- `status` (optional): Filter by status: `pending`, `in_progress`, `completed`
- `sort_by` (optional): Sort field: `createdAt`, `updatedAt`, `title`, `status` (default: `createdAt`)
- `sort_order` (optional): Sort order: `ASC`, `DESC` (default: `DESC`)

**Response (200):**
```json
{
    "message": "Tasks fetched successfully",
    "data": [
        {
            "id": 1,
            "title": "Complete project documentation",
            "description": "Write comprehensive documentation for the API",
            "status": "pending",
            "created_at": "2024-01-01 10:00:00",
            "updated_at": "2024-01-01 10:00:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "total_pages": 3,
        "has_next_page": true,
        "has_previous_page": false
    },
    "filters": {
        "status": "pending",
        "sort_by": "createdAt",
        "sort_order": "DESC"
    },
    "status_counts": {
        "pending": 10,
        "in_progress": 5,
        "completed": 10,
        "total": 25
    }
}
```

#### Create Task
```http
POST /api/v1/tasks
Authorization: Bearer <your_jwt_token>
Content-Type: application/json

{
    "title": "Complete project documentation",
    "description": "Write comprehensive documentation for the API",
    "status": "pending"
}
```

**Response (201):**
```json
{
    "message": "Task created successfully",
    "data": {
        "id": 1,
        "title": "Complete project documentation",
        "description": "Write comprehensive documentation for the API",
        "status": "pending",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:00:00"
    }
}
```

#### Get Task by ID
```http
GET /api/v1/tasks/1
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
    "message": "Task fetched successfully",
    "data": {
        "id": 1,
        "title": "Complete project documentation",
        "description": "Write comprehensive documentation for the API",
        "status": "pending",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:00:00"
    }
}
```

#### Update Task
```http
PUT /api/v1/tasks/1
Authorization: Bearer <your_jwt_token>
Content-Type: application/json

{
    "title": "Updated task title",
    "description": "Updated task description",
    "status": "in_progress"
}
```

**Response (200):**
```json
{
    "message": "Task updated successfully",
    "data": {
        "id": 1,
        "title": "Updated task title",
        "description": "Updated task description",
        "status": "in_progress",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 11:00:00"
    }
}
```

#### Delete Task
```http
DELETE /api/v1/tasks/1
Authorization: Bearer <your_jwt_token>
```

**Response (204):**
```json
{
    "message": "Task deleted successfully"
}
```

## Error Responses

### 400 Bad Request
```json
{
    "message": "Invalid data",
    "title": "This value should not be blank.",
    "status": "The value you selected is not a valid choice."
}
```

### 401 Unauthorized
```json
{
    "message": "JWT Token not found"
}
```

### 404 Not Found
```json
{
    "message": "Task not found"
}
```

## Interactive Documentation

You can access the interactive API documentation at:
```
http://localhost:8000/api/docs
```

This provides a Swagger UI interface where you can:
- View all available endpoints
- Test API calls directly from the browser
- See request/response schemas
- Authenticate with your JWT token

## Task Status Values

- `pending`: Task is waiting to be started
- `in_progress`: Task is currently being worked on
- `completed`: Task has been finished

## Examples

### Using cURL

**Register a user:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Login and get token:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Create a task (replace TOKEN with your JWT token):**
```bash
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "title": "Complete project documentation",
    "description": "Write comprehensive documentation for the API",
    "status": "pending"
  }'
```

**Get paginated tasks:**
```bash
curl -X GET "http://localhost:8000/api/v1/tasks?page=1&limit=10&status=pending" \
  -H "Authorization: Bearer TOKEN"
``` 