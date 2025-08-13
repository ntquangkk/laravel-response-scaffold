# Laravel Model Doc Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/triquang/laravel-response-scaffold.svg?style=flat-square)](https://packagist.org/packages/triquang/laravel-response-scaffold)
[![Total Downloads](https://img.shields.io/packagist/dt/triquang/laravel-response-scaffold.svg?style=flat-square)](https://packagist.org/packages/triquang/laravel-response-scaffold)
[![License](https://img.shields.io/packagist/l/triquang/laravel-response-scaffold.svg?style=flat-square)](https://github.com/ntquangkk/laravel-response-scaffold?tab=MIT-1-ov-file)

A Laravel Artisan command to quickly generate an API Response class, standardize response formats, and configure exception handling and guest redirects for APIs.

---

## 🚀 Features

- Create basic `routes/api.php` file if not exists.
- Create standardized `app/Support/Responses/ApiResponse.php` file if not exists.
- Configure **Global Exception Handler** in `bootstrap/app.php` to handle API errors:
  - `AuthenticationException` → 401 Unauthenticated
  - `AuthorizationException` → 403 Unauthorized
  - `ModelNotFoundException` → 404 Resource not found
  - `NotFoundHttpException` → 404 Endpoint not found
  - `ValidationException` → 422 Validation failed
  - Other errors → 500 Server Error (or return detailed message if `APP_DEBUG=true`)
- Prevent Laravel from automatically redirecting unlogged API to web login page.
- Works well on Laravel 11+ with configurations:
  - ->withRouting()
  - ->withExceptions()
  - ->withMiddleware()

---

## 📦 Installation

Install via Composer (recommended to use --dev as this is a development tool):

```bash
composer require triquang/laravel-response-scaffold --dev
```

---

## ⚙️ Usage

Run the Artisan command:

```bash
php artisan make:response-scaffold
```

This command will:
1. Create `routes/api.php` if it does not exist.
2. Create `app/Support/Responses/ApiResponse.php` if it does not exist.
3. Add `Api route` configuration to `bootstrap/app.php`.
4. Add `redirectGuestsTo` configuration to `bootstrap/app.php`.
5. Add `Global Exception Handler` configuration to `bootstrap/app.php`.

---

## 📂 Output structure

After running, you will have:

```php
app/
└── Support/
    └── Responses/
        └── ApiResponse.php
bootstrap/
└── app.php
routes/
└── api.php

```
---

## 💡 Examples

> How to use:
1. Shorthand with parameter order
2. Named parameters with arbitrary order (PHP 8+)

```php
use App\Support\Responses\ApiResponse;

class Example
{
    public function shorthands()
    {
        // Just data
        ApiResponse::success($users);

        // With custom message
        ApiResponse::success($users, 'Users loaded');

        // With custom status code
        ApiResponse::success($user, 'User created', 201);

        // Full parameters
        ApiResponse::success($users, 'Users found', 200, ['page' => 3]);

        // Error cases
        ApiResponse::error('Server error');
        ApiResponse::error('User not found', [], 404);
        ApiResponse::error('Validation failed', $validator->errors(), 422);

        // With exception (debug)
        try {
            // some code
        } catch (Exception $e) {
            return ApiResponse::error('Something went wrong', [], 500, [], $e);
        }
    }

    public function namedParameters()
    {
        // Full parameters, in arbitrary order
        ApiResponse::success(
           data: $users, 
           message: 'Users found', 
           statusCode: 200, 
           meta: ['page' => 3]
        );

        // Error case
        ApiResponse::error(
            message: 'Something went wrong',
            errors: $validator->errors(),
            statusCode: 422,
            meta: ['s' => 2],
            exception: $e   // <- in try-catch {}, if needed
        );
    }
}
```
💡 Tip: Named parameters make your code more readable and avoid mistakes when skipping optional arguments.

When there is an error like `NotFoundHttpException`, the API will return:

```json
{
  "status": "error",
  "message": "Endpoint not found",
  "errors": [],
  "meta": {
    "url": "http://127.0.0.1:8000/api/not-exist"
  }
}
```

---

## ✅ Requirements

- PHP >= 8.0
- Laravel 11 / 12
- Composer

---

## 📄 License

MIT © [Nguyễn Trí Quang](mailto:ntquangkk@gmail.com)

---

## 🙌 Contributing

PRs are welcome! Feel free to improve functionality or report issues via GitHub Issues.

---

## 📬 Contact

- GitHub: [github.com/ntquangkk](https://github.com/ntquangkk)
- Email: [ntquangkk@gmail.com](mailto:ntquangkk@gmail.com)