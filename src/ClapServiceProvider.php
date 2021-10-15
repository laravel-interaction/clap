<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap;

use LaravelInteraction\Support\InteractionList;
use LaravelInteraction\Support\InteractionServiceProvider;

class ClapServiceProvider extends InteractionServiceProvider
{
    /**
     * @var string
     */
    protected $interaction = InteractionList::CLAP;
}
