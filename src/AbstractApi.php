<?php


namespace think\addon;


use think\App;
use think\Paginator;

abstract class AbstractApi
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->initialize();
    }

    protected function initialize()
    {
    }

    protected function api(array $data, $result = true)
    {
        if ($result === true) {
            return json(['data' => $data, 'message' => '请求成功', 'code' => -1], 200);
        }
        return json(['data' => $data, 'message' => $result === false ? '请求失败' : $result, 'code' => 0], 500);
    }

    protected function formatPaginator(Paginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'list_rows' => $paginator->listRows(),
            'current' => $paginator->currentPage(),
            'data' => $paginator->all(),
        ];
    }

    protected function error(string $msg)
    {
        return json(['message' => $msg], 500);
    }

}