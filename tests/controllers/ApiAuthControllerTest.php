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
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::actionAuthenticate
     */
    public function testApiAuthControllerAuthenticateShouldReturnErrorWhenNotPostRequest()
    {
        $this->setSimpleMockApiAuthService();
        $this->setMockRequestService('GET');

        $apiAuthController = $this->getMockApiAuthController('returnErrorJson', '');

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

        $this->setSimpleMockApiAuthService();
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

        $apiAuthController = $this->getMockApiAuthController('returnJson', array(
            'key' => $key,
            'user' => array(
                'username' => $mockUser->username,
                'photo' => $mockUser->photo,
                'firstName' => $mockUser->firstName,
                'lastName' => $mockUser->lastName,
                'email' => $mockUser->email,
            ),
        ));

        $apiAuthController->actionAuthenticate();
    }

    /**
     * @covers ::actionResetPassword
     */
    public function testApiAuthControllerResetPasswordShouldReturnErrorWhenNotPostRequest()
    {
        $this->setSimpleMockApiAuthService();
        $this->setMockRequestService('GET');

        $apiAuthController = $this->getMockApiAuthController('returnErrorJson', '');

        $apiAuthController->actionResetPassword();
    }

    /**
     * @covers ::actionResetPassword
     */
    public function testApiAuthControllerResetPasswordShouldReturnSuccessMessageWhenUserNotFound()
    {
        $username = 'username';
        $message = array('message' => Craft::t('Email has been sent if address exists'));

        $this->setSimpleMockApiAuthService();
        $this->setMockUsersService($username);
        $this->setMockRequestService('POST', $username, null, 1);

        $apiAuthController = $this->getMockApiAuthController('returnJson', $message);

        $apiAuthController->actionResetPassword();
    }

    /**
     * @covers ::actionResetPassword
     */
    public function testApiAuthControllerResetPasswordShouldSendMailWhenUserFound()
    {
        $username = 'username';
        $message = array('message' => Craft::t('Email has been sent if address exists'));

        $this->setSimpleMockApiAuthService();
        $this->setMockRequestService('POST', $username, null, 1);

        $mockUser = $this->getMockUser();
        $mockUsersService = $this->setMockUsersService($username, $mockUser);

        $mockUsersService->expects($this->exactly(1))
            ->method('sendPasswordResetEmail')
            ->with($mockUser);

        $apiAuthController = $this->getMockApiAuthController('returnJson', $message);

        $apiAuthController->actionResetPassword();
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
     * @param int $count
     *
     * @return UserPermissionsService|mock
     */
    private function setMockRequestService($requestType, $username = null, $password = null, $count = 2)
    {
        $mockRequestService = $this->getMockBuilder('Craft\HttpRequestService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestService->expects($this->exactly(1))
            ->method('getRequestType')
            ->willReturn($requestType);

        if ($username !== null || $password !== null) {
            $mockRequestService->expects($this->exactly($count))
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
     * @return mock|ApiAuthService
     */
    private function setSimpleMockApiAuthService()
    {
        $mockApiAuthService = $this->getMockBuilder('Craft\ApiAuthService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setComponent(craft(), 'apiAuth', $mockApiAuthService);

        return $mockApiAuthService;
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
        $mockApiAuthService = $this->setSimpleMockApiAuthService();

        $mockApiAuthService->expects($this->exactly(1))
            ->method('generateKey')
            ->willReturn($key);

        $mockApiAuthService->expects($this->exactly(1))
            ->method('saveKey')
            ->with($mockUser, $key)
            ->willReturn($success);


        return $mockApiAuthService;
    }

    /**
     * @param string $username
     * @param UserModel $user
     * @return mock|UsersService
     */
    private function setMockUsersService($username, UserModel $user = null)
    {
        $mockUsersService = $this->getMockBuilder('Craft\UsersService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockUsersService->expects($this->exactly(1))
            ->method('getUserByUsernameOrEmail')
            ->with($username)
            ->willReturn($user);

        $this->setComponent(craft(), 'users', $mockUsersService);

        return $mockUsersService;
    }
}
