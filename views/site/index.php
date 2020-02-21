<?php
use yii\helpers\Url;
/* @var $this yii\web\View */

$this->title = "MC3 Database";
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>MC3 Backend</h1>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4"> 
                <?php if(!Yii::$app->user->isGuest) {?>
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Insert Data</strong></div>
                    <div class="panel-body">
                        <ul>
                            <li>
                                <a href="<?=Url::to(["@web/users"])?>">
                                <strong>Users</strong>
                                </a>
                            </li>                            
                            <li>
                                <a href="<?=Url::to(["@web/helprequest"])?>">
                                    <strong>Help Request</strong>
                                </a>
                            </li>
                        </ul>
                    </div>                    
                </div>
                <?php } ?>
            </div>
            <div class="col-lg-4">                
            </div>
        </div>

    </div>
</div>
