<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Events;

use Illuminate\Support\Facades\Event;
use LaravelInteraction\Clap\Events\Clapped;
use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\TestCase;

class ClappedTest extends TestCase
{
    public function testOnce(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([Clapped::class]);
        $user->clap($channel);
        Event::assertDispatchedTimes(Clapped::class);
    }

    public function testTimes(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([Clapped::class]);
        $user->clap($channel);
        $user->clap($channel);
        $user->clap($channel);
        Event::assertDispatchedTimes(Clapped::class, 3);
    }

    public function testClapOnceTimes(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([Clapped::class]);
        $user->clapOnce($channel);
        $user->clapOnce($channel);
        $user->clapOnce($channel);
        Event::assertDispatchedTimes(Clapped::class);
    }
}
