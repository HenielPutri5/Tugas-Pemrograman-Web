<?php

declare(strict_types=1);

class Transaction
{
    public function __construct(
        private string $id,
        private string $type,
        private float $amount
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}