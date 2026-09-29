# Laravel Task Management REST API

A multi-user REST API for managing projects and tasks — built with Laravel, MySQL, and Sanctum token authentication.

## Features

- **User-scoped data** — every user only sees their own projects and tasks. Ownership is enforced at the query level (not just the UI), so one user can never read or modify another user's data, even by guessing an ID.
- **Nested resources** — tasks belong to projects, projects belong to users (`User → Project → Task`)
- **Token authentication** — register/login returns a Sanctum bearer token; all project/task routes require it
- **Status & priority tracking** — tasks have an enum-constrained `status` (pending/in_progress/completed) and `priority` (low/medium/high)

## Tech Stack

- PHP 8.3 / Laravel 12
- MySQL (Docker for local development)
- Laravel Sanctum (token-based API authentication)

## API Endpoints

### Authentication (public)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/register` | Create an account, returns a Sanctum token |
| POST | `/api/login` | Log in, returns a Sanctum token |
| POST | `/api/logout` | Revoke the current token (requires auth) |

### Projects & Tasks (require `Authorization: Bearer <token>`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/projects` | List the authenticated user's projects |
| POST | `/api/projects` | Create a project |
| GET | `/api/projects/{id}` | Get a project with its tasks |
| PUT/PATCH | `/api/projects/{id}` | Update a project |
| DELETE | `/api/projects/{id}` | Delete a project |
| GET | `/api/projects/{id}/tasks` | List tasks for a project |
| POST | `/api/projects/{id}/tasks` | Create a task in a project |
| GET | `/api/tasks/{id}` | Get a single task |
| PUT/PATCH | `/api/tasks/{id}` | Update a task (status, priority, etc.) |
| DELETE | `/api/tasks/{id}` | Delete a task |

## How ownership is enforced

Every project/task query is scoped through the authenticated user rather than querying the model directly:

```php
// Not this — would let any authenticated user fetch any project by ID:
Project::findOrFail($id);

// This — only matches if the project belongs to the current user:
$request->user()->projects()->findOrFail($id);
```

Tasks are similarly scoped through their parent project's ownership (`whereHas('project', ...)`), since a task doesn't have its own `user_id` column.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# configure DB_* in .env, then:
php artisan migrate
php artisan install:api   # sets up Sanctum
php artisan serve
```
