<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\HelpRequest */

$this->title = Yii::t('app', 'Create Help Request');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Help Requests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="help-request-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
