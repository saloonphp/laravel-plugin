<?php declare(strict_types=1);

use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Laravel\Tests\Fixtures\Models\OAuthModel;

test('the authenticator is serialized and unserialized when using the cast', function () {
    $model = new OAuthModel();
    $authenticator = new AccessTokenAuthenticator('access', 'refresh', now());

    $model->auth = $authenticator;

    $rawAuth = $model->getAttributes()['auth'];

    expect($rawAuth)->toBeString();
    expect($rawAuth)->toEqual(serialize($authenticator));

    // Now lets try to use the accessor

    $modelAuth = $model->auth;

    expect($modelAuth)->toEqual($authenticator);
});

test('the cast will accept null', function () {
    $model = new OAuthModel();
    $model->auth = null;

    expect($model->auth)->toBeNull();
});

test('it will throw an exception if you pass in a value that is not null', function () {
    $model = new OAuthModel();
    $model->auth = 'Hello';
})->throws(InvalidArgumentException::class, 'The given value is not an OAuthAuthenticatorInterface instance.');
