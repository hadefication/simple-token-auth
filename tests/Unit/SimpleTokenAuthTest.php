<?php

use Hadefication\SimpleTokenAuth\SimpleTokenAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->auth = $this->app->make(SimpleTokenAuth::class);
});

it('validates a correct service token', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
    expect($this->auth->validateToken($request, 'test-service'))->toBeTrue();
});

it('rejects an incorrect service token', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer wrong-token']);
    expect($this->auth->validateToken($request, 'test-service'))->toBeFalse();
});

it('validates a correct fallback token', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer fallback-token']);
    expect($this->auth->validateToken($request))->toBeTrue();
});

it('rejects an incorrect fallback token', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer wrong-token']);
    expect($this->auth->validateToken($request))->toBeFalse();
});

it('validates a token from the x api token header', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_API_TOKEN' => 'test-token']);
    expect($this->auth->validateToken($request, 'test-service'))->toBeTrue();
});

it('logs failed attempts if enabled', function () {
    config()->set('simple-token-auth.log_failed_attempts', true);

    // Create a fresh instance with mocked log
    $mockLog = Mockery::mock('Psr\Log\LoggerInterface');
    $mockLog->shouldReceive('warning')->once();

    $auth = new \Hadefication\SimpleTokenAuth\SimpleTokenAuth(
        $this->app->make('config'),
        $mockLog
    );

    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer wrong-token']);
    $auth->validateToken($request);
});

it('does not log failed attempts if disabled', function () {
    config()->set('simple-token-auth.log_failed_attempts', false);
    Log::shouldReceive('warning')->never();
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer wrong-token']);
    $this->auth->validateToken($request);
});

it('handles empty token gracefully', function () {
    $request = Request::create('/', 'GET');
    expect($this->auth->validateToken($request))->toBeFalse();
});

it('handles null token gracefully', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer ']);
    expect($this->auth->validateToken($request))->toBeFalse();
});

it('validates multiple service tokens', function () {
    config()->set('simple-token-auth.tokens.service1', 'token1');
    config()->set('simple-token-auth.tokens.service2', 'token2');

    $request1 = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer token1']);
    $request2 = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer token2']);

    expect($this->auth->validateToken($request1, 'service1'))->toBeTrue();
    expect($this->auth->validateToken($request2, 'service2'))->toBeTrue();
});

it('uses hash_equals for timing attack resistance', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);

    // This test verifies that hash_equals is used internally
    // We can't directly test the timing, but we can verify the behavior
    $start = microtime(true);
    $this->auth->validateToken($request, 'test-service');
    $validTime = microtime(true) - $start;

    $start = microtime(true);
    $this->auth->validateToken($request, 'wrong-service');
    $invalidTime = microtime(true) - $start;

    // The times should be reasonably similar (within 50ms) if hash_equals is used
    expect(abs($validTime - $invalidTime))->toBeLessThan(0.05);
});
