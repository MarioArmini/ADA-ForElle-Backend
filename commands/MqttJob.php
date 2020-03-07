<?php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use Yii;
use app\models\HelpRequest;
use app\models\HelpRequestDetails;
use app\models\HelpRequestNotifications;
use app\models\Utils;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class MqttJob extends yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $dati = "";
    public $mqttQueue = "";

    public function execute($queue)
    {
        Utils::AddLog("MqttJob->execute: START " . $this->helpRequestId,'info',false,'log-job');

        try
        {
            $j = new \yii\helpers\Json();
            $connection = new AMQPStreamConnection(Yii::$app->params["MQTT"]["HOST"],
                                                    Yii::$app->params["MQTT"]["PORT"],
                                                    Yii::$app->params["MQTT"]["USER"],
                                                    Yii::$app->params["MQTT"]["PASSWORD"]);
            $channel = $connection->channel();

            $channel->queue_declare($this->mqttQueue, false, false, false, false);

            $msg = new AMQPMessage($j->encode($this->dati));
            $channel->basic_publish($msg,'', $this->mqttQueue);

            $channel->close();
            $connection->close();

            Utils::AddLog("MqttJob->execute: END ",'info',false,'log-job');
        }
        catch(\Exception $e)
        {
            Utils::AddLog("MqttJob->execute: ERROR " . $e->getMessage(),'info',false,'log-job');
            Utils::AddLogException($e);
        }
    }

}