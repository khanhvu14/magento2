<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper;

class PostDataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPostData()
    {
        $url = '/controller/sample/action/url/';
        $product = ['product' => new \Magento\Object(['id' => 1])];
        $expected = json_encode([
            'action' => $url,
            'data' => [
                'product' => new \Magento\Object(['id' => 1]),
                \Magento\App\Action\Action::PARAM_NAME_URL_ENCODED =>
                    strtr(base64_encode($url . 'for_uenc'), '+/=', '-_,')
            ]
        ]);

        $contextMock = $this->getMock('Magento\App\Helper\Context', array('getUrlBuilder'), array(), '', false);
        $urlBuilderMock = $this->getMockForAbstractClass(
            'Magento\UrlInterface',
            array(),
            '',
            true,
            true,
            true,
            array('getCurrentUrl')
        );

        $contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilderMock));
        $urlBuilderMock->expects($this->once())
            ->method('getCurrentUrl')
            ->will($this->returnValue($url . 'for_uenc'));

        $model = new \Magento\Core\Helper\PostData($contextMock);

        $actual = $model->getPostData($url, $product);
        $this->assertEquals($expected, $actual);
    }
}
