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

    /**
     * Liked constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $applause
     */
    public function __construct(Model $applause)
    {
        $this->applause = $applause;
    }
}
