<?php

namespace App\Http\Requests\Batches;

use Domains\Broiler\Models\DailyLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDailyLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $batch = $this->route('batch');

        return $batch && $batch->team_id === Auth::user()?->current_team_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $batch = $this->route('batch');

        return [
            'log_date' => [
                'required',
                'date',
                'before_or_equal:today',
                Rule::unique(DailyLog::class)
                    ->where('batch_id', $batch?->id)
                    ->ignore($this->route('dailyLog')),
            ],
            'mortality_count' => [
                'required',
                'integer',
                'min:0',
            ],
            'feed_consumed_kg' => [
                'required',
                'numeric',
                'min:0',
                'max:99999.99',
            ],
            'water_consumed_liters' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999.99',
            ],
            'temperature_celsius' => [
                'nullable',
                'numeric',
                'min:-10',
                'max:60',
            ],
            'humidity_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'ammonia_ppm' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'log_date.unique' => 'A log entry already exists for this date.',
            'log_date.before_or_equal' => 'Cannot log data for future dates.',
            'mortality_count.min' => 'Mortality count cannot be negative.',
            'feed_consumed_kg.min' => 'Feed consumed cannot be negative.',
        ];
    }
}
