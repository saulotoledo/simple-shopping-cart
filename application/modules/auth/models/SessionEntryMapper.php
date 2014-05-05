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
 * Mapeia objetos do tipo "Entrada de Sessão", responsáveis
 * pela sessão de autenticação de usuários, para o Banco
 * de Dados.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Model_SessionEntryMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva uma entrada de sessão. Se não existe,
     * é adicionada ao Banco de Dados. Se já
     * existe, é atualizada.
     *
     * @param  Auth_Model_SessionEntry $sessionEntry A entrada de sessão a salvar.
     */
    public function save($sessionEntry)
    {
        $data = array(
            'session_hash' => $sessionEntry->getSessionHash(),
            'time'         => $sessionEntry->getTime(),
            'user_id'      => $sessionEntry->getUserId()
        );

        if (count($this->getDbTable()->fetchAll("session_hash = '". $sessionEntry->getSessionHash() ."'")) == 0) {
            //TODO: Esta classe é uma excessão às outras por não necessitar retornar ID algum no insert. Rever este comportamento.
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('session_hash = ?' => $sessionEntry->getSessionHash()));
        }
    }

    /**
     * Retorna todas as entradas de sessão do sistema.
     *
     * @return array Um array de entradas de sessão com todas as
     *         cadastradas no sistema.
     */
    //TODO: Rever a implementação desta funcionalidade devido à classe pai.
    public function fetchAll($expirationSeconds)
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $itens     = array();

        foreach ($resultSet as $row) {
            $item = new Auth_Model_SessionEntry($expirationSeconds);
            $item->setSessionHash($row->session_hash)
                 ->setTime((int) $row->time)
                 ->setUserId((int) $row->user_id)
                 ->setMapper($this)
            ;
            $itens[] = $item;
        }
        return $itens;
    }

    /**
     * Preenche uma entrada de sessão com dados de busca
     * pelo ID do usuário.
     *
     * @param  string $userId O ID do usuário.
     * @param  Auth_Model_SessionEntry $emptySessionEntry Um objeto
     *         de entrada de sessão inicialmente vazio.
     * @return Auth_Model_SessionEntry Uma entrada de sessão.
     */
    public static function fetchByUserId($userId, $emptySessionEntry)
    {
        $result = $this->getDbTable()->fetchAll(
            $this->getDbTable()->select()->where("user_id = $userId")->limit(1, 0)
        );

        if (0 == count($result->toArray())) {
            $emptySessionEntry = null;
        }

        $row = $result->current();
        $emptySessionEntry
            ->setSessionHash($row->session_hash)
            ->setTime((int) $row->time)
            ->setUserId((int) $row->user_id)
        ;
    }

    /**
     * Encontra uma entrada de sessão com o identificador
     * informado e a coloca em um objeto informado.
     *
     * @param  string $sessionHash O hash da sessão a adquirir os dados.
     * @param  Auth_Model_SessionEntry $emptySession_entry Um objeto do tipo
     *         "Entrada de Sessão" vazio para ser preenchido com os dados da
     *         busca. Se este objeto tiver dados, eles serão sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($sessionHash, $emptySessionEntry)
    {
        $result = $this->getDbTable()->fetchAll(
            $this->getDbTable()->select()->where("session_hash = '$sessionHash'")
        );

        if (0 == count($result->toArray())) {
            return false;
        }

        $row = $result->current();
        $emptySessionEntry
            ->setSessionHash($row->session_hash)
            ->setTime((int) $row->time)
            ->setUserId((int) $row->user_id)
        ;

        return true;
    }

    /**
     * Remove uma entrada de sessão.
     *
     * @param  string  $sessionHash O hash da sessão a remover.
     * @return boolean true se esta entrada de sessão está registrada
     *         e foi removida com sucesso, false caso contrário.
     */
    public function remove($sessionHash)
    {
        $numRowsRemoved = $this->getDbTable()->delete(
            array('session_hash = ?' => $sessionHash)
        );

        if ($numRowsRemoved > 0) {
            return true;
        }
        return false;
    }

    /**
     * Remove as sessões expiradas do Banco de Dados.
     *
     * @param  int $expirationSeconds O tempo de expiração de referência para saber
     *         o que remover.
     * @return void
     */
    public function removeExpiredSessions($expirationSeconds)
    {
        $this->getDbTable()->delete("(time + $expirationSeconds) < ". time());
    }

    /**
     * Retorna a tabela sessões associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Auth_Model_DbTable_SessionEntries A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Auth_Model_DbTable_SessionEntries');
        }
        return $this->_dbTable;
    }

    /**
     * {@inheritDoc}
     */
    protected function __applyViewFilters($select, $filters)
    {
    }
}
