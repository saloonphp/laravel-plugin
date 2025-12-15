## SaloonPHP

- SaloonPHP is a PHP library for building beautiful, maintainable API integrations and SDKs with a fluent, expressive API.
- SaloonPHP uses a connector-based architecture where **Connectors** define the base URL and shared configuration, and **Requests** represent specific API endpoints.
- **Version Support**: SaloonPHP v2 and v3 are both actively supported versions. Check the installed version in `composer.json` to determine which version-specific documentation to reference. Most core concepts are similar between versions, but v3 includes improvements like enhanced PSR-7 support, global retry system, and restructured pagination.
- Always use Artisan commands to generate SaloonPHP classes: `php artisan saloon:connector`, `php artisan saloon:request`, `php artisan saloon:response`, `php artisan saloon:plugin`, `php artisan saloon:auth`.
- SaloonPHP documentation is hosted at `https://docs.saloon.dev`
- **Before implementing any features using SaloonPHP, use the `web-search` tool to get the latest docs for that specific feature. The docs listing is available in <available-docs>**

### Basic Usage Patterns

#### Connectors
- Connectors extend `Saloon\Http\Connector` and define the base URL via `resolveBaseUrl()` method.
- Use constructor property promotion to inject dependencies (like User models, API keys, etc.) into connectors.
- Override `defaultHeaders()` to set common headers for all requests.
- Override `defaultAuth()` to set default authentication for all requests.
- Use plugins (traits) like `AcceptsJson`, `AlwaysThrowOnErrors`, `HasTimeout` to add reusable functionality.

@verbatim
<code-snippet name="Basic Connector Example" lang="php">
use Saloon\Http\Connector;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class ApiConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        public string $apiKey,
    ) {}

    public function resolveBaseUrl(): string
    {
        return 'https://api.example.com/v1';
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->apiKey);
    }
}
</code-snippet>
@endverbatim

#### Requests
- Requests extend `Saloon\Http\Request` and define the HTTP method and endpoint.
- Set the `$method` property using `Saloon\Enums\Method` enum (e.g., `Method::GET`, `Method::POST`).
- Override `resolveEndpoint()` to return the endpoint path (relative to connector's base URL).
- Override `defaultQuery()` to set default query parameters.
- Override `defaultHeaders()` to set request-specific headers.
- Override `defaultBody()` or use `body()` method for request bodies.
- Use constructor property promotion to pass data to requests.

@verbatim
<code-snippet name="Basic Request Example" lang="php">
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUsersRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public ?int $page = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/users';
    }

    protected function defaultQuery(): array
    {
        return [
            'page' => $this->page,
        ];
    }
}
</code-snippet>
@endverbatim

#### Sending Requests
- Instantiate a connector, create a request, then call `$connector->send($request)`.
- Responses have methods like `json()`, `body()`, `status()`, `headers()`, `isSuccess()`, `isFailed()`, `dto()`, `dtoOrFail()`.

@verbatim
<code-snippet name="Sending a Request" lang="php">
$connector = new ApiConnector(apiKey: 'your-key');
$request = new GetUsersRequest(page: 1);

$response = $connector->send($request);

if ($response->isSuccess()) {
    $users = $response->json();
}
</code-snippet>
@endverbatim

#### Request Bodies
- Implement `Saloon\Contracts\Body\HasBody` interface and use body traits for different content types.
- Use `HasJsonBody` trait for JSON payloads (sets `Content-Type: application/json`).
- Use `HasXmlBody` trait for XML payloads (sets `Content-Type: application/xml`).
- Use `HasMultipartBody` trait for multipart form data and file uploads (sets `Content-Type: multipart/form-data`).
- Override `defaultBody()` method to define the body content.
- Use `MultipartValue` class for file uploads in multipart bodies.

@verbatim
<code-snippet name="JSON Body Example" lang="php">
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Enums\Method;

class CreateUserRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    protected function defaultBody(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
</code-snippet>
@endverbatim

#### Query Parameters & Headers
- Override `defaultQuery()` in connectors or requests to set default query parameters.
- Override `defaultHeaders()` in connectors or requests to set default headers.
- Use `$request->query()->add()` or `$request->headers()->add()` to modify at runtime.

@verbatim
<code-snippet name="Query Parameters and Headers" lang="php">
protected function defaultQuery(): array
{
    return [
        'page' => $this->page,
        'per_page' => 50,
    ];
}

protected function defaultHeaders(): array
{
    return [
        'X-Custom-Header' => 'value',
    ];
}
</code-snippet>
@endverbatim

#### Data Transfer Objects (DTOs)
- Cast API responses into DTOs by implementing `createDtoFromResponse()` method in request classes.
- Use `$response->dto()` to get the DTO (works even on failed responses).
- Use `$response->dtoOrFail()` to get the DTO or throw an exception on failure.
- Implement `Saloon\Contracts\DataObjects\WithResponse` and use `HasResponse` trait to access the original response from DTOs.

@verbatim
<code-snippet name="DTO Example" lang="php">
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetUserRequest extends Request
{
    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json();

        return new UserDto(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
        );
    }
}

// Usage
$response = $connector->send(new GetUserRequest());
$user = $response->dto();
</code-snippet>
@endverbatim

#### Middleware
- Use middleware to interact with the request and response lifecycle.
- Register request middleware using `middleware()->onRequest()` to modify requests before sending.
- Register response middleware using `middleware()->onResponse()` to modify responses after receiving.
- Use the `boot()` method on connectors or requests to execute logic on every request.
- Return `FakeResponse` in request middleware to mock responses without sending actual requests.

@verbatim
<code-snippet name="Middleware Example" lang="php">
public function boot(PendingRequest $pendingRequest): void
{
    $pendingRequest->headers()->add('X-Request-ID', Str::uuid());
}

// Or register middleware
$this->middleware()->onRequest(function (PendingRequest $pendingRequest) {
    // Modify request
    return $pendingRequest;
});
</code-snippet>
@endverbatim

#### Authenticators
- Use `Saloon\Http\Auth\TokenAuthenticator` for Bearer token authentication.
- Use `Saloon\Http\Auth\BasicAuthenticator` for Basic authentication.
- Use `Saloon\Http\Auth\QueryAuthenticator` for query parameter authentication.
- Create custom authenticators by implementing `Saloon\Contracts\Authenticator` interface.
- Set authenticators on connectors via `defaultAuth()` or on individual requests via `defaultAuth()`.

@verbatim
<code-snippet name="Token Authentication" lang="php">
use Saloon\Http\Auth\TokenAuthenticator;

protected function defaultAuth(): TokenAuthenticator
{
    return new TokenAuthenticator($this->accessToken);
}
</code-snippet>
@endverbatim

#### Plugins
- Plugins are traits that add reusable functionality to connectors or requests.
- Use plugins on connectors to apply to all requests, or on individual requests for request-specific behavior.

**Built-in Plugins (Traits):**
- `AcceptsJson` - Adds `Accept: application/json` header to requests
- `AlwaysThrowOnErrors` - Automatically throws exceptions on failed responses
- `HasTimeout` - Allows setting custom connection and request timeouts
- `HasRetry` - Implements retry logic with exponential backoff
- `HasRateLimit` - Handles rate limiting with configurable limits
- `WithDebugData` - Enables Guzzle's debug mode for detailed request information
- `DisablesSSLVerification` - Disables SSL verification (useful for local development)
- `CastsToDto` - Allows specifying a Data Transfer Object (DTO) for request responses

**Version Notes:**
- **v3**: Global retry system - You can set `$tries`, `$retryInterval`, and `$useExponentialBackoff` properties directly on connectors/requests without the `HasRetry` trait
- **v3**: Pagination is now a separate installable plugin (required for pagination features)
- **v3**: Enhanced PSR-7 support with new response methods like `saveBodyToFile()`, `getRawStream()`, `getConnector()`, `getPsrRequest()`

**Installable Plugins:**
- **Pagination Plugin** - Simplifies handling paginated API responses with various pagination methods (required in v3, optional in v2)
- **Rate Limit Handler Plugin** - Provides tools to prevent and manage rate limits
- **Caching Plugin** - Enables caching of API responses to improve performance
- **XML Wrangler Plugin** - Modern XML reading and writing with dot notation and XPath queries
- **Lawman Plugin** - PestPHP plugin for writing architecture tests for API integrations
- **SDK Generator Plugin** - Generates Saloon SDKs from OpenAPI files or Postman collections

@verbatim
<code-snippet name="Using Plugins" lang="php">
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;
use Saloon\Traits\Plugins\HasRetry;

class ApiConnector extends Connector
{
    use AcceptsJson;
    use HasTimeout;
    use HasRetry; // Optional in v3 - can use $tries property directly

    protected int $connectTimeout = 5;
    protected int $requestTimeout = 15;
    
