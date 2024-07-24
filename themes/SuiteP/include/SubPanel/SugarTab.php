<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Tab representation
 * @api
 */
class SugarTab
{
    public function __construct($type='singletabmenu')
    {
        $this->type = $type;
        $this->ss = new Sugar_Smarty();
    }

    public function setup($mainTabs, $otherTabs=array(), $subTabs=array(), $selected_group='All')
    {
        global $sugar_version, $sugar_config, $current_user;

        $max_tabs = $current_user->getPreference('max_tabs');
        if (!isset($max_tabs) || $max_tabs <= 0) {
            $max_tabs = $GLOBALS['sugar_config']['default_max_tabs'];
        }

        $key_all = translate('LBL_TABGROUP_ALL');
        if ($selected_group == 'All') {
            $selected_group = $key_all;
        }

        $moreTabs = array_slice($mainTabs, $max_tabs);
        /* If the current tab is in the 'More' menu, move it into the visible menu. */
        if (!empty($moreTabs[$selected_group])) {
            $temp = array($selected_group => $mainTabs[$selected_group]);
            unset($mainTabs[$selected_group]);
            array_splice($mainTabs, $max_tabs-1, 0, $temp);
        }

        $subpanelTitles = array();

        if (isset($otherTabs[$key_all]) && isset($otherTabs[$key_all]['tabs'])) {
            foreach ($otherTabs[$key_all]['tabs'] as $subtab) {
                $subpanelTitles[$subtab['key']] = $subtab['label'];
            }
        }

        $this->ss->assign('showLinks', 'false');
        $this->ss->assign('sugartabs', array_slice($mainTabs, 0, $max_tabs));
        $this->ss->assign('moreMenu', array_slice($mainTabs, $max_tabs));
        $this->ss->assign('othertabs', $otherTabs);
        $this->ss->assign('subpanelTitlesJSON', json_encode($subpanelTitles));
        $this->ss->assign('startSubPanel', $selected_group);
        $this->ss->assign('sugarVersionJsStr', "?s=$sugar_version&c={$sugar_config['js_custom_version']}");
        if (!empty($mainTabs)) {
            $mtak = array_keys($mainTabs);
            $this->ss->assign('moreTab', $mainTabs[$mtak[min(count($mtak)-1, $max_tabs-1)]]['label']);
        }
    }

    public function fetch()
    {
        return $this->ss->fetch('include/SubPanel/tpls/' . $this->type . '.tpl');
    }

    public function display()
    {
        $this->ss->display('include/SubPanel/tpls/' . $this->type . '.tpl');
    }
}
