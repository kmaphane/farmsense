import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FieldLayout } from '@/layouts/app/field-layout';
import { Head } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import { show } from '@/actions/App/Http/Controllers/Batches/BatchController';
import { update } from '@/actions/App/Http/Controllers/Batches/DailyLogController';
import {
    AlertTriangle,
    Calendar,
    Droplets,
    Gauge,
    Save,
    Thermometer,
    Utensils,
    Wind,
} from 'lucide-react';

interface DailyLogData {
    id: number;
    log_date: string;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    humidity_percent: number | null;
    ammonia_ppm: number | null;
    notes: string | null;
}

interface BatchData {
    id: number;
    name: string;
    age_in_days: number;
}

interface Props {
    batch: BatchData;
    dailyLog: DailyLogData;
}

interface FormFieldProps {
    icon: React.ElementType;
    label: string;
    name: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
    hint?: string;
    error?: string;
    defaultValue?: string | number | null;
    min?: number;
    max?: number;
    step?: string;
}

function FormField({
    icon: Icon,
    label,
    name,
    type = 'text',
    placeholder,
    required,
    hint,
    error,
    defaultValue,
    min,
    max,
    step,
}: FormFieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={name} className="flex items-center gap-2">
                <Icon className="h-4 w-4 text-gray-500" />
                {label}
                {required && <span className="text-red-500">*</span>}
            </Label>
            <Input
                id={name}
                name={name}
                type={type}
                placeholder={placeholder}
                required={required}
                className={error ? 'border-red-500' : ''}
                defaultValue={defaultValue ?? ''}
                min={min}
                max={max}
                step={step}
            />
            {hint && !error && (
                <p className="text-xs text-gray-500 dark:text-gray-400">{hint}</p>
            )}
            {error && (
                <p className="text-xs text-red-500">{error}</p>
            )}
        </div>
    );
}

export default function Edit({ batch, dailyLog }: Props) {
    return (
        <FieldLayout
            title="Edit Daily Log"
            backHref={show.url(batch.id)}
            backLabel={batch.name}
        >
            <Head title={`Edit Log - ${batch.name}`} />

            <Form {...update.form({ batch: batch.id, dailyLog: dailyLog.id })}>
                {({ errors, processing }) => (
                    <div className="space-y-6">
                        {/* Batch Info */}
                        <Card className="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/30">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm text-amber-800 dark:text-amber-200">
                                    Editing Log for {new Date(dailyLog.log_date).toLocaleDateString()}
                                </CardTitle>
                                <CardDescription className="text-amber-700 dark:text-amber-300">
                                    You can only edit logs from today. After midnight, logs become read-only.
                                </CardDescription>
                            </CardHeader>
                        </Card>

                        {/* Form Fields */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Log Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <FormField
                                    icon={Calendar}
                                    label="Log Date"
                                    name="log_date"
                                    type="date"
                                    required
                                    defaultValue={dailyLog.log_date}
                                    error={errors.log_date}
                                />

                                <FormField
                                    icon={AlertTriangle}
                                    label="Mortality Count"
                                    name="mortality_count"
                                    type="number"
                                    placeholder="0"
                                    required
                                    min={0}
                                    defaultValue={dailyLog.mortality_count}
                                    hint="Number of birds that died"
                                    error={errors.mortality_count}
                                />

                                <FormField
                                    icon={Utensils}
                                    label="Feed Consumed"
                                    name="feed_consumed_kg"
                                    type="number"
                                    placeholder="0.00"
                                    required
                                    min={0}
                                    step="0.01"
                                    defaultValue={dailyLog.feed_consumed_kg}
                                    hint="Total feed consumed in kg"
                                    error={errors.feed_consumed_kg}
                                />

                                <FormField
                                    icon={Droplets}
                                    label="Water Consumed"
                                    name="water_consumed_liters"
                                    type="number"
                                    placeholder="0.00"
                                    min={0}
                                    step="0.01"
                                    defaultValue={dailyLog.water_consumed_liters}
                                    hint="Total water consumed in liters (optional)"
                                    error={errors.water_consumed_liters}
                                />
                            </CardContent>
                        </Card>

                        {/* Environmental Data */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Environmental Data</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <FormField
                                    icon={Thermometer}
                                    label="Temperature"
                                    name="temperature_celsius"
                                    type="number"
                                    placeholder="25.0"
                                    min={-10}
                                    max={60}
                                    step="0.1"
                                    defaultValue={dailyLog.temperature_celsius}
                                    hint="Average house temperature in °C"
                                    error={errors.temperature_celsius}
                                />

                                <FormField
                                    icon={Gauge}
                                    label="Humidity"
                                    name="humidity_percent"
                                    type="number"
                                    placeholder="65.0"
                                    min={0}
                                    max={100}
                                    step="0.1"
                                    defaultValue={dailyLog.humidity_percent}
                                    hint="Average humidity percentage"
                                    error={errors.humidity_percent}
                                />

                                <FormField
                                    icon={Wind}
                                    label="Ammonia Level"
                                    name="ammonia_ppm"
                                    type="number"
                                    placeholder="0.0"
                                    min={0}
                                    max={100}
                                    step="0.1"
                                    defaultValue={dailyLog.ammonia_ppm}
                                    hint="Ammonia reading in ppm"
                                    error={errors.ammonia_ppm}
                                />
                            </CardContent>
                        </Card>

                        {/* Notes */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <Label htmlFor="notes">Additional Notes</Label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows={3}
                                        className="border-input flex min-h-[80px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-gray-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800"
                                        placeholder="Any observations, issues, or notes..."
                                        defaultValue={dailyLog.notes ?? ''}
                                    />
                                    {errors.notes && (
                                        <p className="text-xs text-red-500">{errors.notes}</p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Submit */}
                        <div className="flex gap-3 pb-20 sm:pb-6">
                            <Button
                                type="submit"
                                className="flex-1"
                                disabled={processing}
                            >
                                <Save className="h-4 w-4" />
                                {processing ? 'Saving...' : 'Update Log'}
                            </Button>
                        </div>
                    </div>
                )}
            </Form>
        </FieldLayout>
    );
}
