<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


$used_aliases = array();
$alias_map = array();

class SugarWidgetReportField extends SugarWidgetField
{
    /**
     * Layout manager reporter attribute
     * @var SugarBean
     */
    protected $reporter;

    public function __construct(&$layout_manager)
    {
        parent::__construct($layout_manager);
        $this->reporter = $this->layout_manager->getAttribute("reporter");
    }

    public function getSubClass($layout_def)
    {
        if (! empty($layout_def['type'])) {
            if ($layout_def['type'] == 'time') {
                $layout_def['widget_class'] = 'Fielddate';
            } else {
                $layout_def['widget_class'] = 'Field'.$layout_def['type'];
            }
            return $this->layout_manager->getClassFromWidgetDef($layout_def);
        } else {
            return $this;
        }
    }


    public function display($layout_def)
    {
        $obj = $this->getSubClass($layout_def);

        $context = $this->layout_manager->getAttribute('context');
        $func_name = 'display'.$context;


        if (! empty($context) && method_exists($obj, $func_name)) {
            return  $obj->$func_name($layout_def);
        } else {
            return 'display not found:'.$func_name;
        }
    }

    public function _get_column_select_special($layout_def)
    {
        $alias = '';
        if (! empty($layout_def['table_alias'])) {
            $alias = $layout_def['table_alias'];
        }

        if ($layout_def['name'] == 'weighted_sum') {
            return sprintf(
                "SUM(%s * %s * 0.01)",
                $this->reporter->db->convert("$alias.probability", "IFNULL", array(0)),
                $this->reporter->db->convert("$alias.amount_usdollar", "IFNULL", array(0))
            );
        }
        if ($layout_def['name'] == 'weighted_amount') {
            return sprintf(
                "AVG(%s * %s * 0.01)",
                $this->reporter->db->convert("$alias.probability", "IFNULL", array(0)),
                $this->reporter->db->convert("$alias.amount_usdollar", "IFNULL", array(0))
            );
        }
    }

    public function _get_column_select($layout_def)
    {
        global $reportAlias;
        if (!isset($reportAlias)) {
            $reportAlias = array();
        }

        if (! empty($layout_def['table_alias'])) {
            $alias = $layout_def['table_alias'].".".$layout_def['name'];
        } else {
            if (! empty($layout_def['name'])) {
                $alias = $layout_def['name'];
            } else {
                $alias = "*";
            }
        }

        if (! empty($layout_def['group_function'])) {
            if ($layout_def['name'] == 'weighted_sum' || $layout_def['name'] == 'weighted_amount') {
                $alias = $this->_get_column_select_special($layout_def);
                $reportAlias[$alias] = $layout_def;
                return $alias;
            }

            // Use IFNULL only if it's not AVG aggregate
            // because it adds NULL rows to the count when it should not, thus getting wrong result
            if ($layout_def['group_function'] != 'avg') {
                $alias = $this->reporter->db->convert($alias, 'IFNULL', array(0));
            }

            // for a field with type='currency' conversion of values into a user-preferred currency
            if ($layout_def['type'] == 'currency' && strpos($layout_def['name'], '_usdoll') === false) {
                $currency = $this->reporter->currency_obj;
                $currency_alias = isset($layout_def['currency_alias'])
                ? $layout_def['currency_alias'] : $currency->table_name;
                $query = $this->reporter->db->convert($currency_alias.".conversion_rate", "IFNULL", array(1));
                // We need to use convert() for AVG because of Oracle
                if ($layout_def['group_function'] != 'avg') {
                    $alias = "{$layout_def['group_function']}($alias/{$query})*{$currency->conversion_rate}";
                } else {
                    $alias = $this->reporter->db->convert("$alias/$query", "AVG") . " * {$currency->conversion_rate}";
                }
            } else {
                // We need to use convert() for AVG because of Oracle
                if ($layout_def['group_function'] != 'avg') {
                    $alias = "{$layout_def['group_function']}($alias)";
                } else {
                    $alias = $this->reporter->db->convert($alias, "AVG");
                }
            }
        }

        $reportAlias[$alias] = $layout_def;
        return $alias;
    }

    public function querySelect(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." ".$this->_get_column_alias($layout_def)."\n";
    }

    public function queryGroupBy($layout_def)
    {
        return $this->_get_column_select($layout_def)." \n";
    }


    public function queryOrderBy($layout_def)
    {
        $field_def = array();
        if (!empty($this->reporter->all_fields[$layout_def['column_key']])) {
            $field_def = $this->reporter->all_fields[$layout_def['column_key']];
        }

        if (!empty($layout_def['group_function'])) {
            $order_by = $this->_get_column_alias($layout_def);
        } elseif (!empty($field_def['sort_on'])) {
            $order_by = $layout_def['table_alias'].".".$field_def['sort_on'];
            if (!empty($field_def['sort_on2'])) {
                $order_by .= ', ' . $layout_def['table_alias'].".".$field_def['sort_on2'];
            }
        } else {
            $order_by = $this->_get_column_alias($layout_def)." \n";
        }

        //use sugar db function convert on order by string to convert to varchar.  This is mainly for db's
        //that do not allow sorting on clob/text fields
        if ($this->reporter->db->isTextType($this->reporter->db->getFieldType($field_def))) {
            $order_by = $this->reporter->db->convert($order_by, 'text2char', array(10000)); // array(10000) is for db2 only
        }

        if (empty($layout_def['sort_dir']) || $layout_def['sort_dir'] == 'a') {
            return $order_by." ASC";
        } else {
            return $order_by." DESC";
        }
    }


