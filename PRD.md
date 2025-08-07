# PRD: Simple Token Authentication Package for Laravel

## Product Overview

A lightweight Laravel package for server-to-server API authentication using static bearer tokens. This package provides a simpler alternative to Laravel Sanctum for internal APIs, microservices, and trusted system integrations. Uses spatie/laravel-package-tools package as base.

## Problem Statement

- Laravel Sanctum is overkill for server-to-server communication
- Need simple, performant authentication for internal APIs
- Current solutions require complex user management for system-to-system calls
- Token management should be straightforward but secure

## Target Audience

- Laravel developers building internal APIs
- DevOps teams managing microservices
- Organizations with server-to-server integrations
- Teams needing simple webhook authentication

## Success Metrics

- Zero-configuration authentication setup
- Sub-10ms authentication overhead
- Support for multiple service tokens
- Comprehensive audit logging capabilities

## Core Features

### 1. Token Validation System
- **Hash-safe comparison** using `hash_equals()` to prevent timing attacks
- **Multiple token support** for different services and rotation
- **Fallback token system** for backward compatibility
- **Service identification** through named tokens

### 2. Middleware Implementation
- **Bearer token extraction** from `Authorization` header
- **Alternative header support** via `X-API-Token`
- **Service context injection** into request attributes
- **Standardized error responses** with proper HTTP status codes

### 3. Configuration Management
- **Named token configuration** for service identification
- **Environment-based token storage** with multiple options
- **Rate limiting configuration** for brute force protection
- **Logging preferences** for audit requirements

### 4. Security Features
- **Rate limiting** with configurable lockout periods
- **Failed attempt logging** with IP and endpoint tracking
- **Token masking** in logs and debug output
- **Brute force protection** using cache-based attempt tracking

### 5. Developer Tools
- **Token generation command** with customizable length
- **Service-specific token creation** with environment formatting
- **Configuration inspection** via info command
- **Token rotation support** with dual-token validation

## Technical Specifications

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

### Middleware Usage Patterns
```php
// Basic usage
Route::middleware('simple-token-auth')

// Service-specific
Route::middleware('simple-token-auth:service_name')
```

### Command Interface
```bash
php artisan simple-token:generate [service] [--length=64] [--env]
php artisan simple-token:info
```

## Implementation Requirements

### Core Classes Needed
1. **SimpleTokenAuth** - Main validation logic with static methods
2. **SimpleTokenAuthMiddleware** - Request interception and validation
3. **SimpleTokenAuthServiceProvider** - Laravel service registration
4. **GenerateTokenCommand** - Token creation utility
5. **TokenInfoCommand** - Configuration inspection tool

### Key Methods
- `validateToken(string $token, ?string $service): bool`
- `generateSecureToken(int $length): string`
- `getTokenInfo(): array`
- `isTokenRateLimited(string $token): bool`

### Security Considerations
- Use `random_bytes()` for token generation
- Implement `hash_equals()` for constant-time comparison
- Cache-based rate limiting with SHA-256 hashed keys
- Comprehensive audit logging with IP tracking
- No token exposure in logs or error messages

### Error Handling
- Standardized JSON error responses
- Appropriate HTTP status codes (401 for auth failures)
- Detailed logging without token exposure
- Graceful fallback for missing configuration

## Testing Strategy

### Unit Tests Required
- Token validation logic
- Hash comparison safety
- Rate limiting functionality
- Token generation randomness
- Configuration parsing

### Integration Tests Required
- Middleware flow testing
- Multiple token scenarios
- Service-specific authentication
- Command execution verification
- Laravel service integration

### Security Tests
- Timing attack resistance
- Rate limiting effectiveness
- Token brute force scenarios
- Log data sanitization

## Documentation Requirements

### README Sections
1. Installation instructions
2. Basic usage examples
3. Advanced configuration
4. Security best practices
5. Command reference
6. Troubleshooting guide

### Code Documentation
- PHPDoc for all public methods
- Configuration file comments
- Inline security explanations
- Usage examples in docblocks

## Deployment Considerations

### Composer Package
- PSR-4 autoloading
- Laravel package discovery
- Semantic versioning
- Dependency management

### Environment Setup
- Clear .env variable naming
- Configuration publishing
- Service provider registration
- Middleware alias registration

## Future Enhancements

### Phase 2 Features
- Token expiration support
- IP whitelisting integration
- Webhook signature validation
- Token usage analytics
- Admin dashboard integration

### Potential Integrations
- Laravel Telescope compatibility
- Sentry error tracking
- External secret management
- Prometheus metrics export

## Non-Goals

- User session management
- Database-stored tokens
- OAuth implementation
- Frontend authentication
- Complex permission systems