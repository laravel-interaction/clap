<?php

declare(strict_types=1);

namespace LaravelInteraction\Clap\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Clap\Concerns\Clappable;
use LaravelInteraction\Clap\Concerns\Clapper;

/**
 * @method static \LaravelInteraction\Clap\Tests\Models\User|\Illuminate\Database\Eloquent\Builder query()
 */
class User extends Model
{
    use Clapper;

    use Clappable;
}
