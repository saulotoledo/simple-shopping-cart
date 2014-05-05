<?php
/**
 * LICENSE
 *
 * This source file is subject to the BSD 3-Clause license.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to saulotoledo@gmail.com so we can send you a copy immediately.
 *
 * @category   SimpleShoppingCart
 * @package    Order_Model
 * @subpackage Order_Model_DbTable
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Tabela que associa produtos a suas categorias.
 *
 * @category   SimpleShoppingCart
 * @package    Order_Model
 * @subpackage Order_Model_DbTable
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_DbTable_ProductCategoriesAssoc extends STLib_Model_DbTable_PrefixedDbTable
{
    /**
     * O nome da tabela (padrão do Zend Framework).
     *
     * @var string
     */
    protected $_name = 'product_categories_assoc';

    /**
     * O mapa que associa esta tabela às outras
     * (padrão do Zend Framework).
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Product' => array(
            'columns'       => array('product_id'),
            'refTableClass' => 'Order_Model_DbTable_Products',
            'refColumns'    => array('id'),
            'onDelete'      => self::RESTRICT,
            'onUpdate'      => self::RESTRICT
        ),
        'Category' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'Order_Model_DbTable_ProductCategories',
            'refColumns'    => 'id',
            'onDelete'      => self::RESTRICT,
            'onUpdate'      => self::RESTRICT
        )
    );

    /**
     * O campo primário da tabela (padrão do Zend
     * Framework).
     *
     * @var string
     */
    protected $_primary = 'id';
}
