<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * ImapHandlerFakeData
 *
 * For tests only, it deals fake return values for fake calls on an IMAP wrapper.
 *
 * @author gyula
 * @todo using common Call faker as base class
 */
class ImapHandlerFakeData
{
    const ERR_NO_MATCH_ARGS = 1;
    const ERR_CALL_NOT_FOUND = 2;
    const ERR_CALL_ALREDY_EXISTS = 3;
    const ERR_CALL_NOT_EXISTS = 4;
    const ERR_CALL_REMOVE = 5;
    const ERR_WRONG_TESTSET = 6;

    /**
     *
     * @var array
     */
    protected $calls = [];

    /**
     *
     * @param array|null $args
     * @return string
     */
    protected function encodeArgs($args = null)
    {
        return $encoded = md5(serialize($args));
    }

    /**
     *
     * @param string $name
     * @param string $argsEncoded
     * @return mixed
     */
    protected function getNextCallReturn($name, $argsEncoded)
    {
        if (!is_array($this->calls[$name][$argsEncoded])) {
            throw new Exception('Fake handler given an incorrect data. Returns should be an array at name: ' . $name, self::ERR_WRONG_TESTSET);
        }
        $ret = array_shift($this->calls[$name][$argsEncoded]);
        $this->calls[$name][$argsEncoded] = array_values($this->calls[$name][$argsEncoded]);
        if (empty($this->calls[$name][$argsEncoded])) {
            // using the last element forever..
            $this->calls[$name][$argsEncoded] = [$ret];
        }
        return $ret;
    }

    /**
     *
     * @param string $name
     * @param array|null $args
     * @return mixed
     * @throws Exception
     */
    protected function getCall($name, $args = null)
    {
        if (array_key_exists($name, $this->calls)) {
            $argsEncoded = $this->encodeArgs($args);
            if (array_key_exists($argsEncoded, $this->calls[$name])) {
                $ret = $this->getNextCallReturn($name, $argsEncoded);
                return $ret;
            } else {
                throw new Exception('Fake caller has not matched arguments for this call: ' . $name . "\nArguments was: " . print_r($args, true), self::ERR_NO_MATCH_ARGS);
            }
        } else {
            throw new Exception('Fake call does not exists for this function call: ' . $name . "\nwith specific arguments:\n" . print_r($args, true), self::ERR_CALL_NOT_FOUND);
        }
    }

    /**
     *
     * @param string $name
     * @param array|null $args
     * @return mixed
     * @throws Exception
     */
    public function call($name, $args = null)
    {
        $ret = $this->getCall($name, $args);
        if (is_object($ret) && ($ret instanceof Closure)) {
            $out = $ret();
        } else {
            $out = $ret;
        }
        return $out;
    }

    /**
     *
     * @param string $name
     * @param array|null $args
     * @param mixed|null $ret
     * @throws Exception
     */
    public function add($name, $args = null, $ret = null)
    {
        $argsEncoded = $this->encodeArgs($args);
        if (isset($this->calls[$name][$argsEncoded])) {
            LoggerManager::getLogger()->warn('Fake call already exists with given arguments: ' . $name . ', hint: remove first, use ' . __CLASS__ . '::remove(...)');
            $this->remove($name, $args);
        }
        $this->calls[$name][$argsEncoded] = $ret;
    }

    /**
     *
     * @param string $name
     * @param array|null $args
     * @throws Exception
     */
    public function remove($name, $args = null)
    {
        $argsEncoded = $this->encodeArgs($args);
        if (!isset($this->calls[$name][$argsEncoded])) {
            throw new Exception('Trying to remove a fake call but it is not exists: ' . $name, self::ERR_CALL_NOT_EXISTS);
        }
        unset($this->calls[$name][$argsEncoded]);
    }

    /**
     *
     */
    public function reset()
    {
        $this->calls = null;
    }

    /**
     *
     * @param string $name
     * @param array|null $args
     * @throws Exception
     */
    public function set($name, $args = null)
    {
        try {
            $this->remove($name, $args);
        } catch (Exception $e) {
            if ($e->getCode() != self::ERR_CALL_NOT_EXISTS) {
                throw new Exception('Call remove error', self::ERR_CALL_REMOVE, $e);
            }
        }
        $this->add($name, $args);
    }

    /**
     * Following example when ImapHandlerFake::open() called and imitate a success IMAP connection
     *
     * @param array $calls
     */
    public function retrieve($calls)
    {
        foreach ($calls as $name => $call) {
            if (empty($call)) {
                $call = [[]];
            }
            foreach ($call as $param) {
                $args = isset($param['args']) ? $param['args'] : null;
                $ret = isset($param['return']) ? $param['return'] : [null];
                $this->add($name, $args, $ret);
            }
        }
    }
}
