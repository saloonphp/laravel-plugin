<?php declare(strict_types=1);

namespace Saloon\Laravel\Casts;

use InvalidArgumentException;
use Saloon\Interfaces\OAuthAuthenticatorInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class EncryptedOAuthAuthenticatorCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return OAuthAuthenticatorInterface|null
     */
    public function get($model, string $key, $value, array $attributes): ?OAuthAuthenticatorInterface
    {
        if (is_null($value)) {
            return null;
        }

        return unserialize(decrypt($value), ['allowed_classes' => true]);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return mixed|void
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if (! $value instanceof OAuthAuthenticatorInterface) {
            throw new InvalidArgumentException('The given value is not an OAuthAuthenticatorInterface instance.');
        }

        return encrypt(serialize($value));
    }
}
