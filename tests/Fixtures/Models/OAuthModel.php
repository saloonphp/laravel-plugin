<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Saloon\Laravel\Casts\OAuthAuthenticatorCast;

class OAuthModel extends BaseModel
{
    protected $casts = [
        'auth' => OAuthAuthenticatorCast::class,
    ];
}
