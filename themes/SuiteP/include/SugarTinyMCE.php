<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 * PHP wrapper class for Javascript driven TinyMCE WYSIWYG HTML editor
 */
class SugarTinyMCE
{
    public $jsroot = "include/javascript/tiny_mce/";
    public $customConfigFile = 'custom/include/tinyButtonConfig.php';
    public $customDefaultConfigFile = 'custom/include/tinyMCEDefaultConfig.php';
    public $buttonConfigs = array(
            'default' => array(
                        'buttonConfig' => "code,help,separator,bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,
	                     					justifyfull,separator,forecolor,backcolor,separator,styleselect,formatselect,fontselect,fontsizeselect,",
                        'buttonConfig2' => "cut,copy,paste,pastetext,pasteword,selectall,separator,search,replace,separator,bullist,numlist,separator,outdent,
	                     					indent,separator,ltr,rtl,separator,undo,redo,separator, link,unlink,anchor,image,separator,sub,sup,separator,charmap,
	                     					visualaid",
                        'buttonConfig3' => "tablecontrols,separator,advhr,hr,removeformat,separator,insertdate,inserttime,separator,preview"),
            'email_compose' => array(
                        'buttonConfig' => "code,help,separator,bold,italic,underline,strikethrough,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,
	                     					justifyfull,separator,link,unlink,separator,forecolor,backcolor,separator,styleselect,formatselect,fontselect,fontsizeselect,",
                        'buttonConfig2' => "",
                        'buttonConfig3' => ""),
            'email_compose_light' => array(
                        'buttonConfig' => "code,separator,bold,italic,underline,strikethrough,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,
	                     					justifyfull,separator,link,unlink,separator,forecolor,backcolor,separator,formatselect,fontselect,fontsizeselect,",
                        'buttonConfig2' => "",
                        'buttonConfig3' => ""),
    );

    public $pluginsConfig = array(
        'email_compose_light' => 'insertdatetime,paste,directionality,safari',
        'email_compose' => 'advhr,insertdatetime,table,preview,paste,searchreplace,directionality,fullpage',
    );

    public $defaultConfig = array(
        'convert_urls' => false,
        'valid_children' => '+body[style]',
        'height' => 300,
        'width'	=> '100%',
        'theme'	=> 'advanced',
        'theme_advanced_toolbar_align' => "left",
        'theme_advanced_toolbar_location'	=> "top",
        'theme_advanced_buttons1'	=> "",
        'theme_advanced_buttons2'	=> "",
        'theme_advanced_buttons3'	=> "",
        'strict_loading_mode'	=> true,
        'mode'	=> 'exact',
        'language' => 'en',
        'plugins' => 'advhr,insertdatetime,table,preview,paste,searchreplace,directionality',
        'elements'	=> '',
        'extended_valid_elements' => 'style[dir|lang|media|title|type],hr[class|width|size|noshade],@[class|style]',
        'content_css' => 'include/javascript/tiny_mce/themes/advanced/skins/default/content.css',

    );


    /**
     * Sole constructor
     */
    public function __construct()
    {
        $this->overloadButtonConfigs();
        $this->overloadDefaultConfigs();
    }

