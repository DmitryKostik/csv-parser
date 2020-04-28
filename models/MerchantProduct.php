<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class MerchantProduct extends ActiveRecord
{
    /**
     * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
     */
    public static function tableName() : string
    {
        return '{{merchant_products}}';
    }


    /**
     * Правила валидации модели
     */
    public function rules() : array
    {
        return [
            [['price', 'old_price', 'quantity'], 'integer'],

            [['vendor_name', 'image'], 'string', 'length' => [1, 255]],

            ['title', 'string', 'length' => [0, 300]],
            ['title', 'default', 'value' => '']
        ];
    }

    /**
     * Метки атрибутов
     */
    public function attributeLabels()
    {
        return [
            'vendor_name' => 'Поставщик',
            'title' => 'Наименование',
            'price' => 'Цена',
            'old_price' => 'Старая цена',
            'image' => 'Путь к изображению',
            'quantity' => 'Количество'
        ];
    }


    public function behaviors() : array
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }
}