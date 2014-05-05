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
 * Mapeia categorias de produto para o Banco de Dados.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_ProductCategoryMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva uma categoria. Se não existe, é adicionada ao
     * Banco de Dados. Se já existe, é atualizada.
     *
     * @param Order_Model_ProductCategory $productCategory A
     *        categoria de produto a salvar.
     */
    public function save($productCategory)
    {
        $data = array(
            'parent_id' => $productCategory->getName(),
            'name'      => $productCategory->getDescription()
        );

        if (null === ($id = $productCategory->getId())) {
            $id = $this->getDbTable()->insert($data);
            $productCategory->setId($id);
        } else {
            $data['id'] = $productCategory->getId();
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /**
     * Encontra uma categoria de produto com o ID informado e o coloca
     * em um objeto informado.
     *
     * @param  int $id O ID da categoria de produto a ser localizado.
     * @param  Order_Model_ProductCategory $emptyProductCategory Um objeto
     *         do tipo Order_Model_ProductCategory vazio para ser preenchido
     *         com os dados da busca. Se este objeto tiver dados, eles serão
     *         sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($id, $emptyProductCategory)
    {
        $result = $this->getDbTable()->find($id);

        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $emptyProductCategory
            ->setId((int) $row->id)
            ->setName($row->name)
            ->setParentId($row->parent_id)
        ;

        return true;
    }

    /**
     * Retorna a tabela de categorias de produto associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Order_Model_DbTable_ProductCategories A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Order_Model_DbTable_ProductCategories');
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
