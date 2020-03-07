<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\mongodb\file\Upload;
use yii\mongodb\Collection;
use yii\mongodb\file\Query;
use app\models\Utils;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TestController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionTestMongo($id = 1)
    {
        echo("<pre>");
        $pathFile  = Yii::$app->basePath . "/dati/1/audio-1-4.wav";
        $content = file_get_contents($pathFile);
        $document = Yii::$app->mongodb->getFileCollection()->createUpload([
                        "filename" => basename($pathFile),
                        "document" => ["helpRequestDetailId" => $id],
                        ])
                ->addContent($content)
                ->complete();
        var_dump($document);
        //$mng = new \MongoDB\Driver\Manager("mongodb://localhost:27017");
        //var_dump($mng);
        //$collection = Yii::$app->dbfiles->getCollection('files');
        //$collection->insert(['helpRequestDetailId' => 1, 'audioFile' => base64_encode($content)]);
    }

    public function actionTestMongoDownload($id = 1)
    {
        $pathFile  = Yii::$app->basePath . "/temp/temp.wav";
        Utils::delFile($pathFile);


        $query = new Query();
        $obj = $query->from('fs')->where(["helpRequestDetailId" => $id])->one();
        if($obj != null)
        {
            $document = Yii::$app->mongodb->getFileCollection()->createDownload($obj["_id"])->toFile($pathFile);
            return Yii::$app->response->sendFile($pathFile);
        }
        echo("<pre>");
        /*foreach ($rows as $row) {
            var_dump($row); // outputs: "object(\yii\mongodb\file\Download)"
            //echo $row['file']->toString(); // outputs file content
        }*/
        exit;
        //

        //$document = Yii::$app->mongodb->getFileCollection()->createDownload($idObject)->toFile($pathFile);

        //var_dump($obj);
        //$mng = new \MongoDB\Driver\Manager("mongodb://localhost:27017");
        //var_dump($mng);
        //$collection = Yii::$app->dbfiles->getCollection('files');
        //$collection->insert(['helpRequestDetailId' => 1, 'audioFile' => base64_encode($content)]);
    }
    public function actionTestMqtt()
    {
        $j = new \yii\helpers\Json();
        $connection = new AMQPStreamConnection(Yii::$app->params["MQTT"]["HOST"],
                                                Yii::$app->params["MQTT"]["PORT"],
                                                Yii::$app->params["MQTT"]["USER"],
                                                Yii::$app->params["MQTT"]["PASSWORD"]);
        $channel = $connection->channel();

        $dati = [
                "id" => 1,
                "lon" => 2,
                "lat" => 3
            ];

        $channel->queue_declare('hello', false, false, false, false);

        $msg = new AMQPMessage($j->encode($dati));
        $channel->basic_publish($msg,'', 'hello');

        echo " [x] Sent'\n";

        $channel->close();
        $connection->close();
    }

}
