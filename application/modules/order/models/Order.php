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
 * Definição de um pedido de produtos.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_Order extends STLib_Model_Model
{
    /**
     * Um ID único para controle do pedido.
     *
     * @var int
     */
    protected $id = null;

    /**
     * O ID do usuário responsável pelo pedido.
     *
     * @var int
     */
    protected $userId = null;

    /**
     * O ID do endereço de entrega do pedido.
     *
     * @var int
     */
    protected $shippingAddressId = null;

    /**
     * Data e hora da realização do pedido.
     *
     * @var Zend_Date
     */
    protected $datetime = null;

    /**
     * Lista de produtos do pedido
     * (lista de IDs e quantidades).
     *
     * @var array
     */
    protected $_productList = null;

    /**
     * Adiciona ou, se já existir, altera um produto na
     * lista deste pedido.
     *
     * @param  int $productId O ID do produto a adicionar.
     * @param  int $productQuantity A quantidade de itens do produto informado.
     * @return Order_Model_Order O próprio objeto.
     */
    public function setProduct($productId, $productQuantity)
    {
        //TODO: Adicionar verificação de produtos inexistentes (IDs inválidos).
        if ($productQuantity > 0) {
            $this->_productList[$productId] = $productQuantity;
        }

        return $this;
    }

    /**
     * Remove um produto da lista, se existir.
     *
     * @param  int $productId O ID do produto a remover da lista.
     * @return Order_Model_Order O próprio objeto.
     */
    public function removeProduct($productId)
    {
        unset($this->_productList[$productId]);
        return $this;
    }

    /**
     * Verifica se um produto existe na lista deste pedido.
     *
     * @param  int $productId O ID do produto a verificar.
     * @return boolean true se existir, false caso contrário.
     */
    public function productExists($productId)
    {
        return array_key_exists($productId, $this->_productList);
    }

    /**
     * Retorna a quantidade de itens de um produto neste pedido.
     *
     * @param  int $productId O ID do produto a verificar.
     * @return int A quantidade de itens solicitada.
     */
    public function getProductQuantity($productId)
    {
        if ($this->productExists($productId)) {
            return (int) $this->_productList[$productId];
        }
        return 0;
    }

    /**
     * Retorna a lista de produtos.
     *
     * @return array A lista de produtos, ou null se ela não
     *         foi inicializada.
     */
    public function getProducts()
    {
        return $this->_productList;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        if ($this->getDatetime() == null) {
            $this->setDatetime(
                new Zend_Date()
            );
        }

        parent::save();
    }
}
