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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System config email field backend model
 */
namespace Magento\Backend\Model\Config\Backend\Email;

use Magento\Core\Exception;

class Address extends \Magento\Core\Model\Config\Value
{
    /**
     * @return $this
     * @throws Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (!\Zend_Validate::is($value, 'EmailAddress')) {
            throw new Exception(__('Please correct the email address: "%1".', $value));
        }
        return $this;
    }
}
