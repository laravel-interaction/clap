<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Events;

use Illuminate\Database\Eloquent\Model;

class Unclapped
{
    public function __construct(public Model $model)
    {
    }
}
