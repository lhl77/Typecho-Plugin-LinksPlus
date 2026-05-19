<?php

require_once dirname(__FILE__) . '/MailDriverInterface.php';

/**
 * SMTP 邮件驱动（纯 PHP stream_socket_client 实现，无需扩展库）
 * 支持 STARTTLS（port 587）和 SSL/TLS（port 465）以及明文（port 25）。
 */
class Links_SmtpMailDriver implements Links_MailDriverInterface
{
    private $host;
    private $port;
    private $user;
    private $pass;
    /** @var string 'ssl'|'tls'|'' */
    private $secure;

    public function __construct($host, $port, $user, $pass, $secure = 'tls')
    {
        $this->host   = (string)$host;
        $this->port   = (int)$port ?: 587;
        $this->user   = (string)$user;
        $this->pass   = (string)$pass;
        $this->secure = strtolower(trim((string)$secure));
    }

    private function sanitizeHeaderValue($value)
    {
        return trim(str_replace(array("\r", "\n"), '', (string)$value));
    }

    /**
     * 发送 SMTP 命令并读取完整响应（支持多行 250-xxx 格式）
     */
    private function smtpExchange($fp, $cmd)
    {
        if ($cmd !== '') {
            fwrite($fp, $cmd . "\r\n");
        }
        $resp = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($line === false) {
                break;
            }
            $resp .= $line;
            // RFC 2821：响应结束标志 —— 第4字节是空格（非连字符 '-'）
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $resp;
    }

    private function smtpCode($resp)
    {
        return (int)substr(ltrim((string)$resp), 0, 3);
    }

    public function send($to, $subject, $htmlBody, array $options = array())
    {
        $to      = $this->sanitizeHeaderValue($to);
        $subject = $this->sanitizeHeaderValue($subject);

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return array('ok' => false, 'error' => '收件人邮箱不合法');
        }

        $fromEmail = isset($options['from_email']) ? $this->sanitizeHeaderValue($options['from_email']) : '';
        $fromName  = isset($options['from_name'])  ? $this->sanitizeHeaderValue($options['from_name'])  : '';
        if ($fromEmail === '') {
            $fromEmail = $this->user;
        }
        if ($fromEmail === '') {
            return array('ok' => false, 'error' => '未配置发件邮箱（SMTP 用户名）');
        }

        // ---------- 建立 TCP 连接 ----------
        $errno = 0; $errstr = '';
        $socketHost = ($this->secure === 'ssl') ? ('ssl://' . $this->host) : $this->host;
        $fp = @stream_socket_client($socketHost . ':' . $this->port, $errno, $errstr, 15);
        if (!$fp) {
            return array('ok' => false, 'error' => 'SMTP 连接失败: ' . $errstr);
        }
        stream_set_timeout($fp, 30);
        stream_set_blocking($fp, true);

        // ---------- 读取服务器问候 ----------
        $greeting = fgets($fp, 1024);
        if ($this->smtpCode($greeting) !== 220) {
            fclose($fp);
            return array('ok' => false, 'error' => 'SMTP 问候失败: ' . trim((string)$greeting));
        }

        $helo = isset($options['helo_domain']) && $options['helo_domain'] !== '' ? $options['helo_domain'] : 'localhost';
        $resp = $this->smtpExchange($fp, 'EHLO ' . $helo);
        if ($this->smtpCode($resp) !== 250) {
            $resp = $this->smtpExchange($fp, 'HELO ' . $helo);
            if ($this->smtpCode($resp) !== 250) {
                fclose($fp);
                return array('ok' => false, 'error' => 'EHLO/HELO 失败: ' . trim($resp));
            }
        }

