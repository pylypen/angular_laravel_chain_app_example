<?php 

class LoginUserCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
    }

    // tests
    public function tryToTest(ApiTester $I)
    {

        $I->sendPOST('/login', [
            'email' => 'U1@M.COM',
            'password' => 'secret'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }
}
