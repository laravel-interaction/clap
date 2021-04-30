# Laravel Clap

User clap/unclap behaviour for Laravel.

<p align="center">
<a href="https://packagist.org/packages/laravel-interaction/clap"><img src="https://poser.pugx.org/laravel-interaction/clap/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel-interaction/clap"><img src="https://poser.pugx.org/laravel-interaction/clap/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel-interaction/clap"><img src="https://poser.pugx.org/laravel-interaction/clap/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/laravel-interaction/clap"><img src="https://poser.pugx.org/laravel-interaction/clap/license" alt="License"></a>
</p>

> **Requires [PHP 7.2.0+](https://php.net/releases/)**

Require Laravel Clap using [Composer](https://getcomposer.org):

```bash
composer require laravel-interaction/clap
```

## Usage

### Setup Clapper

```php
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Clap\Concerns\Clapper;

class User extends Model
{
    use Clapper;
}
```

### Setup Ratable

```php
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Clap\Concerns\Clappable;

class Channel extends Model
{
    use Clappable;
}
```

### Clapr

```php
use LaravelInteraction\Clap\Tests\Models\Channel;
/** @var \LaravelInteraction\Clap\Tests\Models\User $user */
/** @var \LaravelInteraction\Clap\Tests\Models\Channel $channel */
// Clap to Ratable
$user->clap($channel);
// clap is only allowed to be called once
$user->clapOnce($channel);
$user->unclap($channel);

// Compare Ratable
$user->hasClapped($channel);
$user->hasNotClapped($channel);

// Get clapped info
$user->clapperApplause()->count(); 

// with type
$user->clapperApplause()->withType(Channel::class)->count(); 

// get clapped channels
Channel::query()->whereClappedBy($user)->get();

// get channels doesnt clapped by user
Channel::query()->whereNotClappedBy($user)->get();
```

### Ratable

```php
use LaravelInteraction\Clap\Tests\Models\User;
use LaravelInteraction\Clap\Tests\Models\Channel;
/** @var \LaravelInteraction\Clap\Tests\Models\User $user */
/** @var \LaravelInteraction\Clap\Tests\Models\Channel $channel */
// Compare Clapper
$channel->isClappedBy($user); 
$channel->isNotClappedBy($user);
// Get clappers info
$channel->clappers->each(function (User $user){
    echo $user->getKey();
});
$channel->loadClappersCount();
$channels = Channel::query()->withClappersCount()->get();
$channels->each(function (Channel $channel){
    echo $channel->clappers()->count(); // 1100
    echo $channel->clappers_count; // "1100"
    echo $channel->clappersCount(); // 1100
    echo $channel->clappersCountForHumans(); // "1.1K"
});
```

### Events

| Event | Fired |
| --- | --- |
| `LaravelInteraction\Clap\Events\Clapped` | When an object get clapped. |
| `LaravelInteraction\Clap\Events\Unclapped` | When an object get unclapped. |

## License

Laravel Clap is an open-sourced software licensed under the [MIT license](LICENSE).
