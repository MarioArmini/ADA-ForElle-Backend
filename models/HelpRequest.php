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
    const CATEGORY_HELP_REQUEST = "Help Request";
    const CATEGORY_END_REQUEST = "End Request";
    const CATEGORY_UPDATE_REQUEST = "Update Request";

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
    function beforeDelete() {
        if(!parent::beforeDelete()) return false;
        $pRs = $this->getHelpRequestDetails()->all();
        foreach($pRs as $r)
        {
            $r->delete();
        }
        $pRs = $this->getHelpRequestNotifications()->all();
        foreach($pRs as $r)
        {
            $r->delete();
        }
        return true;
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
    public function sendNotifica($deviceToken,$category) {
        try
        {
            Yii::$app->queue->push(new \app\commands\NotifyJob([
                                'deviceToken' => $deviceToken,
                                'category' => $category,
                                'helpRequestId' => $this->id,
                                'description' => $this->description,
                                ]));
            return true;
        }
        catch(\Exception $ex)
        {
            Utils::AddLog($ex);
        }
        return false;
    }

    function getUserJson() {
        $user = Users::findOne($this->userId);
        if($user != null) return $user->getJson();
        return null;
    }
    function sendNotificaFriends($category,$lastSeenValid = false) {
        try
        {
            $devices = [];

            $sql = "SELECT u.tokenDevice,r.dateLastSeen
                    FROM HelpRequestNotifications r
                    LEFT JOIN Users u ON u.id = r.friendId
                    WHERE r.helpRequestId = " . intval($this->id);

            $pRs = Yii::$app->db->createCommand($sql)->queryAll();
            foreach($pRs as $r)
            {
                $tokenDevice = trim($r["tokenDevice"]);
                $dateLastSeen = trim($r["dateLastSeen"]);
                if ($lastSeenValid)
                {
                    if(strlen($dateLastSeen) == 0) continue;
                }

                if(strlen(trim($tokenDevice)) > 0 && Users::checkValidToken($tokenDevice))
                {
                    $devices[] = trim($tokenDevice);
                }
            }
            if(count($devices) > 0)
            {
                $this->sendNotifica($devices,$category);
            }
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }

    }
}
