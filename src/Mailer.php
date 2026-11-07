<?php

namespace youvision\mailer;

use PHPMailer\PHPMailer\PHPMailer;
use youvision\mailer\exception\MailException;

class Mailer
{
    /**
     * @var Mailer|null 全局单例
     */
    protected static ?Mailer $instance = null;

    /**
     * @var PHPMailer PHPMailer 实例
     */
    protected PHPMailer $mailer;

    /**
     * @var array 当前生效配置
     */
    protected array $config;

    /**
     * @var array 纯文本行内容
     */
    protected array $lines = [];

    /**
     * @var string|null 最近一次错误信息
     */
    protected ?string $error = null;

    /**
     * @param array $config 临时配置，会覆盖项目中的同名配置项
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            Config::set($config);
        }

        $this->config = Config::get();
        $this->init();
    }

    /**
     * 获取全局单例
     *
     * @param array $config 临时配置
     */
    public static function instance(array $config = []): static
    {
        if (static::$instance === null) {
            static::$instance = new static($config);
        } elseif ($config) {
            Config::set($config);
            static::$instance->config = Config::get();
            static::$instance->init();
        }

        return static::$instance;
    }

    /**
     * 创建一个全新的实例
     *
     * @param array $config 临时配置
     */
    public static function make(array $config = []): static
    {
        return new static($config);
    }

    /**
     * 重置邮件消息，重新应用配置
     */
    public function init(): static
    {
        $this->mailer = new PHPMailer(true);
        $this->lines  = [];
        $this->error  = null;

        $this->applyConfig();

        return $this;
    }

    /**
     * 应用配置到 PHPMailer
     */
    protected function applyConfig(): void
    {
        $config = $this->config;
        $mailer = $this->mailer;

        $driver = strtolower(trim((string) ($config['driver'] ?? 'smtp')));

        switch ($driver) {
            case 'smtp':
                $mailer->isSMTP();
                $mailer->Host       = (string) ($config['host'] ?? '');
                $mailer->Port       = (int) ($config['port'] ?? 25);
                $mailer->SMTPAuth   = (bool) ($config['auth'] ?? true);
                $mailer->Timeout    = (int) ($config['timeout'] ?? 30);
                $mailer->SMTPAutoTLS = (bool) ($config['auto_tls'] ?? true);

                if ($mailer->SMTPAuth) {
                    $mailer->Username = (string) ($config['username'] ?? '');
                    $mailer->Password = (string) ($config['password'] ?? '');
                }

                $mailer->SMTPSecure  = $this->resolveSecure((string) ($config['security'] ?? ''));
                $mailer->SMTPOptions = $this->resolveSmtpOptions((array) ($config['smtp_options'] ?? []));
                break;

            case 'sendmail':
                $mailer->isSendmail();
                if (!empty($config['sendmail'])) {
                    $mailer->Sendmail = (string) $config['sendmail'];
                }
                break;

            case 'mail':
                $mailer->isMail();
                break;

            default:
                throw new MailException(sprintf('不支持的邮件驱动：%s', $driver));
        }

        $mailer->CharSet  = (string) ($config['charset'] ?? 'UTF-8');
        $mailer->Encoding = (string) ($config['encoding'] ?? 'base64');
        $mailer->isHTML('text/plain' !== (string) ($config['content_type'] ?? 'text/html'));

        $fromAddress = trim((string) ($config['from_address'] ?? ''));
        if ('' === $fromAddress) {
            $fromAddress = trim((string) ($config['username'] ?? ''));
        }

        if ('' !== $fromAddress) {
            $mailer->setFrom($fromAddress, (string) ($config['from_name'] ?? ''));
        }
    }

    /**
     * 解析加密方式
     */
    protected function resolveSecure(string $security): string
    {
        return match (strtolower(trim($security))) {
            'ssl', 'smtps'       => PHPMailer::ENCRYPTION_SMTPS,
            'tls', 'starttls'    => PHPMailer::ENCRYPTION_STARTTLS,
            default              => '',
        };
    }

    /**
     * 解析 SMTP 连接选项
     */
    protected function resolveSmtpOptions(array $options): array
    {
        if (array_key_exists('verify_peer', $this->config) && !$this->config['verify_peer']) {
            $options = array_replace_recursive($options, [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ]);
        }

        return $options;
    }

    /**
     * 设置发件人
     */
    public function from(string $address, ?string $name = null): static
    {
        $this->mailer->setFrom($address, $name ?? (string) ($this->config['from_name'] ?? ''));

        return $this;
    }

