<?php

namespace App\Nova;

use App\Nova\Actions\ManageProductStatus;
use App\Nova\Filters\ProductBrand;
use App\Nova\Lenses\LowStockProducts;
use App\Nova\Lenses\OutOfStockProducts;
use App\Nova\Metrics\AveragePrice;
use App\Nova\Metrics\NewProducts;
use App\Nova\Metrics\ProductsPerDay;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Product>
     */
    public static $model = \App\Models\Product::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public function subtitle(){
        return "Brand:{$this->brand->name}";
    }

    public static $globalSearchResults = 10;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id','name','description','sku'
    ];

    public static $perPageOptions = [10, 25, 50, 100];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Slug::make('Slug')
                ->from('name')
                ->required()
                ->withMeta(['extraAttributes' => [
                'readonly' => true
            ]])->hideFromIndex(),

            Text::make('Name')
                ->required()
                ->showOnPreview()
                ->placeholder('Product name...')
                ->textAlign('left')
                ->sortable(),

            Markdown::make('Description')
                ->required()
                ->showOnPreview(),

            Currency::make('Price')
                ->currency('LKR')
                ->required()
                ->showOnPreview()
                ->placeholder('Enter product price...')
                ->textAlign('left')
                ->sortable(),

            Text::make('Sku')
                ->required()
                ->placeholder('Enter product SKU...')
                ->help('Number that retailers use to differentiate products and track inventory levels.')
            ->sortable(),

            Number::make('Quantity')
                ->required()
                ->showOnPreview()
                ->placeholder('Enter Quantity...')
                ->textAlign('left')
                ->sortable(),

            Boolean::make('Status', 'is_published')
                ->required()
                ->textAlign('left')
                ->sortable(),

            BelongsTo::make('Brand')
                ->sortable()
                ->showOnPreview()
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            new NewProducts(),
            new AveragePrice(),
            new ProductsPerDay()
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new ProductBrand()
            
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [
            new LowStockProducts,
            new OutOfStockProducts,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
             new ManageProductStatus,
        ];
    }
}
