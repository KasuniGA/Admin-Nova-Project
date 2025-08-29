<?php

namespace App\Providers;


use App\Nova\Brand;
use App\Models\User;
use App\Nova\Dashboards\Main;
use Blade;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Features;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Acme\ProductStatus\ProductStatus;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        Nova::withoutGlobalSearch();

        $this->getFooterContent();
        $this->getCustomMenu();
    }

    /**
     * Register the configurations for Laravel Fortify.
     */
    protected function fortify(): void
    {
        Nova::fortify()
            ->features([
                Features::updatePasswords(),
                // Features::emailVerification(),
                // Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
            ])
            ->register();
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes(default: true)
            ->withPasswordResetRoutes()
            ->withoutEmailVerificationRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewNova', function (User $user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Tool>
     */
    public function tools(): array
    {
        return [
        //     (new PriceTracker)->canSee(function ($request) {
        //     return true;
        // }),
            new ProductStatus, 
            ];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        //
    }

    private function getFooterContent(): void
    {
        Nova::footer(function ($request) {
            return Blade::render('nova/footer');
        });
    }

    private function getCustomMenu()
    {
        Nova::mainMenu(function ($request) {
            return [
                 MenuSection::dashboard(Main::class)
                    ->icon('chart-bar')
                    ->withBadge('New', 'success'),

                MenuSection::make('Products', [
                    MenuItem::make('All Products', '/resources/products'),
                    MenuItem::make('Create Product', '/resources/products/new'),
                ])->icon('shopping-bag')->collapsable(),

                Menusection::resource(Brand::class)->icon('tag'),

                MenuSection::make('Users', [
                    MenuItem::make('All Users', '/resources/users'),
                    MenuItem::make('Create User', '/resources/users/new')
                        ->canSee(function(NovaRequest $request) {
                            return $request->user()->is_admin;
                        }),
                ])->icon('users')->collapsable(),

                MenuSection::make('Product Status')
                ->path('/product-status')
                ->icon('server'),

                // MenuItem::externalLink('Visit Site', 'https://nova.laravel.com/docs')->openInNewTab()


            ];
        });
    }
}
