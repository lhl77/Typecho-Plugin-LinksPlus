<?php

/**
 * 非邮件通知通道调度器
 *
 * 通道机制说明：
 *   - 邮件（email）：可同时发给管理员和申请者，在 Action.php 的 sendTemplatedMail() 中处理
 *   - Server酱：仅发给管理员
 *   - Telegram：仅发给管理员（优先复用 TelegramNotice 插件的 Bot Token / Chat ID）
 */
class Links_NotifyChannel
{
    /**
     * 通过 Server酱（Turbo v3）推送管理员通知
     *
     * @param string $sendKey  Server酱 SendKey
     * @param string $title    消息标题（最多 32 字）
     * @param string $body     消息正文（支持 Markdown，可为空）
     * @return array{ok:bool,error:string}
     */
    public static function sendServerChan($sendKey, $title, $body = '')
    {
        $sendKey = trim((string)$sendKey);
        if ($sendKey === '') {
            return array('ok' => false, 'error' => 'Server酱 SendKey 未配置');
        }

        if (function_exists('mb_substr')) {
            $title = mb_substr(trim((string)$title), 0, 32, 'UTF-8');
        } else {
            $title = substr(trim((string)$title), 0, 96); // 保守截断
        }
        if ($title === '') {
            return array('ok' => false, 'error' => '消息标题不能为空');
        }

        // Server酱 Turbo API
        $url     = 'https://sctapi.ftqq.com/' . rawurlencode($sendKey) . '.send';
        $payload = http_build_query(array(
            'title' => $title,
            'desp'  => (string)$body,
        ));

        $ctx = stream_context_create(array(
            'http' => array(
                'method'        => 'POST',
                'header'        => "Content-Type: application/x-www-form-urlencoded\r\n"
                                 . "Content-Length: " . strlen($payload) . "\r\n",
                'content'       => $payload,
                'timeout'       => 10,
                'ignore_errors' => true,
            ),
            'ssl' => array(
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ),
        ));

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            return array('ok' => false, 'error' => 'Server酱请求失败（网络错误）');
        }

        $json = @json_decode($response, true);
        // Server酱 Turbo v3 成功时返回 {"code":0}
        if (is_array($json) && isset($json['code']) && (int)$json['code'] === 0) {
            return array('ok' => true, 'error' => '');
        }

        $errMsg = (is_array($json) && isset($json['message'])) ? (string)$json['message'] : (string)$response;
        return array('ok' => false, 'error' => 'Server酱返回错误: ' . $errMsg);
    }

    /**
     * 通过 Telegram Bot API 推送管理员通知
     *
     * 优先使用 $token / $chatId 参数；若为空则尝试从 TelegramNotice 插件读取配置。
     *
     * @param string $token   Bot Token（可为空，自动从 TelegramNotice 插件读取）
     * @param string $chatId  Chat ID（可为空，自动从 TelegramNotice 插件读取）
     * @param string $text    消息文本（支持 HTML 格式）
     * @return array{ok:bool,error:string}
     */
    public static function sendTelegram($token, $chatId, $text)
    {
        $token  = trim((string)$token);
        $chatId = trim((string)$chatId);

        // 尝试从 TelegramNotice 插件自动读取配置
        if (($token === '' || $chatId === '') && class_exists('TelegramNotice_Plugin')) {
            try {
                $tnOptions = Typecho_Widget::widget('Widget_Options')->plugin('TelegramNotice');
                if ($token === '') {
                    // 兼容 token / bot_token 两种字段名
                    if (isset($tnOptions->token) && trim((string)$tnOptions->token) !== '') {
                        $token = trim((string)$tnOptions->token);
                    } elseif (isset($tnOptions->bot_token) && trim((string)$tnOptions->bot_token) !== '') {
                        $token = trim((string)$tnOptions->bot_token);
                    }
                }
                if ($chatId === '') {
                    // 兼容 chatId / chat_id 两种字段名
                    if (isset($tnOptions->chatId) && trim((string)$tnOptions->chatId) !== '') {
                        $chatId = trim((string)$tnOptions->chatId);
                    } elseif (isset($tnOptions->chat_id) && trim((string)$tnOptions->chat_id) !== '') {
                        $chatId = trim((string)$tnOptions->chat_id);
                    }
                }
            } catch (Exception $e) {
                // 插件未激活或配置读取异常，静默跳过
            }
        }

        if ($token === '') {
            return array('ok' => false, 'error' => 'Telegram Bot Token 未配置');
        }
        if ($chatId === '') {
            return array('ok' => false, 'error' => 'Telegram Chat ID 未配置');
        }

        $text = trim((string)$text);
        if ($text === '') {
            return array('ok' => false, 'error' => '消息内容不能为空');
        }

        $url     = 'https://api.telegram.org/bot' . rawurlencode($token) . '/sendMessage';
        $payload = json_encode(array(
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ), JSON_UNESCAPED_UNICODE);

        $ctx = stream_context_create(array(
            'http' => array(
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\n"
                                 . "Content-Length: " . strlen($payload) . "\r\n",
                'content'       => $payload,
                'timeout'       => 10,
                'ignore_errors' => true,
            ),
            'ssl' => array(
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ),
        ));

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            return array('ok' => false, 'error' => 'Telegram 请求失败（网络错误，请检查服务器是否可访问 Telegram）');
        }

        $json = @json_decode($response, true);
        if (is_array($json) && !empty($json['ok'])) {
            return array('ok' => true, 'error' => '');
        }

        $errMsg = (is_array($json) && isset($json['description'])) ? (string)$json['description'] : (string)$response;
        return array('ok' => false, 'error' => 'Telegram API 返回错误: ' . $errMsg);
    }
}
