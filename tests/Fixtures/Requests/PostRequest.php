<?php

namespace Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Plugins\HasJsonBody;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Connectors\TestConnector;

class PostRequest extends SaloonRequest
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = Saloon::POST;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected ?string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/post';
    }

    /**
     * Default Data
     *
     * @return string[]
     */
    public function defaultData(): array
    {
        return [
            'name' => 'Sammy',
        ];
    }

    public function defaultHeaders(): array
    {
        return [
            'X-Foo' => 'Bar',
        ];
    }

    public function defaultQuery(): array
    {
        return [
            'test_query' => 'hello',
        ];
    }
}
