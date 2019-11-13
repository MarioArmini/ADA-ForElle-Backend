<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\LabelSentiment;

/* @var $this yii\web\View */
/* @var $model app\models\QuestionSentiment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-6">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'description')->textArea(['maxlength' => true,'rows' => 3]) ?>

        <?= $form->field($model, 'label')->dropDownlist(LabelSentiment::getArray()) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
    
</div>
