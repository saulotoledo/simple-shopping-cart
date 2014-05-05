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
 * Mapeia endereços do usuário para o Banco de Dados.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_UserAddressMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva um usuário. Se não existe, é adicionado ao
     * Banco de Dados. Se já existe, é atualizado.
     *
     * @param  Order_Model_UserAddress $address O usuário a salvar.
     */
    public function save($address)
    {
        $data = array(
            'user_id'      => $address->getUserId(),
            'main'         => $address->getMain(),
            'street'       => $address->getStreet(),
            'number'       => $address->getNumber(),
            'complement'   => $address->getComplement(),
            'neighborhood' => $address->getNeighborhood(),
            'city'         => $address->getCity(),
            'state'        => $address->getState(),
            'cep'          => $address->getCep()
        );

        if (null === ($id = $address->getId())) {
            $id = $this->getDbTable()->insert($data);
            $address->setId($id);
        } else {
            $data['id'] = $address->getId();
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /**
     * Encontra um usuário com o ID informado e o coloca
     * em um objeto usuário informado.
     *
     * @param  int $id O ID do usuário a ser localizado.
     * @param  Order_Model_UserAddress $emptyUserAddress Um objeto do
     *         tipo Order_Model_UserAddress vazio para ser preenchido
     *         com os dados da busca. Se este objeto tiver dados,
     *         eles serão sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($id, $emptyUserAddress)
    {
        $result = $this->getDbTable()->find($id);

        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $emptyUserAddress
            ->setId((int) $row->id)
            ->setUserId((int) $row->user_id)
            ->setMain((bool) $row->main)
            ->setStreet($row->street)
            ->setNumber((int) $row->number)
            ->setComplement($row->complement)
            ->setNeighborhood($row->neighborhood)
            ->setCity($row->city)
            ->setState($row->state)
            ->setCep($row->cep)
        ;

        return true;
    }

    /**
     * Procura o endereço principal do usuário com ID informado.
     *
     * @param  int $userId O ID do usuário a buscar o endereço
     *         principal.
     * @param  Order_Model_UserAddress $emptyUserAddress O objeto
     *         a preencher com os dados da busca.
     * @return boolean true se o endereço foi encontrado, false
     *         caso contrário.
     */
    public function findMainAddressOf($userId, $emptyUserAddress)
    {
        $select = $this->getDbTable()
            ->select()
            ->from($this->getDbTable(), 'id')
            ->where('user_id = ?', $userId)
            ->where('main = ?', true)
        ;
        $resultSet = $this->getDbTable()->fetchRow($select);

        if ($resultSet) {
            $id = $resultSet->__get('id');
            return $this->find($id, $emptyUserAddress);
        }

        return false;
    }

    /**
     * Retorna a tabela de endereços associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Order_Model_DbTable_UserAddresses A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Order_Model_DbTable_UserAddresses');
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
