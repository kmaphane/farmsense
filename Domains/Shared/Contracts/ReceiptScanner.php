<?php

namespace Domains\Shared\Contracts;

/**
 * ReceiptScanner Contract
 *
 * Defines the interface for OCR receipt scanning services.
 * Different drivers can implement this to support various OCR providers.
 *
 * Drivers:
 * - OcrSpaceScanner: Free OCR.space API (development)
 * - GoogleVisionScanner: Google Vision API (production)
 */
interface ReceiptScanner
{
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
    public function scan(string $imagePath): array;

    /**
     * Validate if the driver is properly configured
     */
    public function isConfigured(): bool;
}
