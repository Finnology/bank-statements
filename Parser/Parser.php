<?php

namespace JakubZapletal\Component\BankStatement\Parser;

use JakubZapletal\Component\BankStatement\Statement\Statement;
use JakubZapletal\Component\BankStatement\Statement\Transaction\Transaction;

abstract class Parser implements ParserInterface
{
    const MODE_ALL         = 0;
    const MODE_CREDIT_ONLY = 1;
    const MODE_DEBIT_ONLY  = 2;

    /**
     * @var Statement
     */
    protected $statement;

    /** @var int */
    protected $mode = self::MODE_ALL;

    /**
     * @param string $filePath
     *
     * @return Statement
     * @throw \Exception
     */
    abstract public function parseFile($filePath);

    /**
     * @param string $content
     *
     * @return Statement
     * @throw \Exception
     */
    abstract public function parseContent($content);

    /**
     * @return Statement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get a new instance of statement class
     *
     * @return Statement
     */
    protected function getStatementClass()
    {
        return new Statement();
    }

    /**
     * Get a new instance of transaction class
     *
     * @return Transaction
     */
    protected function getTransactionClass()
    {
        return new Transaction();
    }
}
