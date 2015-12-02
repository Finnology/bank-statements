<?php

namespace JakubZapletal\Component\BankStatement\Statement;

use JakubZapletal\Component\BankStatement\Statement\Transaction\TransactionInterface;

class Statement implements StatementInterface, \Countable, \Iterator
{
    /**
     * @var string
     */
    protected $accountNumber;

    /**
     * @var string
     */
    protected $accountBankNumber;

    /**
     * @var float
     */
    protected $balance;

    /**
     * @var float
     */
    protected $debitTurnover;

    /**
     * @var float
     */
    protected $creditTurnover;

    /**
     * @var string
     */
    protected $serialNumber;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     */
    protected $dateLastBalance;

    /**
     * @var float
     */
    protected $lastBalance;

    /**
     * @var TransactionInterface[]
     */
    protected $transactions = array();

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param $balance
     *
     * @return $this
     */
    public function setBalance($balance)
    {
        $this->balance = (float) $balance;

        return $this;
    }

    /**
     * @return float
     */
    public function getCreditTurnover()
    {
        return $this->creditTurnover;
    }

    /**
     * @param $creditTurnover
     *
     * @return $this
     */
    public function setCreditTurnover($creditTurnover)
    {
        $this->creditTurnover = (float) $creditTurnover;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @param $serialNumber
     *
     * @return $this
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * @return float
     */
    public function getDebitTurnover()
    {
        return $this->debitTurnover;
    }

    /**
     * @param $debitTurnover
     *
     * @return $this
     */
    public function setDebitTurnover($debitTurnover)
    {
        $this->debitTurnover = (float) $debitTurnover;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @return string
     */
    public function getAccountBankNumber()
    {
        return $this->accountBankNumber;
    }

    /**
     * @param $accountNumber
     *
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAccountBankNumber($accountBankNumber)
    {
        $this->accountBankNumber = $accountBankNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastBalance()
    {
        return $this->dateLastBalance;
    }

    /**
     * @param \DateTime $dateLastBalance
     *
     * @return $this
     */
    public function setDateLastBalance(\DateTime $dateLastBalance)
    {
        $this->dateLastBalance = $dateLastBalance;

        return $this;
    }

    /**
     * @return float
     */
    public function getLastBalance()
    {
        return $this->lastBalance;
    }

    /**
     * @param float $lastBalance
     *
     * @return $this
     */
    public function setLastBalance($lastBalance)
    {
        $this->lastBalance = (float) $lastBalance;

        return $this;
    }

    /**
     * @return TransactionInterface[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return $this
     */
    public function addTransaction(TransactionInterface $transaction)
    {
        $added = false;

        foreach ($this->transactions as $addedTransaction) {
            if ($transaction === $addedTransaction) {
                $added = true;
            }
        }

        if ($added !== true) {
            $this->transactions[] = $transaction->setStatement($this);
        }

        return $this;
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return $this
     */
    public function removeTransaction(TransactionInterface $transaction)
    {
        foreach ($this->transactions as $key => $addedTransaction) {
            if ($transaction === $addedTransaction) {
                unset($this->transactions[$key]);
            }
        }

        return $this;
    }

    /**
     * @see \Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->transactions);
    }

    /**
     * @see \Iterator::current()
     *
     * @return TransactionInterface
     */
    public function current()
    {
        return current($this->transactions);
    }

    /**
     * @see \Iterator::key()
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->transactions);
    }

    /**
     * @see \Iterator::next()
     *
     * @return TransactionInterface
     */
    public function next()
    {
        return next($this->transactions);
    }

    /**
     * @see \Iterator::rewind()
     *
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->transactions);
    }

    /**
     * @see \Iterator::valid()
     *
     * @return bool
     */
    public function valid()
    {
        return key($this->transactions) !== null;
    }
}
