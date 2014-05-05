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
class Order_Model_DbTable_Orders extends STLib_Model_DbTable_PrefixedDbTable
{
    /**
     * O nome da tabela (padrão do Zend Framework).
     *
     * @var string
     */
    protected $_name = 'orders';

    /**
     * O nome da tabela que contém os itens desta compra.
     *
     * @var string
     */
    protected $_orderItemsTableName = 'order_items';

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
        "Order_Model_DbTable_OrderItems"
    );

    /**
     * O mapa que associa esta tabela às outras
     * (padrão do Zend Framework).
     *
     * @var array
     */
    //TODO: Rever acoplamento entre esta tabela e a do pacote auth abaixo. Esta conexão nao deveria estar aqui:
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'),
            'refTableClass' => 'Auth_Model_DbTable_Users',
            'refColumns'    => array('id'),
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::RESTRICT
        ),
        'ShipingAddress' => array(
            'columns'       => array('shipping_address_id'),
            'refTableClass' => 'Order_Model_DbTable_UserAddresses',
            'refColumns'    => array('id'),
            //TODO: Revisar remoção de endereços ao remover pedidos:
            'onDelete'      => self::RESTRICT,
            'onUpdate'      => self::RESTRICT
        ),
    );

    /**
     * Retorna o nome desta tabela;
     */
    public function getTableName()
    {
        return $this->_name;
    }

    /**
     * Retorna o nome da tabela de itens de compra,
     * dependente desta.
     *
     * @return string O nome da tabela de itens de compra.
     */
    public function getOrderItemsTableName()
    {
        $prefix = '';
        if (defined('TABLE_PREFIX')) {
            $prefix = TABLE_PREFIX;
        }
        return $prefix . $this->_orderItemsTableName;
    }

    /**
     * Retorna um objeto da tabela de itens de compra.
     * @return Order_Model_DbTable_OrderItems Um objeto
     *         da tabela de itens de compra.
     */
    public function getOrderItemsTableTable()
    {
        return new Order_Model_DbTable_OrderItems();
    }
}
