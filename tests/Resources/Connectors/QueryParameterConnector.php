<?php

namespace Sammyjo20\SaloonLaravel\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends SaloonConnector
{
    use AcceptsJson;

    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    public function defaultQuery(): array
    {
        return [
            'sort' => 'first_name',
        ];
    }
}
