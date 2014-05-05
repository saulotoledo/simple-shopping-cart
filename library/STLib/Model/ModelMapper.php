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
 * Define a base de um ModelMapper.
 *
 * @category   STLib
 * @package    STLib_Model
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
abstract class STLib_Model_ModelMapper extends STLib_Model_Object
{
    /**
     * Identificador de ordenação ascendente para banco de dados.
     *
     * @var string
     */
    const FETCH_FILTER_ORDER_ASC = 'ASC';

    /**
     * Identificador de ordenação descendente para banco de dados.
     *
     * @var string
     */
    const FETCH_FILTER_ORDER_DESC = 'DESC';

    /**
     * Atributo destinado a guardar uma instância do
     * objeto que representa a tabela deste objeto
     * no sistema. A necessidade deste atributo é a de
     * que não haja "new" de objetos desnecessariamente,
     * O objeto já está aqui criado para ser usado.
     *
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable = null;

    /**
     * Remove uma entrada.
     *
     * @param  int $id O ID da entrada a remover.
     * @return boolean true se foi removido com sucesso, false se não existe.
     */
    public function remove($id)
    {
        $num_rows_deleted = $this->getDbTable()->fetchRow(array('id = ?' => $id))->delete();
        if ($num_rows_deleted > 0) {
            return true;
        }

        return false;
    }

    /**
     * Retorna a quantidade de entradas deste tipo no Banco de Dados.
     *
     * @param array $filters Filtros de registros vindos da interface gráfica.
     * @return int A quantidade de entradas deste tipo no Banco de Dados.
     */
    public function countAll($filters = null)
    {
        $subQuery = $this->fetchAllQuery(array(), 1, null, $filters);
        $select = $this->getDbTable()
            ->select()
            ->setIntegrityCheck(false)
            ->from(
                array(new Zend_Db_Expr('(' . $subQuery . ')')),
                'COUNT(*) as num_rows'
            )
        ;

        $resultSet = $this->getDbTable()->fetchRow($select);

        return (int) $resultSet->__get('num_rows');
    }

    /**
     * Altera a tabela de associada ao mapper.
     * Método de uso interno ao mapper.
     *
     * @param  string|Zend_Db_Table_Abstract $newDbTable A nova tabela.
     * @return STLib_Model_ModelMapper O próprio mapper.
     * @throws Zend_Exception Se a tabela informada não for válida para o sistema.
     */
    protected function setDbTable($newDbTable)
    {
        if (is_string($newDbTable)) {
            $newDbTable = new $newDbTable();
        }

        if (!$newDbTable instanceof STLib_Model_DbTable_PrefixedDbTable) {
            throw new Zend_Exception(
                Zend_Registry::get('translate')->_('STLIB_MODEL_MODEL_DBTABLE_INVALID_TABLE')
            );
        }

        $this->_dbTable = $newDbTable;
        return $this;
    }

    /**
     * Retorna a DbTable associada a este objeto.
     *
     * @return STLib_Model_DbTable_PrefixedDbTable A DbTable associada.
     */
    protected function getDbTable()
    {
        if (null === $this->_dbTable) {
            $dbTableClassName = preg_replace('/([a-zA-Z]*)Mapper/', 'DbTable_$1', get_class($this));
            $this->setDbTable(new $dbTableClassName());
        }
        return $this->_dbTable;
    }

