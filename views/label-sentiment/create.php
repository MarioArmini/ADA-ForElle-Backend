<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LabelSentiment */

$this->title = Yii::t('app', 'Create Label Sentiment');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Label Sentiments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="label-sentiment-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
