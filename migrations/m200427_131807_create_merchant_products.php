<?php

use yii\db\Migration;

/**
 * Class m200427_131807_create_merchant_products
 */
class m200427_131807_create_merchant_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('merchant_products', [
            'id' => $this->primaryKey(11),
            'created_at' => $this->integer(11)->notNull(),
            'vendor_name' => $this->string(255),
            'title' => $this->string(300)->notNull(),
            'price' => $this->integer(11),
            'old_price' => $this->integer(11),
            'image' => $this->string(255),
            'quantity' => $this->integer(11),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('merchant_products');
    }
}
