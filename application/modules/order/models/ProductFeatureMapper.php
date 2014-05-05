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
 * Mapeia características de produto para o Banco de Dados.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_ProductFeatureMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva uma característica. Se não existe, é adicionada ao
     * Banco de Dados. Se já existe, é atualizada.
     *
     * @param Order_Model_ProductFeature $productFeature A característica
     *        de produto a salvar.
     */
    public function save($productFeature)
    {
        $data = array(
            'name'      => $productFeature->getDescription()
        );

        if (null === ($id = $productFeature->getId())) {
            $id = $this->getDbTable()->insert($data);
            $productFeature->setId($id);
        } else {
            $data['id'] = $productFeature->getId();
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /**
     * Encontra uma característica de produto com o ID informado e o coloca
     * em um objeto informado.
     *
     * @param  int $id O ID da característica de produto a ser localizado.
     * @param  Order_Model_ProductFeature $emptyProductFeature Um objeto do
     *         tipo Order_Model_ProductFeature vazio para ser preenchido
     *         com os dados da busca. Se este objeto tiver dados, eles
     *         serão sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($id, $emptyProductFeature)
    {
        $result = $this->getDbTable()->find($id);

        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $emptyProductFeature
            ->setId((int) $row->id)
            ->setName($row->name)
        ;

        return true;
    }

    /**
     * Retorna a tabela de características de produto associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Order_Model_DbTable_ProductFeatures A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Order_Model_DbTable_ProductFeatures');
        }
        return $this->_dbTable;
    }

    /**
     * {@inheritDoc}
     */
    protected function __applyViewFilters($select, $filters)
    {
        if (isset($filters['expr1'])) {
            $select
                ->where(
                    "name LIKE '%{$filters['expr1']}%'"
                )
            ;
        }
    }
}
