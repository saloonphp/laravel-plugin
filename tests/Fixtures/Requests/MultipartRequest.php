<?php declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Contracts\Body\WithBody;
use Saloon\Http\SaloonRequest;
use Saloon\Traits\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Body\HasMultipartBody;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class MultipartRequest extends SaloonRequest implements WithBody
{
    use HasMultipartBody;

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
    protected function defineEndpoint(): string
    {
        return '/payload';
    }
}
