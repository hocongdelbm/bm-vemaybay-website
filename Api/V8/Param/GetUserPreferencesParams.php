<?php

namespace Api\V8\Param;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Param\Options as ParamOption;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * GetUserPreferencesParams
 *
 * @author gyula
 */
class GetUserPreferencesParams extends BaseParam
{

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->parameters['id'];
    }

    /**
     *
     * @param OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
        $this->setOptions($resolver, [ParamOption\Id::class]);
    }
}
