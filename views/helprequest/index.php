<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\HelpRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Help Requests');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="help-request-index">

    <h3><?= Html::encode($this->title) ?></h3>

    
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'userId',
            'serverity',
            'lat',
            'lon',
            //'description',
            //'dateInsert',
            //'dateModify',
            //'active',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    <p>
        <?= Html::a(Yii::t('app', 'Create Help Request'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

</div>
