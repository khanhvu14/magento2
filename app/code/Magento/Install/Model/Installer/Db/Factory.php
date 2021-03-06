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
 * @package     Magento_Install
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Installer DB factory
 */
namespace Magento\Install\Model\Installer\Db;

class Factory
{
    /**
     * @var array
     */
    protected $_types = array('mysql4' => 'Magento\Install\Model\Installer\Db\Mysql4');

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get Installer Db type instance
     *
     * @param string $type
     * @return \Magento\Install\Model\Installer\Db\AbstractDb | bool
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        if (!empty($type) && isset($this->_types[(string)$type])) {
            return $this->_objectManager->get($this->_types[(string)$type]);
        }
        return false;
    }
}
