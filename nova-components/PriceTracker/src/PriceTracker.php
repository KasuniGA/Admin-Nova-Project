<?php

namespace Acme\PriceTracker;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class PriceTracker extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot(): void
    {
        Nova::mix('price-tracker', __DIR__.'/../dist/mix-manifest.json');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     */
    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Price Tracker')
            ->path('/price-tracker')
            ->icon('server')
            ->canSee(function ($request) {
            // Add more sophisticated authorization logic here
            return $request->user()->can('viewPriceTracker');
        });
    }
}
