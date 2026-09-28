<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\Cart\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One cart per request (also under Octane / queues).
        $this->app->scoped(CartService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // @money($cents) -> "1,299.99 PLN"
        Blade::directive('money', fn (string $expression) => "<?php echo e(\\App\\Support\\Money::format({$expression})); ?>");

        // Shared data for layouts and partials: cart item count and category menu.
        View::composer(['layouts.*', 'partials.*'], function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('navigationCategories', Cache::remember(
                'navigation-categories',
                now()->addHour(),
                fn () => Category::query()->active()->roots()->ordered()
                    ->with(['children' => fn ($q) => $q->active()])
                    ->get(),
            ));
        });

        Category::saved(fn () => Cache::forget('navigation-categories'));
        Category::deleted(fn () => Cache::forget('navigation-categories'));
    }
}
