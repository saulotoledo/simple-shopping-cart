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
 * @package    Application
 * @subpackage Views_Helpers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * View Helper para impressão de menu em árvore
 * utilizando classes do Twitter Bootstrap.
 *
 * @category   SimpleShoppingCart
 * @package    Application
 * @subpackage Views_Helpers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Zend_View_Helper_PrintMenu extends Zend_View_Helper_Abstract
{
    /**
     * Imprime o menu recursivamente utilizando
     * classes do Twitter Bootstrap.
     *
     * @param array     $menu O menu em formato de árvore.
     * @param Zend_View $view A view da aplicação.
     * @param string    $linkPrefix Prefixo da URL a ser impresso antes do id do item no link.
     * @param boolean   $isChild Identifica se a execução atual é para um item que
     *        não é a raiz. É utilizado durante a recursão.
     * @return string   o HTML resultante do menu.
     */
    public function printMenu($menu, $view, $linkPrefix, $isChild = false)
    {
        $extraClass = ($isChild) ? "tree" : 'bs-sidebar';
        $menuCode = '<ul class="nav nav-list ' . $extraClass . '">'."\n";

        foreach ($menu as $itemId => $itemContent) {

            $menuCode .= "<li>\n";
            $menuPart = '<a href="'. $view->baseUrl() . '/' . $linkPrefix . $itemContent['id'] . '">'
                . ($itemContent['name']) .'</a>';

            if (count($itemContent['childs']) > 0) {
                $menuCode .= '<label class="nav-header"><span class="fa fa-toggle-down tree-toggler"></span>&nbsp;';
                $menuCode .= $menuPart."\n";
                $menuCode .= '</label>';
                $menuCode .= "\n". $this->printMenu($itemContent['childs'], $view, $linkPrefix, true) . "\n";

            } else {
                $menuCode .= $menuPart;
            }

            $menuCode .= "</li>\n";
        }
        $menuCode .= "</ul>\n";

        return $menuCode;
    }
}
