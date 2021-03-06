<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\UserFriendsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'User Friends');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-friends-index">

    <h3><?= Html::encode($this->title) ?></h3>

   

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'id',
            'userId',
            'friendId',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    <p>
        <?= Html::a(Yii::t('app', 'Create User Friends'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
</div>
