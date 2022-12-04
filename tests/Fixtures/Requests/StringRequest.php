<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Traits\Body\HasBody;
use Saloon\Contracts\Body\WithBody;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class StringRequest extends Request implements WithBody
{
    use HasBody;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'POST';

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
