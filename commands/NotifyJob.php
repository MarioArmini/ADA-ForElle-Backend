<?php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use Yii;
use app\models\HelpRequest;
use app\models\HelpRequestDetails;
use app\models\HelpRequestNotifications;
use app\models\Utils;

class NotifyJob extends yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $deviceToken = [];
    public $category = "";
    public $helpRequestId = 0;
    public $description = "";

    public function execute($queue)
    {
        Utils::AddLog("NotifyJob->execute: START " . $this->helpRequestId,'info',false,'log-job');

        try
        {
            $this->sendNotificaBatch();

            Utils::AddLog("NotifyJob->execute: END " . $this->helpRequestId,'info',false,'log-job');
        }
        catch(\Exception $e)
        {
            Utils::AddLog("NotifyJob->execute: ERROR " . $e->getMessage(),'info',false,'log-job');
            Utils::AddLogException($e);
        }
    }
    public function sendNotificaBatch() {
        $result = false;
        try {
            $sound = 'alert';
            $badge = 1;
            switch($this->category)
            {
                case HelpRequest::CATEGORY_HELP_REQUEST:
                    $sound = 'alert';
                    $badge = 1;
                    break;
                case HelpRequest::CATEGORY_END_REQUEST:
                    $sound = 'alert';
                    $badge = 0;
                    break;
                case HelpRequest::CATEGORY_UPDATE_REQUEST:
                    $sound = 'alert';
                    $badge = 0;
                    break;
            }
            if(is_array($this->deviceToken))
                Utils::AddLog("sendNotifica ->" . implode(" - ",$this->deviceToken));
            else
                Utils::AddLog("sendNotifica ->" . $this->deviceToken);
            $messageBody = [
                "title" => "Help Request",
                "body" => $this->description,
            ];
            $params = [
                'sound' => $sound,
                'badge' => $badge,
                //'mutable-content' => 1,
                ];
            $customParams = [
                'category' => $this->category,
                'HelpRequest' => $this->helpRequestId
            ];
            $apns = Yii::$app->apns;
            if(is_array($this->deviceToken))
                $apns->sendMulti($this->deviceToken, $messageBody,$customParams,$params);
            else
                $apns->send($this->deviceToken, $messageBody,$customParams,$params);

            $result = $apns->success;
            if($result)
            {
                Utils::AddLog("sendNotifica OK");
            }
            else
            {
                Utils::AddLog("sendNotifica KO");
                Utils::AddLog($apns->errors);
            }
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
            $result = false;
        }
        return $result;
    }
}