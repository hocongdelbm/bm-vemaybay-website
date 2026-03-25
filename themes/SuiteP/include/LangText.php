<?php

namespace SuiteCRM;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LangText
 *
 * @author gyula
 */
class LangText
{

    /**
     * string
     */
    const LOG_LEVEL = 'fatal';

    /**
     * integer
     */
    const USING_MOD_STRINGS = 1;

    /**
     * integer
     */
    const USING_APP_STRINGS = 2;

    /**
     * integer
     */
    const USING_ALL_STRINGS = 3;

    /**
     *
     * @var string
     */
    protected $key;

    /**
     *
     * @var array
     */
    protected $args;

    /**
     *
     * @var integer
     */
    protected $use;

    /**
     *
     * @var boolean
     */
    protected $log;

    /**
     *
     * @var boolean
     */
    protected $throw;

    /**
     *
     * @var string
     */
    protected $module;

    /**
     *
     * @var string
     */
    protected $lang;

    /**
     *
     * @param string|null $key
     * @param array|null $args
     * @param integer $use
     * @param boolean $log
     * @param boolean $throw
     * @param string $module
     * @param string $lang
     */
    public function __construct($key = null, $args = null, $use = self::USING_ALL_STRINGS, $log = true, $throw = true, $module = null, $lang = null)
    {
        $this->key = $key;
        $this->args = $args;
        $this->use = $use;
        $this->log = $log;
        $this->throw = $throw;
        $this->module = $module;
        $this->lang = $lang;
    }

    /**
     *
     * @global array $app_strings
     * @global array $mod_strings
     * @param string|null $key
     * @param array|null $args
     * @param integer|null $use
     * @param string $module
     * @param string $lang
     * @return string
     * @throws ErrorMessageException
     */
    public function getText($key = null, $args = null, $use = null, $module = null, $lang = null)
    { // TODO: rename the methode to LangText::translate()

        $this->selfUpdate($key, $args, $use);
        $textResolved = $this->resolveText($module, $lang);
        $text = $this->replaceArgs($textResolved);

        return $text;
    }

    /**
     *
     * @global array $app_strings
     * @global array $mod_strings
     * @global array $app_list_strings
     * @param string $module
     * @param string $lang
     * @return string
     */
    protected function resolveText($module = null, $lang = null)
    {
        $textFromGlobals = $this->resolveTextByGlobals();
        $text = $this->updateTextByModuleLang($textFromGlobals, $module, $lang);

        if (!$text) {
            if ($this->log) {
                ErrorMessage::handler('A language key does not found: [' . $this->key . ']', self::LOG_LEVEL, $this->throw);
            } else {
                $text = $this->key;
            }
        }

        return $text;
    }

    /**
     *
     * @global array $app_strings
     * @global array $mod_strings
     * @global array $app_list_strings
     * @return string
     */
    protected function resolveTextByGlobals()
    {
        // TODO: app_strings and mod_strings could be in separated methods
        global $app_strings, $mod_strings, $app_list_strings;

        switch ($this->use) {
            case self::USING_MOD_STRINGS:
                $text = $this->resolveTextByGlobal($mod_strings, $this->key);
                break;
            case self::USING_APP_STRINGS:
                $text = $this->resolveTextByGlobal($app_strings, $this->key);
                break;
            case self::USING_ALL_STRINGS:
                $text = $this->resolveTextByGlobal(
                    $mod_strings,
                    $this->key,
                    $this->resolveTextByGlobal(
                        $app_strings,
                        $this->key,
                        $this->resolveTextByGlobal($app_list_strings, $this->key)
                    )
                );
                break;
            default:
                ErrorMessage::drop('Unknown use case for translation: ' . $this->use);
                break;
        }
        return $text;
    }

    /**
     *
     * @param array $texts
     * @param string $key
     * @param string|null $default
     * @return string
     */
    protected function resolveTextByGlobal($texts, $key, $default = null)
    {
        $text = isset($texts[$key]) && $texts[$key] ? $texts[$key] : $default;
        return $text;
    }

    /**
     *
     * @param string $text
     * @param string $module
     * @param string $lang
     * @return string
     */
    protected function updateTextByModuleLang($text, $module = null, $lang = null)
    {
        $moduleLang = $this->getModuleLang($module, $lang);
        if (!$text && $moduleLang) {
            $text = isset($moduleLang[$this->key]) && $moduleLang[$this->key] ? $moduleLang[$this->key] : null;
        }
        return $text;
    }

    /**
     *
     * @param string $text
     * @return string
     */
    protected function replaceArgs($text)
    {
        foreach ((array) $this->args as $name => $value) {
            $text = str_replace('{' . $name . '}', $value, $text);
        }
        return $text;
    }

    /**
     *
     * @param string|null $key
     * @param array|null $args
     * @param integer|null $use
     */
    protected function selfUpdate($key = null, $args = null, $use = null)
    {
        if (!is_null($key)) {
            $this->key = $key;
        }

        if (!is_null($args)) {
            $this->args = $args;
        }

        if (!is_null($use)) {
            $this->use = $use;
        }
    }

    /**
     *
     * @param string $module
     * @param string $lang
     * @return array|null
     */
    protected function getModuleLang($module = null, $lang = null)
    {
        $moduleLang = null;

        $moduleName = $module ? $module : $this->module;

        if ($moduleName) {
            // retrieve translation for specified module
            $lang = $lang ? $lang : ($this->lang ? $this->lang : $GLOBALS['current_language']);
            include_once __DIR__ . '/SugarObjects/LanguageManager.php';
            $moduleLang = \LanguageManager::loadModuleLanguage($moduleName, $lang);
        }

        return $moduleLang;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $text = $this->getText();
        return $text;
    }

    /**
     *
     * @param string $key
     * @param array|null $args
     * @param boolean|null $log
     * @param boolean $throw
     * @param string $module
     * @param string $lang
     * @return string
     * @throws ErrorMessageException
     */
    public static function get($key, $args = null, $use = self::USING_ALL_STRINGS, $log = true, $throw = true, $module = null, $lang = null)
    {
        $text = new LangText($key, $args, $use, $log, $throw, $module, $lang);
        $translated = $text->getText();
        return $translated;
    }
}