    // v3: Can set retry properties directly without HasRetry trait
    public ?int $tries = 3;
    public ?int $retryInterval = 500;
    public ?bool $useExponentialBackoff = true;
}
</code-snippet>
@endverbatim

### Laravel Integration

#### Artisan Commands
- Use `php artisan saloon:connector {name}` to create a new connector.
- Use `php artisan saloon:connector {name} --oauth` to create an OAuth2 connector.
- Use `php artisan saloon:request {name}` to create a new request.
- Use `php artisan saloon:response {name}` to create a custom response class.
- Use `php artisan saloon:plugin {name}` to create a custom plugin.
- Use `php artisan saloon:auth {name}` to create a custom authenticator.
- Use `php artisan saloon:list` to list all API integrations.
- **Note**: Artisan commands will create classes in the path specified by `config/saloon.php`'s `integrations_path` setting. Check this configuration to ensure classes are created in the correct location for your application's architecture (DDD, traditional Laravel, etc.).

#### Saloon Facade
- Use `Saloon\Laravel\Facades\Saloon` facade for mocking requests in tests.
- Use `Saloon::fake()` to mock responses globally or per request class.

@verbatim
<code-snippet name="Mocking Requests in Tests" lang="php">
use Saloon\Laravel\Facades\Saloon;
use Saloon\Http\MockResponse;

Saloon::fake([
    GetUsersRequest::class => MockResponse::make(
        body: ['users' => []],
        status: 200
    ),
]);
</code-snippet>
@endverbatim

#### Events
- Saloon emits `SendingSaloonRequest` and `SentSaloonRequest` events during the request lifecycle.
- Listen to these events in `EventServiceProvider` to implement custom functionality like logging, monitoring, etc.

#### Laravel HTTP Client Sender
- Use `Saloon\Laravel\HttpSender` to integrate with Laravel's HTTP client.
- This enables request recording in Laravel Telescope and compatibility with Laravel's event system.
- Configure in `config/saloon.php`: `'default_sender' => \Saloon\Laravel\HttpSender::class`

### File Structure
- **Always check `config/saloon.php` for the `integrations_path` configuration** before creating Saloon classes. The default path is `app/Http/Integrations`, but applications using DDD (Domain-Driven Design) or other architectural patterns may have customized this path (e.g., `App/Integrations`).
- Store connectors and requests in the configured integrations path: `{integrations_path}/{ServiceName}/` directory.
- Use descriptive names: `{Service}Connector`, `{Action}{Resource}Request`.
- Group related requests in subdirectories if needed: `{integrations_path}/{Service}/Requests/`.
- The `integrations_path` configuration is used by the `saloon:list` command to discover your integration classes.

