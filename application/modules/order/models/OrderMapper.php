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
 * Mapeia pedidos para o Banco de Dados.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_OrderMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva um pedido. Se não existe, é adicionado ao
     * Banco de Dados. Se já existe, é atualizado.
     *
     * @param Order_Model_Order $order O pedido a salvar.
     */
    public function save($order)
    {
        // A data é sempre salva no fuso GMT, de modo a permitir conversões facilmente:
        $data = array(
            'user_id'             => $order->getUserId(),
            'shipping_address_id' => $order->getShippingAddressId(),
            'datetime'            => $this->zendDateToGmtString($order->getDatetime())
        );

        if (null === ($id = $order->getId())) {
            $id = $this->getDbTable()->insert($data);
            $order->setId($id);
        } else {

            $data['id'] = $id;
            $this->getDbTable()->update($data, array('id = ?' => $id));

            // Remove itens porque eles serão adicionados (atualizados) a seguir.
            // Evita ter que verificar um a um, suas quantidades, se foram
            // removidos ou adicionados:
            $this->getDbTable()->getOrderItemsTableTable()->fetchRow(array('order_id = ?' => $id))->delete();
        }

        if (count($order->getProducts() > 0)) {
            foreach ($order->getProducts() as $itemId => $itemQuantity) {
                $this->getDbTable()->getOrderItemsTableTable()->insert(array(
                    'order_id'   => $id,
                    'product_id' => $itemId,
                    'quantity'   => $itemQuantity
                ));
            }
        }
    }

    /**
     * Encontra um pedido com o ID informado e o coloca
     * em um objeto pedido informado.
     *
     * @param  int $id O ID do pedido a ser localizado.
     * @param  Order_Model_Order $emptyOrder Um objeto do tipo
     *         Order_Model_Order vazio para ser preenchido
     *         com os dados da busca. Se este objeto tiver dados,
     *         eles serão sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($id, $emptyOrder)
    {
        $result = $this->getDbTable()->find($id);

        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $emptyOrder
            ->setId((int) $row->id)
            ->setUserId($row->user_id)
            ->setShippingAddressId($row->shipping_address_id)
            ->setDatetime($this->gmtStringToZendDate($row->datetime))
        ;

        $orderItems = $row->findDependentRowset('Order_Model_DbTable_OrderItems');
        $productList = array();
        foreach ($orderItems as $item) {
            $emptyOrder->setProduct($item->product_id, $item->quantity);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll($orderBy = array(), $page = 1, $rowCount = null, $filters = array())
    {
        $orders = parent::fetchAll($orderBy, $page, $rowCount, $filters);

        foreach ($orders as $order) {
            $orderItems = $this->getDbTable()->getOrderItemsTableTable()->fetchRow(array('order_id = ?' => $order->getId()));

            foreach ($orderItems as $item) {
                $order->setProduct($item->product_id, $item->quantity);
            }
        }

        return $orders;
    }

    /**
     * Retorna a tabela de pedidos associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Auth_Model_DbTable_Users A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Order_Model_DbTable_Orders');
        }
        return $this->_dbTable;
    }

    /**
     * {@inheritDoc}
     */
    protected function __applyViewFilters($select, $filters)
    {
    }
}