    public function queryFilter($layout_def)
    {
        $method_name = "queryFilter".$layout_def['qualifier_name'];
        return $this->$method_name($layout_def);
    }

    public function displayHeaderCell($layout_def)
    {
        global $start_link_wrapper,$end_link_wrapper;


        // don't show sort links if name isn't defined
        $no_sort = $this->layout_manager->getAttribute('no_sort');
        if (empty($layout_def['name']) || ! empty($no_sort) || ! empty($layout_def['no_sort'])) {
            return $layout_def['label'];
        }



        $sort_by ='';
        if (! empty($layout_def['table_key']) && ! empty($layout_def['name'])) {
            if (! empty($layout_def['group_function']) && $layout_def['group_function'] == 'count') {
                $sort_by = $layout_def['table_key'].":".'count';
            } else {
                $sort_by = $layout_def['table_key'].":".$layout_def['name'];
                if (! empty($layout_def['column_function'])) {
                    $sort_by .= ':'.$layout_def['column_function'];
                } else {
                    if (! empty($layout_def['group_function'])) {
                        $sort_by .= ':'.$layout_def['group_function'];
                    }
                }
            }
        } else {
            return $this->displayHeaderCellPlain($layout_def);
        }

        $start = empty($start_link_wrapper) ? '': $start_link_wrapper;
        $end = empty($end_link_wrapper) ? '': $end_link_wrapper;

        // unable to retrieve the vardef here, exclude columns of type clob/text from being sortable

        if (!in_array($layout_def['name'], array('description', 'account_description', 'lead_source_description', 'status_description', 'to_addrs', 'cc_addrs', 'bcc_addrs', 'work_log', 'objective', 'resolution'))) {
            $header_cell = "<a class=\"listViewThLinkS1\" href=\"".$start.$sort_by.$end."\">";
            $header_cell .= $this->displayHeaderCellPlain($layout_def);
            $objListView = new ListView();
            $header_cell .= $objListView->getArrowUpDownStart(isset($layout_def['sort']) ? $layout_def['sort'] : '');
            $header_cell .= $objListView->getArrowUpDownEnd(isset($layout_def['sort']) ? $layout_def['sort'] : '');
            $header_cell .= "</a>";
            return $header_cell;
        }

        return $this->displayHeaderCellPlain($layout_def);
    }

    public function query($layout_def)
    {
        $obj = $this->getSubClass($layout_def);

        $context = $this->layout_manager->getAttribute('context');
        $func_name = 'query'.$context;

        if (! empty($context) && method_exists($obj, $func_name)) {
            return  $obj->$func_name($layout_def);
        } else {
            return '';
        }
    }

    public function _get_column_alias($layout_def)
    {
        $alias_arr = array();

        if (!empty($layout_def['table_key']) && $layout_def['table_key'] == 'self' && !empty($layout_def['name']) && $layout_def['name'] == 'id') {
            return 'primaryid';
        }

        // Bug: 44605
        // this comment is being added to trigger the upgrade package
        if (! empty($layout_def['group_function']) && $layout_def['group_function']=='count') {
            return $layout_def['table_alias'] . '__count';
        }

        if (! empty($layout_def['table_alias'])) {
            array_push($alias_arr, $layout_def['table_alias']);
        }

        if (! empty($layout_def['group_function']) && $layout_def['group_function'] != 'weighted_amount' && $layout_def['group_function'] != 'weighted_sum') {
            array_push($alias_arr, $layout_def['group_function']);
        } else {
            if (! empty($layout_def['column_function'])) {
                array_push($alias_arr, $layout_def['column_function']);
            } else {
                if (! empty($layout_def['qualifier'])) {
                    array_push($alias_arr, $layout_def['qualifier']);
                }
            }
        }

        if (! empty($layout_def['name'])) {
            array_push($alias_arr, $layout_def['name']);
        }

        global $used_aliases, $alias_map;

        $alias = strtolower(implode("_", $alias_arr));

        $short_alias = $this->getTruncatedColumnAlias($alias);

        if (empty($used_aliases[$short_alias])) {
            $alias_map[$alias] = $short_alias;
            $used_aliases[$short_alias] = 1;
            return $short_alias;
        } else {
            if (! empty($alias_map[$alias])) {
                return $alias_map[$alias];
            } else {
                $alias_map[$alias] = $short_alias.'_'.$used_aliases[$short_alias];
                $used_aliases[$short_alias]++;
                return $alias_map[$alias];
            }
        }
    }

    public function queryFilterEmpty($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NULL OR $column = ".$this->reporter->db->emptyValue($layout_def['type']).")";
    }

    public function queryFilterIs($layout_def)
    {
        return '( '.$this->_get_column_select($layout_def)."='".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."')\n";
    }

    public function queryFilteris_not($layout_def)
    {
        return '( '.$this->_get_column_select($layout_def)."<>'".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."')\n";
    }

    public function queryFilterNot_Empty($layout_def)
    {
        /** @var $db DBManager */
        $db = $this->reporter->db;
        $column = $this->_get_column_select($layout_def);
        return "(coalesce(" . $db->convert($column, "length") . ",0) > 0)\n";
    }
}
