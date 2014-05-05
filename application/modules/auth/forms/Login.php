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
 * @subpackage Forms
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Formulário de login da aplicação.
 *
 * @category   SimpleShoppingCart
 * @package    Auth
 * @subpackage Forms
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Auth_Form_Login extends EasyBib_Form
{
    /**
     * Inicializa o formulário e seus componentes.
     */
    public function init()
    {
        $this->setName('login');

        $login = new Zend_Form_Element_Text('login');
        $login
            ->setLabel('AUTH_FORM_LOGIN_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
        ;

        $senha = new Zend_Form_Element_Password('password');
        $senha
            ->setLabel('AUTH_FORM_PASSWORD_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
        ;


        $submit = new Zend_Form_Element_Submit('loginsubmit');
        $submit->setLabel('AUTH_FORM_SUBMIT_LABEL');

        $this->addElements(array($login, $senha, $submit));

        EasyBib_Form_Decorator::setFormDecorator(
            $this,
            EasyBib_Form_Decorator::BOOTSTRAP_MINIMAL,
            'submit',
            'cancel'
        );
    }

    /**
     * Adiciona um campo oculto a este formulário.
     *
     * @param string  $elementName  O nome do elemento a adicionar.
     * @param string  $elementValue O valor do elemento a adicionar.
     * @param boolean $isParam      Define se o campo é um parâmetro que deve ou não
     *        ser repassado à ação de destino como parâmetro pelo formulário.
     */
    public function addHiddenElement($elementName, $elementValue, $isParam = false)
    {
        $elementOptions = array(
            'value' => $elementValue
        );

        if ($isParam) {
            $elementOptions['belongsTo'] = 'params';
        }

        $this->addElement('hidden', $elementName, $elementOptions);
    }
}
