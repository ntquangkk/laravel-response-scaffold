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

Using in Controller:

```php
use App\Support\Responses\ApiResponse;

class UserController
{
    public function show($id)
    {
        $user = User::find($id);

        if (! $user) {
            return ApiResponse::error(
                message: 'User not found',
                statusCode: 404,
            );
        }

        return ApiResponse::success(
            message: 'User retrieved successfully',
            data: $user,
        );
    }
}
```
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