<?php

namespace app\controllers\api;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\VerbFilter;
use app\models\Cacl;
use app\models\User;
use app\models\Users;
use app\models\HelpRequest;
use app\models\HelpRequestDetails;
use app\models\HelpRequestNotifications;
use app\models\Utils;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class HelpRequestController extends \yii\rest\Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->user->enableSession = false;
    }
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            $behaviors['authenticator'] = [
                'class' => JwtHttpBearerAuth::class,
                'optional' => [
                    'create','download'
                ],
            ];
        }
        /*$behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];*/

        return $behaviors;
    }
    function beforeAction($action)
    {
        $result = parent::beforeAction($action);
        if(Yii::$app->params["LOG"]["API-REQUEST"]) Utils::AddLog("Before: " . $action->controller->id . "/" . $action->id . " - Body:" . Yii::$app->request->rawBody,'info',false,'log-request');
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
            header("Access-Control-Allow-Headers: Authorization, X-Prototype-Version, x-Connection, Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
            exit;
        }

        return $result;
    }
    function afterAction($action, $result)
    {
        //if(Yii::$app->params["LOG"]["API-REQUEST"]) utilfunc::AddLog("After: " . $action->controller->id . "/" . $action->id . "",'info',false,'log-request');

        $result = parent::afterAction($action,$result);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Request-Method: GET, PUT, POST, DELETE, HEAD, OPTIONS");
        //if(Yii::$app->params["LOG"]["API-REQUEST"] && strpos($action->id,"download-file") === false) utilfunc::AddLog($result,'info',false,'log-request');

        return $result;
    }

    public function actionGet($id)
    {
        $userId = Utils::GetUserID();
        $obj = HelpRequest::findOne($id);
        if($obj != null)
        {
            $pRs = $obj->getHelpRequestNotifications()->where(["friendId" => $userId])->all();
            if(count($pRs) > 0 || $obj->userId == $userId)
            {
                $sendNotify = false;
                foreach($pRs as $r)
                {
                    if(strlen($r->dateLastSeen) == 0) {
                        $r->dateLastSeen = date("Y-m-d H:i:s");
                        $r->save(false);
                        $sendNotify = true;
                    }
                }
                if($sendNotify)
                {
                    $obj->sendLastSeenNotificaMqtt($obj->getLastSeenFriends());
                }
                return $obj->getJson();
            }

        }
        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionAddRequest()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);

            $serverity = intval(Utils::GetVal($dati,"serverity"));
            $lat = floatval(Utils::GetVal($dati,"lat"));
            $lon = floatval(Utils::GetVal($dati,"lon"));
            $description = Utils::GetVal($dati,"description");

            $obj = new HelpRequest();
            $obj->userId = Utils::GetUserID();
            $obj->serverity = $serverity;
            $obj->lat = $lat;
            $obj->lon = $lon;
            $obj->description = $description;
            $obj->dateInsert = date("Y-m-d H:i:s");
            $obj->dateModify = date("Y-m-d H:i:s");
            $obj->active = 1;
            if($obj->save(false))
            {
                $obj->publishQueue = "Q-" . $obj->userId . "-" . $obj->id . "-" . Yii::$app->security->generateRandomString(32);
                $obj->save(false);
                $devices = [];
                $helpRequestNotifications = [];
                $pRs = \app\models\UserFriends::find()->where(["userId" => $obj->userId])->all();
                foreach($pRs as $r)
                {
                    $hrd = new HelpRequestNotifications();
                    $hrd->userId = $obj->userId;
                    $hrd->helpRequestId = $obj->id;
                    $hrd->friendId = $r->friendId;
                    $hrd->dateInsert = date("Y-m-d H:i:s");
                    $hrd->dateLastSeen = null;
                    $hrd->save(false);
                    $helpRequestNotifications[] = $hrd;

                    $friend = Users::findOne($r->friendId);
                    if($friend != null)
                    {
                        if(strlen(trim($friend->tokenDevice)) > 0 && $friend->isValidToken()) $devices[] = trim($friend->tokenDevice);
                    }
                }
                if(count($devices) > 0)
                {
                    $obj->sendNotifica($devices,HelpRequest::CATEGORY_HELP_REQUEST);
                }

                return [
                    "helpRequest" => $obj,
                    "helpRequestDetails" => [],
                    "helpRequestNotifications" => $helpRequestNotifications,
                    ];
            }
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionUpdateRequest()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);

            $id = intval(Utils::GetVal($dati,"id"));
            $serverity = intval(Utils::GetVal($dati,"serverity"));
            $active = intval(Utils::GetVal($dati,"active"));

            $obj = HelpRequest::findOne($id);
            if($obj != null && $obj->userId == Utils::GetUserID())
            {
                if($serverity > 0) $obj->serverity = $serverity;
                if($active >= 0) $obj->active = $active;
                if($obj->save(false))
                {
                    if($active == 0)
                    {
                        $obj->sendNotificaFriends(HelpRequest::CATEGORY_END_REQUEST,true);
                    }
                    return $obj->getJson();
                }
            }

        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionAddRequestDetails()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);

            $helpRequestId = intval(Utils::GetVal($dati,"helpRequestId"));
            $lat = floatval(Utils::GetVal($dati,"lat"));
            $lon = floatval(Utils::GetVal($dati,"lon"));
            $audioFile = Utils::GetVal($dati,"audioFile");
            $type = Utils::GetVal($dati,"type");

            $helpRequest = HelpRequest::findOne($helpRequestId);
            if($helpRequestId > 0 && $helpRequest != null)
            {
                $obj = new HelpRequestDetails();
                $obj->helpRequestId = $helpRequestId;
                $obj->lat = $lat;
                $obj->lon = $lon;
                $obj->dateInsert = date("Y-m-d H:i:s");
                $obj->audioFileUrl = "";
                $obj->tokenKey = "";

                if($obj->save(false))
                {
                    if(strlen($audioFile) > 0)
                    {
                        $obj->saveAudio($audioFile,$type);
                        //$helpRequest->sendNotificaFriends(HelpRequest::CATEGORY_UPDATE_REQUEST);
                    }
                    $helpRequest->sendDetailNotificaMqtt($obj->getJson());
                    return $obj;
                }
            }
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionGetAudioFile($id)
    {
        $obj = HelpRequestDetails::findOne($id);
        if($obj != null)
        {
            $userId = Utils::GetUserID();
            $request = HelpRequest::findOne($obj->helpRequestId);
            if($request != null)
            {
                $pRs = $request->getHelpRequestNotifications()->where(["friendId" => $userId])->all();
                if(count($pRs) > 0 || $request->userId == $userId)
                {
                    return Yii::$app->response->sendFile($obj->getFullPathFile(),$obj->audioFileUrl,["inline" => true]);
                }
                else
                {
                    Utils::AddLog("actionGetAudioFile utente non corretto : " . $userId .  " " . $request->userId);
                }
            }
        }
        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionDownload($key)
    {
        $obj = HelpRequestDetails::findOne(["tokenKey" => $key]);
        if($obj != null)
        {
            $request = HelpRequest::findOne($obj->helpRequestId);
            if($request != null)
            {
                $userId = $request->userId;
                $pRs = $request->getHelpRequestNotifications()->where(["friendId" => $userId])->all();
                if(count($pRs) > 0 || $request->userId == $userId)
                {
                    return Yii::$app->response->sendFile($obj->getFullPathFile(),$obj->audioFileUrl,["inline" => true]);
                }
                else
                {
                    Utils::AddLog("actionGetAudioFile utente non corretto : " . $userId .  " " . $request->userId);
                }
            }
        }
        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionCurrentRequests()
    {
        $result = [];
        $pRs = HelpRequest::find()->where(["userId" => Utils::GetUserID(), "active" => 1])->orderBy(["id" => SORT_DESC])->all();
        foreach($pRs as $r)
        {
            $result[] = $r->getJson();
        }
        return $result;
    }
    public function actionActiveRequests()
    {
        $result = [];
        $sql = "SELECT h.*
                FROM HelpRequest h
                LEFT JOIN HelpRequestNotifications hrn ON hrn.helpRequestId = h.id
                WHERE hrn.friendId = " . Utils::GetUserID() . " AND h.active = 1";

        $pRs = HelpRequest::findBySql($sql)->all();
        foreach($pRs as $r)
        {
            $result[] = $r->getJson();
        }
        return $result;
    }
    public function actionIndex($start = 0,$limit = 20)
    {
        $result = [];
        $sql = "SELECT distinct h.*
                FROM HelpRequest h
                LEFT JOIN HelpRequestNotifications hrn ON hrn.helpRequestId = h.id
                WHERE hrn.friendId = " . Utils::GetUserID() . " OR h.userId = " . Utils::GetUserID() . "
                ORDER BY h.id DESC
                LIMIT " . $start . "," . $limit;


        $pRs = HelpRequest::findBySql($sql)->all();
        foreach($pRs as $r)
        {
            $result[] = $r->getJson();
        }
        return $result;
    }
    public function actionSendNotify($id,$device,$category = "")
    {
        $obj = HelpRequest::findOne($id);
        if($obj != null)
        {
            if(strlen($category) == 0) $category = HelpRequest::CATEGORY_HELP_REQUEST;

            return ["result" => $obj->sendNotifica($device,$category)];
        }
        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionDelete($id)
    {
        if(Yii::$app->request->isDelete)
        {
            $userId = Utils::GetUserID();
            $obj = HelpRequest::findOne($id);
            if($obj != null)
            {
                $pRs = $obj->getHelpRequestNotifications()->where(["friendId" => $userId])->all();
                if(count($pRs) > 0 || $obj->userId == $userId)
                {
                    if($obj->userId == $userId)
                    {
                        $obj->delete();
                    }
                    else
                    {
                        foreach($pRs as $r)
                        {
                            $r->delete();
                        }
                    }

                    return ["success" => true];
                }

            }
        }
        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionParameterMqtt()
    {
        $host =Yii::$app->params["MQTT"]["PUBLIC-HOST"];
        $port = Yii::$app->params["MQTT"]["PUBLIC-PORT"];
        $user = Yii::$app->params["MQTT"]["PUBLIC-USER"];
        $password = Yii::$app->params["MQTT"]["PUBLIC-PASSWORD"];

        $result = [
                "url" => "amqp://$user:$password@$host:$port"
            ];

        return $result;
    }
    public function actionLastSeenFriends($id)
    {
        $obj = HelpRequest::findOne($id);
        $userId = Utils::GetUserID();
        if($obj != null && ($obj->userId == $userId || $obj->isFriend($userId)))
        {
            return $obj->getLastSeenFriends();
        }
        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
}
