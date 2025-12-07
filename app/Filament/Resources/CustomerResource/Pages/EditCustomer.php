<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Domains\CRM\DTOs\CustomerData;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Mutate form data before saving the record using DTO
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $dto = CustomerData::fromFilament($data);

        return $dto->toArray();
    }
}
