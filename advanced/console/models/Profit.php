<?php

namespace console\models;

use Yii;

class Profit
{
    /**
     * Returns data of accounts from database
     * @param string $client_uid
     * @param string $dateTo
     * @return array
     */

    public static function getTradesFromDB($client_uid, $dateTo)
    {
        $sql = "SELECT SUM(trades.profit), SUM(trades.volume * trades.coeff_h * trades.coeff_cr) FROM accounts
                INNER JOIN trades ON accounts.login = trades.login
                WHERE accounts.client_uid IN (" . $client_uid . ") AND trades.close_time < '" . $dateTo . "'";
        $trades = Yii::$app->db->createCommand($sql)->queryAll();

        return $trades;
    }

    /**
     * Returns values from other functions
     * @param array $referrals
     * @param string $dateTo
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
        $trades = self::getTradesFromDB($clientsList, $dateTo);

        return $trades;
    }
}
