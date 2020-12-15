<?php


namespace think\addon\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Config;
use think\helper\Str;

class Build extends Command
{
    protected $type;

    protected function configure()
    {
        $this->addArgument('name', Argument::REQUIRED, "插件包名称");
        $this->addArgument('type', Argument::REQUIRED, "插件包类型");
        $this->setName('addon:build')->setDescription('创建一个插件包');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!Config::get('addon.enable')) {
            $output->writeln('<error>插件服务已关闭</error>');
            return true;
        }
        $name = trim($input->getArgument('name'));
        $type = $input->getArgument('type');

        $addonRootDir = $this->app->getRootPath() . 'addon';

        if (!is_dir($addonRootDir)) {
            mkdir($addonRootDir, 0755, true);
        }

        if (is_dir($addonDir = $addonRootDir . DIRECTORY_SEPARATOR . $name)) {
            $output->writeln('<error>插件已存在</error>');
            return false;
        }
        // 1、创建插件根目录
        mkdir($addonDir, 0755, true);
        $addonNameSpace = Str::studly($name);
        // 2、写入composer文件
        $composerFile = <<<EOQ
{
  "name": "addon/{$name}",
  "description": "",
  "autoload": {
    "psr-4": {
      "Addon\\\\{$addonNameSpace}\\\\": "./src"
    }
  }
}
EOQ;
        file_put_contents($addonDir . DIRECTORY_SEPARATOR . 'composer.json', $composerFile);
        // 3、建立程序目录
        $mainDir = $addonDir . DIRECTORY_SEPARATOR . 'src';
        mkdir($mainDir, 0755, true);
        // 4、创建插件信息文件
        $infoFile = <<<EOQ
name = {$name}
title = {$name}
description = {$name}
version = 1.0.0
state = 1
type = {$type}
EOQ;
        file_put_contents($mainDir . DIRECTORY_SEPARATOR . 'info.ini', $infoFile);
        // 5、创建插件服务类
        $stub = file_get_contents(__DIR__ . '/stubs/service.stub');
        $service = str_replace(['{%namespace%}'], ["Addon\\{$addonNameSpace}"], $stub);
        file_put_contents($mainDir . DIRECTORY_SEPARATOR . 'Service.php', $service);
        // 6、创建插件入口类
        $stub = file_get_contents(__DIR__ . '/stubs/entry.stub');
        $service = str_replace(['{%namespace%}'], ["Addon\\{$addonNameSpace}"], $stub);
        file_put_contents($mainDir . DIRECTORY_SEPARATOR . 'Entry.php', $service);
        // 7、创建静态资源目录并连接到public/addon下
        $target = $mainDir . DIRECTORY_SEPARATOR . 'public';
        mkdir($target, 0775);
        $linkDir = $this->app->getRootPath() . 'public/static/addon';
        if (!is_dir($linkDir)) {
            mkdir($linkDir, 0775);
        }
        $link = $linkDir . DIRECTORY_SEPARATOR . $name;
        if (is_dir($link)) {
            unlink($link);
        }

        $this->link($target, $linkDir . DIRECTORY_SEPARATOR . $name);

        $output->writeln('<info>插件创建成功</info>');
    }

    protected function link($target, $link)
    {
        return symlink($target, $link);
    }

}