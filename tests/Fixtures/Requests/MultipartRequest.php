<?php declare(strict_types=1);

namespace Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Contracts\Body\WithBody;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Body\HasBody;
use Sammyjo20\Saloon\Traits\Body\HasJsonBody;
use Sammyjo20\Saloon\Traits\Body\HasMultipartBody;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Connectors\TestConnector;

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
