<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Clap\Concerns\Clappable;

/**
 * @method static \LaravelInteraction\Clap\Tests\Models\Channel|\Illuminate\Database\Eloquent\Builder query()
 */
class Channel extends Model
{
    use Clappable;
}
