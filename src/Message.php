<?php

namespace Wincy\EasySms;

use Wincy\EasySms\Contracts\GatewayInterface;

class Message implements \Wincy\EasySms\Contracts\MessageInterface
{
    /**
     * @var array
     */
    protected $gateways = [];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * constructor
     * 
     * @param array $attributes
     * @param string $type
     */
    public function __construct(array $attributes, $type = MessageInterface::TEXT_MESSAGE)
    {
        $this->type = $type;

        foreach ($attributes as $property => $value) {
            // preperty_exists 检查对象或类是否具有该属性
            if (property_exists($this, $property)) {
                $this->property = $value;
            }
        }
    }

    /**
     * 信息类型
     * 
     * @return string
     */
    public function getMessageType()
    {
        return $this->type;
    }

    /**
     * 信息内容
     * 
     * @param \Wincy\EasySms\Contracts\GatewayInterface|null $gateway
     * 
     * @return string
     */
    public function getContent(GatewayInterace $gateway = null)
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * 
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
    /**
     * 信息的模板id
     * 
     * @param \Wincy\EasySms\Contracts\GatewayInterface|null $gateway
     */
    public function getTemplate(GatewayInterface $gateway = null)
    {
        return $this->template;
    }

    /**
     * 设置信息模板
     * 
     * @param mixed $template
     * 
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param string $type
     * 
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Return the template data of message
     * 
     * @param GatewayInterface|null $gateway
     */
    public function getData(GatewayInterface $gateway = null)
    {
        return $this->data;
    }

    /**
     * 设置模板参数
     * 
     * @param array $data
     * 
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 返回支持的短信网关
     * 
     * @return array
     */
    public function getGateways()
    {
        return $this->gateways;
    }

    public function setGateways(array $gateways = [])
    {
        $this->gateways = $gateways;

        return $this;
    }

    /**
     * @param $property
     * 
     * @return string
     */
    public function __get($property)
    {
        if (property_exists($property)) {
            return $this->$property;
        }
    }
}