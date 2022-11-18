<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Saloon\Laravel\Casts\EncryptedOAuthAuthenticatorCast;

class EncryptedOAuthModel extends BaseModel
{
    protected $casts = [
        'auth' => EncryptedOAuthAuthenticatorCast::class,
    ];
}
