<?php


namespace think\addon\service\AddonChain;


interface HandleInterface
{
    public function install($addon, $data): bool;

    public function uninstall($addon, $data): bool;

    public function getError(): string;
}