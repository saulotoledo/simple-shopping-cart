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
 * Controller de autenticação.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Controllers
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_AuthController extends Zend_Controller_Action
{
    /**
     * Ação básica do controller. Redireciona para o login.
     */
    public function indexAction()
    {
        $this->_forward('login', 'auth', 'auth');
    }

    /**
     * Ação de login na aplicação. Valida o login e, em caso
     * de sucesso, encaminha o usuário para seu destino.
     */
    public function loginAction()
    {
        if (isset($this->view->loginForm)) {
            $loginForm = $this->view->loginForm;
        } else {
            $loginForm = new Auth_Form_Login();
        }

        if ($this->_request->isPost() && $this->_request->getParam('loginsubmit') != null) {

            $formData = $this->_request->getPost();
            if ($loginForm->isValid($formData)) {

                $filter = new Zend_Filter_StripTags();
                $login = $filter->filter($this->_request->getPost('login'));
                $password = $filter->filter($this->_request->getPost('password'));

                $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'));
                $authAdapter
                    ->setTableName(TABLE_PREFIX . 'users')
                    ->setIdentityColumn('login')
                    ->setCredentialColumn('password')
                    ->setCredentialTreatment('MD5(?)')
                    ->setIdentity($login)
                    ->setCredential($password)
                ;

                $auth = Auth_Model_SystemAuth::getInstance()->setSessionTimeout(
                    (int) Zend_Registry::get('systemConfig')->session->timeout
                );
                $authResult = $auth->authenticate($authAdapter);

                switch ($authResult->getCode()) {

                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        $this->view->messageType = 'danger';
                        $this->view->message = Zend_Registry::get('translate')->_('AUTH_FAILURE_IDENTITY_NOT_FOUND');
                        break;

                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $this->view->messageType = 'danger';
                        $this->view->message = Zend_Registry::get('translate')->_('AUTH_FAILURE_CREDENTIAL_INVALID');
                        break;

                    case Zend_Auth_Result::FAILURE:
                        $this->view->messageType = 'danger';
                        $error_messages = $authResult->getMessages();
                        if ($error_messages[0] == Zend_Registry::get('translate')->_('AUTH_FAILURE_USER_INACTIVE')) {
                            $this->view->message = implode(' ', $authResult->getMessages());
                        } else {
                            $this->view->message = Zend_Registry::get('translate')->_('AUTH_GENERAL_FAILURE');
                        }
                        break;

                    case Zend_Auth_Result::SUCCESS:

                        $targetModule = 'default';
                        $targetController = 'index';
                        $targetAction = 'index';
                        $params = $this->_request->getParam('params');

                        if ($params != null) {

                            foreach ($params as $paramName => $paramValue) {
                                if ($paramName == 'module') {
                                    $targetModule = $paramValue;
                                } elseif ($paramName == 'controller') {
                                    $targetController = $paramValue;
                                } elseif ($paramName == 'action') {
                                    $targetAction = $paramValue;
                                } else {
                                    $this->_request->setParam($paramName, $paramValue);
                                }
                            }
                        }

                        $this->_forward($targetAction, $targetController, $targetModule);
                        break;

                    default:
                        $this->view->messageType = 'danger';
                        $this->view->message = Zend_Registry::get('translate')->_('AUTH_GENERAL_FAILURE');
                }
            } else {
                $loginForm->populate($formData);
            }
        }

        $this->view->form = $loginForm;
    }

    /**
     * Ação de logout da aplicação. Fecha a sessão do usuário.
     */
    public function logoutAction()
    {
        Auth_Model_SystemAuth::getInstance()->clearIdentity();
        $this->_helper->redirector('show', 'products', 'order');
    }
}
