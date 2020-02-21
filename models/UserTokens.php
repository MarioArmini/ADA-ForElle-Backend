<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "UserTokens".
 *
 * @property int $id
 * @property int $userId
 * @property string $key
 * @property string $dateInsert
 * @property string $dateExpired
 * @property string $dateUsed
 */
class UserTokens extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UserTokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId'], 'integer'],
            [['dateInsert', 'dateExpired', 'dateUsed'], 'safe'],
            [['key'], 'string', 'max' => 100],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'userId' => Yii::t('app', 'User ID'),
            'key' => Yii::t('app', 'Key'),
            'dateInsert' => Yii::t('app', 'Date Insert'),
            'dateExpired' => Yii::t('app', 'Date Expired'),
            'dateUsed' => Yii::t('app', 'Date Used'),
        ];
    }
    public static function createToken($email)
    {
        try
        {
            $account = User::findOne(["username" => $email]);
            if($account == null) return false;

            $t = new UserTokens();
            $t->userId = $account->id;
            $t->key = Utils::randomString(40);
            $t->dateInsert = date("Y-m-d H:i:s");
            $t->dateExpired =  date("Y-m-d H:i:s",strtotime("+3 days",time()));
            $t->dateUsed = 0;
            if($t->save()) return $t;
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }

        return false;
    }
    public static function getToken($key)
    {
        try
        {
            $t = UserTokens::findOne(["key" => $key]);
            if($t == null) return false;
            if(strtotime($t->dateExpired) < time()) return false;
            if(strtotime($t->dateUsed) > 0) return false;
            return $t->account_id;
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }

        return false;
    }
    public static function setUsedToken($key)
    {
        try
        {
            $t = UserTokens::findOne(["key" => $key]);
            if($t != null)
            {
                $t->dateUsed = date("Y-m-d H:i:s");
                $t->save(false);
            }
            return true;
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }

        return false;
    }
}
