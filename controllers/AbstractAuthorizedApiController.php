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
abstract class AbstractAuthorizedApiController extends BaseController
{
    /**
     * Allow anonymous access to this controller.
     *
     * @var bool
     */
    protected $allowAnonymous = true;

    /**
     * Initialize controller
     */
    public function init()
    {
        craft()->apiAuth->setCorsHeaders();
        if (craft()->apiAuth->isOptionsRequest()) {
            craft()->end();
        }
        $this->authorizeApi();
    }

    /**
     * @return bool
     */
    private function authorizeApi()
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401);
            $this->returnErrorJson(Craft::t('Authorization header missing'));
        }
        $key = $_SERVER['HTTP_AUTHORIZATION'];
        list($bearer, $token) = explode(' ', $key);
        if ($bearer != 'Bearer' || !craft()->apiAuth->authenticateKey($token)) {
            http_response_code(401);
            $this->returnErrorJson(Craft::t('Invalid key used'));
        }
        return true;
    }
}
