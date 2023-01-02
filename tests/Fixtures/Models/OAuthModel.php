<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Models;

use Saloon\Laravel\Casts\OAuthAuthenticatorCast;
use Illuminate\Database\Eloquent\Model as BaseModel;

class OAuthModel extends BaseModel
{
    protected $casts = [
        'auth' => OAuthAuthenticatorCast::class,
    ];
}
