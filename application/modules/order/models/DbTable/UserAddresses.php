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
 * Tabela de endereços de usuário cadastrados no sistema.
 *
 * @category   SimpleShoppingCart
 * @package    Order_Model
 * @subpackage Order_Model_DbTable
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_DbTable_UserAddresses extends STLib_Model_DbTable_PrefixedDbTable
{
    /**
     * O nome da tabela (padrão do Zend Framework).
     *
     * @var string
     */
    protected $_name = 'user_addresses';

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
        "Order_Model_DbTable_Orders"
    );
}
