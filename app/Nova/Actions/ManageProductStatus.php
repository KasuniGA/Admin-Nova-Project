<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class ManageProductStatus extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     */
    public $name = 'Manage Status';

    /**
     * Indicates if this action is only available on the resource index.
     */
    public $onlyOnIndex = true;

    /**
     * Indicates if this action is available on the resource detail view.
     */
    public $showOnDetail = false;

    /**
     * Indicates if this action is available on the resource's table row.
     */
    public $showOnTableRow = false;

    /**
     * The text to be used for the action's confirm button.
     */
    public $confirmButtonText = 'Update Status';

    /**
     * The text to be used for the action's confirmation text.
     */
    public $confirmText = 'Are you sure you want to update the status of the selected products?';

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $status = $fields->status === 'active';

        foreach ($models as $model) {
            $model->update([
                'is_published' => $status
            ]);
        }

        return Action::message('Successfully updated ' . $models->count() . ' product(s) status!');
    }


    /**
     * Get the fields available on the action.
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make('Status')
                ->options([
                    'active' => 'Published (Active)',
                    'inactive' => 'Unpublished (Inactive)',
                ])
                ->rules('required')
                ->help('Select the status to apply to all selected products'),
        ];
    }
}