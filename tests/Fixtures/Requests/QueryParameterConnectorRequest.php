<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\QueryParameterConnector;

class QueryParameterConnectorRequest extends SaloonRequest
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
    protected string $connector = QueryParameterConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '/user';
    }

    protected function defaultQueryParameters(): array
    {
        return [
            'include' => 'user',
        ];
    }
}