        // ---------- STARTTLS ----------
        if ($this->secure === 'tls') {
            $resp = $this->smtpExchange($fp, 'STARTTLS');
            if ($this->smtpCode($resp) !== 220) {
                fclose($fp);
                return array('ok' => false, 'error' => 'STARTTLS 失败: ' . trim($resp));
            }
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            if (!@stream_socket_enable_crypto($fp, true, $cryptoMethod)) {
                fclose($fp);
                return array('ok' => false, 'error' => 'TLS 握手失败，请检查 SMTP 服务器配置');
            }
            // TLS 升级后必须重新 EHLO
            $this->smtpExchange($fp, 'EHLO ' . $helo);
        }

        // ---------- AUTH LOGIN ----------
        if ($this->user !== '') {
            $resp = $this->smtpExchange($fp, 'AUTH LOGIN');
            if ($this->smtpCode($resp) !== 334) {
                fclose($fp);
                return array('ok' => false, 'error' => 'AUTH LOGIN 失败: ' . trim($resp));
            }
            $resp = $this->smtpExchange($fp, base64_encode($this->user));
            if ($this->smtpCode($resp) !== 334) {
                fclose($fp);
                return array('ok' => false, 'error' => 'SMTP 用户名认证失败');
            }
            $resp = $this->smtpExchange($fp, base64_encode($this->pass));
            if ($this->smtpCode($resp) !== 235) {
                fclose($fp);
                return array('ok' => false, 'error' => 'SMTP 密码认证失败（用户名或密码错误）');
            }
        }

        // ---------- MAIL FROM / RCPT TO ----------
        $resp = $this->smtpExchange($fp, 'MAIL FROM:<' . $fromEmail . '>');
        if ($this->smtpCode($resp) !== 250) {
            fclose($fp);
            return array('ok' => false, 'error' => 'MAIL FROM 失败: ' . trim($resp));
        }
        $resp = $this->smtpExchange($fp, 'RCPT TO:<' . $to . '>');
        $rcptCode = $this->smtpCode($resp);
        if ($rcptCode !== 250 && $rcptCode !== 251) {
            fclose($fp);
            return array('ok' => false, 'error' => 'RCPT TO 失败: ' . trim($resp));
        }

        // ---------- DATA ----------
        $resp = $this->smtpExchange($fp, 'DATA');
        if ($this->smtpCode($resp) !== 354) {
            fclose($fp);
            return array('ok' => false, 'error' => 'DATA 指令失败: ' . trim($resp));
        }

        // 构建 RFC 2822 邮件头
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        if ($fromName !== '') {
            $encodedName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
            $fromHeader  = $encodedName . ' <' . $fromEmail . '>';
        } else {
            $fromHeader = $fromEmail;
        }

        $msgLines   = array();
        $msgLines[] = 'Date: ' . date('r');
        $msgLines[] = 'Message-ID: <' . uniqid('lp', true) . '@links-plus>';
        $msgLines[] = 'From: ' . $fromHeader;
        $msgLines[] = 'To: ' . $to;
        $msgLines[] = 'Subject: ' . $encodedSubject;
        $msgLines[] = 'MIME-Version: 1.0';
        $msgLines[] = 'Content-Type: text/html; charset=UTF-8';
        $msgLines[] = 'Content-Transfer-Encoding: base64';
        $msgLines[] = '';
        $msgLines[] = chunk_split(base64_encode((string)$htmlBody), 76, "\r\n");

        $rawMsg = implode("\r\n", $msgLines);
        // RFC 2821 §4.5.2：行首单独的 '.' 需转义为 '..'
        $rawMsg = preg_replace('/^\.$/m', '..', $rawMsg);
        fwrite($fp, $rawMsg . "\r\n.\r\n");

        // 读取发送结果响应
        $resp = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($line === false) {
                break;
            }
            $resp .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        $this->smtpExchange($fp, 'QUIT');
        fclose($fp);

        if ($this->smtpCode($resp) !== 250) {
            return array('ok' => false, 'error' => '邮件发送失败: ' . trim($resp));
        }
        return array('ok' => true, 'error' => '');
    }
}