<available-docs>
## Upgrade
- [https://docs.saloon.dev/upgrade/whats-new-in-v3] Use these docs to understand what's new in SaloonPHP v3
- [https://docs.saloon.dev/upgrade/upgrading-from-v2] Use these docs for upgrading from SaloonPHP v2 to v3

## The Basics
- [https://docs.saloon.dev/the-basics/installation] Use these docs for installation instructions, Composer setup, and initial configuration
- [https://docs.saloon.dev/the-basics/connectors] Use these docs for creating connectors, setting base URLs, default headers, and shared configuration
- [https://docs.saloon.dev/the-basics/requests] Use these docs for creating requests, defining endpoints, HTTP methods, query parameters, and request bodies
- [https://docs.saloon.dev/the-basics/authentication] Use these docs for authentication methods including token, basic, OAuth2, and custom authenticators
- [https://docs.saloon.dev/the-basics/sending-body-data] Use these docs for sending body data in requests, including JSON, XML, and multipart form data
- [https://docs.saloon.dev/the-basics/request-body-data/json-body] Use these docs for sending JSON request bodies using HasJsonBody trait
- [https://docs.saloon.dev/the-basics/request-body-data/multipart-form-body] Use these docs for sending multipart form data and file uploads using HasMultipartBody trait
- [https://docs.saloon.dev/the-basics/request-body-data/xml-body] Use these docs for sending XML request bodies using HasXmlBody trait
- [https://docs.saloon.dev/the-basics/request-body-data/form-body-url-encoded] Use these docs for sending URL-encoded form data using HasFormBody trait
- [https://docs.saloon.dev/the-basics/request-body-data/string-plain-text-body] Use these docs for sending plain text/string request bodies using HasStringBody trait
- [https://docs.saloon.dev/the-basics/request-body-data/stream-body] Use these docs for sending stream/file resource request bodies using HasStreamBody trait
- [https://docs.saloon.dev/the-basics/sending-requests] Use these docs for sending requests through connectors, handling responses, and request lifecycle
- [https://docs.saloon.dev/the-basics/responses] Use these docs for handling responses, accessing response data, status codes, and headers
- [https://docs.saloon.dev/the-basics/handling-failures] Use these docs for handling failed requests, error responses, and using AlwaysThrowOnErrors trait
- [https://docs.saloon.dev/the-basics/debugging] Use these docs for debugging requests and responses, using the debug() method, and inspecting PSR-7 requests
- [https://docs.saloon.dev/the-basics/testing] Use these docs for testing Saloon integrations, mocking requests, and writing assertions

## Digging Deeper
- [https://docs.saloon.dev/digging-deeper/data-transfer-objects] Use these docs for casting API responses into DTOs, creating DTOs from responses, implementing WithResponse interface, and using DTOs in requests
- [https://docs.saloon.dev/digging-deeper/building-sdks] Use these docs for building SDKs with Saloon, creating resource classes, and organizing API integrations
- [https://docs.saloon.dev/digging-deeper/solo-requests] Use these docs for creating standalone requests without connectors using SoloRequest class
- [https://docs.saloon.dev/digging-deeper/retrying-requests] Use these docs for implementing retry logic with exponential backoff and custom retry strategies (v3 includes global retry system at connector level)
- [https://docs.saloon.dev/digging-deeper/delaying-requests] Use these docs for adding delays between requests to prevent rate limiting and server overload
- [https://docs.saloon.dev/digging-deeper/concurrency-and-pools] Use these docs for sending concurrent requests using pools, managing multiple API calls efficiently, and asynchronous request handling
- [https://docs.saloon.dev/digging-deeper/oauth2] Use these docs for OAuth2 authentication flows including Authorization Code Grant, Client Credentials, and token refresh
- [https://docs.saloon.dev/digging-deeper/middleware] Use these docs for creating and using middleware to modify requests and responses, request lifecycle hooks, and boot methods
- [https://docs.saloon.dev/digging-deeper/psr-support] Use these docs for PSR-7 and PSR-17 support, accessing PSR requests and responses, and modifying PSR-7 requests

## Installable Plugins
- [https://docs.saloon.dev/installable-plugins/pagination] Use these docs for the Pagination plugin to handle paginated API responses with various pagination methods (required in v3, optional in v2)
- [https://docs.saloon.dev/installable-plugins/laravel-integration] Use these docs for Laravel plugin features including Artisan commands, facade, events, and HTTP client sender
- [https://docs.saloon.dev/installable-plugins/laravel-integration/testing] Use these docs for testing Saloon integrations with Laravel, mocking requests, and using the Saloon facade
- [https://docs.saloon.dev/installable-plugins/caching-responses] Use these docs for the Caching plugin to cache API responses and improve performance
- [https://docs.saloon.dev/installable-plugins/rate-limit-handler] Use these docs for the Rate Limit Handler plugin to prevent and manage rate limits
- [https://docs.saloon.dev/installable-plugins/sdk-generator] Use these docs for the Auto SDK Generator plugin to generate Saloon SDKs from OpenAPI files or Postman collections
- [https://docs.saloon.dev/installable-plugins/lawman] Use these docs for the Lawman plugin, a PestPHP plugin for writing architecture tests for API integrations
- [https://docs.saloon.dev/installable-plugins/xml-wrangler] Use these docs for the XML Wrangler plugin for modern XML reading and writing with dot notation and XPath queries
- [https://docs.saloon.dev/installable-plugins/building-your-own-plugins] Use these docs for building custom plugins (traits), creating boot methods, and extending Saloon functionality

## Conclusion
- [https://docs.saloon.dev/conclusion/official-book] Use these docs for information about the official Saloon book
- [https://docs.saloon.dev/conclusion/how-to-guides] Use these docs for how-to guides and tutorials
- [https://docs.saloon.dev/conclusion/tutorials-and-blog-posts] Use these docs for tutorials and blog posts about Saloon
- [https://docs.saloon.dev/conclusion/showcase] Use these docs to see examples of Saloon in production
- [https://docs.saloon.dev/conclusion/known-issues] Use these docs for known issues and workarounds
- [https://docs.saloon.dev/conclusion/credits] Use these docs for credits and acknowledgments
- [https://docs.saloon.dev/conclusion/support-saloon] Use these docs for information on supporting Saloon development
</available-docs>
