<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends SaloonConnector
{
    use AcceptsJson;

    public function defineBaseUrl(): string
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