    /**
     * 设置收件人，支持字符串或数组
     *
     * 数组支持两种写法：
     * ['foo@example.com', 'bar@example.com']
     * ['foo@example.com' => '张三', 'bar@example.com' => '李四']
     */
    public function to(string|array $address, ?string $name = null): static
    {
        $this->addAddresses('addAddress', $address, $name);

        return $this;
    }

    /**
     * 设置抄送人
     */
    public function cc(string|array $address, ?string $name = null): static
    {
        $this->addAddresses('addCC', $address, $name);

        return $this;
    }

    /**
     * 设置密送人
     */
    public function bcc(string|array $address, ?string $name = null): static
    {
        $this->addAddresses('addBCC', $address, $name);

        return $this;
    }

    /**
     * 设置回复地址
     */
    public function replyTo(string|array $address, ?string $name = null): static
    {
        $this->addAddresses('addReplyTo', $address, $name);

        return $this;
    }

    /**
     * 批量添加地址
     */
    protected function addAddresses(string $method, string|array $address, ?string $name = null): void
    {
        foreach ((array) $address as $key => $value) {
            if (is_int($key)) {
                $this->mailer->{$method}((string) $value, (string) ($name ?? ''));
            } else {
                $this->mailer->{$method}((string) $key, (string) $value);
            }
        }
    }

    /**
     * 设置邮件主题
     */
    public function subject(string $subject): static
    {
        $this->mailer->Subject = $subject;

        return $this;
    }

    /**
     * 设置 HTML 邮件内容
     *
     * @param string $content 内容或模板字符串
     * @param array  $param   变量替换参数
     * @param array  $config  模板定界符配置，如 ['left_delimiter' => '{$', 'right_delimiter' => '}']
     */
    public function html(string $content, array $param = [], array $config = []): static
    {
        $this->mailer->isHTML(true);
        $this->mailer->Body = $this->render($content, $param, $config);

        return $this;
    }

    /**
     * 设置纯文本邮件内容
     */
    public function text(string $content, array $param = [], array $config = []): static
    {
        $this->mailer->isHTML(false);
        $this->mailer->Body = $this->render($content, $param, $config);

        return $this;
    }

    /**
     * 设置纯文本邮件内容（text 的别名）
     */
    public function raw(string $content, array $param = [], array $config = []): static
    {
        return $this->text($content, $param, $config);
    }

    /**
     * 追加一行纯文本内容
     */
    public function line(string $content = '', array $param = [], array $config = []): static
    {
        $this->lines[] = $this->render($content, $param, $config);

        return $this;
    }

    /**
     * 使用 ThinkPHP 模板渲染邮件内容
     *
     * @param string $template 模板名称，遵循 ThinkPHP 视图规则，如 mail:register
     * @param array  $param    模板变量
     * @param array  $config   视图配置
     */
    public function view(string $template, array $param = [], array $config = []): static
    {
        $param   = $this->parseEmbedParams($param);
        $content = app('view')->fetch($template, $param, $config);

        return $this->html($content);
    }

