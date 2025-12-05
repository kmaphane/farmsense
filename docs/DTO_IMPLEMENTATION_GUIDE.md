# DTO Implementation Guide

## Overview

This guide explains how to use Spatie Laravel Data DTOs in the Farmsense application to replace array-based data handling with type-safe data transfer objects.

## Package Installation

**REQUIRED FIRST STEP:**

```bash
composer require spatie/laravel-data
```

After installation, publish the config (optional):
```bash
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="config"
```

## Architecture

### Directory Structure

```
Domains/
├── Shared/
│   └── DTOs/
│       └── BaseData.php           # Base DTO with team_id helpers
├── Auth/
│   └── DTOs/
│       └── ProfileUpdateData.php  # Profile update DTO
├── CRM/
│   └── DTOs/
│       ├── CustomerData.php       # Customer create/update DTO
│       └── SupplierData.php       # Supplier create/update DTO
├── Finance/
│   └── DTOs/
│       ├── ExpenseData.php        # Expense DTO
│       ├── InvoiceData.php        # Invoice DTO
│       └── PaymentData.php        # Payment DTO
└── Inventory/
    └── DTOs/
        ├── ProductData.php        # Product DTO
        ├── WarehouseData.php      # Warehouse DTO
        └── StockMovementData.php  # Stock movement DTO
```

## Base DTO Class

All DTOs extend `Domains\Shared\DTOs\BaseData`:

```php
<?php

namespace Domains\Shared\DTOs;

use Spatie\LaravelData\Data;

abstract class BaseData extends Data
{
    /**
     * Get the team_id from the current authenticated user
     */
    protected static function getCurrentTeamId(): ?int
    {
        return auth()->user()?->current_team_id;
    }

    /**
     * Merge team_id into the payload if not already present
     */
    public function withTeamId(): static
    {
        if (property_exists($this, 'team_id') && ! $this->team_id) {
            $this->team_id = static::getCurrentTeamId();
        }

        return $this;
    }
}
```

## DTO Features

### 1. Type Safety

DTOs provide full type hints for all properties:

```php
public function __construct(
    #[Required]
    #[Max(255)]
    public string $name,

    #[Nullable]
    #[Email]
    public ?string $email,
)
```

### 2. Built-in Validation

Use Spatie's validation attributes:

```php
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\In;
```

### 3. Automatic Transformation

DTOs can be created from arrays and transformed back:

```php
// From array
$dto = CustomerData::from($request->all());

// To array
$array = $dto->toArray();

// To model attributes
$customer = Customer::create($dto->all());
```

## Usage Patterns

### Pattern 1: Filament Resources

Use the `fromFilament()` static method in Create/Edit pages:

**CreateCustomer.php:**
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    $dto = CustomerData::fromFilament($data);
    return $dto->toArray();
}
```

**EditCustomer.php:**
```php
protected function mutateFormDataBeforeSave(array $data): array
{
    $dto = CustomerData::fromFilament($data);
    return $dto->toArray();
}
```

### Pattern 2: Controllers (React Frontend)

Use DTOs in controllers with Inertia:

```php
use Domains\CRM\DTOs\CustomerData;

public function store(Request $request)
{
    // Validate and create DTO
    $dto = CustomerData::validateAndCreate($request->all());

    // Or manually validate
    $dto = CustomerData::from($request->validate(
        CustomerData::rules()
    ));

    // Create model
    $customer = Customer::create($dto->toArray());

    return redirect()->route('customers.index');
}
```

### Pattern 3: API Resources

```php
use Domains\CRM\DTOs\CustomerData;

public function store(Request $request)
{
    $dto = CustomerData::from($request->all());

    $customer = Customer::create($dto->toArray());

    return CustomerData::from($customer);
}
```

### Pattern 4: Services & Actions

```php
class CreateCustomerAction
{
    public function execute(CustomerData $dto): Customer
    {
        // Business logic here
        $customer = Customer::create($dto->toArray());

        // Additional operations
        event(new CustomerCreated($customer));

        return $customer;
    }
}

