<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    protected function defaultQueryParameters(): array
    {
        return [
            'sort' => 'first_name',
        ];
    }
}
