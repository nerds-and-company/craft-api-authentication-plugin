<?php

namespace Craft;

/**
 * Class ApiAuthController
 *
 * Api authentication using user keys
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class ApiAuthController extends BaseController
{
    /** @var bool */
    protected $allowAnonymous = array('authenticate');

    /** @var HttpRequestService  */
    private $requestService;

    /** @var UserSessionService */
    private $userSessionService;

    /** @var ApiAuthService */
    private $apiAuthService;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct($id);

        $this->requestService = craft()->request;
        $this->userSessionService = craft()->userSession;
        $this->apiAuthService = craft()->apiAuth;
    }

    /**
     * Authenticate action.
     */
    public function actionAuthenticate()
    {
        try{
            $this->requirePostRequest();

            $username = $this->requestService->getRequiredPost('username');
            $password = $this->requestService->getRequiredPost('password');

            if($this->userSessionService->login($username, $password)){
                $key = $this->apiAuthService->generateKey();
                $user = $this->userSessionService->getUser();

                if($this->apiAuthService->saveKey($user, $key)){
                    $this->returnJson(array(
                        'key' => $key,
                    ));
                } else {
                    HeaderHelper::setHeader('HTTP/ 500 Internal server error');
                    $this->returnErrorJson(Craft::t('Something went wrong'));
                }
            } else {
                HeaderHelper::setHeader('HTTP/ 401 Bad Credentials');
                $this->returnErrorJson(Craft::t('Invalid username or password'));
            }

        } catch(HttpException $e){
            HeaderHelper::setHeader('HTTP/ ' . $e->statusCode);
            $this->returnErrorJson($e->getMessage());
        }
    }
}
