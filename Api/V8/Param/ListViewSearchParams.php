<?php

namespace Api\V8\Param;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Param\Options as ParamOption;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ListViewSearchParams
 *
 * @author gyula
 */
class ListViewSearchParams extends BaseParam
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
     * @param OptionsResolver $resolver
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
