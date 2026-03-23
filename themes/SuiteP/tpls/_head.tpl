<!DOCTYPE html>
<html {$langHeader}>
<head>
    <link rel="SHORTCUT ICON" href="{$FAVICON_URL}">
    <meta http-equiv="Content-Type" content="text/html; charset={$APP.LBL_CHARSET}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1" />
    
    <script type="text/javascript" src='themes/SuiteP/js/gb_preload.js?v={$JS_VERSION}'></script>

    <title>{if $BROWSER_TITLE}{$BROWSER_TITLE}{else}{$APP.LBL_BROWSER_TITLE}{/if}</title>

    {$SUGAR_JS}
    {literal}
    <script type="text/javascript">
        SUGAR.themes.theme_name = '{/literal}{$THEME}{literal}';
        SUGAR.themes.theme_ie6compat = '{/literal}{$THEME_IE6COMPAT}{literal}';
        SUGAR.themes.hide_image = '{/literal}{sugar_getimagepath file="hide.gif"}{literal}';
        SUGAR.themes.show_image = '{/literal}{sugar_getimagepath file="show.gif"}{literal}';
        SUGAR.themes.loading_image = '{/literal}{sugar_getimagepath file="img_loading.gif"}{literal}';
        
        if (YAHOO.env.ua) UA = YAHOO.env.ua;
    </script>
    {/literal}
    {$SUGAR_CSS}

    <script type="text/javascript" src='themes/SuiteP/libs/js/select2.min.js'></script>
    <link rel="stylesheet" type="text/css" href="themes/SuiteP/libs/css/select2.min.css">

    <link rel="stylesheet" type="text/css" href="themes/SuiteP/css/colourSelector.php">
    <script type="text/javascript" src='{sugar_getjspath file="cache/include/javascript/sugar_field_grp.js"}'></script>

    <!-- JQUERY-UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js" integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
