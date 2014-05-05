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
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Definição de uma característica de produto.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_ProductFeature extends STLib_Model_Model
{
    /**
     * Um ID único para controle da característica.
     *
     * @var int
     */
    protected $id = null;

    /**
     * O nome da característica.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Retorna o objeto como string.
     *
     * @return string O objeto como string.
     */
    public function __toString()
    {
        return sprintf("%s", $this->getName());
    }
}
