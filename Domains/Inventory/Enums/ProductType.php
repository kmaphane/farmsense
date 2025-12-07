<?php

declare(strict_types=1);

namespace Domains\Inventory\Enums;

enum ProductType: string
{
    // Inventory Items
    case Feed = 'feed';
    case Medicine = 'medicine';
    case Packaging = 'packaging';
    case Equipment = 'equipment';

    // Poultry Products
    case LiveBird = 'live_bird';
    case WholeChicken = 'whole_chicken';
    case ChickenPieces = 'chicken_pieces';
    case Offal = 'offal';
    case ByProduct = 'by_product';

    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Feed => 'Feed',
            self::Medicine => 'Medicine',
            self::Packaging => 'Packaging',
            self::Equipment => 'Equipment',
            self::LiveBird => 'Live Bird',
            self::WholeChicken => 'Whole Chicken',
            self::ChickenPieces => 'Chicken Pieces',
            self::Offal => 'Offal',
            self::ByProduct => 'By-Product',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Feed => 'warning',
            self::Medicine => 'danger',
            self::Packaging => 'info',
            self::Equipment => 'gray',
            self::LiveBird => 'success',
            self::WholeChicken => 'success',
            self::ChickenPieces => 'primary',
            self::Offal => 'secondary',
            self::ByProduct => 'secondary',
            self::Other => 'gray',
        };
    }

    /**
     * Determine if this product type is a poultry product (sellable).
     */
    public function isPoultryProduct(): bool
    {
        return in_array($this, [
            self::LiveBird,
            self::WholeChicken,
            self::ChickenPieces,
            self::Offal,
            self::ByProduct,
        ], true);
    }

    /**
     * Determine if this product type is an inventory/input item.
     */
    public function isInventoryItem(): bool
    {
        return in_array($this, [
            self::Feed,
            self::Medicine,
            self::Packaging,
            self::Equipment,
            self::Other,
        ], true);
    }

    /**
     * Get all poultry product types.
     *
     * @return array<self>
     */
    public static function poultryProducts(): array
    {
        return [
            self::LiveBird,
            self::WholeChicken,
            self::ChickenPieces,
            self::Offal,
            self::ByProduct,
        ];
    }

    /**
     * Get all inventory item types.
     *
     * @return array<self>
     */
    public static function inventoryItems(): array
    {
        return [
            self::Feed,
            self::Medicine,
            self::Packaging,
            self::Equipment,
            self::Other,
        ];
    }
}
