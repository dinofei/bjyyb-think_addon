<?php

use think\migration\Migrator;

class AddonInfo extends Migrator
{
    public function up()
    {
        if (!$this->hasTable('yyb_addon_info')) {
            $table = $this->table('yyb_addon_info', ['engine' => 'InnoDB', 'comment' => '插件表', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('title', 'string', ['length' => 100, 'null' => false, 'comment' => '插件名字'])
                ->addColumn('name', 'string', ['length' => 50, 'null' => false, 'comment' => '插件标识'])
                ->addColumn('description', 'string', ['length' => 255, 'null' => true, 'comment' => '插件描述', 'default' => ''])
                ->addColumn('app_version', 'string', ['length' => 20, 'null' => false, 'comment' => '插件版本', 'default' => '1.0.0'])
                ->addColumn('app_state', 'boolean', ['length' => 1, 'null' => false, 'comment' => '状态 0 禁用 1 正常', 'default' => 1])
                ->addColumn('image', 'string', ['length' => 255, 'null' => true, 'comment' => '插件封面', 'default' => ''])
                ->addColumn('icon', 'string', ['length' => 255, 'null' => true, 'comment' => '插件图标', 'default' => ''])
                ->addColumn('price', 'decimal', ['length' => '10,2', 'null' => false, 'comment' => '价格', 'default' => 0.00])
                ->addColumn('created_at', 'integer', ['length' => 11, 'null' => false, 'comment' => ''])
                ->addColumn('updated_at', 'integer', ['length' => 11, 'null' => false, 'comment' => ''])
                ->addColumn('pay_type', 'boolean', ['length' => 1, 'null' => false, 'comment' => '0 永久免费 1 按月', 'default' => 0])
                ->addColumn('type', 'boolean', ['length' => 1, 'null' => false, 'comment' => '系统 0 用户 1', 'default' => 0])
                ->addColumn('pay_count', 'integer', ['length' => 11, 'null' => false, 'comment' => '购买量', 'default' => 0])
                ->addColumn('install_count', 'integer', ['length' => 11, 'null' => false, 'comment' => '安装量', 'default' => 0])
                ->create();
        }
    }

    public function down()
    {
        $this->dropTable('yyb_addon_info');
    }
}
