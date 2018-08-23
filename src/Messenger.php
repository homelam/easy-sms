<?php

namespace Wincy\EasySms;

use Wincy\EasySms\Contracts\{MessageInterface, PhoneNumberInterface};
use Wincy\EasySms\Exceptions\NoGatewayAvailableException;
use Wincy\EasySms\Support\Config;

class Messenger
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * @var \Wincy\EasySms\EasySms
     */
    protected $easySms;

    /**
     * constructor
     * 
     * @param \Wincy\EasySms\EasySms
     */
    public function __construct(EasySms $easySms)
    {
        $this->easySms = $easySms;
    }

    /**
     * Send a Message
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
    public function send(PhoneNumberInterface $to, MessageInterface $message, array $gateways = [])
    {
        if (empty($gateways)) {
            $gateways = $message->getGateways();
        }

        if (empty($gateways)) {
            $gateways = $this->easySms->getConfig()->get('default.gateways', []);
        }

        $gateways = $this->formatGateways($gateways);
        $strategyAppliedGateways = $this->easySms->strategy()->apply($gateways);

        $results = [];
        $isSuccessful = false;

        foreach ($strategyAppliedGateways as $gateway) {
            try {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status' => self::STATUS_SUCCESS,
                    'result' => $this->easySms->gateway($gateway)->send($to, $message, new Config($gateways[$gateway])),
                ];
                $isSuccessful = true;
                break;
            } catch (\Throwable $e) {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status' => self::STATUS_FAILURE,
                    'exception' => $e,
                ];
            } catch (\Exception $e) {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status' => self::STATUS_FAILURE,
                    'exception' => $e,
                ];
            }

            if (!$isSuccessful) {
                throw new NoGatewayAvailableException($results);
            }
    
            return $results;
        }


    }

    /**
     * @param array $gateways
     *
     * @return array
     */
    public function formatGateways(array $gateways)
    {
        $formatted = [];
        $config = $this->easySms->getConfig();

        foreach ($gateways as $gateway => $setting) {
            if (is_int($gateway) && is_string($setting)) {
                $gateway = $setting;
                $setting = [];
            }

            $formatted[$gateway] = $setting;
            $globalSetting = $config->get("gateways.{$gateway}", []);

            if (is_string($gateway) && !empty($globalSetting) && is_array($setting)) {
                $formatted[$gateway] = array_merge($globalSetting, $setting);
            }
        }

        return $formatted;
    }
}