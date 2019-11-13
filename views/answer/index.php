<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\AnswerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Bot Answers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="answer-index">

    <h3><?= Html::encode($this->title) ?></h3>

    

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'id',
            'description',
            'suffix',
            'answer',


            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}{delete}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    <p>
        <?= Html::a(Yii::t('app', 'Create Answer'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Download Json'), ['download-json'], ['class' => 'btn btn-primary','target' => '_blank']) ?>
    </p>
</div>
