<?php
require_once 'modules/ModuleBuilder/Module/StudioModule.php' ;

class EmployeesStudioModule extends StudioModule
{
    public function getProvidedSubpanels()
    {
        // Much like pointy haired bosses, other modules should not be able to relate to Employees.
        return false;
    }

    public function getModule()
    {
        $normalModules = parent::getModule();
        
        if (isset($normalModules[translate('LBL_RELATIONSHIPS')])) {
            unset($normalModules[translate('LBL_RELATIONSHIPS')]);
        }

        return $normalModules;
    }
}
