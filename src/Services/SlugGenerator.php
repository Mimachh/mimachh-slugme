<?php

namespace Mimachh\Slugme\Services;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class SlugGenerator
{
    /**
     * Generate a unique slug for the given attribute.
     *
     * @param string $attribute
     * @param string $modelClass
     * @param int|null $currentId
     * @param string $slugColumn
     * @return string
     */
    public static function generateUniqueSlug(string $attribute, string $modelClass, ?int $currentId = null, string $slugColumn = 'slug'): string
    {
        $counter = 1;
        $slug = Str::slug($attribute);
        $originalSlug = $slug;

        // Check for slug uniqueness excluding the current ID
        while ($modelClass::where($slugColumn, $slug)->when($currentId, function ($query) use ($currentId, $slugColumn) {
            return $query->where('id', '!=', $currentId);
        })->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
