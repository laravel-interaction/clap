<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use LaravelInteraction\Clap\Events\Clapped;
use LaravelInteraction\Clap\Events\Unclapped;

/**
 * @property float $applause
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Database\Eloquent\Model $user
 * @property \Illuminate\Database\Eloquent\Model $clapper
 * @property \Illuminate\Database\Eloquent\Model $clappable
 *
 * @method static \LaravelInteraction\Clap\Applause|\Illuminate\Database\Eloquent\Builder withType(string $type)
 * @method static \LaravelInteraction\Clap\Applause|\Illuminate\Database\Eloquent\Builder query()
 */
class Applause extends MorphPivot
{
    protected function uuids(): bool
    {
        return (bool) config('clap.uuids');
    }

    /**
     * @var bool
     */
    public $incrementing = true;

    public function getIncrementing(): bool
    {
        if ($this->uuids()) {
            return false;
        }

        return parent::getIncrementing();
    }

    public function getKeyName(): string
    {
        return $this->uuids() ? 'uuid' : parent::getKeyName();
    }

    public function getKeyType(): string
    {
        return $this->uuids() ? 'string' : parent::getKeyType();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(
            function (self $like): void {
                if ($like->uuids()) {
                    $like->{$like->getKeyName()} = Str::orderedUuid();
                }
            }
        );
    }

    /**
     * @var array<string, class-string<\LaravelInteraction\Clap\Events\Clapped>>|array<string, class-string<\LaravelInteraction\Clap\Events\Unclapped>>
     */
    protected $dispatchesEvents = [
        'created' => Clapped::class,
        'deleted' => Unclapped::class,
    ];

    public function getTable(): string
    {
        return config('clap.table_names.pivot') ?: parent::getTable();
    }

    public function clappable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('clap.models.user'), config('clap.column_names.user_foreign_key'));
    }

    public function clapper(): BelongsTo
    {
        return $this->user();
    }

    public function isClappedBy(Model $user): bool
    {
        return $user->is($this->clapper);
    }

    public function isClappedTo(Model $object): bool
    {
        return $object->is($this->clappable);
    }

    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('clappable_type', app($type)->getMorphClass());
    }
}
