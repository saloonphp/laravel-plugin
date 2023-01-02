<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class UserRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
