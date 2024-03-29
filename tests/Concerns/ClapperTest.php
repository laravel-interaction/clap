<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Concerns;

use LaravelInteraction\Clap\Applause;
use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\TestCase;

/**
 * @internal
 */
final class ClapperTest extends TestCase
{
    public function testClap(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        $this->assertDatabaseHas(
            Applause::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'clappable_type' => $channel->getMorphClass(),
                'clappable_id' => $channel->getKey(),
            ]
        );
        $user->load('clapperApplause');
        $user->unclap($channel);
        $user->load('clapperApplause');
        $user->clap($channel);
        $user->unclap($channel);
        $user->load('clapperApplause');
        $user->clapOnce($channel);
    }

    public function testUnclap(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        $this->assertDatabaseHas(
            Applause::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'clappable_type' => $channel->getMorphClass(),
                'clappable_id' => $channel->getKey(),
            ]
        );
        $user->clap($channel);
        $user->unclap($channel);
        $this->assertDatabaseMissing(
            Applause::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'clappable_type' => $channel->getMorphClass(),
                'clappable_id' => $channel->getKey(),
            ]
        );
    }

    public function testApplause(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        $this->assertSame(1, $user->clapperApplause()->count());
        $this->assertSame(1, $user->clapperApplause->count());
    }

    public function testHasClapped(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        $this->assertTrue($user->hasClapped($channel));
        $user->unclap($channel);
        $user->load('clapperApplause');
        $this->assertFalse($user->hasClapped($channel));
    }

    public function testHasNotClapped(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        $this->assertFalse($user->hasNotClapped($channel));
        $user->unclap($channel);
        $this->assertTrue($user->hasNotClapped($channel));
    }
}
