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
 * @subpackage Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Descreve uma entrada de sessão, objeto responsável
 * pela sessão de autenticação de usuários.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Model_SessionEntry extends STLib_Model_Model
{
    /**
     * Contém o namespace da sessão.
     *
     * @var Zend_Session_Namespace
     */
    private $_namespace = null;

    /**
     * Um hash SHA1 montado pela aplicação que identifica
     * a sessão.
     *
     * @var string
     */
    protected $sessionHash = null;

    /**
     * O tempo em segundos desde a "Unix Epoch"
     * (January 1 1970 00:00:00 GMT) em que a sessão
     * foi iniciada. Usado para verificar o timeout.
     *
     * @var int
     */
    protected $time = null;

    /**
     * O ID do usuário que iniciou a sessão.
     *
     * @var int
     */
    protected $userId = null;

    /**
     * Tempo de expiração da autenticação em segundos.
     * Deve ser passado pelo construtor da entrada.
     *
     * @var int
     */
    protected $expirationSeconds = null;

   /**
    * O construtor da classe. Ele deve limpar as sessões
    * expiradas do banco de dados com uma chamada
    * ao método clearExpiredDatabaseSessions()
    * desta mesma classe.
    * Ele deve receber o tempo em segundos
    * da expiração de sessão para saber como
    * remover as sessões expiradas.
    *
    * @param  string $namespaceName     O nome do namespace de registro da sessão.
    * @param  int    $expirationSeconds Tempo de expiração de sessão em segundos.
    * @param  array  $options           Um array de opções compatível com o Zend Framework.
    */
    public function __construct($expirationSeconds, $options = null)
    {
        parent::__construct($options);

        $this->setExpirationSeconds($expirationSeconds)
             ->setNamespace(Zend_Registry::get('systemConfig')->session->namespace)
        ;

        $this->time = time();

        // O clear deve ser chamado no final, após definir os segundos de expiração:
        $this->clearExpiredDatabaseSessions();
    }

    /**
     * Valida e define o tempo de expiração da sessão.
     *
     * @param  int $expirationSeconds Tempo de expiração de sessão em segundos.
     * @return Auth_Model_SessionEntry O próprio objeto.
     * @throws Zend_Exception Se o valor de expiração de sessão não for um número
     *         de segundos válido.
     */
    public function setExpirationSeconds($expirationSeconds)
    {
        $throwException = false;
        if (is_int($expirationSeconds)) {
            if ($expirationSeconds < 0) {
                $throwException = true;
            }
        }

        if (!is_int($expirationSeconds)) {
            $throwException = true;
        }

        if ($throwException === true) {
            throw new Zend_Exception(
                sprintf(
                    Zend_Registry::get('translate')->_('AUTH_MODELS_EXCEPTION_INVALID_TIMEOUT_%s'),
                    $expirationSeconds
                )
            );
        }

        $this->expirationSeconds = $expirationSeconds;

        return $this;
    }

    /**
     * Define o namespace da sessão.
     *
     * @param  string $namespaceName O nome do namespace.
     * @return Auth_Model_SessionEntry O próprio objeto.
     * @throws Zend_Exception Se o namespace sa sessão for nulo ou vazio.
     */
    public function setNamespace($namespaceName)
    {
        if ($namespaceName == null || $namespaceName == '') {
            throw new Exception(
                Zend_Registry::get('translate')->_('AUTH_MODELS_SESSION_EXCEPTION_SESSION_MUST_HAVE_A_NAMESPACE')
            );
        }

        $this->_namespace = new Zend_Session_Namespace($namespaceName);
        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function remove()
    {
        return $this->getMapper()->remove($this->getSessionHash());
    }

    /**
     * Encontra uma entrada de sessão com o identificador
     * informado e popula o atual objeto com os dados
     * usando o método do mapper para tal operação.
     * Retorna-o no final da operação.
     *
     * @param  string $sessionHash O hash da sessão a adquirir os dados.
     * @return Auth_Model_SessionEntry O próprio objeto.
     */
    public function find($sessionHash)
    {
        $this->getMapper()->find($sessionHash, $this);
        return $this;
    }

    /**
     * Chamado pelo construtor, executa operação
     * de limpeza de sessões inválidas ainda registradas
     * no banco de dados. Esta operação faz com
     * que possamos sempre trabalhar com sessões
     * válidas, sem a necessidade de sempre checar
     * se já foi ou não expirada.
     */
    protected function clearExpiredDatabaseSessions()
    {
        $this->getMapper()->removeExpiredSessions($this->expirationSeconds);
    }

    /**
     * {@inheritDoc}
     */
    //TODO: Repensar implementação desta funcionalidade. Há uma série de parâmetros inutilizados.
    public static function fetchAll($orderBy = null, $page = 1, $rowCount = null, $filters = array())
    {
        $model = new Auth_Model_SessionEntry(Zend_Registry::get('systemConfig')->session->timeout);
        return $model->getMapper()->fetchAll($model->getExpirationSeconds());
    }

    /**
     * Encontra uma entrada de sessão pelo ID do usuário
     * e retorna-a.
     *
     * @param  string $userId O ID do usuário.
     * @return Auth_Model_SessionEntry Uma entrada de sessão.
     */
    public static function fetchByUserId($userId)
    {
        $model = new Auth_Model_SessionEntry(Zend_Registry::get('systemConfig')->session->timeout);
        return $model->getMapper()->fetchByUserId($userId, $this);
    }

    /**
     * Retorna o objeto como string.
     *
     * @return string O objeto como string.
     */
    public function __toString()
    {
        return sprintf(
            "%s (UID: %s, TIME: %s)",
            $this->getSessionHash(),
            $this->getUserId(),
            $this->getTime()
        );
    }
}
