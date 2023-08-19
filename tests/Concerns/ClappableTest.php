<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Concerns;

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
    public static function provideModelClasses(): \Iterator
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
        $this->assertSame(1, $model->clappableApplause()->count());
        $this->assertSame(1, $model->clappableApplause->count());
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
        $this->assertSame(1, $model->clappersCount());
        $user->unclap($model);
        $this->assertSame(1, $model->clappersCount());
        $model->loadCount('clappers');
        $this->assertSame(0, $model->clappersCount());
        $user->clap($model);
        $this->assertSame(1, $model->clappers()->count());
        $this->assertSame(1, $model->clappers->count());
        $paginate = $model->clappers()
            ->paginate();
        $this->assertSame(1, $paginate->total());
        $this->assertCount(1, $paginate->items());
        $model->loadClappersCount(static fn ($query) => $query->whereKeyNot($user->getKey()));
        $this->assertSame(0, $model->clappersCount());
        $user2 = User::query()->create();
        $user2->clap($model);

        $model->loadClappersCount();
        $this->assertSame(2, $model->clappersCount());
        $this->assertSame(2, $model->clappers()->count());
        $model->load('clappers');
        $this->assertSame(2, $model->clappers->count());
        $paginate = $model->clappers()
            ->paginate();
        $this->assertSame(2, $paginate->total());
        $this->assertCount(2, $paginate->items());
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
        $this->assertSame(0, $model->clappersCount());
        $user->clap($model);
        $model = $modelClass::query()->withClappersCount()->whereKey($model->getKey())->firstOrFail();
        $this->assertSame(1, $model->clappersCount());
        $user->clap($model);
        $model = $modelClass::query()->withClappersCount()->whereKey($model->getKey())->firstOrFail();
        $this->assertSame(1, $model->clappersCount());
        $model = $modelClass::query()->withClappersCount(
            static fn ($query) => $query->whereKeyNot($user->getKey())
        )->whereKey($model->getKey())
            ->firstOrFail();

        $this->assertSame(0, $model->clappersCount());
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
        $this->assertSame('1', $model->clappersCountForHumans());
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
        $this->assertFalse($model->isClappedBy($model));
        $user->clap($model);
        $this->assertTrue($model->isClappedBy($user));
        $model->load('clappers');
        $user->unclap($model);
        $this->assertTrue($model->isClappedBy($user));
        $model->load('clappers');
        $this->assertFalse($model->isClappedBy($user));
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
        $this->assertTrue($model->isNotClappedBy($model));
        $user->clap($model);
        $this->assertFalse($model->isNotClappedBy($user));
        $model->load('clappers');
        $user->unclap($model);
        $this->assertFalse($model->isNotClappedBy($user));
        $model->load('clappers');
        $this->assertTrue($model->isNotClappedBy($user));
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
        $this->assertSame(1, $model->clappers()->count());
        $user->unclap($model);
        $this->assertSame(0, $model->clappers()->count());
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
        $this->assertSame(1, $modelClass::query()->whereClappedBy($user)->count());
        $this->assertSame(0, $modelClass::query()->whereClappedBy($other)->count());
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
        $this->assertSame(
            $modelClass::query()->whereKeyNot($model->getKey())->count(),
            $modelClass::query()->whereNotClappedBy($user)->count()
        );
        $this->assertSame($modelClass::query()->count(), $modelClass::query()->whereNotClappedBy($other)->count());
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
        $this->assertSame(2, $model->clappableApplauseCount());
        $user->clap($model);
        $this->assertSame(2, $model->clappableApplauseCount());
        $model->loadCount('clappableApplause');
        $this->assertSame(3, $model->clappableApplauseCount());
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
        $this->assertSame('2', $model->clappableApplauseCountForHumans());
    }
}
