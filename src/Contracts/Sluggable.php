<?php
declare(strict_types=1);

namespace Mimachh\Slugme\Contracts;

interface Sluggable {
    public function slugColumn(): string;

    public function slugAttribute(): string;

    public static function generateUniqueSlug(string $attribute): string;
}