<?php

require_once dirname(__FILE__) . '/lib/Mailer.php';

class Links_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $db;
    private $options;
    private $prefix;

    /**
     * 将配置值统一转成数组
     *
     * @param mixed $value
     * @return array<int,string>
     */
    private function normalizeOptionArray($value)
    {
        if (is_array($value)) {
            $result = array();
            foreach ($value as $item) {
                $item = trim((string)$item);
                if ($item !== '') {
                    $result[] = $item;
                }
            }
            return array_values(array_unique($result));
        }

        $value = trim((string)$value);
        return $value === '' ? array() : array($value);
    }

    /**
     * 获取操作目标 lids（兼容批量/单条）
     *
     * @return array<int,int>
     */
    private function getTargetLids()
    {
        $lids = $this->request->filter('int')->getArray('lid');
        if (!$lids || !is_array($lids)) {
            $single = (int)$this->request->get('lid');
            $lids = $single > 0 ? array($single) : array();
        }

        $normalized = array();
        foreach ($lids as $lid) {
            $lid = (int)$lid;
            if ($lid > 0) {
                $normalized[] = $lid;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * 文本清洗：去首尾空白、去控制字符、按长度截断
     */
    private function sanitizeText($value, $maxLen)
    {
        $value = trim((string)$value);
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        if ($maxLen > 0 && function_exists('mb_substr')) {
            $value = mb_substr($value, 0, (int)$maxLen, 'UTF-8');
        } elseif ($maxLen > 0) {
            $value = substr($value, 0, (int)$maxLen);
        }
        return trim((string)$value);
    }

    /**
     * URL 校验与标准化（仅允许 http/https）
     */
    private function sanitizeUrl($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return '';
        }

        $parts = @parse_url($value);
        $scheme = isset($parts['scheme']) ? strtolower((string)$parts['scheme']) : '';
        if (!in_array($scheme, array('http', 'https'), true)) {
            return '';
        }

        return $value;
    }

    /**
     * 友链申请频控：同 IP 在窗口期内限制提交次数
     */
    private function hitApplyRateLimit($ip, $maxCount = 8, $windowSeconds = 600)
    {
        $ip = trim((string)$ip);
        if ($ip === '') {
            return false;
        }

        $file = sys_get_temp_dir() . '/links_plus_apply_rate_' . md5($ip) . '.json';
        $now = time();
        $records = array();

        $fp = @fopen($file, 'c+');
        if (!$fp) {
            // 频控文件不可用时，默认放行，避免误伤正常提交
            return false;
        }

        if (!@flock($fp, LOCK_EX)) {
            @fclose($fp);
            return false;
        }

        $raw = stream_get_contents($fp);
        if ($raw) {
            $decoded = @json_decode($raw, true);
            if (is_array($decoded)) {
                $records = $decoded;
            }
        }

        $valid = array();
        foreach ($records as $ts) {
            $ts = (int)$ts;
            if ($ts > 0 && ($now - $ts) <= (int)$windowSeconds) {
                $valid[] = $ts;
            }
        }

        $limited = count($valid) >= (int)$maxCount;
        if (!$limited) {
            $valid[] = $now;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($valid));
        fflush($fp);
        @flock($fp, LOCK_UN);
        @fclose($fp);

        return $limited;
    }

    /**
     * 获取当前请求安全回跳地址（仅允许本站域名）
     */
    private function getSafeApplyReturnUrl()
    {
        $fallback = Typecho_Common::url('/', $this->options->siteUrl);
        $ref = isset($_SERVER['HTTP_REFERER']) ? trim((string)$_SERVER['HTTP_REFERER']) : '';
        if ($ref === '') {
            return $fallback;
        }

        $refParts = @parse_url($ref);
        $siteParts = @parse_url((string)$this->options->siteUrl);
        if (!is_array($refParts) || !is_array($siteParts)) {
            return $fallback;
        }

        $scheme = isset($refParts['scheme']) ? strtolower((string)$refParts['scheme']) : '';
        $refHost = isset($refParts['host']) ? strtolower((string)$refParts['host']) : '';
        $siteHost = isset($siteParts['host']) ? strtolower((string)$siteParts['host']) : '';
        if (!in_array($scheme, array('http', 'https'), true) || $refHost === '' || $siteHost === '' || $refHost !== $siteHost) {
            return $fallback;
        }

        return $ref;
    }

    /**
     * 向 URL 追加参数
     *
     * @param string $url
     * @param array<string,string> $params
     */
    private function appendUrlParams($url, array $params)
    {
        $parts = @parse_url($url);
        if (!is_array($parts)) {
            return $url;
        }

        $query = array();
        if (!empty($parts['query'])) {
            parse_str((string)$parts['query'], $query);
        }
        foreach ($params as $k => $v) {
            $query[$k] = $v;
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $pass = ($user || $pass) ? $pass . '@' : '';
        $path = isset($parts['path']) ? $parts['path'] : '';
        $q = http_build_query($query);
        $frag = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $user . $pass . $host . $port . $path . ($q !== '' ? '?' . $q : '') . $frag;
    }

    /**
     * 判断当前请求是否为 AJAX（XMLHttpRequest）
     */
    private function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower(trim((string)$_SERVER['HTTP_X_REQUESTED_WITH'])) === 'xmlhttprequest';
    }

    /**
     * 前台申请回跳并带状态码；AJAX 请求时改为返回 JSON
     */
    private function redirectApplyStatus($status)
    {
        if ($this->isAjaxRequest()) {
            $messages = array(
                'ok'           => _t('申请已提交，正在等待管理员审核。'),
                'duplicate'    => _t('该友链地址已存在，请勿重复提交。'),
                'rate_limited' => _t('提交过于频繁，请稍后再试。'),
                'invalid'      => _t('提交失败：参数不合法，请检查后重试。'),
                'token'        => _t('提交失败：表单校验未通过，请刷新页面后重试。'),
                'server_error' => _t('提交失败：服务器内部错误，请稍后再试。'),
            );
            $msg = isset($messages[$status]) ? $messages[$status] : _t('未知错误');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'status'  => (string)$status,
                'message' => (string)$msg,
            ), JSON_UNESCAPED_UNICODE);
            exit;
        }

        $url = $this->appendUrlParams($this->getSafeApplyReturnUrl(), array(
            'links_apply_status' => (string)$status,
        ));
        $this->response->redirect($url);
    }

    /**
     * 构建 MD3 风格 HTML 邮件外壳
     *
     * @param string $contentHtml 内容 HTML 片段
     * @param string $siteName    站点名称
     * @param string $siteUrl     站点地址
     * @return string 完整 HTML 邮件字符串
     */
    private static function buildEmailHtml($contentHtml, $siteName, $siteUrl)
    {
        $sn = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
        $su = htmlspecialchars($siteUrl,  ENT_QUOTES, 'UTF-8');
        return '<!DOCTYPE html>'
            . '<html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:0;background:#f0f4f8;-webkit-text-size-adjust:100%">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"'
            . ' style="background:#f0f4f8;padding:32px 16px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',sans-serif">'
            . '<tr><td align="center">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px">'
            . '<tr><td style="background:linear-gradient(135deg,#0061a4 0%,#4a7cc9 100%);border-radius:16px 16px 0 0;padding:24px 32px">'
            . '<div style="color:#ffffff;font-size:20px;font-weight:700;line-height:1.3">' . $sn . '</div>'
            . '<div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:4px;letter-spacing:.3px">友链通知</div>'
            . '</td></tr>'
            . '<tr><td style="background:#ffffff;padding:28px 32px;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb">'
            . $contentHtml
            . '</td></tr>'
            . '<tr><td style="background:#f5f7fa;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 16px 16px;padding:14px 32px;text-align:center">'
            . '<div style="font-size:11px;color:#9ca3af">'
            . '此邮件由 <a href="' . $su . '" style="color:#0061a4;text-decoration:none">' . $sn . '</a> 自动发送 &middot; Powered by Links+'
            . '</div>'
            . '</td></tr>'
            . '</table></td></tr></table>'
            . '</body></html>';
    }

    /**
     * 发送模板邮件
     *
     * @param string $to
     * @param string $subjectTpl
     * @param string $bodyTpl
     * @param array<string,string> $vars
     * @param string $replyTo
     */
    private function sendTemplatedMail($to, $subjectTpl, $bodyTpl, array $vars, $replyTo = '')
    {
        $settings = $this->options->plugin('Links');
        $enabled = $this->normalizeOptionArray(isset($settings->apply_mail_enabled) ? $settings->apply_mail_enabled : array());
        if (!in_array('enabled', $enabled, true)) {
            return false;
        }

        $to = trim((string)$to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $safeVars = array();
        foreach ($vars as $k => $v) {
            $safeVars[$k] = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        }

        $subject = Links_Plugin::renderTemplateString((string)$subjectTpl, $safeVars);
        $body = Links_Plugin::renderTemplateString((string)$bodyTpl, $safeVars);
        if (trim($subject) === '' || trim($body) === '') {
            return false;
        }

        $driver = isset($settings->apply_mail_driver) ? trim((string)$settings->apply_mail_driver) : 'phpmail';
        $adminTo = isset($settings->apply_mail_admin_to) ? trim((string)$settings->apply_mail_admin_to) : '';
        $fromEmail = isset($settings->apply_mail_from_email) ? trim((string)$settings->apply_mail_from_email) : '';
        if ($fromEmail === '' && $adminTo !== '' && filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = $adminTo;
        }
        $fromName = isset($settings->apply_mail_from_name) ? trim((string)$settings->apply_mail_from_name) : 'Links Plus';

        // SMTP 配置（仅 smtp 驱动使用）
        $smtpHost   = isset($settings->apply_smtp_host)   ? trim((string)$settings->apply_smtp_host)   : '';
        $smtpPort   = isset($settings->apply_smtp_port)   ? (int)$settings->apply_smtp_port             : 587;
        $smtpSecure = isset($settings->apply_smtp_secure) ? trim((string)$settings->apply_smtp_secure) : 'tls';
        $smtpUser   = isset($settings->apply_smtp_user)   ? trim((string)$settings->apply_smtp_user)   : '';
        $smtpPass   = isset($settings->apply_smtp_pass)   ? trim((string)$settings->apply_smtp_pass)   : '';

        // 检测 HTML 模板或纯文本，包装为完整 MD3 风格 HTML 邮件
        $bodyTrimmed = ltrim($body);
        $isHtml = isset($bodyTrimmed[0]) && $bodyTrimmed[0] === '<';
        $contentHtml = $isHtml
            ? $body
            : '<p style="margin:0;font-size:14px;line-height:1.8;color:#374151">' . nl2br($body) . '</p>';
        $fullHtml = self::buildEmailHtml($contentHtml, (string)$this->options->title, (string)$this->options->siteUrl);

        $result = Links_Mailer::send(
            $driver,
            $to,
            $subject,
            $fullHtml,
            array(
                'from_email'  => $fromEmail,
                'from_name'   => $fromName,
                'reply_to'    => trim((string)$replyTo),
                'smtp_host'   => $smtpHost,
                'smtp_port'   => $smtpPort,
                'smtp_secure' => $smtpSecure,
                'smtp_user'   => $smtpUser,
                'smtp_pass'   => $smtpPass,
            )
        );

        return isset($result['ok']) ? (bool)$result['ok'] : false;
    }

    /**
     * 新申请提醒管理员
     *
     * @param array<string,string> $link
     */
    private function notifyAdminForApply(array $link)
    {
        $settings = $this->options->plugin('Links');

        $subjectTpl = isset($settings->apply_mail_tpl_admin_subject) && trim((string)$settings->apply_mail_tpl_admin_subject) !== ''
            ? (string)$settings->apply_mail_tpl_admin_subject
            : '【{{site_name}}】收到新的友链申请：{{name}}';
        $bodyTpl = isset($settings->apply_mail_tpl_admin_body) && trim((string)$settings->apply_mail_tpl_admin_body) !== ''
            ? (string)$settings->apply_mail_tpl_admin_body
            : '<p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6">收到一条新的友链申请，请前往后台审核。</p><div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px"><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;width:90px;border-bottom:1px solid #e5e7eb">站点名称</td><td style="padding:10px 16px;background:#f8fafc;color:#111827;font-weight:600;border-bottom:1px solid #e5e7eb">{{name}}</td></tr><tr><td style="padding:10px 16px;color:#6b7280;border-bottom:1px solid #e5e7eb">站点地址</td><td style="padding:10px 16px;border-bottom:1px solid #e5e7eb"><a href="{{url}}" style="color:#0061a4">{{url}}</a></td></tr><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;border-bottom:1px solid #e5e7eb">描述</td><td style="padding:10px 16px;background:#f8fafc;color:#374151;border-bottom:1px solid #e5e7eb">{{description}}</td></tr><tr><td style="padding:10px 16px;color:#6b7280;border-bottom:1px solid #e5e7eb">邮箱</td><td style="padding:10px 16px;color:#374151;border-bottom:1px solid #e5e7eb">{{email}}</td></tr><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280">分类</td><td style="padding:10px 16px;background:#f8fafc;color:#374151">{{sort}}</td></tr></table></div><div style="margin-top:20px;text-align:center"><a href="{{manage_url}}" style="display:inline-block;padding:10px 24px;background:#0061a4;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600">前往审核</a></div>';

        $vars = array(
            'site_name'   => (string)$this->options->title,
            'site_url'    => (string)$this->options->siteUrl,
            'manage_url'  => Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', (string)$this->options->adminUrl),
            'name'        => isset($link['name'])        ? $link['name']        : '',
            'url'         => isset($link['url'])         ? $link['url']         : '',
            'image'       => isset($link['image'])       ? $link['image']       : '',
            'sort'        => isset($link['sort'])        ? $link['sort']        : '',
            'description' => isset($link['description']) ? $link['description'] : '',
            'email'       => isset($link['email'])       ? $link['email']       : '',
            'user'        => isset($link['user'])        ? $link['user']        : '',
            'reason'      => '',
        );

        // 邮件通知管理员
        $adminEmail = isset($settings->apply_mail_admin_to) ? trim((string)$settings->apply_mail_admin_to) : '';
        $replyTo = isset($link['email']) ? trim((string)$link['email']) : '';
        if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->sendTemplatedMail($adminEmail, $subjectTpl, $bodyTpl, $vars, $replyTo);
        }

        return true;
    }

    /**
     * 通知申请者审核结果
     *
     * @param array<string,mixed> $link
     */
    private function notifyApplicantForAudit(array $link, $approved, $reason = '')
    {
        $applicant = isset($link['email']) ? trim((string)$link['email']) : '';
        if ($applicant === '' || !filter_var($applicant, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $settings = $this->options->plugin('Links');
        if ($approved) {
            $subjectTpl = isset($settings->apply_mail_tpl_approved_subject) && trim((string)$settings->apply_mail_tpl_approved_subject) !== ''
                ? (string)$settings->apply_mail_tpl_approved_subject
                : '【{{site_name}}】你的友链申请已通过';
            $bodyTpl = isset($settings->apply_mail_tpl_approved_body) && trim((string)$settings->apply_mail_tpl_approved_body) !== ''
                ? (string)$settings->apply_mail_tpl_approved_body
                : '<div style="text-align:center;padding-bottom:20px"><div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:#e8f3ff;font-size:26px">✓</div><p style="margin:10px 0 0;font-size:20px;font-weight:700;color:#0061a4">申请已通过！</p></div><p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.7">Hi <strong>{{name}}</strong>，你提交至 <strong>{{site_name}}</strong> 的友链申请已审核通过，感谢你的申请！</p><div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px"><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;width:90px">友链地址</td><td style="padding:10px 16px;background:#f8fafc"><a href="{{url}}" style="color:#0061a4;font-weight:600">{{url}}</a></td></tr></table></div><p style="margin:0;font-size:14px;color:#6b7280">🌐 欢迎互链，期待与你交流！</p>';
        } else {
            $subjectTpl = isset($settings->apply_mail_tpl_rejected_subject) && trim((string)$settings->apply_mail_tpl_rejected_subject) !== ''
                ? (string)$settings->apply_mail_tpl_rejected_subject
                : '【{{site_name}}】你的友链申请未通过';
            $bodyTpl = isset($settings->apply_mail_tpl_rejected_body) && trim((string)$settings->apply_mail_tpl_rejected_body) !== ''
                ? (string)$settings->apply_mail_tpl_rejected_body
                : '<p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.7">Hi <strong>{{name}}</strong>，很遗憾，你提交至 <strong>{{site_name}}</strong> 的友链申请本次未能通过审核。</p><div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px"><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;width:90px;border-bottom:1px solid #e5e7eb">友链地址</td><td style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #e5e7eb"><a href="{{url}}" style="color:#0061a4">{{url}}</a></td></tr><tr><td style="padding:10px 16px;color:#6b7280">驳回原因</td><td style="padding:10px 16px;color:#374151">{{reason}}</td></tr></table></div><p style="margin:0;font-size:13px;color:#9ca3af">如有疑问，欢迎直接回复此邮件与我们联系。</p>';
        }

        $vars = array(
            'site_name' => (string)$this->options->title,
            'site_url' => (string)$this->options->siteUrl,
            'name' => isset($link['name']) ? (string)$link['name'] : '',
            'url' => isset($link['url']) ? (string)$link['url'] : '',
            'image' => isset($link['image']) ? (string)$link['image'] : '',
            'sort' => isset($link['sort']) ? (string)$link['sort'] : '',
            'description' => isset($link['description']) ? (string)$link['description'] : '',
            'email' => $applicant,
            'user' => isset($link['user']) ? (string)$link['user'] : '',
            'reason' => $reason !== '' ? $reason : '不符合本站友链收录规则',
        );

        return $this->sendTemplatedMail($applicant, $subjectTpl, $bodyTpl, $vars, '');
    }

    public function insertLink()
    {
        if (Links_Plugin::form('insert')->validate()) {
            $this->response->goBack();
        }
        /** 取出数据 */
        $link = $this->request->from('email', 'image', 'url', 'state');

        /** 过滤XSS */
        $link['name'] = $this->request->filter('xss')->name;
        $link['sort'] = $this->request->filter('xss')->sort;
        $link['description'] = $this->request->filter('xss')->description;
        $link['user'] = $this->request->filter('xss')->user;
        $link['order'] = $this->db->fetchObject($this->db->select(array('MAX(order)' => 'maxOrder'))->from($this->prefix . 'links'))->maxOrder + 1;

        /** 插入数据 */
        $link_lid = $this->db->query($this->db->insert($this->prefix . 'links')->rows($link));

        /** 设置高亮 */
        $this->widget('Widget_Notice')->highlight('link-' . $link_lid);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t(
            '友链 <a href="%s">%s</a> 已经被增加',
            $link['url'],
            $link['name']
        ), null, 'success');

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }


    public function updateLink()
    {
        if (Links_Plugin::form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $link = $this->request->from('email', 'image', 'url', 'state');
        $link_lid = $this->request->from('lid');

        /** 过滤XSS */
        $link['name'] = $this->request->filter('xss')->name;
        $link['sort'] = $this->request->filter('xss')->sort;
        $link['description'] = $this->request->filter('xss')->description;
        $link['user'] = $this->request->filter('xss')->user;

        /** 更新数据 */
        $this->db->query($this->db->update($this->prefix . 'links')->rows($link)->where('lid = ?', $link_lid));

        /** 设置高亮 */
        $this->widget('Widget_Notice')->highlight('link-' . $link_lid);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t(
            '友链 <a href="%s">%s</a> 已经被更新',
            $link['url'],
            $link['name']
        ), null, 'success');

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function deleteLink()
    {
        $lids = $this->getTargetLids();
        $deleteCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($this->db->query($this->db->delete($this->prefix . 'links')->where('lid = ?', $lid))) {
                    $deleteCount++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            $deleteCount > 0 ? _t('友链已经删除') : _t('没有友链被删除'),
            null,
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function enableLink()
    {
        $lids = $this->getTargetLids();
        $enableCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($this->db->query($this->db->update($this->prefix . 'links')->rows(array('state' => '1'))->where('lid = ?', $lid))) {
                    $enableCount++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            $enableCount > 0 ? _t('友链已经启用') : _t('没有友链被启用'),
            null,
            $enableCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function prohibitLink()
    {
        $lids = $this->getTargetLids();
        $prohibitCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($this->db->query($this->db->update($this->prefix . 'links')->rows(array('state' => '0'))->where('lid = ?', $lid))) {
                    $prohibitCount++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            $prohibitCount > 0 ? _t('友链已经禁用') : _t('没有友链被禁用'),
            null,
            $prohibitCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }

    /**
     * 审核通过（state: 2 -> 1），并通知申请者
     */
    public function approveLink()
    {
        $lids = $this->getTargetLids();
        $approvedCount = 0;
        $notifyCount = 0;

        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                $row = $this->db->fetchRow(
                    $this->db->select()->from($this->prefix . 'links')->where('lid = ?', $lid)->limit(1)
                );
                if (!$row) {
                    continue;
                }

                $prevState = isset($row['state']) ? (int)$row['state'] : 0;
                $ok = $this->db->query(
                    $this->db->update($this->prefix . 'links')->rows(array('state' => '1'))->where('lid = ?', $lid)
                );
                if ($ok) {
                    $approvedCount++;
                    if ($prevState === 2 && $this->notifyApplicantForAudit($row, true, '')) {
                        $notifyCount++;
                    }
                }
            }
        }

        $msg = $approvedCount > 0
            ? _t('已通过 %d 条友链申请，通知发送 %d 封', $approvedCount, $notifyCount)
            : _t('没有待审核友链被通过');
        $this->widget('Widget_Notice')->set($msg, null, $approvedCount > 0 ? 'success' : 'notice');

        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }

    /**
     * 审核驳回（state: 2 -> 0），并通知申请者
     */
    public function rejectLink()
    {
        $lids = $this->getTargetLids();
        $reason = $this->sanitizeText($this->request->filter('xss')->reason, 120);
        if ($reason === '') {
            $reason = '不符合本站友链收录规则';
        }

        $rejectedCount = 0;
        $notifyCount = 0;

        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                $row = $this->db->fetchRow(
                    $this->db->select()->from($this->prefix . 'links')->where('lid = ?', $lid)->limit(1)
                );
                if (!$row) {
                    continue;
                }

                $prevState = isset($row['state']) ? (int)$row['state'] : 0;
                $ok = $this->db->query(
                    $this->db->update($this->prefix . 'links')->rows(array('state' => '0'))->where('lid = ?', $lid)
                );
                if ($ok) {
                    $rejectedCount++;
                    if ($prevState === 2 && $this->notifyApplicantForAudit($row, false, $reason)) {
                        $notifyCount++;
                    }
                }
            }
        }

        $msg = $rejectedCount > 0
            ? _t('已驳回 %d 条友链申请，通知发送 %d 封', $rejectedCount, $notifyCount)
            : _t('没有待审核友链被驳回');
        $this->widget('Widget_Notice')->set($msg, null, $rejectedCount > 0 ? 'success' : 'notice');

        $this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', $this->options->adminUrl));
    }

    /**
     * 前台提交友链申请（公开入口）
     */
    public function submitApplyLink()
    {
        if (!$this->request->isPost()) {
            $this->redirectApplyStatus('invalid');
            return;
        }

        // 蜜罐：命中后伪装成功，降低机器人探测有效性
        $honeypot = trim((string)$this->request->get('lp_contact'));
        if ($honeypot !== '') {
            $this->redirectApplyStatus('ok');
            return;
        }

        $token = trim((string)$this->request->get('apply_token'));
        if (!Links_Plugin::verifyApplyToken($token)) {
            $this->redirectApplyStatus('token');
            return;
        }

        $clientIp = Links_Plugin::getClientIp();
        if ($this->hitApplyRateLimit($clientIp)) {
            $this->redirectApplyStatus('rate_limited');
            return;
        }

        $settings = $this->options->plugin('Links');
        $enabled = $this->normalizeOptionArray(isset($settings->enable_link_apply) ? $settings->enable_link_apply : array());
        if (!in_array('enabled', $enabled, true)) {
            $this->redirectApplyStatus('invalid');
            return;
        }

        $requireDescription = in_array('required', $this->normalizeOptionArray(isset($settings->apply_require_description) ? $settings->apply_require_description : array()), true);
        $requireEmail = in_array('required', $this->normalizeOptionArray(isset($settings->apply_require_email) ? $settings->apply_require_email : array()), true);
        $requireUser = in_array('required', $this->normalizeOptionArray(isset($settings->apply_require_user) ? $settings->apply_require_user : array()), true);

        $name = $this->sanitizeText($this->request->filter('xss')->name, 50);
        $url = $this->sanitizeUrl($this->request->url);
        $image = $this->sanitizeUrl($this->request->image);
        $description = $this->sanitizeText($this->request->filter('xss')->description, 200);
        $email = $this->sanitizeText($this->request->filter('xss')->email, 50);
        $user = $this->sanitizeText($this->request->filter('xss')->user, 200);

        $sort = $this->sanitizeText(isset($settings->apply_default_sort) ? $settings->apply_default_sort : '', 50);
        if ($sort === '') {
            $sort = '友链申请';
        }

        if ($name === '' || $url === '' || $image === '') {
            $this->redirectApplyStatus('invalid');
            return;
        }
        if ($requireDescription && $description === '') {
            $this->redirectApplyStatus('invalid');
            return;
        }
        if ($requireEmail && $email === '') {
            $this->redirectApplyStatus('invalid');
            return;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectApplyStatus('invalid');
            return;
        }
        if ($requireUser && $user === '') {
            $this->redirectApplyStatus('invalid');
            return;
        }

        $exists = $this->db->fetchRow(
            $this->db->select()->from($this->prefix . 'links')->where('url = ?', $url)->limit(1)
        );
        if ($exists) {
            $this->redirectApplyStatus('duplicate');
            return;
        }

        $maxOrderObj = $this->db->fetchObject(
            $this->db->select(array('MAX(order)' => 'maxOrder'))->from($this->prefix . 'links')
        );
        $nextOrder = ($maxOrderObj && isset($maxOrderObj->maxOrder)) ? ((int)$maxOrderObj->maxOrder + 1) : 1;

        $row = array(
            'name' => $name,
            'url' => $url,
            'sort' => $sort,
            'email' => $email !== '' ? $email : null,
            'image' => $image,
            'description' => $description !== '' ? $description : null,
            'user' => $user !== '' ? $user : null,
            'state' => '2',
            'order' => $nextOrder,
        );

        try {
            $this->db->query($this->db->insert($this->prefix . 'links')->rows($row));
        } catch (Exception $e) {
            $this->redirectApplyStatus('server_error');
            return;
        }

        $this->notifyAdminForApply(array(
            'name' => $name,
            'url' => $url,
            'sort' => $sort,
            'email' => $email,
            'image' => $image,
            'description' => $description,
            'user' => $user,
        ));

        $this->redirectApplyStatus('ok');
    }

    public function sortLink()
    {
        $links = $this->request->filter('int')->getArray('lid');
        if ($links && is_array($links)) {
            foreach ($links as $sort => $lid) {
                $this->db->query($this->db->update($this->prefix . 'links')->rows(array('order' => $sort + 1))->where('lid = ?', $lid));
            }
        }
    }

    public function emailLogo()
    {
        /* 邮箱头像解API接口 by 懵仙兔兔 */
        $type = $this->request->type;
        $email = $this->request->email;

        if ($email == null || $email == '') {
            $this->response->throwJson('请提交邮箱链接 [email=abc@abc.com]');
            exit;
        } else if ($type == null || $type == '' || ($type != 'txt' && $type != 'json')) {
            $this->response->throwJson('请提交type类型 [type=txt, type=json]');
            exit;
        } else {
            $f = str_replace('@qq.com', '', $email);
            $email = $f . '@qq.com';
            if (is_numeric($f) && strlen($f) < 11 && strlen($f) > 4) {
                stream_context_set_default([
                    'ssl' => [
                        'verify_host' => false,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
                $geturl = 'https://s.p.qq.com/pub/get_face?img_type=3&uin=' . $f;
                $headers = get_headers($geturl, TRUE);
                if ($headers) {
                    $g = $headers['Location'];
                    $g = str_replace("http:", "https:", $g);
                } else {
                    $g = 'https://q.qlogo.cn/g?b=qq&nk=' . $f . '&s=100';
                }
            } else {
                $g = 'https://cdn.helingqi.com/wavatar/' . md5($email) . '?d=mm';
            }
            $r = array('url' => $g);
            if ($type == 'txt') {
                $this->response->throwJson($g);
                exit;
            } else if ($type == 'json') {
                $this->response->throwJson(json_encode($r));
                exit;
            }
        }
    }

    /**
     * 后端检测友链可用性（避免前端 CORS 影响）：
     * - 仅管理员可调用（action() 已授权）
     * - 返回 JSON：{ ok: bool, status: int, finalUrl?: string, error?: string }
     */
    public function checkLink()
    {
        $url = trim((string)$this->request->get('url'));
        if ($url === '') {
            $this->response->throwJson(array(
                'ok' => false,
                'status' => 0,
                'finalUrl' => null,
                'error' => '缺少 url'
            ));
            return;
        }

        // 仅允许 http/https
        if (!preg_match('#^https?://#i', $url)) {
            $this->response->throwJson(array(
                'ok' => false,
                'status' => 0,
                'finalUrl' => null,
                'error' => '仅支持 http/https'
            ));
            return;
        }

        // 防御：禁止访问内网/本地地址（简单版）
        $parts = @parse_url($url);
        $host = isset($parts['host']) ? $parts['host'] : '';
        if ($host === '') {
            $this->response->throwJson(array(
                'ok' => false,
                'status' => 0,
                'finalUrl' => null,
                'error' => 'URL 不合法'
            ));
            return;
        }
        $ip = @gethostbyname($host);
        // DNS 解析失败时，gethostbyname 会原样返回 host
        if ($ip === $host) {
            $this->response->throwJson(array(
                'ok' => false,
                'status' => 0,
                'finalUrl' => null,
                'error' => '无法解析域名'
            ));
            return;
        }
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            // 10.0.0.0/8, 127.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16
            $isPrivate = false;
            if (preg_match('#^(10\.|127\.|192\.168\.)#', $ip)) $isPrivate = true;
            if (preg_match('#^172\.(1[6-9]|2\d|3[0-1])\.#', $ip)) $isPrivate = true;
            if ($isPrivate) {
                $this->response->throwJson(array(
                    'ok' => false,
                    'status' => 0,
                    'finalUrl' => null,
                    'error' => '禁止访问内网地址'
                ));
                return;
            }
        }

        $timeout = 6;
        $status = 0;
        $finalUrl = null;
        $error = null;

        /**
         * 兜底：网络可达性探测（“ping 域名”的等价实现）
         * - 真实 ICMP ping 往往需要系统权限/被禁；因此优先用 TCP connect(80/443) 判断主机是否可达
         * - 仅在 HTTP 探测全部失败时触发，用于把“站点拦截/SSL/应用层错误”和“主机根本不可达”区分开
         */
        $netProbe = function ($host, $scheme, $timeoutSec) {
            $timeoutSec = max(1, (int)$timeoutSec);
            $ports = array();
            if (strtolower((string)$scheme) === 'https') {
                $ports = array(443, 80);
            } else {
                $ports = array(80, 443);
            }

            $lastErr = null;
            foreach ($ports as $port) {
                $errno = 0;
                $errstr = '';
                // IP 已在前面 gethostbyname 解析过，这里直接连 IP，避免再次被 DNS 影响
                $fp = @fsockopen($host, $port, $errno, $errstr, $timeoutSec);
                if ($fp) {
                    @fclose($fp);
                    return array('ok' => true, 'via' => 'tcp', 'port' => $port);
                }
                $lastErr = $errstr ?: ($errno ? ('errno ' . $errno) : null);
            }

            // 可选：尝试系统 ping（不保证可用）
            $pingCmd = null;
            if (function_exists('shell_exec')) {
                $hasPing = @shell_exec('command -v ping 2>/dev/null');
                if (is_string($hasPing) && trim($hasPing) !== '') {
                    $pingCmd = 'ping -c 1 -W ' . (int)$timeoutSec . ' ' . escapeshellarg($host) . ' 2>&1';
                    $out = @shell_exec($pingCmd);
                    if (is_string($out) && preg_match('/\b1\s+received\b|\b1\s+packets\s+received\b/i', $out)) {
                        return array('ok' => true, 'via' => 'icmp');
                    }
                    if (is_string($out) && trim($out) !== '') {
                        $lastErr = trim($out);
                    }
                }
            }

            return array('ok' => false, 'via' => 'tcp', 'error' => $lastErr ?: '主机不可达');
        };

    // 只按用户填写的 URL 协议探测，不做 http/https 降级（避免 http 301 兜底误导）
    $candidateUrls = array($url);

        // 优先使用 cURL
        if (function_exists('curl_init')) {
            $tryError = null;

            // 更像浏览器的基础请求头（减少部分 WAF/防火墙对“探测请求”的拦截概率）
            $browserHeaders = array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'Connection: close'
            );

            // 针对同一个地址做多次尝试：
            // - 第 1 次：默认协商 TLS
            // - 第 2/3/4 次：如果环境支持，按 TLSv1.2 -> TLSv1.1 -> TLSv1 依次尝试（兼容少数老站点）
            $sslVersionsToTry = array(null);
            if (defined('CURL_SSLVERSION_TLSv1_2')) {
                $sslVersionsToTry[] = CURL_SSLVERSION_TLSv1_2;
            }
            if (defined('CURL_SSLVERSION_TLSv1_1')) {
                $sslVersionsToTry[] = CURL_SSLVERSION_TLSv1_1;
            }
            if (defined('CURL_SSLVERSION_TLSv1')) {
                $sslVersionsToTry[] = CURL_SSLVERSION_TLSv1;
            }

            foreach ($candidateUrls as $candidateUrl) {
                foreach ($sslVersionsToTry as $sslVersion) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $candidateUrl);
                    // 兼容性：部分站点不支持 HEAD；这里用 GET + Range 只取很小数据
                    curl_setopt($ch, CURLOPT_NOBODY, false);
                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                    curl_setopt($ch, CURLOPT_RANGE, '0-0');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // 需要区分 301/302
                    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'LinksPlus/1.3.3 LinkChecker');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $browserHeaders);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                    // 明确按 URL scheme 走 HTTP/HTTPS（避免错误协议探测）
                    if (stripos($candidateUrl, 'https://') === 0 && defined('CURLPROTO_HTTPS')) {
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
                        if (defined('CURLPROTO_HTTP')) {
                            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
                        }
                    } elseif (stripos($candidateUrl, 'http://') === 0 && defined('CURLPROTO_HTTP')) {
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
                        if (defined('CURLPROTO_HTTP')) {
                            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | (defined('CURLPROTO_HTTPS') ? CURLPROTO_HTTPS : 0));
                        }
                    }

                    // 有些环境下启用 HTTP/2 会导致个别站点握手失败，强制 HTTP/1.1 更稳
                    if (defined('CURL_HTTP_VERSION_1_1')) {
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    }

                    // 明确接受压缩，避免某些站点对空 Accept-Encoding 行为异常
                    curl_setopt($ch, CURLOPT_ENCODING, '');

                    if ($sslVersion !== null) {
                        curl_setopt($ch, CURLOPT_SSLVERSION, $sslVersion);
                    }

                    $resp = curl_exec($ch);
                    if ($resp === false) {
                        $tryError = curl_error($ch);
                        curl_close($ch);
                        continue;
                    }

                    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                    // 尝试读取 Location
                    if ($status === 301 || $status === 302) {
                        if (preg_match('/\nLocation:\s*([^\r\n]+)/i', "\n" . $resp, $m)) {
                            $finalUrl = trim($m[1]);
                        }
                    }

                    curl_close($ch);
                    // 成功拿到状态码就停止尝试
                    if ($status > 0) {
                        $error = null;
                        break 2;
                    }
                }
            }

            // 兜底A：如果主探测失败，尝试一次“普通 GET（不带 Range）”。
            // 说明：部分站点/WAF 会对 Range/探测型请求直接断连（Empty reply），但对普通 GET 会返回 200/3xx/4xx。
            if ($status <= 0) {
                foreach ($candidateUrls as $candidateUrl) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $candidateUrl);
                    curl_setopt($ch, CURLOPT_NOBODY, false);
                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // 保持与你的规则一致：不自动跟随，便于区分 301/302
                    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $browserHeaders);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    if (defined('CURL_HTTP_VERSION_1_1')) {
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    }

                    // 明确按 URL scheme 走 HTTP/HTTPS
                    if (stripos($candidateUrl, 'https://') === 0 && defined('CURLPROTO_HTTPS')) {
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
                        if (defined('CURLPROTO_HTTP')) {
                            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
                        }
                    } elseif (stripos($candidateUrl, 'http://') === 0 && defined('CURLPROTO_HTTP')) {
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
                        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | (defined('CURLPROTO_HTTPS') ? CURLPROTO_HTTPS : 0));
                    }

                    $resp = curl_exec($ch);
                    if ($resp === false) {
                        $tryError = curl_error($ch);
                        curl_close($ch);
                        continue;
                    }

                    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                    if ($status === 301 || $status === 302) {
                        if (preg_match('/\nLocation:\s*([^\r\n]+)/i', "\n" . $resp, $m)) {
                            $finalUrl = trim($m[1]);
                        }
                    }
                    curl_close($ch);

                    if ($status > 0) {
                        $error = null;
                        break;
                    }
                }
            }

            // 兜底：部分站点会对 GET + Range 探测直接断开（Empty reply）或对特定内容协商敏感。
            // 这里在“主探测失败”时再做一次更传统的 HEAD 探测，并允许跟随一次重定向，尽量拿到一个可用的状态码。
            if ($status <= 0) {
                foreach ($candidateUrls as $candidateUrl) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $candidateUrl);
                    curl_setopt($ch, CURLOPT_NOBODY, true);  // HEAD
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    // 允许跟随一次重定向：很多站点 http->https 或域名跳转，HEAD 更容易拿到 301/302
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'LinksPlus/1.3.3 LinkChecker');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $browserHeaders);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    if (defined('CURL_HTTP_VERSION_1_1')) {
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    }

                    // 明确按 URL scheme 走 HTTP/HTTPS
                    if (stripos($candidateUrl, 'https://') === 0 && defined('CURLPROTO_HTTPS')) {
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
                        if (defined('CURLPROTO_HTTP')) {
                            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
                        }
                    } elseif (stripos($candidateUrl, 'http://') === 0 && defined('CURLPROTO_HTTP')) {
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
                        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | (defined('CURLPROTO_HTTPS') ? CURLPROTO_HTTPS : 0));
                    }

                    $resp = curl_exec($ch);
                    if ($resp === false) {
                        $tryError = curl_error($ch);
                        curl_close($ch);
                        continue;
                    }

                    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                    $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                    curl_close($ch);

                    if ($status > 0) {
                        // 如果发生过跳转，把最终 URL 作为 finalUrl 给前端展示
                        if (is_string($effective) && $effective !== '' && $effective !== $candidateUrl) {
                            $finalUrl = $effective;
                        }
                        $error = null;
                        break;
                    }
                }
            }

            if ($status <= 0 && $tryError) {
                // 统一一下错误文案，避免目前这类错误全都被前端当作“请求失败”难以定位
                $te = (string)$tryError;
                // 典型 OpenSSL 版本不匹配报错：
                // - error:1407742E:SSL routines:SSL23_GET_SERVER_HELLO:tlsv1 alert protocol version
                // - tlsv1 alert protocol version
                // - protocol version
                // - wrong version number
                // - SSL23_GET_SERVER_HELLO
                if (stripos($te, 'unsupported protocol') !== false
                    || stripos($te, 'SSL23_GET_SERVER_HELLO') !== false
                    || stripos($te, 'tlsv1 alert protocol version') !== false
                    || stripos($te, 'alert protocol version') !== false
                    || stripos($te, 'wrong version number') !== false
                    || stripos($te, 'protocol version') !== false
                ) {
                    $error = 'SSL 不兼容/已过期';
                } elseif (stripos($te, 'handshake') !== false) {
                    $error = 'SSL 握手失败';
                } elseif (stripos($te, 'Empty reply from server') !== false) {
                    // 常见于：服务器主动断开连接/拒绝这类探测请求/需要特定 Host/SNI/策略拦截
                    $error = '服务器无响应';
                } elseif (stripos($te, 'Could not resolve host') !== false) {
                    // 理论上前面 gethostbyname 已拦截，但某些环境下仍可能在 cURL 报错
                    $error = '无法解析域名';
                } else {
                    $error = $te;
                }
            }

            // 兜底：如果 HTTP 层面都失败了，再做一次“网络可达性”探测，给出更明确的原因
            if ($status <= 0) {
                $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
                $net = $netProbe($ip, $scheme, $timeout);
                if (!$net['ok']) {
                    $error = '主机不可达/端口不可达';
                } else {
                    // 主机可达但 HTTP 失败：多半是 TLS/WAF/应用层拦截
                    if (!$error) {
                        $error = '主机可达但 HTTP 探测失败（可能被拦截/SSL/站点策略）';
                    }
                }
            }
        } else {
            // fallback：get_headers（不一定准确，但比纯前端强）
            $ctx = stream_context_create(array('http' => array('method' => 'HEAD', 'timeout' => $timeout)));
            $headers = @get_headers($url, 1, $ctx);
            if ($headers === false) {
                $error = '请求失败';
            } else {
                // 可能出现多段，取最后一次的 HTTP 状态行
                $statusLine = null;
                if (is_array($headers)) {
                    foreach ($headers as $k => $v) {
                        if (is_int($k) && stripos($v, 'HTTP/') === 0) {
                            $statusLine = $v;
                        }
                    }
                }
                if ($statusLine && preg_match('#\s(\d{3})\s#', $statusLine, $m)) {
                    $status = (int)$m[1];
                }

                if (($status === 301 || $status === 302) && isset($headers['Location'])) {
                    $loc = $headers['Location'];
                    $finalUrl = is_array($loc) ? end($loc) : $loc;
                }
            }

            // get_headers 也失败时，同样做一次网络可达性兜底
            if ($status <= 0) {
                $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
                $net = $netProbe($ip, $scheme, $timeout);
                if (!$net['ok']) {
                    $error = '主机不可达/端口不可达';
                } else {
                    if (!$error) {
                        $error = '主机可达但 HTTP 探测失败（可能被拦截/SSL/站点策略）';
                    }
                }
            }
        }

        $ok = ($status > 0);
        $this->response->throwJson(array(
            'ok' => $ok,
            'status' => $status,
            'finalUrl' => $finalUrl,
            'error' => $ok ? null : ($error ?: '请求失败')
        ));
    }

    /**
     * 按插件设置的 cid 列表，重写文章/页面正文：
     * - 用 {{links_plus}} 占位符替换为插件生成的友链 HTML
     */
    public function rewriteContents()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $settings = $options->plugin('Links');

        $raw = isset($settings->rewrite_cids) ? trim((string)$settings->rewrite_cids) : '';
        if ($raw === '') {
            $this->widget('Widget_Notice')->set(_t('请先在插件设置中填写需要重写的 cid'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
        }

        $cids = array_filter(array_map('trim', explode(',', $raw)), function ($v) {
            return $v !== '';
        });
        $cids = array_values(array_unique(array_map('intval', $cids)));
        $cids = array_filter($cids, function ($v) {
            return $v > 0;
        });

        if (!$cids) {
            $this->widget('Widget_Notice')->set(_t('cid 格式不正确，请使用英文逗号分隔的数字'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
        }

        $placeholder = Links_Plugin::REWRITE_PLACEHOLDER;
    $blockStart = Links_Plugin::REWRITE_BLOCK_START;
    $blockEnd = Links_Plugin::REWRITE_BLOCK_END;
        $html = Links_Plugin::buildRewriteHtml();
        if ($html === null || trim($html) === '') {
            $this->widget('Widget_Notice')->set(_t('重写已取消：未生成任何友链输出（可能没有启用的友链，或输出模板为空）'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
        }

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $table = $prefix . 'contents';

        $total = 0;
        $hit = 0;
        $miss = 0;
        $fail = 0;

        foreach ($cids as $cid) {
            $total++;
            try {
                $row = $db->fetchRow($db->select()->from($table)->where('cid = ?', $cid)->limit(1));
                if (!$row) {
                    $fail++;
                    continue;
                }
                $text = (string)($row['text'] ?? '');

                if ($text === '') {
                    $miss++;
                    continue;
                }

                $wrappedHtml = $blockStart . "\n" . $html . "\n" . $blockEnd;
                $newText = null;

                // 若存在历史重写块，则直接替换块内容（支持重复重写）
                if (strpos($text, $blockStart) !== false && strpos($text, $blockEnd) !== false) {
                    $pattern = '/' . preg_quote($blockStart, '/') . '.*?' . preg_quote($blockEnd, '/') . '/s';
                    $newText = preg_replace($pattern, $wrappedHtml, $text, 1);
                }

                // 否则用占位符替换
                if ($newText === null) {
                    // 一些编辑器/主题会把占位符包裹在行内标签里，或做 HTML 转义，
                    // 例如：<style>{{links_plus}}</style> / &lbrace;&lbrace;links_plus&rbrace;&rbrace;
                    // 为了让“重写”更稳，这里把几种常见等价写法都当作占位符处理。
                    $placeholderCandidates = array_values(array_unique(array_filter(array(
                        $placeholder,
                        // HTML 实体转义后的样式（部分编辑器会这么存）
                        htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'),
                        // 全角花括号（中文输入法常见）
                        str_replace(array('{', '}'), array('｛', '｝'), $placeholder),
                        // 被代码标记包裹
                        '`' . $placeholder . '`',
                        '<code>' . $placeholder . '</code>',
                        // 兼容用户把占位符写成 {{ links_plus }}
                        '{{ links_plus }}',
                        '{{ links_plus}}',
                        '{{links_plus }}',
                    ), function ($v) {
                        return is_string($v) && $v !== '';
                    })));

                    $foundCandidate = null;
                    foreach ($placeholderCandidates as $cand) {
                        if (strpos($text, $cand) !== false) {
                            $foundCandidate = $cand;
                            break;
                        }
                    }

                    if ($foundCandidate === null) {
                        // 兼容历史：如果正文已被替换成“裸 HTML”（没有占位符，也没有标记块），
                        // 仍允许通过查找一次生成的 html 片段来包裹标记块。
                        // 这里采用宽松策略：只要正文包含当前生成的 html（trim 后），则包裹它。
                        $plain = trim($html);
                        if ($plain !== '' && strpos($text, $plain) !== false) {
                            $newText = str_replace($plain, $wrappedHtml, $text);
                        } else {
                            $miss++;
                            continue;
                        }
                    }

                    // 命中占位符（或等价写法）则替换
                    if ($newText === null) {
                        $newText = str_replace($foundCandidate, $wrappedHtml, $text);
                    }
                }

                if ($newText === null || $newText === $text) {
                    $miss++;
                    continue;
                }

                $db->query($db->update($table)->rows(array('text' => $newText))->where('cid = ?', $cid));
                $hit++;
            } catch (Exception $e) {
                $fail++;
            }
        }

        $this->widget('Widget_Notice')->set(
            _t('重写完成：目标 %d 篇，命中替换 %d 篇，未发现占位符 %d 篇，失败 %d 篇。', $total, $hit, $miss, $fail),
            null,
            $hit > 0 ? 'success' : 'notice'
        );

        $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
    }

    public function action()
    {
        $this->db = Typecho_Db::get();
        $this->prefix = $this->db->getPrefix();
        $this->options = Typecho_Widget::widget('Widget_Options');

        // 前台公开入口：友链申请提交
        if ($this->request->is('do=apply-submit')) {
            $this->submitApplyLink();
            return;
        }

        // 其余操作全部要求管理员 + CSRF 防护
        Helper::security()->protect();
        $user = Typecho_Widget::widget('Widget_User');
        $user->pass('administrator');

        $this->on($this->request->is('do=insert'))->insertLink();
        $this->on($this->request->is('do=update'))->updateLink();
        $this->on($this->request->is('do=delete'))->deleteLink();
        $this->on($this->request->is('do=enable'))->enableLink();
        $this->on($this->request->is('do=prohibit'))->prohibitLink();
        $this->on($this->request->is('do=approve'))->approveLink();
        $this->on($this->request->is('do=reject'))->rejectLink();
        $this->on($this->request->is('do=sort'))->sortLink();
        $this->on($this->request->is('do=email-logo'))->emailLogo();
        $this->on($this->request->is('do=rewrite'))->rewriteContents();
        $this->on($this->request->is('do=check-link'))->checkLink();
        $this->on($this->request->is('do=update_templates'))->updateTemplates();
        $this->response->redirect($this->options->adminUrl);
    }

    /**
     * 从 GitHub 下载 templates 并覆盖本地 templates 目录
     */
    public function updateTemplates()
    {
        // 仅管理员可操作（action() 已授权）
        $zipUrl = 'https://github.com/lhl77/Typecho-Plugin-LinksPlus/archive/refs/heads/main.zip';
        $tmpZip = sys_get_temp_dir() . '/links_templates_' . time() . '.zip';
        $tmpDir = sys_get_temp_dir() . '/links_templates_dir_' . time();

        // 检查环境
        if (!class_exists('ZipArchive')) {
            $this->widget('Widget_Notice')->set(_t('服务器环境缺少 ZipArchive 扩展，无法解压模板包，请联系主机提供商安装。'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
            return;
        }

        // 下载 ZIP
        $context = stream_context_create(array('http' => array('timeout' => 30)));
        $data = @file_get_contents($zipUrl, false, $context);
        if (!$data) {
            $this->widget('Widget_Notice')->set(_t('下载失败：无法从 GitHub 获取模板压缩包。'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
            return;
        }
        file_put_contents($tmpZip, $data);

        // 解压到临时目录
        $zip = new ZipArchive();
        if ($zip->open($tmpZip) !== true) {
            @unlink($tmpZip);
            $this->widget('Widget_Notice')->set(_t('解压失败：无法打开下载的压缩包。'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
            return;
        }
        @mkdir($tmpDir, 0755, true);
        $zip->extractTo($tmpDir);
        $zip->close();

        // 识别压缩包内的 templates 目录（支持不同根目录名）
        $srcTemplates = null;
        // 直接检查根下是否有 templates
        if (is_dir($tmpDir . DIRECTORY_SEPARATOR . 'templates')) {
            $srcTemplates = $tmpDir . DIRECTORY_SEPARATOR . 'templates';
        } else {
            // 检查每个一级子目录（例如 Typecho-Plugin-LinksPlus-main/templates）
            $entries = @scandir($tmpDir);
            if ($entries && is_array($entries)) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') continue;
                    $candidate = $tmpDir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'templates';
                    if (is_dir($candidate)) {
                        $srcTemplates = $candidate;
                        break;
                    }
                }
            }
        }

        $dstTemplates = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates';

        if ($srcTemplates === null || !is_dir($srcTemplates)) {
            // 清理
            @unlink($tmpZip);
            $this->rrmdir($tmpDir);
            $this->widget('Widget_Notice')->set(_t('模板包中未找到 templates 目录，操作已取消。'), null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
            return;
        }

        // 检查目标目录可写
        $targetDir = dirname(__FILE__);
        if (!is_writable($targetDir)) {
            @unlink($tmpZip);
            $this->rrmdir($tmpDir);
            $this->widget('Widget_Notice')->set(_t('插件目录不可写，无法更新模板，请检查文件权限：') . $targetDir, null, 'notice');
            $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
            return;
        }

        // 按 manifest.json 的 version 做选择性覆盖（不备份）
        @mkdir($dstTemplates, 0755, true);
        $updated = array();
        $skipped = array();
        $failed = array();

        $entries = @scandir($srcTemplates);
        if ($entries && is_array($entries)) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                $srcTpl = $srcTemplates . DIRECTORY_SEPARATOR . $entry;
                if (!is_dir($srcTpl)) continue;
                $dstTpl = $dstTemplates . DIRECTORY_SEPARATOR . $entry;

                // 读取 manifest.json
                $srcManifestFile = $srcTpl . DIRECTORY_SEPARATOR . 'manifest.json';
                $dstManifestFile = $dstTpl . DIRECTORY_SEPARATOR . 'manifest.json';
                $srcVersion = null;
                $dstVersion = null;
                if (is_file($srcManifestFile)) {
                    $json = @file_get_contents($srcManifestFile);
                    $m = @json_decode($json, true);
                    if (is_array($m) && isset($m['version'])) {
                        $srcVersion = (string)$m['version'];
                    }
                }
                if (is_file($dstManifestFile)) {
                    $json = @file_get_contents($dstManifestFile);
                    $m = @json_decode($json, true);
                    if (is_array($m) && isset($m['version'])) {
                        $dstVersion = (string)$m['version'];
                    }
                }

                $shouldCopy = false;
                if (!is_dir($dstTpl)) {
                    // 目标不存在，直接复制
                    $shouldCopy = true;
                } else if ($srcVersion === null) {
                    // 源没有 version，仍覆盖以保证同步
                    $shouldCopy = true;
                } else if ($dstVersion === null) {
                    // 目标没有 version，覆盖
                    $shouldCopy = true;
                } else {
                    // 版本比较，只有当 github 的版本更大才覆盖
                    if (version_compare($srcVersion, $dstVersion, '>')) {
                        $shouldCopy = true;
                    }
                }

                if ($shouldCopy) {
                    // 删除目标（如果存在），然后复制
                    if (is_dir($dstTpl)) {
                        $this->rrmdir($dstTpl);
                    }
                    $ok = $this->rcopy($srcTpl, $dstTpl);
                    if ($ok) {
                        $updated[] = $entry . ($srcVersion !== null ? ' (v' . $srcVersion . ')' : '');
                    } else {
                        $failed[] = $entry;
                    }
                } else {
                    $skipped[] = $entry . ($dstVersion !== null ? ' (v' . $dstVersion . ')' : '');
                }
            }
        }

        // 清理临时文件
        @unlink($tmpZip);
        $this->rrmdir($tmpDir);

        // 汇总结果并提示
        $msgParts = array();
        if (!empty($updated)) {
            $msgParts[] = '已更新：' . implode(', ', $updated);
        }
        if (!empty($skipped)) {
            $msgParts[] = '跳过（版本相同或更新）：' . implode(', ', $skipped);
        }
        if (!empty($failed)) {
            $msgParts[] = '失败：' . implode(', ', $failed);
        }
        $notice = $msgParts ? implode('；', $msgParts) : '未检测到可更新的模板';

        $this->widget('Widget_Notice')->set(_t('模板更新完成：') . $notice, null, empty($failed) ? 'success' : 'notice');
        $this->response->redirect(Typecho_Common::url('options-plugin.php?config=Links', $this->options->adminUrl));
    }

    // 递归复制目录
    private function rcopy($src, $dst)
    {
        if (!is_dir($src)) return false;
        @mkdir($dst, 0755, true);
        $dir = opendir($src);
        if (!$dir) return false;
        while (false !== ($file = readdir($dir))) {
            if ($file == '.' || $file == '..') continue;
            $s = $src . DIRECTORY_SEPARATOR . $file;
            $d = $dst . DIRECTORY_SEPARATOR . $file;
            if (is_dir($s)) {
                $this->rcopy($s, $d);
            } else {
                @copy($s, $d);
            }
        }
        closedir($dir);
        return true;
    }

    // 递归删除目录
    private function rrmdir($dir)
    {
        if (!is_dir($dir)) return;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            @$todo($fileinfo->getRealPath());
        }
        @rmdir($dir);
    }
}

