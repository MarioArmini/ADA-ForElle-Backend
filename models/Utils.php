<?php
namespace app\models;
use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use app\models\User;
use linslin\yii2\curl;
use yii\base\ErrorException;
/**
 * Utils short summary.
 *
 * Utils description.
 *
 * @version 1.0
 * @author Utente
 */
class Utils
{
    public static function PrintArray($a, $pretty = false)
    {
        if($pretty)
        {
            $buf = "";
            foreach($a as $k => $v)
            {
                if(strlen($buf) > 0) $buf .= "\n";
                if(is_array($v))
                {
                    $buf .= $k . ":\n" . self::PrintArray($v,true);
                }
                else if(is_object($v))
                {
                    $buf .= $k . ":\n" . self::PrintArray((array)$v,true);
                }
                else
                {
                    $buf .= $k . ": " . $v;
                }
            }

            return $buf;
        }

        ob_start();

        print_r($a);
        $page = ob_get_contents();
        ob_end_clean();

        return $page;
    }

    public static function PrintArrayDiff($a)
    {
        $buf = "";

        foreach($a as $k => $v)
        {
            if(strlen($buf) > 0) $buf .= "\n";
            $buf .= $k . ": " . $v;
        }

        return $buf;
    }

    public static function PrintArrayHtml($arr, $add_key = true){
        $retStr = '<ul>';
        if (is_array($arr)){
            foreach ($arr as $key=>$val){
                if (is_array($val)){
                    if($add_key)
                        $retStr .= '<li>' . $key . ' => ' . self::PrintArrayHtml($val) . '</li>';
                    else
                        $retStr .= '<li>' . self::PrintArrayHtml($val) . '</li>';
                }else{
                    if($add_key)
                        $retStr .= '<li>' . $key . ' => ' . $val . '</li>';
                    else
                        $retStr .= '<li>' . $val . '</li>';
                }
            }
        }
        $retStr .= '</ul>';
        return $retStr;
    }
    public static function secure_unserialize($s)
    {
        $aVal = array();
        $bRetry = false;
        try
        {
            if(strlen($s) == 0) return $aVal;

            $aVal = unserialize($s);
        }
        catch(\Exception $e )
        {
            $bRetry = true;
        }


        if($bRetry)
        {
            try
            {
                $s = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $s);
                $aVal = unserialize($s);
            }
            catch (\Exception $e )
            {
                $bRetry = true;
                Utils::AddLog($e->getMessage() . "\r\n" . $e->getTraceAsString());
            }
        }

