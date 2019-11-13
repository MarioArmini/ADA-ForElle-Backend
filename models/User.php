<?php

namespace app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    public static function copyObject($u)
    {
        $tmp = new User();
        $tmp->id = $u->id;
        $tmp->username = $u->username;
        $tmp->password = $u->password;
        $tmp->authKey = $u->authkey;
        $tmp->accessToken = $u->accesstoken;
        return $tmp;
    }
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        $u = Users::findOne($id);
        if($u !=  null)
        {
            return self::copyObject($u);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $u = Users::findOne(["accesstoken" => $token]);
        if($u !=  null)
        {
            return self::copyObject($u);
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $u = Users::findOne(["username" => $username]);
        if($u !=  null)
        {
            return self::copyObject($u);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
