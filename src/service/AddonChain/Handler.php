<?php
declare(strict_types=1);

namespace think\addon\service\AddonChain;


class Handler
{
    protected $handle;
    protected $error;

    public function __construct(HandleInterface ...$handle)
    {
        $this->handle = $handle;
    }

    public function install($addon, $data): bool
    {
        foreach ($this->handle as $item) {
            if (!$item->install($addon, $data)) {
                $this->error = $item->getError();
                return false;
            }
        }
        return true;
    }

    public function uninstall($addon, $data): bool
    {
        foreach ($this->handle as $item) {
            if (!$item->uninstall($addon, $data)) {
                $this->error = $item->getError();
                return false;
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

}