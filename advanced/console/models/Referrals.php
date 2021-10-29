<?php

namespace console\models;

use Yii;

class Referrals
{
    /**
     * Returns id of user from database
     * @param string $client_uid
     * @return array
     */
    public static function getUserFromDB($client_uid)
    {
        $sql = "SELECT client_uid FROM users WHERE client_uid = " . $client_uid;
        $user = Yii::$app->db->createCommand($sql)->queryOne();

        return $user;
    }

    /**
     * Returns id of users from database
     * @param string $client_uid
     * @return array
     */
    public static function getReferralsFromDB($client_uid)
    {
        $sql = "SELECT client_uid, partner_id FROM users WHERE partner_id IN (" . $client_uid . ")";
        $users = Yii::$app->db->createCommand($sql)->queryAll();

        return $users;
    }

    /**
     * Returns all referrals of user
     * @param string $user
     * @return mixed
     */
    public static function getReferrals($user)
    {
        $currentLevelReferrals = self::getReferralsFromDB($user);                                                       //выполняем запрос один раз, чтобы получить первый массив рефералов. если он пустой, возвращаем 0, иначе, начинаем подсчет

        if ($currentLevelReferrals !== []) {
            $allReferrals = [];
            $allReferralsCount = 0;
            $totalLevelsReferrals = 0;
            $firstLevelReferrals = count($currentLevelReferrals);

            while ($currentLevelReferrals !== []) {                                                                     //выполняем, пока не пришел пустой массив рефералов
                $totalLevelsReferrals++;
                $nextLevelReferrals = [];
                $allReferralsCount = $allReferralsCount + count($currentLevelReferrals);                                //считаем общее количество рефералов

                foreach ($currentLevelReferrals as $referral) {                                                         //проходим по каждому рефералу из текущего уровня
                    $allReferrals[] =  $referral;                                                                       //наполняем массив всех рефералов
                    $nextLevelReferrals[] = $referral['client_uid'];                                                    //наполлняем массив для поиска следующего уровня рефералов
                }

                $clients_uid = implode(', ', $nextLevelReferrals);                                              //преобразуем массив в строку, для sql запроса
                $currentLevelReferrals = self::getReferralsFromDB($clients_uid);
            }

            return array(
                'referrals' => $allReferrals,
                'allreferralscount' => $allReferralsCount,
                'firstlevelreferrals' => $firstLevelReferrals,
                'totallevelsreferrals' => $totalLevelsReferrals,
            );
        } else {
            echo 'User has no referrals';
            die;
        }
    }

    /**
     * Returns values from other functions
     * @param string $client_uid
     * @return mixed
     */
    public static function mainFunction($client_uid)
    {
        $user = self::getUserFromDB($client_uid);

        if ($user) {
            $referrals = self::getReferrals($user['client_uid']);
            $tree[$client_uid] = self::buildTree($referrals['referrals'], $client_uid);

            return array ('referrals' => $referrals, 'tree' => $tree);
        } else {
            echo 'User not found';
            die;
        }
    }

    /**
     * Функция была взята с ресурса stackoverflow
     * https://stackoverflow.com/questions/8840319/build-a-tree-from-a-flat-array-in-php
     * Не хватает мозгов, чтобы докрутить ее до "красивого" вывода массива
     * без ключей client_uid, partner_id и children.
     *
     * Returns referral tree.
     * @param array $referrals
     * @param string $user
     * @return array
     */
    public static function buildTree($referrals, $user)
    {
        $branch = array();

        foreach ($referrals as $referral) {
            if ($referral['partner_id'] == $user) {
                $children = self::buildTree($referrals, $referral['client_uid']);
                if ($children) {
                    $referral['children'] = $children;
                }
                $branch[$referral['client_uid']] = $referral;
                unset($referral);
            }
        }

        return $branch;
    }
}
