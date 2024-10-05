<?php

class TemplateSampleService
{
    /**
     * @return string
     */
    public static function getAbsoluteLogoUrl()
    {
        global $sugar_config;
        $baseUrl = $sugar_config['site_url'];
        $logoUrlArr = explode('?', SugarThemeRegistry::current()->getImageURL('company_logo.png'));
        return $baseUrl . '/' . $logoUrlArr[0];
    }
}
