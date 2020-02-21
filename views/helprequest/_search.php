<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\HelpRequestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="help-request-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'userId') ?>

    <?= $form->field($model, 'serverity') ?>

    <?= $form->field($model, 'lat') ?>

    <?= $form->field($model, 'lon') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'dateInsert') ?>

    <?php // echo $form->field($model, 'dateModify') ?>

    <?php // echo $form->field($model, 'active') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
