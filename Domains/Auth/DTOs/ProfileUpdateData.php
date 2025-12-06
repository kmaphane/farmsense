<?php

namespace Domains\Auth\DTOs;

use Domains\Shared\DTOs\BaseData;
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
}