    /**
     * Retorna o objeto Zend_Db_Table_Select necessário para
     * o select desejado na tabela deste Model. Aplica todos
     * os filtros necessários.
     *
     * @param  array $orderBy Lista de campos para ordenação.
     * @param  int $page A página solicitada na paginação.
     * @param  int $rowCount A quantidade limite de registros
     *         a retornar. Se for zero, todos os registros são
     *         retornados.
     * @param  array $filters Lista de filtros a aplicar.
     * @return Zend_Db_Table_Select O objeto select.
     * @throws Zend_Exception Quando a coluna para ordenação não existe na tabela.
     * @throws Zend_Exception Quando "order_direction" é diferente de ASC e DESC.
     */
    //TODO: Simplificar este método, está muito grande e pode ser subdividido.
    private function fetchAllQuery($orderBy = array(), $page = 1, $rowCount = null, $filters = array())
    {
        $tableInfo = $this->getDbTable()->info();

        //TODO: Avaliar todas essas possibilidades e simplificar:
        if (is_null($orderBy)
            || (!is_array($orderBy) && trim($orderBy) == '')
            || (is_array($orderBy) && (empty($orderBy) || trim($orderBy[0]) == ''))
        ) {
            $orderBy = $tableInfo['cols'][0];
        }

        if (is_null($orderBy)) {
            $orderBy = array();
        }

        if (is_string($orderBy)) {
            $orderBy = array($orderBy);
        }

        $orderByResult = array();

        //TODO: Repensar as verificações a seguir:
        foreach ($orderBy as $orderEntry) {
            //TODO: Verificar: há uma falha aqui em que "$orderEntry" vem com um espaço no final em algumas situações. O trim resolve por enquanto.
            $orderEntryArray = explode(' ', trim($orderEntry));

            // A entrada deve ser no formato "coluna DIREÇÃO", onde direção é "ASC" ou "DESC".
            // Com o explode, o array só pode ter estes dois valores, ou a entrada é inválida:
            if (count($orderEntryArray) > 2) {
                throw new Zend_Exception(
                    sprintf(
                        Zend_Registry::get('translate')->_('STLIB_MODEL_MODELMAPPER_INVALID_ORDER_COLUMN_%s'),
                        $orderEntry
                    )
                );
            }

            // Verifica se a coluna existe na tabela:
            if (!in_array($orderEntryArray[0], $tableInfo['cols'])) {
                throw new Zend_Exception(
                    sprintf(
                        Zend_Registry::get('translate')->_('STLIB_MODEL_MODELMAPPER_INVALID_ORDER_COLUMN_%s'),
                        $orderEntryArray[0]
                    )
                );
            }

            // Verifica se a direção da ordenação informada para a coluna é válida, se existir:
            if (count($orderEntryArray) > 1
                && strtoupper($orderEntryArray[1]) != self::FETCH_FILTER_ORDER_ASC
                && strtoupper($orderEntryArray[1]) != self::FETCH_FILTER_ORDER_DESC
            ) {
                throw new Zend_Exception(
                    sprintf(
                        Zend_Registry::get('translate')->_('STLIB_MODEL_MODELMAPPER_INVALID_ORDER_DIRECTION_%s'),
                        $orderEntryArray[1]
                    )
                );
            }

            // Adiciona a ordenação atual na lista de ordenações:
            if (count($orderEntryArray) > 0) {
                $orderByResult[] = implode(' ', $orderEntryArray);
            }
        }

        $select = $this->getDbTable()->select();
        if (!empty($filters)) {
            $this->__applyViewFilters($select, $filters);
        }

        if (!is_null($rowCount)) {
            // Não se pode, aqui, retornar zero valores. Se receber zero, o LIMIT não é aplicado:
            if ($rowCount > 0) {
                $select->order($orderByResult)->limitPage($page, $rowCount);
            } else {
                $select->order($orderByResult);
            }
        }

        return $select;
    }

    /**
     * Retorna todos os registros do tipo do objeto de acordo
     * com as informações solicitadas. Campos vindo das queries
     * com underline no início são ignorados na construção dos
     * objetos.
     *
     * @param  array $orderBy Lista de campos para ordenação.
     * @param  int $page A página solicitada na paginação.
     * @param  int $rowCount A quantidade limite de registros
     *         a retornar. Se for zero, todos os registros são
     *         retornados.
     * @param  array $filters Lista de filtros a aplicar.
     * @return array Um array de registros com todos os registros
     *         de acordo com as informações solicitadas.
     */
    public function fetchAll($orderBy = array(), $page = 1, $rowCount = null, $filters = array())
    {
        $select = $this->fetchAllQuery($orderBy, $page, $rowCount, $filters);
        $resultSet = $this->getDbTable()->fetchAll($select);

        $tableInfo  = $this->getDbTable()->info(Zend_Db_Table_Abstract::METADATA);

        // Preenche a coleção de models:
        $itens = array();
        $modelName = str_replace('Mapper', '', get_class($this));
        foreach ($resultSet as $row) {
            $item = new $modelName();
            $item->setMapper($this);

            $inflector = new Zend_Filter_Inflector(':varName');
            $inflector->setRules(array(
                ':varName'  => 'Word_UnderscoreToCamelCase'
            ));

            foreach ($row->toArray() as $varName => $varValue) {
                // Ignora campos começando com "_":
                if (substr($varName, 0, 1) != '_') {

                    //TODO: Adicionar suporte a outros formatos de dados a seguir:
                    if ($tableInfo[$varName]['DATA_TYPE'] == 'datetime') {
                        $varValue = $this->gmtStringToZendDate($varValue);
                    }

                    call_user_func(array($item, 'set' . $inflector->filter(array('varName' => $varName))), $varValue);
                }
            }
            $itens[] = $item;
        }

        return $itens;
    }

    /**
     * Converte uma string de data GMT em um objeto Zend_Date
     * no fuso horário local.
     *
     * @param  string $dateString Uma string de data GMT.
     * @return Zend_Date Um objeto Zend_Date com a data
     *         informada no fuso horário local.
     */
    //TODO: Este comportamento precisa de chamada manual da função e deveria ser automático:
    protected function gmtStringToZendDate($dateString)
    {
        $date = new Zend_Date();
        $date->setTimestamp(
            strtotime($dateString) - $date->getGmtOffset()
        );
        return $date;
    }

    /**
     * Converte um objeto Zend_Date com data no fuso horário
     * local para uma string de data GMT no formato Y-m-d H:i:s'.
     *
     * @param  Zend_Date $dateObject O objeto Zend_Date com a data
     *        a converter
     * @return string Uma string de data GMT com a data informada.
     */
    //TODO: Este comportamento precisa de chamada manual da função e deveria ser automático:
    protected function zendDateToGmtString(Zend_Date $dateObject)
    {
        return date(
            'Y-m-d H:i:s',
            $dateObject->getTimestamp() + $dateObject->getGmtOffset()
        );
    }

    /**
     * Aplica filtros específicos do model.
     *
     * @param Zend_Db_Select $select O select a adicionar os filtros.
     * @param array $filters Os filtros a aplicar.
     */
    abstract protected function __applyViewFilters($select, $filters);
}
