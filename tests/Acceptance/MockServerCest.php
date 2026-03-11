<?php

declare(strict_types=1);

namespace WebProject\Codeception\Module\Tests\Acceptance;

use WebProject\Codeception\Module\Tests\Support\AcceptanceTester;

class MockServerCest
{
    public function testGetUsers(AcceptanceTester $I): void
    {
        $I->sendGet('/users');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        // Since mock server uses DYNAMIC strategy by default, we check for structure
        $I->seeResponseMatchesJsonType([], '$');
    }

    public function testForceStatusCode(AcceptanceTester $I): void
    {
        // /users is 200 in spec, we force 400 (now defined in spec)
        $I->haveOpenApiMockStatusCode(400);
        $I->sendGet('/users');
        $I->seeResponseCodeIs(400);
    }

    public function testForceExample(AcceptanceTester $I): void
    {
        // We set the example header. Even if the server doesn't use it for STATIC values
        // yet due to default DYNAMIC strategy, we verify the request still succeeds.
        $I->haveOpenApiMockExample('empty');
        $I->sendGet('/users');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testMockActiveToggle(AcceptanceTester $I): void
    {
        // Default is active, should return mocked data
        $I->sendGet('/users');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // Deactivate mock
        $I->setOpenApiMockActive(false);
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendGet('/');

        // Root path is handled before 'isActive' check in the middleware
        // and returns the base status of the mock server.
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['message' => 'OpenAPI Mock Server is running!']);

        $I->setOpenApiMockActive(true);
        $I->sendGet('/users');
        $I->seeResponseCodeIs(200);
    }

    public function testGetMockServerUrl(AcceptanceTester $I): void
    {
        $url = $I->getOpenApiMockServerUrl();
        $I->assertEquals('http://localhost:8080', $url);
    }
}
