<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\QuestionSentiment */

$this->title = Yii::t('app', 'Create Question Sentiment');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Question Sentiments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="question-sentiment-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
