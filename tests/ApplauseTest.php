<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests;

use Illuminate\Support\Carbon;
use LaravelInteraction\Clap\Applause;
use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;

/**
 * @internal
 */
final class ApplauseTest extends TestCase
{
    private User $user;

    private Channel $channel;

    private Applause $applause;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::query()->create();
        $this->channel = Channel::query()->create();
        $this->user->clap($this->channel);
        $this->applause = Applause::query()->firstOrFail();
    }

    public function testApplauseTimestamp(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->applause->created_at);
        $this->assertInstanceOf(Carbon::class, $this->applause->updated_at);
    }

    public function testScopeWithType(): void
    {
        $this->assertSame(1, Applause::query()->withType(Channel::class)->count());
        $this->assertSame(0, Applause::query()->withType(User::class)->count());
    }

    public function testGetTable(): void
    {
        $this->assertSame(config('clap.table_names.pivot'), $this->applause->getTable());
    }

    public function testClapper(): void
    {
        $this->assertInstanceOf(User::class, $this->applause->clapper);
    }

    public function testRatable(): void
    {
        $this->assertInstanceOf(Channel::class, $this->applause->clappable);
    }

    public function testUser(): void
    {
        $this->assertInstanceOf(User::class, $this->applause->user);
    }

    public function testIsClappedTo(): void
    {
        $this->assertTrue($this->applause->isClappedTo($this->channel));
        $this->assertFalse($this->applause->isClappedTo($this->user));
    }

    public function testIsClappedBy(): void
    {
        $this->assertFalse($this->applause->isClappedBy($this->channel));
        $this->assertTrue($this->applause->isClappedBy($this->user));
    }
}
