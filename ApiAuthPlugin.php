<?php

namespace Craft;

/**
 * ApiAuth Plugin.
 *
 * Authentication plugin for an api
 *
 * @author    Nerds and company
 * @copyright Copyright (c) 2015, Nerds and company
 *
 * @link      http://www.itmundi.nl
 */
class ApiAuthPlugin extends BasePlugin
{

    /**
     * Return plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Api Authentication');
    }

    /**
     * Return plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '0.1';
    }

    /**
     * Return developer name.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Nerds and company';
    }

    /**
     * Return developer url.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://www.nerds.company';
    }

    /**
     * Register site routes.
     *
     * @return array
     */
    public function registerSiteRoutes()
    {
        return array(
            'api/authenticate' => array('action' => 'apiAuth/authenticate'),
            'api/resetPassword' => array('action' => 'apiAuth/resetPassword')
        );
    }
}
