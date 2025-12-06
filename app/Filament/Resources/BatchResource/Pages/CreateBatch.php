<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use Domains\Broiler\Actions\CreateBatchAction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBatch extends CreateRecord
{
    protected static string $resource = BatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Auth::user()->current_team_id;

        // Auto-generate batch number if not provided
        if (empty($data['batch_number'])) {
            $action = app(CreateBatchAction::class);
            $data['batch_number'] = $action->generateBatchNumber($data['team_id']);
        }

        // Set initial current_quantity to initial_quantity
        $data['current_quantity'] = $data['initial_quantity'];

        return $data;
    }
}
