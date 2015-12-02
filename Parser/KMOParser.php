<?php

namespace JakubZapletal\Component\BankStatement\Parser;

use JakubZapletal\Component\BankStatement\Statement\Statement;
use JakubZapletal\Component\BankStatement\Statement\StatementInterface;
use JakubZapletal\Component\BankStatement\Statement\Transaction\Transaction;

/**
 * Class KMOParser
 * @package Finnology\BankStatement\Parser
 * @author Michal Bystricky <michal.bystricky@finnology.com>
 */
class KMOParser extends Parser
{
    const LINE_TYPE_STATEMENT   = 'statement';
    const LINE_TYPE_TRANSACTION = 'transaction';

    const POSTING_CODE_DEBIT           = 0;
    const POSTING_CODE_CREDIT          = 1;
    const POSTING_CODE_DEBIT_REVERSAL  = 2;
    const POSTING_CODE_CREDIT_REVERSAL = 3;

    /** @var array|StatementInterface */
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
        $lastStatement = NULL;

        foreach ($fileObject as $line) {

            if ($fileObject->valid()) {
                switch ($this->getLineType($line)) {
                    case self::LINE_TYPE_STATEMENT:
                        $statement = $this->parseStatementLine($line);
                        $this->statements[ $statement->getSerialNumber() ] = $statement;

                        $lastStatement = $statement;
                        break;
                    case self::LINE_TYPE_TRANSACTION:
                        if (!$lastStatement instanceof Statement)
                            throw new \InvalidArgumentException('Cannot assign transaction to statement');
                        $transaction = $this->parseTransactionLine($line);
                        $transaction->setReceiptId(
                            $lastStatement->getSerialNumber() . '-' . $transaction->getReceiptId()
                        );

                        $this->statements[ $lastStatement->getSerialNumber() ]
                            ->addTransaction($transaction);

                        break;
                }
            }
        }
    }

    /**
     * @return array|Statement
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * @param string $line
     * @throws \Exception
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    protected function getLineType($line)
    {
        switch (substr($line, 0, 2)) {
            case '51':
                return self::LINE_TYPE_STATEMENT;
            case '52':
                return self::LINE_TYPE_TRANSACTION;
        }

        return null;
    }


    /**
     * 1  | Type zaznamu                | F | 2  | 51                   | ALIGN_LEFT
     * 2  | Cislo uctu                  | F | 16 | NNNNNNNNNNNNNNNN     | ALIGN_RIGHT
     * 3  | Datum uctovania             | F | 8  | YYYYMMDD             |
     * 4  | Cislo vypisu                | F | 3  | NNN                  | ALIGN_RIGHT
     * 5  | Datum minuleho vypisu       | F | 8  | YYYYMMDD             |
     * 6  | Pocet poloziek              | F | 5  | NNNNN                | ALIGN_RIGHT
     * 7  | Stary zostatok              | F | 15 | NNNNNNNNNNNNNVV      | ALIGN_RIGHT[N] ALIGN_RIGHT[V]
     * 8  | Znamienko stareho zostatku  | F | 1  | C                    | +/-
     * 9  | Novy zostatok               | F | 15 | NNNNNNNNNNNNNVV      | ALIGN_RIGHT[N] ALIGN_RIGHT[V]
     * 10 | Znamienko noveho zostatku   | F | 1  | C                    | +/-
     * 11 | Obraty debet                | F | 15 | NNNNNNNNNNNNNVV      | ALIGN_RIGHT[N] ALIGN_RIGHT[V]
     * 12 | Znamienko obratov debet     | F | 1  | C                    | +/-
     * 13 | Obraty kredit               | F | 15 | NNNNNNNNNNNNNVV      | ALIGN_RIGHT[N] ALIGN_RIGHT[V]
     * 14 | Znamienko obratov kredit    | F | 1  | C                    | +/-
     * 15 | Nazov uctu                  | F | 30 | C*30                 | ALIGN_LEFT
     * 16 | IBAN                        | F | 24 |                      |
     * 17 | Filler                      | F | 313| (space)
     * 18 | Koniec vety                 | F | 2  | CR LF
     *
     * @param string $line
     * @return Statement
     */
    protected function parseStatementLine($line)
    {
        $statement = new Statement();

        // Cislo uctu
        $accountNumber = ltrim(substr($line, 2, 16), '0');
        $statement->setAccountNumber($accountNumber);

        // Datum uctovania
        $date = substr($line, 18, 8);
        $dateCreated = \DateTime::createFromFormat('YmdHis', $date . '120000');
        $statement->setDateCreated($dateCreated);

        // Cislo vypisu
        $serialNumber = ltrim(substr($line, 26, 3), '0');
        $statement->setSerialNumber($serialNumber);

        // Datum minuleho vypisu
        $date = substr($line, 29, 8);
        $dateLastBalance = \DateTime::createFromFormat('YmdHis', $date . '120000');
        $statement->setDateLastBalance($dateLastBalance);

        // Stary zostatok
        $lastBalance = ltrim(substr($line, 42, 15), 0) / 100;
        $lastBalanceSign = substr($line, 57, 1);
        if ($lastBalanceSign === '-') {
            $lastBalance *= -1;
        }
        $statement->setLastBalance($lastBalance);

        // Novy zostatok
        $balance = ltrim(substr($line, 58, 15), 0) / 100;
        $balanceSign = substr($line, 73, 1);
        if ($balanceSign === '-') {
            $balance *= -1;
        }
        $statement->setBalance($balance);

        // Obraty debet
        $debitTurnover = ltrim(substr($line, 74, 15), 0) / 100;
        $debitTurnoverSign = substr($line, 89, 1);
        if ($debitTurnoverSign === '-') {
            $debitTurnover *= -1;
        }
        $statement->setDebitTurnover($debitTurnover);

        // Obraty kredit
        $creditTurnover = ltrim(substr($line, 90, 15), 0) / 100;
        $creditTurnoverSign = substr($line, 105, 1);
        if ($creditTurnoverSign === '-') {
            $creditTurnover *= -1;
        }
        $statement->setCreditTurnover($creditTurnover);

        // TODO Nazov uctu
        // TODO IBAN

        return $statement;
    }

    /**
     * @param $line
     * @return Transaction
     */
    protected function parseTransactionLine($line)
    {
        $transaction = new Transaction;

        # Receipt ID
        $receiptId = ltrim(substr($line, 2, 5), '0');
        $transaction->setReceiptId($receiptId);

        # Debit / Credit
        $amount = ltrim(substr($line, 50, 15), '0') / 100;
        $postingCode = substr($line, 46, 1);
        switch ($postingCode) {
            case self::POSTING_CODE_DEBIT:
                $transaction->setDebit($amount);
                break;
            case self::POSTING_CODE_CREDIT:
                $transaction->setCredit($amount);
                break;
            case self::POSTING_CODE_DEBIT_REVERSAL:
                $transaction->setDebit($amount * (-1));
                break;
            case self::POSTING_CODE_CREDIT_REVERSAL:
                $transaction->setCredit($amount * (-1));
                break;
        }

        // Cislo protiuctu
        $counterAccountNumber = ltrim(substr($line, 23, 16), '0');
        $counterAccountBank   = ltrim(substr($line, 39,  7), '0');
        $transaction
            ->setCounterAccountNumber($counterAccountNumber)
            ->setCounterAccountBankNumber($counterAccountBank);

        # Variable symbol
        $variableSymbol = ltrim(substr($line, 117, 10), '0');
        $transaction->setVariableSymbol($variableSymbol);

        # Constant symbol
        $constantSymbol = ltrim(substr($line, 137, 10), '0');
        $transaction->setConstantSymbol($constantSymbol);

        # Specific symbol
        $specificSymbol = ltrim(substr($line, 147, 10), '0');
        $transaction->setSpecificSymbol($specificSymbol);

        # Date created
        $date = substr($line, 167, 8);
        $dateCreated = \DateTime::createFromFormat('YmdHis', $date . '120000');
        $transaction->setDateCreated($dateCreated);

        # Note
        $note = rtrim(substr($line, 209, 30));
        $transaction->setNote($note);

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
