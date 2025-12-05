<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Domains\CRM\DTOs\CustomerData;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    /**
     * Mutate form data before creating the record using DTO
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $dto = CustomerData::fromFilament($data);

        return $dto->toArray();
    }
}
