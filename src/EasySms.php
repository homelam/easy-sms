<?php

namespace Wincy\EasySms;

use Closure;
use Wincy\EasySms\Contracts\GatewayInterface;
use Wincy\EasySms\Cnotracts\MessageInterface;
use Wincy\EasySms\Cnotracts\PhoneNumberInterface;
use Wincy\EasySms\Cnotracts\StrategyInterface;
use Wincy\EasySms\Strategies\OrderStrategy;
use Wincy\EasySms\Support\Config;
use RuntimeException;

/**
 * Class EasySms
 */
class EasySms
{
    /** @var Wincy\EasySms\Support\Config */
    protected $config;

    /**
     * @var string
     */
    protected $defaultGateway;

    /**
     * @var array
     */
    protected $customerCreateor = [];

    /**
     * @var array
     */
    protected $gateway = [];

    /**
     * @var Wincy\EasySms\Messenger
     */
    protected $messenger;

    /**
     * @var array
     */
    protected $strategies = [];

    /**
     * constructor
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);

        if (!empty($config['default'])) {
            $this->setDefaultGateway($config['default']);
        }
    }

    /**
     * send a message
     * 
     * @param string|array $to 发送的目标号码
     * @param \Wincy\EasySms\Contracts\MessageInterface|array $message 发送的内容
     * @param array $gateways 网关
     * 
     * @return array
     * 
     * @throws \Wincy\EasySms\Exceptions\InvalidArgumentException
     * @throws \Wincy\EasySms\Exceptions\NoGatewayAvailableException
     */
    public function send($to, $message, array $gateways = [])
    {
        $to = $this->formatPhoneNumber($to);
        $message = $this->formatMessage($message);

        return $this->getMessenger()->send($to, $message, $gateways);
    }

    /**
     * 格式化号码
     * 
     * @param string|\Wincy\EasySms\Contracts\PhoneNumberInterface $number
     * 
     * @return \Wincy\EasySms\PhoneNumber
     */
    public function formatPhoneNumber($number)
    {
        if ($number instanceof PhoneNumberInterface) {
            return $number;
        }

        return new PhoneNumber(trim($number));
    }

    /**
     * 格式化发送信息
     * 
     * @param array|string|\Wincy\EasySms\Contracts\MessageInterface $message
     * 
     * @return \Wincy\EasySms\Contracts\MessgeInterface
     */
    protected function formatMessage($message)
    {
        if (!($message instanceof MessageInterface)) {
            if (!is_array($message)) {
                $message = [
                    'content' => strval($message),
                    'template' => strval($message),
                ];
            }
            $message = new Message($message);
        }

        return $message;
    }

    public function getMessenger()
    {
        return $this->messenger ?: $this->messenger = new Messenger($this);
    }

    /**
     * @return \Wincy\EasySms\Support\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function strategy($strategy = null)
    {
        if (is_null($strategy)) {
            $strategy = $this->config->get('default.strategy', OrderStrategy::class);
        }

        if (!class_exists($strategy)) {
            $strategy = __NAMESPACE__.'\Strategies\\'.ucfirst($strategy);
        }

        if (!class_exists($strategy)) {
            throw new InvalidArgumentException("Unsupported strategy \"{$strategy}\"");
        }

        if (empty($this->strategies[$strategy]) || !($this->strategies[$strategy] instanceof StrategyInterface)) {
            $this->strategies[$strategy] = new $strategy($this);
        }

        return $this->strategies[$strategy];
    }

    /**
     * Create a gateway
     * 
     * @param string|null $name
     * 
     * @return \Wincy\EasySms\Contracts\GatewayInterface
     * 
     * @throws \Wincy\EasySms\Exceptions\InvalidArgumentException
     */
    public function gateway($name = null)
    {
        $name = $name ?: $this->getDefaultGateway();
        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->createGateway($name);
        }

        return $this->gateways[$name];
    }

    /**
     * Get default gateway name.
     *
     * @return string
     *
     * @throws \RuntimeException if no default gateway configured
     */
    public function getDefaultGateway()
    {
        if (empty($this->defaultGateway)) {
            throw new RuntimeException('No default gateway configured');
        }

        return $this->defaultGateway;
    }

    public function setDefaultGateway($name)
    {
        $this->defaultGateway = $name;

        return $this;
    }

    /**
     * Create a new driver instance.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return GatewayInterface
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    protected function createGateway($name)
    {
        if (isset($this->customCreators[$name])) {
            $gateway = $this->callCustomCreator($name);
        } else {
            $className = $this->formatGatewayClassName($name);
            $gateway = $this->makeGateway($className, $this->config->get("gateways.{$name}", []));
        }

        if (!($gateway instanceof GatewayInterface)) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" not inherited from %s.', $name, GatewayInterface::class));
        }

        return $gateway;
    }

    /**
     * Call a custom gateway creator.
     *
     * @param string $gateway
     *
     * @return mixed
     */
    protected function callCustomCreator($gateway)
    {
        return call_user_func($this->customCreators[$gateway], $this->config->get("gateways.{$gateway}", []));
    }

    /**
     * Format gateway name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatGatewayClassName($name)
    {
        if (class_exists($name)) {
            return $name;
        }

        $name = ucfirst(str_replace(['-', '_', ''], '', $name));

        return __NAMESPACE__."\\Gateways\\{$name}Gateway";
    }

    /**
     * Make gateway instance.
     *
     * @param string $gateway
     * @param array  $config
     *
     * @return \Overtrue\EasySms\Contracts\GatewayInterface
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    protected function makeGateway($gateway, $config)
    {
        if (!class_exists($gateway)) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" not exists.', $gateway));
        }

        return new $gateway($config);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($name, Closure $callback)
    {
        $this->customCreators[$name] = $callback;

        return $this;
    }
}