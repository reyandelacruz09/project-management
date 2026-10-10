<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Active => 'Active',
            self::OnHold => 'On Hold',
            self::Completed => 'Completed',
        };
    }

    public function classes(): string
    {
        return match ($this) {
            self::Planning => 'bg-gray-100 text-gray-700',
            self::Active => 'bg-green-50 text-green-700',
            self::OnHold => 'bg-amber-50 text-amber-700',
            self::Completed => 'bg-indigo-50 text-indigo-700',
        };
    }
}
