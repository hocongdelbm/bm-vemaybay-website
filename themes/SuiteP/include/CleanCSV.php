<?php
namespace SuiteCRM;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 * Class CleanCSV
 * @package SuiteCRM
 */
class CleanCSV
{
    /**
     * @var string
     */
    protected $escapeChar;

    /**
     * @var array|string[]
     */
    protected $startingChars;

    /**
     * CleanCSV constructor.
     * @param string $escapeChar character to escape each CSV field.
     * @param array|string[] $startingChars starting characters to be escaped.
     */
    public function __construct($escapeChar = "'", array $startingChars = ['=', '-', '+', '@'])
    {
        $this->escapeChar = $escapeChar;
        $this->startingChars = $startingChars;
    }

    /**
     * @return array|string[]
     */
    public function getStartingChars()
    {
        return $this->startingChars;
    }

    /**
     * @return string
     */
    public function getEscapeChar()
    {
        return $this->escapeChar;
    }

    /**
     * @param string $cell
     * @return string
     */
    public function escapeField($cell)
    {
        if (!is_string($cell) || empty($cell)) {
            return $cell;
        }

        if (in_array($cell[0], $this->startingChars, true)) {
            return $this->escapeChar . $cell;
        }

        return $cell;
    }
}
