<?php

use think\migration\Migrator;

class Addon extends Migrator
{
    public function up()
    {
        if (!$this->hasTable('yyb_addon')) {
            $table = $this->table('yyb_addon', ['engine' => 'InnoDB', 'comment' => '插件安装表', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('info_id', 'integer', ['length' => 11, 'null' => false, 'comment' => '插件ID'])
                ->addColumn('version', 'string', ['length' => 20, 'null' => false, 'comment' => '插件版本', 'default' => '1.0.0'])
                ->addColumn('state', 'boolean', ['length' => 1, 'null' => false, 'comment' => '状态 0 禁用 1 正常', 'default' => 1])
                ->addColumn('created_at', 'integer', ['length' => 11, 'null' => false, 'comment' => ''])
                ->addColumn('updated_at', 'integer', ['length' => 11, 'null' => false, 'comment' => ''])
                ->addColumn('belong_to', 'integer', ['length' => 11, 'null' => false, 'comment' => '用户 0 系统 >0 其他用户', 'default' => 0])
                ->addColumn('method', 'boolean', ['length' => 1, 'null' => false, 'comment' => '-1 无需付费 0 套餐购买 1 单独购买', 'default' => 0])
                ->addColumn('expired_at', 'integer', ['length' => 11, 'null' => true, 'comment' => '过期时间', 'default' => 0])
                ->addColumn('closed_at', 'integer', ['length' => 11, 'null' => true, 'comment' => '关闭时间', 'default' => 0])
                ->addColumn('uninstalled_at', 'integer', ['length' => 11, 'null' => true, 'comment' => '卸载时间', 'default' => 0])
                ->create();
        }
    }

    public function down()
    {
        $this->dropTable('yyb_addon');
    }
}
