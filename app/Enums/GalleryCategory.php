<?php

namespace App\Enums;

enum GalleryCategory: string
{
    case Project = 'project';
    case Event = 'event';
    case RandomPhoto = 'random_photo';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Proyek',
            self::Event => 'Event',
            self::RandomPhoto => 'Foto Random',
        };
    }
}
