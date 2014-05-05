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
 * Mapeia produtos para o Banco de Dados.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_ProductMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva um produto. Se não existe, é adicionado ao
     * Banco de Dados. Se já existe, é atualizado.
     *
     * @param  Order_Model_Product $product O produto a salvar.
     */
    public function save($product)
    {
        $data = array(
            'name'        => $product->getName(),
            'description' => $product->getDescription(),
            'image'       => $product->getImage(),
            'price'       => $product->getPrice(),
            'categories'  => $product->getCategories(),
            'features'    => $product->getFeatures()
        );

        if (null === ($id = $product->getId())) {
            $id = $this->getDbTable()->insert($data);
            $product->setId($id);
        } else {
            $data['id'] = $product->getId();
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /**
     * Encontra um produto com o ID informado e o coloca
     * em um objeto Order_Model_Product informado.
     *
     * @param  int $id O ID do produto a ser localizado.
     * @param  Order_Model_Product $emptyProduct Um objeto do tipo
     *         Order_Model_Product vazio para ser preenchido
     *         com os dados da busca. Se este objeto tiver dados,
     *         eles serão sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($id, $emptyProduct)
    {
        $result = $this->getDbTable()->find($id);

        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $emptyProduct
            ->setId((int) $row->id)
            ->setName($row->name)
            ->setDescription($row->description)
            ->setImage($row->image)
            ->setPrice((float) $row->price)
            ->setCategoryIds($this->getCategoryIdsOf((int) $row->id))
            ->setFeatureIds($this->getFeatureIdsOf((int) $row->id))
        ;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll($orderBy = array(), $page = 1, $rowCount = null, $filters = array())
    {
        $items = parent::fetchAll($orderBy, $page, $rowCount, $filters);
        foreach ($items as $item) {
            $item
                ->setCategoryIds($this->getCategoryIdsOf((int) $item->getId()))
                ->setFeatureIds($this->getFeatureIdsOf((int) $item->getId()))
            ;
        }

        return $items;
    }


    /**
     * Retorna um array de IDs das categorias que
     * o produto com o ID informado pertence.
     *
     * @param  int   $productId O ID do produto.
     * @return array Um array de inteiros com os IDs das categorias.
     */
    public function getCategoryIdsOf($productId)
    {
        return $this->getFieldListFromDependentTable(
            $productId,
            'Order_Model_DbTable_ProductCategoriesAssoc',
            'Product',
            'category_id'
        );
    }

    /**
     * Retorna um array de IDs das características que
     * o produto com o ID informado pertence.
     *
     * @param  int $productId O ID do produto.
     * @return array Um array de inteiros com os IDs das características.
     */
    public function getFeatureIdsOf($productId)
    {
        return $this->getFieldListFromDependentTable(
            $productId,
            'Order_Model_DbTable_ProductFeaturesAssoc',
            'Product',
            'feature_id'
        );
    }

    /**
     * Retorna uma lista de valores de uma tabela dependente.
     *
     * @param  int    $productId O ID do produto.
     * @param  string $dependentTableName O nome da tabela dependente.
     * @param  string $rule A regra do array $_referenceMap da classe
     *         da tabela dependente.
     * @param  string $columnName O nome da coluna a retornar.
     * @return array  Um array com a lista desejada.
     */
    private function getFieldListFromDependentTable($productId, $dependentTableName, $rule, $columnName)
    {
        if (is_null($productId)) {
            return array();
        }
        $product = $this->getDbTable()->fetchAll(
            $this->getDbTable()->select()->where("id = $productId")
        );

        $productAssocInfo = $product->current()->findDependentRowset($dependentTableName, $rule);

        $result = array();
        foreach ($productAssocInfo as $key => $info) {
            array_push($result, $info[$columnName]);
        }

        return $result;
    }

    /**
     * Retorna a tabela de produtos associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Order_Model_DbTable_Products A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Order_Model_DbTable_Products');
        }
        return $this->_dbTable;
    }

    /**
     * Retorna os IDs das categorias filhas de uma outra informada.
     *
     * @return array IDs das categorias filhas de uma outra informada.
     */
    private function getDescendantCategoryIdsOf($categoryId)
    {
        $category = new Order_Model_ProductCategory();
        $descendants = $category
            ->find($categoryId)
            ->getDescendants()
        ;

        return $descendants;
    }

    /**
     * {@inheritDoc}
     */
    protected function __applyViewFilters($select, $filters)
    {
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {

            $categoryAndDescendants = array_merge(
                array($filters['category_id']),
                $this->getDescendantCategoryIdsOf($filters['category_id'])
            );

            $select
                ->setIntegrityCheck(false)
                ->from(
                    array($this->getDbTable()->getCategoriesAssocTableName()),
                    array('_product_id' => 'product_id')
                )
                ->group($this->getDbTable()->getCategoriesAssocTableName() .'.product_id')
                ->joinLeft(
                    $this->getDbTable()->getTableName(),
                    $this->getDbTable()->getTableName().'.id = '. $this->getDbTable()->getCategoriesAssocTableName() .'.product_id',
                    array($this->getDbTable()->getTableName() .'.*')
                )
                ->where($this->getDbTable()->getCategoriesAssocTableName().".category_id IN (" . implode(', ', $categoryAndDescendants) . ")")
            ;
        }

        if (isset($filters['expr1'])) {
            $select
                ->where(
                    "name LIKE '%{$filters['expr1']}%'
                    OR description LIKE '%{$filters['expr1']}%'"
                )
            ;
        }
    }
}
