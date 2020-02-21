<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "HelpRequestNotifications".
 *
 * @property int $id
 * @property int $userId
 * @property int $helpRequestId
 * @property int $friendId
 * @property string $dateInsert
 * @property string $dateLastSeen
 *
 * @property Users $user
 * @property Users $friend
 * @property HelpRequest $helpRequest
 */
class HelpRequestNotifications extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'HelpRequestNotifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'helpRequestId', 'friendId'], 'integer'],
            [['dateInsert', 'dateLastSeen'], 'safe'],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
            [['friendId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['friendId' => 'id']],
            [['helpRequestId'], 'exist', 'skipOnError' => true, 'targetClass' => HelpRequest::className(), 'targetAttribute' => ['helpRequestId' => 'id']],
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
            'helpRequestId' => Yii::t('app', 'Help Request ID'),
            'friendId' => Yii::t('app', 'Friend ID'),
            'dateInsert' => Yii::t('app', 'Date Insert'),
            'dateLastSeen' => Yii::t('app', 'Date Last Seen'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFriend()
    {
        return $this->hasOne(Users::className(), ['id' => 'friendId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpRequest()
    {
        return $this->hasOne(HelpRequest::className(), ['id' => 'helpRequestId']);
    }
}
