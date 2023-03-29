<?php


namespace App\Domain\Transaction\Library;

use Illuminate\Support\Collection;

final class CreditableAccountList
{
    private  $items ;

    public function __construct()
    {
        $this->items = collect();
    }

    public function addItem(CreditableAccount $item): void
    {
        $this->items->push($item);
    }

    public function all()
    {
        return $this->items->all();
    }

}
