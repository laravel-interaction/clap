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
    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return \LaravelInteraction\Clap\Applause
     */
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return \LaravelInteraction\Clap\Applause
     */
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clapperApplause(): HasMany
    {
        return $this->hasMany(
            config('clap.models.applause'),
            config('clap.column_names.user_foreign_key'),
            $this->getKeyName()
        );
    }

    /**
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
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
