# Feature Specification: Core Package Implementation

## Overview
This specification covers the core implementation of the `simple-token-auth` package. It includes the main service class, middleware, service provider, and command-line tools as defined in the PRD. The goal is to create a lightweight, secure, and easy-to-use authentication package for server-to-server communication in Laravel applications.

## Requirements

### 1. Token Validation System
- Implement hash-safe comparison using `hash_equals()` to prevent timing attacks.
- Support multiple named tokens for different services.
- Allow for a fallback token for backward compatibility.
- Identify services through their named tokens.

### 2. Middleware
- Extract bearer tokens from the `Authorization` header.
- Support `X-API-Token` as an alternative header.
- Inject a service context into request attributes upon successful authentication.
- Return standardized JSON error responses with correct HTTP status codes (401).

### 3. Configuration
- Create a `simple-token-auth.php` configuration file.
- Allow storing tokens in `.env` and referencing them in the config.
- Include settings for rate limiting (enabled, max attempts, lockout duration).
- Add a boolean flag for logging requests.

### 4. Security
- Implement rate limiting to protect against brute-force attacks.
- Log failed authentication attempts, including IP address and endpoint.
- Mask tokens in all logs and debug outputs.
- Use Laravel's built-in rate limiter (`Illuminate\Cache\RateLimiter`) for tracking brute-force attempts.

### 5. Developer Tools
- Create a `simple-token:generate` command to create secure tokens.
- The command should allow specifying a service name and format the output for `.env`.
- Add a `--save` flag to automatically append the generated token to the `.env` file.
- Provide additional output instructions for updating the config file after token generation.
- Create a `simple-token:info` command to inspect the current configuration and loaded tokens.

## Implementation Notes

### File Structure
```
src/
├── SimpleTokenAuth.php              # Core validation logic
├── SimpleTokenAuthServiceProvider.php # Package service provider
├── Middleware/
│   └── SimpleTokenAuthMiddleware.php  # Authentication middleware
└── Commands/
    ├── GenerateTokenCommand.php     # php artisan simple-token:generate
    └── TokenInfoCommand.php         # php artisan simple-token:info
config/
└── simple-token-auth.php            # Configuration file
```

### Key Class Implementations
- **`SimpleTokenAuth`**: Will contain the static `validateToken()` method. This class will handle the core logic of fetching tokens from the configuration and performing the `hash_equals` comparison.
- **`SimpleTokenAuthMiddleware`**: Will handle the request logic, extracting the token, calling `SimpleTokenAuth::validateToken()`, and handling success/failure responses. It will also be responsible for rate limiting checks.
- **`SimpleTokenAuthServiceProvider`**: Will register the package's configuration, commands, and middleware. It will use `spatie/laravel-package-tools`.
- **`GenerateTokenCommand`**: Will use `random_bytes()` to generate a cryptographically secure token. Will support `--save` flag to automatically append to `.env` file and provide config update instructions.
- **`TokenInfoCommand`**: Will read the configuration and provide a summary of the setup.

### Security Implementation Details
- **Token Generation**: Use `bin2hex(random_bytes($length / 2))` for secure tokens.
- **Rate Limiting**: Use Laravel's built-in `RateLimiter` service. The rate limit key should be a hash of the IP address to avoid exposing it directly while still tracking attempts per IP.
- **Token Comparison**: Ensure `hash_equals()` is used for all token comparisons to mitigate timing attacks.

### Enhanced Token Generation Command Details
- **`--save` Flag**: Automatically append the generated token to the `.env` file with proper formatting. Always appends, even if the variable already exists.
- **Config Instructions**: Provide clear output showing how to update the config file with the new token reference.
- **Example Output**: 
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

## TODO
- [x] Create initial package structure and `composer.json`.
- [x] Implement `SimpleTokenAuthServiceProvider` using `spatie/laravel-package-tools`.
- [x] Create the `config/simple-token-auth.php` file.
- [x] Implement the `SimpleTokenAuth` class with the `validateToken` method.
- [x] Implement the `SimpleTokenAuthMiddleware`.
- [x] Implement the `GenerateTokenCommand`.
- [x] Enhance `GenerateTokenCommand` with `--save` flag and config update instructions.
- [x] Implement the `TokenInfoCommand`.
- [x] Write unit tests for token validation and generation.
- [x] Write integration tests for the middleware and commands.
- [x] Write security tests for timing attack resistance and rate limiting.
- [x] Update `README.md` with installation and usage instructions.
- [x] Mark specification as complete.
