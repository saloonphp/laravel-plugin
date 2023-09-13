<?php

declare(strict_types=1);

namespace Saloon\Laravel\Casts;

use InvalidArgumentException;
use Saloon\Contracts\OAuthAuthenticator;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class OAuthAuthenticatorCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get($model, string $key, $value, array $attributes): ?OAuthAuthenticator
    {
        if (is_null($value)) {
            return null;
        }

        return unserialize($value, ['allowed_classes' => true]);
    }

    /**
     * Prepare the given value for storage.
     *
     * @return mixed|void
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if (! $value instanceof OAuthAuthenticator) {
            throw new InvalidArgumentException('The given value is not an OAuthAuthenticator instance.');
        }

        return serialize($value);
    }
}
