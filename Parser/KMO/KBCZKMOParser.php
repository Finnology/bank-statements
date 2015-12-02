<?php

namespace JakubZapletal\Component\BankStatement\Parser\KMO;

use JakubZapletal\Component\BankStatement\Parser\KMOParser;

/**
 * Class KBCZKMOParser
 * @package JakubZapletal\Component\BankStatement\Parser\KMO
 * @author Michal Bystricky <michal.bystricky@finnology.com>
 */
class KBCZKMOParser extends KMOParser
{
    const BANK_CODE = '0100';

    /**
     * @param string $line
     * @return \JakubZapletal\Component\BankStatement\Statement\Statement
     */
    protected function parseStatementLine($line)
    {
        $statement = parent::parseStatementLine($line);
        $statement->setAccountBankNumber(self::BANK_CODE);

        return $statement;
    }
}