<?php

return [
    /*
     * 邮件发送驱动
     * smtp     : 通过 SMTP 服务器发送，最常用（推荐）
     * sendmail : 调用本机 sendmail 程序发送，一般用于 Linux 服务器
     * mail     : 调用 PHP 内置 mail() 函数发送，依赖服务器本地邮件服务
     */
    'driver' => 'smtp',

    /*
     * SMTP 服务器地址
     * 常见示例：smtp.qq.com、smtp.163.com、smtp.gmail.com、smtp.aliyun.com
     */
    'host' => 'smtp.qq.com',

    /*
     * SMTP 服务器端口
     * SSL 加密一般为 465，TLS 加密一般为 587，不加密一般为 25
     */
    'port' => 465,

    /*
     * SMTP 登录用户名，通常为完整的邮箱地址
     */
    'username' => '',

    /*
     * SMTP 登录密码
     * QQ 邮箱、163 邮箱等需要使用在邮箱后台开启的「授权码」，而不是邮箱登录密码
     */
    'password' => '',

    /*
     * 是否启用 SMTP 身份认证
     * 大部分公共邮件服务商都需要开启
     */
    'auth' => true,

    /*
     * 连接加密方式
     * ssl  : SMTPS 加密，通常配合 465 端口
     * tls  : STARTTLS 加密，通常配合 587 端口
     * ''   : 不加密，通常配合 25 端口
     */
    'security' => 'ssl',

    /*
     * 未显式配置加密方式时，是否允许 PHPMailer 自动协商 STARTTLS
     * 服务器不支持 TLS 或使用自签证书时可设为 false
     */
    'auto_tls' => true,

    /*
     * 是否校验 SMTP 服务器 SSL 证书
     * 公网环境请保持 true；内网自签证书环境可设为 false
     */
    'verify_peer' => true,

    /*
     * 默认发件人邮箱地址
     * 留空时自动使用 username 作为发件人地址
     */
    'from_address' => '',

    /*
     * 默认发件人显示名称，例如「某某系统」
     */
    'from_name' => '',

    /*
     * 邮件字符集，推荐 UTF-8
     */
    'charset' => 'UTF-8',

    /*
     * 默认邮件内容类型
     * text/html  : HTML 邮件
     * text/plain : 纯文本邮件
     */
    'content_type' => 'text/html',

    /*
     * 邮件正文编码方式
     * 8bit | base64 | binary | quoted-printable
     */
    'encoding' => 'base64',

    /*
     * SMTP 连接超时时间，单位：秒
     */
    'timeout' => 30,

    /*
     * 调试模式
     * true  : 发送失败时抛出 youvision\mailer\exception\MailException 异常
     * false : 发送失败时返回 false，通过 getError() 获取错误信息
     * 无论是否开启调试模式，失败信息都会写入框架日志
     */
    'debug' => false,

    /*
     * sendmail 驱动使用的命令
     * 仅在 driver 为 sendmail 时生效
     */
    'sendmail' => '/usr/sbin/sendmail -bs',

    /*
     * 模板变量替换左定界符
     * 例如变量名为 name，则模板中的 {name} 会被替换
     */
    'left_delimiter' => '{',

    /*
     * 模板变量替换右定界符
     */
    'right_delimiter' => '}',

    /*
     * 内嵌图片变量前缀
     * 参数键以该前缀开头时，变量值会作为图片嵌入邮件正文
     * 例如 'embed:logo' => '/path/to/logo.png'，模板中使用 {logo} 输出 cid 地址
     */
    'embed' => 'embed:',

    /*
     * 日志通道名称
     * 对应项目 config/log.php 中配置的通道名称，留空使用默认通道
     */
    'log_channel' => '',

    /*
     * 传递给 stream_context_create 的额外选项
     * 例如：['ssl' => ['allow_self_signed' => true, 'verify_peer' => false]]
     */
    'smtp_options' => [],
];