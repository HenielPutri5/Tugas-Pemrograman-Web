<?php

declare(strict_types=1);

class Transaction
{
    public function __construct(
        private string $id,
        private string $type,
        private float $amount
    ) {}
}