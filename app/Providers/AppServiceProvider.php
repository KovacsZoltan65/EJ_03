<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Schema::defaultStringLength(191);

        Inertia::share([
            'errors' => function () {
                return Session::get('errors')
                    ? Session::get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
        ]);

        Inertia::share('flash', function () {
            return [
                'message' => Session::get('message'),
            ];
        });

        Inertia::share('csrf_token', function () {
            return csrf_token();
        });

        /**
         * Observe the Book model with the BookObserver class.
         *
         * This allows us to hook into various events of the Book model and
         * perform actions accordingly.
         *
         * @see \App\Observers\BookObserver
         */
        \App\Models\Book::observe(\App\Observers\BookObserver::class);
    }
}
