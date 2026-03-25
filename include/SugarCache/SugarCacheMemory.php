<?php



require_once('include/SugarCache/SugarCacheAbstract.php');

class SugarCacheMemory extends SugarCacheAbstract
{
    /**
     * @see SugarCacheAbstract::$_priority
     */
    protected $_priority = 999;

    /**
     * @see SugarCacheAbstract::useBackend()
     */
    public function useBackend()
    {
        // we'll always have this backend available
        return true;
    }

    /**
     * @see SugarCacheAbstract::_setExternal()
     *
     * Does nothing; cache is gone after request is done.
     */
    protected function _setExternal($key, $value) {}

    /**
     * @see SugarCacheAbstract::_getExternal()
     *
     * Does nothing; cache is gone after request is done.
     */
    protected function _getExternal($key) {}

    /**
     * @see SugarCacheAbstract::_clearExternal()
     *
     * Does nothing; cache is gone after request is done.
     */
    protected function _clearExternal($key) {}

    /**
     * @see SugarCacheAbstract::_resetExternal()
     *
     * Does nothing; cache is gone after request is done.
     */
    protected function _resetExternal() {}
}
