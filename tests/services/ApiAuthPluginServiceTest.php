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
     */
    public function testAuthenticateKeyShouldReturnFalseWhenKeyExpired()
    {
        $key = 'test123';
        $expires = new DateTime('- 1 minute');

        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(1))
            ->method('__get')
            ->willReturnMap(array(
                array('expires', $expires),
            ));

        $service = $this->setMockApiAuthServiceUserKeyModelByKey($key, $mockUserKeyModel);

        $result = $service->authenticateKey($key);
        $this->assertFalse($result);
    }

    /**
     * @covers ::authenticateKey
     */
    public function testAuthenticateKeyShouldReturnFalseWhenKeyNotFound()
    {
        $key = 'test123';

        $service = $this->setMockApiAuthServiceUserKeyModelByKey($key);

        $result = $service->authenticateKey($key);
        $this->assertFalse($result);
    }

    /**
     * @covers ::authenticateKey
     */
    public function testAuthenticateKeyShouldLoginUserWhenKeyValid()
    {
        $userId = 1;
        $key = 'test123';
        $expires = new DateTime('+ 1 minute');

        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap(array(
                array('expires', $expires),
                array('userId', $userId)
            ));

        $this->setMockUserSessionService($userId);

        $service = $this->setMockApiAuthServiceUserKeyModelByKey($key, $mockUserKeyModel);

        $result = $service->authenticateKey($key);
        $this->assertTrue($result);
    }

    /**
     * @covers ::saveKey
     */
    public function testSaveKeyShouldReturnTrueWhenSavingSucceeds()
    {
        $key = 'test123';
        $userId = 1;

        $mockUser = $this->getMockUser($userId);
        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(1))
            ->method('save')
            ->willReturn(true);

        $service = $this->setMockApiAuthServiceNewUserKeyModel($mockUserKeyModel);

        $result = $service->saveKey($mockUser, $key);

        $this->assertTrue($result);
    }

    /**
     * @covers ::saveKey
     */
    public function testSaveKeyShouldReturnFalseWhenSavingFails()
    {
        $key = 'test123';
        $userId = 1;

        $mockUser = $this->getMockUser($userId);
        $mockUserKeyModel = $this->getMockUserKeyModel();
        $mockUserKeyModel->expects($this->exactly(1))
            ->method('save')
            ->willReturn(false);

        $service = $this->setMockApiAuthServiceNewUserKeyModel($mockUserKeyModel);

        $result = $service->saveKey($mockUser, $key);

        $this->assertFalse($result);
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
     * @param ApiAuth_UserKeyModel $mockUserKeyModel
     * @param string $key
     * @return ApiAuthService|mock
     */
    private function setMockApiAuthServiceUserKeyModelByKey($key, ApiAuth_UserKeyModel $mockUserKeyModel = null)
    {
        $service = $this->getMock('Craft\ApiAuthService', array('getUserKeyModelByKey'));

        $service->expects($this->exactly(1))
            ->method('getUserKeyModelByKey')
            ->with($key)
            ->willReturn($mockUserKeyModel);

        return $service;
    }

    /**
     * @param ApiAuth_UserKeyModel $mockUserKeyModel
     * @return ApiAuthService|mock
     */
    private function setMockApiAuthServiceNewUserKeyModel(ApiAuth_UserKeyModel $mockUserKeyModel)
    {
        $service = $this->getMock('Craft\ApiAuthService', array('getNewUserKeyModel'));

        $service->expects($this->exactly(1))
            ->method('getNewUserKeyModel')
            ->willReturn($mockUserKeyModel);

        return $service;
    }
}
