<?php
/**
 * LICENSE
 *
 * This source file is subject to the BSD 3-Clause license.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to saulotoledo@gmail.com so we can send you a copy immediately.
 *
 * @category   STLib
 * @package    STLib_Model
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Objeto base para Models em aplicações com Zend_Framework.
 *
 * @category   STLib
 * @package    STLib_Model
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
abstract class STLib_Model_Object
{
    /**
     * Construtor
     *
     * @param array|Zend_Config|null $options Opções a definir.
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        $this->init();
    }

    /**
     * Parâmetros de inicialização.
     */
    public function init()
    {
    }

    /**
     * Determina as opções usando setters.
     *
     * @param  array $options Opções a definir
     * @return STLib_Model_Object O próprio objeto.
     */
    public function setOptions(array $options)
    {
        $classMethods = get_class_methods($this);

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $classMethods)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Retorna o namespace deste módulo.
     *
     * @return string O namespace deste módulo.
     */
    private function _getNamespace()
    {
        $namespace = explode('_', get_class($this));
        return $namespace[0];
    }

    /**
     * Usa o inflector filter para transformar
     * camelCaseName em Camel_Case_Name.
     *
     * @param string $name O nome a utilizar no filtro.
     * @return string A string resultante.
     */
    private function _getInflected($name)
    {
        $inflector = new Zend_Filter_Inflector(':class');
        $inflector->setRules(array(
            ':class' => array(
                'Word_CamelCaseToUnderscore'
            )
        ));

        return ucfirst($inflector->filter(array('class' => $name)));
    }

    /**
     * Envelope para getters e setters. Parâmetros com underscore
     * são internos e não podem ser alterados por getters e setters.
     *
     * @param  string $method O nome do método chamado.
     * @param  array  $args Os argumentos do método.
     * @return mixed  O resultado da chamada ou $this para setters.
     */
    public function __call($method, $args)
    {
        if (strlen($method) > 3 && strpos($method, '_') === false) {
            $propertyName = substr($method, 3);
            // Simula lcfirst(), que só está disponível a partir do PHP 5.3:
            $propertyName = strtolower(substr($propertyName, 0, 1)) . substr($propertyName, 1);
            $methodType = substr($method, 0, 3);

            if (property_exists($this, $propertyName)) {
                if ($methodType == 'get') {
                    return $this->$propertyName;
                } else {
                    if ($methodType == 'set') {
                        $this->$propertyName = $args[0];
                        return $this;
                    }
                }
            }
        }

        throw new Exception(
            sprintf(
                Zend_Registry::get('translate')->_('STLIB_MODEL_OBJECT_INVALID_METHOD_NAME_%s'),
                $method
            )
        );
    }
}
