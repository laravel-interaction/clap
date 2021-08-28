<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Configuration;

use LaravelInteraction\Clap\Applause;
use LaravelInteraction\Clap\Tests\Models\Channel;
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\TestCase;

class UuidsTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config([
            'clap.uuids' => true,
        ]);
    }

    public function testKeyType(): void
    {
        $applause = new Applause();
        self::assertSame('string', $applause->getKeyType());
    }

    public function testIncrementing(): void
    {
        $applause = new Applause();
        self::assertFalse($applause->getIncrementing());
    }

    public function testKeyName(): void
    {
        $applause = new Applause();
        self::assertSame('uuid', $applause->getKeyName());
    }

    public function testKey(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->clap($channel);
        self::assertIsString($user->clapperApplause()->firstOrFail()->getKey());
    }
}
