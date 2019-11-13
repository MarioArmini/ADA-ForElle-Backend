<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "question_sentiment".
 *
 * @property int $id
 * @property string $description
 * @property string $label
 */
class QuestionSentiment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question_sentiment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string', 'max' => 1024],
            [['label'], 'string', 'max' => 100],
            [['label','description'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Question',
            'label' => 'Label',
        ];
    }
}
