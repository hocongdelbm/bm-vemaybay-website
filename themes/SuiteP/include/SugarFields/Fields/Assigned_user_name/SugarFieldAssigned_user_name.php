<?php
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldAssigned_user_name extends SugarFieldBase
{
    public function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex)
    {
        $vardef['options'] = get_user_array(false);
        if (!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html') {
            $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
            return $this->fetch($this->findTemplate('EditViewFunction'));
        } else {
            $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
            return $this->fetch($this->findTemplate('SearchView'));
        }
    }
}
