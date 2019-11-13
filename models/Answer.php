<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "answer".
 *
 * @property int $id
 * @property string $description
 * @property string $answer
 */
class Answer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'answer', 'suffix', 'answer_positive', 'answer_negative'], 'string', 'max' => 1024],
            [['description', 'answer', 'suffix', 'answer_positive', 'answer_negative'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Prefix - (ex. what is, , ecc...)',
            'suffix' => 'Suffix - (ex. favorite color, ecc...)',
            'answer' => 'Answer with neutral sentiment',
            'answer_positive' => 'Answer with positive sentiment',
            'answer_negative' => 'Answer with negative sentiment',
        ];
    }
}
