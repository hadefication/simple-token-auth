<?php

namespace Hadefication\SimpleTokenAuth;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Hadefication\SimpleTokenAuth\Commands\GenerateTokenCommand;
use Hadefication\SimpleTokenAuth\Commands\TokenInfoCommand;
use Hadefication\SimpleTokenAuth\Http\Middleware\SimpleTokenAuthMiddleware;

class SimpleTokenAuthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('simple-token-auth')
            ->hasConfigFile()
            ->hasCommand(GenerateTokenCommand::class)
            ->hasCommand(TokenInfoCommand::class);
    }

    public function packageRegistered()
    {
        $this->app->singleton(SimpleTokenAuth::class, function ($app) {
            return new SimpleTokenAuth($app['config'], $app['log']);
        });
    }

    public function packageBooted()
    {
        $this->app['router']->aliasMiddleware('simple-token-auth', SimpleTokenAuthMiddleware::class);
    }
}
