<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SugarWidgetSubPanelEmailLink
 */
class SugarWidgetSubPanelEmailLink extends SugarWidgetField
{
    /**
     * @param array $layout_def
     * @return string
     */
    public function displayList(&$layout_def)
    {
        global $current_user;
        global $focus;

        require_once('modules/Emails/EmailUI.php');
        $emailUi = new EmailUI();
        if ($focus !== null) {
            return $emailUi->populateComposeViewFields($focus);
        }
        if (!empty($layout_def['module']) && !empty($layout_def['fields']) && !empty($layout_def['fields']['ID'])) {
            $bean = BeanFactory::getBean($layout_def['module'], $layout_def['fields']['ID']);
            if (!empty($bean)) {
                return $emailUi->populateComposeViewFields($bean);
            }
        }
        if ($current_user !== null) {
            return $emailUi->populateComposeViewFields($current_user);
        }
    }
}