    /**
     * Returns the Javascript necessary to initialize a TinyMCE instance for a given <textarea> or <div>
     * @param string target Comma delimited list of DOM ID's, <textarea id='someTarget'>
     * @return string
     */
    public function getInstance($targets = "", $type = 'default')
    {
        global $json;

        if (empty($json)) {
            $json = getJSONobj();
        }

        $config = $this->defaultConfig;
        //include tinymce lang file
        $lang = substr($GLOBALS['current_language'], 0, 2);
        if (file_exists('include/javascript/tiny_mce/langs/'.$lang.'.js')) {
            $config['language'] = $lang;
        }
        $config['directionality'] = SugarThemeRegistry::current()->directionality;
        $config['elements'] = $targets;
        $config['theme_advanced_buttons1'] = $this->buttonConfigs[$type]['buttonConfig'];
        $config['theme_advanced_buttons2'] = $this->buttonConfigs[$type]['buttonConfig2'];
        $config['theme_advanced_buttons3'] = $this->buttonConfigs[$type]['buttonConfig3'];

        $jsConfig = $json->encode($config);

        $instantiateCall = '';
        $unique = 'default';
        if (!empty($targets)) {
            $exTargets = explode(",", $targets);
            $unique = $exTargets[0];
            foreach ($exTargets as $instance) {
                $instantiateCall .= "tinyMCE.execCommand('mceAddControl', false, document.getElementById('{$instance}'));\n";
            }
        }
        $path = getJSPath('include/javascript/tiny_mce/tiny_mce.js');
        $ret =<<<eoq
<script type="text/javascript"  src="$path"></script>
<script type="text/javascript">
<!--
$(document).ready(function(){
    if (!SUGAR.ajaxUI.hist_loaded){
      load_mce_{$unique}();
    }
    if (SUGAR.ajaxUI && SUGAR.ajaxUI.hist_loaded){
      setTimeout(function(){ load_mce_{$unique}();},40);
    }
  });
function load_mce_{$unique}(){
    if (!SUGAR.util.isTouchScreen()) {
        if(tinyMCE.editors.length === 0 ){
            tinyMCE.init({$jsConfig});
        }else{
           {$instantiateCall}
        }
    } else {
eoq;
        $exTargets = explode(",", $targets);
        foreach ($exTargets as $instance) {
            $ret .=<<<eoq
    document.getElementById('$instance').style.width = '100%';
    document.getElementById('$instance').style.height = '100px';
eoq;
        }
        $ret .=<<<eoq
    }
}
-->
</script>

eoq;
        return $ret;
    }

    public function getConfig($type = 'default')
    {
        global $json;

        if (empty($json)) {
            $json = getJSONobj();
        }

        $config = $this->defaultConfig;
        //include tinymce lang file
        $lang = substr($GLOBALS['current_language'], 0, 2);
        if (file_exists('include/javascript/tiny_mce/langs/'.$lang.'.js')) {
            $config['language'] = $lang;
        }
        $config['theme_advanced_buttons1'] = $this->buttonConfigs[$type]['buttonConfig'];
        $config['theme_advanced_buttons2'] = $this->buttonConfigs[$type]['buttonConfig2'];
        $config['theme_advanced_buttons3'] = $this->buttonConfigs[$type]['buttonConfig3'];

        if (isset($this->pluginsConfig[$type])) {
            $config['plugins'] = $this->pluginsConfig[$type];
        }

        $jsConfig = $json->encode($config);
        return "var tinyConfig = ".$jsConfig.";";
    }

    /**
     * This function takes in html code that has been produced (and somewhat mauled) by TinyMCE
     * and returns a cleaned copy of it.
     *
     * @param $html
     * @return $html with all the tinyMCE specific html removed
     */
    public function cleanEncodedMCEHtml($html)
    {
        $html = str_replace("mce:script", "script", $html);
        $html = str_replace("mce_src=", "src=", $html);
        $html = str_replace("mce_href=", "href=", $html);
        return $html;
    }

    /**
     * Reload the default button configs by allowing admins to specify
     * which tinyMCE buttons will be displayed in a separate config file.
     *
     */
    private function overloadButtonConfigs()
    {
        if (file_exists($this->customConfigFile)) {
            require_once($this->customConfigFile);

            if (!isset($buttonConfigs)) {
                return;
            }

            foreach ($buttonConfigs as $k => $v) {
                if (isset($this->buttonConfigs[$k])) {
                    $this->buttonConfigs[$k] = $v;
                }
            }
        }
    }

    /**
     * Reload the default tinyMCE config, preserving our default extended
     * allowable tag set.
     *
     */
    private function overloadDefaultConfigs()
    {
        if (file_exists($this->customDefaultConfigFile)) {
            require_once($this->customDefaultConfigFile);

            if (!isset($defaultConfig)) {
                return;
            }

            foreach ($defaultConfig as $k => $v) {
                if (isset($this->defaultConfig[$k])) {
                    if ($k == "extended_valid_elements") {
                        $this->defaultConfig[$k] .= "," . $v;
                    } else {
                        $this->defaultConfig[$k] = $v;
                    }
                }
            }
        }
    }
} // end class def
