<?php declare(strict_types=1);

namespace Sammyjo20\SaloonLaravel\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Sammyjo20\SaloonLaravel\Casts\EncryptedOAuthAuthenticatorCast;

class EncryptedOAuthModel extends BaseModel
{
    protected $casts = [
        'auth' => EncryptedOAuthAuthenticatorCast::class,
    ];
}
