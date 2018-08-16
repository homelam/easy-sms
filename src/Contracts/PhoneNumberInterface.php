<?php

namespace Wincy\EasySms\Contracts;

interface PhoneNumberInterface extends \JsonSerializable
{
    /**
     * @return int
     */
    public function getIDDCode();

    /**
     * @return int
     */
    public function getNumber();

    /**
     * @return string
     */
    public function getUniversalNumber();

    /**
     * @return string
     */
    public function getZeroPrefixedNumber();

    /**
     * @return string
     */
    public function __toString();
}