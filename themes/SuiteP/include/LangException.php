<?php

namespace SuiteCRM;

use Exception;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LangException
 *
 * @author gyula
 * @todo should it implement or an interface enough for it?
 * (We can use any kind of exception if it's an interface but
 * can not guarantee the proper translation in each implementation)
 */
class LangException extends Exception implements LangExceptionInterface
{

    /**
     *
     * @var LangText
     */
    protected $langMessage;

    /**
     *
     * @param string $message
     * @param integer $code
     * @param Exception $previous (Throwable)
     * @param LangText $langMessage
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, LangText $langMessage = null)
    {
        parent::__construct($message, $code, $previous);
        $this->langMessage = $langMessage;
    }

    /**
     *
     * @return string|null
     */
    public function getLangMessage()
    {
        $message = null;

        if (null !== $this->langMessage) {
            $message = $this->langMessage->getText();
        }

        return $message;
    }
}
