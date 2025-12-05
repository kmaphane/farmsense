<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Domains\Finance\DTOs\ExpenseData;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert amount to cents before creating DTO
        $data['amount'] = (int) ($data['amount'] * 100);

        $dto = ExpenseData::fromFilament($data);

        return $dto->toArray();
    }
}
