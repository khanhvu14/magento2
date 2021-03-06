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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\TestFramework\Bootstrap\Profiler.
 */
namespace Magento\Test\Bootstrap;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler
     */
    protected $_object;

    /**
     * @var \Magento\Profiler\Driver\Standard|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_driver;

    protected function setUp()
    {
        $this->expectOutputString('');
        $this->_driver = $this->getMock('Magento\Profiler\Driver\Standard', array('registerOutput'));
        $this->_object = new \Magento\TestFramework\Bootstrap\Profiler($this->_driver);
    }

    protected function tearDown()
    {
        $this->_driver = null;
        $this->_object = null;
    }

    public function testRegisterFileProfiler()
    {
        $this->_driver->expects(
            $this->once()
        )->method(
            'registerOutput'
        )->with(
            $this->isInstanceOf('Magento\Profiler\Driver\Standard\Output\Csvfile')
        );
        $this->_object->registerFileProfiler('php://output');
    }

    public function testRegisterBambooProfiler()
    {
        $this->_driver->expects(
            $this->once()
        )->method(
            'registerOutput'
        )->with(
            $this->isInstanceOf('Magento\TestFramework\Profiler\OutputBamboo')
        );
        $this->_object->registerBambooProfiler('php://output', __DIR__ . '/_files/metrics.php');
    }
}
