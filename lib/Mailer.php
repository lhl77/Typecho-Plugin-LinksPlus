<?php

require_once dirname(__FILE__) . '/PhpMailDriver.php';

class Links_Mailer
{
    /**
     * 根据驱动名和配置实例化邮件驱动
     *
     * @param string               $driver  'phpmail'|'smtp'
     * @param array<string,string> $config  SMTP 配置（smtp_host/smtp_port/smtp_user/smtp_pass/smtp_secure）
     * @return Links_MailDriverInterface
     */
    public static function createDriver($driver, array $config = array())
    {
        $driver = strtolower(trim((string)$driver));
        if ($driver === 'smtp') {
            require_once dirname(__FILE__) . '/SmtpMailDriver.php';
            return new Links_SmtpMailDriver(
                isset($config['smtp_host'])   ? (string)$config['smtp_host']   : '',
                isset($config['smtp_port'])   ? (int)$config['smtp_port']      : 587,
                isset($config['smtp_user'])   ? (string)$config['smtp_user']   : '',
                isset($config['smtp_pass'])   ? (string)$config['smtp_pass']   : '',
                isset($config['smtp_secure']) ? (string)$config['smtp_secure'] : 'tls'
            );
        }

        // phpmail 或未知驱动，回退 phpmail
        return new Links_PhpMailDriver();
    }

    /**
     * @param string               $driver
     * @param string               $to
     * @param string               $subject
     * @param string               $htmlBody
     * @param array<string,string> $options  同时作为 SMTP config 传入 createDriver
     * @return array{ok:bool,error:string}
     */
    public static function send($driver, $to, $subject, $htmlBody, array $options = array())
    {
        $mailer = self::createDriver($driver, $options);
        return $mailer->send($to, $subject, $htmlBody, $options);
    }
}
