<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\SaloonLaravel\Managers\MockManager;

test('you can create sequence mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);

    Saloon::fake([$responseA, $responseB]);

    $mockManager = MockManager::resolve();

    expect($mockManager->getNextFromSequence())->toEqual($responseA);
    expect($mockManager->getNextFromSequence())->toEqual($responseB);
});

test('you can create wildcard url mocks', function () {
    $mockManager = MockManager::resolve();

    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['name' => 'Elsa'], 200);

    Saloon::fake([
        'github.com/*' => $responseA,
        'abc.com/exact' => $responseB,
        '*' => $responseC,
    ]);
});

test('you can create connector mocks', function () {

});

test('you can create explicit request mocks', function () {

});
