<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Events;

use Illuminate\Support\Facades\Event;
use LaravelInteraction\Clap\Events\Unclapped;
use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\TestCase;

class UnclappedTest extends TestCase
{
    public function testOnce(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        Event::fake([Unclapped::class]);
        $user->unclap($channel);
        Event::assertDispatchedTimes(Unclapped::class);
    }

    public function testTimes(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        Event::fake([Unclapped::class]);
        $user->unclap($channel);
        $user->unclap($channel);
        Event::assertDispatchedTimes(Unclapped::class);
    }
}
