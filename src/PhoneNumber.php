<?php

namespace Wincy\EasySms;

use Wincy\EasySms\Contracts\PhoneNumberInterface;

class PhoneNumber implements PhoneNumberInterface
{
    /**
     * @var int
     */
    protected $number;

    /**
     * @var int
     */
    protected $IDDCode;

    /**
     * constructor
     * 
     * @param int $numberWithoutIDDCode
     * @param string $IDDCode
     */
    public function __construct($numberWithoutIDDCode, $IDDCode = null)
    {
        $this->number = $numberWithoutIDDCode;
        $this->IDDCode = $IDDCode ? intval(trim($IDDCode, '+0')) : null;
    }

    /**
     * 获取号码前面的代表  86.
     * 
     * @return int
     */
    public function getIDDCode()
    {
        return $this->IDDCode;
    }

    /**
     * 获取手机号码
     * 
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * 获取国际化的号码
     * 
     * @return string
     */
    public function getUniversalNumber()
    {
        return $this->getPrefixedIDDCode('+') . $this->number;
    }

    /**
     * 获取前面补充0的号码格式
     * 
     * @return string
     */
    public function getZeroPrefixedNumber()
    {
        return $this->getPrefixedIDDCode('00') . $this->number;
    }

    /**
     * @param string $prefix
     * 
     * @return null|string
     */
    public function getPrefixedIDDCode($prefix)
    {
        return $this->IDDCode ? $prefix . $this->IDDCode : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUniversalNumber();
    }

    /**
     * Specify data which should be serialized to JSON.
     * 
     * @param mixed
     */
    public function jsonSerialize()
    {
        return $this->getUniversalNumber();
    } 
}