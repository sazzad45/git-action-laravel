<?php


namespace App\Domain\Transaction\Models;


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
}
