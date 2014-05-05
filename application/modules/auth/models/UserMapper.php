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
 * Mapeia objetos do tipo "Usuário" para o Banco de
 * Dados. Fica entre os objetos e as classes que
 * representam o Banco de Dados referente a eles.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Model_UserMapper extends STLib_Model_ModelMapper
{
    /**
     * Salva um usuário. Se não existe, é adicionado ao
     * Banco de Dados. Se já existe, é atualizado.
     *
     * @param  Auth_Model_User $user O usuário a salvar.
     * @throws Zend_Exception Se o login informado já está cadastrado no Banco de Dados.
     * @throws Zend_Exception Se o e-mail informado já está cadastrado no Banco de Dados.
     */
    public function save($user)
    {
        $data = array(
            'name'                => $user->getName(),
            'login'               => $user->getLogin(),
            'password'            => $user->getPasswordMd5(),
            'email'               => $user->getEmail(),
            'active'              => $user->getActive()
        );

        if (null === ($id = $user->getId())) {
            $loginWhereClause = "login = '". $user->getLogin() ."'";
            $emailWhereClause = "email= '". $user->getEmail() ."'";
        } else {
            $loginWhereClause = "login = '". $user->getLogin() ."' AND id <> ". $id;
            $emailWhereClause = "email= '". $user->getEmail() ."' AND id <> ". $id;
        }

        $result = $this->getDbTable()->fetchAll(
            $this->getDbTable()->select()->where($loginWhereClause)
        );
        if (0 != count($result->toArray())) {
            throw new Zend_Exception(
                sprintf(
                    Zend_Registry::get('translate')->_('AUTH_MODELS_USER_EXCEPTION_ADDUSER_LOGIN_%s_ALREADY_EXISTS'),
                    $user->getLogin()
                )
            );
        }

        $result = $this->getDbTable()->fetchAll(
            $this->getDbTable()->select()->where($emailWhereClause)
        );

        if (0 != count($result->toArray())) {
            throw new Zend_Exception(
                sprintf(
                    Zend_Registry::get('translate')->_('AUTH_MODELS_USER_EXCEPTION_ADDUSER_%s_EMAIL_ALREADY_EXISTS'),
                    $user->getEmail()
                )
            );
        }

        if (null === $id) {
            $id = $this->getDbTable()->insert($data);
            $user->setId($id);
        } else {
            $data['id'] = $user->getId();
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /**
     * Encontra um usuário com o ID informado e o coloca
     * em um objeto usuário informado.
     *
     * @param  int $id O ID do usuário a ser localizado.
     * @param  Auth_Model_User $emptyUser Um objeto do tipo User vazio para ser preenchido
     *         com os dados da busca. Se este objeto tiver dados,
     *         eles serão sobrescritos.
     * @return boolean true se foi encontrado, false caso contrário.
     */
    public function find($id, $emptyUser)
    {
        $result = $this->getDbTable()->find($id);

        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $emptyUser
            ->setId((int) $row->id)
            ->setName($row->name)
            ->setLogin($row->login)
            ->setPasswordMd5($row->password)
            ->setEmail($row->email)
            ->setActive((bool) $row->active)
        ;

        return true;
    }

    /**
     * Retorna a tabela de usuários associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @return Auth_Model_DbTable_Users A tabela solicitada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Auth_Model_DbTable_Users');
        }
        return $this->_dbTable;
    }

    /**
     * {@inheritDoc}
     */
    protected function __applyViewFilters($select, $filters)
    {
        if (isset($filters['expr1'])) {
            $select
                ->where(
                    "name LIKE '%{$filters['expr1']}%'
                     OR login LIKE '%{$filters['expr1']}%'
                     OR email LIKE '%{$filters['expr1']}%'"
                )
            ;
        }
    }
}
