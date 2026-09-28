<?php

namespace App\Providers;

use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @money($amount) -> "1,299.99 PLN"
        Blade::directive('money', fn (string $expression) => "<?php echo number_format((float) ({$expression}), 2, '.', ',').' PLN'; ?>");

        // Number of items in the cart, available in every layout.
        View::composer('layouts.*', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });
    }
}
