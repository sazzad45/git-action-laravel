<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TriedToSendDuplicateTransferWithinTimeLimit extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $lastTransaction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($lastTransaction)
    {
        $this->lastTransaction = $lastTransaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->cc([
            'leton.miah@fast-pay.cash',
            'monowar@fastsolutioninc.com'
        ])
            ->view('email.duplicate-transfer')
            ->subject('Duplicate Transfer Report');
    }
}
