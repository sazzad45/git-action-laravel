<?php

namespace App\Domain\Transaction\Library\B2BTransfer;

use App\Domain\Transaction\Models\Transaction;

class Response
{
    public bool $status;
    public $message;
    public $data;
    public ?Transaction $transaction;

    public function __construct(bool $status, Transaction $transaction = null, $message = null, $data = null){
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->transaction = $transaction;
    }

    public function notificationMessage($type = "Sender")
    {
        if($this->data != null)
        {
            if($type == "Sender") {
                return $this->data['senderNotificationMessage'];
            }elseif($type == "Receiver"){
                return $this->data['receiverNotificationMessage'];
            }
        }
    }


}
