<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

class PostSensitiveFormRequest extends Request implements HasBody
{
    use HasFormBody;

    protected Method $method = Method::POST;

    protected string $connector = TestConnector::class;

    public function resolveEndpoint(): string
    {
        return '/sensitive-form';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'email' => 'a@b.test',
            'password' => 'form-secret',
            'name' => 'test',
        ];
    }
}
