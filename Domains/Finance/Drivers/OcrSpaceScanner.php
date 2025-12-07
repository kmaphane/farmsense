<?php

namespace Domains\Finance\Drivers;

use Domains\Shared\Contracts\ReceiptScanner;

/**
 * OcrSpaceScanner Driver
 *
 * Free OCR service driver for receipt scanning (development).
 * Uses the OCR.space API for extracting text from receipt images.
 *
 * For production, use GoogleVisionScanner instead.
 *
 * Note: Full implementation in Phase 2 with API integration.
 */
class OcrSpaceScanner implements ReceiptScanner
{
    protected string $apiUrl = 'https://api.ocr.space/parse/image';

    /**
     * Scan a receipt image and extract data
     *
     * @param  string  $imagePath  Path to receipt image file
     * @return array{
     *     amount: float,
     *     currency: string,
     *     vendor: string|null,
     *     date: string|null,
     *     raw_data: array,
     *     confidence: float
     * }
     */
    public function scan(string $imagePath): array
    {
        // Stub for Phase 2 implementation
        return [
            'amount' => 0.00,
            'currency' => 'BWP',
            'vendor' => null,
            'date' => null,
            'raw_data' => [],
            'confidence' => 0.0,
        ];
    }

    /**
     * Validate if the driver is properly configured
     */
    public function isConfigured(): bool
    {
        // Phase 2: Check if API key is configured
        return true;
    }
}
