# Laravel Route Management Best Practices

## Introduction

This document outlines recommended practices for managing routes in this Laravel application to prevent conflicts and ensure proper authentication flows.

## Route Organization

### 1. Keep related routes together

-   **Authentication routes** should be defined in `routes/auth_extended.php`
-   **Admin panel routes** should be defined in `routes/admin.php`
-   **Data management routes** should be defined in `routes/data.php`
-   **Subject/exam routes** should be defined in `routes/naskah.php` and `routes/ujian.php`
-   **Supervisor routes** should be defined in `routes/pengawas.php` and `routes/guru_pengawas.php`

### 2. Use consistent naming conventions

-   Use the format `{resource}.{action}` for route names
-   Use plurals for resource collections (`siswa.index`) and singular for individual resources (`siswa.show`)
-   Avoid duplicate route names across different route files

### 3. Use route groups with prefixes and middleware

```php
// Example
Route::middleware(['auth:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    // Other routes...
});
```

## Authentication Best Practices

### 1. Separate guards for different user types

-   Use the `web` guard for admin users
-   Use the `siswa` guard for student users
-   Use the `guru` guard for teacher users

### 2. Always check middleware on protected routes

-   Ensure all protected routes have the correct guard middleware
-   Use `auth:siswa` for student routes
-   Use `auth:web` for admin and guru routes

### 3. Proper redirect handling

-   After login, redirect to a route that uses the same guard
-   Example: After siswa login, redirect to `route('siswa.dashboard')` which uses `auth:siswa` middleware

## Preventing Route Conflicts

### 1. Avoid defining routes with the same name in different files

-   Check existing route names before adding new ones
-   Use `php artisan route:list` to see all registered routes

### 2. Use unique prefixes for different user types

-   `/admin/*` for administrators
-   `/siswa/*` for students
-   `/guru/*` for teachers

### 3. Clear route cache after changes

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Testing Routes

### 1. Verify route existence

```php
$this->assertTrue(Route::has('siswa.dashboard'));
```

### 2. Test authentication/authorization

```php
$response = $this->get(route('siswa.dashboard'));
$response->assertRedirect(route('login.siswa'));

$this->actingAs($siswa, 'siswa');
$response = $this->get(route('siswa.dashboard'));
$response->assertStatus(200);
```

### 3. Test route conflicts

Run `php artisan route:list | grep route-name` to check for duplicate route names.

## Common Issues and Solutions

1. **Infinite redirect loops**: Usually caused by redirecting to a route that redirects back to the login page. Check authentication middleware on destination routes.

2. **404 errors after login**: Route may be defined but controller method is missing or has a different name than expected.

3. **403 Forbidden errors**: User may be authenticated but with the wrong guard, or missing required permissions.

By following these guidelines, you can maintain a clean, organized routing structure that prevents conflicts and ensures proper authentication flows.
