<?php

namespace youvision\mailer;

use think\facade\Config as ThinkConfig;

class Config
{
    /**
     * @var array 当前生效的配置
     */
    protected static array $config = [];

    /**
     * @var bool 是否已经加载配置
     */
    protected static bool $loaded = false;

    /**
     * 加载配置
     *
     * 会先读取组件自带的默认配置，再与项目 config/mail.php 中的配置合并。
     *
     * @param bool $force 是否强制重新加载
     */
    public static function load(bool $force = false): void
    {
        if (static::$loaded && !$force) {
            return;
        }

        $default = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'mail.php';
        $config  = [];

        if (class_exists(ThinkConfig::class)) {
            try {
                $config = (array) ThinkConfig::get('mail', []);
            } catch (\Throwable) {
                $config = [];
            }
        }

        static::$config = array_merge($default, $config);
        static::$loaded = true;
    }

    /**
     * 获取配置
     *
     * @param string|null $name    配置名，为 null 时返回全部配置
     * @param mixed       $default 默认值
     *
     * @return mixed
     */
    public static function get(?string $name = null, mixed $default = null): mixed
    {
        static::load();

        if ($name === null) {
            return static::$config;
        }

        return static::$config[$name] ?? $default;
    }

    /**
     * 设置配置
     *
     * @param string|array $name  配置名，传入数组时批量合并
     * @param mixed        $value 配置值
     */
    public static function set(string|array $name, mixed $value = null): void
    {
        static::load();

        if (is_array($name)) {
            static::$config = array_merge(static::$config, $name);
        } else {
            static::$config[$name] = $value;
        }
    }

    /**
     * 重置配置，下次读取时重新从框架加载
     */
    public static function reset(): void
    {
        static::$config = [];
        static::$loaded = false;
    }
}