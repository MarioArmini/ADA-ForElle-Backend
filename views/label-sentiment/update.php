<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LabelSentiment */

$this->title = Yii::t('app', 'Update Label Sentiment: {name}', [
    'name' => $model->description,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Label Sentiments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->description, 'url' => ['view', 'id' => $model->description]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="label-sentiment-update">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
