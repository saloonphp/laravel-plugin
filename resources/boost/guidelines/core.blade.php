@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp

## SaloonPHP

- PHP library for building API integrations and SDKs.
- Documentation: `https://docs.saloon.dev`
- Use `web-search` tool for latest docs before implementing.
- Always use Artisan commands to generate SaloonPHP classes: `{{ $assist->artisanCommand('saloon:connector') }}`, `{{ $assist->artisanCommand('saloon:request') }}`, `{{ $assist->artisanCommand('saloon:response') }}`, `{{ $assist->artisanCommand('saloon:plugin') }}`, `{{ $assist->artisanCommand('saloon:auth') }}`.
