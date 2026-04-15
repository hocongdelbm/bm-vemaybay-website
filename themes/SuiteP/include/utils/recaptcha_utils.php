<?php
if (!defined('sugarEntry') || !sugarEntry) {
       die('Not A Valid Entry Point');
}

use \ReCaptcha\ReCaptcha as ReCaptcha;
use \ReCaptcha\Response as Response;
use SuiteCRM\Utility\SuiteLogger as SuiteLogger;

/**
 * @return array|null
 */
function getRecaptchaSettings()
{
    $administration = BeanFactory::newBean('Administration');
    $administration->retrieveSettings('captcha');

    return $administration->settings;
}

/**
 * @param array $settings
 * @see getRecaptchaSettings()
 * @return bool|string false if setting is not found
 */
function getRecaptchaSiteKey(array $settings)
{
    return isset($settings['captcha_public_key']) ? $settings['captcha_public_key'] : false;
}

/**
 * @return bool|string
 */
function getRecaptchaChallengeField()
{
    return isset($_REQUEST['recaptcha_challenge_field']) ? $_REQUEST['recaptcha_challenge_field'] : false;
}

/**
 * @param array $settings
 * @see getRecaptchaSettings()
 * @return bool|string false if setting is not found
 */
function getRecaptchaPrivateKey(array $settings)
{
    return isset($settings['captcha_private_key']) ? $settings['captcha_private_key'] : false;
}

/**
 * @param array $settings
 * @see getRecaptchaSettings()
 * @return bool|string false if setting is not found
 */
function getRecaptchaEnabled(array $settings)
{
    return isset($settings['captcha_on']) ? $settings['captcha_on'] : false;
}

/**
 * @param array $settings
 * @see getRecaptchaSettings()
 * @return bool
 */
function isRecaptchaEnabled(array $settings)
{
    return getRecaptchaEnabled($settings) === '1';
}

/**
 * @return string|null
 */
function getRecapthaResponse()
{
    return $_REQUEST['recaptcha_response_field'];
}

/**
 * @return string|null
 */
function getRemoteIpAddress()
{
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * @param ReCaptcha $reCaptcha
 * @param string $response
 * @param string $remoteIpAddress
 * @return Response
 */
function verifyRecapthaResponse(ReCaptcha $reCaptcha, $response, $remoteIpAddress)
{
    return $reCaptcha->verify($response, $remoteIpAddress);
}

/**
 * @param Response $response
 * @return bool
 */
function isRecapthaResponseVerified(Response $response)
{
    return $response->isSuccess();
}

/**
 * @param Response $response
 * @return string
 */
function getRecaptchaErrors(Response $response)
{
    $errors = '';

    foreach ($response->getErrorCodes() as $code) {
        $errors .= '<kbd>' . $code . '</kbd>';
    }

    return $errors;
}

/**
 * @return string Success or the error(s) found
 */
function displayRecaptchaValidation()
{
    $log = new SuiteLogger();
    /** @var array $settings */
    $settings = getRecaptchaSettings();

    if (
        !isRecaptchaEnabled($settings)
        || empty(getRecaptchaSiteKey($settings))
        || empty(getRecaptchaPrivateKey($settings))
    ) {
        $msg = 'Missing Captcha Settings';
        $log->error($msg);

        return $msg;
    }

    /** @var Response $response */
    $response = verifyRecapthaResponse(
        new ReCaptcha(getRecaptchaPrivateKey($settings)),
        getRecapthaResponse(),
        getRemoteIpAddress()
    );

    if (!isRecapthaResponseVerified($response)) {
        $log->warning(
            'FAILED TO VERIFY RECAPCHA, ip[{remoteIpAddress}]',
            array(
                'remoteIpAddress' => getRemoteIpAddress()
            )
        );

        return getRecaptchaErrors($response);
    }

    return 'Success';
}

/**
 * @return string recaptcha enabled template or the recaptcha disabled template
 */
function displayRecaptcha()
{
    $captchaContentTemplate = new Sugar_Smarty();
    $log = new SuiteLogger();
    /** @var array $settings */
    $settings = getRecaptchaSettings();

    if (
        !isRecaptchaEnabled($settings)
        || empty(getRecaptchaSiteKey($settings))
        || empty(getRecaptchaPrivateKey($settings))
    ) {
        $log->info('Captcha Settings are disabled');
        return $captchaContentTemplate->fetch(__DIR__ . '/recaptcha_disabled.tpl');
    }

    $captchaContentTemplate->assign('SITE_KEY', getRecaptchaSiteKey($settings));
    $captchaContentTemplate->assign('SECRET', getRecaptchaPrivateKey($settings));

    return $captchaContentTemplate->fetch(__DIR__ . '/recaptcha_enabled.tpl');
}
