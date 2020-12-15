<?php
use think\migration\Migrator;

class AddonInfoDetail extends Migrator
{

    public function up()
    {
        if (!$this->hasTable('yyb_addon_info_detail')) {
            $table = $this->table('yyb_addon_info_detail', ['engine' => 'InnoDB', 'comment' => '插件副表', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('info_id', 'integer', ['length' => 11, 'null' => false, 'comment' => ''])
                ->addColumn('content', 'text');
        }
    }

    public function down()
    {
        $this->dropTable('yyb_addon_info_detail');
    }

}