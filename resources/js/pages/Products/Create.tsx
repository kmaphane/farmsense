import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';

export default function CreateProduct() {
    const { data, setData, post, errors, processing, recentlySuccessful } =
        useForm({
            name: '',
            type: '',
            unit: '',
            selling_price_cents: '',
            units_per_package: '',
            package_unit: '',
            yield_per_bird: '',
            quantity_on_hand: '',
            reorder_level: '',
            unit_cost: '',
            is_active: true,
        });

    function handleChange(
        e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
    ) {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'checkbox' ? checked : value);
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/inventory/products');
    }

    return (
        <AppLayout>
            <Head title="Create Product" />
            <div className="mx-auto max-w-xl p-8">
                <h1 className="mb-6 text-2xl font-bold">Create Product</h1>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            value={data.name}
                            onChange={handleChange}
                            required
                            placeholder="Product name"
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="type">Type</Label>
                        <Input
                            id="type"
                            name="type"
                            value={data.type}
                            onChange={handleChange}
                            required
                            placeholder="Type (e.g. live_bird)"
                        />
                        <InputError message={errors.type} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="unit">Unit</Label>
                        <Input
                            id="unit"
                            name="unit"
                            value={data.unit}
                            onChange={handleChange}
                            required
                            placeholder="Unit (e.g. single, kg)"
                        />
                        <InputError message={errors.unit} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="selling_price_cents">
                            Selling Price (thebe)
                        </Label>
                        <Input
                            id="selling_price_cents"
                            name="selling_price_cents"
                            type="number"
                            value={data.selling_price_cents}
                            onChange={handleChange}
                            placeholder="Selling price in thebe"
                        />
                        <InputError message={errors.selling_price_cents} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="units_per_package">
                            Units per Package
                        </Label>
                        <Input
                            id="units_per_package"
                            name="units_per_package"
                            type="number"
                            value={data.units_per_package}
                            onChange={handleChange}
                            placeholder="Units per package"
                        />
                        <InputError message={errors.units_per_package} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="package_unit">Package Unit</Label>
                        <Input
                            id="package_unit"
                            name="package_unit"
                            value={data.package_unit}
                            onChange={handleChange}
                            placeholder="Package unit (e.g. single, pack)"
                        />
                        <InputError message={errors.package_unit} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="yield_per_bird">Yield per Bird</Label>
                        <Input
                            id="yield_per_bird"
                            name="yield_per_bird"
                            type="number"
                            value={data.yield_per_bird}
                            onChange={handleChange}
                            placeholder="Yield per bird"
                        />
                        <InputError message={errors.yield_per_bird} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="quantity_on_hand">
                            Quantity on Hand
                        </Label>
                        <Input
                            id="quantity_on_hand"
                            name="quantity_on_hand"
                            type="number"
                            value={data.quantity_on_hand}
                            onChange={handleChange}
                            placeholder="Quantity on hand"
                        />
                        <InputError message={errors.quantity_on_hand} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="reorder_level">Reorder Level</Label>
                        <Input
                            id="reorder_level"
                            name="reorder_level"
                            type="number"
                            value={data.reorder_level}
                            onChange={handleChange}
                            placeholder="Reorder level"
                        />
                        <InputError message={errors.reorder_level} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="unit_cost">Unit Cost</Label>
                        <Input
                            id="unit_cost"
                            name="unit_cost"
                            type="number"
                            value={data.unit_cost}
                            onChange={handleChange}
                            placeholder="Unit cost"
                        />
                        <InputError message={errors.unit_cost} />
                    </div>
                    <div className="flex items-center gap-2">
                        <input
                            id="is_active"
                            name="is_active"
                            type="checkbox"
                            checked={!!data.is_active}
                            onChange={handleChange}
                        />
                        <Label htmlFor="is_active">Active</Label>
                    </div>
                    <button
                        type="submit"
                        className="rounded bg-blue-600 px-4 py-2 text-white"
                        disabled={processing}
                    >
                        Create
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
