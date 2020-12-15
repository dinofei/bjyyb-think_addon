<?php
use think\migration\Migrator;

class AddonConfig extends Migrator
{

    public function up()
    {
        if (!$this->hasTable('yyb_addon_config')) {
            $table = $this->table('yyb_addon_config', ['engine' => 'InnoDB', 'comment' => '插件配置', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('belong_to', 'integer', ['length' => 11, 'null' => false, 'comment' => '所属用户 0 系统 ', 'default' => 0])
                ->addColumn('content', 'string', ['length' => 5000, 'null' => false, 'comment' => '配置内容'])
                ->addColumn('name', 'string', ['length' => 60, 'null' => false, 'comment' => '插件标识']);
        }
    }

    public function down()
    {
        $this->dropTable('yyb_addon_config');
    }

}