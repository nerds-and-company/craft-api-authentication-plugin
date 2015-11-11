<?php

namespace Craft;

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
class ApiAuthServiceTest extends \Craft\BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../services/ApiAuthService.php';
    }

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
}
