<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "HelpRequest".
 *
 * @property int $id
 * @property int $userId
 * @property int $serverity
 * @property double $lat
 * @property double $lon
 * @property string $description
 * @property string $dateInsert
 * @property string $dateModify
 * @property int $active
 *
 * @property Users $user
 * @property HelpRequestDetails[] $helpRequestDetails
 * @property HelpRequestNotifications[] $helpRequestNotifications
 */
class HelpRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'HelpRequest';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'serverity', 'active'], 'integer'],
            [['lat', 'lon'], 'number'],
            [['dateInsert', 'dateModify'], 'safe'],
            [['description'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
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
            'serverity' => Yii::t('app', 'Serverity'),
            'lat' => Yii::t('app', 'Lat'),
            'lon' => Yii::t('app', 'Lon'),
            'description' => Yii::t('app', 'Description'),
            'dateInsert' => Yii::t('app', 'Date Insert'),
            'dateModify' => Yii::t('app', 'Date Modify'),
            'active' => Yii::t('app', 'Active'),
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
    public function getHelpRequestDetails()
    {
        return $this->hasMany(HelpRequestDetails::className(), ['helpRequestId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpRequestNotifications()
    {
        return $this->hasMany(HelpRequestNotifications::className(), ['helpRequestId' => 'id']);
    }
    public function getJson()
    {
        $helpRequestDetails = $this->getHelpRequestDetails()->all();
        $helpRequestNotifications = $this->getHelpRequestNotifications()->all();

        return [
            "helpRequest" => $this,
            "helpRequestDetails" => $helpRequestDetails,
            "helpRequestNotifications" => $helpRequestNotifications,
            "user" => $this->getUserJson(),
        ];
    }
    public function sendNotifica($deviceToken) {
        $result = false;
        try {
            $messageBody = [
                "title" => "Help Request",
                "body" => $this->description,
            ];
            $params = [
                'sound' => 'alert',
                'badge' => 1,
                //'mutable-content' => 1,
                ];
            $customParams = [
                'category' => 'Help Request',
                'HelpRequest' => $this->id
            ];
            $apns = Yii::$app->apns;
            if(is_array($deviceToken))
                $apns->sendMulti($deviceToken, $messageBody,$customParams,$params);
            else
                $apns->send($deviceToken, $messageBody,$customParams,$params);

            $result = $apns->success;
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
            $result = false;
        }
        return $result;
    }
    function getUserJson() {
        $user = Users::findOne($this->userId);
        if($user != null) return $user->getJson();
        return null;
    }
}
