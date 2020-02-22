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
use app\models\Utils;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class AuthController extends \yii\rest\Controller
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
                    'login',
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
    public function actionLogin()
    {
        $body = trim(Yii::$app->request->rawBody);
        if(strlen($body) == 0) throw new \yii\web\ForbiddenHttpException("User/Password Wrong",401);
        $j = new \yii\helpers\Json();

        $dati = $j->decode($body);

        $username =Utils::GetVal($dati,"username");
        $password =Utils::GetVal($dati,"password");

        $user = \app\models\User::findByUsername($username);
        if($user == null)
        {
            return ['token' => ''];
        }
        $isPass = $user->validatePassword($password);
        if(!$isPass)
        {
            return ['token' => ''];
        }

        return ['token' => (string)$user->generateTokenJwt()];
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCheckLogin()
    {
        $user = \app\models\Users::findOne(Utils::GetUserID());
        return $this->asJson([
            "userId" =>  $user->id,
            "isLogged" => true,
            "name" => trim($user->name),
            "number" => trim($user->number),
        ]);
    }


}
