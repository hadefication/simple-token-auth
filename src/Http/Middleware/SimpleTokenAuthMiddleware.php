<?php

namespace Hadefication\SimpleTokenAuth\Http\Middleware;

use Closure;
use Hadefication\SimpleTokenAuth\SimpleTokenAuth;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleTokenAuthMiddleware
{
    protected $auth;
    protected $limiter;

    public function __construct(SimpleTokenAuth $auth, RateLimiter $limiter)
    {
        $this->auth = $auth;
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, ?string $service = null): Response
    {
        if ($this->isRateLimited($request)) {
            return response()->json(['message' => 'Too Many Attempts.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (!$this->auth->validateToken($request, $service)) {
            $this->limiter->hit($this->getRateLimitKey($request));
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $this->limiter->clear($this->getRateLimitKey($request));

        if ($service) {
            $request->attributes->add(['authenticated_service' => $service]);
        }

        return $next($request);
    }

    protected function isRateLimited(Request $request): bool
    {
        if (!$this->getRateLimitingConfig('enabled')) {
            return false;
        }

        $key = $this->getRateLimitKey($request);
        $maxAttempts = $this->getRateLimitingConfig('max_attempts');

        return $this->limiter->tooManyAttempts($key, $maxAttempts);
    }

    protected function getRateLimitKey(Request $request): string
    {
        return 'simple-token-auth|' . sha1($request->ip());
    }

    protected function getRateLimitingConfig(string $key)
    {
        return config("simple-token-auth.rate_limiting.{$key}");
    }
}
