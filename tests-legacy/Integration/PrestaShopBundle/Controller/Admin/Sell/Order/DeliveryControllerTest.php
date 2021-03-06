<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace LegacyTests\Integration\PrestaShopBundle\Controller\Admin\Sell\Order;

use LegacyTests\Integration\PrestaShopBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group demo
 */
class DeliveryControllerTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->enableDemoMode();
    }

    public function testSlipAction()
    {
        $this->client->request(
            'GET',
            $this->router->generate(
                'admin_order_delivery_slip'
            )
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testSlipActionWithInvalidData()
    {
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('form');
        $this->client->request(
            'POST',
            $this->router->generate(
                'admin_order_delivery_slip'
            ),
            [
                'form' => [
                    'options' => [
                        'number' => 'foo',
                    ],
                    '_token' => $token,
                ],
            ]
        );
        $response = $this->client->getResponse();
        $this->assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
        $this->assertContains('This value is not valid.', $response->getContent());
    }

    public function testSlipActionWithValidData()
    {
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('form');
        $this->client->request(
            'POST',
            $this->router->generate(
                'admin_order_delivery_slip'
            ),
            [
                'form' => [
                    'options' => [
                        'number' => '100',
                    ],
                    '_token' => $token,
                ],
            ]
        );
        $response = $this->client->getResponse();
        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );

        $this->assertArrayHasKey(
            'success',
            self::$kernel->getContainer()->get('session')->getFlashBag()->all()
        );
    }

    public function testPdfActionWithInvalidData()
    {
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('slip_pdf_form');
        $this->client->request(
            'POST',
            $this->router->generate(
                'admin_order_delivery_slip_pdf'
            ),
            [
                'slip_pdf_form' => [
                    'pdf' => [
                        'date_from' => 'foo',
                    ],
                    '_token' => $token,
                ],
            ]
        );
        $response = $this->client->getResponse();
        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertArrayHasKey(
            'error',
            self::$kernel->getContainer()->get('session')->getFlashBag()->all()
        );
        $this->assertContains('/sell/orders/delivery-slips/?_token', $response->getTargetUrl());
    }

    public function testPdfActionWithEmptyData()
    {
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('slip_pdf_form');
        $this->client->request(
            'POST',
            $this->router->generate(
                'admin_order_delivery_slip_pdf'
            ),
            [
                'slip_pdf_form' => [
                    'pdf' => [],
                    '_token' => $token,
                ],
            ]
        );
        $response = $this->client->getResponse();
        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );

        $this->assertArrayHasKey(
            'error',
            self::$kernel->getContainer()->get('session')->getFlashBag()->all()
        );
        $this->assertContains('/sell/orders/delivery-slips/?_token', $response->getTargetUrl());
    }
}
