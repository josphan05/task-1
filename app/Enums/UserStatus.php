<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get all status values as array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label in Vietnamese
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Hoạt động',
            self::INACTIVE => 'Không hoạt động',
        };
    }

    /**
     * Check if status is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}

