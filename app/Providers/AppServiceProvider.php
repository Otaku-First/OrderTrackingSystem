<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


/**
 * @method static string|null login(array $credentials)
 * @method static void logout()
 * @method static string refresh()
 * @method static \App\Models\User register(array $data)
 * @method static \App\Models\User|null getAuthenticatedUser()
 * @method static array respondWithToken(string $token)
 */
class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
