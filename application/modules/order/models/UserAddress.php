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
 * Definição de um endereço de usuário.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_UserAddress extends STLib_Model_Model
{
    /**
     * Um ID único para controle do endereço.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Um ID do usuário dono deste endereço.
     *
     * @var int
     */
    protected $userId = null;

    /**
     * Armazena se este endereço é o principal do usuário.
     *
     * @var boolean
     */
    protected $main = null;

    /**
     * A rua do endereço.
     *
     * @var string
     */
    protected $street = null;

    /**
     * O número do endereço.
     *
     * @var int
     */
    protected $number = null;

    /**
     * O complemento do endereço.
     *
     * @var string
     */
    protected $complement = null;

    /**
     * O bairro do endereço.
     *
     * @var string
     */
    protected $neighborhood = null;

    /**
     * A cidade do endereço.
     *
     * @var string
     */
    protected $city = null;

    /**
     * O estado do endereço.
     *
     * @var string
     */
    protected $state = null;

    /**
     * O CEP do endereço.
     *
     * @var string
     */
    protected $cep = null;

    /**
     * Preenche este objeto com o endereço principal do
     * usuário com ID informado.
     *
     * @param  int $userId O ID do usuário a buscar o endereço
     *         principal.
     * @return Order_Model_UserAddress O próprio objeto, ou null
     *         se o endereço ainda não existir.
     */
    public function findMainAddressOf($userId)
    {
        $result = $this->getMapper()->findMainAddressOf($userId, $this);
        if ($result) {
            return $this;
        }
        return null;
    }
}
