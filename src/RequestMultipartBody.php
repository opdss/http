<?php

namespace Opdss\Http;

class RequestMultipartBody
{

    /**
     * 类型，键值对
     */
    const TYPE_KV = 0;
    /**
     * 类型，文件
     */
    const TYPE_FILE = 1;

    /**
     * @var array 列表
     */
    private $list = array();

    /**
     * @var string 边界字符串
     */
    private $boundary;

    /**
     * 添加键值对
     * @param $key string
     * @param $value string
     */
    public function add($key, $value)
    {
        $this->list[] = array(
            'type'          => static::TYPE_KV,
            'key'           => $key,
            'value'         => $value,
        );
    }

    /**
     * 添加文件
     * @param $key string
     * @param $file string 文件路径
     * @param $file_name string 文件名
     */
    public function addFile($key, $file, $file_name)
    {
        $this->list[] = array(
                'type'      => static::TYPE_FILE,
                'key'       => $key,
                'file'      => $file,
                'file_name' => $file_name
        );
    }

    public function remove($key)
    {
        $count = count($this->list);
        for($i = 0; $i < $count; $i++)
        {
            if($this->list[$i]['key'] === $key)
            {
                array_splice($this->list, $i, 1);
            }
        }
    }

    public function clear()
    {
        $this->list = array();
    }

    /**
     * @return string 最终构建的body内容
     */
    public function content()
    {
        $this->generateBoundary();
        $content = '';
        foreach ($this->list as $item)
        {
            switch ($item['type'])
            {
                case static::TYPE_KV :
                default :
                    $content .= sprintf("--%s\r\n", $this->boundary);
                    $content .= sprintf("Content-Disposition: form-data; name=\"%s\"\r\n\r\n", $item["key"]);
                    $content .= $item["value"] . "\r\n";
                    break;
                case static::TYPE_FILE :
                    $content .= sprintf("--%s\r\n", $this->boundary);
                    $content .= sprintf("Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"\r\n", $item["key"], $item["file_name"]);
                    $content .= sprintf("Content-Type: application/octet-stream\r\n\r\n");
                    $content .= file_get_contents($item['file']) . "\r\n";
                    break;
            }
        }
        $content .= sprintf("--%s--\r\n\r\n", $this->boundary);
        return $content;
    }

    /**
     * 随机生成一个新的boundary
     */
    private function generateBoundary()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randStr = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < 64; $i++)
        {
            $randStr .= $chars[ mt_rand(0, $max) ];
        }

        $this->boundary = '__BOUNDARY__' . $randStr . '__BOUNDARY__';
    }

    public function getBoundary()
    {
        return $this->boundary;
    }

}
