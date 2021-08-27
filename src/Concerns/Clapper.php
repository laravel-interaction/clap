<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelInteraction\Clap\Applause;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Clap\Applause[] $clapperApplause
 * @property-read int|null $clapper_applause_count
 */
trait Clapper
{
    public function clap(Model $object): Applause
    {
        $clapperApplauseLoaded = $this->relationLoaded('clapperApplause');
        if ($clapperApplauseLoaded) {
            $this->unsetRelation('clapperApplause');
        }

        return $this->clapperApplause()
            ->create([
                'clappable_id' => $object->getKey(),
                'clappable_type' => $object->getMorphClass(),
            ]);
    }

    public function clapOnce(Model $object): Applause
    {
        $attributes = [
            'clappable_id' => $object->getKey(),
            'clappable_type' => $object->getMorphClass(),
        ];

        return $this->clapperApplause()
            ->where($attributes)
            ->firstOr(function () use ($attributes) {
                $clapperApplauseLoaded = $this->relationLoaded('clapperApplause');
                if ($clapperApplauseLoaded) {
                    $this->unsetRelation('clapperApplause');
                }

                return $this->clapperApplause()
                    ->create($attributes);
            });
    }

    public function unclap(Model $object): bool
    {
        $hasNotClapped = $this->hasNotClapped($object);
        if ($hasNotClapped) {
            return true;
        }
        $clapperApplauseLoaded = $this->relationLoaded('clapperApplause');
        if ($clapperApplauseLoaded) {
            $this->unsetRelation('clapperApplause');
        }

        return (bool) $this->clappedItems(get_class($object))
            ->detach($object->getKey());
    }

    public function hasClapped(Model $object): bool
    {
        return ($this->relationLoaded('clapperApplause') ? $this->clapperApplause : $this->clapperApplause())
            ->where('clappable_id', $object->getKey())
            ->where('clappable_type', $object->getMorphClass())
            ->count() > 0;
    }

    public function hasNotClapped(Model $object): bool
    {
        return ! $this->hasClapped($object);
    }

    public function clapperApplause(): HasMany
    {
        return $this->hasMany(
            config('clap.models.applause'),
            config('clap.column_names.user_foreign_key'),
            $this->getKeyName()
        );
    }

    protected function clappedItems(string $class): MorphToMany
    {
        return $this->morphedByMany(
            $class,
            'clappable',
            config('clap.models.applause'),
            config('clap.column_names.user_foreign_key')
        )
            ->withTimestamps();
    }
}
