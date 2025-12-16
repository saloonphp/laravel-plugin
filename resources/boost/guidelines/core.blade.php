## SaloonPHP

- SaloonPHP is a PHP library for building beautiful, maintainable API integrations and SDKs with a fluent, expressive API.
- Uses a connector-based architecture where **Connectors** define the base URL and shared configuration, and **Requests** represent specific API endpoints.
- **Version Support**: SaloonPHP v2 and v3 are both actively supported. Check `composer.json` to determine which version-specific documentation to reference.
- Always use Artisan commands to generate SaloonPHP classes: `php artisan saloon:connector`, `php artisan saloon:request`, `php artisan saloon:response`, `php artisan saloon:plugin`, `php artisan saloon:auth`.
- Documentation: `https://docs.saloon.dev`
- **Before implementing features, use the `web-search` tool to get the latest docs. The docs listing is available in <available-docs>**

### Key Concepts

- **Connectors**: Extend `Saloon\Http\Connector`, define base URL via `resolveBaseUrl()`, use constructor property promotion for dependencies, override `defaultHeaders()` and `defaultAuth()`.
- **Requests**: Extend `Saloon\Http\Request`, set `$method` using `Saloon\Enums\Method` enum, override `resolveEndpoint()`, `defaultQuery()`, `defaultHeaders()`, `defaultBody()`.
- **Sending**: `$connector->send($request)` returns a response with methods like `json()`, `body()`, `status()`, `isSuccess()`, `dto()`, `dtoOrFail()`.
- **Body Types**: Implement `HasBody` interface and use traits: `HasJsonBody`, `HasXmlBody`, `HasMultipartBody`, `HasFormBody`, `HasStringBody`, `HasStreamBody`.
- **Authentication**: Use `TokenAuthenticator`, `BasicAuthenticator`, `QueryAuthenticator`, or implement `Saloon\Contracts\Authenticator`.
- **Plugins**: Traits that add reusable functionality. Built-in: `AcceptsJson`, `AlwaysThrowOnErrors`, `HasTimeout`, `HasRetry`, `HasRateLimit`, `WithDebugData`, `DisablesSSLVerification`, `CastsToDto`.
- **Middleware**: Use `middleware()->onRequest()` and `middleware()->onResponse()`, or implement `boot()` method.
- **DTOs**: Implement `createDtoFromResponse()` in request classes, use `$response->dto()` or `$response->dtoOrFail()`.

### Laravel Integration

- **Artisan Commands**: `saloon:connector`, `saloon:request`, `saloon:response`, `saloon:plugin`, `saloon:auth`, `saloon:list`.
- **Facade**: Use `Saloon\Laravel\Facades\Saloon` facade for mocking: `Saloon::fake([RequestClass::class => MockResponse::make(...)])`.
- **Events**: `SendingSaloonRequest` and `SentSaloonRequest` events are emitted during request lifecycle.
- **HTTP Client Sender**: Use `Saloon\Laravel\HttpSender` to integrate with Laravel's HTTP client (enables Telescope recording). Configure in `config/saloon.php`: `'default_sender' => \Saloon\Laravel\HttpSender::class`.
- **File Structure**: Check `config/saloon.php` for `integrations_path` setting. Default is `app/Http/Integrations`. Store connectors/requests in `{integrations_path}/{ServiceName}/` directory.

### Version Notes (v3)

- Global retry system: Set `$tries`, `$retryInterval`, `$useExponentialBackoff` properties directly on connectors/requests.
- Pagination is now a separate installable plugin (required for pagination features).
- Enhanced PSR-7 support with new response methods.

<available-docs>
## Upgrade
- [https://docs.saloon.dev/upgrade/whats-new-in-v3] Use these docs to understand what's new in SaloonPHP v3
- [https://docs.saloon.dev/upgrade/upgrading-from-v2] Use these docs for upgrading from SaloonPHP v2 to v3

## The Basics
- [https://docs.saloon.dev/the-basics/installation] Use these docs for installation instructions, Composer setup, and initial configuration
- [https://docs.saloon.dev/the-basics/connectors] Use these docs for creating connectors, setting base URLs, default headers, and shared configuration
- [https://docs.saloon.dev/the-basics/requests] Use these docs for creating requests, defining endpoints, HTTP methods, query parameters, and request bodies
- [https://docs.saloon.dev/the-basics/authentication] Use these docs for authentication methods including token, basic, OAuth2, and custom authenticators
- [https://docs.saloon.dev/the-basics/request-body-data] Use these docs for sending body data in requests, including JSON, XML, and multipart form data
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
- [https://docs.saloon.dev/digging-deeper/delay] Use these docs for adding delays between requests to prevent rate limiting and server overload
- [https://docs.saloon.dev/digging-deeper/concurrency-and-pools] Use these docs for sending concurrent requests using pools, managing multiple API calls efficiently, and asynchronous request handling
- [https://docs.saloon.dev/digging-deeper/oauth2-authentication] Use these docs for OAuth2 authentication flows including Authorization Code Grant, Client Credentials, and token refresh
- [https://docs.saloon.dev/digging-deeper/middleware] Use these docs for creating and using middleware to modify requests and responses, request lifecycle hooks, and boot methods
- [https://docs.saloon.dev/digging-deeper/psr-support] Use these docs for PSR-7 and PSR-17 support, accessing PSR requests and responses, and modifying PSR-7 requests

## Installable Plugins
- [https://docs.saloon.dev/installable-plugins/pagination] Use these docs for the Pagination plugin to handle paginated API responses with various pagination methods (required in v3, optional in v2)
- [https://docs.saloon.dev/installable-plugins/laravel-integration] Use these docs for Laravel plugin features including Artisan commands, facade, events, and HTTP client sender
- [https://docs.saloon.dev/installable-plugins/caching-responses] Use these docs for the Caching plugin to cache API responses and improve performance
- [https://docs.saloon.dev/installable-plugins/handling-rate-limits] Use these docs for the Rate Limit Handler plugin to prevent and manage rate limits
- [https://docs.saloon.dev/installable-plugins/sdk-generator] Use these docs for the Auto SDK Generator plugin to generate Saloon SDKs from OpenAPI files or Postman collections
- [https://docs.saloon.dev/installable-plugins/lawman] Use these docs for the Lawman plugin, a PestPHP plugin for writing architecture tests for API integrations
- [https://docs.saloon.dev/installable-plugins/xml-wrangler] Use these docs for the XML Wrangler plugin for modern XML reading and writing with dot notation and XPath queries
- [https://docs.saloon.dev/installable-plugins/building-your-own-plugins] Use these docs for building custom plugins (traits), creating boot methods, and extending Saloon functionality
</available-docs>
