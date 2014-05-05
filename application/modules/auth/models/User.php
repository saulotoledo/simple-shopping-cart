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
 * Definição de um usuário que acessa o sistema.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Models
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Model_User extends STLib_Model_Model
{
    /**
     * Um ID único para controle do usuário.
     *
     * @var int
     */
    protected $id = null;

    /**
     * O nome do usuário.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Um login único que identifica o usuário no sistema.
     *
     * @var string
     */
    protected $login = null;

    /**
     * String MD5 da senha do usuário
     *
     * @var string
     */
    protected $passwordMd5 = null;

    /**
     * O e-mail do usuário.
     *
     * @var string
     */
    protected $email = null;

    /**
     * Indica se o usuário está ou não ativo no sistema.
     *
     * @var boolean
     */
    protected $active = null;

    /**
     * Altera a senha do usuário, gravando seu MD5.
     *
     * @param  string $newPassword A nova senha do usuário.
     * @return Auth_Model_User O próprio objeto.
     */
    public function setPassword($newPassword)
    {
        $this->passwordMd5 = md5($newPassword);
        return $this;
    }

    /**
     * Retorna se o usuário do sistema está ou não logado no momento.
     *
     * @return boolean true para sim, false para não.
     */
    public function isAuth()
    {
        $session = Auth_Model_SessionEntry::fetchByUserId($this->getId());
        $isAuth = !is_null($session);
        return $isAuth;
    }

    /**
     * Retorna o objeto como string.
     *
     * @return string O objeto como string.
     */
    public function __toString()
    {
        return sprintf("%s (%s)", $this->getName(), $this->getLogin());
    }
}
