<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * This resolves SugarCRM Bug # 52929
 * http://www.sugarcrm.com/support/bugs.html#issue_52929
 *
 * Jeff Bickart
 * Twitter: @bickart
 * Email: jeff @ neposystems.com
 * Blog: http://sugarcrm-dev.blogspot.com
 */

class SugarWidgetFieldLong extends SugarWidgetFieldDecimal
{
    public function __construct(&$layout_manager)
    {
        parent::__construct($layout_manager);
    }
}
