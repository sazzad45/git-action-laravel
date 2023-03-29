<?php


namespace App\Constant;


class TransactionTypeText
{
    const ONLINE_SHOPPING = "Online-Shopping";
    const PHYSICAL_SHOP = "Physical-Shop";
    const P2P_TRANSFER = "P2P-Transfer";
    const CASH_IN = "Cash-In";
    const CASH_OUT = "Cash-Out";
    const AIRTIME = "Airtime";
    const CATALOGUE_SHOPPING = "Catalogue-Shopping";
    const GAS_BILL = "Gas-Bill";
    const WATER_BILL = "Water-Bill";
    const ELECTRICITY_BILL = "Electricity-Bill";
    const OTHER = "Other";

    const DEPOSIT_CASH_CARD = "Deposit/Cash Card";
    const DEPOSIT_E_VOUCHER = "Deposit/e-Voucher";
    const MONEY_TRANSFER = "Money Transfer";
    const DATA_BUNDLE = "Data Bundle";

    const COMMISSION = "Commission";
    const REFUND = "Refund";
    const CASH_BACK = "Cash-Back";
    const ONLINE_CARD = "Online-Card";

    const REWARD = "Reward";
    const REMITTANCE = "Remittance";
    const SALARY = "Salary";

    public function getText($sl_no)
    {
        switch($sl_no){
            case 1; return self::ONLINE_SHOPPING; break;
            case 2: return self::PHYSICAL_SHOP; break;
            case 3: return self::P2P_TRANSFER; break;
            case 4: return self::CASH_IN; break;
            case 5: return self::CASH_OUT; break;

            case 6: return self::AIRTIME; break;
            case 7: return self::CATALOGUE_SHOPPING; break;
            case 8: return self::GAS_BILL; break;
            case 9: return self::WATER_BILL; break;
            case 10: return self::ELECTRICITY_BILL; break;

            case 11: return self::OTHER; break;
            case 12: return self::DEPOSIT_CASH_CARD; break;
            case 13: return self::DEPOSIT_E_VOUCHER; break;
            case 14: return self::MONEY_TRANSFER; break;
            case 15: return self::DATA_BUNDLE; break;

            case 16: return self::COMMISSION; break;
            case 17: return self::REFUND; break;
            case 18: return self::CASH_BACK; break;
            case 19: return self::ONLINE_CARD; break;
            case 20: return self::REWARD; break;

            case 21: return self::REMITTANCE; break;
            case 22: return self::SALARY; break;

        }
    }
}

