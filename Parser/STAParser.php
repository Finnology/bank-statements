<?php

namespace JakubZapletal\Component\BankStatement\Parser;

use JakubZapletal\Component\BankStatement\Statement\Statement;
use JakubZapletal\Component\BankStatement\Statement\Transaction\Transaction;

/**
 * Class STAParser
 * @package JakubZapletal\Component\BankStatement\Parser
 * @author Michal Bystricky <michal.bystricky@finnology.com>
 */
class STAParser extends Parser
{
    const LINE_TYPE_STATEMENT   = 'statement';
    const LINE_TYPE_TRANSACTION = 'transaction';

    const POSTING_CODE_DEBIT    = 'D';
    const POSTING_CODE_CREDIT   = 'C';

    /** @var array|Statement */
    protected $statements = [];

    /**
     * @param string $filePath
     *
     * @return Statement
     * @throws \RuntimeException
     */
    public function parseFile($filePath)
    {
        $fileObject = new \SplFileObject($filePath);
        return $this->parseFileObject($fileObject);
    }

    /**
     * @param \SplFileObject $fileObject
     *
     * @return Statement
     */
    protected function parseFileObject(\SplFileObject $fileObject)
    {
        $statementData = NULL;

        foreach ($fileObject as $line)
        {
            // Detect start of statement
            if (substr($line, 0, 3) == "{1:")
            {
                $statementData = NULL;
            }

            $statementData .= $line;

            // If end of statement data, process it
            if (substr($line, 0, 2) == "-}")
            {
                $statement = $this->parseStatementData($statementData);
                $this->statements[ $statement->getSerialNumber() ] = $statement;
            }
        }
    }

    /**
     * @param $statementData
     * @return Statement
     */
    protected function parseStatementData($statementData)
    {
        $statement = new Statement;

        $explodedData = explode("\r\n", $statementData);
        $transactionStarted = FALSE;
        $transactionData = NULL;

        foreach ($explodedData as $line)
        {
            $tmp = explode(":", trim($line));

            if (substr($line, 0, 3) == "{1:")
            {

            }

            // Reference
            if (substr($line, 0, 4) == ":20:")
            {
            }

            // Account number
            if (substr($line, 0, 4) == ":25:")
            {
                $account = explode("/", $tmp[2]);
                $statement->setAccountNumber($account[1]);
                $statement->setAccountBankNumber($account[0]);
            }

            // Serial number
            if (substr($line, 0, 5) == ":28C:")
            {
                $statement->setSerialNumber($tmp[2]);
            }

            if (substr($line, 0, 5) == ":60M:" || substr($line, 0, 5) == ":60F:")
            {
                $balance = str_replace(',','.',substr($tmp[2], 10)) * 1;
                $balanceSign = substr($tmp[2], 0, 1);
                if ($balanceSign === 'D') {
                    $balance *= -1;
                }
                $statement->setLastBalance($balance);

                $date = substr($tmp[2], 1, 6);
                $dateLastBalance = \DateTime::createFromFormat('ymdHis', $date . '120000');
                $statement->setDateLastBalance($dateLastBalance);
            }


            if (substr($line, 0, 5) == ":62M:" || substr($line, 0, 5) == ":62F:")
            {
                $balance = str_replace(',','.',substr($tmp[2], 10)) * 1;
                $balanceSign = substr($tmp[2], 0, 1);
                if ($balanceSign === 'D') {
                    $balance *= -1;
                }
                $statement->setBalance($balance);

                $date = substr($tmp[2], 1, 6);
                $dateBalance = \DateTime::createFromFormat('ymdHis', $date . '120000');
                $statement->setDateCreated($dateBalance);

                // Finished transaction
                $transaction = $this->parseTransactionLine($transactionData);
                if ($transaction !== FALSE)
                {
                    $statement->addTransaction($transaction);
                }

                $transactionStarted = FALSE;
            }

            if (substr($line, 0, 4) == ":61:")
            {
                if ($transactionStarted == FALSE)
                {
                    $transactionData .= "\n" .$line;
                    $transactionStarted = TRUE;
                } else {
                    // Finished transaction
                    $transaction = $this->parseTransactionLine($transactionData);
                    if ($transaction !== FALSE)
                    {
                        $statement->addTransaction($transaction);
                    }

                    $transactionData = $line;
                }
            } elseif ($transactionStarted == TRUE) {
                $transactionData .= "\n" . $line;
            }
        }

        return $statement;
    }



    /**
     * @return array|Statement
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * @param $line
     * @return Transaction
     */
    protected function parseTransactionLine($line)
    {
        $lineData = explode("\n", trim($line));

        $line = trim($line);

        $transaction = new Transaction;

        # Date created
        $date = substr($lineData[0], 4, 6);
        $dateCreated = \DateTime::createFromFormat('ymdHis', $date . '120000');
        $transaction->setDateCreated($dateCreated);

        # Je zahranicna platba ?
        preg_match('/([A-Z]+)\s([0-9]+,[0-9]+)+\sRATE/', $line, $matches);
        if (isset($matches[1]) && isset($matches[2]))
        {
            $transaction->setCurrency($matches[1]);
            $amount = str_replace(',','.', $matches[2]);
        } else {
            // Tuzemska
            $transaction->setCurrency('CZK');
            $amountValue = str_replace(',', '.', substr($lineData[0], 15, 15));
            preg_match('/([0-9.]+)/', $amountValue, $matches);
            $amount = $matches[1];
        }

        $postingCode = substr($lineData[0], 14, 1);

        switch ($postingCode) {
            case self::POSTING_CODE_DEBIT:
                $transaction->setDebit($amount);
                break;
            case self::POSTING_CODE_CREDIT:
                $transaction->setCredit($amount);
                break;
        }

        if ($this->mode == Parser::MODE_CREDIT_ONLY && $postingCode != self::POSTING_CODE_CREDIT)
            return FALSE;

        if ($this->mode == Parser::MODE_DEBIT_ONLY && $postingCode != self::POSTING_CODE_DEBIT)
            return FALSE;

        preg_match('/VS:\s([0-9]+)/', $line, $matches);

        # Variable symbol
        if (isset($matches[1])) {
            $transaction->setVariableSymbol($matches[1]);
        }

        preg_match('/KS:\s([0-9]+)/', $line, $matches);

        # Constant symbol
        if (isset($matches[1])) {
            $transaction->setConstantSymbol($matches[1]);
        }

        preg_match('/SS:\s([0-9]+)/', $line, $matches);

        # Specific symbol
        if (isset($matches[1])) {
            $transaction->setSpecificSymbol($matches[1]);
        }

        preg_match('/\sZ\s([0-9\/-]+)/', $line, $matches);

        # Cislo protiuctu
        if (isset($matches[1]))
        {
            $tmp = explode('/', $matches[1]);
            $transaction->setCounterAccountNumber($tmp[0]);
            $transaction->setCounterAccountBankNumber($tmp[1]);
        }

        # Receipt ID
        preg_match('/\/\/([0-9]+)/', $line, $matches);
        $transaction->setReceiptId($matches[1]);

        return $transaction;
    }

    /**
     * @param string $content
     *
     * @return Statement
     * @throws \InvalidArgumentException
     */
    public function parseContent($content)
    {
        if (is_string($content) === false) {
            throw new \InvalidArgumentException('Argument "$content" isn\'t a string type');
        }

        $fileObject = new \SplTempFileObject();
        $fileObject->fwrite($content);

        return $this->parseFileObject($fileObject);
    }
}
