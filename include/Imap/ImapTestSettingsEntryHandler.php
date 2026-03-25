<?php


use SuiteCRM\LangText;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once __DIR__ . '/ImapHandlerFactory.php';
require_once __DIR__ . '/ImapHandlerException.php';

/**
 * ImapTestSettingsEntryHandler
 *
 * @author gyula
 */
class ImapTestSettingsEntryHandler
{

    /**
     *
     * @param array $sugarConfig
     * @param array $request
     * @return string entry point output as string
     * @throws InvalidArgumentException
     */
    public function handleEntryPointRequest($sugarConfig, $request)
    {
        if (!isset($sugarConfig['imap_test']) || !$sugarConfig['imap_test']) {
            throw new InvalidArgumentException('IMAP test config is required, use $sugar_config[\'imap_test\'].');
        }

        $var = 'imap_test_settings';
        $key = isset($request[$var]) && $request[$var] ? $request[$var] : null;
        if (null === $key) {
            throw new InvalidArgumentException("Invalid request variable at '$var'.");
        }

        $error = $this->doSaveTestSettingsKey($key, $var);

        if (!empty($error)) {
            $output = LangText::get('IMAP_HANDLER_ERROR', ['error' => $error, 'key' => $key]);
        } else {
            $output = LangText::get('IMAP_HANDLER_SUCCESS', ['key' => $key]);
        }

        return $output;
    }

    /**
     *
     * @param string $key
     * @param string $var
     * @return string|null return a translated error message if error occurred
     */
    protected function doSaveTestSettingsKey($key, $var)
    {
        if (!$key) {
            $error = LangText::get('IMAP_HANDLER_ERROR_INVALID_REQUEST', ['var' => $var]);
        } else {
            $imapHandlerFactory = new ImapHandlerFactory();
            try {
                $error = $this->getSaveTestSettingsKey($imapHandlerFactory, $key);
            } catch (ImapHandlerException $e) {
                $error = $this->handleImapHandlerException($e);
            }
        }
        return $error;
    }

    /**
     *
     * @param ImapHandlerFactory $imapHandlerFactory
     * @param string $key
     * @return string|null return a translated error message if error occurred
     */
    protected function getSaveTestSettingsKey(ImapHandlerFactory $imapHandlerFactory, $key)
    {
        $error = null;
        if (!$imapHandlerFactory->saveTestSettingsKey($key)) {
            $error = LangText::get('IMAP_HANDLER_ERROR_UNKNOWN_BY_KEY', ['key' => $key]);
        }
        return $error;
    }

    /**
     *
     * @param ImapHandlerException $e
     * @return string return a translated error message
     */
    protected function handleImapHandlerException(ImapHandlerException $e)
    {
        $code = $e->getCode();
        LoggerManager::getLogger()->error('ImapHandlerException detected: ' . $e->getMessage() . ' #' . $code);

        switch ($code) {
            case ImapHandlerException::ERR_TEST_SET_NOT_EXISTS:
                $error = LangText::get('IMAP_HANDLER_ERROR_NO_TEST_SET');
                break;

            case ImapHandlerException::ERR_KEY_NOT_FOUND:
                $error = LangText::get('IMAP_HANDLER_ERROR_NO_KEY');
                break;

            case ImapHandlerException::ERR_KEY_SAVE_ERROR:
                $error = LangText::get('IMAP_HANDLER_ERROR_KEY_SAVE');
                break;

            default:
                $error = LangText::get('IMAP_HANDLER_ERROR_UNKNOWN');
                break;
        }
        return $error;
    }
}
