# todo.philipnewborough.co.uk

A personal todo-list web application built with [CodeIgniter 4](https://codeigniter.com/) (PHP 8.2+).

## Overview

This application provides a single-user (or multi-user) todo list with a clean Bootstrap-based UI. Users can create, edit, categorise, pin, complete, soft-delete, and restore todo items. Item content is written in Markdown and rendered to HTML on save.

Authentication is delegated to an external auth service. On every request a filter validates the user's cookies against that service and populates the session with user data (UUID, username, email, admin status, etc.).

## Features

- **Todo items** — create and edit items using Markdown; HTML is stored alongside the raw Markdown for fast rendering
- **Categories** — optionally tag items with a category; a category filter and datalist autocomplete are provided in the UI
- **Status tabs** — separate views for active (TODO), completed, and soft-deleted items
- **Pin items** — pin important items to the top of the TODO list
- **Search** — full-text search across item content and categories
- **Soft deletes** — deleted items are recoverable; a separate destroy action permanently removes them
- **Admin panel** — server-side DataTables view of all items across all users with bulk-delete support
- **REST API** — JSON API consumed by the front-end (vanilla JS); supports pagination, filtering by status/category/search, and all CRUD operations
- **External auth** — session hydrated via a remote auth server using cookie-based tokens

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | CodeIgniter 4.7 |
| Language | PHP 8.2+ |
| ORM / DB | CodeIgniter Model + MySQL/MariaDB |
| UUIDs | `ramsey/uuid` |
| Admin tables | `hermawan/codeigniter4-datatables` |
| Front-end | Bootstrap 5, Bootstrap Icons, vanilla JS |
| Linting | ESLint |
| Testing | PHPUnit 10 |

## Project Structure

```
app/
  Config/          — Application configuration (routes, filters, URLs, etc.)
  Controllers/
    Admin/         — Admin panel
    Api/           — JSON API (TodoItems, Test)
    CLI/           — Spark CLI commands
    Debug/         — Debug helpers
    Auth.php       — Logout handler
    Home.php       — Main todo UI
  Database/
    Migrations/    — Database schema migrations
    Seeds/         — Database seeders
  Filters/         — Auth, API, Admin, and optional-auth request filters
  Libraries/       — Markdown renderer, Notification, Sendmail helpers
  Models/
    TodoItemModel  — todo_items table (soft deletes, timestamps, UUIDs)
  Views/           — PHP/HTML templates (Bootstrap layout)
public/            — Web root (index.php, static assets)
tests/             — PHPUnit test suite
```

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- MySQL or MariaDB

### Install

```bash
composer install
```

Copy the environment file and configure it:

```bash
cp env .env
# edit .env — set database credentials, base URL, and external service URLs
```

Run database migrations:

```bash
php spark migrate
```

Serve locally:

```bash
php spark serve
```

### Running Tests

```bash
composer test
```

## License

MIT — see [LICENSE](LICENSE).

