<?php


require_once('include/MVC/Controller/SugarController.php');
/**
 * MVC Controller Factory
 * @api
 */
class ControllerFactory
{
    /**
     * Obtain an instance of the correct controller.
     *
     * @return an instance of SugarController
     */
    public static function getController($module)
    {
        $class = ucfirst($module) . 'Controller';
        $customClass = 'Custom' . $class;
        if (file_exists('custom/modules/' . $module . '/controller.php')) {
            $customClass = 'Custom' . $class;
            require_once('custom/modules/' . $module . '/controller.php');
            if (class_exists($customClass)) {
                $controller = new $customClass();
            } else {
                if (class_exists($class)) {
                    $controller = new $class();
                }
            }
        } elseif (file_exists('modules/' . $module . '/controller.php')) {
            require_once('modules/' . $module . '/controller.php');
            if (class_exists($customClass)) {
                $controller = new $customClass();
            } else {
                if (class_exists($class)) {
                    $controller = new $class();
                }
            }
        } else {
            if (file_exists('custom/include/MVC/Controller/SugarController.php')) {
                require_once('custom/include/MVC/Controller/SugarController.php');
            }
            if (class_exists('CustomSugarController')) {
                $controller = new CustomSugarController();
            } else {
                $controller = new SugarController();
            }
        }
        //setup the controller
        $controller->setup($module);
        return $controller;
    }
}
