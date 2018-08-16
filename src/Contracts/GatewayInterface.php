<?php

namespace Wincy\EasySms\Contracts;

use Wincy\EasySms\Support\Config;

interface GatewayInterface
{
    /**
     * 获取网关名称
     * 
     * @return string
     */
    public function getName();

    /**
     * 发送消息
     * 
     * @param \Wincy\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Wincy\EasySms\Contracts\MessageInterface $message
     * @param \Wincy\EasySms\Support\Config $config
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config);
}