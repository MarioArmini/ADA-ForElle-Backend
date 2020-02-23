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
        ];
    }
    public function sendNotifica($deviceToken) {
        $wroteSuccessfully = false;
        try
        {
            $apnsServer = Yii::$app->params["NOTIFICHE"]["URL"];
            $privateKeyPassword = Yii::$app->params["NOTIFICHE"]["PASSWORD"];
            $pushCertAndKeyPemFile = Yii::$app->basePath . "/" . Yii::$app->params["NOTIFICHE"]["CERIFICATO"];
            $stream = stream_context_create();
            stream_context_set_option($stream, 'ssl', 'passphrase', $privateKeyPassword);
            stream_context_set_option($stream, 'ssl', 'local_cert', $pushCertAndKeyPemFile);

            $connectionTimeout = 20;
            $connectionType = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
            $connection = stream_socket_client($apnsServer, $errorNumber, $errorString, $connectionTimeout, $connectionType, $stream);
            if (!$connection)
            {
                Utils::AddLog("Failed to connect to the APNS server. Error no = $errorNumber");
                return false;
            }
            else
            {
                Utils::AddLog("Successfully connected to the APNS. Processing...");
            }


            $messageBody['aps'] = [
                'alert' => [
                    "body" => $this->description,
                    "title" => "",
                    "subtitle" => "",
                ],
                'sound' => 'alert',
                'badge' => 2,
                'category' => 'Help Request',
                'thread-id' => $this->id
            ];
            $payload = json_encode($messageBody);
            $notification = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
            $wroteSuccessfully = fwrite($connection, $notification, strlen($notification));
            if (!$wroteSuccessfully){
                Utils::AddLog("Could not send the message");
            }
            else {
                Utils::AddLog("Successfully sent the message");
            }
            fclose($connection);
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }
        return $wroteSuccessfully;
    }
}
