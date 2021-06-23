<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use LaravelInteraction\Support\Interaction;
use function is_a;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Clap\Applause[] $clappableApplause
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Clap\Concerns\Clapper[] $clappers
 * @property-read int|null $clappable_applause_count
 * @property-read int|null $clappers_count
 * @property float|null $clappable_applause_sum_applause
 * @property float|null $clappable_applause_avg_applause
 *
 * @method static static|\Illuminate\Database\Eloquent\Builder whereClappedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereNotClappedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder withClappersCount($constraints = null)
 */
trait Clappable
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isClappedBy(Model $user): bool
    {
        if (! is_a($user, config('clap.models.user'))) {
            return false;
        }
        $clappersLoaded = $this->relationLoaded('clappers');

        if ($clappersLoaded) {
            return $this->clappers->contains($user);
        }

        return ($this->relationLoaded('clappableApplause') ? $this->clappableApplause : $this->clappableApplause())
            ->where(config('clap.column_names.user_foreign_key'), $user->getKey())
            ->count() > 0;
    }

    public function isNotClappedBy(Model $user): bool
    {
        return ! $this->isClappedBy($user);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function clappableApplause(): MorphMany
    {
        return $this->morphMany(config('clap.models.applause'), 'clappable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function clappers(): MorphToMany
    {
        return tap(
            $this->morphToMany(
                config('clap.models.user'),
                'clappable',
                config('clap.models.applause'),
                null,
                config('clap.column_names.user_foreign_key')
            ),
            static function (MorphToMany $relation): void {
                $relation->distinct($relation->getRelated()->qualifyColumn($relation->getRelatedKeyName()));
            }
        );
    }

    /**
     * @param callable|null $constraints
     *
     * @return $this
     */
    public function loadClappersCount($constraints = null)
    {
        $this->loadCount(
            [
                'clappers' => function ($query) use ($constraints) {
                    return $this->selectDistinctClappersCount($query, $constraints);
                },
            ]
        );

        return $this;
    }

    public function clappersCount(): int
    {
        if ($this->clappers_count !== null) {
            return (int) $this->clappers_count;
        }

        $this->loadClappersCount();

        return (int) $this->clappers_count;
    }

    public function clappersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->clappersCount(),
            $precision,
            $mode,
            $divisors ?? config('clap.divisors')
        );
    }

    public function scopeWhereClappedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'clappers',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereNotClappedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'clappers',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWithClappersCount(Builder $query, $constraints = null): Builder
    {
        return $query->withCount(
            [
                'clappers' => function ($query) use ($constraints) {
                    return $this->selectDistinctClappersCount($query, $constraints);
                },
            ]
        );
    }

    protected function selectDistinctClappersCount(Builder $query, $constraints = null): Builder
    {
        if ($constraints !== null) {
            $query = $constraints($query);
        }

        $column = $query->getModel()
            ->getQualifiedKeyName();

        return $query->select(DB::raw(sprintf('COUNT(DISTINCT(%s))', $column)));
    }

    public function clappableApplauseCount(): int
    {
        if ($this->clappable_applause_count !== null) {
            return (int) $this->clappable_applause_count;
        }
        $this->loadCount('clappableApplause');

        return (int) $this->clappable_applause_count;
    }

    public function clappableApplauseCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->clappableApplauseCount(),
            $precision,
            $mode,
            $divisors ?? config('clap.divisors')
        );
    }
}
