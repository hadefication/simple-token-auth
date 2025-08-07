# simple-token-auth - Project Context

## Overview
A lightweight Laravel package for server-to-server API authentication using static bearer tokens. This package provides a simpler alternative to Laravel Sanctum for internal APIs, microservices, and trusted system integrations. It uses `spatie/laravel-package-tools` as a base.

The problem this package solves is that Laravel Sanctum can be overkill for simple server-to-server communication, and there is a need for simple, performant authentication for internal APIs without complex user management.

## Tech Stack
- PHP, Laravel
- Key Dependency: `spatie/laravel-package-tools`

## Architecture
The package follows a standard Laravel package structure.

### Package Structure
```
src/
├── SimpleTokenAuth.php (core validation logic)
├── Middleware/SimpleTokenAuthMiddleware.php
├── Commands/GenerateTokenCommand.php
├── Commands/TokenInfoCommand.php
└── ServiceProvider.php
config/simple-token-auth.php
```

### Configuration Schema
```php
'tokens' => [
    'service_name' => env('API_TOKEN_SERVICE_NAME'),
    // Multiple named services supported
],
'rate_limiting' => [
    'enabled' => boolean,
    'max_attempts' => integer,
    'lockout_duration' => seconds,
],
'log_requests' => boolean
```

## Key Components

### Core Classes
1.  **SimpleTokenAuth** - Main validation logic with static methods
2.  **SimpleTokenAuthMiddleware** - Request interception and validation
3.  **SimpleTokenAuthServiceProvider** - Laravel service registration
4.  **GenerateTokenCommand** - Token creation utility
5.  **TokenInfoCommand** - Configuration inspection tool

### Key Methods
- `validateToken(string $token, ?string $service): bool`
- `generateSecureToken(int $length): string`
- `getTokenInfo(): array`
- `isTokenRateLimited(string $token): bool`


## Non-Goals
- User session management
- Database-stored tokens
- OAuth implementation
- Frontend authentication
- Complex permission systems