        return $aVal;
    }
    static function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    public static function  StringToDate($s)
    {
        $s = trim($s);

        if(strlen($s) == 0) return 0;

        $data = "";
        $aDataOra = explode(" ",$s);

        if(count($aDataOra) > 0)
        {
            $aBuf = explode("/", $aDataOra[0]);
            if(count($aBuf) >= 3)
            {
                $data = $aBuf[2]."-".$aBuf[1]."-".$aBuf[0];
            }
        }
        if(count($aDataOra) > 1 && strlen($data) > 0)
        {
            $aDataOra[1] = str_replace(".",":",$aDataOra[1]);
            $aBuf = explode(":", $aDataOra[1]);
            $ora = "";
            if(count($aBuf) > 0)
                $ora = intval($aBuf[0]);

            if(count($aBuf) > 1)
                $ora .= ":" . intval($aBuf[1]);
            else
                $ora .= ":00";
            if(count($aBuf) > 2)
                $ora .= ":" . intval($aBuf[2]);
            else
                $ora .= ":00";

            $data .= " " . $ora;
        }

        // Yii::trace("DATA " .$data . " " . $s);
		$data = strtotime(trim($data));

        if($data !== false) return $data;


        return 0;
    }

    public static function DatetimeToString($d,$includi_ora = false, $includi_ss = false)
    {
        $s = "";
        try
        {
            $t = new \DateTime($d);
            $format = "d/m/Y";
            if($includi_ora) $format .= " H:i";
            if($includi_ora && $includi_ss) $format .= ":s";
            $s = $t->format($format);
        }
        catch(\Exception $e)
        {
            Utils::AddLogException($e);
        }

        return $s;
    }

    public static function StringToDatetime($s)
    {
        $s = trim($s);

        if(strlen($s) == 0) return 0;

        $data = "";
        $aDataOra = explode(" ",$s);

        if(count($aDataOra) > 0)
        {
            $aBuf = explode("/", $aDataOra[0]);
            if(count($aBuf) >= 3)
            {
                $data = $aBuf[2]."-".$aBuf[1]."-".$aBuf[0];
            }
        }
        if(count($aDataOra) > 1 && strlen($data) > 0)
        {
            $aDataOra[1] = str_replace(".",":",$aDataOra[1]);
            $aBuf = explode(":", $aDataOra[1]);
            $ora = "";
            if(count($aBuf) > 0)
                $ora = intval($aBuf[0]);

            if(count($aBuf) > 1)
                $ora .= ":" . intval($aBuf[1]);
            else
                $ora .= ":00";
            if(count($aBuf) > 2)
                $ora .= ":" . intval($aBuf[2]);
            else
                $ora .= ":00";

            $data .= " " . $ora;
        }

        return $data;
    }
    public static function AddLogFileName($s,$filename)
    {
        if(is_array($s))
        {
            ob_start();

            print_r($s);

            $s = ob_get_contents();

            ob_end_clean();
        }
        $s = date("d/m/Y H:i:s") . " : " . $s . chr(13).chr(10);

        try
        {
            $exist = file_exists(Yii::$app->basePath . "/temp/log/" . $filename);
            $f =  fopen(Yii::$app->basePath . "/temp/log/" . $filename,"a+");
            fwrite($f,$s,strlen($s));
            fclose($f);
            if(!$exist)
            {
                chmod(Yii::$app->basePath . "/temp/log/" . $filename,0666);
            }
        }
        catch ( ErrorException $e )
        {
            Yii::trace($s);
            Yii::trace($e->getMessage());
        }
    }


    public static function AddLogFile($s,$filename = "log")
    {
        if(is_array($s))
        {
            ob_start();

            print_r($s);

            $s = ob_get_contents();

            ob_end_clean();
        }

        $s .=  chr(13).chr(10);
        $bRetry = false;
        try
        {
            $exist = file_exists(Yii::$app->basePath . "/temp/log/" . $filename . "-" .  date("Ymd"). "-" . getmyuid() . ".txt");
            $f =  fopen(Yii::$app->basePath . "/temp/log/" . $filename . "-" .  date("Ymd"). "-" . getmyuid() . ".txt","a+");
            fwrite($f,$s,strlen($s));
            fclose($f);


            if(!$exist)
            {
                chmod(Yii::$app->basePath . "/temp/log/" . $filename . "-" .  date("Ymd"). "-" . getmyuid() . ".txt",0666);
            }
        }
        catch ( ErrorException $e )
        {
            $bRetry = true;
            Yii::trace($s);
            Yii::trace($e->getMessage());
        }
        if($bRetry)
        {
            $i = 0;
            for($i = 1; $i < 10; $i++)
            {
                try
                {
                    $exist = file_exists(Yii::$app->basePath . "/temp/log/" . $filename . "-" .  date("Ymd_"). "-" . getmyuid() . "_" . $i . ".txt");

                    $f =  fopen(Yii::$app->basePath . "/temp/log/" . $filename . "-" .  date("Ymd_"). "-" . getmyuid() . "_" . $i . ".txt","a+");
                    fwrite($f,$s,strlen($s));
                    fclose($f);

                    if(!$exist)
                    {
                        chmod(Yii::$app->basePath . "/temp/log/" . $filename . "-" .  date("Ymd_"). "-" . getmyuid() . "_" . $i . ".txt",0666);
                    }
                    break;
                }
                catch ( ErrorException $e )
                {
                    Yii::trace($s);
                    Yii::trace($e->getMessage());
                }
            }

        }
    }
    public static function AddLog($s, $type = "info", $echo = false,$filename = "log",$print_stack = false)
    {
        try
        {
            if(is_array($s))
            {
                ob_start();

                print_r($s);

                $s = ob_get_contents();

                ob_end_clean();
            }
            else if(is_object($s))
            {
                ob_start();

                print_r((array)$s);

                $s = ob_get_contents();

                ob_end_clean();
            }
            if($print_stack)
            {
                $s .= "\r\nSTACK TRACE : ############################################\r\n";
                $file_paths = debug_backtrace();
                $i_level = 0;
                foreach($file_paths AS $file_path) {
                    $arg = "";
                    if(isset($file_path['args']))
                    {
                        $arg = "ARGS: ";
                        foreach($file_path['args'] AS $key_arg => $var_arg) {
                            if(is_object($var_arg)) $var_arg = (array)$var_arg;
                            if(is_array($var_arg)) $var_arg = Utils::PrintArray($var_arg);
                            $arg .= "[" . $key_arg . '] => ' . $var_arg . " ";
                        }
                    }
                    $file = "";
                    $function = "";
                    $class = "";
                    $line = "";
                    if(isset($file_path['file'])) $file = $file_path['file'];
                    if(isset($file_path['function'])) $function = "->" . $file_path['function'];
                    if(isset($file_path['class'])) $class = $file_path['class'] . "->";
                    if(isset($file_path['line'])) $line = ":" . $file_path['line'];

                    $s .= "> " . str_pad(" ", $i_level * 2) . $file . "\t" . $class . $function . $line . "\r\n";
                    if(strlen($arg) > 0)
                    {
                        $s .= str_pad(" ", $i_level * 2) . "\t\t" . $arg . "\r\n";
                    }
                    $i_level++;
                }
                $s .= "\r\nFINE STACK TRACE : ############################################\r\n";
            }
            $s = date("d/m/Y H:i:s") . " : " . $type . "\t [" . self::GetIP() . "] - [" . self::GetUserID() . "]\t" . $s;
            if($echo) echo($s . "\n");

            self::AddLogFile($s,$filename);

        }
        catch(\Exception $e)
        {
            $s = $e->getMessage();
            $s .= "\n" . $e->getTraceAsString();
            Yii::debug($s);

            if($type == "error" || $type == "mail")
            {
                $s = $e->getMessage();
                $s .= "\n" . $e->getTraceAsString();
                Utils::AddLog($s); //no email
            }
        }
    }
    public static function AddLogException($e,$type="error")
    {
        $s = $e->getMessage();
        $s .= "\n" . $e->getTraceAsString();
        self::AddLog($s,$type);
    }
    public static function AddLogExceptionStack($e,$type="error")
    {
        $s = $e->getMessage();
        $s .= "\n" . $e->getTraceAsString();
        self::AddLog($s,$type,false,'log',true);
    }
    public static function GetVal(&$a, $k, $value = "")
    {
        if(!is_array($a)) return $value;

        if(isset($a[$k])) return $a[$k];

        return $value;
    }
    public static function GetValInt(&$a, $k, $value = "")
    {
        if(!is_array($a)) return intval($value);

        if(isset($a[$k])) return intval($a[$k]);

        return intval($value);
    }

    public static function GetVal2(&$a, $k1,$k2, $value = "")
    {
        if(!is_array($a)) return $value;

        if(isset($a[$k1]) && strlen($a[$k1]) > 0) return $a[$k1];
        if(isset($a[$k2]) && strlen($a[$k2]) > 0) return $a[$k2];

        return $value;
    }
    public static function GetUserID()
    {
        if(self::IsConsoleMode()) return 0;
        if(Yii::$app->user->identity == null) return 0;
        if(Yii::$app->user->isGuest)
        {
            return 0;
        }

        return Yii::$app->user->identity->id;
    }

    public static function IsConsoleMode()
    {
        if (Yii::$app instanceof \yii\console\Application) return true;

        return false;
    }
    public static function GetCurrentAction()
    {
        return strtolower(trim(Yii::$app->controller->id . "/" . Yii::$app->controller->action->id));
    }
    public static function randomString($len = 8)
    {
        return Yii::$app->security->generateRandomString($len);
    }
    public static function CryptPassword($p)
    {
        return md5($p);
    }
    public static function GetIP()
    {
        if(!self::IsConsoleMode())
        {
            if(isset($_SERVER))
            {
                if(isset($_SERVER["REMOTE_ADDR"])) return $_SERVER["REMOTE_ADDR"];
            }
        }
        return "";
    }
    public static function Chmod($file)
    {
        try
        {

            if(file_exists($file))
            {
                chmod($file,0766);
            }
            return true;
        }
        catch(\Exception $e)
        {
            self::AddLogException($e);
        }

        return false;
    }
    public static function MkDir($dir)
    {
        try
        {

            if(!file_exists($dir))
            {
                mkdir($dir,0777,true);
                chmod($dir,0777);
            }
            return true;
        }
        catch(\Exception $e)
        {
            self::AddLogException($e);
        }

        return false;
    }
    public static function clearPrefix($numero)
    {
        $numero = str_replace(" ","",$numero);
        $numero = str_replace("-","",$numero);
        $numero = str_replace("(","",$numero);
        $numero = str_replace(")","",$numero);
        if(substr($numero,0,3) == "+39") $numero = substr($numero,3);
        $numero = trim($numero);
        return $numero;
    }

    public static function CryptString($s)
    {
        $s = base64_encode(Yii::$app->security->encryptByPassword($s,Yii::$app->params["PASSWORD_KEY"]));

        return $s;
    }

    public static function DecryptString($s)
    {
        $s = base64_decode($s);
        $s = Yii::$app->security->decryptByPassword($s,Yii::$app->params["PASSWORD_KEY"]);

        return $s;
    }
    public static function delFile($dir,$filter = "")
    {
        try
        {
            if(is_dir($dir))
            {
                if(strlen($filter) > 0)
                {
                    $files = glob($dir . $filter);
                    foreach ($files as $file)
                    {
                        if(!is_dir("$file"))
                        {
                            unlink("$file");
                        }
                    }
                }
                else
                {
                    $files = array_diff(scandir($dir), array('.','..'));
                    foreach ($files as $file)
                    {
                        if(!is_dir("$dir/$file"))
                        {
                            unlink("$dir/$file");
                        }
                    }
                }
            }
            else
            {
                if(file_exists($dir)) unlink($dir);
            }
        }
        catch(\Exception $ex)
        {
            Utils::AddLogException($ex);
        }
    }
    public static function ToUTC($s)
    {
        if(strlen($s) == 0) return "";
        $t = strtotime($s);
        if($t == 0) return "";
        return date("c",$t);
    }
}
