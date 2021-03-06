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
namespace Magento\ObjectManager\Config\Mapper;

use Magento\Stdlib\BooleanUtils;

class Dom implements \Magento\Config\ConverterInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ArgumentParser
     */
    private $argumentParser;

    /**
     * @param BooleanUtils $booleanUtils
     * @param ArgumentParser $argumentParser
     */
    public function __construct(BooleanUtils $booleanUtils, ArgumentParser $argumentParser)
    {
        $this->booleanUtils = $booleanUtils;
        $this->argumentParser = $argumentParser;
    }

    /**
     * Convert configuration in DOM format to assoc array that can be used by object manager
     *
     * @param \DOMDocument $config
     * @return array
     * @throws \Exception
     * @todo this method has high cyclomatic complexity in order to avoid performance issues
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function convert($config)
    {
        $output = array();
        /** @var \DOMNode $node */
        foreach ($config->documentElement->childNodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($node->nodeName) {
                case 'preference':
                    $output['preferences'][$node->attributes->getNamedItem(
                        'for'
                    )->nodeValue] = $node->attributes->getNamedItem(
                        'type'
                    )->nodeValue;
                    break;
                case 'type':
                case 'virtualType':
                    $typeData = array();
                    $typeNodeAttributes = $node->attributes;
                    $typeNodeShared = $typeNodeAttributes->getNamedItem('shared');
                    if ($typeNodeShared) {
                        $typeData['shared'] = $this->booleanUtils->toBoolean($typeNodeShared->nodeValue);
                    }
                    if ($node->nodeName == 'virtualType') {
                        $attributeType = $typeNodeAttributes->getNamedItem('type');
                        // attribute type is required for virtual type only in merged configuration
                        if ($attributeType) {
                            $typeData['type'] = $attributeType->nodeValue;
                        }
                    }
                    $typeArguments = array();
                    $typePlugins = array();
                    /** @var \DOMNode $typeChildNode */
                    foreach ($node->childNodes as $typeChildNode) {
                        if ($typeChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        switch ($typeChildNode->nodeName) {
                            case 'arguments':
                                /** @var \DOMNode $argumentNode */
                                foreach ($typeChildNode->childNodes as $argumentNode) {
                                    if ($argumentNode->nodeType != XML_ELEMENT_NODE) {
                                        continue;
                                    }
                                    $argumentName = $argumentNode->attributes->getNamedItem('name')->nodeValue;
                                    $argumentData = $this->argumentParser->parse($argumentNode);
                                    $typeArguments[$argumentName] = $argumentData;
                                }
                                break;
                            case 'plugin':
                                $pluginAttributes = $typeChildNode->attributes;
                                $pluginDisabledNode = $pluginAttributes->getNamedItem('disabled');
                                $pluginSortOrderNode = $pluginAttributes->getNamedItem('sortOrder');
                                $pluginTypeNode = $pluginAttributes->getNamedItem('type');
                                $pluginData = array(
                                    'sortOrder' => $pluginSortOrderNode ? (int)$pluginSortOrderNode->nodeValue : 0
                                );
                                if ($pluginDisabledNode) {
                                    $pluginData['disabled'] = $this->booleanUtils->toBoolean(
                                        $pluginDisabledNode->nodeValue
                                    );
                                }
                                if ($pluginTypeNode) {
                                    $pluginData['instance'] = $pluginTypeNode->nodeValue;
                                }
                                $typePlugins[$pluginAttributes->getNamedItem('name')->nodeValue] = $pluginData;
                                break;
                            default:
                                throw new \Exception(
                                    "Invalid application config. Unknown node: {$typeChildNode->nodeName}."
                                );
                        }
                    }

                    $typeData['arguments'] = $typeArguments;
                    if (!empty($typePlugins)) {
                        $typeData['plugins'] = $typePlugins;
                    }
                    $output[$typeNodeAttributes->getNamedItem('name')->nodeValue] = $typeData;
                    break;
                default:
                    throw new \Exception("Invalid application config. Unknown node: {$node->nodeName}.");
            }
        }

        return $output;
    }
}
