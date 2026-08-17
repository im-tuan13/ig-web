<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
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
        View::composer('components.instagram.sidebar', function ($view) {
            $user = Auth::user();

            if ($user) {
                $notifications = $user->notifications()
                    ->with(['actor:id,name,username,avatar', 'post:id,image'])
                    ->latest()
                    ->paginate(20);

                $view->with('notificationsForDrawer', $notifications);
            }
        });
    }
}
