<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Laravel\Tests\Fixtures\Connectors\QueryParameterConnector;

class QueryParameterConnectorRequest extends Request
{
    /**
     * Define the method that the request will use.
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     */
    protected string $connector = QueryParameterConnector::class;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
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
