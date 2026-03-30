<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'data/Relationships/SugarRelationship.php';

/**
 * Create relationship objects.
 *
 * @api
 */
class SugarRelationshipFactory
{
    public static $rfInstance;

    protected $relationships;

    /**
     * SugarRelationshipFactory constructor.
     */
    protected function __construct()
    {
        //Load the relationship definitions from the cache.
        $this->loadRelationships();
    }

    /**
     * @static
     *
     * @return SugarRelationshipFactory
     */
    public static function getInstance()
    {
        if (is_null(self::$rfInstance)) {
            self::$rfInstance = new self();
        }

        return self::$rfInstance;
    }

    public static function rebuildCache()
    {
        self::getInstance()->buildRelationshipCache();
    }

    public static function deleteCache()
    {
        $file = self::getInstance()->getCacheFile();
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @param string $relationshipName name of relationship to load
     *
     * @return SugarRelationship|bool
     */
    public function getRelationship($relationshipName)
    {
        if (empty($this->relationships[$relationshipName])) {
            $GLOBALS['log']->error("Unable to find relationship $relationshipName");

            return false;
        }

        $def = $this->relationships[$relationshipName];

        $type = isset($def['true_relationship_type']) ? $def['true_relationship_type'] : $def['relationship_type'];
        switch ($type) {
            case 'many-to-many':
                if (isset($def['rhs_module']) && $def['rhs_module'] === 'EmailAddresses') {
                    require_once 'data/Relationships/EmailAddressRelationship.php';

                    return new EmailAddressRelationship($def);
                }
                require_once 'data/Relationships/M2MRelationship.php';

                return new M2MRelationship($def);
                break;
            case 'one-to-many':
                require_once 'data/Relationships/One2MBeanRelationship.php';
                //If a relationship has no table or join keys, it must be bean based
                if (empty($def['true_relationship_type']) || (empty($def['table']) && empty($def['join_table'])) || empty($def['join_key_rhs'])) {
                    return new One2MBeanRelationship($def);
                } else {
                    return new One2MRelationship($def);
                }
                break;
            case 'one-to-one':
                if (empty($def['true_relationship_type'])) {
                    require_once 'data/Relationships/One2OneBeanRelationship.php';

                    return new One2OneBeanRelationship($def);
                } else {
                    require_once 'data/Relationships/One2OneRelationship.php';

                    return new One2OneRelationship($def);
                }
                break;
        }

        $GLOBALS['log']->fatal("$relationshipName had an unknown type $type ");

        return false;
    }

    /**
     * @param string $relationshipName
     * @return bool
     */
    public function getRelationshipDef($relationshipName)
    {
        if (empty($this->relationships[$relationshipName])) {
            $GLOBALS['log']->error("Unable to find relationship $relationshipName");

            return false;
        }

        return $this->relationships[$relationshipName];
    }

    protected function loadRelationships()
    {
        if (is_file($this->getCacheFile())) {
            include $this->getCacheFile();
            $this->relationships = $relationships;
        } else {
            $this->buildRelationshipCache();
        }
    }

    protected function buildRelationshipCache()
    {
        global $beanList, $dictionary, $buildingRelCache;
        if ($buildingRelCache) {
            return;
        }
        $buildingRelCache = true;
        include 'modules/TableDictionary.php';

        if (empty($beanList)) {
            include 'include/modules.php';
        }
        //Reload ALL the module vardefs....
        foreach ($beanList as $moduleName => $beanName) {
            VardefManager::loadVardef($moduleName, BeanFactory::getObjectName($moduleName), false, array(
                //If relationships are not yet loaded, we can't figure out the rel_calc_fields.
                'ignore_rel_calc_fields' => true,
            ));
        }

        $relationships = array();

        //Grab all the relationships from the dictionary.
        foreach ($dictionary as $key => $def) {
            if (!empty($def['relationships'])) {
                foreach ($def['relationships'] as $relKey => $relDef) {
                    if ($key === $relKey) { //Relationship only entry, we need to capture everything
                        $relationships[$key] = array_merge(array('name' => $key), (array) $def, (array) $relDef);
                    } else {
                        $relationships[$relKey] = array_merge(array('name' => $relKey), (array) $relDef);
                        if (
                            !empty($relationships[$relKey]['join_table']) && empty($relationships[$relKey]['fields'])
                            && isset($dictionary[$relationships[$relKey]['join_table']]['fields'])
                        ) {
                            $relationships[$relKey]['fields'] = $dictionary[$relationships[$relKey]['join_table']]['fields'];
                        }
                    }
                }
            }
        }
        //Save it out
        sugar_mkdir(dirname($this->getCacheFile()), null, true);
        $out = "<?php \n \$relationships = " . var_export($relationships, true) . ';';
        sugar_file_put_contents_atomic($this->getCacheFile(), $out);

        $this->relationships = $relationships;

        //Now load all vardefs a second time populating the rel_calc_fields
        foreach ($beanList as $moduleName => $beanName) {
            VardefManager::loadVardef($moduleName, BeanFactory::getObjectName($moduleName));
        }

        $buildingRelCache = false;
    }

    /**
     * @return string
     */
    protected function getCacheFile()
    {
        return sugar_cached('Relationships/relationships.cache.php');
    }
}
