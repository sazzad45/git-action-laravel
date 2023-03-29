<?php


namespace App\Domain\Transaction\Library;


class DebitableAccount
{
    public int $user_id;
    public string $description;
    public float $amount;

    public function __construct(int $user_id, string $description, float $amount)
    {
        $this->user_id = $user_id;
        $this->description = $description;
        $this->amount = $amount;
    }
}
