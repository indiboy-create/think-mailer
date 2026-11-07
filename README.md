# think-mailer

基于 [PHPMailer](https://github.com/PHPMailer/PHPMailer) 封装的 ThinkPHP 8 邮件发送组件，支持 SMTP / sendmail / mail 三种驱动、模板邮件、纯文本邮件、附件、内嵌图片、抄送密送、自定义邮件头等常用功能。

## 环境要求

- PHP >= 8.0
- ThinkPHP >= 8.0

## 安装

```bash
composer require youvision/think-mailer
```

组件通过 Composer 的 `extra.think.services` 自动注册服务，安装后无需手动注册。

如果安装后服务未生效，可在项目根目录执行：

```bash
php think service:discover
```

## 配置

执行以下命令将配置模板发布到项目 `config/mail.php`：

```bash
php think vendor:publish
```

也可以手动在项目 `config` 目录下新建 `mail.php`，内容参考组件自带的 [config/mail.php](config/mail.php)：

```php
<?php

return [
    'driver'          => 'smtp',
    'host'            => 'smtp.qq.com',
    'port'            => 465,
    'username'        => 'your-mail@qq.com',
    'password'        => '邮箱授权码',
    'auth'            => true,
    'security'        => 'ssl',
    'auto_tls'        => true,
    'verify_peer'     => true,
    'from_address'    => 'your-mail@qq.com',
    'from_name'       => 'ThinkMailer',
    'charset'         => 'UTF-8',
    'content_type'    => 'text/html',
    'encoding'        => 'base64',
    'timeout'         => 30,
    'debug'           => false,
    'sendmail'        => '/usr/sbin/sendmail -bs',
    'left_delimiter'  => '{',
    'right_delimiter' => '}',
    'embed'           => 'embed:',
    'log_channel'     => '',
    'smtp_options'    => [],
];
```

项目未创建 `config/mail.php` 时，组件会直接使用内置默认配置。

## 快速开始

### 使用门面

```php
use youvision\mailer\Mail;

Mail::to('to@example.com', '收件人')
    ->subject('测试邮件')
    ->html('<h1>欢迎使用 think-mailer</h1>')
    ->send();
```

### 使用实例

```php
use youvision\mailer\Mailer;

Mailer::instance()
    ->from('from@example.com', '发件人')
    ->to('to@example.com')
    ->subject('测试邮件')
    ->text('这是一封纯文本邮件')
    ->send();
```

### 使用辅助函数

```php
mailer()
    ->to('to@example.com')
    ->subject('测试邮件')
    ->html('<p>Hello</p>')
    ->send();
```

## 常用功能

### 多收件人、抄送、密送

```php
Mail::to(['a@example.com', 'b@example.com'])
    ->cc('c@example.com', '抄送人')
    ->bcc('d@example.com')
    ->subject('通知')
    ->html('<p>内容</p>')
    ->send();
```

也支持以邮箱为键、名称为值的数组写法：

```php
Mail::to([
    'a@example.com' => '张三',
    'b@example.com' => '李四',
]);
```

### 模板邮件

```php
Mail::to('to@example.com')
    ->subject('注册成功')
    ->view('mail:register', [
        'username' => '张三',
        'url'      => 'https://example.com',
    ])
    ->send();
```

`view()` 使用 ThinkPHP 视图引擎渲染，模板路径遵循项目视图配置。

### 字符串模板变量替换

```php
Mail::to('to@example.com')
    ->subject('通知')
    ->html('<p>您好，{username}！</p>', ['username' => '张三'])
    ->send();
```

自定义定界符：

```php
->html('<p>您好，{$username}！</p>', ['username' => '张三'], [
    'left_delimiter'  => '{$',
    'right_delimiter' => '}',
])
```

### 多行纯文本

```php
Mail::to('to@example.com')
    ->subject('多行文本')
    ->line('第一行')
    ->line('第二行')
    ->line()
    ->line('第四行')
    ->send();
```

### 附件

```php
Mail::to('to@example.com')
    ->subject('带附件邮件')
    ->html('<p>请查收附件</p>')
    ->attach('/path/to/report.pdf', '报告.pdf')
    ->send();
```

内存附件：

```php
->attachData($content, 'data.csv', 'text/csv')
```

### 内嵌图片

参数键加上 `embed:` 前缀即可将图片嵌入邮件正文：

```php
Mail::to('to@example.com')
    ->subject('内嵌图片')
    ->html('<p>Logo：</p><img src="{logo}">', [
        'embed:logo' => '/path/to/logo.png',
    ])
    ->send();
```

也可以直接传入二进制内容：

```php
'embed:logo' => [$binaryData, 'logo.png', 'image/png']
```

### 自定义邮件头与已读回执

```php
Mail::to('to@example.com')
    ->subject('回执')
    ->header('X-Custom-Header', 'value')
    ->readReceiptTo('receipt@example.com')
    ->html('<p>内容</p>')
    ->send();
```

### 发送前回调

```php
Mail::to('to@example.com')
    ->subject('回调')
    ->html('<p>内容</p>')
    ->send(function (Mailer $mailer, PHPMailer $phpMailer) {
        $phpMailer->addCustomHeader('X-Trace-Id', 'abc123');
    });
```

### 原生 PHPMailer 方法

未封装的方法会通过 `__call` 自动代理到 PHPMailer，也可以直接获取底层实例：

```php
Mail::getPhpMailer()->addCustomHeader('X-Foo', 'bar');
```

## 错误处理

默认 `debug` 为 `false`，发送失败返回 `false`：

```php
if (!Mail::to('to@example.com')->subject('测试')->text('内容')->send()) {
    echo Mail::getError();
}
```

将 `debug` 设为 `true` 后，发送失败会抛出 `youvision\mailer\exception\MailException`：

```php
use youvision\mailer\exception\MailException;

try {
    Mail::to('to@example.com')->subject('测试')->text('内容')->send();
} catch (MailException $e) {
    echo $e->getMessage();
}
```

无论是否开启调试模式，失败信息都会写入 ThinkPHP 日志。

## 动态配置

```php
use youvision\mailer\Config;

Config::set('from_name', '动态发件人');

Mail::make([
    'host'     => 'smtp.163.com',
    'username' => 'your-mail@163.com',
    'password' => '授权码',
])->to('to@example.com')->subject('测试')->text('内容')->send();

Config::reset();
```

## 主要方法

| 方法 | 说明 |
| --- | --- |
| `from($address, $name = null)` | 设置发件人 |
| `to($address, $name = null)` | 设置收件人 |
| `cc($address, $name = null)` | 设置抄送 |
| `bcc($address, $name = null)` | 设置密送 |
| `replyTo($address, $name = null)` | 设置回复地址 |
| `subject($subject)` | 设置主题 |
| `html($content, $param = [], $config = [])` | 设置 HTML 内容 |
| `text($content, $param = [], $config = [])` | 设置纯文本内容 |
| `raw($content, $param = [], $config = [])` | `text()` 别名 |
| `line($content = '', $param = [], $config = [])` | 追加纯文本行 |
| `view($template, $param = [], $config = [])` | 渲染模板内容 |
| `attach($path, $name = null)` | 添加附件 |
| `attachData($data, $name, $mime)` | 添加内存附件 |
| `charset($charset)` | 设置字符集 |
| `priority($priority = 3)` | 设置优先级 |
| `header($name, $value)` | 添加自定义邮件头 |
| `readReceiptTo($address)` | 请求已读回执 |
| `clearRecipients()` | 清空所有收件人 |
| `send($callback = null)` | 发送邮件 |
| `getError()` | 获取最近一次错误 |
| `getErrorInfo()` | 获取 PHPMailer 原始错误 |
| `init()` | 重置消息并重新应用配置 |
| `getPhpMailer()` | 获取底层 PHPMailer 实例 |

## License

MIT