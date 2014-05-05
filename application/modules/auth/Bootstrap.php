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
 * @package    Auth
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Bootstrap do módulo.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Carrega e registra os plugins citados abaixo da aplicação.
     */
    protected function _initPlugins()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Auth_Plugin_ManageAuthPlugin());
    }
}
