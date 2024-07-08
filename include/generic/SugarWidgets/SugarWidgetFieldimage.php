<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFieldImage extends SugarWidgetFieldVarchar
{
    public function displayListPlain($layout_def)
    {
        $value = $this->_get_list_value($layout_def);
        return "<a href=\"javascript:SUGAR.image.lightbox('index.php?entryPoint=download&id=$value&type=SugarFieldImage&isTempFile=1')\">"
               . translate("LBL_VIEW_IMAGE") . '</a>';
    }
}
