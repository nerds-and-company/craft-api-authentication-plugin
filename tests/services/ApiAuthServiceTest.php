<?php

namespace Craft;

use \PHPUnit_Framework_MockObject_MockObject as mock;

/**
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @coversDefaultClass Craft\ApiAuthService
 * @covers ::<!public>
 */
class ApiAuthServiceTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        //Plugin classes
        require_once __DIR__ . '/../../services/ApiAuthService.php';
        require_once __DIR__ . '/../../models/ApiAuth_UserKeyModel.php';
        require_once __DIR__ . '/../../records/ApiAuth_UserKeyRecord.php';
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::generateKey
     */
    public function testGenerateKeyShouldReturnRandomKey()
    {
        $service = new ApiAuthService();
        $token1 = $service->generateKey();
        $token2 = $service->generateKey();

        $this->assertNotSame($token1, $token2);
    }

    /**
     * @covers ::authenticateKey
     * @dataProvider provideInvalidUserKeyModelAttributes
     *
     * @param string $key
     * @param int $userId
     * @param DateTime $expires
     */
    public function testAuthenticateKeyShouldReturnFalseWhenKeyExpired($key, $userId, DateTime $expires)
    {
        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(1))
            ->method('__get')
            ->willReturnMap(array(
                array('expires', $expires),
            ));

        $service = $this->setMockApiAuthService('getUserKeyModelByKey', $key, $mockUserKeyModel);

        $result = $service->authenticateKey($key);
        $this->assertFalse($result);
    }

    /**
     * @covers ::authenticateKey
     * @dataProvider provideValidUserKeyModelAttributes
     *
     * @param string $key
     */
    public function testAuthenticateKeyShouldReturnFalseWhenKeyNotFound($key)
    {
        $service = $this->setMockApiAuthService('getUserKeyModelByKey', $key);

        $result = $service->authenticateKey($key);
        $this->assertFalse($result);
    }

    /**
     * @covers ::authenticateKey
     * @dataProvider provideValidUserKeyModelAttributes
     *
     * @param string $key
     * @param int $userId
     * @param DateTime $expires
     */
    public function testAuthenticateKeyShouldLoginUserWhenKeyValid($key, $userId, DateTime $expires)
    {
        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap(array(
                array('expires', $expires),
                array('userId', $userId)
            ));

        $this->setMockUserSessionService($userId);

        $service = $this->setMockApiAuthService('getUserKeyModelByKey', $key, $mockUserKeyModel);

        $result = $service->authenticateKey($key);
        $this->assertTrue($result);
    }

    /**
     * @covers ::saveKey
     * @dataProvider provideValidUserKeyModelAttributes
     *
     * @param string $key
     * @param int $userId
     */
    public function testSaveKeyShouldReturnTrueWhenSavingSucceeds($key, $userId)
    {
        $mockUser = $this->getMockUser($userId);
        $mockUserKeyModel = $this->setMockUserKeyModelSaveExpectation(true);
        $service = $this->setMockApiAuthService('getNewUserKeyModel', 'skip', $mockUserKeyModel);

        $result = $service->saveKey($mockUser, $key);

        $this->assertTrue($result);
    }

    /**
     * @covers ::saveKey
     * @dataProvider provideInvalidUserKeyModelAttributes
     *
     * @param string $key
     * @param int $userId
     */
    public function testSaveKeyShouldReturnFalseWhenSavingFails($key, $userId)
    {
        $mockUser = $this->getMockUser($userId);
        $mockUserKeyModel = $this->setMockUserKeyModelSaveExpectation(false);
        $service = $this->setMockApiAuthService('getNewUserKeyModel', 'skip', $mockUserKeyModel);

        $result = $service->saveKey($mockUser, $key);

        $this->assertFalse($result);
    }

    /**
     * @covers ::isOptionsRequest
     */
    public function testIsOptionsRequestShouldReturnFalseByDefault()
    {
        $service = new ApiAuthService();

        $result = $service->isOptionsRequest();

        $this->assertFalse($result);
    }

    /**
     * @covers ::isOptionsRequest
     */
    public function testIsOptionsRequestShouldReturnTrueWhenOptionsRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $service = new ApiAuthService();

        $result = $service->isOptionsRequest();

        $this->assertTrue($result);
    }

    /**
     * @covers ::setCorsHeaders
     */
    public function testSetCorsHeaderShouldSetCorsHeaders()
    {
        /** @var ApiAuthService|mock $service */
        $service = $this->getMock('Craft\ApiAuthService', array('setHeader'));

        $service->expects($this->exactly(2))
            ->method('setHeader')
            ->withConsecutive(
                array('Access-Control-Allow-Headers', 'Authorization'),
                array('Access-Control-Allow-Origin', '*')
            );

        $service->setCorsHeaders();
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideValidUserKeyModelAttributes()
    {
        return array(
            'valid key' => array(
                'key' => 'test123',
                'userId' => 1,
                'expires' => new DateTime('+ 1 minute'),
            )
        );
    }

    /**
     * @return array
     */
    public function provideInvalidUserKeyModelAttributes()
    {
        return array(
            'invalid key' => array(
                'key' => 'anotherkey',
                'userId' => 2,
                'expires' => new DateTime('- 1 minute'),
            )
        );
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param int $userId
     * @param bool $success
     * @return UserSessionService|mock
     */
    private function setMockUserSessionService($userId, $success = true)
    {
        $mockUserSessionService = $this->getMockBuilder('Craft\UserSessionService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserSessionService->expects($this->exactly(1))
            ->method('loginByUserId')
            ->with($userId)
            ->willReturn($success);

        $this->setComponent(craft(), 'userSession', $mockUserSessionService);

        return $mockUserSessionService;
    }

    /**
     * @return ApiAuth_UserKeyModel|mock
     */
    private function getMockUserKeyModel()
    {
        $mockUserKeyModel = $this->getMockBuilder('Craft\ApiAuth_UserKeyModel')
            ->disableOriginalConstructor()
            ->getMock();

        return $mockUserKeyModel;
    }

    /**
     * @param int $userId
     * @return UserModel|mock
     */
    private function getMockUser($userId)
    {
        $mockUser = $this->getMockBuilder('Craft\UserModel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockUser->expects($this->exactly(1))
            ->method('getAttribute')
            ->with('id')
            ->willReturn($userId);

        return $mockUser;
    }

    /**
     * @param array $methodName
     * @param mixed $param
     * @param ApiAuth_UserKeyModel $mockUserKeyModel
     * @return ApiAuthService|mock
     */
    private function setMockApiAuthService($methodName, $param, ApiAuth_UserKeyModel $mockUserKeyModel = null)
    {
        $service = $this->getMock('Craft\ApiAuthService', array($methodName));

        $method = $service->expects($this->exactly(1))->method($methodName);
        if ($param !== 'skip') {
            $method->with($param);
        }
        $method->willReturn($mockUserKeyModel);

        return $service;
    }

    /**
     * @param $success
     * @return ApiAuth_UserKeyModel|mock
     */
    private function setMockUserKeyModelSaveExpectation($success)
    {
        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(1))
            ->method('save')
            ->willReturn($success);
        return $mockUserKeyModel;
    }
}
