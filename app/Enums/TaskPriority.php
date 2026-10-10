<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function classes(): string
    {
        return match ($this) {
            self::Low => 'bg-gray-100 text-gray-600',
            self::Medium => 'bg-sky-50 text-sky-700',
            self::High => 'bg-orange-50 text-orange-700',
            self::Urgent => 'bg-red-50 text-red-700',
        };
    }
}
