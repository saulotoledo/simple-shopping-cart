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
 * Definição de uma categoria de produto.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_ProductCategory extends STLib_Model_Model
{
    /**
     * Um ID único para controle da categoria.
     *
     * @var int
     */
    protected $id = null;

    /**
     * O id da categoria pai.
     *
     * @var int
     */
    protected $parentId = null;

    /**
     * O nome da categoria.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Faz a montagem recursiva da árvore de categorias.
     * É uma função de uso interno.
     *
     * @param  array $parentsAndChilds Array no formato array([IdDoPai] => [Filhos deste ID]).
     *         Cada [Filho deste ID] é um array no formato [IdDoItem] => [Conteúdo do item].
     *         Utilizado para mover os blocos de menu com facilidade.
     * @param  array $childs Os filhos do nível chamado a partir do qual a montagem deve se iniciar.
     * @return array Retorna as categorias em formato de árvore, considerando a raiz o nível onde
     *         os dados do parâmetro "childs" estão.
     */
    private static function recursiveCategories($parentsAndChilds, $childs)
    {
        $categoriesArray = $childs;

        foreach ($childs as $childId => $childContent) {
            $categoriesArray[$childId]['childs'] = array();

            if (isset($parentsAndChilds[$childId])) {
                $categoriesArray[$childId]['childs'] = Order_Model_ProductCategory::recursiveCategories(
                    $parentsAndChilds,
                    $parentsAndChilds[$childId]
                );
            }
        }

        return $categoriesArray;
    }

    /**
     * Méodo auxiliar que retorna array com as categorias
     * pai e seus filhos.
     *
     * @return array Categorias pai e seus filhos no formato
     *         ([IdDoPai] => [Filhos deste ID]).
     */
    //TODO: Este comportamento como estático não está bom. Repensar.
    private static function getParentsAndChilds()
    {
        $model = new Order_Model_ProductCategory();
        $itens = $model->getMapper()->fetchAll();

        // Gera um array com o formato array([IdDoPai] => [Filhos deste ID]).
        // Cada [Filho deste ID] é um array no formato [IdDoItem] => [Conteúdo do item]
        $parentsAndChilds = array();
        foreach ($itens as $item) {
            if (!isset($parentsAndChilds[$item->getParentId()])) {
                $parentsAndChilds[$item->getParentId()] = array();
            }

            $parentsAndChilds[$item->getParentId()][$item->getId()] = array(
                'id' => (int) $item->getId(),
                'parent_id' => (int) $item->getParentId(),
                'name' => $item->getName()
            );
        }

        return $parentsAndChilds;
    }

    /**
     * Retorna todas as categorias em formato de árvore de array.
     *
     * @return array Um array de arrays montando uma árvore com
     *         as cagegorias de produtos cadastradas no sistema.
     */
    public static function fetchTree()
    {
        $parentsAndChilds = Order_Model_ProductCategory::getParentsAndChilds();

        $categoriesArray = array();

        // O menu só pode ser montado se existe uma raiz:
        if (isset($parentsAndChilds[0])) {
            $categoriesArray = Order_Model_ProductCategory::recursiveCategories(
                $parentsAndChilds,
                $parentsAndChilds[0]
            );
        }

        return $categoriesArray;
    }

    /**
     * Método auxiliar para busca recursiva de descendentes
     * de uma categoria informada.
     *
     * @param  unknown $catId A categoria a buscar os descendentes.
     * @param  unknown $parentsAndChilds Array de IDs de categorias
     *         no formato ([IdDoPai] => [Filhos deste ID]).
     * @return array Lista de IDs de categorias descendentes.
     */
    private function recursiveDescendantsSearchOf($catId, $parentsAndChilds)
    {
        $descendants = array();
        if (array_key_exists($catId, $parentsAndChilds)) {
            $childIds = array_keys($parentsAndChilds[$catId]);

            $descendants = array_merge(
                $descendants,
                $childIds
            );

            if (count($childIds) > 0) {
                foreach ($childIds as $childId) {
                    $descendants = array_merge(
                        $descendants,
                        $this->recursiveDescendantsSearchOf($childId, $parentsAndChilds)
                    );
                }
            }
        }
        return $descendants;
    }

    /**
     * Retorna os descendentes desta categoria.
     * @return array Lista de IDs de categorias descendentes
     *         desta categoria.
     */
    public function getDescendants()
    {
        $parentsAndChilds = Order_Model_ProductCategory::getParentsAndChilds();

        $catId = $this->getId();
        $descendants = $this->recursiveDescendantsSearchOf($catId, $parentsAndChilds);

        return $descendants;
    }

    /**
     * Retorna o objeto Order_Model_ProductCategory
     * da categoria pai da atual.
     *
     * @return Order_Model_ProductCategory O objeto da categoria pai.
     */
    public function getParent()
    {
        $parentCategory = new Order_Model_ProductCategory();
        return $parentCategory->find($this->getParentId());
    }

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
