<?php

namespace JakubZapletal\Component\BankStatement\Statement\Transaction;

use JakubZapletal\Component\BankStatement\Statement\Statement;

class Transaction implements TransactionInterface
{
    /**
     * @var Statement
     */
    protected $statement;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $counterAccountNumber;

    /**
     * @var string
     */
    protected $counterAccountBankNumber;

    /**
     * @var string
     */
    protected $receiptId;

    /**
     * @var float
     */
    protected $debit;

    /**
     * @var float
     */
    protected $credit;

    /**
     * @var int
     */
    protected $variableSymbol;

    /**
     * @var int
     */
    protected $constantSymbol;

    /**
     * @var int
     */
    protected $specificSymbol;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @inheritdoc
     */
    public function setStatement(Statement $statement)
    {
        $this->statement = $statement;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @inheritdoc
     */
    public function getCounterAccountNumber()
    {
        return $this->counterAccountNumber;
    }

    /**
     * @inheritdoc
     */
    public function getCounterAccountBankNumber()
    {
        return $this->counterAccountBankNumber;
    }

    /**
     * @inheritdoc
     */
    public function setCounterAccountNumber($counterAccountNumber)
    {
        $this->counterAccountNumber = $counterAccountNumber;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCounterAccountBankNumber($counterAccountBankNumber)
    {
        $this->counterAccountBankNumber = $counterAccountBankNumber;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConstantSymbol()
    {
        return $this->constantSymbol;
    }

    /**
     * @inheritdoc
     */
    public function setConstantSymbol($constantSymbol)
    {
        $this->constantSymbol = $constantSymbol;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @inheritdoc
     */
    public function setCredit($credit)
    {
        $this->credit = (float) $credit;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @inheritdoc
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * @inheritdoc
     */
    public function setDebit($debit)
    {
        $this->debit = (float) $debit;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @inheritdoc
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReceiptId()
    {
        return $this->receiptId;
    }

    /**
     * @inheritdoc
     */
    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSpecificSymbol()
    {
        return $this->specificSymbol;
    }

    /**
     * @inheritdoc
     */
    public function setSpecificSymbol($specificSymbol)
    {
        $this->specificSymbol = $specificSymbol;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVariableSymbol()
    {
        return $this->variableSymbol;
    }

    /**
     * @inheritdoc
     */
    public function setVariableSymbol($variableSymbol)
    {
        $this->variableSymbol = $variableSymbol;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
