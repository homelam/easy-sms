<?php

namespace Wincy\EasySms\Gateways;
use Wincy\EasySms\Contracts\GatewayInterface;
use Wincy\EasySms\Support\Config;
/**
 * Class Gateway.
 */
abstract class Gateway implements GatewayInterface
{
    const DEFAULT_TIMEOUT = 5.0;
    /**
     * @var \Wincy\EasySms\Support\Config
     */
    protected $config;
    /**
     * @var float
     */
    protected $timeout;
    /**
     * Gateway constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }
    /**
     * Return timeout.
     *
     * @return int|mixed
     */
    public function getTimeout()
    {
        return $this->timeout ?: $this->config->get('timeout', self::DEFAULT_TIMEOUT);
    }
    /**
     * Set timeout.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = floatval($timeout);
        return $this;
    }
    /**
     * @return \Wincy\EasySms\Support\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * @param \Wincy\EasySms\Support\Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return \strtolower(str_replace([__NAMESPACE__.'\\', 'Gateway'], '', \get_class($this)));
    }
}