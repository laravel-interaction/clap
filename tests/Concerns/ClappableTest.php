<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Concerns;

use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\TestCase;

class ClappableTest extends TestCase
{
    public function modelClasses(): array
    {
        return[[Channel::class], [User::class]];
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testClaps($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $model->clappableApplause()->count());
        self::assertSame(1, $model->clappableApplause->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testClappersCount($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $model->clappersCount());
        $user->unclap($model);
        self::assertSame(1, $model->clappersCount());
        $model->loadCount('clappers');
        self::assertSame(0, $model->clappersCount());
        $user->clap($model);
        self::assertSame(1, $model->clappers()->count());
        self::assertSame(1, $model->clappers->count());
        $paginate = $model->clappers()
            ->paginate();
        self::assertSame(1, $paginate->total());
        self::assertCount(1, $paginate->items());
        $model->loadClappersCount(function ($query) use ($user) {
            return $query->whereKeyNot($user->getKey());
        });
        self::assertSame(0, $model->clappersCount());
        $user2 = User::query()->create();
        $user2->clap($model);

        $model->loadClappersCount();
        self::assertSame(2, $model->clappersCount());
        self::assertSame(2, $model->clappers()->count());
        $model->load('clappers');
        self::assertSame(2, $model->clappers->count());
        $paginate = $model->clappers()
            ->paginate();
        self::assertSame(2, $paginate->total());
        self::assertCount(2, $paginate->items());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testWithClappersCount($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertSame(0, $model->clappersCount());
        $user->clap($model);
        $model = $modelClass::query()->withClappersCount()->whereKey($model->getKey())->firstOrFail();
        self::assertSame(1, $model->clappersCount());
        $user->clap($model);
        $model = $modelClass::query()->withClappersCount()->whereKey($model->getKey())->firstOrFail();
        self::assertSame(1, $model->clappersCount());
        $model = $modelClass::query()->withClappersCount(
            function ($query) use ($user) {
                return $query->whereKeyNot($user->getKey());
            }
        )->whereKey($model->getKey())
            ->firstOrFail();

        self::assertSame(0, $model->clappersCount());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testClappersCountForHumans($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame('1', $model->clappersCountForHumans());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testIsClappedBy($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertFalse($model->isClappedBy($model));
        $user->clap($model);
        self::assertTrue($model->isClappedBy($user));
        $model->load('clappers');
        $user->unclap($model);
        self::assertTrue($model->isClappedBy($user));
        $model->load('clappers');
        self::assertFalse($model->isClappedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testIsNotClappedBy($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertTrue($model->isNotClappedBy($model));
        $user->clap($model);
        self::assertFalse($model->isNotClappedBy($user));
        $model->load('clappers');
        $user->unclap($model);
        self::assertFalse($model->isNotClappedBy($user));
        $model->load('clappers');
        self::assertTrue($model->isNotClappedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testClappers($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $model->clappers()->count());
        $user->unclap($model);
        self::assertSame(0, $model->clappers()->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testScopeWhereClappedBy($modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $modelClass::query()->whereClappedBy($user)->count());
        self::assertSame(0, $modelClass::query()->whereClappedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testScopeWhereNotClappedBy($modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(
            $modelClass::query()->whereKeyNot($model->getKey())->count(),
            $modelClass::query()->whereNotClappedBy($user)->count()
        );
        self::assertSame($modelClass::query()->count(), $modelClass::query()->whereNotClappedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testClappableApplauseCount($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        $user->clap($model);
        self::assertSame(2, $model->clappableApplauseCount());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel $modelClass
     */
    public function testClappableApplauseCountForHumans($modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        $user->clap($model);
        self::assertSame('2', $model->clappableApplauseCountForHumans());
    }
}
