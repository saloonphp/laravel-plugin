<?php

namespace Sammyjo20\SaloonLaravel\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Sammyjo20\SaloonLaravel\Casts\EncryptedOAuthAuthenticatorCast;
use Sammyjo20\SaloonLaravel\Casts\OAuthAuthenticatorCast;

class EncryptedOAuthModel extends BaseModel
{
    protected $casts = [
        'auth' => EncryptedOAuthAuthenticatorCast::class,
    ];
}
