<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class ErrorRequest extends SaloonRequest
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
    protected function defineEndpoint(): string
    {
        return '/error';
    }
}
