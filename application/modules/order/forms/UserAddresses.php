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
 * @package    Order
 * @subpackage Forms
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Formulário de endereços do usuário.
 *
 * @category   SimpleShoppingCart
 * @package    Order
 * @subpackage Forms
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
class Order_Form_UserAddresses extends EasyBib_Form
{
    /**
     * Inicializa o formulário e seus componentes.
     */
    public function init()
    {
        $this
            ->setName('useraddressesform')
            ->setMethod('post')
        ;

        $brazilianStates = array(
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins'
        );

        $personalCep = new Zend_Form_Element_Text('personalCep');
        $personalCep
            ->setLabel('ORDER_FORM_PERSONAL_CEP_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 9))
            ->addValidator('Regex', false, array(
                'pattern'  => '/^[0-9]{5}-[0-9]{3}$/',
                'messages' => array(
                    Zend_Validate_Regex::NOT_MATCH => $this->getTranslator()->_('ORDER_FORM_ERRORMESSAGE_INVALID_CEP')
                )
            ))
            ->setAttrib('maxlength', 9)
        ;

        $personalStreet = new Zend_Form_Element_Text('personalStreet');
        $personalStreet
            ->setLabel('ORDER_FORM_PERSONAL_STREET_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 150))
            ->setAttrib('maxlength', 150)
        ;

        $personalNumber = new Zend_Form_Element_Text('personalNumber');
        $personalNumber
            ->setLabel('ORDER_FORM_PERSONAL_NUMBER_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Int')
            ->setAttrib('maxlength', 6)
        ;

        $personalComplement = new Zend_Form_Element_Text('personalComplement');
        $personalComplement
            ->setLabel('ORDER_FORM_PERSONAL_NUMBER_COMPLEMENT_LABEL')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array('max' => 10))
            ->setAttrib('maxlength', 10)
        ;

        $personalNeighborhood = new Zend_Form_Element_Text('personalNeighborhood');
        $personalNeighborhood
            ->setLabel('ORDER_FORM_PERSONAL_NEIGHBORHOOD_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 100))
            ->setAttrib('maxlength', 100)
        ;

        $personalCity = new Zend_Form_Element_Text('personalCity');
        $personalCity
            ->setLabel('ORDER_FORM_PERSONAL_CITY_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 150))
            ->setAttrib('maxlength', 150)
        ;

        $personalState = new Zend_Form_Element_Select('personalState');
        $personalState
            ->setLabel('ORDER_FORM_PERSONAL_STATE_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->setMultiOptions(array_merge(
                array('' => ''),
                $brazilianStates
            ))
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
        ;

        $sameAddressCheckbox = new Zend_Form_Element_Checkbox('sameAddress');
        $sameAddressCheckbox
            ->setLabel('ORDER_FORM_SAME_ADDRESS_LABEL')
            ->setCheckedValue(true)
            ->setUncheckedValue(false)
        ;

        $shippingCep = new Zend_Form_Element_Text('shippingCep');
        $shippingCep
            ->setLabel('ORDER_FORM_SHIPPING_CEP_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 9))
            ->addValidator('Regex', false, array(
                'pattern'  => '/^[0-9]{5}-[0-9]{3}$/',
                'messages' => array(
                    Zend_Validate_Regex::NOT_MATCH => $this->getTranslator()->_('ORDER_FORM_ERRORMESSAGE_INVALID_CEP')
                )
            ))
            ->setAttrib('maxlength', 9)
        ;

        $shippingStreet = new Zend_Form_Element_Text('shippingStreet');
        $shippingStreet
            ->setLabel('ORDER_FORM_SHIPPING_STREET_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 150))
            ->setAttrib('maxlength', 150)
        ;

        $shippingNumber = new Zend_Form_Element_Text('shippingNumber');
        $shippingNumber
            ->setLabel('ORDER_FORM_SHIPPING_NUMBER_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Int')
            ->setAttrib('maxlength', 6)
        ;

        $shippingComplement = new Zend_Form_Element_Text('shippingComplement');
        $shippingComplement
            ->setLabel('ORDER_FORM_SHIPPING_NUMBER_COMPLEMENT_LABEL')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array('max' => 10))
            ->setAttrib('maxlength', 10)
        ;

        $shippingNeighborhood = new Zend_Form_Element_Text('shippingNeighborhood');
        $shippingNeighborhood
            ->setLabel('ORDER_FORM_SHIPPING_NEIGHBORHOOD_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 100))
            ->setAttrib('maxlength', 100)
        ;

        $shippingCity = new Zend_Form_Element_Text('shippingCity');
        $shippingCity
            ->setLabel('ORDER_FORM_SHIPPING_CITY_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', false, array('max' => 150))
            ->setAttrib('maxlength', 150)
        ;

        $shippingState = new Zend_Form_Element_Select('shippingState');
        $shippingState
            ->setLabel('ORDER_FORM_SHIPPING_STATE_LABEL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->setMultiOptions(array_merge(
                array('' => ''),
                $brazilianStates
            ))
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
        ;

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('ORDER_FORM_SUBMIT_LABEL');

        $this->addElements(array(
            $personalCep,
            $personalCity,
            $personalNeighborhood,
            $personalNumber,
            $personalComplement,
            $personalState,
            $personalStreet,
            $sameAddressCheckbox,
            $shippingCep,
            $shippingCity,
            $shippingNeighborhood,
            $shippingNumber,
            $shippingComplement,
            $shippingState,
            $shippingStreet,
            $submit
        ));

        $this->addDisplayGroup(
            array(
                'personalCep',
                'personalStreet',
                'personalNumber',
                'personalComplement',
                'personalNeighborhood',
                'personalCity',
                'personalState'
            ),
            'personal',
            array(
                'legend' => $this->getTranslator()->translate('ORDER_FORM_PERSONAL_GROUP_LABEL')
            )
        );

        $this->addDisplayGroup(
            array(
                'shippingCep',
                'shippingStreet',
                'shippingNumber',
                'shippingComplement',
                'shippingNeighborhood',
                'shippingCity',
                'shippingState'
            ),
            'shipping',
            array(
                'legend' => $this->getTranslator()->translate('ORDER_FORM_SHIPPING_GROUP_LABEL')
            )
        );

        $this->addDisplayGroup(
            array('sameAddress'),
            'sameaddressgroup'
        );

        $this->addDisplayGroup(
            array('submit'),
            'submitgroup'
        );

        EasyBib_Form_Decorator::setFormDecorator(
            $this,
            EasyBib_Form_Decorator::BOOTSTRAP_MINIMAL,
            'submit',
            'cancel'
        );
    }
}
