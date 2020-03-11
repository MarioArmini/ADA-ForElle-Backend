<?php

/**
 * RabbitController short summary.
 *
 * RabbitController description.
 *
 * @version 1.0
 * @author Utente
 */
namespace app\controllers\api;
use Yii;
use app\models\Cacl;
use app\models\User;
use app\models\Users;
use app\models\HelpRequest;
use app\models\HelpRequestDetails;
use app\models\HelpRequestNotifications;
use app\models\Utils;

class MqttController  extends \yii\web\Controller
{
    public function actionIndex()
    {
        return "";
    }

    function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actionUser($username = "",$password = "")
    {
        /*
        username - the name of the user
        password - the password provided (may be missing if e.g. rabbitmq-auth-mechanism-ssl is used)
         */
        $grants = [];
        if(Yii::$app->params["MQTT"]["USER"] == $username && Yii::$app->params["MQTT"]["PASSWORD"] == $password)
        {
            $grants = [
                "allow",
                "management",
                "user",
                "administrator",
                "monitoring",
                "policymaker",
                "impersonator"
            ];
        }
        else if(Yii::$app->params["MQTT"]["PUBLIC-USER"] == $username && Yii::$app->params["MQTT"]["PUBLIC-PASSWORD"] == $password)
        {
            $grants = [
                "allow",                
            ];
        }
        return implode(" ",$grants);
    }
    public function actionVhost()
    {
        /*
        username - the name of the user
        vhost - the name of the virtual host being accessed
        ip - the client ip address
         */
        return "allow";
    }

    public function actionTopic()
    {
        /*
        username - the name of the user
        vhost - the name of the virtual host containing the resource
        resource - the type of resource (topic in this case)
        name - the name of the exchange
        permission - the access level to the resource (write or read)
        routing_key - the routing key of a published message (when the permission is write) or routing key of the queue binding (when the permission is read)
         */
        return "allow";
    }
    public function actionResource()
    {
        /*
        username - the name of the user
        vhost - the name of the virtual host containing the resource
        resource - the type of resource (exchange, queue, topic)
        name - the name of the resource
        permission - the access level to the resource (configure, write, read) - see the Access Control guide for their meaning
         */
        return "allow";
    }
}