    /**
     * 处理内嵌图片参数
     */
    protected function parseEmbedParams(array $param): array
    {
        $flag = (string) ($this->config['embed'] ?? 'embed:');
        if ('' === $flag) {
            return $param;
        }

        $result = [];
        foreach ($param as $key => $value) {
            $key = (string) $key;

            if (str_starts_with($key, $flag)) {
                $key   = substr($key, strlen($flag));
                $value = $this->embedImage($value);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 将图片嵌入邮件并返回 cid 地址
     *
     * 支持两种写法：
     * 'embed:logo' => '/path/to/logo.png'
     * 'embed:logo' => [$binaryData, 'logo.png', 'image/png']
     */
    protected function embedImage(mixed $value): string
    {
        $cid = 'img_' . bin2hex(random_bytes(8));

        if (is_array($value)) {
            $data = (string) ($value[0] ?? '');
            $name = (string) ($value[1] ?? 'image.jpg');
            $mime = (string) ($value[2] ?? 'image/jpeg');

            $this->mailer->addStringEmbeddedImage($data, $cid, $name, PHPMailer::ENCODING_BASE64, $mime);
        } else {
            $path = (string) $value;
            $this->mailer->addEmbeddedImage($path, $cid, basename($path));
        }

        return 'cid:' . $cid;
    }

    /**
     * 替换内容中的模板变量
     */
    protected function render(string $content, array $param, array $config = []): string
    {
        if (!$param) {
            return $content;
        }

        $left  = (string) ($config['left_delimiter'] ?? $this->config['left_delimiter'] ?? '{');
        $right = (string) ($config['right_delimiter'] ?? $this->config['right_delimiter'] ?? '}');
        $flag  = (string) ($this->config['embed'] ?? 'embed:');

        $map = [];
        foreach ($param as $key => $value) {
            $key = (string) $key;

            if ('' !== $flag && str_starts_with($key, $flag)) {
                $key   = substr($key, strlen($flag));
                $value = $this->embedImage($value);
            }

            if (is_scalar($value) || null === $value) {
                $map[$left . $key . $right] = (string) $value;
            }
        }

        return strtr($content, $map);
    }

    /**
     * 添加附件
     *
     * @param string      $path 附件路径
     * @param string|null $name 附件显示名称
     */
    public function attach(string $path, ?string $name = null): static
    {
        $this->mailer->addAttachment($path, $name ?? '');

        return $this;
    }

    /**
     * 添加内存中的附件
     */
    public function attachData(string $data, string $name = 'attachment', string $mime = 'application/octet-stream'): static
    {
        $this->mailer->addStringAttachment($data, $name, PHPMailer::ENCODING_BASE64, $mime);

        return $this;
    }

    /**
     * 设置字符编码
     */
    public function charset(string $charset): static
    {
        $this->mailer->CharSet = $charset;

        return $this;
    }

    /**
     * 设置邮件优先级
     *
     * 1 = 最高，3 = 普通，5 = 最低
     */
    public function priority(int $priority = 3): static
    {
        $this->mailer->Priority = $priority;

        return $this;
    }

    /**
     * 请求已读回执
     */
    public function readReceiptTo(string $address): static
    {
        $this->mailer->addCustomHeader('Disposition-Notification-To', $address);

        return $this;
    }

    /**
     * 添加自定义邮件头
     */
    public function header(string $name, string $value): static
    {
        $this->mailer->addCustomHeader($name, $value);

        return $this;
    }

    /**
     * 清空所有收件人
     */
    public function clearRecipients(): static
    {
        $this->mailer->clearAllRecipients();

        return $this;
    }

    /**
     * 发送邮件
     *
     * @param \Closure|null $callback 发送前回调，签名 function (Mailer $mailer, PHPMailer $phpMailer)
     *
     * @return bool 发送成功返回 true
     * @throws MailException 调试模式下发送失败时抛出
     */
    public function send(?\Closure $callback = null): bool
    {
        try {
            if ($this->lines) {
                $this->mailer->isHTML(false);
                $this->mailer->Body = implode("\r\n", $this->lines);
                $this->lines        = [];
            }

            if (null !== $callback) {
                $callback($this, $this->mailer);
            }

            $result = $this->mailer->send();

            if (!$result && $this->mailer->ErrorInfo) {
                throw new MailException($this->mailer->ErrorInfo);
            }

            if (!empty($this->config['debug'])) {
                $this->log('邮件发送成功，主题：' . (string) $this->mailer->Subject, 'info');
            }

            return true;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();

            $this->log(sprintf(
                '邮件发送失败，主题：%s，错误：%s',
                (string) $this->mailer->Subject,
                $e->getMessage()
            ), 'error');

            if (!empty($this->config['debug'])) {
                if ($e instanceof MailException) {
                    throw $e;
                }

                throw new MailException($e->getMessage(), (int) $e->getCode(), $e);
            }

            return false;
        }
    }

    /**
     * 获取最近一次错误信息
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * 获取 PHPMailer 原始错误信息
     */
    public function getErrorInfo(): string
    {
        return (string) $this->mailer->ErrorInfo;
    }

    /**
     * 获取底层 PHPMailer 实例
     */
    public function getPhpMailer(): PHPMailer
    {
        return $this->mailer;
    }

    /**
     * 写入日志
     */
    protected function log(string $message, string $level = 'info'): void
    {
        try {
            if (!class_exists(\think\facade\Log::class)) {
                return;
            }

            $channel = (string) ($this->config['log_channel'] ?? '');

            if ('' !== $channel) {
                \think\facade\Log::channel($channel)->{$level}($message);
            } else {
                \think\facade\Log::{$level}($message);
            }
        } catch (\Throwable) {
            // 日志写入失败不影响邮件发送流程
        }
    }

    /**
     * 代理 PHPMailer 原生方法
     */
    public function __call(string $method, array $arguments): mixed
    {
        if (method_exists($this->mailer, $method)) {
            $result = $this->mailer->{$method}(...$arguments);

            return $result === $this->mailer ? $this : $result;
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $method));
    }
}