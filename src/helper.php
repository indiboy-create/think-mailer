<?php

use youvision\mailer\Mailer;

if (!function_exists('mailer')) {
    /**
     * 获取邮件发送实例
     *
     * @param array $config 临时配置，传入后会覆盖同名配置项
     */
    function mailer(array $config = []): Mailer
    {
        return $config ? Mailer::make($config) : Mailer::instance();
    }
}