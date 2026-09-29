<?php

namespace App\Providers;

use App\Models\User;
use App\Support\WaLink;
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
        View::composer('components.ui.admin-contact', function (\Illuminate\View\View $view): void {
            $admin = User::where('role', 'admin')->orderBy('id')->first();
            $url = $admin ? WaLink::url($admin->no_hp, 'Halo Admin, saya memerlukan bantuan terkait jurnalkita.') : null;
            $url ??= $admin?->email ? 'mailto:'.$admin->email : null;
            $view->with('adminContactUrl', $url);
        });
    }
}
