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
 * Define a base de um Model.
 *
 * @category   STLib
 * @package    STLib_Model
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
abstract class STLib_Model_Model extends STLib_Model_Object
{
    /**
     * O Mapper associado.
     *
     * @var STLib_Model_ModelMapper
     */
    protected $_mapper = null;

    /**
     * Construtor.
     *
     * @param array|Zend_Config|null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    /**
     * Salva alterações do objeto chamando
     * o método do Mapper que faz a operação.
     *
     * @throws Zend_Exception Exceção do mapper.
     */
    public function save()
    {
        try {
            $this->getMapper()->save($this);
        } catch (Zend_Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove esta entrada do Banco de Dados.
     *
     * @return boolean true se esta entrada está registrada
     *         e foi removida com sucesso, false caso contrário.
     */
    public function remove()
    {
        return $this->getMapper()->remove($this->getId());
    }

    /**
     * Retorna a quantidade de objetos deste tipo no Banco de Dados.
     *
     * @return int A quantidade de objetos deste tipo no Banco de Dados.
     */
    public function countAll($filters = null)
    {
        return $this->getMapper()->countAll($filters);
    }

    /**
     * Retorna todos os registros do tipo do objeto de acordo
     * com as informações solicitadas.
     *
     * @param  array $orderBy Lista de campos para ordenação.
     * @param  int $page A página solicitada na paginação.
     * @param  int $rowCount A quantidade limite de registros
     *         a retornar.
     * @param  array $filters Lista de filtros a aplicar.
     * @return array Um array de registros com todos aqueles
     *         de acordo com as informações solicitadas.
     */
    public static function fetchAll($orderBy = null, $page = 1, $rowCount = null, $filters = array())
    {
        $className = get_called_class();
        $model = new $className();
        return $model->getMapper()->fetchAll($orderBy, $page, (int) $rowCount, $filters);
    }

    /**
     * Encontra um objeto com o ID informado
     * e popula o atual objeto com os dados usando
     * o método do mapper para tal operação,
     * retornando-o ao final.
     *
     * @param  int $id O ID do objeto a adquirir os dados.
     * @return STLib_Model_Model O próprio objeto populado com os dados encontrados
     *         ou null se o id não foi encontrado.
     */
    public function find($id)
    {
        $result = $this->getMapper()->find($id, $this);
        if ($result) {
            return $this;
        }
        return null;
    }

    /**
     * Retorna o Mapper associado a este objeto.
     *
     * @return STLib_Model_ModelMapper O Mapper associado.
     */
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $mapperClassName = get_class($this).'Mapper';
            $this->setMapper(new $mapperClassName());
        }
        return $this->_mapper;
    }

    /**
     * Altera o Mapper associado.
     *
     * @param  STLib_Model_ModelMapper $newMapper O novo Mapper.
     * @return STLib_Model_Model O próprio objeto.
     * @throws Zend_Exception Se o mapper informado for inválido.
     * @throws Zend_Exception Se o mapper informado não for compatível com
     *         esta classe.
     */
    public function setMapper($newMapper)
    {
        if (!$newMapper instanceof STLib_Model_ModelMapper) {
            throw new Zend_Exception(
                Zend_Registry::get('translate')->_('STLIB_MODEL_MODELMAPPER_INVALID_MAPPER')
            );
        }

        if (get_class($newMapper) != get_class($this).'Mapper') {
            throw new Zend_Exception(
                sprintf(
                    Zend_Registry::get('translate')->_('STLIB_MODEL_MODELMAPPER_INVALID_MAPPER_%s_FOR_CLASS_%s'),
                    get_class($newMapper),
                    get_class($this)
                )
            );
        }

        $this->_mapper = $newMapper;
        return $this;
    }
}
