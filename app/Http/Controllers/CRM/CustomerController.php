<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Domains\CRM\DTOs\CustomerData;
use Domains\CRM\Models\Customer;
use Domains\CRM\Resources\CustomerFormDataResource;
use Domains\Shared\Enums\CustomerType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $customers = Customer::query()
            ->where('team_id', $teamId)
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers->through(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => $customer->type->value,
                'type_label' => $customer->type->label(),
                'credit_limit' => $customer->credit_limit,
                'payment_terms' => $customer->payment_terms,
            ]),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): Response
    {
        // Authorization: ensure user can only view their team's records
        abort_if($customer->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        return Inertia::render('Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => $customer->type->value,
                'type_label' => $customer->type->label(),
                'credit_limit' => $customer->credit_limit,
                'payment_terms' => $customer->payment_terms,
                'notes' => $customer->notes,
                'created_at' => $customer->created_at->format('M d, Y'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        // Authorization: ensure user can only edit their team's records
        abort_if($customer->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        $customerTypes = collect(CustomerType::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ])->toArray();

        return Inertia::render('Customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => $customer->type->value,
                'credit_limit' => $customer->credit_limit,
                'payment_terms' => $customer->payment_terms,
                'notes' => $customer->notes,
            ],
            'customerTypes' => $customerTypes,
        ]);
    }

    /**
     * Update the specified customer.
     */
    public function update(CustomerData $data, Customer $customer): RedirectResponse
    {
        // Authorization: ensure user can only update their team's records
        abort_if($customer->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        $customer->update([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'type' => $data->type,
            'credit_limit' => $data->credit_limit,
            'payment_terms' => $data->payment_terms,
            'notes' => $data->notes,
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

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
