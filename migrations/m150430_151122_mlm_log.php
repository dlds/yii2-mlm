<?php

namespace dlds\galleryManager\migrations;

use yii\db\Schema;
use yii\db\Migration;

class m140930_003227_gallery_manager extends Migration {

    public function up()
    {
        $this->createTable(
            '{{%log_mlm}}', array(
            'id' => Schema::TYPE_PK,
            'result_saving' => Schema::TYPE_INTEGER.' NOT NULL',
            'result_generator' => Schema::TYPE_STRING.' NOT NULL',
            'commissions' => Schema::TYPE_TEXT.' NOT NULL',
            'created_at' => Schema::TYPE_INT.' NOT NULL',
            'updated_at' => Schema::TYPE_INT.' NOT NULL',
            )
        );
    }

    public function down()
    {
        $this->dropTable('{{%log_mlm}}');
    }
}