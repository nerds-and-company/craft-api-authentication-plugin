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
    protected $allowAnonymous = array('authenticate', 'resetPassword');

    /**
     * Set cors headers and check for options request
     */
    public function init()
    {
        craft()->apiAuth->setCorsHeaders();
        if (craft()->apiAuth->isOptionsRequest()) {
            craft()->end();
        }
    }

    /**
     * Authenticate action.
     */
    public function actionAuthenticate()
    {

        try {
            $this->requirePostRequest();

            $username = craft()->request->getRequiredPost('username');
            $password = craft()->request->getRequiredPost('password');

            if (craft()->userSession->login($username, $password)) {
                $key = craft()->apiAuth->generateKey();
                $user = craft()->userSession->getUser();

                if (craft()->apiAuth->saveKey($user, $key)) {
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

        } catch (HttpException $e) {
            HeaderHelper::setHeader('HTTP/ ' . $e->statusCode);
            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Forgot password action
     */
    public function actionResetPassword()
    {

        try {
            $this->requirePostRequest();

            $username = craft()->request->getRequiredPost('username');
            $user = craft()->users->getUserByUsernameOrEmail($username);

            if ($user) {
                craft()->users->sendPasswordResetEmail($user);
            }

            $this->returnJson(Craft::t('Email has been sent if address exists'));

        } catch (HttpException $e) {
            HeaderHelper::setHeader('HTTP/ ' . $e->statusCode);
            $this->returnErrorJson($e->getMessage());
        }
    }
}
