<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class UserRequest extends Request
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
