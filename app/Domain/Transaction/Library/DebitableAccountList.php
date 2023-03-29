<?php


namespace App\Domain\Transaction\Library;

use Illuminate\Support\Collection;

final class DebitableAccountList
{
    private  $items ;

    public function __construct()
    {
        $this->items = collect();
    }

    public function addItem(DebitableAccount $item): void
    {
        $this->items->push($item);
    }

    public function all()
    {
        return $this->items->all();
    }
}
