import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/react';
import { store, update } from '@/actions/App/Http/Controllers/Batches/DailyLogController';
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
import * as React from 'react';

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

export interface LastLogData {
    log_date: string;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    humidity_percent: number | null;
}

export interface DailyLogFormData {
    id?: number;
    log_date: string;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    humidity_percent: number | null;
    ammonia_ppm: number | null;
    notes: string | null;
}

interface DailyLogFormProps {
    batchId: number;
    dailyLog?: DailyLogFormData;
    lastLog?: LastLogData | null;
    suggestedDate?: string;
    isEdit?: boolean;
    compact?: boolean;
}

export function DailyLogForm({
    batchId,
    dailyLog,
    lastLog,
    suggestedDate,
    isEdit = false,
    compact = false,
}: DailyLogFormProps) {
    const formUrl = isEdit && dailyLog
        ? update.url({ batch: batchId, dailyLog: dailyLog.id! })
        : store.url(batchId);

    const formMethod = isEdit ? 'put' : 'post';

    return (
        <Form action={formUrl} method={formMethod}>
            {({ errors, processing }) => (
                <div className={compact ? 'space-y-4' : 'space-y-6'}>
                    {/* Last Log Reference */}
                    {lastLog && !isEdit && !compact && (
                        <Card className="border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950/30">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm text-blue-800 dark:text-blue-200">
                                    Previous Log ({new Date(lastLog.log_date).toLocaleDateString()})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-2 gap-2 text-sm text-blue-700 dark:text-blue-300 sm:grid-cols-4">
                                    <div>Mortality: {lastLog.mortality_count}</div>
                                    <div>Feed: {lastLog.feed_consumed_kg}kg</div>
                                    {lastLog.temperature_celsius && (
                                        <div>Temp: {lastLog.temperature_celsius}°C</div>
                                    )}
                                    {lastLog.humidity_percent && (
                                        <div>Humidity: {lastLog.humidity_percent}%</div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Edit Warning */}
                    {isEdit && !compact && (
                        <Card className="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/30">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm text-amber-800 dark:text-amber-200">
                                    Editing Log for {dailyLog ? new Date(dailyLog.log_date).toLocaleDateString() : ''}
                                </CardTitle>
                                <CardDescription className="text-amber-700 dark:text-amber-300">
                                    You can only edit logs from today. After midnight, logs become read-only.
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    )}

                    {/* Form Fields */}
                    {compact ? (
                        <div className="space-y-4">
                            <FormField
                                icon={Calendar}
                                label="Log Date"
                                name="log_date"
                                type="date"
                                required
                                defaultValue={dailyLog?.log_date ?? suggestedDate}
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
                                defaultValue={dailyLog?.mortality_count}
                                hint="Number of birds that died"
                                error={errors.mortality_count}
                            />

                            <FormField
                                icon={Utensils}
                                label="Feed Consumed (kg)"
                                name="feed_consumed_kg"
                                type="number"
                                placeholder="0.00"
                                required
                                min={0}
                                step="0.01"
                                defaultValue={dailyLog?.feed_consumed_kg}
                                error={errors.feed_consumed_kg}
                            />

                            <FormField
                                icon={Droplets}
                                label="Water Consumed (L)"
                                name="water_consumed_liters"
                                type="number"
                                placeholder="0.00"
                                min={0}
                                step="0.01"
                                defaultValue={dailyLog?.water_consumed_liters}
                                error={errors.water_consumed_liters}
                            />

                            <div className="grid grid-cols-2 gap-3">
                                <FormField
                                    icon={Thermometer}
                                    label="Temp (°C)"
                                    name="temperature_celsius"
                                    type="number"
                                    placeholder="25"
                                    min={-10}
                                    max={60}
                                    step="0.1"
                                    defaultValue={dailyLog?.temperature_celsius}
                                    error={errors.temperature_celsius}
                                />

                                <FormField
                                    icon={Gauge}
                                    label="Humidity (%)"
                                    name="humidity_percent"
                                    type="number"
                                    placeholder="65"
                                    min={0}
                                    max={100}
                                    step="0.1"
                                    defaultValue={dailyLog?.humidity_percent}
                                    error={errors.humidity_percent}
                                />
                            </div>

                            <FormField
                                icon={Wind}
                                label="Ammonia (ppm)"
                                name="ammonia_ppm"
                                type="number"
                                placeholder="0"
                                min={0}
                                max={100}
                                step="0.1"
                                defaultValue={dailyLog?.ammonia_ppm}
                                error={errors.ammonia_ppm}
                            />

                            <div className="space-y-2">
                                <Label htmlFor="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={2}
                                    className="border-input flex min-h-[60px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-gray-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800"
                                    placeholder="Any observations..."
                                    defaultValue={dailyLog?.notes ?? ''}
                                />
                                {errors.notes && (
                                    <p className="text-xs text-red-500">{errors.notes}</p>
                                )}
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                <Save className="h-4 w-4" />
                                {processing ? 'Saving...' : isEdit ? 'Update Log' : 'Save Log'}
                            </Button>
                        </div>
                    ) : (
                    <>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Log Details</CardTitle>
                            <CardDescription>
                                {isEdit ? 'Update' : 'Record'} daily batch data. Required fields are marked with *.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <FormField
                                icon={Calendar}
                                label="Log Date"
                                name="log_date"
                                type="date"
                                required
                                defaultValue={dailyLog?.log_date ?? suggestedDate}
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
                                defaultValue={dailyLog?.mortality_count}
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
                                defaultValue={dailyLog?.feed_consumed_kg}
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
                                defaultValue={dailyLog?.water_consumed_liters}
                                hint="Total water consumed in liters (optional)"
                                error={errors.water_consumed_liters}
                            />
                        </CardContent>
                    </Card>

                    {/* Environmental Data */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Environmental Data</CardTitle>
                            <CardDescription>
                                Optional environmental readings from the house
                            </CardDescription>
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
                                defaultValue={dailyLog?.temperature_celsius}
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
                                defaultValue={dailyLog?.humidity_percent}
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
                                defaultValue={dailyLog?.ammonia_ppm}
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
                                    defaultValue={dailyLog?.notes ?? ''}
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
                            {processing ? 'Saving...' : isEdit ? 'Update Log' : 'Save Log'}
                        </Button>
                    </div>
                    </>
                    )}
                </div>
            )}
        </Form>
    );
}
