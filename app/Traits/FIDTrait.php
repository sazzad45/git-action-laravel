<?php

namespace App\Traits;

use App\Domain\Accounting\Models\UserAccount;


trait FIDTrait
{
    protected function generateFID(string $accountType)
    {
        $accountPrefix = $this->accountTypePrefix($accountType);
        $serialNumber = $this->serialNumber();
        $listingPrefix = $this->listingPrefix();
        return $accountPrefix . $listingPrefix . $serialNumber;
    }

    protected function accountTypePrefix($accountType)
    {
        switch($accountType)
        {
            case 'Admin':
                $accountPrefix = 'AD';
                break;

            case 'Agent':
                $accountPrefix = 'AG';
                break;

            case 'Merchant':
                $accountPrefix = 'MR';
                break;

            case 'Dealer':
                $accountPrefix = 'DL';
                break;

            case 'Distributor':
                $accountPrefix = 'DS';
                break;

            case 'Reseller':
                $accountPrefix = 'RS';
                break;

            case 'SalesRep':
                $accountPrefix = 'SR';
                break;

            default:
                $accountPrefix = 'PA';
                break;
        }

        return $accountPrefix;
    }

    protected function serialNumber()
    {
        config(['database.connections.mysql.strict' => false]);

        $lastSerialNumber = UserAccount::selectRaw("RIGHT(account_no, 6) as lastSerialNumber")
                                ->whereRaw("account_no IS NOT NULL")
                               // ->whereStatus(true)
                                ->orderBy('lastSerialNumber', 'DESC')
                                ->first();

        if($lastSerialNumber != null){
            $lastSerialNumber = (int) $lastSerialNumber->lastSerialNumber;
        }else{
            $lastSerialNumber = 0;
        }

        if($lastSerialNumber >= 999999) {
            $serialNumber = 1;
            \DB::statement("UPDATE account_listings SET status = 1 WHERE status = 0 ORDER BY alphabet ASC LIMIT 1");
        } else {
            $serialNumber = $lastSerialNumber + 1;
        }

        return sprintf("%06d", $serialNumber);
    }

    protected function listingPrefix()
    {
        return \DB::table('account_listings')
                    ->select('alphabet')
                    ->whereStatus(false)
                    ->orderBy('alphabet', 'ASC')
                    ->first()
                    ->alphabet;
    }
}
