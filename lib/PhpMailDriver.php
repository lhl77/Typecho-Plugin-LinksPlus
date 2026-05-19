<?php

require_once dirname(__FILE__) . '/MailDriverInterface.php';

class Links_PhpMailDriver implements Links_MailDriverInterface
{
    /**
     * 防止邮件头注入
     */
    private function sanitizeHeaderValue($value)
    {
        $value = str_replace(array("\r", "\n"), '', (string)$value);
        return trim($value);
    }

    public function send($to, $subject, $htmlBody, array $options = array())
    {
        $to = $this->sanitizeHeaderValue($to);
        $subject = $this->sanitizeHeaderValue($subject);

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return array('ok' => false, 'error' => '收件人邮箱不合法');
        }
        if ($subject === '') {
            return array('ok' => false, 'error' => '邮件主题不能为空');
        }

        $fromEmail = isset($options['from_email']) ? $this->sanitizeHeaderValue($options['from_email']) : '';
        $fromName = isset($options['from_name']) ? $this->sanitizeHeaderValue($options['from_name']) : '';
        $replyTo = isset($options['reply_to']) ? $this->sanitizeHeaderValue($options['reply_to']) : '';

        if ($fromEmail !== '' && !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return array('ok' => false, 'error' => '发件邮箱不合法');
        }
        if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $replyTo = '';
        }

        $headers = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        if ($fromEmail !== '') {
            if ($fromName !== '') {
                $encodedName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
                $headers[] = 'From: ' . $encodedName . ' <' . $fromEmail . '>';
            } else {
                $headers[] = 'From: ' . $fromEmail;
            }
        }

        if ($replyTo !== '') {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $body = (string)$htmlBody;

        $ok = @mail($to, $encodedSubject, $body, implode("\r\n", $headers));
        if (!$ok) {
            return array('ok' => false, 'error' => 'PHP mail() 发送失败');
        }

        return array('ok' => true, 'error' => '');
    }
}
