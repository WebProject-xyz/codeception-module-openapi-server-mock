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
    }

    public function testForceStatusCode(AcceptanceTester $I): void
    {
        // /disabled-resource has 404 in spec
        $I->haveOpenApiMockStatusCode(404);
        $I->sendGet('/disabled-resource');
        $I->seeResponseCodeIs(404);
    }

    public function testMockActiveToggle(AcceptanceTester $I): void
    {
        // Default is active
        $I->sendGet('/users');
        $I->seeResponseCodeIs(200);

        $I->setOpenApiMockActive(false);
        // Root path exists in Mezzio
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendGet('/');
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
