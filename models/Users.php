<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $authkey
 * @property string $accesstoken
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username'], 'string', 'max' => 50],
            [['password','name','number','tokenDevice'], 'string', 'max' => 255],
            [['authkey', 'accesstoken'], 'string', 'max' => 255],
            [['username','password'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'authkey' => 'Authkey',
            'accesstoken' => 'Accesstoken',
            'name' => 'Name',
            'number' => 'Number',
            'tokenDevice' => 'Token Device',
        ];
    }
    public function getJson()
    {
        return [
            "id" =>  $this->id,
            "name" => trim($this->name),
            "number" => trim($this->number),
            "tokenDevice" => trim($this->tokenDevice),
        ];
    }
}
