<?php

namespace Api\V8\Helper;

/**
 * Class ModuleListProvider
 * @package Api\V8\Helper
 */
class ModuleListProvider
{
    /**
     * @return array
     */
    public function getModuleList()
    {
        global $current_user;

        $modules = query_module_access_list($current_user);
        \ACLController::filterModuleList($modules, false);
        $modules = $this->removeInvisibleModules($modules);
        $modules = $this->markACLAccess($modules);
        $modules = $this->addModuleLabels($modules);

        return $modules;
    }

    /**
     * @param $modules
     * @return mixed
     */
    private function addModuleLabels($modules)
    {
        global $app_list_strings;

        foreach ($modules as $moduleName => &$moduleData) {
            $moduleData['label'] = $app_list_strings['moduleList'][$moduleName];
        }
        return $modules;
    }

    /**
     * @param $modules
     * @return array
     */
    private function removeInvisibleModules($modules)
    {
        global $modInvisList;

        foreach ($modInvisList as $invis) {
            unset($modules[$invis]);
        }

        return $modules;
    }

    /**
     * @param $modules
     * @return mixed
     */
    private function markACLAccess($modules)
    {
        global $current_user;

        $modulesWithAccess = [];
        $moduleActions = \ACLAction::getUserActions($current_user->id, true);

        foreach ($moduleActions as $moduleName => $value) {
            if (!in_array($moduleName, $modules, true)) {
                continue;
            }
            $access = $this->buildAccessArray($moduleName, $value['module']);
            if (!count($access)) {
                continue;
            }
            $modulesWithAccess[$moduleName] = [
                'label' => '',
                'access' => array_unique($access)
            ];
        }

        return $modulesWithAccess;
    }

    /**
     * @param mixed $moduleName
     * @param mixed $actions
     * @return array
     */
    private function buildAccessArray($moduleName, $actions)
    {
        $access = [];
        foreach ($actions as $actionName => $record) {
            if (!$this->hasACL($record['aclaccess'], $moduleName)) {
                continue;
            }
            $access[] = $actionName;
        }
        return $access;
    }

    /**
     * @param mixed $level
     * @param mixed $module
     * @return bool
     */
    private function hasACL($level, $module)
    {
        global $current_user;

        if (is_admin(is_admin($current_user))) {
            return true;
        }

        return $level >= ACL_ALLOW_ENABLED;
    }
}
