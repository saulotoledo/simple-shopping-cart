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
 * @subpackage STLib_Model_DbTable
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */

/**
 * Trabalha o redimensionamento de imagens.
 *
 * @category   STLib
 * @package    STLib_Model
 * @subpackage STLib_Model_DbTable
 * @author     Saulo Soares de Toledo <saulotoledo@gmail.com>
 * @copyright  Copyright (c) 2014, Saulo S. Toledo
 * @license    BSD 3-Clause license
 */
abstract class STLib_Model_DbTable_PrefixedDbTable extends Zend_Db_Table_Abstract
{
    /**
     * Constrói o nome da tabela. Adiciona prefixo com o valor
     * de TABLE_PREFIX se esta constante estiver definida.
     */
    protected function _setupTableName()
    {
        parent::_setupTableName();
        if (defined('TABLE_PREFIX')) {
            $this->_name = TABLE_PREFIX . $this->_name;
        }
    }
}
