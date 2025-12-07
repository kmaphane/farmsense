<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Domains\Finance\DTOs\ExpenseData;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert amount to cents before creating DTO
        $data['amount'] = (int) ($data['amount'] * 100);

        $dto = ExpenseData::fromFilament($data);

        return $dto->toArray();
    }
}
