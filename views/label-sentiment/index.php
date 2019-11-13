<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\LabelSentimentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Label Sentiments');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="label-sentiment-index">

    <h3><?= Html::encode($this->title) ?></h3>

    
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'description',

            ['class' => 'yii\grid\ActionColumn','template' => '{update}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    <p>
        <?= Html::a(Yii::t('app', 'Create Label'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

</div>
