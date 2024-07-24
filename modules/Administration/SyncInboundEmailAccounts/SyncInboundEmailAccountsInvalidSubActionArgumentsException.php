<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SyncInboundEmailAccountsInvalidSubActionArgumentsException
 *
 * Handle the action calls with incorrect argument(s)
 *
 * It is a simple exception with an additional message which
 * contains the incorrectly called action-method name
 *
 */
class SyncInboundEmailAccountsInvalidSubActionArgumentsException extends Exception
{

    /**
     * steps for get the caller method in the backtrace
     *
     * @var int
     */
    protected $callerMethodDistance = 2;

    /**
     * SyncInboundEmailAccountsInvalidSubActionArgumentsException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            ($message ? $message . " - " : "") .
            "An action called with wrong parameters, incorrectly called action was: " .
            $this->getCallerMethod(),
            $code,
            $previous
        );
    }

    /**
     * Return the caller function/method name
     * call this function without argument
     * if you override this method may you have to change
     * the $this->callerMethodBackStep default value or
     * override it with step parameter
     *
     * @param int $step
     * @return mixed
     */
    protected function getCallerMethod($step = 2)
    {
        $trace = debug_backtrace();
        $function = $trace[$step ? $step : $this->callerMethodDistance]['function'];

        return $function;
    }
}
