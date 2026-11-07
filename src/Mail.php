<?php

namespace youvision\mailer;

use think\Facade;

/**

 * @method static Mailer init()
 * @method static Mailer subject(string $subject)
 * @method static Mailer from(string $address, ?string $name = null)
 * @method static Mailer to(string|array $address, ?string $name = null)
 * @method static Mailer cc(string|array $address, ?string $name = null)
 * @method static Mailer bcc(string|array $address, ?string $name = null)
 * @method static Mailer replyTo(string|array $address, ?string $name = null)
 * @method static Mailer html(string $content, array $param = [], array $config = [])
 * @method static Mailer text(string $content, array $param = [], array $config = [])
 * @method static Mailer raw(string $content, array $param = [], array $config = [])
 * @method static Mailer line(string $content = '', array $param = [], array $config = [])
 * @method static Mailer attach(string $path, ?string $name = null)
 * @method static Mailer attachData(string $data, string $name = 'attachment', string $mime = 'application/octet-stream')
 * @method static Mailer view(string $template, array $param = [], array $config = [])
 * @method static Mailer charset(string $charset)
 * @method static Mailer priority(int $priority = 3)
 * @method static Mailer readReceiptTo(string $address)
 * @method static Mailer header(string $name, string $value)
 * @method static Mailer clearRecipients()
 * @method static bool send(?\Closure $callback = null)
 * @method static string|null getError()
 * @method static string getErrorInfo()
 * @method static \PHPMailer\PHPMailer\PHPMailer getPhpMailer()
 */
class Mail extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Mailer::class;
    }
}