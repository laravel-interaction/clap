<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Concerns;

use Iterator;
use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\TestCase;

/**
 * @internal
 */
final class ClappableTest extends TestCase
{
    /**
     * @return \Iterator<array<class-string<\LaravelInteraction\Clap\Tests\Models\Channel|\LaravelInteraction\Clap\Tests\Models\User>>>
     */
    public function provideModelClasses(): Iterator
    {
        yield [Channel::class];

        yield [User::class];
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testApplause(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $model->clappableApplause()->count());
        self::assertSame(1, $model->clappableApplause->count());
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testClappersCount(string $modelClass): void
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
        $model->loadClappersCount(static fn ($query) => $query->whereKeyNot($user->getKey()));
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
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testWithClappersCount(string $modelClass): void
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
            static fn ($query) => $query->whereKeyNot($user->getKey())
        )->whereKey($model->getKey())
            ->firstOrFail();

        self::assertSame(0, $model->clappersCount());
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testClappersCountForHumans(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame('1', $model->clappersCountForHumans());
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testIsClappedBy(string $modelClass): void
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
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testIsNotClappedBy(string $modelClass): void
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
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testClappers(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $model->clappers()->count());
        $user->unclap($model);
        self::assertSame(0, $model->clappers()->count());
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testScopeWhereClappedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        self::assertSame(1, $modelClass::query()->whereClappedBy($user)->count());
        self::assertSame(0, $modelClass::query()->whereClappedBy($other)->count());
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testScopeWhereNotClappedBy(string $modelClass): void
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
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testClappableApplauseCount(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        $user->clap($model);
        self::assertSame(2, $model->clappableApplauseCount());
        $user->clap($model);
        self::assertSame(2, $model->clappableApplauseCount());
        $model->loadCount('clappableApplause');
        self::assertSame(3, $model->clappableApplauseCount());
    }

    /**
     * @dataProvider provideModelClasses
     *
     * @param class-string<\LaravelInteraction\Clap\Tests\Models\User|\LaravelInteraction\Clap\Tests\Models\Channel> $modelClass
     */
    public function testClappableApplauseCountForHumans(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->clap($model);
        $user->clap($model);
        self::assertSame('2', $model->clappableApplauseCountForHumans());
    }
}
