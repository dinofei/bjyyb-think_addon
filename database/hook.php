<?php

use think\migration\Migrator;

class Hook extends Migrator
{
    public function up()
    {
        if (!$this->hasTable('yyb_hook')) {
            $table = $this->table('yyb_hook', ['engine' => 'InnoDB', 'comment' => '插件钩子表', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('title', 'string', ['length' => 100, 'comment' => '钩子名称', 'null' => false])
                ->addColumn('description', 'string', ['length' => 255, 'comment' => '钩子描述', 'null' => true, 'default' => ''])
                ->addColumn('addon_id', 'integer', ['length' => 11, 'comment' => '插件ID', 'null' => false])
                ->addColumn('event', 'string', ['length' => 100, 'comment' => '触发标识', 'null' => false])
                ->addColumn('state', 'boolean', ['length' => 1, 'comment' => '状态 0 禁用 1 正常', 'null' => false, 'default' => 1])
                ->addColumn('belong_to', 'integer', ['length' => 11, 'null' => false, 'comment' => '用户 0 系统 >0 其他用户', 'default' => 0])
                ->create();
        }
    }

    public function down()
    {
        $this->dropTable('yyb_hook');
    }
}
