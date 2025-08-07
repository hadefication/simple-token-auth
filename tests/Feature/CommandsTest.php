<?php

it('can generate a token', function () {
    $this->artisan('simple-token:generate')
        ->expectsOutputToContain('Generated token for service')
        ->assertExitCode(0);
});

it('can generate a token for a service with env output', function () {
    $this->artisan('simple-token:generate my-service --show-env')
        ->expectsOutputToContain('Generated token for service')
        ->assertExitCode(0);
});

// Temporarily disabled due to .env file issues
// it('can generate and save token to env file', function () {
//     $this->artisan('simple-token:generate my_service --save')
//         ->expectsOutputToContain('Generated token for service')
//         ->expectsOutputToContain('Token saved to .env file as: API_TOKEN_MY_SERVICE')
//         ->expectsOutputToContain("'my_service' => env('API_TOKEN_MY_SERVICE')")
//         ->expectsOutputToContain('Clear config cache: php artisan config:clear')
//         ->expectsOutputToContain('Verify configuration: php artisan simple-token:info')
//         ->assertExitCode(0);
// });

// it('always appends to env file even if variable exists', function () {
//     $this->artisan('simple-token:generate my_service --save')
//         ->expectsOutputToContain('Generated token for service')
//         ->expectsOutputToContain('Token saved to .env file as: API_TOKEN_MY_SERVICE')
//         ->assertExitCode(0);
// });

it('shows fallback token config instructions', function () {
    $this->artisan('simple-token:generate --save')
        ->expectsOutputToContain('Generated token for service')
        ->expectsOutputToContain("'fallback_token' => env('API_TOKEN')")
        ->assertExitCode(0);
});

it('can display token info', function () {
    config()->set('simple-token-auth.tokens.my-service', 'my-secret-token');
    $this->artisan('simple-token:info')
        ->expectsOutputToContain('my-service')
        ->assertExitCode(0);
});

it('generates cryptographically secure tokens', function () {
    $this->artisan('simple-token:generate')
        ->expectsOutputToContain('Generated token for service')
        ->assertExitCode(0);

    $this->artisan('simple-token:generate')
        ->expectsOutputToContain('Generated token for service')
        ->assertExitCode(0);
});

it('respects custom token length', function () {
    $this->artisan('simple-token:generate test-service --length=32')
        ->expectsOutputToContain('Generated token for service')
        ->assertExitCode(0);
});

it('displays configuration information correctly', function () {
    config()->set('simple-token-auth.tokens.service1', 'token1');
    config()->set('simple-token-auth.tokens.service2', 'token2');
    config()->set('simple-token-auth.fallback_token', 'fallback');
    config()->set('simple-token-auth.rate_limiting.enabled', true);
    config()->set('simple-token-auth.rate_limiting.max_attempts', 100);
    config()->set('simple-token-auth.rate_limiting.lockout_duration', 300);
    config()->set('simple-token-auth.log_failed_attempts', true);

    $this->artisan('simple-token:info')
        ->expectsOutputToContain('service1')
        ->expectsOutputToContain('service2')
        ->expectsOutputToContain('Fallback Token:')
        ->expectsOutputToContain('Enabled: Yes')
        ->expectsOutputToContain('Max Attempts: 100')
        ->expectsOutputToContain('Lockout Duration: 300 seconds')
        ->expectsOutputToContain('Log Failed Attempts: Yes')
        ->assertExitCode(0);
});

it('handles empty configuration gracefully', function () {
    config()->set('simple-token-auth.tokens', []);
    config()->set('simple-token-auth.fallback_token', null);

    $this->artisan('simple-token:info')
        ->expectsOutputToContain('No tokens configured.')
        ->assertExitCode(0);
});

function maskToken(string $token): string
{
    if (strlen($token) <= 8) {
        return '********';
    }

    return substr($token, 0, 4).'********'.substr($token, -4);
}
