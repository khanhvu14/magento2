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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cart
     */
    protected $_model;

    /**
     * @var \Magento\Object
     */
    protected $_validItem;

    /**
     * @var \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_salesModel;

    /**
     * @param null|string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_validItem = new \Magento\Object(
            array(
                'parent_item' => null,
                'price' => 2.0,
                'qty' => 3,
                'name' => 'valid item',
                'original_item' => new \Magento\Object(array('base_row_total' => 6.0))
            )
        );
    }

    protected function setUp()
    {
        $this->_salesModel = $this->getMockForAbstractClass(
            'Magento\Payment\Model\Cart\SalesModel\SalesModelInterface'
        );
        $factoryMock = $this->getMock('Magento\Payment\Model\Cart\SalesModel\Factory', array(), array(), '', false);
        $factoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'sales model'
        )->will(
            $this->returnValue($this->_salesModel)
        );
        $eventManagerMock = $this->getMockForAbstractClass('Magento\Event\ManagerInterface');

        $this->_model = new \Magento\Paypal\Model\Cart($factoryMock, $eventManagerMock, 'sales model');
    }

    /**
     * @param array $items
     * @dataProvider invalidGetAllItemsDataProvider
     */
    public function testInvalidGetAllItems($items)
    {
        $taxContainer = new \Magento\Object(
            array('base_hidden_tax_amount' => 0.2, 'base_shipping_hidden_tax_amnt' => 0.1)
        );
        $this->_salesModel->expects($this->once())->method('getTaxContainer')->will($this->returnValue($taxContainer));
        $this->_salesModel->expects($this->once())->method('getAllItems')->will($this->returnValue($items));
        $this->_salesModel->expects($this->once())->method('getBaseSubtotal')->will($this->returnValue(2.1));
        $this->_salesModel->expects($this->once())->method('getBaseTaxAmount')->will($this->returnValue(0.1));
        $this->_salesModel->expects($this->once())->method('getBaseShippingAmount')->will($this->returnValue(1.1));
        $this->_salesModel->expects($this->once())->method('getBaseDiscountAmount')->will($this->returnValue(0.3));
        $this->assertEmpty($this->_model->getAllItems());
        $this->assertEquals(2.1, $this->_model->getSubtotal());
        $this->assertEquals(0.1 + 0.2 + 0.1, $this->_model->getTax());
        $this->assertEquals(1.1, $this->_model->getShipping());
        $this->assertEquals(0.3, $this->_model->getDiscount());
    }

    public function invalidGetAllItemsDataProvider()
    {
        return array(
            array(array()),
            array(
                array(
                    new \Magento\Object(
                        array('parent_item' => new \Magento\Object(), 'price' => 2.0, 'qty' => 3, 'name' => 'item 1')
                    )
                )
            ),
            array(
                array(
                    $this->_validItem,
                    new \Magento\Object(
                        array(
                            'price' => 2.0,
                            'qty' => 3,
                            'name' => 'item 2',
                            'original_item' => new \Magento\Object(array('base_row_total' => 6.01))
                        )
                    )
                )
            ),
            array(
                array(
                    $this->_validItem,
                    new \Magento\Object(
                        array(
                            'price' => sqrt(2),
                            'qty' => sqrt(2),
                            'name' => 'item 3',
                            'original_item' => new \Magento\Object(array('base_row_total' => 2))
                        )
                    )
                )
            )
        );
    }

    /**
     * @param array $values
     * @param bool $transferDiscount
     * @dataProvider invalidTotalsGetAllItemsDataProvider
     */
    public function testInvalidTotalsGetAllItems($values, $transferDiscount)
    {
        $expectedSubtotal = $this->_prepareInvalidModelData($values, $transferDiscount);
        $this->assertEmpty($this->_model->getAllItems());
        $this->assertEquals($expectedSubtotal, $this->_model->getSubtotal());
        $this->assertEquals(
            $values['base_tax_amount'] + $values['base_hidden_tax_amount'] + $values['base_shipping_hidden_tax_amnt'],
            $this->_model->getTax()
        );
        $this->assertEquals($values['base_shipping_amount'], $this->_model->getShipping());
        $this->assertEquals($transferDiscount ? 0.0 : $values['base_discount_amount'], $this->_model->getDiscount());
    }

    public function invalidTotalsGetAllItemsDataProvider()
    {
        return array(
            array(
                array(
                    'base_hidden_tax_amount' => 0,
                    'base_shipping_hidden_tax_amnt' => 0,
                    'base_subtotal' => 0,
                    'base_tax_amount' => 0,
                    'base_shipping_amount' => 0,
                    'base_discount_amount' => 6.1,
                    'base_grand_total' => 0
                ),
                false
            ),
            array(
                array(
                    'base_hidden_tax_amount' => 1,
                    'base_shipping_hidden_tax_amnt' => 2,
                    'base_subtotal' => 3,
                    'base_tax_amount' => 4,
                    'base_shipping_amount' => 5,
                    'base_discount_amount' => 100,
                    'base_grand_total' => 5.5
                ),
                true
            )
        );
    }

    public function testGetAllItems()
    {
        $totals = $this->_prepareValidModelData();
        $this->assertEquals(
            array(
                new \Magento\Object(
                    array(
                        'name' => $this->_validItem->getName(),
                        'qty' => $this->_validItem->getQty(),
                        'amount' => $this->_validItem->getPrice()
                    )
                )
            ),
            $this->_model->getAllItems()
        );
        $this->assertEquals($totals['subtotal'], $this->_model->getSubtotal());
        $this->assertEquals($totals['tax'], $this->_model->getTax());
        $this->assertEquals($totals['shipping'], $this->_model->getShipping());
        $this->assertEquals($totals['discount'], $this->_model->getDiscount());
    }

    /**
     * @param array $values
     * @param bool $transferDiscount
     * @param bool $transferShipping
     * @dataProvider invalidGetAmountsDataProvider
     */
    public function testInvalidGetAmounts($values, $transferDiscount, $transferShipping)
    {
        $expectedSubtotal = $this->_prepareInvalidModelData($values, $transferDiscount);
        if ($transferShipping) {
            $this->_model->setTransferShippingAsItem();
        }
        $result = $this->_model->getAmounts();
        $expectedSubtotal += $this->_model->getTax();
        $expectedSubtotal += $values['base_shipping_amount'];
        if (!$transferDiscount) {
            $expectedSubtotal -= $this->_model->getDiscount();
        }
        $this->assertEquals(array(Cart::AMOUNT_SUBTOTAL => $expectedSubtotal), $result);
    }

    public function invalidGetAmountsDataProvider()
    {
        $data = array();
        $invalidTotalsData = $this->invalidTotalsGetAllItemsDataProvider();
        foreach ($invalidTotalsData as $dataItem) {
            $data[] = array($dataItem[0], $dataItem[1], true);
            $data[] = array($dataItem[0], $dataItem[1], false);
        }
        return $data;
    }

    /**
     * Prepare invalid data for cart
     *
     * @param array $values
     * @param bool $transferDiscount
     * @return float
     */
    protected function _prepareInvalidModelData($values, $transferDiscount)
    {
        $taxContainer = new \Magento\Object(
            array(
                'base_hidden_tax_amount' => $values['base_hidden_tax_amount'],
                'base_shipping_hidden_tax_amnt' => $values['base_shipping_hidden_tax_amnt']
            )
        );
        $expectedSubtotal = $values['base_subtotal'];
        if ($transferDiscount) {
            $this->_model->setTransferDiscountAsItem();
            $expectedSubtotal -= $values['base_discount_amount'];
        }
        $this->_salesModel->expects($this->once())->method('getTaxContainer')->will($this->returnValue($taxContainer));
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getAllItems'
        )->will(
            $this->returnValue(array($this->_validItem))
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseSubtotal'
        )->will(
            $this->returnValue($values['base_subtotal'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseTaxAmount'
        )->will(
            $this->returnValue($values['base_tax_amount'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseShippingAmount'
        )->will(
            $this->returnValue($values['base_shipping_amount'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseDiscountAmount'
        )->will(
            $this->returnValue($values['base_discount_amount'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getDataUsingMethod'
        )->with(
            'base_grand_total'
        )->will(
            $this->returnValue($values['base_grand_total'])
        );
        return $expectedSubtotal;
    }

    public function testGetAmounts()
    {
        $totals = $this->_prepareValidModelData();
        $this->assertEquals($totals, $this->_model->getAmounts());
    }

    /**
     * Prepare valid cart data
     *
     * @return array
     */
    protected function _prepareValidModelData()
    {
        $totals = array('discount' => 0.1, 'shipping' => 0.2, 'subtotal' => 0.3, 'tax' => 0.4);
        $taxContainer = new \Magento\Object(
            array('base_hidden_tax_amount' => 0, 'base_shipping_hidden_tax_amnt' => 0)
        );
        $this->_salesModel->expects($this->once())->method('getTaxContainer')->will($this->returnValue($taxContainer));
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getAllItems'
        )->will(
            $this->returnValue(array($this->_validItem))
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseSubtotal'
        )->will(
            $this->returnValue($totals['subtotal'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseTaxAmount'
        )->will(
            $this->returnValue($totals['tax'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseShippingAmount'
        )->will(
            $this->returnValue($totals['shipping'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getBaseDiscountAmount'
        )->will(
            $this->returnValue($totals['discount'])
        );
        $this->_salesModel->expects(
            $this->once()
        )->method(
            'getDataUsingMethod'
        )->with(
            'base_grand_total'
        )->will(
            $this->returnValue(6.0 + $totals['tax'] + $totals['shipping'] - $totals['discount'])
        );
        return $totals;
    }
}
