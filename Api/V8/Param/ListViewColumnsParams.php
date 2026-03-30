<?php

namespace Api\V8\Param;

use Api\V8\Param\Options as ParamOption;
use Api\V8\Param\OptionsResolver;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * ListViewColumnsParams
 *
 * @author gyula
 */
class ListViewColumnsParams extends BaseParam
{

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->parameters['moduleName'];
    }

    /**
     *
     * @param \Api\V8\Param\OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
        $this->setOptions(
            $resolver,
            [
                ParamOption\ModuleName::class,
            ]
        );
    }
}
