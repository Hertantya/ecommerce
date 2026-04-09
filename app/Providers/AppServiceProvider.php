<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /**
         * Custom Blade directive: @active('pattern')
         *
         * Usage in layout:
         *   <a class="nav-link @active('admin/products*')">Products</a>
         *
         * It outputs the string 'active' when the current URL matches the pattern.
         * The '*' wildcard works just like request()->is().
         */
        Blade::directive('active', function ($pattern) {
            return "<?php echo request()->is({$pattern}) ? 'active' : ''; ?>";
        });
    }
}
