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
 * @coversDefaultClass Craft\ApiAuthController
 * @covers ::<!public>
 */
class ApiAuthControllerTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        //Plugin classes
        require_once __DIR__ . '/../../controllers/ApiAuthController.php';
        require_once __DIR__ . '/../../services/ApiAuthService.php';
//        require_once __DIR__ . '/../../models/ApiAuth_UserKeyModel.php';
//        require_once __DIR__ . '/../../records/ApiAuth_UserKeyRecord.php';
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::actionAuthenticate
     */
    public function testApiAuthControllerAuthenticateShouldReturnErrorWhenNotPostRequest()
    {
        $apiAuthController = $this->getMockApiAuthController('returnErrorJson', '');

        $this->setMockRequestService('GET');

        $apiAuthController->actionAuthenticate();
    }

    /**
     * @covers ::actionAuthenticate
     */
    public function testApiAuthControllerAuthenticateShouldReturnBadCredentialsWhenLoginFails()
    {
        $username = 'username';
        $password = 'test123';
        $errorMessage = Craft::t('Invalid username or password');

        $this->setMockRequestService('POST', $username, $password);
        $this->setMockUserSessionService($username, $password, false);

        $apiAuthController = $this->getMockApiAuthController('returnErrorJson', $errorMessage);

        $apiAuthController->actionAuthenticate();
    }

    /**
     * @covers ::actionAuthenticate
     */
    public function testApiAuthControllerAuthenticateShouldReturnInternalServerErrorWhenKeyCannotBeSaved()
    {
        $username = 'username';
        $password = 'test123';
        $key = 'averynicekey';
        $errorMessage = Craft::t('Something went wrong');

        $mockUser = $this->getMockUser();
        $this->setMockRequestService('POST', $username, $password);
        $this->setMockUserSessionService($username, $password, true, $mockUser);
        $this->setMockApiAuthService($key, $mockUser, false);

        $apiAuthController = $this->getMockApiAuthController('returnErrorJson', $errorMessage);

        $apiAuthController->actionAuthenticate();
    }

    /**
     * @covers ::actionAuthenticate
     */
    public function testApiAuthControllerAuthenticateShouldReturnKey()
    {
        $username = 'username';
        $password = 'test123';
        $key = 'averynicekey';

        $mockUser = $this->getMockUser();
        $this->setMockRequestService('POST', $username, $password);
        $this->setMockUserSessionService($username, $password, true, $mockUser);
        $this->setMockApiAuthService($key, $mockUser, true);

        $apiAuthController = $this->getMockApiAuthController('returnJson', array('key' => $key));

        $apiAuthController->actionAuthenticate();
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $method
     * @param mixed $param
     * @param mixed $return
     * @return ApiAuthController|mock
     */
    private function getMockApiAuthController($method, $param, $return = null)
    {
        $apiAuthController = $this->getMockBuilder('Craft\ApiAuthController')
            ->disableOriginalConstructor()
            ->setMethods(array($method))
            ->getMock();

        $apiAuthController->expects($this->exactly(1))
            ->method($method)
            ->with($param)
            ->willReturn($return);

        return $apiAuthController;
    }

    /**
     * @return UserModel|mock
     */
    private function getMockUser()
    {
        $mockUser = $this->getMockBuilder('Craft\UserModel')
            ->disableOriginalConstructor()
            ->getMock();

        return $mockUser;
    }

    /**
     * @param string $requestType
     * @param string $username
     * @param string $password
     *
     * @return UserPermissionsService|mock
     */
    private function setMockRequestService($requestType, $username = null, $password = null)
    {
        $mockRequestService = $this->getMockBuilder('Craft\HttpRequestService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestService->expects($this->exactly(1))
            ->method('getRequestType')
            ->willReturn($requestType);

        if ($username && $password) {
            $mockRequestService->expects($this->exactly(2))
                ->method('getRequiredPost')
                ->willReturnMap(array(
                    array('username', $username),
                    array('password', $password),
                ));
        }

        $this->setComponent(craft(), 'request', $mockRequestService);

        return $mockRequestService;
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $success
     * @param UserModel $mockUser
     *
     * @return UserSessionService|mock
     */
    private function setMockUserSessionService($username, $password, $success = true, UserModel $mockUser = null)
    {
        $mockUserSessionService = $this->getMockBuilder('Craft\UserSessionService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockUserSessionService->expects($this->exactly(1))
            ->method('login')
            ->with($username, $password)
            ->willReturn($success);

        if ($mockUser) {
            $mockUserSessionService->expects($this->exactly(1))
                ->method('getUser')
                ->willReturn($mockUser);
        }

        $this->setComponent(craft(), 'userSession', $mockUserSessionService);

        return $mockUserSessionService;
    }

    /**
     * @param string $key
     * @param UserModel $mockUser
     * @param bool $success
     *
     * @return mock|ApiAuthService
     */
    private function setMockApiAuthService($key, UserModel $mockUser, $success)
    {
        $mockApiAuthService = $this->getMockBuilder('Craft\ApiAuthService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockApiAuthService->expects($this->exactly(1))
            ->method('generateKey')
            ->willReturn($key);

        $mockApiAuthService->expects($this->exactly(1))
            ->method('saveKey')
            ->with($mockUser, $key)
            ->willReturn($success);

        $this->setComponent(craft(), 'apiAuth', $mockApiAuthService);

        return $mockApiAuthService;
    }


}
