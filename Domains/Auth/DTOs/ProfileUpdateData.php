<?php

namespace Domains\Auth\DTOs;

use App\Models\User;
use Domains\Shared\DTOs\BaseData;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;

class ProfileUpdateData extends BaseData
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string $name,

        #[Required]
        #[StringType]
        #[Email]
        #[Max(255)]
        public string $email,
    ) {}

    /**
     * Get validation rules with unique email check
     */
    public static function rules(): array
    {
        $userId = auth()->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
        ];
    }
}
