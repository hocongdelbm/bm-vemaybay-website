<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once('include/database/MssqlManager.php');

/**
 * SQL Server driver for FreeTDS
 */
class FreeTDSManager extends MssqlManager
{
    public $dbName = 'FreeTDS SQL Server';
    public $variant = 'freetds';
    public $label = 'LBL_MSSQL2';

    protected $capabilities = array(
        "affected_rows" => true,
        'fulltext' => true,
        'limit_subquery' => true,
    );

    protected $type_map = array(
            'int'      => 'int',
            'double'   => 'float',
            'float'    => 'float',
            'uint'     => 'int',
            'ulong'    => 'int',
            'long'     => 'bigint',
            'short'    => 'smallint',
            'varchar'  => 'nvarchar',
            'text'     => 'nvarchar(max)',
            'longtext' => 'nvarchar(max)',
            'date'     => 'datetime',
            'enum'     => 'nvarchar',
            'relate'   => 'nvarchar',
            'multienum'=> 'nvarchar(max)',
            'html'     => 'nvarchar(max)',
            'longhtml' => 'text',
        'emailbody' => 'nvarchar(max)',
            'datetime' => 'datetime',
            'datetimecombo' => 'datetime',
            'time'     => 'datetime',
            'bool'     => 'bit',
            'tinyint'  => 'tinyint',
            'char'     => 'char',
            'blob'     => 'nvarchar(max)',
            'longblob' => 'nvarchar(max)',
            'currency' => 'decimal(26,6)',
            'decimal'  => 'decimal',
            'decimal2' => 'decimal',
            'id'       => 'varchar(36)',
            'url'      => 'nvarchar',
            'encrypt'  => 'nvarchar',
            'file'     => 'nvarchar',
            'decimal_tpl' => 'decimal(%d, %d)',
    );

    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        global $app_strings;
        if (is_array($sql)) {
            return $this->queryArray($sql, $dieOnError, $msg, $suppress);
        }

        $sql = $this->_appendN($sql);
        return parent::query($sql, $dieOnError, $msg, $suppress, $keepResult);
    }

    /**
     * Check if this driver can be used
     * @return bool
     */
    public function valid()
    {
        return function_exists("mssql_connect") && DBManagerFactory::isFreeTDS();
    }
}
