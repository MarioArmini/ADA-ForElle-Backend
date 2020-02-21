<?php

namespace app\models;

use Yii;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    public static function copyObject($u)
    {
        $tmp = new User();
        $tmp->id = $u->id;
        $tmp->username = $u->username;
        $tmp->password = $u->password;
        $tmp->authKey = $u->authkey;
        $tmp->accessToken = $u->accesstoken;
        return $tmp;
    }
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        $u = Users::findOne($id);
        if($u !=  null)
        {
            return self::copyObject($u);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        Yii::debug("findIdentityByAccessToken : " . $token . " [" . $type . "]");

        if($type == "sizeg\jwt\JwtHttpBearerAuth")
        {
            $token = Yii::$app->jwt->getParser()->parse((string) $token);
            //utilfunc::AddLog($token);
            return self::findIdentity($token->getClaim('uid'));
        }

        $s = Users::findOne(["accesstoken" => $token]);
        if($s != null)
        {
            return self::findIdentity($s->id);
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $u = Users::findOne(["username" => $username]);
        if($u !=  null)
        {
            return self::copyObject($u);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $result = false;
        if($this->password === Utils::CryptPassword(trim($password)))
        {
            $result = true;
        }
        return $result;
    }

    public function generateTokenJwt()
    {
        $expireDate = strtotime("+60 days");
        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $token = $jwt->getBuilder()
            ->setIssuer(Yii::$app->params["SITE_URL"])              // Configures the issuer (iss claim)
            ->setAudience(Yii::$app->params["SITE_URL"])            // Configures the audience (aud claim)
            ->setId("4f1g23a12aa", true)                            // Configures the id (jti claim), replicating as a header item
            ->setIssuedAt(time())                                   // Configures the time that the token was issue (iat claim)
            ->setExpiration($expireDate)                            // Configures the expiration time of the token (exp claim)
            ->set('uid', $this->id)                                 // Configures a new claim, called "uid"
            ->sign($signer, $jwt->key)                              // creates a signature using [[Jwt::$key]]
            ->getToken();

        return $token;
    }
    public static function GetCurrentUser() {
        return self::findOne(Utils::GetUserID());
    }
}
