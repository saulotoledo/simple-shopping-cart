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
 * Definição de um produto.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Order_Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Model_Product extends STLib_Model_Model
{
    /**
     * Um ID único para controle do produto.
     *
     * @var int
     */
    protected $id = null;

    /**
     * O nome do produto.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Descrição do produto.
     *
     * @var string
     */
    protected $description = null;

    /**
     * Caminho da imagem principal do produto.
     *
     * @var string
     */
    protected $image = null;

    /**
     * Preço do produto.
     *
     * @var float
     */
    protected $price = null;

    /**
     * Lista de IDs de categorias do produto.
     *
     * @var array
     */
    protected $categoryIds = null;

    /**
     * Lista de IDs de características do produto.
     *
     * @var array
     */
    protected $featureIds = null;

    /**
     * Retorna as categorias deste produto.
     *
     * @return array Lista de Order_Model_ProductCategory
     *         que representam as categorias deste produto.
     */
    public function getCategories()
    {
        $categories = array();
        if (count($this->getCategoryIds()) > 0) {
            foreach ($this->getCategoryIds() as $catId) {
                $category = new Order_Model_ProductCategory();
                $category->find($catId);
                $categories[] = $category;
            }
        }
        return $categories;
    }

    /**
     * Retorna as características deste produto.
     *
     * @return array Lista de Order_Model_ProductFeature
     *         que representam as características deste produto.
     */
    public function getFeatures()
    {
        $features = array();
        if (count($this->getFeatureIds()) > 0) {
            foreach ($this->getFeatureIds() as $featureId) {
                $feature = new Order_Model_ProductFeature();
                $feature->find($featureId);
                $features[] = $feature;
            }
        }
        return $features;
    }

    /**
     * Retorna o objeto como string.
     *
     * @return string O objeto como string.
     */
    public function __toString()
    {
        return sprintf("%s (%s)", $this->getName(), $this->getLogin());
    }
}
