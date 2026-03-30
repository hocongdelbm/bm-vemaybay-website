<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


function get_hook_array($module_name)
{
    $hook_array = null;
    // This will load an array of the hooks to process
    $file = "custom/modules/$module_name/logic_hooks.php";
    if (file_exists($file)) {
        include($file);
    } else {
        LoggerManager::getLogger()->warn('File not found: ' . $file);
    }
    return $hook_array;
}



function check_existing_element($hook_array, $event, $action_array)
{
    if (isset($hook_array[$event])) {
        foreach ($hook_array[$event] as $action) {
            if ($action[1] == $action_array[1]) {
                return true;
            }
        }
    }
    return false;

    //end function check_existing_element
}

function replace_or_add_logic_type($hook_array)
{
    $new_entry = build_logic_file($hook_array);

    $new_contents = "<?php\n$new_entry\n?>";

    return $new_contents;
}



function write_logic_file($module_name, $contents)
{
    $file = "modules/" . $module_name . '/logic_hooks.php';
    $file = create_custom_directory($file);

    return sugar_file_put_contents($file, $contents) !== false;

    //end function write_logic_file
}

function build_logic_file($hook_array)
{
    $hook_contents = "";

    $hook_contents .= "// Do not store anything in this file that is not part of the array or the hook version.  This file will	\n";
    $hook_contents .= "// be automatically rebuilt in the future. \n ";
    $hook_contents .= "\$hook_version = 1; \n";
    $hook_contents .= "\$hook_array = Array(); \n";
    $hook_contents .= "// position, file, function \n";

    foreach ($hook_array as $event_array => $event) {
        $hook_contents .= "\$hook_array['" . $event_array . "'] = Array(); \n";

        foreach ($event as $second_key => $elements) {
            $hook_contents .= "\$hook_array['" . $event_array . "'][] = ";
            $hook_contents .= "Array(" . $elements[0] . ", '" . $elements[1] . "', '" . $elements[2] . "','" . $elements[3] . "', '" . $elements[4] . "'); \n";
        }

        //end foreach hook_array as event => action_array
    }

    $hook_contents .= "\n\n";

    return $hook_contents;

    //end function build_logic_file
}
