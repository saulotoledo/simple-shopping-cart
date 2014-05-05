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
 * Define que recursos e componentes devem ser inicializados.
 * Por padrão inicializa o "Front Controller" do framework Zend.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Construtor da classe. Executa o construtor original do Bootstrap e
     * carrega configurações básicas da aplicação.
     *
     * @param Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     *        Aplicação Zend necessária para o funcionamento do Bootstrap.
     *        É repassada para o construtor da classe pai.
     */
    public function __construct($application)
    {
        // Executa o construtor da classe pai (obrigatório):
        parent::__construct($application);

        // Carrega configurações básicas para o funcionamento da aplicação:
        $systemConfig = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/application.ini',
            'production'
        );

        // Configura o banco de dados:
        $db = Zend_Db::factory(
            $systemConfig->database->adapter,
            $systemConfig->database->params->toArray()
        );

        // O profiler para depuração:
        if ((bool) $systemConfig->database->debug->profiler) {
            $db->getProfiler()->setEnabled(true);
        }

        // Define o adapter do banco de dados:
        Zend_Db_Table::setDefaultAdapter($db);

        // Algumas constantes para uso dentro da aplicação:
        define('TABLE_PREFIX', $systemConfig->database->params->tableprefix);
        define('DEFAULT_PAGINATION_ITEMS_PER_PAGE', $systemConfig->pagination->defaultquantity);

        // Grava variáveis dentro do registro do Zend:
        Zend_Registry::set('systemConfig', $systemConfig);
        Zend_Registry::set('db', $db);

        // Formato da moeda
        // O Zend_Currency não é utilizado porque ele não põe um espaço entre o símbolo da
        // moeda com seu valor na view.
        // TODO: Remover configuração a seguir ao implementar suporte a outras moedas.
        setlocale(LC_MONETARY, 'pt_BR');
    }

    /**
     * Inicia o autoloader do Zend Framework.
     *
     * @return Zend_Application_Module_Autoloader O autoloader do Zend.
     */
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                'basePath' => APPLICATION_PATH,
                'namespace' => ''
            )
        );

        return $autoloader;
    }

    /**
     * Inicia uma sessão vazia ou carrega dados de sessão pré-existente.
     */
    protected function _initSession()
    {
        Zend_Session::start();
    }

    /**
     * Configura o timezone da aplicação.
     */
    protected function _initTimezone()
    {
        date_default_timezone_set(
            Zend_Registry::get('systemConfig')->date->timezone
        );
    }

    /**
     * Inicia sistema de tradução da aplicação.
     */
    protected function _initTranslations()
    {
        $defaultLocale = Zend_Registry::get('systemConfig')->locale->default;

        $translationPath = APPLICATION_PATH . '/../languages/';
        $translate = null;

        $langBaseDir = opendir($translationPath);
        while (false !== ($langDirStr = readdir($langBaseDir))) {

            if (is_dir($translationPath . $langDirStr) && substr($langDirStr, 0, 1) != '.') {
                $langDir = opendir($translationPath . $langDirStr);
                while (false !== ($langFile = readdir($langDir))) {

                    if (strtolower(end(explode('.', $langFile))) == 'mo') {

                        if ($translate == null) {
                            $translate = new Zend_Translate(
                                array(
                                    'adapter' => 'gettext',
                                    'content' => $translationPath . $langDirStr . '/' . $langFile,
                                    'locale'  => $langDirStr,
                                    'delimiter' => ','
                                )
                            );
                        } else {
                            $translate->addTranslation(
                                array(
                                    'content' => $translationPath . $langDirStr . '/' . $langFile,
                                    'locale'  => $langDirStr
                                )
                            );
                        }
                    }
                }

            }
        }

        // Note que um erro ocorrerá se nenhuma tradução for encontrada acima...
        $translate->setLocale($defaultLocale);

        // Guarda o objeto no registro, para os usos onde os componentes não tem suporte direto.
        Zend_Registry::set('translate', $translate);

        // Diz aos formulários para usarem o Zend_Translate para seus textos:
        Zend_Form::setDefaultTranslator($translate);
        Zend_Validate_Abstract::setDefaultTranslator($translate);
    }

    /**
     * Inicia o sistema de e-mail.
     */
    protected function _initMail()
    {
        Zend_Mail::setDefaultFrom(
            Zend_Registry::get('systemConfig')->mail->from,
            Zend_Registry::get('systemConfig')->mail->fromname
        );
        Zend_Mail::setDefaultReplyTo(
            Zend_Registry::get('systemConfig')->mail->from,
            Zend_Registry::get('systemConfig')->mail->fromname
        );

        if ((bool) Zend_Registry::get('systemConfig')->mail->smtp->enabled) {

            $smtpInfo = array (
                'auth' => Zend_Registry::get('systemConfig')->mail->smtp->auth,
                'username' => Zend_Registry::get('systemConfig')->mail->smtp->username,
                'password' => Zend_Registry::get('systemConfig')->mail->smtp->password,
                'port' => Zend_Registry::get('systemConfig')->mail->smtp->port
            );
            if (Zend_Registry::get('systemConfig')->mail->smtp->ssl != '') {
                $smtpInfo['ssl'] = Zend_Registry::get('systemConfig')->mail->smtp->ssl;
            }

            $smtpTransport = new Zend_Mail_Transport_Smtp(
                Zend_Registry::get('systemConfig')->mail->smtp->host,
                $smtpInfo
            );
            Zend_Mail::setDefaultTransport($smtpTransport);
        }
    }

    /**
     * Inicializa o view e o doctype.
     */
    protected function _initProjectView()
    {
        $this->bootstrap('view');

        $view = $this->getResource('view');
        $view->setEncoding("UTF-8")
             ->setEscape("htmlentities")
             ->addHelperPath(
                 "ZendX/JQuery/View/Helper",
                 "ZendX_JQuery_View_Helper"
             )
             ->addHelperPath(
                 APPLICATION_PATH ."/views/helpers/",
                 "ZendX_JQuery_View_Helper"
             )
             ->doctype('HTML5');

        $view->jQuery()->enable();
        $view->jQuery()->uiEnable();

        /* O bloco try/catch abaixo garante que as traduções sejam carregadas
         * antes. Se não o forem, uma exceção de "não encontrado" é lançada
         * pelo Zend_Registry, então o bloco "catch" carrega as traduções e
         * tenta a operação que falhou novamente.
         */
        try {
            $view->translator = Zend_Registry::get('translate');
        } catch (Zend_Exception $e) {
            $this->_initTranslations();
            $view->translator = Zend_Registry::get('translate');
        }
    }
}
