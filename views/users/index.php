<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <h3><?= Html::encode($this->title) ?></h3>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            //'password',
            'authkey',
            'accesstoken',

            ['class' => 'yii\grid\ActionColumn','template' => '{update}{delete}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    <p>
        <?= Html::a(Yii::t('app', 'New User'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

</div>
