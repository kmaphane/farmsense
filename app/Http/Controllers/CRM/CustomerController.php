<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Domains\CRM\DTOs\CustomerData;
use Domains\CRM\Models\Customer;
use Domains\CRM\Resources\CustomerFormDataResource;
use Domains\Shared\Enums\CustomerType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Get form data for Quick Actions sheet (JSON API).
     */
    public function data(): CustomerFormDataResource
    {
        $customerTypes = collect(CustomerType::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ])->toArray();

        return new CustomerFormDataResource([
            'customerTypes' => $customerTypes,
        ]);
    }

    /**
     * Store a newly created customer record.
     */
    public function store(CustomerData $data): JsonResponse
    {
        $customer = Customer::create([
            'team_id' => Auth::user()->current_team_id,
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'type' => $data->type,
            'credit_limit' => $data->credit_limit,
            'payment_terms' => $data->payment_terms,
            'notes' => $data->notes,
        ]);

        return response()->json([
            'message' => 'Customer created successfully',
            'customer_id' => $customer->id,
        ], 201);
    }
}
