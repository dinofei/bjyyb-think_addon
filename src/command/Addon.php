<?php
declare (strict_types = 1);

namespace think\addon\command;

use think\addon\facade\Manager;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Config;

class Addon extends Command
{
    protected function configure()
    {
        $this->setName('addon-service')
            ->setDescription('插件服务发现器');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!Config::get('addon.enable')) {
            $output->writeln('<error>插件服务已关闭</error>');
            return true;
        }
        $services = Manager::globPathWithClass('Service');
        $sysServices = parse_config_file($this->app->getRootPath() . 'vendor/services.php');
        $content = '<?php ' . PHP_EOL . "return " . var_export(array_merge($sysServices, $services), true) . ';';
        file_put_contents($this->app->getRootPath() . 'vendor/services.php', $content);
        $output->writeln('<info>Succeed!</info>');
    }
}
