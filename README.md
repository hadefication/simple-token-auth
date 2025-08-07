# Simple Token Auth

A lightweight, secure, and easy-to-use authentication package for server-to-server communication in Laravel applications. This package provides simple token-based authentication with built-in security features like rate limiting, timing attack protection, and comprehensive logging.

## Features

- 🔐 **Secure Token Validation**: Uses `hash_equals()` to prevent timing attacks
- 🚀 **Multiple Service Support**: Named tokens for different services with fallback support
- 🛡️ **Rate Limiting**: Built-in protection against brute-force attacks
- 📝 **Comprehensive Logging**: Failed authentication attempts with IP and endpoint tracking
- 🔧 **Developer Tools**: CLI commands for token generation and configuration inspection
- 🎯 **Flexible Headers**: Supports both `Authorization: Bearer` and `X-API-Token` headers
- 🔒 **Token Masking**: Secure token masking in logs and debug outputs

## Installation

You can install the package via Composer:

```bash
composer require hadefication/simple-token-auth
```

The package will automatically register its service provider and configuration.

## Configuration

### Publishing Configuration

Publish the configuration file to customize the package settings:

```bash
php artisan vendor:publish --provider="Hadefication\SimpleTokenAuth\SimpleTokenAuthServiceProvider"
```

### Environment Variables

Add the following to your `.env` file:

```env
# Fallback token (general purpose)
API_TOKEN=your-fallback-token-here

# Service-specific tokens
API_TOKEN_SERVICE_NAME=your-service-token-here
API_TOKEN_ANOTHER_SERVICE=another-service-token-here

# Rate limiting configuration
API_RATE_LIMITING_ENABLED=true
API_RATE_LIMITING_MAX_ATTEMPTS=60
API_RATE_LIMITING_LOCKOUT_DURATION=60

# Logging configuration
API_LOG_FAILED_ATTEMPTS=true
```

### Configuration File

The `config/simple-token-auth.php` file contains all configuration options:

```php
return [
    'tokens' => [
        'service-name' => env('API_TOKEN_SERVICE_NAME'),
        'another-service' => env('API_TOKEN_ANOTHER_SERVICE'),
    ],

    'fallback_token' => env('API_TOKEN'),

    'rate_limiting' => [
        'enabled' => env('API_RATE_LIMITING_ENABLED', true),
        'max_attempts' => env('API_RATE_LIMITING_MAX_ATTEMPTS', 60),
        'lockout_duration' => env('API_RATE_LIMITING_LOCKOUT_DURATION', 60),
    ],

    'log_failed_attempts' => env('API_LOG_FAILED_ATTEMPTS', true),
];
```

## Usage

### Basic Middleware Usage

Apply the middleware to your routes:

```php
// Using the middleware class directly
Route::middleware(\Hadefication\SimpleTokenAuth\Http\Middleware\SimpleTokenAuthMiddleware::class)
    ->group(function () {
        Route::get('/api/protected', function () {
            return response()->json(['message' => 'Authenticated!']);
        });
    });

// Using the registered middleware alias
Route::middleware('simple-token-auth')
    ->group(function () {
        Route::get('/api/protected', function () {
            return response()->json(['message' => 'Authenticated!']);
        });
    });
```

### Service-Specific Authentication

Authenticate with a specific service token:

```php
Route::middleware('simple-token-auth:service-name')
    ->group(function () {
        Route::get('/api/service-specific', function () {
            return response()->json(['message' => 'Service authenticated!']);
        });
    });
```

### Accessing Service Context

When using service-specific authentication, you can access the authenticated service:

```php
Route::middleware('simple-token-auth:service-name')
    ->get('/api/service-data', function (Request $request) {
        $service = $request->attributes->get('authenticated_service');
        return response()->json(['service' => $service]);
    });
```

### Token Headers

The package supports two header formats:

```bash
# Bearer token (recommended)
Authorization: Bearer your-token-here

# X-API-Token header (alternative)
X-API-Token: your-token-here
```

## Developer Tools

### Generate Tokens

Generate cryptographically secure tokens:

```bash
# Generate a fallback token
php artisan simple-token:generate

# Generate a token for a specific service
php artisan simple-token:generate my-service

# Generate with custom length
php artisan simple-token:generate my-service --length=128

# Generate with .env format output
php artisan simple-token:generate my-service --show-env

# Generate and automatically save to .env file
php artisan simple-token:generate my-service --save
```

Example output:
```
Generated token for service [my-service]:
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6

Add the following to your .env file:
API_TOKEN_MY_SERVICE=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6
```

With `--save` flag:
```
Generated token for service [my-service]:
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6

Token saved to .env file as: API_TOKEN_MY_SERVICE

Next steps:
1. Add the following to your config/simple-token-auth.php file:
   'my-service' => env('API_TOKEN_MY_SERVICE'),

2. Clear config cache: php artisan config:clear

3. Verify configuration: php artisan simple-token:info
```

### Adding Generated Tokens to Configuration

After generating a token, you need to manually add it to your configuration:

#### 1. Add to .env file

Copy the generated token and add it to your `.env` file:

```env
# For service-specific tokens
API_TOKEN_MY_SERVICE=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6

# For fallback tokens
API_TOKEN=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6
```

#### 2. Update config file (optional)

If you want to reference the token in your `config/simple-token-auth.php` file:

```php
return [
    'tokens' => [
        'my-service' => env('API_TOKEN_MY_SERVICE'),
        // Add other service tokens here
    ],
    
    'fallback_token' => env('API_TOKEN'),
    // ... rest of configuration
];
```

#### 3. Clear configuration cache

After updating the configuration, clear the cache:

```bash
php artisan config:clear
```

#### 4. Verify the token

Use the info command to verify your token is properly configured:

```bash
php artisan simple-token:info
```

### Inspect Configuration

View your current token configuration:

```bash
php artisan simple-token:info
```

Example output:
```
Simple Token Auth Configuration:

Tokens:
  - Service: my-service, Token: my-s********oken
  - Service: another-service, Token: ano-********ther
  - Fallback Token: fall********back

Rate Limiting:
  - Enabled: Yes
  - Max Attempts: 60
  - Lockout Duration: 60 seconds

Logging:
  - Log Failed Attempts: Yes
```

## Security Features

### Timing Attack Protection

All token comparisons use `hash_equals()` to prevent timing attacks, ensuring that comparing valid and invalid tokens takes the same amount of time.

### Rate Limiting

The package implements rate limiting to protect against brute-force attacks:

- **Configurable Limits**: Set maximum attempts and lockout duration
- **IP-based Tracking**: Uses hashed IP addresses to protect privacy
- **Automatic Reset**: Rate limits are cleared on successful authentication

### Token Masking

Tokens are automatically masked in logs and debug outputs:

- **Log Security**: Failed authentication attempts log IP and endpoint without exposing tokens
- **Debug Safety**: Configuration inspection shows masked tokens only

### Comprehensive Logging

Failed authentication attempts are logged with:

- IP address of the requester
- Full URL that was accessed
- Timestamp of the attempt

## Error Responses

The package returns standardized JSON error responses:

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Too Many Requests (429)
```json
{
    "message": "Too Many Attempts."
}
```

## Testing

The package includes comprehensive tests covering:

- Token validation and generation
- Middleware functionality
- Rate limiting behavior
- Security features (timing attacks, token masking)
- Command-line tools

Run the test suite:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@hadefication.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
