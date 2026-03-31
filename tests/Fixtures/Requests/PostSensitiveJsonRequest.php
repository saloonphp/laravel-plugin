<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class PostSensitiveJsonRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    protected string $connector = TestConnector::class;

    public function resolveEndpoint(): string
    {
        return '/sensitive';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'email' => 'user@example.com',
            'password' => 'plain-password',
            'client_secret' => 'plain-client-secret',
        ];
    }
}
