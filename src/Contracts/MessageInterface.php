<?php

namespace Wincy\EasySms\Contracts;

Interface MessageInterface
{
    const TEXT_MESSAGE = 'text';

    /**
     * 信息类型
     * 
     * @return string
     */
    public function getMessageType();

    /**
     * 信息内容
     * 
     * @param \Wincy\EasySms\Contracts\GatewayInterface|null $gateway
     * 
     * @return string
     */
    public function getContent(GatewayInterace $gateway = null);

    /**
     * 信息的模板id
     * 
     * @param \Wincy\EasySms\Contracts\GatewayInterface|null $gateway
     */
    public function getTemplate(GatewayInterface $gateway = null);

    /**
     * Return the template data of message
     * 
     * @param GatewayInterface|null $gateway
     */
    public function getData(GatewayInterface $gateway = null);

    /**
     * 返回支持的短信网关
     * 
     * @return array
     */
    public function getGateways();
}