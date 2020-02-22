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
use app\models\Utils;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class UserController extends \yii\rest\Controller
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
                    'create',
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
    public function actionCreate()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);

            $username = trim(Utils::GetVal($dati,"username"));
            $number = trim(Utils::GetVal($dati,"number"));
            $name = Utils::GetVal($dati,"name");
            $password = Utils::GetVal($dati,"password");
            $tokenDevice = Utils::GetVal($dati,"tokenDevice");

            $user = \app\models\User::findByUsername($username);
            if($user == null)
            {
                $user = new \app\models\Users();
                $user->username = $username;
                $user->password = Utils::CryptPassword($password);
                $user->name = $name;
                $user->number = $number;
                $user->authkey = md5(time());
                $user->accesstoken = md5(time() + 26500);
                $user->tokenDevice = $tokenDevice;
                $user->save();
                $user = \app\models\User::findByUsername($username);
            }
            return ['token' => (string)$user->generateTokenJwt()];
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }

    /**
     * @return \yii\web\Response
     */
    public function actionGet()
    {
        $user = \app\models\Users::findOne(Utils::GetUserID());
        return $this->asJson($user->getJson());
    }
    public function actionAddFriend()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);

            $friendId = intval(Utils::GetVal($dati,"friendId"));

            $user = \app\models\Users::findOne(Utils::GetUserID());
            $friend = \app\models\Users::findOne($friendId);
            if($user != null && $friend != null)
            {
                $obj = \app\models\UserFriends::findOne(["userId" => $user->id, "friendId" => $friendId]);
                if($obj == null)
                {
                    $obj = new \app\models\UserFriends();
                    $obj->userId = $user->id;
                    $obj->friendId = $friendId;
                    if($obj->save(false))
                    {
                        return [
                            "success" => true,
                            "id" => $obj->id,
                            "friend" => $friend->getJson()
                            ];
                    }
                }
                else
                {
                    return [
                            "success" => true,
                            "id" => $obj->id,
                            "friend" => $friend->getJson()
                            ];
                }
            }
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionDeleteFriend()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);

            $friendId = intval(Utils::GetVal($dati,"friendId"));

            $user = \app\models\Users::findOne(Utils::GetUserID());
            $friend = \app\models\Users::findOne($friendId);
            if($user != null && $friend != null)
            {
                $obj = \app\models\UserFriends::findOne(["userId" => $user->id, "friendId" => $friendId]);
                if($obj != null)
                {
                    $obj->delete();
                    return [
                            "success" => true,
                            "id" => $obj->id,
                            "friend" => $friend->getJson()
                            ];
                }
            }
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionFriends()
    {
        $user = \app\models\Users::findOne(Utils::GetUserID());
        if($user != null)
        {
            $friends = [];
            $pRs = \app\models\UserFriends::find()->where(["userId" => $user->id])->all();
            foreach($pRs as $r)
            {
                $friend = \app\models\Users::findOne($r->friendId);
                if($friend != null)
                {
                    $friends[] = $friend->getJson();
                }
            }
            return [
                    "userId" => $user->id,
                    "friends" => $friends,
                    ];
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
    public function actionFindFriends()
    {
        if(Yii::$app->request->isPost)
        {
            $body = trim(Yii::$app->request->rawBody);
            $j = new \yii\helpers\Json();

            $dati = $j->decode($body);
            if(!is_array($dati))  throw new \yii\web\BadRequestHttpException("Data Wrong");



            $user = \app\models\Users::findOne(Utils::GetUserID());
            if($user != null)
            {
                $pRs = Users::find()->where(['number' => $dati])->all();
                return $pRs;
            }
        }

        throw new \yii\web\BadRequestHttpException("Data Wrong");
    }
}
