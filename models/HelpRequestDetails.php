<?php

namespace app\models;

use Yii;
use yii\mongodb\file\Upload;
use yii\mongodb\Collection;
use yii\mongodb\file\Query;

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
            [['tokenKey'], 'string', 'max' => 255],
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
            'tokenKey' => Yii::t('app', 'Token'),
        ];
    }
    function beforeDelete() {
        if(!parent::beforeDelete()) return false;

        if(strlen($this->audioFileUrl) > 0)
        {
            $query = new Query();
            $obj = $query->from('fs')->where(["helpRequestDetailId" => intval($this->id)])->one();
            if($obj != null)
            {
                Yii::$app->mongodb->getFileCollection()->delete($obj["_id"]);
            }
            //Utils::delFile($this->getFullPathFile());
        }

        return true;
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
        //$path = $this->getFullPath() . $this->audioFileUrl;
        return $this->downloadAudio();
    }
    public function saveAudio($content,$type)
    {
        try
        {
            $buf = base64_decode($content);
            if(strlen($buf) > 0)
            {
                $filename = "audio-" . $this->helpRequestId . "-" . $this->id . "." . $type;
                /*
                file_put_contents($this->getFullPath() . $filename,$buf);
                Utils::Chmod($filename);
                */
                $document = Yii::$app->mongodb->getFileCollection()->createUpload([
                        "filename" => $filename,
                        "document" => ["helpRequestDetailId" => intval($this->id), "helpRequestId" => intval($this->helpRequestId)],
                        ])
                    ->addContent($buf)
                    ->complete();

                Yii::debug($document);

                if($document != null)
                {
                    $this->tokenKey = Yii::$app->security->generateRandomString(64);
                    $this->audioFileUrl = $filename;
                    return $this->save(false);
                }
            }
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }
        return false;
    }
    public function getJson()
    {
        return [
            'id' => intval($this->id),
            'helpRequestId' => intval($this->helpRequestId),
            'lat' => floatval($this->lat),
            'lon' => floatval($this->lon),
            'audioFileUrl' => trim($this->audioFileUrl),
            'dateInsert' => Utils::ToUTC($this->dateInsert),
            'tokenKey' => trim($this->tokenKey),
        ];
    }
    public function downloadAudio()
    {
        try
        {
            $pathFile = "/tmp/" . date("Ymd-His") . "-" . $this->audioFileUrl;
            $query = new Query();
            $obj = $query->from('fs')->where(["helpRequestDetailId" => $this->id])->one();
            if($obj != null)
            {
                Yii::$app->mongodb->getFileCollection()->createDownload($obj["_id"])->toFile($pathFile);
                return $pathFile;
            }
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }
        return false;
    }
}
