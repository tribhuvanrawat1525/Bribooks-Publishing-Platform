# Bribooks Publishing Platform API

This is a REST API built with Laravel 13 for managing the full lifecycle of books — from writing and versioning to moderation and publishing. It covers everything from chapter and page management to document uploads and role-based workflows.

---

## Tech Stack

- PHP 8.3
- Laravel 13
- MySQL
- JWT Authentication
- Laravel Queue (Database Driver)
- PHPWord (DOC/DOCX Processing)

---

## Features

### Authentication

Basic auth flow with JWT tokens. Covers registration, login, profile access, and logout.

### Role Based Access Control

There are three roles in the system — Admin, Author, and Reviewer. Each role has a specific set of permissions:

**Author** can create books, edit their own books, and view their own books.

**Reviewer** can view books that have been submitted and review them.

**Admin** is the only one who can publish books.

### Book Management

Authors can create, update, and delete books (soft delete is used so nothing is permanently lost). The API also supports listing all books and fetching individual book details.

### Chapter Management

Chapters can be created, updated, and deleted within a book.

### Page Management

Pages can be created, updated, and deleted within chapters.

### Book Versioning

A new version is automatically created every time something significant changes. This includes:

- Book updated
- Chapter created, updated, or deleted
- Page created, updated, or deleted
- Book submitted
- Document uploaded

Every version stores a full snapshot of the book along with its chapters and pages, so nothing is ever lost.

### Publishing Workflow

Books move through the following statuses:

```
draft → submitted → under_review → approved → published → rejected
```

The flow works like this: the Author submits the book, a Reviewer reviews it and either approves or rejects it, and then the Admin publishes it.

### Content Moderation

When a book is submitted, it automatically goes through a moderation check. This includes:

- Restricted words detection
- Profanity detection

The result of the moderation check is stored in the database.

### Document Upload

Authors can upload manuscript files in `.doc` or `.docx` format. When a document is uploaded, the system automatically:

- Converts the document content into pages
- Creates a chapter for the content
- Generates individual pages from the document
- Takes a version snapshot

### Queue Processing

Document conversion runs in the background using Laravel's queue system. This keeps the API response fast and makes the system more scalable overall. The queue uses the database driver.

### API Logging

Every request hitting the API gets logged. The log captures:

- The user making the request
- HTTP method and URL
- Request and response body
- Status code
- Execution time

### Activity Logging

Key system events are tracked as activity logs. Examples include book creation, submission, publishing, version creation, and document uploads.

### Event Driven Architecture

The system uses Laravel events and listeners to handle side effects cleanly. Events fired include `BookCreated`, `BookSubmitted`, `BookPublished`, and `BookVersionCreated`, all handled by the `BookActivityListener`.

---

## Installation

Clone the repository and navigate into the project folder:

```bash
git clone https://github.com/tribhuvanrawat1525/Bribooks-Publishing-Platform.git
cd Bribooks-Publishing-Platform
```

Install PHP dependencies:

```bash
composer install
```

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Generate the JWT secret:

```bash
php artisan jwt:secret
```

Open the `.env` file and set your database credentials:

```env
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Run the migrations:

```bash
php artisan migrate
```

Run the seeders to create default users and restricted words:

```bash
php artisan db:seed --class=UserSeeder

php artisan db:seed --class=RestrictedWordsSeeder
```

Create the storage symlink:

```bash
php artisan storage:link
```

Start the queue worker (required for document processing):

```bash
php artisan queue:work
```

Start the development server:

```bash
php artisan serve
```

---

## Composer Packages

JWT authentication:

```bash
composer require tymon/jwt-auth
```

DOC/DOCX processing:

```bash
composer require phpoffice/phpword
```

---

## Default Users

These accounts are created automatically when you run the `UserSeeder`.

**Admin**

```
Email:    admin@example.com
Password: password
```

**Author**

```
Email:    author@example.com
Password: password
```

**Reviewer**

```
Email:    reviewer@example.com
Password: password
```

---

## Queue Commands

Start the worker:

```bash
php artisan queue:work
```

View failed jobs:

```bash
php artisan queue:failed
```

Retry all failed jobs:

```bash
php artisan queue:retry all
```

Clear failed jobs:

```bash
php artisan queue:flush
```

---

## API Response Format

Successful response:

```json
{
    "code": 200,
    "message": "Success",
    "data": {}
}
```

Error response:

```json
{
    "code": 422,
    "message": "Validation Error"
}
```

---

## Author

## Note:

1.I also provide the migrated database inside "database/book_publish.sql" if migration fails so you can directly import this database
2.An API Collection in the root folder name as "PHP Assignment.postman_collection.json"

Assignment submission for the Bribooks Publishing Platform.
