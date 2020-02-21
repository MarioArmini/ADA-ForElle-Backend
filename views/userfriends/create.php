<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserFriends */

$this->title = Yii::t('app', 'Create User Friends');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Friends'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-friends-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
