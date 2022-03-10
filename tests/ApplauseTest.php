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
    /**
     * @var \LaravelInteraction\Clap\Tests\Models\User
     */
    private $user;

    /**
     * @var \LaravelInteraction\Clap\Tests\Models\Channel
     */
    private $channel;

    /**
     * @var \LaravelInteraction\Clap\Applause
     */
    private $applause;

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
        self::assertInstanceOf(Carbon::class, $this->applause->created_at);
        self::assertInstanceOf(Carbon::class, $this->applause->updated_at);
    }

    public function testScopeWithType(): void
    {
        self::assertSame(1, Applause::query()->withType(Channel::class)->count());
        self::assertSame(0, Applause::query()->withType(User::class)->count());
    }

    public function testGetTable(): void
    {
        self::assertSame(config('clap.table_names.pivot'), $this->applause->getTable());
    }

    public function testClapper(): void
    {
        self::assertInstanceOf(User::class, $this->applause->clapper);
    }

    public function testRatable(): void
    {
        self::assertInstanceOf(Channel::class, $this->applause->clappable);
    }

    public function testUser(): void
    {
        self::assertInstanceOf(User::class, $this->applause->user);
    }

    public function testIsClappedTo(): void
    {
        self::assertTrue($this->applause->isClappedTo($this->channel));
        self::assertFalse($this->applause->isClappedTo($this->user));
    }

    public function testIsClappedBy(): void
    {
        self::assertFalse($this->applause->isClappedBy($this->channel));
        self::assertTrue($this->applause->isClappedBy($this->user));
    }
}
