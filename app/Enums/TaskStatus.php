<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Done = 'done';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Review => 'In Review',
            self::Done => 'Done',
        };
    }

    public function classes(): string
    {
        return match ($this) {
            self::Todo => 'bg-gray-100 text-gray-700',
            self::InProgress => 'bg-blue-50 text-blue-700',
            self::Review => 'bg-purple-50 text-purple-700',
            self::Done => 'bg-green-50 text-green-700',
        };
    }
}
