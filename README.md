<h1 align="center">Easy SMS</h1>

## 特点

1. 一套写法兼容所有平台
1. 简单配置即可灵活增减服务商
1. 内置多种服务商轮询策略、支持自定义轮询策略
1. 统一的返回值格式，便于日志与监控
1. 自动轮询选择可用的服务商
1. 可以扩展自定义的短信服务

## 平台支持

- [阿里云](https://www.aliyun.com/)
- [阿里大于](https://www.alidayu.com/)(不推荐使用，请使用阿里云)


## 环境需求

- PHP >= 7.0

## 安装

```shell
$ composer require "homelam/easy-sms"
```

## 使用

```php
use Wincy\EasySms\EasySms;

$config = [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Wincy\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'aliyun'
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => '/tmp/easy-sms.log',
        ],
        'aliyun' => [
            'access_key_id' => '',
            'access_key_secret' => '',
            'sign_name' => '',
        ]
    ],
];

$easySms = new EasySms($config);

$easySms->send(13188888888, [
    'content'  => '您的验证码为: 6379',
    'template' => 'SMS_001',
    'data' => [
        'code' => 6379
    ],
]);
```

## 短信内容

由于使用多网关发送，所以一条短信要支持多平台发送，每家的发送方式不一样，但是我们抽象定义了以下公用属性：

- `content` 文字内容，使用在像云片类似的以文字内容发送的平台
- `template` 模板 ID，使用在以模板ID来发送短信的平台
- `data`  模板变量，使用在以模板ID来发送短信的平台

所以，在使用过程中你可以根据所要使用的平台定义发送的内容。

## 发送网关

默认使用 `default` 中的设置来发送，如果某一条短信你想要覆盖默认的设置。在 `send` 方法中使用第三个参数即可：

```php
$easySms->send(13188888888, [
    'content'  => '您的验证码为: 6379',
    'template' => 'SMS_001',
    'data' => [
        'code' => 6379
    ],
 ], ['yunpian', 'juhe']); // 这里的网关配置将会覆盖全局默认值
```

## 返回值

由于使用多网关发送，所以返回值为一个数组，结构如下：
```php
[
    'yunpian' => [
        'gateway' => 'yunpian',
        'status' => 'success',
        'result' => [...] // 平台返回值
    ],
    'juhe' => [
        'gateway' => 'juhe',
        'status' => 'failure',
        'exception' => \Wincy\EasySms\Exceptions\GatewayErrorException 对象
    ],
    //...
]
```

如果所选网关列表均发送失败时，将会抛出 `Wincy\EasySms\Exceptions\NoGatewayAvailableException` 异常，你可以使用 `$e->results` 获取发送结果。

你也可以使用 `$e` 提供的更多便捷方法：

```php
$e->getResults();               // 返回所有 API 的结果，结构同上
$e->getExceptions();            // 返回所有调用异常列表
$e->getException($gateway);     // 返回指定网关名称的异常对象
$e->getLastException();         // 获取最后一个失败的异常对象 
```

## 自定义网关

本拓展已经支持用户自定义网关，你可以很方便的配置即可当成与其它拓展一样的使用：

```php
$config = [
    ...
    'default' => [
        'gateways' => [
            'mygateway', // 配置你的网站到可用的网关列表
        ],
    ],
    'gateways' => [
        'mygateway' => [...], // 你网关所需要的参数，如果没有可以不配置
    ],
];

$easySms = new EasySms($config);

// 注册
$easySms->extend('mygateway', function($gatewayConfig){
    // $gatewayConfig 来自配置文件里的 `gateways.mygateway`
    return new MyGateway($gatewayConfig);
});

$easySms->send(13188888888, [
    'content'  => '您的验证码为: 6379',
    'template' => 'SMS_001',
    'data' => [
        'code' => 6379
    ],
]);
```

## 国际短信

国际短信与国内短信的区别是号码前面需要加国际码，但是由于各平台对国际号码的写法不一致，所以在发送国际短信的时候有一点区别：

```php
use Wincy\EasySms\PhoneNumber;

// 发送到国际码为 31 的国际号码
$number = new PhoneNumber(13188888888, 31);

$easySms->send($number, [
    'content'  => '您的验证码为: 6379',
    'template' => 'SMS_001',
    'data' => [
        'code' => 6379
    ],
]);
```

## 定义短信

你可以根据发送场景的不同，定义不同的短信类，从而实现一处定义多处调用，你可以继承 `Wincy\EasySms\Message` 来定义短信模型：

```php
<?php

use Wincy\EasySms\Message;
use Wincy\EasySms\Contracts\GatewayInterface;
use Wincy\EasySms\Strategies\OrderStrategy;

class OrderPaidMessage extends Message
{
    protected $order;
    protected $strategy = OrderStrategy::class;           // 定义本短信的网关使用策略，覆盖全局配置中的 `default.strategy`
    protected $gateways = ['alidayu', 'yunpian', 'juhe']; // 定义本短信的适用平台，覆盖全局配置中的 `default.gateways`

    public function __construct($order)
    {
        $this->order = $order;
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf('您的订单:%s, 已经完成付款', $this->order->no);    
    }

    // 定义使用模板发送方式平台所需要的模板 ID
    public function getTemplate(GatewayInterface $gateway = null)
    {
        return 'SMS_003';
    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        return [
            'order_no' => $this->order->no    
        ];    
    }
}
```

> 更多自定义方式请参考：[`Wincy\EasySms\Message`](Wincy\EasySms\Message;)

发送自定义短信：

```php
$order = ...;
$message = new OrderPaidMessage($order);

$easySms->send(13188888888, $message);
```

## 各平台配置说明

### [阿里云](https://www.aliyun.com/)

短信内容使用 `template` + `data`

```php
    'aliyun' => [
        'access_key_id' => '',
        'access_key_secret' => '',
        'sign_name' => '',
    ],
```

### [阿里大于](https://www.alidayu.com/)

短信内容使用 `template` + `data`

```php
    'alidayu' => [
        'app_key' => '',
        'app_secret' => '',
        'sign_name' => '',
    ],
```

## License

MIT
