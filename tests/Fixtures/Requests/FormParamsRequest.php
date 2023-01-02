<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\WithBody;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class FormParamsRequest extends Request implements WithBody
{
    use HasFormBody;

    /**
     * Define the method that the request will use.
     *
     * @var Method
     */
    protected Method $method = Method::POST;

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
        return '/payload';
    }
}
