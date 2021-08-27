<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Events;

use Illuminate\Database\Eloquent\Model;

class Unclapped
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $applause;

    public function __construct(Model $applause)
    {
        $this->applause = $applause;
    }
}
