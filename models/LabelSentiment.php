<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "label_sentiment".
 *
 * @property string $description
 */
class LabelSentiment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'label_sentiment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'required'],
            [['description'], 'string', 'max' => 100],
            [['description'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'description' => 'Description',
        ];
    }
    public static function getArray($description = "-1") {
        $result = [];
        $result[""] = "";
        $pRs = self::find()->orderBy(["description" => SORT_ASC])->all();
        foreach($pRs as $r)
        {
            $result[$r->description] = $r->description;
        }
        if($description != "-1") return Utils::GetVal($result,$description);
        return $result;
    }
}
