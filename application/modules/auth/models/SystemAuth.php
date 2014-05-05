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
 * Estende o sistema básico de autenticação do Zend Framework.
 * Integra facilidades de sessão, aquisição de dados do
 * usuário logado e verificações de usuário ativo.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Model_SystemAuth extends Zend_Auth
{
    /**
     * Tempo de expiração da autenticação em segundos.
     *
     * @var int
     */
    protected $sessionTimeout;

    /**
     * Se a sessão expirou.
     *
     * @var boolean
     */
    protected $hasExpired = false;

    /**
     * O namespace de sessão do Zend Framework
     * que controla a sessão de autenticação.
     *
     * @var Zend_Session_Namespace
     */
    protected $_authSession = null;

    /**
     * Instância do próprio objeto (implementação de
     * padrão Singleton).
     *
     * @var Auth_Model_SystemAuth
     */
    protected static $_instance = null;


    /**
     * Construtor protegido para implementação do padrão Singleton.
     */
    protected function __construct()
    {
        //TODO: O acesso ao Zend_Registry não deve permanecer aqui, removê-lo futuramente.
        $this->sessionTimeout = Zend_Registry::get('systemConfig')->session->timeout;
    }

    /**
     * Retorna a instância única da classe. Sobrescreve o getInstance()
     * de Zend_Auth para poder retornar uma instância do tipo desta
     * classe, e não de Zend_Auth.
     *
     * @return Auth_Model_SystemAuth A instância única do objeto.
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Sobrescreve o authenticate padrão do Zend_Auth para adicionar
     * outras funcionalidades.
     *
     * @param  Zend_Auth_Adapter_Interface $adapter O adaptador de login (Zend Framework).
     * @return Zend_Auth_Result Objeto Zend_Auth_Result para os erros que
     *         o Zend Framework é capaz de tratar. Se um erro não está na
     *         lista do Zend_Auth_Result, ele é retornado como
     *         Zend_Auth_Result::FAILURE e uma mensagem informativa é adicionada
     *         à lista de mensagens do objeto.
     * @see    library/Zend/Zend_Auth#authenticate($adapter)
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter)
    {
        $result = parent::authenticate($adapter);

        if ($result->isValid()) {
            if ($adapter->getResultRowObject()->active == 0) {
                $result = new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE,
                    $adapter->getResultRowObject()->login,
                    array(Zend_Registry::get('translate')->_('AUTH_FAILURE_USER_INACTIVE'))
                );
            } else {
                $session = $this->startSession($adapter->getResultRowObject());
            }
        }
        return $result;
    }

    /**
     * Inicia uma sessão com o usuário para guardar dados
     * de autenticação.
     *
     * @param  array $userData Array com os dados de autenticação gravados
     *         no adaptador (Zend_Auth_Adapter_Interface)
     *         do Zend Framework.
     * @return Zend_Session_Namespace O namespace de sessão do Zend Framework
     *         que controla a sessão de autenticação.
     */
    protected function startSession($userData)
    {
        $time = time();

        $secureKey = sha1(
            $userData->login
            . $_SERVER['REMOTE_ADDR']
            . $time
            . $userData->name
        );

        $this->getAuthSessionNamespace()->id = base64_encode($userData->id);
        $this->getAuthSessionNamespace()->login = $userData->login;
        $this->getAuthSessionNamespace()->name = $userData->name;
        $this->getAuthSessionNamespace()->secureKey = $secureKey;
        $this->getAuthSessionNamespace()->time = $time;

        $this->getAuthSessionNamespace()->setExpirationSeconds($this->sessionTimeout);

        // Registrando sessão no banco de dados:
        $dbSessionEntry = new Auth_Model_SessionEntry($this->getSessionTimeout());
        $dbSessionEntry
            ->setSessionHash($secureKey)
            ->setTime((int) $time)
            ->setUserId((int) $userData->id)
            ->save();

        return $this->getAuthSessionNamespace();
    }

    /**
     * Retorna o tempo de expiração da sessão registrado.
     *
     * @return int O tempo de expiração da sessão registrado.
     */
    public function getSessionTimeout()
    {
        return $this->sessionTimeout;
    }

    /**
     * Altera o tempo de expiração da sessão registrado.
     *
     * @param  int $seconds O novo tempo de expiração da sessão em segundos.
     * @return Auth_Model_SystemAuth O próprio objeto.
     * @throws Zend_Exception Se o valor repassado não for um número de segundos válido.
     */
    public function setSessionTimeout($seconds)
    {
        $throwException = false;
        if (is_int($seconds) && ($seconds < 0)) {
            $throwException = true;
        }

        if (!is_int($seconds)) {
            $throwException = true;
        }

        if ($throwException) {
            throw new Zend_Exception(
                sprintf(
                    Zend_Registry::get('translate')->_('AUTH_SYSTEMAUTH_EXCEPTION_INVALID_SESSION_TIMEOUT_%s'),
                    $seconds
                )
            );
        }

        $this->sessionTimeout = $seconds;
        return $this;
    }

    /**
     * Verifica se há uma autenticação válida. Estende
     * a verificação da classe pai para validar a sessão
     * registrada.
     *
     * @return boolean true se uma identidade está disponível
     *         no storage da classe pai e a sessão é válida
     *         e não expirou ainda, false caso contrário.
     * @see    library/Zend/Zend_Auth#hasIdentity()
     */
    public function hasIdentity()
    {
        $identityToVerify = $this->getIdentity();
        $result = parent::hasIdentity(); //<- Aqui a identidade será limpa se a sessão expirar

        if ($result) { //<- Se a sessão ainda existe, verifica agora as chaves e os dados no Banco de Dados
            $result = $this->validateAuthSession();
        }

        // Se a sessão não existe mais, mas havia anteriormente ($dentityToVerify não é nulo), ela expirou!
        if ($result == false && !is_null($identityToVerify)) {
            $this->hasExpired = true;
        }

        return $result;
    }

    /**
     * Informa se uma sessão existente expirou.
     *
     * @return boolean true se uma sessão existente expirou,
     *         false caso contrário.
     */
    public function expired()
    {
        return $this->hasExpired;
    }

    /**
     * Valida uma sessão de autenticação. Método de
     * auxílio a hasIdentity().
     *
     * @return boolean true se a sessão de autenticação é válida,
     *         false caso contrário.
     */
    protected function validateAuthSession()
    {
        if ($this->getAuthSessionNamespace()->id == null) {
            $this->clearIdentity();
            return false;
        }

        $secureKeyFromSession = sha1(
            $this->getAuthSessionNamespace()->login
            . $_SERVER['REMOTE_ADDR']
            . $this->getAuthSessionNamespace()->time
            . $this->getAuthSessionNamespace()->name
        );

        // Verifica dados de sessão guardadas no lado do cliente:
        if ($this->getAuthSessionNamespace()->secureKey != $secureKeyFromSession) {
            $this->clearIdentity();
            return false;
        } else {
            // Verifica dados de sessão guardadas do lado do servidor, no banco de dados:
            $dbSessionEntry = new Auth_Model_SessionEntry($this->getSessionTimeout());
            $userIdFound = $dbSessionEntry->find($this->getAuthSessionNamespace()->secureKey)->getUserId();

            if (is_null($userIdFound)) {
                $this->clearIdentity();
                $this->hasExpired = true;
                return false;
            } else {
                // Atualiza o timeout do lado do cliente:
                $this->getAuthSessionNamespace()->setExpirationSeconds($this->getSessionTimeout());

                // Atualiza o tempo do lado do servidor.
                $dbSessionEntry->setTime(time())->save();

                $loggedInUser = new Auth_Model_User();
                $loggedInUser->find(base64_decode($this->getAuthSessionNamespace()->id));
                $loggedInUser->save();

                //TODO: O comportamento a seguir deve ser retirado daqui:
                Zend_Registry::set('loggedInUser', $loggedInUser);
            }
        }

        return true;
    }

    /**
     * Retorna todas as sessões autenticadas registradas
     * no banco de dados.
     *
     * @return array Um array de entradas de sessão com todas as
     *         registradas no sistema.
     */
    public static function fetchAllSessionEntries()
    {
        return Auth_Model_SessionEntry::fetchAll($this->getSessionTimeout());
    }

    /**
     * Limpa o storage de Zend_Auth e limpa a sessão adicional aqui criada.
     *
     * @see library/Zend/Zend_Auth#clearIdentity()
     */
    public function clearIdentity()
    {
        parent::clearIdentity();

        if (isset($this->getAuthSessionNamespace()->secureKey)) {

            $dbSessionEntry = new Auth_Model_SessionEntry($this->getSessionTimeout());
            $userIdFound = $dbSessionEntry->find($this->getAuthSessionNamespace()->secureKey)->getUserId();

            if (!is_null($userIdFound)) {
                $dbSessionEntry->remove();
            }
        }

        $this->getAuthSessionNamespace()->unsetAll();
    }

    /**
     * Retorna instância de objeto Zend_Session_Namespace para uso interno.
     *
     * @return Zend_Session_Namespace O namespace de sessão do Zend Framework
     *         que controla a sessão de autenticação.
     */
    private function &getAuthSessionNamespace()
    {
        if (is_null($this->_authSession)) {
            $this->_authSession = new Zend_Session_Namespace(Zend_Registry::get('systemConfig')->session->namespace);
        }
        return $this->_authSession;
    }

    /**
     * Grava uma variável na sessão do usuário com nome e valor
     * indicados. Sobrescreve se a variável já existe.
     *
     * @param  string $name  O nome da variável a gravar/alterar.
     * @param  string $value O valor da variável.
     */
    public function setAuthVariable($name, $value)
    {
        if (!isset($this->getAuthSessionNamespace()->globalConfigs)) {
            $this->getAuthSessionNamespace()->globalConfigs = array();
        }

        // Serializar permite armazenar classes ainda não carregadas
        // (incompletas para o PHP).
        $this->getAuthSessionNamespace()->globalConfigs[$name] = serialize($value);
    }

    /**
     * Recupera o valor de uma variável gravada na sessão do usuário.
     *
     * @param  string $name O nome da variável a recuperar.
     * @return string O valor da variável solicitada. Retorna null se
     *         a variável não foi encontrada.
     */
    public function getAuthVariable($name)
    {
        //TODO: Testar casos de erro após serialização caso o Zend ainda não tenha incluído as classes dos objetos serializados.
        if (isset($this->getAuthSessionNamespace()->globalConfigs[$name])) {
            return unserialize($this->getAuthSessionNamespace()->globalConfigs[$name]);
        }
        return null;
    }
}
