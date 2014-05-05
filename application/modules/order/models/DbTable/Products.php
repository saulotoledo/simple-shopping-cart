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
 * Tabela de produtos cadastrados no sistema.
 *
 * @category   SimpleShoppingCart
 * @package    Order_Model
 * @subpackage Order_Model_DbTable
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_DbTable_Products extends STLib_Model_DbTable_PrefixedDbTable
{
    /**
     * O nome da tabela (padrão do Zend Framework).
     *
     * @var string
     */
    protected $_name = 'products';

    /**
     * O nome da tabela que associa produtos a categorias.
     *
     * @var string
     */
    protected $_categoriesAssocTableName = 'product_categories_assoc';

    /**
     * O campo primário da tabela (padrão do Zend
     * Framework).
     *
     * @var string
     */
    protected $_primary = 'id';

    /**
     * As tabelas dependentes (padrão do Zend Framework).
     *
     * @var array
     */
    protected $_dependentTables = array(
        "Order_Model_DbTable_ProductCategoriesAssoc",
        "Order_Model_DbTable_ProductFeaturesAssoc"
    );

    /**
     * Retorna o nome desta tabela.
     */
    public function getTableName()
    {
        return $this->_name;
    }

    /**
     * Retorna o nome da tabela que associa produtos
     * a categorias.
     *
     * @return string O nome da tabela que associa
     *         produtos a categorias.
     */
    public function getCategoriesAssocTableName()
    {
        $prefix = '';
        if (defined('TABLE_PREFIX')) {
            $prefix = TABLE_PREFIX;
        }
        return $prefix . $this->_categoriesAssocTableName;
    }

    /**
     * Retorna um objeto da tabela que associa produtos
     * a categorias.
     *
     * @return Order_Model_DbTable_ProductCategoriesAssoc Um
     *         objeto da tabela que associa produtos a
     *         categorias.
     */
    public function getCategoriesAssocTable()
    {
        return new Order_Model_DbTable_ProductCategoriesAssoc();
    }
}
