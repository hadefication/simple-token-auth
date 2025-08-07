<?php

namespace Hadefication\SimpleTokenAuth\Tests;

use Hadefication\SimpleTokenAuth\SimpleTokenAuthServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Hadefication\\SimpleTokenAuth\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SimpleTokenAuthServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('simple-token-auth.tokens.test-service', 'test-token');
        config()->set('simple-token-auth.fallback_token', 'fallback-token');
    }
}
