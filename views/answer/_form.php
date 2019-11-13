<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Answer */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-6">
        
        <?= $form->field($model, 'description')->textArea(['maxlength' => true,'rows' => 3]) ?>

        <?= $form->field($model, 'suffix')->textArea(['maxlength' => true,'rows' => 3]) ?>

        
       

    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'answer')->textArea(['maxlength' => true,'rows' => 3]) ?>

        <?= $form->field($model, 'answer_positive')->textArea(['maxlength' => true,'rows' => 3]) ?>

        <?= $form->field($model, 'answer_negative')->textArea(['maxlength' => true,'rows' => 3]) ?>



    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>