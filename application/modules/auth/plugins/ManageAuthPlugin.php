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
 * @subpackage Plugins
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Verifica a autenticação do usuário.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Plugins
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Plugin_ManageAuthPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * Armazena a view da aplicação.
     *
     * @var Zend_View
     */
    private $view;

    /**
     * Construtor. Carrega a view da aplicação para acesso pelo plugin.
     */
    public function __construct()
    {
        $this->view =& Zend_Controller_Action_HelperBroker::getStaticHelper('Layout')
            ->getView()
        ;
    }

    /**
     * É chamado antes que uma ação seja expedida pelo Zend Framework.
     * Faz verificações de autenticação, mensagens de login e
     * redirecionamentos segundo uma lista de regras.
     *
     * @param Zend_Controller_Request_Abstract $request O objeto request com os dados
     *          com a ação, o controlador e outros dados necessários ao Zend Framework.
     */
    //TODO: Muito do que é feito aqui deve ser implementado com Zend_Acl:
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $sessionTimeout = (int) Zend_Registry::get('systemConfig')->session->timeout;

        Auth_Model_SystemAuth::getInstance()->setSessionTimeout($sessionTimeout);

        //TODO: A lista a seguir deve fazer parte da implementação do Zend_Acl:
        $sendToLoginPageList = array(
            array('order', 'products', 'addtocart'),
            array('order', 'products', 'removefromcart'),
            array('order', 'products', 'confirmcheckout'),
            array('order', 'products', 'confirmaddresses'),
            array('order', 'products', 'checkout')
        );

        $userHasIdentity = Auth_Model_SystemAuth::getInstance()->hasIdentity();
        $this->view->userHasIdentity = $userHasIdentity;

        if (Auth_Model_SystemAuth::getInstance()->expired()) {
            $this->view->messageType = 'danger';
            $this->view->message = Zend_Registry::get('translate')->_('AUTH_EXPIRED');
        }

        // Para inclusão do menu de autenticação:
        $this->view->addScriptPath(APPLICATION_PATH . '/modules/auth/views/scripts/');

        if (!$userHasIdentity) {

            $this->view->loginForm = new Auth_Form_Login();
            $this->view->loginForm->setAction($this->view->baseUrl() . '/auth/auth');

            //TODO: O comportamento a seguir deve ser manuseado por Auth_Form_Login:
            $formElements = array_keys($this->view->loginForm->getElements());

            $params = $request->getParams();
            if (isset($params['params'])) {
                $params = $params['params'];
            }

            foreach ($params as $paramKey => $paramValue) {

                //TODO: Verificar possibilidade de problemas dado o comportamento a seguir:
                // Se o parâmetro tiver o mesmo nome que um campo do formulário,
                // não será adicionado:
                if (!is_array($paramValue) && !in_array($paramKey, $formElements)) {
                    $this->view->loginForm->addHiddenElement($paramKey, $paramValue, true);
                }
            }

            if (in_array(array($request->getModuleName(), $request->getControllerName(), $request->getActionName()),
                $sendToLoginPageList)
            ) {

                $this->view->message = Zend_Registry::get('translate')->_('AUTH_USER_IS_LOGGED_OUT');
                $this->view->messageType = 'danger';

                if (Auth_Model_SystemAuth::getInstance()->expired()) {
                    $this->view->message .= "<br />" . Zend_Registry::get('translate')->_('AUTH_EXPIRED');
                }

                $this->redirect($request, 'auth', 'auth', 'index');
            }

        } else {

            // Evita que o usuário autenticado abra o form de login:
            if (($request->getControllerName() == 'auth')
            && (($request->getActionName() == 'login') || ($request->getActionName() == 'index'))
            ) {
                $this->redirect($request, 'order', 'products');
            }
        }

        $this->loadAuthGlobalConfigs($request);
    }

    /**
     * Carrega variáveis de configuração do usuário, como por exemplo
     * o "order" das listas de visualização.
     *
     * @param Zend_Controller_Request_Abstract $request O objeto request com os dados
     *          com a ação, o controlador e outros dados necessários ao Zend Framework.
     */
    public function loadAuthGlobalConfigs(Zend_Controller_Request_Abstract $request)
    {
        // A chave que representa o view e a ação atual:
        $key = $request->getModuleName() . '_' . $request->getControllerName() .'_'. $request->getActionName();

        //TODO: Os métodos usados a seguir são muito parecidos e podem ser todos reescritos:
        Auth_Model_SystemAuth::getInstance()->setAuthVariable('limit', $this->__loadLimitParam($key));
        Auth_Model_SystemAuth::getInstance()->setAuthVariable('order', $this->__loadOrderParam($key));
        Auth_Model_SystemAuth::getInstance()->setAuthVariable('viewtype', $this->__loadViewTypeParam($key));
        Auth_Model_SystemAuth::getInstance()->setAuthVariable('filters', $this->__loadFilterParams($key, $request));
    }

    /**
     * Carrega o parâmetro de ordenação dos dados na view.
     *
     * @param  string $key A chave que identifica a view atual (permite
     *         diferentes configurações para diferentes views).
     * @param  string $request O objeto request com os dados
     *           com a ação, o controlador e outros dados necessários ao Zend Framework.
     * @return array Array com valores de ordenação.
     */
    private function __loadOrderParam($key, $request = null)
    {
        $order = Auth_Model_SystemAuth::getInstance()->getAuthVariable('order');

        if (!is_array($order)) {
            $order = array();
        }

        if (!isset($order[$key])) {
            $order[$key] = 'name asc';
        }

        $orderParam = $this->_request->getParam('order', null);
        $orderDirParam = $this->_request->getParam('orderdir', null);
        if (!is_null($orderParam)) {
            if (!is_null($orderDirParam)) {
                $orderParam .= ' ' . $orderDirParam;
            }
            $order[$key] = $orderParam;
        }

        return $order;
    }

    /**
     * Carrega o parâmetro de tipo de visualização dos dados na view.
     *
     * @param  string $key A chave que identifica a view atual (permite
     *         diferentes configurações para diferentes views).
     * @param  string $request O objeto request com os dados
     *           com a ação, o controlador e outros dados necessários ao Zend Framework.
     * @return array Array com valores de tipo de visualização.
     */
    private function __loadViewTypeParam($key, $request = null)
    {
        $viewtype = Auth_Model_SystemAuth::getInstance()->getAuthVariable('viewtype');

        if (!is_array($viewtype)) {
            $viewtype = array();
        }

        if (!isset($viewtype[$key])) {
            $viewtype[$key] = 'list';
        }

        $viewtypeParam = $this->_request->getParam('viewtype', null);
        if (!is_null($viewtypeParam)) {
            $viewtype[$key] = $viewtypeParam;
        }

        return $viewtype;
    }

    /**
     * Carrega o parâmetro de limite de itens apresentados na view.
     *
     * @param  string $key A chave que identifica a view atual (permite
     *         diferentes configurações para diferentes views).
     * @param  string $request O objeto request com os dados
     *           com a ação, o controlador e outros dados necessários ao Zend Framework.
     * @return array Array com valores de limites.
     */
    private function __loadLimitParam($key, $request = null)
    {
        $limit = Auth_Model_SystemAuth::getInstance()->getAuthVariable('limit');

        if (!is_array($limit)) {
            $limit = array();
        }

        if (!isset($limit[$key])) {
            $limit[$key] = Zend_Registry::get('systemConfig')->pagination->defaultpagerange;
        }

        $param = $this->_request->getParam('limit', null);
        if (!is_null($param)) {
            $limit[$key] = $param;
        }

        return $limit;
    }

    /**
     * Carrega o parâmetro de filtros de itens apresentados na view.
     *
     * @param  string $key A chave que identifica a view atual (permite
     *         diferentes configurações para diferentes views).
     * @param  string $request O objeto request com os dados
     *           com a ação, o controlador e outros dados necessários ao Zend Framework.
     * @return array Array com valores de filtros.
     */
    private function __loadFilterParams($key, $request = null)
    {
        $filters = Auth_Model_SystemAuth::getInstance()->getAuthVariable('filters');

        if (!is_array($filters)) {
            $filters = array();
        }

        $param = $this->_request->getParam('filters', null);
        $searchParam = $this->_request->getParam('search', null);
        if (!is_null($param)) {
            $filters[$key] = $param;
        }
        if (!is_null($searchParam)) {
            $filters[$key]['expr1'] = $searchParam;
            unset($filters[$key]['category_id']);
        }

        //TODO: A limpeza a seguir não deveria ser implementada desta forma:
        // Limpa filtro de categoria após finalização de uma compra:
        if ($request->getActionName() == 'checkout') {
            if (count($filters) > 0) {
                foreach ($filters as $tmpKey => $tmpValue) {
                    if (isset($filters[$tmpKey]['category_id'])) {
                        unset($filters[$tmpKey]['category_id']);
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * Redireciona o usuário para outra página.
     *
     * @param  Zend_Controller_Request_Abstract $request O objeto request com os dados
     *           com a ação, o controlador e outros dados necessários ao Zend Framework.
     * @param  string $module O módulo para o qual o usuário será redirecionado.
     * @param  string $controller O controlador para o qual o usuário será redirecionado.
     * @param  string $action A ação para a qual o usuário será redirecionado.
     */
    public function redirect(Zend_Controller_Request_Abstract &$request, $module = 'default', $controller = 'index', $action = 'index')
    {
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
    }
}
