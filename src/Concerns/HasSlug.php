<?php
declare(strict_types=1);

namespace Mimachh\Slugme\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mimachh\Slugme\Contracts\Sluggable;

trait HasSlug 
{
    public function slugColumn(): string {
        return 'slug';
    }

    public static function generateUniqueSlug(string $attribute, $currentId = null): string 
    {
        $counter = 1;

        $slug = Str::slug($attribute);
        $originalSlug = $slug;

        // Vérifie que le slug est unique sauf pour l'ID actuel du modèle (en cas d'update)
        while (self::where('slug', $slug)->when($currentId, function ($query) use ($currentId) {
            return $query->where('id', '!=', $currentId);
        })->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function bootHasSlug()
    {
        // Gestion lors de la création
        self::creating(function (Model $model) {
            if ($model instanceof Sluggable) {
                $model->{$model->slugColumn()} = static::generateUniqueSlug($model->{$model->slugAttribute()});
            }
        });

        // Gestion lors de l'update
        self::updating(function (Model $model) {
            if ($model instanceof Sluggable) {
                $currentSlug = $model->getOriginal($model->slugColumn());
                $newSlug = Str::slug($model->{$model->slugAttribute()});

                // Vérifie si le slug a changé ou non
                if ($currentSlug !== $newSlug) {
                    $model->{$model->slugColumn()} = static::generateUniqueSlug($model->{$model->slugAttribute()}, $model->id);
                }
            }
        });
    }
}
