<?php

namespace Craft;

/**
 * Class ApiAuthService.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class ApiAuthService extends BaseApplicationComponent
{
    /**
     * @param string $key
     * @return bool
     */
    public function authenticateKey($key)
    {
        $userKeyModel = $this->getUserKeyModelByKey($key);
        if($userKeyModel && $userKeyModel->expires > new DateTime()) {
            return craft()->userSession->loginByUserId($userKeyModel->userId);
        }
        return false;
    }

    /**
     * @return string
     */
    public function generateKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    /**
     * @param UserModel $user
     * @param $key
     * @return bool
     */
    public function saveKey(UserModel $user, $key)
    {
        $model = $this->getNewUserKeyModel();
        $model->userId = $user->getAttribute('id');
        $model->key = $key;
        $model->expires = new DateTime('+ 1 week');
        return $model->save();
    }

    /**
     * @codeCoverageIgnore
     *
     * @return ApiAuth_UserKeyModel
     */
    protected function getNewUserKeyModel()
    {
        return new ApiAuth_UserKeyModel();
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $key
     * @return ApiAuth_UserKeyModel
     */
    protected function getUserKeyModelByKey($key)
    {
        return ApiAuth_UserKeyRecord::model()->findByAttributes(array('key' => $key));
    }
}
