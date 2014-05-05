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
 * @subpackage Controllers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Controller básico do módulo.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Controllers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_IndexController extends Zend_Controller_Action
{
    /**
     * Ação básica do controller. Redireciona para o login.
     */
    public function indexAction()
    {
        return $this->_helper->redirector('login', 'auth', 'auth');
    }
}
