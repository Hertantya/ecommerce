<?php
/**
 * ════════════════════════════════════════════════════════════
 *  HOW TO REGISTER THE CUSTOM MIDDLEWARE
 *  File: app/Http/Kernel.php
 * ════════════════════════════════════════════════════════════
 *
 * Open your Laravel project's  app/Http/Kernel.php
 * Find the   $middlewareAliases  array  (Laravel 10)
 * or         $routeMiddleware    array  (older Laravel)
 * and add these two lines inside it:
 *
 *   'admin'    => \App\Http\Middleware\AdminMiddleware::class,
 *   'customer' => \App\Http\Middleware\CustomerMiddleware::class,
 *
 * Example — the array should look like this after editing:
 * ─────────────────────────────────────────────────────────────
 *
 *  protected $middlewareAliases = [
 *      'auth'             => \App\Http\Middleware\Authenticate::class,
 *      'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
 *      'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
 *      'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
 *      'can'              => \Illuminate\Auth\Middleware\Authorize::class,
 *      'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
 *      'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
 *      'precognitive'     => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
 *      'signed'           => \App\Http\Middleware\ValidateSignature::class,
 *      'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
 *      'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
 *
 *      // ↓↓ ADD THESE TWO LINES ↓↓
 *      'admin'    => \App\Http\Middleware\AdminMiddleware::class,
 *      'customer' => \App\Http\Middleware\CustomerMiddleware::class,
 *  ];
 *
 * ─────────────────────────────────────────────────────────────
 *  FOR LARAVEL 11  (uses bootstrap/app.php instead of Kernel.php)
 * ─────────────────────────────────────────────────────────────
 *  Open  bootstrap/app.php  and change the withMiddleware section:
 *
 *  ->withMiddleware(function (Middleware $middleware) {
 *      $middleware->alias([
 *          'admin'    => \App\Http\Middleware\AdminMiddleware::class,
 *          'customer' => \App\Http\Middleware\CustomerMiddleware::class,
 *      ]);
 *  })
 *
 */
