<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Laravel\Tests\Fixtures\Connectors\DifferentServiceConnector;

class DifferentServiceUserRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'GET';

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = DifferentServiceConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '/user';
    }
}