// Usage
$action = new CreateCustomerAction();
$customer = $action->execute(CustomerData::fromFilament($data));
```

## Available DTOs

### CRM Domain

#### CustomerData
```php
CustomerData::fromFilament([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '1234567890',
    'type' => CustomerType::Wholesale,
    'credit_limit' => 100000, // in cents
    'payment_terms' => 30,
    'notes' => 'VIP customer',
]);
```

#### SupplierData
```php
SupplierData::fromFilament([
    'name' => 'Feed Supplier Ltd',
    'email' => 'sales@feedsupplier.com',
    'category' => SupplierCategory::Feed,
    'performance_rating' => 4.5,
    'current_price_per_unit' => 25.50,
    'is_active' => true,
]);
```

### Finance Domain

#### ExpenseData
```php
ExpenseData::fromFilament([
    'amount' => 50000, // in cents
    'currency' => 'BWP',
    'category' => ExpenseCategory::Feed,
    'description' => 'Monthly feed purchase',
    'allocatable_type' => 'Domains\\Broiler\\Models\\Batch',
    'allocatable_id' => 1,
    'receipt_path' => 'receipts/2025/01/receipt.pdf',
]);
```

#### InvoiceData
```php
InvoiceData::fromFilament([
    'customer_id' => 1,
    'invoice_number' => 'INV-2025-001',
    'status' => 'draft',
    'subtotal' => 100000,
    'tax_amount' => 12000,
    'total_amount' => 112000,
    'due_date' => '2025-02-01',
]);
```

#### PaymentData
```php
PaymentData::fromFilament([
    'invoice_id' => 1,
    'amount' => 112000,
    'payment_method' => 'bank_transfer',
    'reference' => 'TXN123456',
    'payment_date' => '2025-01-15',
]);
```

### Inventory Domain

#### ProductData
```php
ProductData::fromFilament([
    'name' => 'Broiler Starter Feed',
    'description' => 'High protein starter feed',
    'type' => 'feed',
    'unit' => 'kg',
    'quantity_on_hand' => 5000,
    'reorder_level' => 1000,
    'unit_cost' => 2500, // in cents
    'is_active' => true,
]);
```

#### StockMovementData
```php
StockMovementData::fromFilament([
    'product_id' => 1,
    'warehouse_id' => 1,
    'quantity' => 500,
    'movement_type' => 'in',
    'reason' => 'Purchase from supplier',
    'notes' => 'Delivery note #12345',
]);
```

#### WarehouseData
```php
WarehouseData::fromFilament([
    'name' => 'Main Storage',
    'location' => 'Building A, Section 2',
    'capacity' => 10000,
    'is_active' => true,
]);
```

### Auth Domain

#### ProfileUpdateData
```php
ProfileUpdateData::from([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
```

## Migration Checklist

For each Filament Resource, follow these steps:

### 1. Create Resource Page (if not exists)
Already done for all resources.

### 2. Update CreateRecord Pages

Add to all `Create*.php` pages:

```php
use Domains\{Domain}\DTOs\{Model}Data;

protected function mutateFormDataBeforeCreate(array $data): array
{
    $dto = {Model}Data::fromFilament($data);
    return $dto->toArray();
}
```

### 3. Update EditRecord Pages

Add to all `Edit*.php` pages:

```php
use Domains\{Domain}\DTOs\{Model}Data;

protected function mutateFormDataBeforeSave(array $data): array
{
    $dto = {Model}Data::fromFilament($data);
    return $dto->toArray();
}
```

### 4. Update Controllers (React Frontend)

Replace array validation with DTOs:

**Before:**
```php
public function update(Request $request, Customer $customer)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email',
        // ...
    ]);

    $customer->update($validated);
}
```

**After:**
```php
public function update(Request $request, Customer $customer)
{
    $dto = CustomerData::from($request->all());
    $customer->update($dto->toArray());
}
```

## Testing DTOs

### Unit Tests

```php
use Domains\CRM\DTOs\CustomerData;
use Domains\Shared\Enums\CustomerType;

it('creates customer DTO from array', function () {
    $dto = CustomerData::from([
        'team_id' => 1,
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'type' => CustomerType::Retail,
    ]);

    expect($dto->name)->toBe('Test Customer')
        ->and($dto->email)->toBe('test@example.com')
        ->and($dto->type)->toBe(CustomerType::Retail);
});

it('validates required fields', function () {
    CustomerData::from([
        'name' => '', // Invalid
    ]);
})->throws(ValidationException::class);
```

### Feature Tests

```php
it('creates customer via API with DTO', function () {
    $data = [
        'name' => 'API Customer',
        'email' => 'api@example.com',
        'type' => CustomerType::Wholesale->value,
    ];

    $this->postJson('/api/customers', $data)
        ->assertCreated()
        ->assertJson([
            'name' => 'API Customer',
        ]);

    $this->assertDatabaseHas('customers', [
        'name' => 'API Customer',
    ]);
});
```

## Benefits

1. **Type Safety**: Full IDE autocomplete and type checking
2. **Validation**: Built-in validation attributes
3. **Documentation**: DTOs serve as living documentation
4. **Refactoring**: Easy to refactor - breaking changes are caught at compile time
5. **Testing**: Easier to test with known data structures
6. **API Consistency**: Same DTO for API, Forms, and internal services

## Common Pitfalls

### ❌ Don't: Manually create arrays
```php
$customer = Customer::create([
    'name' => $request->name,
    'email' => $request->email,
    // Easy to miss fields or make typos
]);
```

### ✅ Do: Use DTOs
```php
$dto = CustomerData::from($request->all());
$customer = Customer::create($dto->toArray());
```

### ❌ Don't: Validate in multiple places
```php
// Controller validates
$request->validate([...]);

// Service validates again
if (empty($data['name'])) { ... }
```

### ✅ Do: Validate once in DTO
```php
$dto = CustomerData::from($request->all());
// Validation happens automatically
```

## Advanced Features

### Conditional Logic

```php
public function withAllocatable(string $type, int $id): static
{
    $this->allocatable_type = $type;
    $this->allocatable_id = $id;
    return $this;
}
```

### Computed Properties

```php
public function getTotalAmount(): int
{
    return $this->subtotal + $this->tax_amount;
}
```

### Casting

DTOs automatically handle enum casting:

```php
public CustomerType $type; // Auto-casts from string to enum
```

## Next Steps

1. ✅ Install `spatie/laravel-data`
2. ✅ DTOs created for all domains
3. ⏳ Update all Filament Resource Create/Edit pages
4. ⏳ Update all Controllers
5. ⏳ Write comprehensive tests
6. ⏳ Run full test suite

## Resources

- [Spatie Laravel Data Documentation](https://spatie.be/docs/laravel-data)
- [Validation Attributes](https://spatie.be/docs/laravel-data/v4/as-a-data-transfer-object/validation)
- [Creating Data Objects](https://spatie.be/docs/laravel-data/v4/as-a-data-transfer-object/creating-a-data-object)
