@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp

## SaloonPHP

- PHP library for building API integrations and SDKs.
- Documentation: https://docs.saloon.dev
- Check `composer.json` for version (v2, v3 or v4). Use `web-search` tool for latest docs before implementing.
- If the project uses v3, inform the user that v3 is affected by three published security issues and prefer upgrading to v4 before adding new Saloon work, unless the user explicitly needs a v3-compatible change.
- Always use Artisan commands to generate SaloonPHP classes: `{{ $assist->artisanCommand('saloon:connector') }}`, `{{ $assist->artisanCommand('saloon:request') }}`, `{{ $assist->artisanCommand('saloon:response') }}`, `{{ $assist->artisanCommand('php artisan saloon:plugin') }}`, `{{ $assist->artisanCommand('saloon:auth') }}`.- Documentation: `https://docs.saloon.dev`
- IMPORTANT: Activate saloon-development skill when working with SaloonPHP-related tasks.
