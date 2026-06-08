# Bribooks Publishing Platform — API Documentation

**Base URL**

```
http://localhost:8000/api
```

All requests must include the following header:

```
Accept: application/json
```

Most endpoints require a JWT token. Pass it as a Bearer token in the Authorization header:

```
Authorization: Bearer {your_token}
```

---

## Authentication

### Register

`POST /auth/register`

Only authors can self-register. Admin and Reviewer accounts are created via database seeders.

**Request Body**

```json
{
    "name": "Author",
    "email": "author@test.com",
    "password": "123456",
    "password_confirmation": "123456",
    "role": "author"
}
```

---

### Login

`POST /auth/login`

All roles (author, reviewer, admin) use this endpoint to log in.

**Request Body**

```json
{
    "email": "author@test.com",
    "password": "123456"
}
```

**Response** includes a JWT token. Copy the `token` from the response and use it for all subsequent requests.

---

### Profile

`GET /auth/profile`

Returns the authenticated user's profile information.

**Headers**

```
Authorization: Bearer {token}
```

---

### Logout

`GET /auth/logout`

Invalidates the current JWT token.

**Headers**

```
Authorization: Bearer {token}
```

---

## Books

All book endpoints require authentication.

### Create Book

`POST /books`

Creates a new book under the authenticated author's account. The book starts in `draft` status.

**Request Body**

```json
{
    "title": "Book B",
    "description": "This book is Book B."
}
```

---

### List Books

`GET /books`

Returns all books accessible to the authenticated user. Authors see their own books; reviewers see submitted books; admins see everything.

---

### Book Details

`GET /books/{id}`

Returns full details for a specific book, including its current status and metadata.

---

### Update Book

`PUT /books/{id}`

Updates the title or description of a book. Only the author who created the book can update it.

**Request Body**

```json
{
    "title": "Bribooks",
    "description": "Updated description Bribooks."
}
```

---

### Delete Book

`DELETE /books/{id}`

Soft deletes a book — the record is kept in the database but marked as deleted. This action is only available to the author.

---

## Chapters

### Create Chapter

`POST /books/{bookId}/chapters`

Adds a new chapter to the specified book. A version snapshot is created automatically.

**Request Body**

```json
{
    "title": "Chapter",
    "sort_order": 1
}
```

---

### List Chapters

`GET /books/{bookId}/chapters`

Returns all chapters for the given book in order.

---

### Update Chapter

`PUT /chapters/{chapterId}`

Updates a chapter's title or sort order. Also triggers a version snapshot.

**Request Body**

```json
{
    "title": "Chapter D updated",
    "sort_order": 1
}
```

---

### Delete Chapter

`DELETE /chapters/{chapterId}`

Removes a chapter from the book. A version snapshot is taken before deletion.

---

## Pages

### Create Page

`POST /chapters/{chapterId}/pages`

Adds a new page inside the specified chapter.

**Request Body**

```json
{
    "title": "Book D Page 1",
    "content": "Book D Page 1"
}
```

---

### List Pages

`GET /chapters/{chapterId}/pages`

Returns all pages for the specified chapter.

---

### Update Page

`PUT /pages/{pageId}`

Updates the title or content of a page.

**Request Body**

```json
{
    "title": "Introduction",
    "content": "modern web applications."
}
```

---

### Delete Page

`DELETE /pages/{pageId}`

Deletes a specific page from its chapter.

---

## Versioning

The system automatically creates a version snapshot whenever a book, chapter, or page is modified, or when a book is submitted or a document is uploaded. You can also create snapshots manually and restore the book to any previous state.

### Create Version

`POST /books/{bookId}/versions`

Manually creates a version snapshot of the current state of the book.

---

### List Versions

`GET /books/{bookId}/versions`

Returns a list of all saved versions for the given book, ordered from newest to oldest.

---

### View Version

`GET /books/{bookId}/versions/{versionId}`

Returns the full snapshot for a specific version, including all chapters and pages as they were at that point in time.

---

### Restore Version

`POST /books/{bookId}/versions/{versionId}/restore`

Restores the book to the state captured in the specified version. This is useful if changes need to be rolled back. A new version snapshot is automatically created before the restore happens, so the current state is always preserved.

---

## Publishing Workflow

Books move through a defined set of statuses: `draft → submitted → under_review → approved → published` (or `rejected`). Each step below advances or changes that status.

### Submit Book

`POST /books/{bookId}/submit`

**Role: Author**

Submits the book for review. Before submission, the system runs content moderation checks (restricted words and profanity). If the book passes, its status changes to `submitted` and it becomes visible to reviewers.

---

### Start Review

`POST /books/{bookId}/review`

**Role: Reviewer**

Marks the book as being actively reviewed. Status changes to `under_review`.

---

### Approve Book

`POST /books/{bookId}/approve`

**Role: Reviewer**

Approves the book after review. Status changes to `approved`, making it available for admin to publish.

---

### Reject Book

`POST /books/{bookId}/reject`

**Role: Reviewer**

Rejects the book. Status changes to `rejected` and the book is returned to the author.

---

### Publish Book

`POST /books/{bookId}/publish`

**Role: Admin**

Publishes an approved book. Status changes to `published`. Only books in `approved` status can be published.

---

## Document Upload

### Upload Manuscript

`POST /books/{id}/upload`

Uploads a `.doc` or `.docx` manuscript file. The document is processed in the background queue, which automatically converts its content into chapters and pages. A version snapshot is also created after processing completes.

**Content-Type:** `multipart/form-data`

**Form Data**

```
file = sample-book.docx
```

Since processing runs in the background, the API responds immediately. The book will be updated once the queue job finishes — make sure your queue worker is running (`php artisan queue:work`).

---

## Dashboard

### Stats

`GET /dashboard`

Returns a summary of all book counts, broken down by status. Useful for getting a quick overview of the platform activity.

**Response includes:**

- Total books
- Draft books
- Submitted books
- Under review books
- Approved books
- Published books
- Rejected books

---

## Response Format

All API responses follow a consistent format.

**Success**

```json
{
    "code": 200,
    "message": "Success",
    "data": {}
}
```

**Validation Error**

```json
{
    "code": 422,
    "message": "Validation Error"
}
```

**Unauthorized**

```json
{
    "code": 401,
    "message": "Unauthorized"
}
```

---

## Notes

- Tokens expire after some time. If you get a 401, log in again to get a fresh token.
- All write operations on books (update, chapter add/remove, page changes) automatically create a version snapshot — you don't need to call the version endpoint manually unless you want a named checkpoint.
- The document upload endpoint queues a background job. If the book content doesn't appear right away, give it a few seconds and refresh.
- Soft-deleted books won't show up in list responses but are still in the database.
