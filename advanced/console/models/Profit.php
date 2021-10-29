<?php

namespace console\models;

use Yii;

class Profit
{
    /**
     * Returns logins of user from database
     * @param string $client_uid
     * @return array
     */
    public static function getAccountsFromDB($client_uid)
    {
        $sql = "SELECT login FROM accounts WHERE client_uid IN (" . $client_uid . ")";
        $accounts = Yii::$app->db->createCommand($sql)->queryAll();

        return $accounts;
    }

    /**
     * Returns data of accounts from database
     * @param string $client_uid
     * @return array
     */
    public static function getTradesFromDB($logins, $dateTo)
    {
        $sql = "SELECT SUM(profit), SUM(volume * coeff_h * coeff_cr) FROM trades WHERE login IN (" . $logins . ") AND close_time < '" . $dateTo . "'";
        $trades = Yii::$app->db->createCommand($sql)->queryAll();

        return $trades;
    }

    /**
     * Returns values from other functions
     * @param array $referrals
     * @param mixed $dateTo
     * @return array
     */
    public static function mainFunction($referrals, $dateTo)
    {
        $clients = []; //массив client_uid всех рефералов

        foreach ($referrals as $referral){
            $client_uid = $referral['client_uid'];
            $clients[] = $client_uid;
        }

        $clientsList = implode(', ', $clients); //строка client_uid всех рефералов
        $accounts = self::getAccountsFromDB($clientsList);
        $logins = []; //массив счетов пользоватлей

        foreach ($accounts as $account){
            $logins[] = $account['login'];
        }

        $loginsList = implode(', ', $logins); //строка счетов пользователей
        $trades = self::getTradesFromDB($loginsList, $dateTo);

        return $trades;
    }
}
