<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "HelpRequestDetails".
 *
 * @property int $id
 * @property int $helpRequestId
 * @property double $lat
 * @property double $lon
 * @property string $audioFileUrl
 * @property string $dateInsert
 *
 * @property HelpRequest $helpRequest
 */
class HelpRequestDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'HelpRequestDetails';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['helpRequestId'], 'integer'],
            [['lat', 'lon'], 'number'],
            [['dateInsert'], 'safe'],
            [['audioFileUrl'], 'string', 'max' => 1024],
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
            'helpRequestId' => Yii::t('app', 'Help Request ID'),
            'lat' => Yii::t('app', 'Lat'),
            'lon' => Yii::t('app', 'Lon'),
            'audioFileUrl' => Yii::t('app', 'Audio File Url'),
            'dateInsert' => Yii::t('app', 'Date Insert'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelpRequest()
    {
        return $this->hasOne(HelpRequest::className(), ['id' => 'helpRequestId']);
    }
    public function getFullPath()
    {
        $path = Yii::$app->basePath . "/dati/" . $this->helpRequestId;
        Utils::MkDir($path);
        return $path . "/";
    }
    public function getFullPathFile()
    {
        $path = $this->getFullPath() . $this->audioFileUrl;
        return $path;
    }
    public function saveAudio($content,$type)
    {
        try
        {
            $buf = base64_decode($content);
            if(strlen($buf) > 0)
            {
                $filename = "audio-" . $this->helpRequestId . "-" . $this->id . "." . $type;
                file_put_contents($this->getFullPath() . $filename,$buf);
                Utils::Chmod($filename);

                $this->audioFileUrl = Yii::$app->params["SITE_URL"] . "api/help-request/download?key=" . Utils::CryptPassword($this->id);
                return $this->save(false);
            }
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }
        return false;
    }
}
