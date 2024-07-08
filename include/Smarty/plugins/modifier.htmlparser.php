<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty HTML parser modifier plugin
 *
 * Type:     modifier<br>
 * Name:     htmlparser<br>
 * Purpose:  parse html to display
 * @param string
 * @return string
 */

function smarty_modifier_htmlparser($string)
{
    $string = from_html($string, true);

    return $string;
}
