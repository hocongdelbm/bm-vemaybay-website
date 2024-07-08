<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Retrieves an array of all the layout_defs defined in the app.
 */

function get_layout_defs()
{
    //TODO add global memory cache support here.  If there is an in memory cache, leverage it.
    global $layout_defs;
    return $layout_defs;
}
