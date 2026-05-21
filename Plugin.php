<?php

/**
 * 友情链接插件 by LHL (增强版)
 * 
 * @package Links+
 * @author LHL
 * @version 1.4.1
 * @link https://github.com/lhl77/Typecho-Plugin-LinksPlus
 * 
 * version 1.4.1 at 2026-05-22 by LHL
 * 修复 PJAX 兼容的一些问题
 * 
 * version 1.4.0 at 2026-05-19 by LHL
 * 优化 友情链接管理界面显示为md3卡片，移动端下体验良好
 * 增加 短代码功能 [LinksPlus /]
 * 增加 友链申请功能，通过AJax提交审核，并可通过邮箱通知
 * 增加 多种主题的友链界面复刻主题
 * ...更新比较多
 * 
 * 
 * version 1.3.3 at 2025-03-02 by LHL
 * 修复 Typecho 1.3.0下管理界面显示问题
 * 添加 一键检查友链网址是否能够正常访问
 * 
 * version 1.3.2 at 2025-02-10 by LHL
 * 修复 admin 运行目录非根目录时相对路径出错的问题
 * 添加 主题预览
 * 添加 一键同步同步Github主题
 * 
 * version 1.3.1 at 2025-02-09 by LHL
 * 优化 一些细节
 * 
 * version 1.3.0 at 2025-02-09 by LHL
 * 优化 UI - Material Design 3
 * 添加 Links Plus到菜单，更加方便操作
 * 添加 模板，能够更灵活地自定义输出结构，支持 CSS/JS 注入
 * 添加 正文重写，免去修改/重新开发主题的步骤
 * 移除 懵仙兔兔 的广告内容（一键添加TA的友链）
 * 
 * version 1.2.7 at 2024-06-21 by 泽泽社长
 * 解决php8.2一处报错问题
 * 
 * version 1.2.6 at 2023-05-15 by 泽泽社长
 * 支持主题作者自定义友链 html 结构
 * 
 * version 1.2.5 at 2023-03-27 by 懵仙兔兔
 * 友链添加 noopener 外链属性
 * 内置友链邮箱解析头像链接 api 接口调整为仅内部调用
 * Action 和内置友链邮箱解析头像链接 api 接口使用加盐地址
 * 文本字段入库过滤 XSS
 * 增加图片尺寸参数支持
 * 增加规则和默认图片尺寸设置选项
 * 修复历史遗留问题更新 lid 导致报错
 * 
 * version 1.2.3 at 2023-03-26 by 懵仙兔兔
 * 修复没有一条友链时，Typecho 1.2 友链设置界面报错问题（虽然报错不影响功能）
 * 调整表格间距
 * 删除失效链接，隐藏界面多余 input 标签
 * 修复友链邮箱解析头像链接功能，内置 api 接口
 * 
 * version 1.2.2 at 2020-03-11 by 懵仙兔兔
 * 修复一个小 BUG
 * 
 * version 1.2.1 at 2020-03-03 by 懵仙兔兔
 * 修复邮箱头像解析问题
 * 优化逻辑问题
 * 
 * version 1.2.0 at 2020-02-16 by 懵仙兔兔
 * 增加友链禁用功能
 * 增加友链邮箱功能
 * 增加友链邮箱解析头像链接功能
 * 修正数据表的占用大小问题
 * 
 * 历史版本 by 懵仙兔兔（第三方维护者）
 * 
 * version 1.1.3 at 2020-02-08 by 懵仙兔兔
 * 修复已存在表激活失败、表检测失败
 * 
 * version 1.1.2 at 2019-08-26 by 泽泽社长
 * 修复越权漏洞
 * 
 * version 1.1.1 at 2014-12-14
 * 修改支持 Typecho 1.0
 * 修正 Typecho 1.0 下不能删除的 BUG
 * 
 * 历史版本 by Hanny（原作者）
 * 
 * version 1.1.0 at 2013-12-08
 * 修改支持 Typecho 0.9
 * 
 * version 1.0.4 at 2010-06-30
 * 修正数据表的前缀问题
 * 在 Pattern 里加上所有的数据表字段
 * 
 * version 1.0.3 at 2010-06-20
 * 修改友链图片的支持方式。
 * 增加友链分类功能
 * 增加自定义字段，以便用户自定义扩展
 * 增加多种友链输出方式。
 * 增加较详细的帮助文档
 * 增加在自定义页面引用标签，方便友情链接页面的引用
 * 
 * version 1.0.2 at 2010-05-16
 * 增加SQLite支持
 * 
 * version 1.0.1 at 2009-12-27
 * 增加显示友链描述
 * 增加首页友链数量限制功能
 * 增加友链图片功能
 * 
 * version 1.0.0 at 2009-12-12
 * 实现友情链接的基本功能
 * 包括: 添加 删除 修改 排序
 */

class Links_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 固定占位符（写入文章正文中用于替换）
     */
    const REWRITE_PLACEHOLDER = '{{links_plus}}';

    /**
     * 重写块标记（用于二次重写时定位并替换旧内容）
     */
    const REWRITE_BLOCK_START = '<!-- LINKS_PLUS_START -->';
    const REWRITE_BLOCK_END = '<!-- LINKS_PLUS_END -->';

    /** 模板目录（相对插件目录） */
    const TEMPLATE_DIR = 'templates';

    /**
     * 获取插件绝对路径
     */
    public static function getPluginDir()
    {
        return dirname(__FILE__);
    }

    /**
     * 获取模板根目录绝对路径
     */
    public static function getTemplateRoot()
    {
        return self::getPluginDir() . DIRECTORY_SEPARATOR . self::TEMPLATE_DIR;
    }

    /**
     * 列出所有文件模板（读取 templates/<name>/manifest.json）
     *
     * @return array<string,array>
     */
    public static function listTemplates()
    {
        $root = self::getTemplateRoot();
        $list = array();
        if (!is_dir($root)) {
            return $list;
        }
        $dirs = glob($root . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        if (!$dirs) {
            return $list;
        }
        foreach ($dirs as $dir) {
            $manifestFile = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
            if (!is_file($manifestFile)) {
                continue;
            }
            $json = @file_get_contents($manifestFile);
            if (!$json) {
                continue;
            }
            $manifest = @json_decode($json, true);
            if (!is_array($manifest)) {
                continue;
            }
            $name = isset($manifest['name']) ? (string)$manifest['name'] : basename($dir);
            if ($name === '') {
                continue;
            }
            $manifest['_dir'] = $dir;
            $list[$name] = $manifest;
        }
        return $list;
    }

    /**
     * 读取模板文件内容
     */
    public static function readTemplateFile($templateName, $file)
    {
        $templateName = trim((string)$templateName);
        $file = trim((string)$file);
        if ($templateName === '' || $file === '') {
            return null;
        }
        // 简单防穿越：只允许 [A-Za-z0-9_-]
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $templateName)) {
            return null;
        }
        $path = self::getTemplateRoot() . DIRECTORY_SEPARATOR . $templateName . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            return null;
        }
        return file_get_contents($path);
    }

    /**
     * 在 <head> 中直接注入模板 CSS（由 Widget_Archive->header 钩子触发）。
     * 因为 <head> 不被 PJAX 替换，CSS 在所有 PJAX 导航中永久生效，
     * 从而彻底解决"首次 PJAX 进入友链页只有文字"的问题。
     */
    public static function injectHeadAssets($header, $widget)
    {
        static $done = false;
        if ($done) return;
        $done = true;

        $options = Typecho_Widget::widget('Widget_Options');
        if (!isset($options->plugins['activated']['Links'])) {
            return;
        }
        $settings = $options->plugin('Links');

        // 收集插件设置中所有已配置的模板名称
        $tplKeys  = array('template_text', 'template_img', 'template_mix');
        $tplNames = array();
        foreach ($tplKeys as $key) {
            $v = isset($settings->$key) ? trim((string)$settings->$key) : '';
            if ($v !== '') {
                $tplNames[$v] = true;
            }
        }

        if (empty($tplNames)) {
            return;
        }

        $templates = self::listTemplates();
        $rawDark   = isset($settings->apply_dark_classes)  ? trim((string)$settings->apply_dark_classes)  : '';
        $rawLight  = isset($settings->apply_light_classes) ? trim((string)$settings->apply_light_classes) : '';

        foreach (array_keys($tplNames) as $tplName) {
            if (!isset($templates[$tplName])) {
                continue;
            }
            $manifest = $templates[$tplName];
            $inject   = isset($manifest['inject']) && is_array($manifest['inject']) ? $manifest['inject'] : array();
            if (empty($inject['css'])) {
                continue;
            }

            $css = self::readTemplateFile($tplName, 'style.css');
            if (!$css || trim($css) === '') {
                continue;
            }

            $id = 'links-plus-tpl-' . htmlspecialchars($tplName, ENT_QUOTES, 'UTF-8');
            echo '<style id="' . $id . '">' . $css . "</style>\n";

            // 同步注入暗色覆盖 CSS（与 injectCustomDarkOverrideOnce 使用相同 id）
            $darkCss  = self::buildTemplateLpThemeAliasCss($css);
            $darkCss .= self::buildCustomDarkCssOverride($css, $rawDark, $rawLight);
            if ($darkCss !== '') {
                $darkId = 'links-plus-tpl-' . htmlspecialchars($tplName, ENT_QUOTES, 'UTF-8') . '-cdark';
                echo '<style id="' . $darkId . '">' . $darkCss . "</style>\n";
            }
        }
    }

    /**
     * 注入模板 CSS/JS（同一模板同一请求只注入一次）
     */
    public static function injectTemplateAssetsOnce($templateName, array $manifest, $ajaxCompatMode = 'default')
    {
        static $injected = array();
        $key = 'tpl:' . $templateName;
        if (isset($injected[$key])) {
            return;
        }
        $injected[$key] = true;

        $inject = isset($manifest['inject']) && is_array($manifest['inject']) ? $manifest['inject'] : array();
        $injectCss = !empty($inject['css']);
        $injectJs  = !empty($inject['js']);
        $pjaxMode  = ($ajaxCompatMode === 'force_pjax');

        if ($injectCss) {
            $css = self::readTemplateFile($templateName, 'style.css');
            if ($css && trim($css) !== '') {
                self::echoStyleTag('links-plus-tpl-' . $templateName, $css, $pjaxMode);
            }
        }

        if ($injectJs) {
            $js = self::readTemplateFile($templateName, 'script.js');
            if ($js && trim($js) !== '') {
                self::echoScriptTag('links-plus-tpl-' . $templateName, $js, $pjaxMode);
            }
        }
    }

    /**
     * 输出 <style> 注入：始终通过 <script> 将样式持久化到 <head>，
     * 并注册常见 PJAX 事件监听，确保 PJAX 导航后自动重新注入。
     * $pjaxMode 参数保留以兼容旧调用，不再影响行为。
     */
    private static function echoStyleTag($id, $css, $pjaxMode = false)
    {
        $idEsc   = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $idJson  = json_encode($id);
        $cssJson = json_encode($css);
        $lKey    = json_encode('__lpCssL_' . preg_replace('/[^a-zA-Z0-9]/', '_', $id));

        // 1. 直接输出 <style> 标签：PJAX 通过 innerHTML 替换容器时会立即应用（jquery.pjax
        //    等库不执行内联 <script> 但会应用 <style>），确保首次 PJAX 导航到友链页有样式。
        echo '<style id="' . $idEsc . '">' . $css . "</style>\n";

        // 2. 输出 <script> 将样式迁移到 <head>（持久化，防止导航离开后 CSS 随容器消失），
        //    同时注册 PJAX 事件监听器（PJAX 执行脚本时生效）。
        //    inj() 检测到 <style> 已在 <head> 则跳过；在 body 中则移至 <head>。
        echo '<script>(function(){'
            . 'var i=' . $idJson . ';'
            . 'var c=' . $cssJson . ';'
            . 'function inj(){'
            .   'var el=document.getElementById(i);'
            .   'if(el&&el.parentNode===document.head)return;'
            .   'if(el)el.parentNode.removeChild(el);'
            .   'var s=document.createElement("style");s.id=i;s.textContent=c;'
            .   '(document.head||document.documentElement).appendChild(s);'
            . '}'
            . 'inj();'
            . 'var lk=' . $lKey . ';'
            . 'if(!window[lk]){window[lk]=true;'
            . 'var ee=["pjax:end","pjax:success","pjax:complete","turbo:load","turbolinks:load","swup:pageView","barba:after-enter"];'
            . 'ee.forEach(function(e){document.addEventListener(e,inj);window.addEventListener(e,inj);});}'
            . '})()</script>';
    }

    /**
     * 输出 <script> 注入：force_pjax 模式时加全局 flag 防止 PJAX 多次导航重复执行，
     * 否则直接输出 <script id="...">。
     */
    private static function echoScriptTag($id, $js, $pjaxMode = false)
    {
        if ($pjaxMode) {
            $key = '__lpInit_' . preg_replace('/[^a-zA-Z0-9]/', '_', $id);
            echo '<script>(function(){var k=' . json_encode($key)
                . ';if(window[k])return;window[k]=true;' . $js . '})()</script>';
        } else {
            echo '<script id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">' . $js . '</script>';
        }
    }

    /**
     * 为模板 CSS 注入自定义亮/暗 class 的选择器覆盖规则（每个模板仅注入一次）。
     *
     * 模板 style.css 中有硬编码的暗色选择器（如 body.dark .lp-xxx），
     * 若用户配置了额外 class（如 body.night），则生成相同属性的追加规则。
     *
     * @param string $templateName 模板名称
     * @param array  $manifest     模板 manifest 数组
     * @param object $settings     插件设置对象
     */
    public static function injectCustomDarkOverrideOnce($templateName, array $manifest, $settings, $ajaxCompatMode = 'default')
    {
        static $injected = array();
        $key = 'cdark:' . $templateName;
        if (isset($injected[$key])) {
            return;
        }

        $inject = isset($manifest['inject']) && is_array($manifest['inject']) ? $manifest['inject'] : array();
        if (empty($inject['css'])) {
            return;
        }

        $css = self::readTemplateFile($templateName, 'style.css');
        if (!$css || trim($css) === '') {
            return;
        }

        $rawDark  = isset($settings->apply_dark_classes)  ? trim((string)$settings->apply_dark_classes)  : '';
        $rawLight = isset($settings->apply_light_classes) ? trim((string)$settings->apply_light_classes) : '';

        // 始终生成 [data-lp-theme="dark"] 等效选择器（使 data-lp-theme 包裹层能触发模板暗色样式）
        $allCss  = self::buildTemplateLpThemeAliasCss($css);
        // 追加用户自定义亮/暗 class 的等效规则（仅当用户配置了自定义 class 时）
        $allCss .= self::buildCustomDarkCssOverride($css, $rawDark, $rawLight);

        if ($allCss === '') {
            return;
        }

        $injected[$key] = true;
        self::echoStyleTag('links-plus-tpl-' . $templateName . '-cdark', $allCss, $ajaxCompatMode === 'force_pjax');
    }

    /**
     * 从模板 CSS 的 [data-theme="dark"] 规则中提取内层选择器，
     * 生成对应的 [data-lp-theme="dark"] 等效规则，使 output_str 的强制暗色包裹层生效。
     *
     * @param string $css 模板 style.css 内容
     * @return string 追加的 CSS 字符串
     */
    public static function buildTemplateLpThemeAliasCss($css)
    {
        if (trim($css) === '') {
            return '';
        }
        $extra = '';
        preg_match_all('/([^{}@]+)\{([^{}]+)\}/s', $css, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $selectorGroup = trim($match[1]);
            $declarations  = trim($match[2]);
            if ($declarations === '' || strpos($selectorGroup, '[data-theme="dark"]') === false) {
                continue;
            }
            foreach (preg_split('/,(?![^(]*\))/', $selectorGroup) as $part) {
                $part = trim($part);
                if (strpos($part, '[data-theme="dark"]') === 0) {
                    $innerSel = trim(substr($part, strlen('[data-theme="dark"]')));
                    if ($innerSel !== '') {
                        $extra .= '[data-lp-theme="dark"] ' . $innerSel . '{' . $declarations . '}';
                    }
                    break;
                }
            }
        }
        return $extra;
    }

    /**
     * 解析模板 CSS，为暗/亮模式规则生成自定义 class 的等效选择器块。
     *
     * 识别格式：[data-theme="dark"] .inner（或 [data-lp-theme="light"] .inner），
     * 对每个自定义 class 生成 body.cls .inner / html.cls .inner 规则。
     *
     * @param string $css      模板 style.css 内容
     * @param string $rawDark  自定义暗色 class 字符串（空格/逗号分隔）
     * @param string $rawLight 自定义亮色 class 字符串
     * @return string 追加的 CSS 字符串
     */
    public static function buildCustomDarkCssOverride($css, $rawDark, $rawLight)
    {
        $darkClasses  = array();
        $lightClasses = array();

        foreach (preg_split('/[\s,]+/', $rawDark,  -1, PREG_SPLIT_NO_EMPTY) as $cls) {
            $cls = preg_replace('/[^a-zA-Z0-9_-]/', '', $cls);
            if ($cls !== '') $darkClasses[] = $cls;
        }
        foreach (preg_split('/[\s,]+/', $rawLight, -1, PREG_SPLIT_NO_EMPTY) as $cls) {
            $cls = preg_replace('/[^a-zA-Z0-9_-]/', '', $cls);
            if ($cls !== '') $lightClasses[] = $cls;
        }

        if (empty($darkClasses) && empty($lightClasses)) {
            return '';
        }

        $extra = '';
        // 仅匹配顶层规则（不含 @media 块内规则）
        preg_match_all('/([^{}@]+)\{([^{}]+)\}/s', $css, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $selectorGroup = trim($match[1]);
            $declarations  = trim($match[2]);
            if ($declarations === '') {
                continue;
            }

            $isDark  = strpos($selectorGroup, '[data-theme="dark"]')    !== false;
            $isLight = strpos($selectorGroup, '[data-lp-theme="light"]') !== false;
            if (!$isDark && !$isLight) {
                continue;
            }

            // 从选择器组中提取内层选择器（[data-theme="dark"] 后面的部分）
            $innerSel = null;
            $darkTrigger  = '[data-theme="dark"]';
            $lightTrigger = '[data-lp-theme="light"]';
            foreach (preg_split('/,(?![^(]*\))/', $selectorGroup) as $part) {
                $part = trim($part);
                if ($isDark && strpos($part, $darkTrigger) === 0) {
                    $innerSel = trim(substr($part, strlen($darkTrigger)));
                    break;
                }
                if ($isLight && strpos($part, $lightTrigger) === 0) {
                    $innerSel = trim(substr($part, strlen($lightTrigger)));
                    break;
                }
            }

            if ($innerSel === null || $innerSel === '') {
                continue;
            }

            $newSelectors = array();
            if ($isDark) {
                foreach ($darkClasses as $cls) {
                    $newSelectors[] = 'body.' . $cls . ' ' . $innerSel;
                    $newSelectors[] = 'html.' . $cls . ' ' . $innerSel;
                }
            } elseif ($isLight) {
                foreach ($lightClasses as $cls) {
                    $newSelectors[] = 'body.' . $cls . ' ' . $innerSel;
                    $newSelectors[] = 'html.' . $cls . ' ' . $innerSel;
                }
            }

            if (!empty($newSelectors)) {
                $extra .= implode(',', $newSelectors) . '{' . $declarations . '}';
            }
        }

        return $extra;
    }

    /**
     */
    public static function buildTemplateWrapper(array $manifest)
    {
        if (!isset($manifest['wrapper']) || !is_array($manifest['wrapper'])) {
            return array('', '');
        }

        $wrapper = $manifest['wrapper'];
        $tag = isset($wrapper['tag']) ? strtolower(trim((string)$wrapper['tag'])) : '';
        if ($tag === '') {
            return array('', '');
        }

        // 仅允许常见容器标签，避免注入风险
        if (!in_array($tag, array('ul', 'ol', 'div'), true)) {
            return array('', '');
        }

        $attrs = '';
        if (isset($wrapper['class']) && trim((string)$wrapper['class']) !== '') {
            $attrs .= ' class="' . htmlspecialchars((string)$wrapper['class'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (isset($wrapper['id']) && trim((string)$wrapper['id']) !== '') {
            $attrs .= ' id="' . htmlspecialchars((string)$wrapper['id'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (isset($wrapper['attrs']) && is_array($wrapper['attrs'])) {
            foreach ($wrapper['attrs'] as $k => $v) {
                $k = trim((string)$k);
                if ($k === '' || !preg_match('/^[A-Za-z_:][-A-Za-z0-9_:.]*$/', $k)) {
                    continue;
                }
                $attrs .= ' ' . $k . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        return array('<' . $tag . $attrs . '>', '</' . $tag . '>');
    }

    /**
     * 将 checkbox/select 的值统一转成数组
     *
     * @param mixed $value
     * @return array<int,string>
     */
    private static function normalizeOptionArray($value)
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
     * 获取客户端 IP：优先 REMOTE_ADDR，仅在缺失时回退代理头
     */
    public static function getClientIp()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string)$_SERVER['REMOTE_ADDR']) : '';
        if ($ip !== '') {
            return $ip;
        }

        $cf = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? trim((string)$_SERVER['HTTP_CF_CONNECTING_IP']) : '';
        if ($cf !== '') {
            return $cf;
        }

        $xff = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim((string)$_SERVER['HTTP_X_FORWARDED_FOR']) : '';
        if ($xff !== '') {
            $parts = explode(',', $xff);
            if (!empty($parts)) {
                return trim((string)$parts[0]);
            }
        }

        return '';
    }

    /**
     * 友链申请表单签名（支持重写后的静态内容长期可用）
     */
    public static function buildApplyToken()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $secret = isset($options->secret) && trim((string)$options->secret) !== ''
            ? (string)$options->secret
            : (string)$options->siteUrl;
        return hash_hmac('sha256', 'links-plus-apply-form', $secret);
    }

    /**
     * 校验友链申请表单签名
     */
    public static function verifyApplyToken($token)
    {
        $expected = self::buildApplyToken();
        $actual = trim((string)$token);
        if ($actual === '') {
            return false;
        }

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        return $expected === $actual;
    }

    /**
     * 判断当前输出上下文是否需要渲染“友链申请”
     */
    public static function shouldRenderApplyForm($context, $settings)
    {
        $enabled = self::normalizeOptionArray(isset($settings->enable_link_apply) ? $settings->enable_link_apply : array());
        if (!in_array('enabled', $enabled, true)) {
            return false;
        }

        // OnlyForm 短代码：只要功能已启用，不检查显示位置限制
        if ($context === 'shortcode-onlyform') {
            return true;
        }

        $targets = self::normalizeOptionArray(isset($settings->apply_display_targets) ? $settings->apply_display_targets : array());
        if (empty($targets)) {
            return false;
        }

        return in_array((string)$context, $targets, true);
    }

    /**
     * 渲染模板字符串（邮件/HTML 复用）
     *
     * 模板变量格式：{{name}}
     */
    public static function renderTemplateString($template, array $vars)
    {
        $replace = array();
        foreach ($vars as $k => $v) {
            $replace['{{' . $k . '}}'] = (string)$v;
        }
        return strtr((string)$template, $replace);
    }

    /**
     * 前台申请状态提示
     */
    private static function getApplyStatusMessageHtml()
    {
        $request = Typecho_Request::getInstance();
        $status = strtolower(trim((string)$request->get('links_apply_status')));
        if ($status === '') {
            return '';
        }

        $map = array(
            'ok' => array('ok', _t('申请已提交，正在等待管理员审核。')),
            'duplicate' => array('warn', _t('该友链地址已存在，请勿重复提交。')),
            'rate_limited' => array('warn', _t('提交过于频繁，请稍后再试。')),
            'invalid' => array('err', _t('提交失败：参数不合法，请检查后重试。')),
            'token' => array('err', _t('提交失败：表单校验未通过，请刷新页面后重试。')),
            'server_error' => array('err', _t('提交失败：服务器内部错误，请稍后重试。')),
        );

        if (!isset($map[$status])) {
            return '';
        }

        $type = $map[$status][0];
        $msg = htmlspecialchars($map[$status][1], ENT_QUOTES, 'UTF-8');

        return '<div class="links-plus-apply-msg links-plus-apply-msg-' . $type . '">' . $msg . '</div>';
    }

    /**
     * 生成前台"接受友链申请"表单 HTML
    /**
     * 生成前台"接受友链申请"表单 HTML
     * 模式：'' = 内嵌（折叠框），'__popup__' = 弹窗按钮（AJAX 提交，不刷页）
     */
    public static function renderApplyFormHtml($context)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $settings = $options->plugin('Links');
        $ajaxCompatMode = isset($settings->ajax_compat_mode) ? (string)$settings->ajax_compat_mode : 'default';
        if (!self::shouldRenderApplyForm($context, $settings)) {
            return '';
        }

        $requireDescription = in_array('required', self::normalizeOptionArray(isset($settings->apply_require_description) ? $settings->apply_require_description : array()), true);
        $requireEmail       = in_array('required', self::normalizeOptionArray(isset($settings->apply_require_email)       ? $settings->apply_require_email       : array()), true);
        $requireUser        = in_array('required', self::normalizeOptionArray(isset($settings->apply_require_user)        ? $settings->apply_require_user        : array()), true);
        $showUserField      = in_array('show',     self::normalizeOptionArray(isset($settings->apply_show_user_field)     ? $settings->apply_show_user_field     : array('show')), true);

        $defaultSort = isset($settings->apply_default_sort) ? trim((string)$settings->apply_default_sort) : '';
        if ($defaultSort === '') {
            $defaultSort = _t('友链申请');
        }

        $customTitle   = isset($settings->apply_title)           ? trim((string)$settings->apply_title)           : '';
        $customDesc    = isset($settings->apply_desc)            ? trim((string)$settings->apply_desc)            : '';
        $colorMode     = isset($settings->apply_color_mode)      ? trim((string)$settings->apply_color_mode)      : 'auto';
        $popupBtnStyle = isset($settings->apply_popup_btn_style) ? trim((string)$settings->apply_popup_btn_style) : 'default';
        $defaultOpen   = in_array('open', self::normalizeOptionArray(isset($settings->apply_default_open) ? $settings->apply_default_open : array('open')), true);

        $actionUrl  = Typecho_Common::url('action/links-apply', $options->siteUrl);
        $applyToken = self::buildApplyToken();
        $statusHtml = self::getApplyStatusMessageHtml();

        $nameReq  = ' required';
        $urlReq   = ' required';
        $imgReq   = ' required';
        $descReq  = $requireDescription ? ' required' : '';
        $emailReq = $requireEmail       ? ' required' : '';
        $userReq  = $requireUser        ? ' required' : '';

        $fieldHtml  = '';
        $fieldHtml .= '<label class="links-plus-apply-label">' . _t('友链名称') . ' *</label>';
        $fieldHtml .= '<input class="links-plus-apply-input" type="text" name="name" maxlength="50" placeholder="' . htmlspecialchars(_t('请输入站点名称'), ENT_QUOTES, 'UTF-8') . '"' . $nameReq . ' />';
        $fieldHtml .= '<label class="links-plus-apply-label">' . _t('友链地址') . ' *</label>';
        $fieldHtml .= '<input class="links-plus-apply-input" type="url" name="url" maxlength="200" placeholder="https://example.com"' . $urlReq . ' />';
        $fieldHtml .= '<label class="links-plus-apply-label">' . _t('友链图片') . ' *</label>';
        $fieldHtml .= '<input class="links-plus-apply-input" type="url" name="image" maxlength="200" placeholder="https://example.com/logo.png"' . $imgReq . ' />';
        $fieldHtml .= '<label class="links-plus-apply-label">' . _t('友链描述') . ($requireDescription ? ' *' : '') . '</label>';
        $fieldHtml .= '<textarea class="links-plus-apply-input" name="description" maxlength="200" rows="3" placeholder="' . htmlspecialchars(_t('可选：站点简介'), ENT_QUOTES, 'UTF-8') . '"' . $descReq . '></textarea>';
        $fieldHtml .= '<label class="links-plus-apply-label">' . _t('邮箱') . ($requireEmail ? ' *' : '') . '</label>';
        $fieldHtml .= '<input class="links-plus-apply-input" type="email" name="email" maxlength="50" placeholder="name@example.com"' . $emailReq . ' />';
        if ($showUserField) {
            $fieldHtml .= '<label class="links-plus-apply-label">' . _t('自定义数据') . ($requireUser ? ' *' : '') . '</label>';
            $fieldHtml .= '<input class="links-plus-apply-input" type="text" name="user" maxlength="200" placeholder="' . htmlspecialchars(_t('可选：站点补充信息'), ENT_QUOTES, 'UTF-8') . '"' . $userReq . ' />';
        }

        $honeypotHtml = '<div class="links-plus-apply-hp" aria-hidden="true"><label>Leave this empty</label><input type="text" name="lp_contact" value="" tabindex="-1" autocomplete="off" /></div>';

        $templateName = isset($settings->template_apply) ? trim((string)$settings->template_apply) : '';
        $isPopup      = ($templateName === '__popup__');

        $themeAttr = '';
        if ($colorMode === 'light') {
            $themeAttr = ' data-lp-theme="light"';
        } elseif ($colorMode === 'dark') {
            $themeAttr = ' data-lp-theme="dark"';
        }

        $popupBtnCls = 'lp-apply-open-btn';
        if (in_array($popupBtnStyle, array('outline', 'ghost', 'gradient'), true)) {
            $popupBtnCls .= ' lp-apply-open-btn--' . $popupBtnStyle;
        }

        // ---- CSS + JS（每个请求只注入一次）----
        static $assetsInjected = false;
        $assetsBlock = '';
        if (!$assetsInjected) {
            $assetsInjected = true;
            $dm9 = '[data-theme="dark"] %1$s,[data-lp-theme="dark"] %1$s,body.dark %1$s,body.dark-mode %1$s,body.dark-theme %1$s,body.theme-dark %1$s,html.dark %1$s,html.dark-mode %1$s,html.dark-theme %1$s,html.theme-dark %1$s';
            $lm  = '[data-lp-theme="light"] %1$s';
            // 追加用户自定义亮/暗 class
            $rawDark = isset($settings->apply_dark_classes) ? trim((string)$settings->apply_dark_classes) : '';
            if ($rawDark !== '') {
                foreach (preg_split('/[\s,]+/', $rawDark, -1, PREG_SPLIT_NO_EMPTY) as $cls) {
                    $cls = preg_replace('/[^a-zA-Z0-9_-]/', '', $cls);
                    if ($cls !== '') { $dm9 .= ',body.' . $cls . ' %1$s,html.' . $cls . ' %1$s'; }
                }
            }
            $rawLight = isset($settings->apply_light_classes) ? trim((string)$settings->apply_light_classes) : '';
            if ($rawLight !== '') {
                foreach (preg_split('/[\s,]+/', $rawLight, -1, PREG_SPLIT_NO_EMPTY) as $cls) {
                    $cls = preg_replace('/[^a-zA-Z0-9_-]/', '', $cls);
                    if ($cls !== '') { $lm .= ',body.' . $cls . ' %1$s,html.' . $cls . ' %1$s'; }
                }
            }
            $assetsBlock = '<style id="links-plus-apply-style">'
                . '@keyframes lp-btn-grad{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}'
                . '@keyframes lp-check{0%{transform:scale(0) rotate(-45deg);opacity:0}60%{transform:scale(1.3) rotate(5deg)}100%{transform:scale(1) rotate(0deg);opacity:1}}'
                . ':root{--lp-btn-h1:215;--lp-btn-h2:275}'
                . '.links-plus-apply{margin-top:18px;padding:18px 20px;border:1px solid rgba(0,0,0,.08);border-radius:14px;background:rgba(255,255,255,.92)}'
                . '.links-plus-apply-summary{display:flex;align-items:center;justify-content:space-between;cursor:pointer;list-style:none;-webkit-user-select:none;user-select:none;gap:8px}'
                . '.links-plus-apply-summary::-webkit-details-marker{display:none}'
                . '.links-plus-apply-title{font-size:16px;font-weight:700;color:#1f2937}'
                . '.links-plus-apply-chevron{width:18px;height:18px;flex-shrink:0;transition:transform .25s;color:#6b7280}'
                . '.links-plus-apply[open] .links-plus-apply-chevron{transform:rotate(180deg)}'
                . '.links-plus-apply-body{margin-top:12px}'
                . '.links-plus-apply-desc{font-size:13px;color:#6b7280;margin-bottom:12px;line-height:1.6}'
                . '.links-plus-apply-form{display:grid;gap:8px;position:relative}'
                . '.links-plus-apply-label{font-size:12px;color:#4b5563;font-weight:600}'
                . '.links-plus-apply-input{width:100%;box-sizing:border-box;border:1px solid rgba(0,0,0,.16);border-radius:10px;padding:9px 10px;background:#fff;color:#111827;font-size:13px}'
                . '.links-plus-apply-input:focus{outline:none;border-color:rgba(0,97,164,.45);box-shadow:0 0 0 3px rgba(0,97,164,.16)}'
                . '.links-plus-apply-submit{margin-top:6px;display:flex;align-items:center;justify-content:center;width:100%;border:0;border-radius:999px;padding:13px 28px;background:linear-gradient(135deg,hsl(var(--lp-btn-h1,215),80%,36%),hsl(var(--lp-btn-h2,275),75%,55%));background-size:200% 200%;animation:lp-btn-grad 5s ease infinite;color:#fff;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.3px}'
                . '.links-plus-apply-submit:hover{opacity:.88}'
                . '.links-plus-apply-submit:disabled{opacity:.55;cursor:not-allowed}'
                . '.links-plus-apply-powered{margin:6px 0 0;text-align:center!important;font-size:11px;color:#9ca3af}'
                . '.links-plus-apply-powered a{color:inherit;text-decoration:none}'
                . '.links-plus-apply-powered a:hover{text-decoration:underline}'
                . '.links-plus-apply-hp{position:absolute;left:-9999px;top:-9999px;opacity:0;height:0;overflow:hidden}'
                . '.links-plus-apply-msg-area,.links-plus-apply-msg{padding:8px 10px;border-radius:10px;font-size:13px;line-height:1.5;margin-bottom:8px}'
                . '.links-plus-apply-msg-area{display:none}'
                . '.links-plus-apply-msg-ok{background:#e8f3ff;color:#12416a;border:1px solid rgba(0,97,164,.22)}'
                . '.links-plus-apply-msg-ok::before{content:"\2713";display:inline-block;margin-right:4px;animation:lp-check .4s cubic-bezier(.34,1.56,.64,1) both}'
                . '.links-plus-apply-msg-warn{background:#fff8e8;color:#8a5b00;border:1px solid rgba(245,158,11,.28)}'
                . '.links-plus-apply-msg-err{background:#ffeeee;color:#9b1c1c;border:1px solid rgba(220,38,38,.26)}'
                . '@keyframes lp-so-pop{from{transform:scale(.6);opacity:0}to{transform:scale(1);opacity:1}}'
                . '.lp-success-overlay{position:absolute;inset:0;z-index:5;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.92);border-radius:12px;opacity:0;pointer-events:none;transition:opacity .25s}'
                . '.lp-success-overlay.is-visible{opacity:1;pointer-events:auto}'
                . '.lp-success-icon{width:72px;height:72px;border-radius:50%;background:#e8f3ff;display:flex;align-items:center;justify-content:center;animation:lp-so-pop .4s cubic-bezier(.34,1.56,.64,1) both}'
                . '.lp-success-icon svg{width:36px;height:36px;stroke:#0061a4;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}'
                . '.lp-success-label{margin-top:14px;font-size:15px;font-weight:700;color:#12416a}'
                . '@media (max-width:680px){.links-plus-apply{padding:13px 15px}}'
                . '.lp-apply-open-btn{display:inline-flex;align-items:center;gap:6px;border:0;border-radius:999px;padding:11px 22px;background:#0061a4;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:opacity .15s}'
                . '.lp-apply-open-btn:hover{opacity:.88}'
                . '.lp-apply-open-btn--outline{background:transparent!important;border:2px solid #0061a4;color:#0061a4}'
                . '.lp-apply-open-btn--outline:hover{background:rgba(0,97,164,.06)!important;opacity:1}'
                . '.lp-apply-open-btn--ghost{background:transparent!important;border:1px solid rgba(0,0,0,.2);color:inherit}'
                . '.lp-apply-open-btn--ghost:hover{background:rgba(0,0,0,.04)!important;opacity:1}'
                . '.lp-apply-open-btn--gradient{background:linear-gradient(135deg,hsl(var(--lp-btn-h1,215),80%,36%),hsl(var(--lp-btn-h2,275),75%,55%))!important;background-size:200% 200%!important;animation:lp-btn-grad 5s ease infinite!important}'
                . '.lp-apply-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .2s}'
                . '.lp-apply-modal-overlay.is-open{opacity:1;pointer-events:auto}'
                . '.lp-apply-modal{background:#fff;border-radius:16px;padding:24px;width:440px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto;position:relative;box-shadow:0 8px 32px rgba(0,0,0,.18);transform:translateY(12px);transition:transform .2s}'
                . '.lp-apply-modal-overlay.is-open .lp-apply-modal{transform:translateY(0)}'
                . '.lp-apply-modal .links-plus-apply-title{margin-bottom:4px;font-size:17px}'
                . '.lp-apply-modal-close{position:absolute;top:10px;right:10px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border:0;background:none;cursor:pointer;border-radius:50%;font-size:22px;color:#6b7280;line-height:1;padding:0}'
                . '.lp-apply-modal-close:hover{background:rgba(0,0,0,.08);color:#1f2937}'
                . sprintf($dm9, '.links-plus-apply')             . '{background:rgba(26,32,44,.96);border-color:rgba(255,255,255,.08)}'
                . sprintf($dm9, '.links-plus-apply-title')       . '{color:#e2e8f0}'
                . sprintf($dm9, '.links-plus-apply-chevron')     . '{color:#94a3b8}'
                . sprintf($dm9, '.links-plus-apply-desc')        . '{color:#94a3b8}'
                . sprintf($dm9, '.links-plus-apply-label')       . '{color:#94a3b8}'
                . sprintf($dm9, '.links-plus-apply-input')       . '{background:#111827;border-color:rgba(255,255,255,.12);color:#e2e8f0}'
                . sprintf($dm9, '.links-plus-apply-input:focus') . '{border-color:rgba(96,165,250,.6);box-shadow:0 0 0 3px rgba(96,165,250,.18)}'
                . sprintf($dm9, '.links-plus-apply-powered')     . '{color:#64748b}'
                . sprintf($dm9, '.links-plus-apply-msg-ok')      . '{background:rgba(0,97,164,.14);color:#7dd3fc;border-color:rgba(96,165,250,.3)}'
                . sprintf($dm9, '.links-plus-apply-msg-warn')    . '{background:rgba(245,158,11,.1);color:#fcd34d;border-color:rgba(245,158,11,.3)}'
                . sprintf($dm9, '.links-plus-apply-msg-err')     . '{background:rgba(220,38,38,.12);color:#fca5a5;border-color:rgba(220,38,38,.3)}'
                . sprintf($dm9, '.lp-apply-modal')               . '{background:#1e2433}'
                . sprintf($dm9, '.lp-apply-modal-close')         . '{color:#94a3b8}'
                . sprintf($dm9, '.lp-apply-modal-close:hover')   . '{background:rgba(255,255,255,.1);color:#e2e8f0}'
                . sprintf($dm9, '.lp-success-overlay')            . '{background:rgba(18,24,40,.9)}'
                . sprintf($dm9, '.lp-success-icon')               . '{background:rgba(0,97,164,.18)}'
                . sprintf($dm9, '.lp-success-icon svg')           . '{stroke:#7dd3fc}'
                . sprintf($dm9, '.lp-success-label')              . '{color:#7dd3fc}'
                . sprintf($lm,  '.links-plus-apply')             . '{background:rgba(255,255,255,.98)!important;border-color:rgba(0,0,0,.08)!important}'
                . sprintf($lm,  '.links-plus-apply-title')       . '{color:#1f2937!important}'
                . sprintf($lm,  '.links-plus-apply-desc')        . '{color:#6b7280!important}'
                . sprintf($lm,  '.links-plus-apply-label')       . '{color:#4b5563!important}'
                . sprintf($lm,  '.links-plus-apply-input')       . '{background:#fff!important;border-color:rgba(0,0,0,.16)!important;color:#111827!important}'
                . sprintf($lm,  '.lp-apply-modal')               . '{background:#fff!important}'
                . '</style>'
                . '<script id="links-plus-apply-script">'
                . '(function(){'
                . '"use strict";'
                . 'var h=Math.floor(Math.random()*360);'
                . 'document.documentElement.style.setProperty("--lp-btn-h1",h);'
                . 'document.documentElement.style.setProperty("--lp-btn-h2",(h+80)%360);'
                . 'function setupForm(form){'
                .   'if(form._lpSet)return;form._lpSet=true;'
                .   'var btn=form.querySelector(".links-plus-apply-submit");'
                .   'form.addEventListener("submit",function(e){'
                .     'e.preventDefault();'
                .     'if(btn){btn.disabled=true;btn.style.opacity="0.55";}'
                .     'var cleanup=function(){if(btn){btn.disabled=false;btn.style.opacity="";}};'
                .     'fetch(form.action,{method:"POST",headers:{"X-Requested-With":"XMLHttpRequest"},body:new FormData(form)})'
                .     '.then(function(r){return r.json();})'
                .     '.then(function(d){showMsg(form,d.status,d.message);if(d.status==="ok")form.reset();cleanup();})'
                .     '.catch(function(){showMsg(form,"err","\u63d0\u4ea4\u5931\u8d25\uff1a\u7f51\u7edc\u9519\u8bef\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5\u3002");cleanup();});'
                .   '});'
                . '}'
                . 'function showMsg(form,status,message){'
                .   'if(status==="ok"){'
                .     'var ov=form.querySelector(".lp-success-overlay");'
                .     'if(ov){'
                .       'ov.innerHTML=\'<div class="lp-success-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span class="lp-success-label">\u7533\u8bf7\u6210\u529f\uff01</span>\';'
                .       'ov.classList.add("is-visible");'
                .       'setTimeout(function(){ov.classList.remove("is-visible");ov.innerHTML="";},2500);'
                .     '}'
                .     'return;'
                .   '}'
                .   'var a=form.querySelector(".links-plus-apply-msg-area");'
                .   'if(!a)return;'
                .   'var t=(status==="rate_limited"||status==="duplicate")?"warn":"err";'
                .   'a.className="links-plus-apply-msg-area links-plus-apply-msg links-plus-apply-msg-"+t;'
                .   'a.textContent=message;'
                .   'a.style.display="block";'
                . '}'
                . 'function setupPopups(){'
                .   'var obs=document.querySelectorAll("[data-lp-popup-open]");'
                .   'for(var i=0;i<obs.length;i++){(function(b){if(b._lpPop)return;b._lpPop=true;'
                .     'b.addEventListener("click",function(){var el=document.getElementById(b.getAttribute("data-lp-popup-open"));if(el)el.classList.add("is-open");});'
                .   '})(obs[i]);}'
                .   'var ols=document.querySelectorAll(".lp-apply-modal-overlay");'
                .   'for(var i=0;i<ols.length;i++){(function(ov){if(ov._lpOv)return;ov._lpOv=true;'
                .     'ov.addEventListener("click",function(e){if(e.target===ov)ov.classList.remove("is-open");});'
                .   '})(ols[i]);}'
                .   'var cls=document.querySelectorAll("[data-lp-popup-close]");'
                .   'for(var i=0;i<cls.length;i++){(function(cl){if(cl._lpCl)return;cl._lpCl=true;'
                .     'cl.addEventListener("click",function(){var p=cl.closest(".lp-apply-modal-overlay");if(p)p.classList.remove("is-open");});'
                .   '})(cls[i]);}'
                .   'if(!document._lpEscSet){document._lpEscSet=true;'
                .     'document.addEventListener("keydown",function(e){'
                .       'if(e.key==="Escape"||e.keyCode===27){var op=document.querySelector(".lp-apply-modal-overlay.is-open");if(op)op.classList.remove("is-open");}});}'
                . '}'
                . 'function init(){'
                .   'var forms=document.querySelectorAll("form[data-lp-ajax]");'
                .   'for(var i=0;i<forms.length;i++)setupForm(forms[i]);'
                .   'setupPopups();'
                . '}'
                . 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",init);}else{init();}'
                . ($ajaxCompatMode === 'force_pjax' ? 'var _lpPE=["pjax:end","pjax:success","pjax:complete","turbo:load","turbolinks:load","swup:pageView","barba:after-enter"];_lpPE.forEach(function(e){document.addEventListener(e,init);window.addEventListener(e,init);});' : '')
                . '})();'
                . '</script>';
            // 将 apply form <style> 保留并在其后追加 <script>，
            // <style> 确保 PJAX 通过 innerHTML 替换时 CSS 立即生效，
            // <script> 将样式迁移到 <head> 并注册 PJAX 重注入监听。
            if (preg_match('/<style id="links-plus-apply-style">(.*?)<\/style>/s', $assetsBlock, $_m)) {
                $_cssCnt = $_m[1];
                $_cssJ   = json_encode($_cssCnt);
                $_jsInj  = $_m[0] . '<script>(function(){'
                    . 'var i="links-plus-apply-style";var c=' . $_cssJ . ';'
                    . 'function inj(){var el=document.getElementById(i);if(el&&el.parentNode===document.head)return;if(el)el.parentNode.removeChild(el);var s=document.createElement("style");s.id=i;s.textContent=c;(document.head||document.documentElement).appendChild(s);}'
                    . 'inj();'
                    . 'var lk="__lpCssL_apply";'
                    . 'if(!window[lk]){window[lk]=true;var ee=["pjax:end","pjax:success","pjax:complete","turbo:load","turbolinks:load","swup:pageView","barba:after-enter"];ee.forEach(function(e){document.addEventListener(e,inj);window.addEventListener(e,inj);});}'
                    . '})()</script>';
                $assetsBlock = str_replace($_m[0], $_jsInj, $assetsBlock);
            }
        }

        $actionEsc     = htmlspecialchars($actionUrl,        ENT_QUOTES, 'UTF-8');
        $sortEsc       = htmlspecialchars($defaultSort,      ENT_QUOTES, 'UTF-8');
        $tokenEsc      = htmlspecialchars($applyToken,       ENT_QUOTES, 'UTF-8');
        $submitTextEsc = htmlspecialchars(_t('提交友链申请'), ENT_QUOTES, 'UTF-8');
        $titleText     = $customTitle !== ''
            ? htmlspecialchars($customTitle, ENT_QUOTES, 'UTF-8')
            : htmlspecialchars(_t('接受友链申请'), ENT_QUOTES, 'UTF-8');
        $descText      = $customDesc !== ''
            ? htmlspecialchars($customDesc, ENT_QUOTES, 'UTF-8')
            : htmlspecialchars(_t('名称 / 地址 / 图片为必填，提交后将进入待审核队列。'), ENT_QUOTES, 'UTF-8');

        $poweredHtml = '<p class="links-plus-apply-powered">Powered by <a href="https://see.lhl.one/typecho-linksplus" target="_blank">Links+</a></p>';

        $chevronSvg = '<svg class="links-plus-apply-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="5 8 10 13 15 8"/></svg>';

        $innerForm = '<form class="links-plus-apply-form" method="post" action="' . $actionEsc . '" data-lp-ajax="1">'
            . '<div class="links-plus-apply-msg-area" style="display:none"></div>'
            . '<input type="hidden" name="do" value="apply-submit" />'
            . '<input type="hidden" name="apply_token" value="' . $tokenEsc . '" />'
            . '<input type="hidden" name="sort" value="' . $sortEsc . '" />'
            . $honeypotHtml
            . $fieldHtml
            . '<button class="links-plus-apply-submit" type="submit">' . $submitTextEsc . '</button>'
            . $poweredHtml
            . '<div class="lp-success-overlay" role="status"></div>'
            . '</form>';

        $finalForm = '';
        if ($isPopup) {
            static $popupCount = 0;
            $popupCount++;
            $modalId  = 'lp-apply-modal-' . $popupCount;
            $btnEsc   = htmlspecialchars(_t('申请友链'), ENT_QUOTES, 'UTF-8');
            $closeEsc = htmlspecialchars(_t('关闭'),     ENT_QUOTES, 'UTF-8');
            $finalForm = '<div' . $themeAttr . '>'
                . '<button class="' . $popupBtnCls . '" type="button" data-lp-popup-open="' . $modalId . '">' . $btnEsc . '</button>'
                . '<div class="lp-apply-modal-overlay" id="' . $modalId . '" role="dialog" aria-modal="true" aria-label="' . $titleText . '">'
                . '<div class="lp-apply-modal">'
                . '<button class="lp-apply-modal-close" type="button" data-lp-popup-close aria-label="' . $closeEsc . '">&times;</button>'
                . '<div class="links-plus-apply-title">' . $titleText . '</div>'
                . '<div class="links-plus-apply-desc">' . $descText . '</div>'
                . $statusHtml
                . $innerForm
                . '</div></div>'
                . '</div>';
        } else {
            $finalForm = '<div' . $themeAttr . '>'
                . '<details class="links-plus-apply"' . ($defaultOpen ? ' open' : '') . '>'
                . '<summary class="links-plus-apply-summary">'
                . '<span class="links-plus-apply-title">' . $titleText . '</span>'
                . $chevronSvg
                . '</summary>'
                . '<div class="links-plus-apply-body">'
                . '<div class="links-plus-apply-desc">' . $descText . '</div>'
                . $statusHtml
                . $innerForm
                . '</div>'
                . '</details>'
                . '</div>';
        }

        return $assetsBlock . $finalForm;
    }

    /**
     * 跨方法传递当前渲染模板名（用于 apply 跟随模式）
     * 不传参数时返回当前存储的模板名，传入 $tpl 时设置并返回。
     */
    private static function activeRenderTplStore($tpl = null)
    {
        static $activeTpl = '';
        if ($tpl !== null) {
            $activeTpl = (string)$tpl;
        }
        return $activeTpl;
    }

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $info = Links_Plugin::linksInstall();
        try {
            $menuIndex = Helper::addMenu('Links Plus');
            Helper::addPanel($menuIndex, 'Links/manage-links.php', _t('友情链接'), _t('管理友情链接'), 'administrator');
        } catch (Exception $e) {
            Helper::addPanel(3, 'Links/manage-links.php', _t('友情链接'), _t('管理友情链接'), 'administrator');
        } catch (Throwable $e) {
            Helper::addPanel(3, 'Links/manage-links.php', _t('友情链接'), _t('管理友情链接'), 'administrator');
        }
        
    Helper::addAction('links-edit', 'Links_Action');
    Helper::addAction('links-apply', 'Links_Action');
        // 注册短代码和标签解析钩子
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Links_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Links_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = array('Links_Plugin', 'parse');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('Links_Plugin', 'renderEditorTool');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('Links_Plugin', 'renderEditorTool');
        // 在 <head> 中预注入模板 CSS，确保 PJAX 导航时 CSS 始终可用
        Typecho_Plugin::factory('Widget_Archive')->header = array('Links_Plugin', 'injectHeadAssets');
        // Typecho_Plugin::factory('Widget_Archive')->callLinks = array('Links_Plugin', 'output_str');
        return _t($info);
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
    Helper::removeAction('links-edit');
    Helper::removeAction('links-apply');
        try {
            $menuIndex = Helper::removeMenu('Links Plus');
            if ($menuIndex !== null) {
                Helper::removePanel($menuIndex, 'Links/manage-links.php');
            }
        } catch (Exception $e) {
            // ignore
        } catch (Throwable $e) {
            // ignore
        }

        // 兼容旧注册方式
        Helper::removePanel(3, 'Links/manage-links.php');
        
        // 移除短代码和标签解析钩子
        // （注意：Typecho 没有提供直接的 removeHook 方法，这里仅作说明）
    }

    /**
     * 是否启用了 AdminBeautify
     */
    public static function isAdminBeautifyEnabled()
    {
        return class_exists('Typecho_Plugin') && Typecho_Plugin::exists('AdminBeautify');
    }

    /**
     * 获取 Links 运行所需的关键钩子
     *
     * @return array<string,string>
     */
    public static function getRequiredRuntimeHooks()
    {
        return array(
            'Widget_Abstract_Contents:contentEx' => _t('正文解析 contentEx'),
            'Widget_Abstract_Contents:excerptEx' => _t('摘要解析 excerptEx'),
            'Widget_Abstract_Comments:contentEx' => _t('评论解析 contentEx'),
            'Widget_Archive:header' => _t('前台 head 资源注入 header（PJAX 兼容）'),
            'admin/write-post.php:bottom' => _t('文章编辑器按钮'),
            'admin/write-page.php:bottom' => _t('页面编辑器按钮'),
        );
    }

    /**
     * 获取 Links 运行所需的 Action 注册
     *
     * @return array<string,string>
     */
    public static function getRequiredRuntimeActions()
    {
        return array(
            'links-edit' => _t('后台管理 Action（links-edit）'),
            'links-apply' => _t('前台申请 Action（links-apply）'),
        );
    }

    /**
     * 检查当前插件是否缺少关键钩子
     *
     * @return array<string,array>  key => ['ok'=>bool,'label'=>string] — 仅返回缺失项
     */
    public static function getMissingRuntimeHooks()
    {
        if (!class_exists('Typecho_Plugin')) {
            return array();
        }

        $export = array();
        try {
            $export = Typecho_Plugin::export();
        } catch (Exception $e) {
        } catch (Throwable $e) {
        }

        $pluginHandles = array();
        if (isset($export['activated']['Links']['handles']) && is_array($export['activated']['Links']['handles'])) {
            $pluginHandles = $export['activated']['Links']['handles'];
        }

        // 期望的回调类（hookKey => expectedClass）
        $expectedCallbacks = array(
            'Widget_Abstract_Contents:contentEx'  => 'Links_Plugin',
            'Widget_Abstract_Contents:excerptEx'  => 'Links_Plugin',
            'Widget_Abstract_Comments:contentEx'  => 'Links_Plugin',
            'Widget_Archive:header'              => 'Links_Plugin',
            'admin/write-post.php:bottom'         => 'Links_Plugin',
            'admin/write-page.php:bottom'         => 'Links_Plugin',
        );

        $items = array();
        foreach (self::getRequiredRuntimeHooks() as $handleKey => $label) {
            $cb = isset($pluginHandles[$handleKey]) ? $pluginHandles[$handleKey] : null;
            $ok = false;
            if ($cb !== null && !empty($cb)) {
                $expectedClass = isset($expectedCallbacks[$handleKey]) ? $expectedCallbacks[$handleKey] : null;
                if ($expectedClass === null) {
                    $ok = true;
                } elseif (is_array($cb) && isset($cb[0]) && $cb[0] === $expectedClass) {
                    $ok = true;
                } else {
                    if (is_array($cb)) {
                        foreach ($cb as $entry) {
                            if (is_array($entry) && isset($entry[0]) && $entry[0] === $expectedClass) {
                                $ok = true;
                                break;
                            }
                        }
                    }
                }
            }
            if (!$ok) {
                $items[$handleKey] = array('ok' => false, 'label' => $label);
            }
        }

        // Action 注册检查
        try {
            $options = Typecho_Widget::widget('Widget_Options');
            $actionTable = isset($options->actionTable) && is_array($options->actionTable) ? $options->actionTable : array();
            foreach (self::getRequiredRuntimeActions() as $actionName => $label) {
                $ok = !empty($actionTable[$actionName]) && trim((string)$actionTable[$actionName]) === 'Links_Action';
                if (!$ok) {
                    $items['action:' . $actionName] = array('ok' => false, 'label' => $label);
                }
            }
        } catch (Exception $e) {
            foreach (self::getRequiredRuntimeActions() as $actionName => $label) {
                $items['action:' . $actionName] = array('ok' => false, 'label' => $label . ' (无法读取)');
            }
        }

        // 数据库表存在性检查
        $tableExists = true;
        $prefix = '';
        try {
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            try {
                $result = $db->query('SHOW TABLES LIKE \'' . $prefix . 'links\'');
                $tableExists = (bool)$db->fetchRow($result);
            } catch (Exception $e) {
                // SQLite fallback
                $result = $db->query('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'' . $prefix . 'links\'');
                $tableExists = (bool)$db->fetchRow($result);
            }
        } catch (Exception $e) {
            $tableExists = true; // 无法判断时不误报
        }
        if (!$tableExists) {
            $items['db:links_table'] = array('ok' => false, 'label' => _t('数据库表（' . $prefix . 'links）'));
        }

        return $items;
    }

    /**
     * 输出“需要重新启用插件”的提示
     */
    public static function renderRuntimeHookNotice()
    {
        $missing = self::getMissingRuntimeHooks();
        if (empty($missing)) {
            return '';
        }

        $listHtml = '';
        foreach ($missing as $key => $item) {
            $listHtml .= '<li style="padding:4px 0;display:flex;align-items:center;gap:6px">'
                . '<span style="color:#ef4444;font-size:16px;line-height:1">&#10007;</span>'
                . '<span>' . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</span>'
                . '</li>';
        }

        $isDbMissing = isset($missing['db:links_table']);
        $isHookMissing = !$isDbMissing || count($missing) > 1;

        $detail = '';
        if ($isHookMissing) {
            $detail .= '<p style="margin:8px 0 4px">运行时钩子/Action 未完整注册，通常在更新插件代码后未重新激活时出现。'
                . '<strong>请先禁用 Links Plus，再重新启用一次。</strong></p>';
        }
        if ($isDbMissing) {
            $detail .= '<p style="margin:8px 0 4px">数据库表丢失或未创建，可尝试<strong>禁用后重新启用</strong>插件以重建表结构。'
                . '若已有数据请勿直接重装，联系管理员手动恢复。</p>';
        }

        return '<div class="md3-card" style="border-color:rgba(239,68,68,.35);background:#fff5f5">'
            . '<div class="md3-title" style="color:#b91c1c">&#9888; 插件完整性检查失败</div>'
            . '<div class="md3-body">'
            . '<ul style="margin:0;padding:0 0 0 4px;list-style:none">' . $listHtml . '</ul>'
            . $detail
            . '</div></div>';
    }

    /**
     * 写作页工具栏按钮：插入 [LinksPlus /]
     */
    public static function renderEditorTool()
    {
        $isAdminBeautify = self::isAdminBeautifyEnabled();
        $buttonInner = $isAdminBeautify
            ? '<i class="material-icons-round" aria-hidden="true">groups</i>'
            : '<svg t="1779027635144" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="8208" width="18" height="18" aria-hidden="true"><path d="M518.966571 653.825634l0-6.70855c89.791366-64.161263 89.447337-175.282379 89.275323-265.589787l0-13.589115c0-130.730724-78.782463-208.653116-210.717285-208.653116l-6.70855 0c-126.602385 0-196.2681 74.138082-196.2681 208.653116 0 67.945574 0 205.728876 103.552495 279.178901l0 5.332437c-120.065849 17.029397-232.735092 66.225433-232.735092 144.663867 0 109.056946 107.680833 162.037292 329.062993 162.037292 283.30724 0 342.824122-88.071225 342.824122-162.037292C737.252478 713.170502 598.437091 669.13489 518.966571 653.825634z" fill="#444" p-id="8209"></path><path d="M929.564253 767.698975c-17.545439 0-31.82261-14.277171-31.994625-31.82261-0.344028-23.393919-101.660339-88.415253-197.300185-99.768184-16.169326-1.892155-28.210314-15.653284-28.210314-31.82261l0-66.225433c0-9.804804 4.472367-18.921552 12.040988-24.942046 54.012431-43.175542 94.951789-137.26726 94.951789-218.801949 0-109.917017-74.310096-132.966907-136.579204-132.966907-17.717453 0-31.994625-14.277171-31.994625-31.994625s14.277171-31.994625 31.994625-31.994625c123.678145 0 200.568453 75.514195 200.568453 197.128171 0 94.779775-44.207626 200.396439-106.992777 258.365194l0 24.598018c91.855535 19.609609 225.682513 83.942886 225.682513 158.59701 0 17.545439-14.277171 31.650596-31.82261 31.82261C929.736267 767.698975 929.736267 767.698975 929.564253 767.698975z" fill="#444" p-id="8210"></path></svg>';
        $shortcode = '[LinksPlus /]';
?>
<script>
(function () {
    var shortcode = <?php echo json_encode($shortcode); ?>;
    var buttonHtml = <?php echo json_encode($buttonInner); ?>;

    function parseLeft(value) {
        var num = parseInt(value, 10);
        return isNaN(num) ? null : num;
    }

    function insertAtCursor(textarea, value) {
        var text = window.jQuery(textarea);
        if (text.length && typeof text.replaceSelection === 'function') {
            var sel = text.getSelection();
            var offset = (sel ? sel.start : 0) + value.length;
            text.replaceSelection(value);
            text.setSelection(offset, offset);
            return;
        }

        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || 0;
        var oldValue = textarea.value || '';
        textarea.value = oldValue.slice(0, start) + value + oldValue.slice(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + value.length;
    }

    function bindInsert(trigger) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            var textarea = document.getElementById('text');
            if (!textarea) {
                return;
            }
            insertAtCursor(textarea, shortcode);
        });
    }

    function getLastButtonLeft(row) {
        var buttons = row.querySelectorAll('.wmd-button');
        var maxLeft = 0;
        for (var i = 0; i < buttons.length; i++) {
            var left = parseLeft(buttons[i].style.left);
            if (left !== null && left > maxLeft) {
                maxLeft = left;
            }
        }
        return maxLeft;
    }

    function ensureButton() {
        if (document.getElementById('wmd-mirages-linksplus-button') || document.getElementById('linksplus-shortcode-button')) {
            return;
        }

        var row = document.getElementById('wmd-button-row');
        if (row) {
            var li = document.createElement('li');
            li.className = 'wmd-button';
            li.id = 'wmd-mirages-linksplus-button';
            li.title = '插入 LinksPlus 短代码';
            li.setAttribute('aria-label', '插入 LinksPlus 短代码');
            li.style.left = (getLastButtonLeft(row) + 25) + 'px';
            li.innerHTML = buttonHtml;
            row.appendChild(li);
            bindInsert(li);
            return;
        }

        var slug = document.querySelector('.url-slug');
        if (slug && slug.parentNode) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.id = 'linksplus-shortcode-button';
            btn.className = 'btn btn-xs';
            btn.innerHTML = '<span class="linksplus-editor-icon">' + buttonHtml + '</span><span class="linksplus-editor-label">LinksPlus</span>';
            btn.style.marginRight = '5px';
            slug.parentNode.insertBefore(btn, slug.nextSibling);
            bindInsert(btn);
        }
    }

    function ensureStyle() {
        if (document.getElementById('linksplus-editor-tool-style')) {
            return;
        }
        var style = document.createElement('style');
        style.id = 'linksplus-editor-tool-style';
        
        var mdRadiusVar = '';
        try {
            mdRadiusVar = window.getComputedStyle(document.documentElement).getPropertyValue('--md-radius-full').trim();
        } catch (e) {
            mdRadiusVar = '';
        }
        var hasAdminBeautify = document.body.classList.contains('ab-write-page') ||
                               !!document.querySelector('.ab-inner-pill') ||
                               !!mdRadiusVar;
        
        var styleText = '' +
            '#wmd-mirages-linksplus-button svg{display:block!important;width:18px!important;height:18px!important}' +
            '#wmd-mirages-linksplus-button .material-icons-round{font-size:18px!important;line-height:18px!important}' +
            '#linksplus-shortcode-button{display:inline-flex!important;align-items:center!important;gap:4px!important}' +
            '#linksplus-shortcode-button .linksplus-editor-icon{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:18px!important;height:18px!important}' +
            '#linksplus-shortcode-button .linksplus-editor-icon svg{display:block!important;width:18px!important;height:18px!important}' +
            '#linksplus-shortcode-button .material-icons-round{font-size:18px!important;line-height:18px!important;color:inherit!important}' +
            '#linksplus-shortcode-button .linksplus-editor-label{line-height:1!important}';
        
        if (hasAdminBeautify) {
            styleText += '' +
                'body.ab-write-page #wmd-mirages-linksplus-button,.ab-inner-pill #wmd-mirages-linksplus-button{color:var(--md-on-surface-variant)!important}' +
                'body.ab-write-page #wmd-mirages-linksplus-button:hover,.ab-inner-pill #wmd-mirages-linksplus-button:hover{background:color-mix(in srgb,var(--md-on-surface-variant) 8%,transparent)!important;color:var(--md-on-surface)!important}' +
                'body.ab-write-page #wmd-mirages-linksplus-button:active,.ab-inner-pill #wmd-mirages-linksplus-button:active{background:color-mix(in srgb,var(--md-on-surface-variant) 16%,transparent)!important}' +
                'body.ab-write-page #wmd-mirages-linksplus-button svg path,.ab-inner-pill #wmd-mirages-linksplus-button svg path{fill:currentColor!important}' +
                'body.ab-write-page #wmd-mirages-linksplus-button:hover svg path,.ab-inner-pill #wmd-mirages-linksplus-button:hover svg path{fill:currentColor!important}' +
                '.ab-inner-pill #wmd-mirages-linksplus-button{border-radius:var(--md-radius-full)!important;color:var(--md-on-surface-variant)!important}' +
                '.ab-inner-pill #wmd-mirages-linksplus-button:hover{background:color-mix(in srgb,var(--md-on-surface-variant) 8%,transparent)!important;color:var(--md-on-surface)!important}' +
                '.ab-inner-pill #wmd-mirages-linksplus-button:active{background:color-mix(in srgb,var(--md-on-surface-variant) 16%,transparent)!important}' +
                '.ab-inner-pill #wmd-mirages-linksplus-button svg path{fill:currentColor!important}' +
                '.ab-inner-pill #wmd-mirages-linksplus-button:hover svg path{fill:currentColor!important}';
        } else {
            styleText += '' +
                '#wmd-mirages-linksplus-button{display:inline-block!important;margin-right:4px!important;padding:3px!important;cursor:pointer!important;vertical-align:middle!important;border-radius:2px!important}' +
                '#wmd-mirages-linksplus-button:hover{background-color:#E9E9E6!important}' +
                '#wmd-mirages-linksplus-button svg path{fill:#444!important}' +
                '#wmd-mirages-linksplus-button:hover svg path{fill:#555!important}' +
                '#wmd-mirages-linksplus-button .material-icons-round{color:#444!important}' +
                '#wmd-mirages-linksplus-button:hover .material-icons-round{color:#555!important}';
        }
        
        style.textContent = styleText;
        document.head.appendChild(style);
    }

    function init() {
        ensureStyle();
        ensureButton();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    document.addEventListener('ab:pageload', init);
})();
</script>
<?php
    }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        echo '
<style>
:root {
    --md-primary: #0061a4;
    --md-on-primary: #ffffff;
    --md-primary-container: #d1e4ff;
    --md-on-primary-container: #001d36;
    --md-surface: #fdfcff;
    --md-surface-variant: #e1e2ec;
    --md-surface-container: #f3f4f7;
    --md-outline: #74777f;
    --md-outline-variant: rgba(0,0,0,.12);
    --md-radius: 12px;
}
.md3-wrap {
    max-width: 1080px;
}
.md3-card {
    background: var(--md-surface);
    border-radius: var(--md-radius);
    padding: 24px;
    box-shadow: 0 1px 2px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.12);
    margin-bottom: 24px;
    border: 1px solid var(--md-outline-variant);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.md3-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--md-on-primary-container);
    margin-bottom: 16px;
    padding-left: 12px;
    border-left: 4px solid var(--md-primary);
    line-height: 1.2;
}
.md3-subtitle {
    font-size: .95rem;
    font-weight: 600;
    color: #374151;
    margin: 18px 0 10px;
}
.md3-body {
    color: #58606b;
    line-height: 1.75;
}
.md3-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 980px) {
    .md3-grid.two {
        grid-template-columns: 1fr 1fr;
    }
}
.md3-header-actions {
    display: flex;
    gap: 16px;
    margin-top: 16px;
    flex-wrap: wrap;
}
.lp-rewrite-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 12px;
}
.lp-rewrite-actions .md3-btn-text {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
@media (max-width: 640px) {
    .lp-rewrite-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .lp-rewrite-actions .md3-btn-text {
        width: 100%;
        box-sizing: border-box;
        text-align: center;
    }
}
.md3-btn-text {
    color: var(--md-primary);
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 20px;
    background-color: var(--md-primary-container);
    transition: opacity 0.2s;
}
.md3-btn-text:hover {
    opacity: 0.9;
    color: var(--md-primary);
    text-decoration: none;
}
/* 同步模板弹窗按钮：AdminBeautify 适配 */
.lp-md3-overlay .md3-btn-text {
    border: 1px solid rgba(0,97,164,.18);
    border-radius: 999px;
    background-color: #dbeafe;
    color: #0b3c68;
    transition: background-color .2s, color .2s, border-color .2s, box-shadow .2s;
}
.lp-md3-overlay .md3-btn-text:hover {
    background-color: #cfe3ff;
    color: #072f55;
    border-color: rgba(0,97,164,.28);
}
.lp-md3-overlay .md3-btn-text:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,97,164,.18);
}
.lp-md3-overlay .md3-btn-text[style*="var(--md-primary)"] {
    border-color: transparent;
    background-color: var(--md-primary) !important;
    color: var(--md-on-primary) !important;
}

[data-theme="dark"] .lp-md3-overlay .md3-btn-text,
body.dark .lp-md3-overlay .md3-btn-text,
body.dark-mode .lp-md3-overlay .md3-btn-text,
html.dark .lp-md3-overlay .md3-btn-text,
html.dark-mode .lp-md3-overlay .md3-btn-text {
    background-color: #283a52;
    border-color: rgba(180,210,255,.28);
    color: #d7e8ff;
}
[data-theme="dark"] .lp-md3-overlay .md3-btn-text:hover,
body.dark .lp-md3-overlay .md3-btn-text:hover,
body.dark-mode .lp-md3-overlay .md3-btn-text:hover,
html.dark .lp-md3-overlay .md3-btn-text:hover,
html.dark-mode .lp-md3-overlay .md3-btn-text:hover {
    background-color: #324a67;
    border-color: rgba(180,210,255,.38);
    color: #eef5ff;
}
[data-theme="dark"] .lp-md3-overlay .md3-btn-text[style*="var(--md-primary)"],
body.dark .lp-md3-overlay .md3-btn-text[style*="var(--md-primary)"],
body.dark-mode .lp-md3-overlay .md3-btn-text[style*="var(--md-primary)"],
html.dark .lp-md3-overlay .md3-btn-text[style*="var(--md-primary)"],
html.dark-mode .lp-md3-overlay .md3-btn-text[style*="var(--md-primary)"] {
    background-color: #0f4a7f !important;
    color: #eaf3ff !important;
    border-color: transparent;
}
.lp-update-note {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid var(--md-outline-variant);
    background: var(--md-surface-container);
    color: #374151;
    font-size: 13px;
    line-height: 1.65;
}
.lp-update-note a { color: var(--md-primary); text-decoration: none; font-weight: 600; }
.lp-update-note a:hover { text-decoration: underline; }
.lp-update-note.is-ok { border-color: rgba(0,97,164,.25); }
.lp-update-note.is-warn { border-color: rgba(245,158,11,.35); }
.lp-update-note.is-err { border-color: rgba(239,68,68,.35); }
.lp-update-note .lp-update-title { font-weight: 700; margin-bottom: 4px; }
.md3-btn-text.is-disabled { opacity: .55; pointer-events: none; }
.md3-chip {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    background: var(--md-surface-container);
    border: 1px solid var(--md-outline-variant);
    color: #374151;
    font-size: 12px;
    gap: 6px;
}
.md3-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.md3-table th {
    text-align: left;
    padding: 12px 16px;
    color: #555;
    background-color: var(--md-surface-variant);
    font-weight: 600;
    border-radius: 4px;
}
.md3-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    color: #333;
    
}
.md3-table tr:last-child td {
    border-bottom: none;
}
.field-tag {
    display: inline-block;
    padding: 2px 8px;
    background: #f0f0f0;
    border-radius: 4px;
    font-family: monospace;
    color: #d63384;
}
/* 覆盖 Typecho 原生表单样式 */
.typecho-option-submit button {
    background-color: var(--md-primary) !important;
    border-radius: 20px !important;
    padding: 0 24px !important; 
    height: 40px !important;
}
textarea, input[type="text"], select {
    border: 1px solid var(--md-outline-variant);
    border-radius: 12px;
    padding: 10px 12px;
    height:auto;
    transition: box-shadow .15s, border-color .15s;
    background: #fff;
}
textarea:focus, input[type="text"]:focus, select:focus {
    border-color: rgba(0, 97, 164, .45);
    box-shadow: 0 0 0 3px rgba(0, 97, 164, 0.18);
    outline: none;
}
.typecho-option {
    border-bottom: 1px dashed rgba(0,0,0,.06);
}
.typecho-option:last-child { border-bottom: 0; }
.typecho-option label {
    font-weight: 600;
    color: #1f2937;
}
.description {
    color: #6b7280;
}
.md3-fold-card {
    padding: 0;
    overflow: hidden;
}
.md3-fold {
    display: block;
}
.md3-fold > summary {
    list-style: none;
    cursor: pointer;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    user-select: none;
}
.md3-fold > summary::-webkit-details-marker {
    display: none;
}
.md3-fold-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--md-on-primary-container);
    padding-left: 12px;
    border-left: 4px solid var(--md-primary);
    line-height: 1.2;
}
.md3-fold-hint {
    color: #6b7280;
    font-size: 12px;
    white-space: nowrap;
}
.md3-fold-body {
    padding: 16px 20px 16px;
    border-top: 1px solid var(--md-outline-variant);
}
.md3-fold-body > .typecho-option,
.md3-fold-body > .md3-card {
    margin-bottom: 14px;
}
.md3-fold-body > .typecho-option:last-child,
.md3-fold-body > .md3-card:last-child {
    margin-bottom: 0;
}

/* AdminBeautify / 通用暗色模式适配 */
[data-theme="dark"] .md3-wrap,
body.dark .md3-wrap,
body.dark-mode .md3-wrap,
html.dark .md3-wrap,
html.dark-mode .md3-wrap {
    --md-surface: #1f232b;
    --md-surface-variant: #2a2f3a;
    --md-surface-container: #242934;
    --md-outline-variant: rgba(255,255,255,.12);
}

[data-theme="dark"] .md3-card,
body.dark .md3-card,
body.dark-mode .md3-card,
html.dark .md3-card,
html.dark-mode .md3-card {
    background: #1f232b;
    border-color: rgba(255,255,255,.12);
    box-shadow: 0 1px 2px rgba(0,0,0,.45), 0 1px 3px rgba(0,0,0,.35);
}

[data-theme="dark"] .md3-title,
[data-theme="dark"] .md3-fold-title,
body.dark .md3-title,
body.dark .md3-fold-title,
body.dark-mode .md3-title,
body.dark-mode .md3-fold-title,
html.dark .md3-title,
html.dark .md3-fold-title,
html.dark-mode .md3-title,
html.dark-mode .md3-fold-title {
    color: #dbe8ff;
}

[data-theme="dark"] .md3-body,
[data-theme="dark"] .description,
[data-theme="dark"] .md3-fold-hint,
body.dark .md3-body,
body.dark .description,
body.dark .md3-fold-hint,
body.dark-mode .md3-body,
body.dark-mode .description,
body.dark-mode .md3-fold-hint,
html.dark .md3-body,
html.dark .description,
html.dark .md3-fold-hint,
html.dark-mode .md3-body,
html.dark-mode .description,
html.dark-mode .md3-fold-hint {
    color: #b8c0cf;
}

[data-theme="dark"] .md3-table th,
body.dark .md3-table th,
body.dark-mode .md3-table th,
html.dark .md3-table th,
html.dark-mode .md3-table th {
    background-color: #2a2f3a !important;
    color: #d7deea !important;
    border-bottom-color: rgba(255,255,255,.12) !important;
}

[data-theme="dark"] .md3-table td,
body.dark .md3-table td,
body.dark-mode .md3-table td,
html.dark .md3-table td,
html.dark-mode .md3-table td {
    color: #c5cedd;
    border-bottom-color: rgba(255,255,255,.08);
}

[data-theme="dark"] .field-tag,
body.dark .field-tag,
body.dark-mode .field-tag,
html.dark .field-tag,
html.dark-mode .field-tag {
    background: #323a48;
    color: #ffb4d0;
}

[data-theme="dark"] .md3-chip,
body.dark .md3-chip,
body.dark-mode .md3-chip,
html.dark .md3-chip,
html.dark-mode .md3-chip {
    background: #2a2f3a;
    border-color: rgba(255,255,255,.14);
    color: #dbe3f0;
}

[data-theme="dark"] textarea,
[data-theme="dark"] input[type="text"],
[data-theme="dark"] select,
body.dark textarea,
body.dark input[type="text"],
body.dark select,
body.dark-mode textarea,
body.dark-mode input[type="text"],
body.dark-mode select,
html.dark textarea,
html.dark input[type="text"],
html.dark select,
html.dark-mode textarea,
html.dark-mode input[type="text"],
html.dark-mode select {
    background: #171b22;
    color: #dde5f3;
    border-color: rgba(255,255,255,.16);
}

[data-theme="dark"] textarea:focus,
[data-theme="dark"] input[type="text"]:focus,
[data-theme="dark"] select:focus,
body.dark textarea:focus,
body.dark input[type="text"]:focus,
body.dark select:focus,
body.dark-mode textarea:focus,
body.dark-mode input[type="text"]:focus,
body.dark-mode select:focus,
html.dark textarea:focus,
html.dark input[type="text"]:focus,
html.dark select:focus,
html.dark-mode textarea:focus,
html.dark-mode input[type="text"]:focus,
html.dark-mode select:focus {
    border-color: rgba(144,202,249,.6);
    box-shadow: 0 0 0 3px rgba(144,202,249,.22);
}

[data-theme="dark"] .typecho-option,
body.dark .typecho-option,
body.dark-mode .typecho-option,
html.dark .typecho-option,
html.dark-mode .typecho-option {
    border-bottom-color: rgba(255,255,255,.09);
}

[data-theme="dark"] .typecho-option label,
body.dark .typecho-option label,
body.dark-mode .typecho-option label,
html.dark .typecho-option label,
html.dark-mode .typecho-option label {
    color: #e4ebf7;
}

[data-theme="dark"] .md3-btn-text,
body.dark .md3-btn-text,
body.dark-mode .md3-btn-text,
html.dark .md3-btn-text,
html.dark-mode .md3-btn-text {
    background-color: #234261;
    color: #d8e9ff;
}

[data-theme="dark"] .md3-fold-body,
body.dark .md3-fold-body,
body.dark-mode .md3-fold-body,
html.dark .md3-fold-body,
html.dark-mode .md3-fold-body {
    border-top-color: rgba(255,255,255,.1);
}

.lp-pjax-pre {
    background: #f3f4f6;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    line-height: 1.7;
    overflow-x: auto;
    margin: 0 0 8px;
    color: #1f2937;
    border: 1px solid rgba(0,0,0,.06);
}

[data-theme="dark"] .lp-pjax-pre,
body.dark .lp-pjax-pre,
body.dark-mode .lp-pjax-pre,
html.dark .lp-pjax-pre,
html.dark-mode .lp-pjax-pre {
    background: #131720;
    color: #c8d6ee;
    border-color: rgba(255,255,255,.1);
}
    </style>
'
. self::renderRuntimeHookNotice()
. <<<LINKS_PLUS_UPDATE_JS
<script>
(function(){
    var REPO = "lhl77/Typecho-Plugin-LinksPlus";
    // 当前版本（按 tag 口径对比）
    var CURRENT = "v1.4.1";

    function normalizeTag(tag){
        tag = (tag || "").toString().trim();
        if(!tag) return "";
        return tag.replace(/^refs\/tags\//, "");
    }

    function tagToVersion(tag){
        tag = normalizeTag(tag);
        return tag.replace(/^[vV]/, "");
    }

    function cmp(a, b){
        a = (a || "").toString();
        b = (b || "").toString();
        var as = a.split('.');
        var bs = b.split('.');
        var n = Math.max(as.length, bs.length);
        for(var i=0;i<n;i++){
            var ai = as[i] || "0";
            var bi = bs[i] || "0";
            var an = /^\d+$/.test(ai) ? parseInt(ai, 10) : null;
            var bn = /^\d+$/.test(bi) ? parseInt(bi, 10) : null;
            if(an !== null && bn !== null){
                if(an > bn) return 1;
                if(an < bn) return -1;
            } else {
                if(ai > bi) return 1;
                if(ai < bi) return -1;
            }
        }
        return 0;
    }

    function fetchJson(url, cb){
        var xhr = new XMLHttpRequest();
        xhr.open("GET", url, true);
        xhr.setRequestHeader("Accept", "application/vnd.github+json");
        xhr.onreadystatechange = function(){
            if(xhr.readyState !== 4) return;
            if(xhr.status >= 200 && xhr.status < 300){
                try { cb(null, JSON.parse(xhr.responseText)); } catch(e){ cb(e); }
            } else {
                cb(new Error("HTTP " + xhr.status));
            }
        };
        xhr.send(null);
    }

    function setBtnText(btn, text){
        if(btn){ btn.textContent = text; }
    }

    function setBusy(btn, busy){
        if(!btn) return;
        busy = !!busy;
        btn.setAttribute('aria-disabled', busy ? 'true' : 'false');
        if(busy){
            btn.classList.add('is-disabled');
            btn.dataset.busy = '1';
        } else {
            btn.classList.remove('is-disabled');
            btn.dataset.busy = '';
        }
    }

    function renderNote(host, type, title, html){
        if(!host) return;
        var cls = 'lp-update-note';
        if(type === 'ok') cls += ' is-ok';
        if(type === 'warn') cls += ' is-warn';
        if(type === 'err') cls += ' is-err';
        host.innerHTML = '<div class="' + cls + '">' +
            '<div class="lp-update-title">' + title + '</div>' +
            '<div class="lp-update-body">' + html + '</div>' +
            '</div>';
    }

    document.addEventListener("DOMContentLoaded", function(){
        var btn = document.getElementById("links-plus-check-update");
        if(!btn) return;

        var card = btn.closest ? btn.closest('.md3-card') : null;
        var out = card ? card.querySelector('.lp-update-out') : null;

        btn.addEventListener("click", function(e){
            e.preventDefault();

            if(btn.dataset && btn.dataset.busy === '1') return;
            setBusy(btn, true);

            var api = "https://api.github.com/repos/" + REPO + "/tags?per_page=100";
            var oldText = btn.textContent;
            setBtnText(btn, "检查中...");

            renderNote(out, 'ok', '检查更新', '正在查询 GitHub tags…');

            fetchJson(api, function(err, tags){
                setBtnText(btn, oldText || "检查更新");
                setBusy(btn, false);

                if(err || !Array.isArray(tags)){
                    renderNote(
                        out,
                        'err',
                        '检查失败',
                        '原因：' + (err ? err.message : '响应异常') + '<br>' +
                        '说明：此方案直接从浏览器访问 GitHub API，可能会受网络或 CORS 限制。'
                    );
                    return;
                }

                var latestTag = "";
                var latestVer = "";
                for(var i=0;i<tags.length;i++){
                    var name = tags[i] && tags[i].name ? tags[i].name : "";
                    var tag = normalizeTag(name);
                    var ver = tagToVersion(tag);
                    if(!ver) continue;
                    if(!latestVer || cmp(ver, latestVer) > 0){
                        latestVer = ver;
                        latestTag = tag;
                    }
                }

                if(!latestTag){
                    renderNote(out, 'err', '检查失败', '未发现可用的版本 tag。');
                    return;
                }

                var curVer = tagToVersion(CURRENT);
                var hasUpdate = cmp(latestVer, curVer) > 0;
                var url = "https://github.com/" + REPO + "/releases/tag/" + encodeURIComponent(latestTag);

                if(hasUpdate){
                    renderNote(
                        out,
                        'warn',
                        '发现新版本：' + latestTag,
                        '当前版本：<code>' + CURRENT + '</code><br>' +
                        '最新版本：<code>' + latestTag + '</code><br>' +
                        '<a href="' + url + '" target="_blank" rel="noopener">打开 GitHub 查看</a>'
                    );
                } else {
                    renderNote(
                        out,
                        'ok',
                        '已是最新版本',
                        '当前版本：<code>' + CURRENT + '</code><br>' +
                        '最新版本：<code>' + latestTag + '</code>'
                    );
                }
            });
        });
    });
})();
</script>
LINKS_PLUS_UPDATE_JS
. '



<div class="md3-wrap">

<div class="md3-card">
    <div class="md3-title">友情链接插件 (Links+)</div>
    <div class="md3-body">
        <p>欢迎使用 Links Plus 增强版。您可以在“管理”菜单下找到“友情链接”进行日常操作。</p>
        <p>本插件支持多种输出模式（文字、图片、图文混合、HTML输出），并支持自定义字段扩展。</p>
    </div>
    <div class="lp-update-out"></div>
    <div class="md3-header-actions">
        <a href="' . Typecho_Common::url('extending.php?panel=Links%2Fmanage-links.php', Helper::options()->adminUrl) . '" class="md3-btn-text">管理友链</a>
    <a href="https://github.com/lhl77/Typecho-Plugin-LinksPlus" target="_blank" class="md3-btn-text">GitHub</a>
    <a id="links-plus-check-update" href="#" class="md3-btn-text">检查更新</a>
    <a href="https://blog.lhl.one/artical/902.html" target="_blank" class="md3-btn-text">帮助文档</a>
    <a href="https://github.com/lhl77/Typecho-Plugin-LinksPlus/issues" target="_blank" class="md3-btn-text">反馈</a>
    </div>
</div>

<div class="md3-card">
    <div class="md3-title">模式字符串变量说明</div>
    <div style="overflow-x: auto;">
        <table class="md3-table">
            <thead>
                <tr>
                    <th style="border-radius:5px 0px 0px 5px;" width="30%">变量占位符</th>
                    <th style="border-radius:0px 5px 5px 0px">说明</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><span class="field-tag">{url}</span></td><td>友链的 URL 地址</td></tr>
                <tr><td><span class="field-tag">{name}</span></td><td>友链显示的名称</td></tr>
                <tr><td><span class="field-tag">{description}</span></td><td>友链的描述</td></tr>
                <tr><td><span class="field-tag">{image}</span></td><td>图片地址 (Logo/头像)</td></tr>
                <tr><td><span class="field-tag">{size}</span></td><td>在调用中设置的图片尺寸值 (数字)</td></tr>
                <tr><td><span class="field-tag">{sort}</span></td><td>分类名称</td></tr>
                <tr><td><span class="field-tag">{user}</span></td><td>自定义扩展数据</td></tr>
                <tr><td><span class="field-tag">{lid}</span></td><td>数据库内的 ID 编号</td></tr>
            </tbody>
        </table>
    </div>
</div>



</div>
';
        // 模板选择（文件模板 / 保留高级自定义 textarea 作为兼容）
        $templates = self::listTemplates();
        $tplOptions = array(
            '' => _t('（不使用模板，沿用下方自定义规则/旧配置）'),
        );
        if (!empty($templates)) {
            foreach ($templates as $name => $manifest) {
                $title = isset($manifest['title']) ? (string)$manifest['title'] : $name;
                $tplOptions[$name] = $title;
            }
        }

        // 短代码模板选项（不包含"沿用"选项，必须选择实际模板）
        $tplOptionsShortcode = array();
        if (!empty($templates)) {
            foreach ($templates as $name => $manifest) {
                $title = isset($manifest['title']) ? (string)$manifest['title'] : $name;
                $tplOptionsShortcode[$name] = $title;
            }
        }

        // 友链申请模块模板选项
        $tplOptionsApply = array(
            ''          => _t('内置表单'),
            '__popup__' => _t('弹窗'),
        );

        $selectedText = new Typecho_Widget_Helper_Form_Element_Select(
            'template_text',
            $tplOptions,
            'default-text',
            _t('SHOW_TEXT 使用的模板'),
            _t('选择一个文件模板（templates 目录）。选择后，前台调用 SHOW_TEXT 会优先使用该模板渲染。')
        );
        $form->addInput($selectedText);

        $selectedImg = new Typecho_Widget_Helper_Form_Element_Select(
            'template_img',
            $tplOptions,
            'default-img',
            _t('SHOW_IMG 使用的模板'),
            _t('选择一个文件模板（templates 目录）。选择后，前台调用 SHOW_IMG 会优先使用该模板渲染。')
        );
        $form->addInput($selectedImg);

        $selectedMix = new Typecho_Widget_Helper_Form_Element_Select(
            'template_mix',
            $tplOptions,
            'default-mix',
            _t('SHOW_MIX 使用的模板'),
            _t('选择一个文件模板（templates 目录）。选择后，前台调用 SHOW_MIX 会优先使用该模板渲染。')
        );
        $form->addInput($selectedMix);

        $advHelp = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $advHelp->html(
            '<div class="md3-title">高级：自定义源码规则（兼容旧版本）</div>' .
            '<div class="md3-body">' .
            '<p>当你不想用模板，或需要更细粒度的自定义时，再使用下面三段规则（旧版配置项）。</p>' .
            '</div>'
        );
        $form->addItem($advHelp);

        $pattern_text = new Typecho_Widget_Helper_Form_Element_Textarea(
            'pattern_text',
            null,
            '<li><a href="{url}" title="{title}" target="_blank" rel="noopener">{name}</a></li>',
            _t('SHOW_TEXT 模式源码规则（高级）'),
            _t('当未选择模板时生效。使用 SHOW_TEXT(仅文字) 模式输出时的源码，可按上表规则替换其中字段')
        );
        $form->addInput($pattern_text);
        $pattern_img = new Typecho_Widget_Helper_Form_Element_Textarea(
            'pattern_img',
            null,
            '<li><a href="{url}" title="{title}" target="_blank" rel="noopener"><img src="{image}" alt="{name}" width="{size}" height="{size}" /></a></li>',
            _t('SHOW_IMG 模式源码规则（高级）'),
            _t('当未选择模板时生效。使用 SHOW_IMG(仅图片) 模式输出时的源码，可按上表规则替换其中字段')
        );
        $form->addInput($pattern_img);
        $pattern_mix = new Typecho_Widget_Helper_Form_Element_Textarea(
            'pattern_mix',
            null,
            '<li><a href="{url}" title="{title}" target="_blank" rel="noopener"><img src="{image}" alt="{name}" width="{size}" height="{size}" /><span>{name}</span></a></li>',
            _t('SHOW_MIX 模式源码规则（高级）'),
            _t('当未选择模板时生效。使用 SHOW_MIX(图文混合) 模式输出时的源码，可按上表规则替换其中字段')
        );
        $form->addInput($pattern_mix);
        $dsize = new Typecho_Widget_Helper_Form_Element_Text(
            'dsize',
            NULL,
            '32',
            _t('默认输出图片尺寸'),
            _t('调用时如果未指定尺寸参数默认输出的图片大小(单位px不用填写)')
        );
        $dsize->input->setAttribute('class', 'w-10');
        $form->addInput($dsize->addRule('isInteger', _t('请填写整数数字')));
        

        $temHelp = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $temHelp->html(
            '<div class="md3-title">正文重写</div>
    <div class="md3-body">
        <p>当主题没有通过 <code>$this->content()</code> 输出正文，导致 <code>&lt;links&gt;...&lt;/links&gt;</code> 不解析时，可用“正文重写工具”把正文中的占位符替换为友链 HTML。</p>
        <p>固定占位符：<span class="md3-chip" style="font-weight:bold;">' . self::REWRITE_PLACEHOLDER . '</span></p>
    <span class="md3-chip">建议</span>
        <span style="margin-left:8px">优先使用文件模板（<code>templates/</code>）来管理输出结构；旧版“源码规则”保留兼容。</span><br><br>
    <div class="lp-rewrite-actions">
    <a id="links-plus-get-templates" href="' . Helper::security()->getIndex('/action/links-edit?do=update_templates') . '" class="md3-btn-text links-plus-get-templates-btn">同步Github主题</a>
    <a href="https://blog.lhl.one/artical/902.html#%E4%B8%BB%E9%A2%98" target="_blank" class="md3-btn-text">查看全部主题</a>
    <a href="https://blog.lhl.one/artical/902.html#%E4%B8%BB%E9%A2%98%E5%BC%80%E5%8F%91%E6%96%87%E6%A1%A3" target="_blank" class="md3-btn-text">主题开发文档</a>
    </div>
    
    <div class="lp-update-out" style="margin-top:12px"></div>
        </div>'
        );
        $form->addItem($temHelp);

        // 前端脚本：获取最新主题按钮行为 — 使用 MD3 风格的模态确认框，确认后由服务器端执行下载并覆盖 templates
                $script = <<<'SCRIPT'
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var btns = document.querySelectorAll('.links-plus-get-templates-btn');
            if(!btns || !btns.length) return;

            function createDialog(title, body, okText, cancelText) {
                // 遮罩
                var overlay = document.createElement('div');
                overlay.className = 'lp-md3-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.right = '0';
                overlay.style.bottom = '0';
                overlay.style.background = 'rgba(0,0,0,0.36)';
                overlay.style.zIndex = '9999';
                overlay.style.display = 'flex';
                overlay.style.alignItems = 'center';
                overlay.style.justifyContent = 'center';

                var card = document.createElement('div');
                card.className = 'md3-card';
                card.setAttribute('role','dialog');
                card.style.maxWidth = '560px';
                card.style.margin = '16px';

                var h = document.createElement('div');
                h.className = 'md3-title';
                h.textContent = title;
                card.appendChild(h);

                var b = document.createElement('div');
                b.className = 'md3-body';
                b.style.marginTop = '8px';
                b.innerHTML = body;
                card.appendChild(b);

                var actions = document.createElement('div');
                actions.className = 'md3-header-actions';
                actions.style.marginTop = '18px';

                var cancelBtn = document.createElement('a');
                cancelBtn.href = '#';
                cancelBtn.className = 'md3-btn-text';
                cancelBtn.textContent = cancelText || '取消';
                cancelBtn.style.marginRight = '8px';

                var okBtn = document.createElement('a');
                okBtn.href = '#';
                okBtn.className = 'md3-btn-text';
                okBtn.textContent = okText || '确定';
                okBtn.style.backgroundColor = 'var(--md-primary)';
                okBtn.style.color = 'var(--md-on-primary)';
                okBtn.style.padding = '8px 14px';
                okBtn.style.borderRadius = '12px';

                actions.appendChild(cancelBtn);
                actions.appendChild(okBtn);
                card.appendChild(actions);

                overlay.appendChild(card);

                // 事件
                cancelBtn.addEventListener('click', function(evt){ evt.preventDefault(); document.body.removeChild(overlay); });
                okBtn.addEventListener('click', function(evt){ evt.preventDefault(); document.body.removeChild(overlay); if (typeof overlay._onconfirm === 'function') overlay._onconfirm(); });

                return overlay;
            }

            for (var i = 0; i < btns.length; i++) {
                (function(btn){
                    btn.addEventListener('click', function(e){
                        e.preventDefault();
                        // 构造 MD3 风格对话
                        var dialog = createDialog('同步模板', '<p>将从 GitHub 下载，此操作将用 GitHub 上的模板覆盖本地模板（仅当远端版本较新时会覆盖）。是否继续？</p>', '开始同步', '取消');

                        dialog._onconfirm = function(){
                            var host = btn.closest ? btn.closest('.md3-card') : null;
                            var out = host ? host.querySelector('.lp-update-out') : null;
                            if(out){ out.innerHTML = '<div class="lp-update-note is-working"><div class="lp-update-title">更新中</div><div class="lp-update-body">正在从 GitHub 下载并更新模板，页面将跳转，请稍候…</div></div>'; }
                            try{ btn.setAttribute('aria-disabled','true'); btn.classList.add('is-disabled'); }catch(e){}
                            // 导航到 action 链接，服务器端处理下载/解压/覆盖逻辑
                            window.location.href = btn.getAttribute('href');
                        };

                        document.body.appendChild(dialog);
                        // 聚焦到确认按钮以便无障碍
                        var focusable = dialog.querySelectorAll('.md3-header-actions a');
                        if (focusable && focusable[1]) focusable[1].focus();
                    }, false);
                })(btns[i]);
            }
        });
        </script>
        SCRIPT;
                echo $script;


        /**
         * 按 cid 重写正文输出（绕过主题不走 contentEx 的情况）
         */
    // 说明已在上方 intro card 给出，这里不再重复插入大段说明卡片，避免页面过长。
        
        $rewriteCid = new Typecho_Widget_Helper_Form_Element_Text(
            'rewrite_cids',
            null,
            '',
            _t('需要重写的 cid（可多个）'),
            _t('填写文章/页面 cid，多个用英文逗号分隔，例如：12,34,56')
        );
        $rewriteCid->input->setAttribute('class', 'w-50');
        $form->addInput($rewriteCid);
        
        $rewriteModeOptions = array(
        );
        if (!empty($templates)) {
            foreach ($templates as $name => $manifest) {
                $title = isset($manifest['title']) ? (string)$manifest['title'] : $name;
                $rewriteModeOptions['TPL:' . $name] = $title ;
            }
        }
        $rewritePattern = new Typecho_Widget_Helper_Form_Element_Select(
            'rewrite_pattern',
            $rewriteModeOptions,
            'SHOW_TEXT',
            _t('重写输出主题'),
            _t('把占位符替换成哪种模式输出,也可以直接选择某个文件模板。')
        );
        $form->addInput($rewritePattern);

        // 预览：使用测试友链渲染所选模板的真实 HTML 输出
        $optionsObj = Typecho_Widget::widget('Widget_Options');
        $tplBaseUrl = Typecho_Common::url('usr/plugins/Links/templates/', $optionsObj->siteUrl);

        $tplPreview = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $tplPreview->html(
            '<div class="md3-title">重写主题预览</div>' .
            '<div class="md3-body">' .
            '<div id="rewrite-tpl-preview-html" style="min-height:160px;border-radius:8px;padding:12px;overflow:auto;"></div>' .
            '<div id="rewrite-tpl-preview-title" style="font-size:13px;color:#555;margin-top:8px;text-align:center"></div>' .
            '</div>'
        );
        $form->addItem($tplPreview);

        // 前端脚本：根据选择渲染测试友链的真实 HTML 预览
        $previewScript = <<<'SCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function(){
    var sel = document.querySelector('select[name="rewrite_pattern"]');
    var host = document.getElementById('rewrite-tpl-preview-html');
    var title = document.getElementById('rewrite-tpl-preview-title');
    if(!sel || !host || !title) return;
    var base = '%s';

    var demoLinks = [
        {
            lid: '1',
            name: "LHL's Blog",
            title: '博客',
            description: '作者博客',
            sort: '博客',
            url: 'https://blog.lhl.one',
            image: 'https://smms-vip3.see.you/2025/04/18/KXpf8u5SQYNPkA3.jpg',
            user: ''
        },
        {
            lid: '2',
            name: "LHL's Shop",
            title: '图床',
            description: '聚合图床',
            sort: '图床',
            url: 'https://img.lhl.one',
            image: 'https://smms-vip3.see.you/2026/01/27/ctiYDAXyxkRGbmB.png',
            user: ''
        }
    ];

    function esc(v){
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function requestText(url, cb){
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function(){
            if(xhr.readyState !== 4) return;
            if(xhr.status >= 200 && xhr.status < 300){
                cb(null, xhr.responseText || '');
            } else {
                cb(new Error('HTTP ' + xhr.status));
            }
        };
        xhr.send(null);
    }

    function applyTemplate(tpl, item){
        var out = tpl;
        var map = {
            lid: item.lid,
            name: esc(item.name),
            url: esc(item.url),
            sort: esc(item.sort),
            title: esc(item.title),
            description: esc(item.description),
            image: esc(item.image),
            user: esc(item.user),
            size: '120'
        };
        for(var k in map){
            if(Object.prototype.hasOwnProperty.call(map, k)){
                out = out.replace(new RegExp('\\{' + k + '\\}', 'g'), map[k]);
            }
        }
        return out;
    }

    function wrapHtml(itemsHtml, manifestText){
        try {
            var manifest = JSON.parse(manifestText || '{}');
            var wrapper = manifest && manifest.wrapper ? manifest.wrapper : null;
            if(!wrapper || !wrapper.tag) return itemsHtml;
            var tag = String(wrapper.tag).toLowerCase();
            if(['ul','ol','div'].indexOf(tag) === -1) return itemsHtml;
            var el = document.createElement(tag);
            if(wrapper.class) el.className = String(wrapper.class);
            if(wrapper.id) el.id = String(wrapper.id);
            if(wrapper.attrs && typeof wrapper.attrs === 'object'){
                for(var key in wrapper.attrs){
                    if(Object.prototype.hasOwnProperty.call(wrapper.attrs, key)){
                        el.setAttribute(key, String(wrapper.attrs[key]));
                    }
                }
            }
            el.innerHTML = itemsHtml;
            return el.outerHTML;
        } catch (e) {
            return itemsHtml;
        }
    }

    function injectPreviewCss(name, cssText){
        var styleId = 'lp-rewrite-preview-style';
        var old = document.getElementById(styleId);
        if(old) old.parentNode.removeChild(old);
        if(!cssText) return;
        var el = document.createElement('style');
        el.id = styleId;
        el.textContent = cssText;
        document.head.appendChild(el);
    }

    function injectPreviewJs(scriptId, jsText){
        var old = document.getElementById(scriptId);
        if(old) old.parentNode.removeChild(old);
        if(!jsText) return;
        var el = document.createElement('script');
        el.id = scriptId;
        el.textContent = jsText;
        document.body.appendChild(el);
    }

    function renderTemplate(name){
        var styleId  = 'lp-rewrite-preview-style';
        var scriptId = 'lp-rewrite-preview-script';
        var old = document.getElementById(styleId);
        if(old) old.parentNode.removeChild(old);
        var oldScript = document.getElementById(scriptId);
        if(oldScript) oldScript.parentNode.removeChild(oldScript);

        requestText(base + name + '/template.html', function(err, tpl){
            if(err || !tpl || !tpl.trim()){
                host.innerHTML = '<div style="color:#b91c1c;">模板读取失败，无法预览。</div>';
                title.textContent = '';
                return;
            }

            var html = '';
            for(var i = 0; i < demoLinks.length; i++){
                html += applyTemplate(tpl, demoLinks[i]);
            }

            requestText(base + name + '/manifest.json', function(manifestErr, manifestText){
                var manifest = null;
                try { manifest = JSON.parse(manifestText || '{}'); } catch(e) {}
                var wrapped = (manifestErr || !manifest) ? html : wrapHtml(html, manifestText);
                host.innerHTML = wrapped;
                title.textContent = '';
                if(manifest && manifest.inject && manifest.inject.css){
                    requestText(base + name + '/style.css?t=' + Date.now(), function(cssErr, cssText){
                        if(!cssErr && cssText) injectPreviewCss(name, cssText);
                    });
                }
                if(manifest && manifest.inject && manifest.inject.js){
                    requestText(base + name + '/script.js?t=' + Date.now(), function(jsErr, jsText){
                        if(!jsErr && jsText) injectPreviewJs(scriptId, jsText);
                    });
                }
            });
        });
    }

    function update(){
        var v = sel.value || '';
        if(v.indexOf('TPL:') === 0){
            renderTemplate(v.substring(4));
        } else {
            var styleId = 'lp-rewrite-preview-style';
            var old = document.getElementById(styleId);
            if(old) old.parentNode.removeChild(old);
            host.innerHTML = '<div style="color:#6b7280;">暂无预览（请选择文件模板）。</div>';
            title.textContent = '';
        }
    }

    sel.addEventListener('change', update);
    update();
});
</script>
SCRIPT;
        $previewScript = sprintf($previewScript, $tplBaseUrl);
        echo $previewScript;
        
        $rewriteNum = new Typecho_Widget_Helper_Form_Element_Text(
            'rewrite_num',
            null,
            '0',
            _t('重写输出数量'),
            _t('0 表示全部')
        );
        $rewriteNum->input->setAttribute('class', 'w-10');
        $form->addInput($rewriteNum->addRule('isInteger', _t('请填写整数数字')));
        
        $rewriteSort = new Typecho_Widget_Helper_Form_Element_Text(
            'rewrite_sort',
            null,
            '',
            _t('重写分类（可选）'),
            _t('只输出指定分类 sort；留空为全部')
        );
        $rewriteSort->input->setAttribute('class', 'w-20');
        $form->addInput($rewriteSort);
        
        $rewriteSize = new Typecho_Widget_Helper_Form_Element_Text(
            'rewrite_size',
            null,
            '0',
            _t('重写图片尺寸（可选）'),
            _t('0 表示使用插件默认尺寸')
        );
        $rewriteSize->input->setAttribute('class', 'w-10');
        $form->addInput($rewriteSize->addRule('isInteger', _t('请填写整数数字')));

        $rewriteWrapBang = new Typecho_Widget_Helper_Form_Element_Radio(
            'rewrite_wrap_bang',
            array(
                '0' => _t('不包裹'),
                '1' => _t('使用 !!! !!! 包裹（部分主题需要）'),
            ),
            '0',
            _t('重写输出 HTML'),
            _t('有些主题/渲染器不支持直接输出 HTML，需要用 “!!!” 包裹整段 HTML 才会被当作原始 HTML 渲染。')
        );
        $form->addInput($rewriteWrapBang);
        
        // ========== 短代码配置卡片（放在正文重写下方） ==========
        $shortcodeCard = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $shortcodeCard->html(
            '<div class="md3-title">短代码支持</div>
            <div class="md3-body">
                <p>启用后，可在文章、页面、评论中使用 <span class="md3-chip" style="font-weight:bold;">[LinksPlus /]</span> 或 <span class="md3-chip" style="font-weight:bold;">[LinksPlus/]</span> 输出友链。</p>
                <p><span class="md3-chip">提示</span> <span style="margin-left:8px">短代码支持参数：<code>num</code>、<code>sort</code></span></p>
                <p><span class="md3-chip">建议</span> <span style="margin-left:8px">优先使用文件模板（<code>templates/</code>）来管理输出结构；旧版“源码规则”保留兼容。</span></p>
                <div class="lp-rewrite-actions">
                    <a href="' . Helper::security()->getIndex('/action/links-edit?do=update_templates') . '" class="md3-btn-text links-plus-get-templates-btn">同步Github主题</a>
                    <a href="https://blog.lhl.one/artical/902.html#%E4%B8%BB%E9%A2%98" target="_blank" class="md3-btn-text">查看全部主题</a>
                    <a href="https://blog.lhl.one/artical/902.html#%E4%B8%BB%E9%A2%98%E5%BC%80%E5%8F%91%E6%96%87%E6%A1%A3" target="_blank" class="md3-btn-text">主题开发文档</a>
                </div>
                <div class="lp-update-out" style="margin-top:12px"></div>
            </div>'
        );
        $form->addItem($shortcodeCard);

        // 短代码参数说明表格
        $shortcodeParamsCard = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $shortcodeParamsCard->html(
            '<div class="md3-title">短代码参数说明</div>' .
            '<div class="md3-body">' .
            '<table class="md3-table" style="width:100%;border-collapse:collapse;margin-bottom:8px;">' .
            '<thead><tr style="background-color:#f5f5f5;"><th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">参数</th><th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">说明</th><th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">示例</th></tr></thead>' .
            '<tbody>' .
            '<tr><td style="padding:8px;border-bottom:1px solid #eee;"><code>num</code></td><td style="padding:8px;border-bottom:1px solid #eee;">显示友链数量，不指定则显示全部</td><td style="padding:8px;border-bottom:1px solid #eee;"><code>[LinksPlus num=5 /]</code></td></tr>' .
            '<tr><td style="padding:8px;border-bottom:1px solid #eee;"><code>sort</code></td><td style="padding:8px;border-bottom:1px solid #eee;">排序字段，支持 name/date/order，不指定则按后台设置</td><td style="padding:8px;border-bottom:1px solid #eee;"><code>[LinksPlus sort=order /]</code></td></tr>' .
            '<tr><td style="padding:8px;border-bottom:1px solid #eee;"><code>OnlyForm</code></td><td style="padding:8px;border-bottom:1px solid #eee;">仅输出友链申请表单，不渲染友链列表（需已启用友链申请功能）</td><td style="padding:8px;border-bottom:1px solid #eee;"><code>[LinksPlus OnlyForm/]</code></td></tr>' .
            '<tr><td style="padding:8px;"><strong>完整示例</strong></td><td colspan="2" style="padding:8px;"><code>[LinksPlus num=10 sort=order /]</code></td></tr>' .
            '</tbody>' .
            '</table>' .
            '</div>'
        );
        $form->addItem($shortcodeParamsCard);

        // 短代码启用开关
        $enableShortcode = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'enable_shortcodes',
            array('links_plus' => _t('启用 [LinksPlus /] 短代码')),
            array('links_plus'),
            _t('短代码开关'),
            _t('勾选后在前台启用短代码功能')
        );
        $form->addInput($enableShortcode);

        // 短代码输出模板选择
        $selectedShortcode = new Typecho_Widget_Helper_Form_Element_Select(
            'template_shortcode',
            $tplOptionsShortcode,
            '',
            _t('短代码使用的模板'),
            _t('选择一个文件模板。选择后，前台使用 [LinksPlus /] 短代码会优先使用该模板渲染。')
        );
        $form->addInput($selectedShortcode);

        // 短代码模板预览卡片
        $optionsObj = Typecho_Widget::widget('Widget_Options');
        $tplBaseUrl = Typecho_Common::url('usr/plugins/Links/templates/', $optionsObj->siteUrl);

        $shortcodePreview = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $shortcodePreview->html(
            '<div class="md3-title">短代码模板预览</div>' .
            '<div class="md3-body">' .
            '<div id="shortcode-tpl-preview-html" style="min-height:160px;border-radius:8px;padding:12px;overflow:auto;"></div>' .
            '<div id="shortcode-tpl-preview-title" style="font-size:13px;color:#555;margin-top:8px;text-align:center"></div>' .
            '</div>'
        );
        $form->addItem($shortcodePreview);

        // 前端脚本：根据短代码模板选择渲染测试友链的真实 HTML 预览
        $previewScriptShortcode = <<<'SCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function(){
    var sel = document.querySelector('select[name="template_shortcode"]');
    var host = document.getElementById('shortcode-tpl-preview-html');
    var title = document.getElementById('shortcode-tpl-preview-title');
    if(!sel || !host || !title) return;
    var base = '%s';

    var demoLinks = [
        {
            lid: '1',
            name: "LHL's Blog",
            title: '博客',
            description: '作者的博客',
            sort: '博客',
            url: 'https://blog.lhl.one',
            image: 'https://smms-vip3.see.you/2025/04/18/KXpf8u5SQYNPkA3.jpg',
            user: ''
        },
        {
            lid: '2',
            name: "LHL's Shop",
            title: '图床',
            description: '作者的聚合图床',
            sort: '图床',
            url: 'https://img.lhl.one',
            image: 'https://smms-vip3.see.you/2026/01/27/ctiYDAXyxkRGbmB.png',
            user: ''
        }
    ];

    function esc(v){
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function requestText(url, cb){
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function(){
            if(xhr.readyState !== 4) return;
            if(xhr.status >= 200 && xhr.status < 300){
                cb(null, xhr.responseText || '');
            } else {
                cb(new Error('HTTP ' + xhr.status));
            }
        };
        xhr.send(null);
    }

    function applyTemplate(tpl, item){
        var out = tpl;
        var map = {
            lid: item.lid,
            name: esc(item.name),
            url: esc(item.url),
            sort: esc(item.sort),
            title: esc(item.title),
            description: esc(item.description),
            image: esc(item.image),
            user: esc(item.user),
            size: '120'
        };
        for(var k in map){
            if(Object.prototype.hasOwnProperty.call(map, k)){
                out = out.replace(new RegExp('\\{' + k + '\\}', 'g'), map[k]);
            }
        }
        return out;
    }

    function wrapHtml(itemsHtml, manifestText){
        try {
            var manifest = JSON.parse(manifestText || '{}');
            var wrapper = manifest && manifest.wrapper ? manifest.wrapper : null;
            if(!wrapper || !wrapper.tag) return itemsHtml;
            var tag = String(wrapper.tag).toLowerCase();
            if(['ul','ol','div'].indexOf(tag) === -1) return itemsHtml;
            var el = document.createElement(tag);
            if(wrapper.class) el.className = String(wrapper.class);
            if(wrapper.id) el.id = String(wrapper.id);
            if(wrapper.attrs && typeof wrapper.attrs === 'object'){
                for(var key in wrapper.attrs){
                    if(Object.prototype.hasOwnProperty.call(wrapper.attrs, key)){
                        el.setAttribute(key, String(wrapper.attrs[key]));
                    }
                }
            }
            el.innerHTML = itemsHtml;
            return el.outerHTML;
        } catch (e) {
            return itemsHtml;
        }
    }

    function injectPreviewCss(name, cssText){
        var styleId = 'lp-shortcode-preview-style';
        var old = document.getElementById(styleId);
        if(old) old.parentNode.removeChild(old);
        if(!cssText) return;
        var el = document.createElement('style');
        el.id = styleId;
        el.textContent = cssText;
        document.head.appendChild(el);
    }

    function injectPreviewJs(scriptId, jsText){
        var old = document.getElementById(scriptId);
        if(old) old.parentNode.removeChild(old);
        if(!jsText) return;
        var el = document.createElement('script');
        el.id = scriptId;
        el.textContent = jsText;
        document.body.appendChild(el);
    }

    function renderTemplate(name){
        var styleId  = 'lp-shortcode-preview-style';
        var scriptId = 'lp-shortcode-preview-script';
        var old = document.getElementById(styleId);
        if(old) old.parentNode.removeChild(old);
        var oldScript = document.getElementById(scriptId);
        if(oldScript) oldScript.parentNode.removeChild(oldScript);

        requestText(base + name + '/template.html', function(err, tpl){
            if(err || !tpl || !tpl.trim()){
                host.innerHTML = '<div style="color:#b91c1c;">模板读取失败，无法预览。</div>';
                title.textContent = '';
                return;
            }

            var html = '';
            for(var i = 0; i < demoLinks.length; i++){
                html += applyTemplate(tpl, demoLinks[i]);
            }

            requestText(base + name + '/manifest.json', function(manifestErr, manifestText){
                var manifest = null;
                try { manifest = JSON.parse(manifestText || '{}'); } catch(e) {}
                var wrapped = (manifestErr || !manifest) ? html : wrapHtml(html, manifestText);
                host.innerHTML = wrapped;
                title.textContent = '';
                if(manifest && manifest.inject && manifest.inject.css){
                    requestText(base + name + '/style.css?t=' + Date.now(), function(cssErr, cssText){
                        if(!cssErr && cssText) injectPreviewCss(name, cssText);
                    });
                }
                if(manifest && manifest.inject && manifest.inject.js){
                    requestText(base + name + '/script.js?t=' + Date.now(), function(jsErr, jsText){
                        if(!jsErr && jsText) injectPreviewJs(scriptId, jsText);
                    });
                }
            });
        });
    }

    function update(){
        var v = sel.value || '';
        if(v){
            renderTemplate(v);
        } else {
            var styleId = 'lp-shortcode-preview-style';
            var old = document.getElementById(styleId);
            if(old) old.parentNode.removeChild(old);
            host.innerHTML = '<div style="color:#6b7280;">请选择模板后预览。</div>';
            title.textContent = '';
        }
    }

    sel.addEventListener('change', update);
    update();
});
</script>
SCRIPT;
        echo sprintf($previewScriptShortcode, $tplBaseUrl);

        // ========== 友链申请配置卡片 ==========
        $applyCard = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $applyCard->html(
            '<div class="md3-title">友链申请</div>'
            . '<div class="md3-body">'
            . '<p>开启后，前台可提交友链申请，提交记录会以“待审核（state=2）”进入管理页。</p>'
            . '<p><span class="md3-chip">显示策略</span><span style="margin-left:8px">可单独设置是否跟随“正文重写”或“短代码”一并显示申请表单。</span></p>'
            . '<p><span class="md3-chip">OnlyForm 短代码</span><span style="margin-left:8px">使用 <code>[LinksPlus OnlyForm/]</code> 可单独输出申请表单，不渲染友链列表。</span></p>'
            . '</div>'
        );
        $form->addItem($applyCard);

        $enableApply = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'enable_link_apply',
            array('enabled' => _t('启用前台友链申请功能')),
            array(),
            _t('友链申请开关'),
            _t('开启后才会渲染申请表单并接受前台提交。')
        );
        $form->addInput($enableApply);

        $applyDisplayTargets = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_display_targets',
            array(
                'rewrite' => _t('跟随“正文重写”输出申请表单'),
                'shortcode' => _t('跟随“短代码”输出申请表单'),
            ),
            array('rewrite', 'shortcode'),
            _t('申请表单显示位置'),
            _t('至少勾选一个位置；未勾选时即使开启功能也不会在前台显示。')
        );
        $form->addInput($applyDisplayTargets);

        $applyTemplate = new Typecho_Widget_Helper_Form_Element_Select(
            'template_apply',
            $tplOptionsApply,
            '',
            _t('友链申请使用模板'),
            _t('通过选取模板来个性化申请表单的显示样式。')
        );
        $form->addInput($applyTemplate);

        $applyDefaultSort = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_default_sort',
            null,
            '友链申请',
            _t('申请默认分类'),
            _t('前台申请写入数据库时默认使用该分类。')
        );
        $applyDefaultSort->input->setAttribute('class', 'w-20');
        $form->addInput($applyDefaultSort);

        $applyRequireDesc = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_require_description',
            array('required' => _t('申请时必须填写友链描述')),
            array(),
            _t('描述字段要求'),
            _t('名称、地址、图片始终为必填；本项用于额外控制描述是否必填。')
        );
        $form->addInput($applyRequireDesc);

        $applyRequireEmail = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_require_email',
            array('required' => _t('申请时必须填写邮箱')),
            array('required'),
            _t('邮箱字段要求'),
            _t('建议保持必填，以便审核通过/驳回时向申请者发送通知。')
        );
        $form->addInput($applyRequireEmail);

        $applyRequireUser = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_require_user',
            array('required' => _t('申请时必须填写自定义数据')),
            array(),
            _t('自定义数据字段要求'),
            _t('可用于要求申请者补充更多信息。')
        );
        $form->addInput($applyRequireUser);
        $applyShowUserField = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_show_user_field',
            array('show' => _t('显示"自定义数据"字段')),
            array('show'),
            _t('自定义数据字段'),
            _t('取消勾选可在前台申请表单中完全隐藏该字段。')
        );
        $form->addInput($applyShowUserField);

        $applyAdvCard = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $applyAdvCard->html(
            '<div class="md3-title">申请表单高级设置</div>'
            . '<div class="md3-body"><p>自定义申请表单的标题、描述与外观风格。</p></div>'
        );
        $form->addItem($applyAdvCard);

        $applyTitle = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_title',
            null,
            '',
            _t('表单标题'),
            _t('留空使用默认文本"接受友链申请"。')
        );
        $form->addInput($applyTitle);

        $applyDesc = new Typecho_Widget_Helper_Form_Element_Textarea(
            'apply_desc',
            null,
            '',
            _t('表单描述'),
            _t('留空使用默认文本。')
        );
        $form->addInput($applyDesc);

        $applyDefaultOpen = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_default_open',
            array('open' => _t('默认展开申请表单')),
            array('open'),
            _t('表单默认状态'),
            _t('勾选则申请表单默认展开；取消则默认折叠（仅对内置内联表单有效，弹窗模式和自定义模板不受此影响）。')
        );
        $form->addInput($applyDefaultOpen);

        $applyColorMode = new Typecho_Widget_Helper_Form_Element_Select(
            'apply_color_mode',
            array(
                'auto'  => _t('自动（跟随主题 class）'),
                'light' => _t('强制亮色'),
                'dark'  => _t('强制暗色'),
            ),
            'auto',
            _t('颜色模式'),
            _t('"自动"随主题 dark class 切换；"强制亮色/暗色"忽略主题，对友链申请表单、正文重写及短代码输出均生效。')
        );
        $form->addInput($applyColorMode);

        $applyPopupBtnStyle = new Typecho_Widget_Helper_Form_Element_Select(
            'apply_popup_btn_style',
            array(
                'default'  => _t('默认（蓝底白字）'),
                'outline'  => _t('描边（透明底蓝边）'),
                'ghost'    => _t('幽灵（透明底灰边）'),
                'gradient' => _t('渐变（蓝紫渐变动画）'),
            ),
            'default',
            _t('弹窗按钮样式'),
            _t('仅在"弹窗"模式下生效。')
        );
        $form->addInput($applyPopupBtnStyle);

        $applyDarkClasses = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_dark_classes',
            null,
            '',
            _t('自定义暗色模式 class'),
            _t('用空格或逗号分隔多个 class 名（如 <code>night my-dark</code>），会自动为 <code>body.xxx</code> 和 <code>html.xxx</code> 生成暗色适配规则。'
                . '<br>已内置：<code>body.dark</code>、<code>body.dark-mode</code>、<code>body.dark-theme</code>、<code>body.theme-dark</code>'
                . '、<code>html.dark</code>、<code>html.dark-mode</code>、<code>html.dark-theme</code>、<code>html.theme-dark</code>'
                . '、<code>[data-theme="dark"]</code>')
        );
        $form->addInput($applyDarkClasses);

        $applyLightClasses = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_light_classes',
            null,
            '',
            _t('自定义亮色模式 class'),
            _t('指定强制亮色模式的 class（空格或逗号分隔），与"颜色模式"为"自动"时配合使用。已内置：<code>[data-lp-theme="light"]</code>')
        );
        $form->addInput($applyLightClasses);

        $applyMailCard = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $applyMailCard->html(
            '<div class="md3-title">友链申请邮件通知</div>'
            . '<div class="md3-body">'
            . '<p>邮件支持 <code>phpmail</code>和 <code>SMTP</code> 两种驱动。邮件通道可同时通知管理员和申请者（审核结果）。</p>'
            . '<p>可用模板变量：<code>{{name}}</code> <code>{{url}}</code> <code>{{image}}</code> <code>{{sort}}</code> <code>{{description}}</code> <code>{{user}}</code> <code>{{email}}</code> <code>{{site_name}}</code> <code>{{site_url}}</code> <code>{{reason}}</code> <code>{{manage_url}}</code>（仅管理员通知可用）</p>'
            . '</div>'
        );
        $form->addItem($applyMailCard);

        $applyMailEnabled = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'apply_mail_enabled',
            array('enabled' => _t('启用邮件通知')),
            array('enabled'),
            _t('邮件通知开关'),
            _t('开启后会发送：申请提醒给管理员、审核通过/驳回通知给申请者。')
        );
        $form->addInput($applyMailEnabled);

        $applyMailDriver = new Typecho_Widget_Helper_Form_Element_Select(
            'apply_mail_driver',
            array(
                'phpmail' => _t('phpmail'),
                'smtp'    => _t('SMTP'),
            ),
            'phpmail',
            _t('邮件驱动'),
            _t('选择 SMTP 后需填写下方 SMTP 服务器配置。')
        );
        $form->addInput($applyMailDriver);

        // SMTP 服务器配置（仅 smtp 驱动时使用）
        $applySmtpHost = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_smtp_host',
            null,
            '',
            _t('SMTP 服务器地址'),
            _t('例如：smtp.qq.com / smtp.gmail.com / smtp.163.com')
        );
        $applySmtpHost->input->setAttribute('class', 'w-40');
        $form->addInput($applySmtpHost);

        $applySmtpPort = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_smtp_port',
            null,
            '587',
            _t('SMTP 端口'),
            _t('STARTTLS 通常用 587；SSL/TLS 直连用 465；明文用 25。')
        );
        $applySmtpPort->input->setAttribute('class', 'w-10');
        $form->addInput($applySmtpPort);

        $applySmtpSecure = new Typecho_Widget_Helper_Form_Element_Select(
            'apply_smtp_secure',
            array(
                'tls'  => _t('STARTTLS（推荐，port 587）'),
                'ssl'  => _t('SSL/TLS（port 465）'),
                'none' => _t('无加密（port 25，不推荐）'),
            ),
            'tls',
            _t('SMTP 加密方式'),
            _t('与端口对应，建议保持 STARTTLS + 587。')
        );
        $form->addInput($applySmtpSecure);

        $applySmtpUser = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_smtp_user',
            null,
            '',
            _t('SMTP 用户名'),
            _t('通常为完整邮箱地址，也用作发件地址（若未单独配置发件邮箱）。')
        );
        $applySmtpUser->input->setAttribute('class', 'w-40');
        $form->addInput($applySmtpUser);

        $applySmtpPass = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_smtp_pass',
            null,
            '',
            _t('SMTP 密码 / 授权码'),
            _t('QQ/163 等使用授权码而非登录密码；密码以明文存入数据库，请确保数据库安全。')
        );
        $applySmtpPass->input->setAttribute('class', 'w-40');
        $applySmtpPass->input->setAttribute('type', 'password');
        $applySmtpPass->input->setAttribute('autocomplete', 'new-password');
        $form->addInput($applySmtpPass);

        $applyMailAdminTo = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_mail_admin_to',
            null,
            '',
            _t('管理员通知邮箱'),
            _t('收到新申请时通知该邮箱，例如：admin@example.com')
        );
        $applyMailAdminTo->input->setAttribute('class', 'w-40');
        $form->addInput($applyMailAdminTo);

        $applyMailFromEmail = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_mail_from_email',
            null,
            '',
            _t('发件邮箱（From）'),
            _t('留空则尝试使用管理员通知邮箱。')
        );
        $applyMailFromEmail->input->setAttribute('class', 'w-40');
        $form->addInput($applyMailFromEmail);

        $applyMailFromName = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_mail_from_name',
            null,
            'Links Plus',
            _t('发件人名称'),
            _t('邮件显示的发件人名称。')
        );
        $applyMailFromName->input->setAttribute('class', 'w-20');
        $form->addInput($applyMailFromName);

        $applyTplAdminSubject = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_mail_tpl_admin_subject',
            null,
            '【{{site_name}}】收到新的友链申请：{{name}}',
            _t('管理员通知主题模板'),
            _t('新申请提交后发送给管理员。')
        );
        $form->addInput($applyTplAdminSubject);

        $applyTplAdminBody = new Typecho_Widget_Helper_Form_Element_Textarea(
            'apply_mail_tpl_admin_body',
            null,
            '<p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6">收到一条新的友链申请，请前往后台审核。</p><div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px"><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;width:90px;border-bottom:1px solid #e5e7eb">站点名称</td><td style="padding:10px 16px;background:#f8fafc;color:#111827;font-weight:600;border-bottom:1px solid #e5e7eb">{{name}}</td></tr><tr><td style="padding:10px 16px;color:#6b7280;border-bottom:1px solid #e5e7eb">站点地址</td><td style="padding:10px 16px;border-bottom:1px solid #e5e7eb"><a href="{{url}}" style="color:#0061a4">{{url}}</a></td></tr><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;border-bottom:1px solid #e5e7eb">描述</td><td style="padding:10px 16px;background:#f8fafc;color:#374151;border-bottom:1px solid #e5e7eb">{{description}}</td></tr><tr><td style="padding:10px 16px;color:#6b7280;border-bottom:1px solid #e5e7eb">邮箱</td><td style="padding:10px 16px;color:#374151;border-bottom:1px solid #e5e7eb">{{email}}</td></tr><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280">分类</td><td style="padding:10px 16px;background:#f8fafc;color:#374151">{{sort}}</td></tr></table></div><div style="margin-top:20px;text-align:center"><a href="{{manage_url}}" style="display:inline-block;padding:10px 24px;background:#0061a4;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600">前往审核</a></div>',
            _t('管理员通知正文模板'),
            _t('支持使用上方列出的模板变量。')
        );
        $form->addInput($applyTplAdminBody);

        $applyTplApprovedSubject = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_mail_tpl_approved_subject',
            null,
            '【{{site_name}}】你的友链申请已通过',
            _t('通过通知主题模板'),
            _t('审核通过后发送给申请者。')
        );
        $form->addInput($applyTplApprovedSubject);

        $applyTplApprovedBody = new Typecho_Widget_Helper_Form_Element_Textarea(
            'apply_mail_tpl_approved_body',
            null,
'<div style="text-align:center;padding-bottom:20px"><div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:#e8f3ff;font-size:26px">✓</div><p style="margin:10px 0 0;font-size:20px;font-weight:700;color:#0061a4">申请已通过！</p></div><p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.7">Hi <strong>{{name}}</strong>，你提交至 <strong>{{site_name}}</strong> 的友链申请已审核通过，感谢你的申请！</p><div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px"><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;width:90px">友链地址</td><td style="padding:10px 16px;background:#f8fafc"><a href="{{url}}" style="color:#0061a4;font-weight:600">{{url}}</a></td></tr></table></div><p style="margin:0;font-size:14px;color:#6b7280">🌐 欢迎互链，期待与你交流！</p>',
            _t('通过通知正文模板'),
            _t('支持使用上方列出的模板变量。')
        );
        $form->addInput($applyTplApprovedBody);

        $applyTplRejectedSubject = new Typecho_Widget_Helper_Form_Element_Text(
            'apply_mail_tpl_rejected_subject',
            null,
            '【{{site_name}}】你的友链申请未通过',
            _t('驳回通知主题模板'),
            _t('审核驳回后发送给申请者。')
        );
        $form->addInput($applyTplRejectedSubject);

        $applyTplRejectedBody = new Typecho_Widget_Helper_Form_Element_Textarea(
            'apply_mail_tpl_rejected_body',
            null,
'<p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.7">Hi <strong>{{name}}</strong>，很遗憾，你提交至 <strong>{{site_name}}</strong> 的友链申请本次未能通过审核。</p><div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px"><tr><td style="padding:10px 16px;background:#f8fafc;color:#6b7280;width:90px;border-bottom:1px solid #e5e7eb">友链地址</td><td style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #e5e7eb"><a href="{{url}}" style="color:#0061a4">{{url}}</a></td></tr><tr><td style="padding:10px 16px;color:#6b7280">驳回原因</td><td style="padding:10px 16px;color:#374151">{{reason}}</td></tr></table></div><p style="margin:0;font-size:13px;color:#9ca3af">如有疑问，欢迎直接回复此邮件与我们联系。</p>',
            _t('驳回通知正文模板'),
            _t('支持使用上方列出的模板变量。')
        );
        $form->addInput($applyTplRejectedBody);

        $ajaxCompat = new Typecho_Widget_Helper_Form_Element_Select(
            'ajax_compat_mode',
            array(
                'default' => _t('默认'),
                'force_pjax' => _t('PJAX 兼容（推荐）'),
                'manual' => _t('手动（高级）')
            ),
            'default',
            _t('AJAX 兼容性模式'),
            _t('主题开启了 PJAX 且友链显示异常时，请选择「PJAX 兼容」。CSS 样式始终会注入到 &lt;head&gt; 并在 PJAX 导航后自动恢复；「PJAX 兼容」模式还会让申请表单在 PJAX 导航后自动重新初始化。')
        );
        $form->addInput($ajaxCompat);

        // PJAX 兼容说明卡片
        $pjaxFuncsCard = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card'));
        $pjaxFuncsCard->html(
            '<div class="md3-title">PJAX 兼容说明</div>' .
            '<div class="md3-body" style="font-size: 13px; line-height: 1.9;">' .
            '<p style="margin-bottom:8px"><strong>CSS 样式</strong>：插件 CSS 始终注入到页面 <code>&lt;head&gt;</code>，并监听 <code>pjax:end</code>、<code>turbo:load</code>、<code>swup:pageView</code> 等常见 PJAX 事件自动恢复，<strong>通常无需在主题的「PJAX RELOAD」框填写任何代码</strong>。</p>' .
            '<p style="margin-bottom:8px"><strong>申请表单 JS</strong>：选择「PJAX 兼容」模式后，表单也会监听上述事件自动重新初始化。</p>' .
            '<p style="margin-bottom:6px"><strong>若主题 PJAX 使用了自定义事件名</strong>（不在以上列表内），可在主题的「PJAX RELOAD」设置框中填入以下代码，手动触发表单重置：</p>' .
            '<pre class="lp-pjax-pre">' .
            'document.querySelectorAll(\'form[data-lp-ajax]\')' . "\n" .
            '  .forEach(function(f) { f._lpSet = false; });</pre>' .
            '<p style="font-size:12px">⚠ 上方代码仅用于申请表单重新绑定；模板 CSS 与 JS 无需额外处理。</p>' .
            '</div>'
        );
        $form->addItem($pjaxFuncsCard);

        // 前端脚本：将配置区重组为可折叠分组（原版设置 / 正文重写 / 短代码支持）
        echo <<<'SCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function(){
    var form = document.querySelector('form');
    if (!form) return;

    function findOptionByName(name){
        var field = form.querySelector('[name="' + name + '"], [name="' + name + '[]"]');
        if (!field) {
            field = form.querySelector('[name^="' + name + '["]');
        }
        if (!field) return null;
        return field.closest('.typecho-option') || null;
    }

    function findCardByTitle(titleText){
        var titles = form.querySelectorAll('.md3-card .md3-title');
        for (var i = 0; i < titles.length; i++) {
            var t = (titles[i].textContent || '').trim();
            if (t === titleText) {
                return titles[i].closest('.typecho-option') || titles[i].closest('.md3-card');
            }
        }
        return null;
    }

    function createFoldCard(title, openByDefault){
        var card = document.createElement('div');
        card.className = 'md3-card md3-fold-card';

        var details = document.createElement('details');
        details.className = 'md3-fold';
        if (openByDefault) details.open = true;

        var summary = document.createElement('summary');
        summary.innerHTML = '<span class="md3-fold-title"></span><span class="md3-fold-hint">点击展开/收起</span>';
        summary.querySelector('.md3-fold-title').textContent = title;

        var body = document.createElement('div');
        body.className = 'md3-fold-body';

        details.appendChild(summary);
        details.appendChild(body);
        card.appendChild(details);
        return { card: card, body: body };
    }

    function uniqueNodes(nodes){
        var out = [];
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i] && out.indexOf(nodes[i]) === -1) out.push(nodes[i]);
        }
        return out;
    }

    function mountFold(title, nodes, openByDefault){
        var items = uniqueNodes(nodes);
        if (!items.length) return;

        var fold = createFoldCard(title, openByDefault);
        var anchor = items[0];
        anchor.parentNode.insertBefore(fold.card, anchor);

        for (var i = 0; i < items.length; i++) {
            fold.body.appendChild(items[i]);
        }
    }

    mountFold('原版设置', [
        findOptionByName('template_text'),
        findOptionByName('template_img'),
        findOptionByName('template_mix'),
        findCardByTitle('高级：自定义源码规则（兼容旧版本）'),
        findOptionByName('pattern_text'),
        findOptionByName('pattern_img'),
        findOptionByName('pattern_mix'),
        findOptionByName('dsize')
    ], false);

    mountFold('正文重写', [
        findCardByTitle('正文重写'),
        findOptionByName('rewrite_cids'),
        findOptionByName('rewrite_pattern'),
        findCardByTitle('重写主题预览'),
        findOptionByName('rewrite_num'),
        findOptionByName('rewrite_sort'),
        findOptionByName('rewrite_size'),
        findOptionByName('rewrite_wrap_bang')
    ], false);

    mountFold('短代码支持', [
        findCardByTitle('短代码支持'),
        findCardByTitle('短代码参数说明'),
        findOptionByName('enable_shortcodes'),
        findOptionByName('template_shortcode'),
        findCardByTitle('短代码模板预览')
    ], false);

    mountFold('友链申请', [
        findCardByTitle('友链申请'),
        findOptionByName('enable_link_apply'),
        findOptionByName('apply_display_targets'),
        findOptionByName('template_apply'),
        findOptionByName('apply_default_sort'),
        findOptionByName('apply_require_description'),
        findOptionByName('apply_require_email'),
        findOptionByName('apply_require_user'),
        findOptionByName('apply_show_user_field'),
        findCardByTitle('申请表单高级设置'),
        findOptionByName('apply_title'),
        findOptionByName('apply_desc'),
        findOptionByName('apply_default_open'),
        findOptionByName('apply_popup_btn_style'),
        findCardByTitle('友链申请邮件通知'),
        findOptionByName('apply_mail_enabled'),
        findOptionByName('apply_mail_driver'),
        findOptionByName('apply_smtp_host'),
        findOptionByName('apply_smtp_port'),
        findOptionByName('apply_smtp_secure'),
        findOptionByName('apply_smtp_user'),
        findOptionByName('apply_smtp_pass'),
        findOptionByName('apply_mail_admin_to'),
        findOptionByName('apply_mail_from_email'),
        findOptionByName('apply_mail_from_name'),
        findOptionByName('apply_mail_tpl_admin_subject'),
        findOptionByName('apply_mail_tpl_admin_body'),
        findOptionByName('apply_mail_tpl_approved_subject'),
        findOptionByName('apply_mail_tpl_approved_body'),
        findOptionByName('apply_mail_tpl_rejected_subject'),
        findOptionByName('apply_mail_tpl_rejected_body')
    ], false);

    mountFold('亮暗色适配', [
        findOptionByName('apply_color_mode'),
        findOptionByName('apply_dark_classes'),
        findOptionByName('apply_light_classes')
    ], false);

    mountFold('AJAX 兼容', [
        findOptionByName('ajax_compat_mode'),
        findCardByTitle('PJAX 兼容说明')
    ], false);

    // SMTP 字段随驱动选择显示/隐藏
    (function(){
        var driverSel = form.querySelector('[name="apply_mail_driver"]');
        var smtpNames = ['apply_smtp_host','apply_smtp_port','apply_smtp_secure','apply_smtp_user','apply_smtp_pass'];
        var smtpEls = smtpNames.map(function(n){
            var f = form.querySelector('[name="' + n + '"]');
            return f ? (f.closest('.typecho-option') || f.parentNode) : null;
        }).filter(Boolean);

        function toggleSmtp(){
            var show = driverSel && driverSel.value === 'smtp';
            smtpEls.forEach(function(el){ el.style.display = show ? '' : 'none'; });
        }
        if (driverSel) {
            driverSel.addEventListener('change', toggleSmtp);
            toggleSmtp();
        }
    })();
});
</script>
SCRIPT;
        
        // //重写按钮（GET，走 Action，带 CSRF），这里是测试的时候用的
        // $sec = Helper::security();
        // $rewriteUrl = $sec->getIndex('/action/links-edit?do=rewrite');
        // $rewriteBtn = new Typecho_Widget_Helper_Layout('p', array('class' => 'typecho-option'));
        // $rewriteBtn->html(
        //     '<a class="btn primary" href="' . $rewriteUrl . '" ' .
        //     'onclick="return confirm(\'确认要对配置的 cid 执行重写吗？该操作会直接修改文章/页面正文内容。\');">' .
        //     '执行重写</a>'
        // );
        // $form->addItem($rewriteBtn);
    }
    
    /**
     * 生成用于“重写正文”的 HTML 字符串
     */
    public static function buildRewriteHtml()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $settings = $options->plugin('Links');
        $pattern = isset($settings->rewrite_pattern) && $settings->rewrite_pattern ? $settings->rewrite_pattern : 'SHOW_TEXT';
        $num = isset($settings->rewrite_num) ? (int)$settings->rewrite_num : 0;
        $sort = isset($settings->rewrite_sort) && $settings->rewrite_sort !== '' ? (string)$settings->rewrite_sort : null;
        // size=0 表示使用插件默认尺寸（dsize）
        $size = isset($settings->rewrite_size) ? (int)$settings->rewrite_size : 0;
        if ($size <= 0) {
            $size = (int)$settings->dsize;
        }

        // 如果重写模式选择了文件模板（TPL:xxx），则把模板的 CSS/JS 一并内联写入正文，
        // 以适配“主题不加载 head 注入 / 仅渲染正文”的场景。
        $assetCss = '';
        $assetJs = '';
        if (is_string($pattern) && stripos($pattern, 'TPL:') === 0) {
            $tplName = trim(substr($pattern, 4));
            $templates = self::listTemplates();
            if ($tplName !== '' && isset($templates[$tplName])) {
                $manifest = $templates[$tplName];
                $inject = isset($manifest['inject']) && is_array($manifest['inject']) ? $manifest['inject'] : array();
                if (!empty($inject['css'])) {
                    $css = self::readTemplateFile($tplName, 'style.css');
                    if ($css && trim($css) !== '') {
                        $assetCss = "<style>\n" . $css . "\n</style>\n";
                    }
                }
                if (!empty($inject['js'])) {
                    $js = self::readTemplateFile($tplName, 'script.js');
                    if ($js && trim($js) !== '') {
                        $assetJs = "<script>\n" . $js . "\n</script>\n";
                    }
                }
            }
        }

        $html = Links_Plugin::output_str('', array($pattern, $num, $sort, $size, 'HTML', 'rewrite'));
        // 资产写在正文前，避免部分主题/解析器只截取首段导致样式丢失
        if ($assetCss !== '' || $assetJs !== '') {
            $html = $assetCss . $assetJs . (string)$html;
        }
        $wrap = isset($settings->rewrite_wrap_bang) ? (string)$settings->rewrite_wrap_bang : '0';
        if ($wrap === '1') {
            // Trim 只去两端空白，避免破坏内部格式
            $html = trim((string)$html);
            if ($html !== '') {
                $html = "!!!\n" . $html . "\n!!!";
            }
        }

        return $html;
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function linksInstall()
    {
        $installDb = Typecho_Db::get();
        $type = explode('_', $installDb->getAdapterName());
        $type = array_pop($type);
        $prefix = $installDb->getPrefix();
        $sqlFile = self::getPluginDir() . DIRECTORY_SEPARATOR . $type . '.sql';
        if (!is_file($sqlFile)) {
            throw new Typecho_Plugin_Exception(_t('SQL 安装文件缺失：') . $sqlFile);
        }
        $scripts = file_get_contents($sqlFile);
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return _t('建立友情链接数据表，插件启用成功');
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if (('Mysql' == $type && (1050 == $code || '42S01' == $code)) ||
                ('SQLite' == $type && ('HY000' == $code || 1 == $code))
            ) {
                try {
                    $script = 'SELECT `lid`, `name`, `url`, `sort`, `email`, `image`, `description`, `user`, `state`, `order` from `' . $prefix . 'links`';
                    $installDb->query($script, Typecho_Db::READ);
                    return _t('检测到友情链接数据表，友情链接插件启用成功');
                } catch (Typecho_Db_Exception $e) {
                    $code = $e->getCode();
                    if (('Mysql' == $type && (1054 == $code || '42S22' == $code)) ||
                        ('SQLite' == $type && ('HY000' == $code || 1 == $code))
                    ) {
                        return Links_Plugin::linksUpdate($installDb, $type, $prefix);
                    }
                    throw new Typecho_Plugin_Exception(_t('数据表检测失败，友情链接插件启用失败。错误号：') . $code);
                }
            } else {
                throw new Typecho_Plugin_Exception(_t('数据表建立失败，友情链接插件启用失败。错误号：') . $code);
            }
        }
    }

    public static function linksUpdate($installDb, $type, $prefix)
    {
        $updateFile = self::getPluginDir() . DIRECTORY_SEPARATOR . 'Update_' . $type . '.sql';
        if (!is_file($updateFile)) {
            throw new Typecho_Plugin_Exception(_t('SQL 更新文件缺失：') . $updateFile);
        }
        $scripts = file_get_contents($updateFile);
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return _t('检测到旧版本友情链接数据表，升级成功');
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if (('Mysql' == $type && (1060 == $code || '42S21' == $code))) {
                return _t('友情链接数据表已经存在，插件启用成功');
            }
            throw new Typecho_Plugin_Exception(_t('友情链接插件启用失败。错误号：') . $code);
        }
    }

    public static function form($action = null)
    {
        /** 构建表格 */
        $options = Typecho_Widget::widget('Widget_Options');
        $form = new Typecho_Widget_Helper_Form(
            Helper::security()->getIndex('/action/links-edit'),
            Typecho_Widget_Helper_Form::POST_METHOD
        );

        /** 友链名称 */
        $name = new Typecho_Widget_Helper_Form_Element_Text('name', null, null, _t('友链名称*'));
        $form->addInput($name);

        /** 友链地址 */
        $url = new Typecho_Widget_Helper_Form_Element_Text('url', null, "http://", _t('友链地址*'));
        $form->addInput($url);

        /** 友链分类 */
        $sort = new Typecho_Widget_Helper_Form_Element_Text('sort', null, null, _t('友链分类'), _t('建议以英文字母开头，只包含字母与数字'));
        $form->addInput($sort);

        /** 友链邮箱 */
        $email = new Typecho_Widget_Helper_Form_Element_Text('email', null, null, _t('友链邮箱'), _t('填写友链邮箱'));
        $form->addInput($email);

        /** 友链图片 */
        $image = new Typecho_Widget_Helper_Form_Element_Text('image', null, null, _t('友链图片'),  _t('需要以http://或https://开头，留空表示没有友链图片'));
        $form->addInput($image);

        /** 友链描述 */
        $description =  new Typecho_Widget_Helper_Form_Element_Textarea('description', null, null, _t('友链描述'));
        $form->addInput($description);

        /** 自定义数据 */
        $user = new Typecho_Widget_Helper_Form_Element_Text('user', null, null, _t('自定义数据'), _t('该项用于用户自定义数据扩展'));
        $form->addInput($user);

        /** 友链状态 */
        $list = array('0' => '禁用', '1' => '启用', '2' => '待审核');
        $state = new Typecho_Widget_Helper_Form_Element_Radio('state', $list, '1', '友链状态');
        $form->addInput($state);

        /** 友链动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $form->addInput($do);

        /** 友链主键 */
        $lid = new Typecho_Widget_Helper_Form_Element_Hidden('lid');
        $form->addInput($lid);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        $request = Typecho_Request::getInstance();

        if (isset($request->lid) && 'insert' != $action) {
            /** 更新模式 */
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $link = $db->fetchRow($db->select()->from($prefix . 'links')->where('lid = ?', $request->lid));
            if (!$link) {
                throw new Typecho_Widget_Exception(_t('友链不存在'), 404);
            }

            $name->value($link['name']);
            $url->value($link['url']);
            $sort->value($link['sort']);
            $email->value($link['email']);
            $image->value($link['image']);
            $description->value($link['description']);
            $user->value($link['user']);
            $state->value($link['state']);
            $do->value('update');
            $lid->value($link['lid']);
            $submit->value(_t('编辑友链'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加友链'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $name->addRule('required', _t('必须填写友链名称'));
            $url->addRule('required', _t('必须填写友链地址'));
            $url->addRule('url', _t('不是一个合法的链接地址'));
            $email->addRule('email', _t('不是一个合法的邮箱地址'));
            $image->addRule('url', _t('不是一个合法的图片地址'));
            $name->addRule('maxLength', _t('友链名称最多包含50个字符'), 50);
            $url->addRule('maxLength', _t('友链地址最多包含200个字符'), 200);
            $sort->addRule('maxLength', _t('友链分类最多包含50个字符'), 50);
            $email->addRule('maxLength', _t('友链邮箱最多包含50个字符'), 50);
            $image->addRule('maxLength', _t('友链图片最多包含200个字符'), 200);
            $description->addRule('maxLength', _t('友链描述最多包含200个字符'), 200);
            $user->addRule('maxLength', _t('自定义数据最多包含200个字符'), 200);
        }
        if ('update' == $action) {
            $lid->addRule('required', _t('友链主键不存在'));
            $lid->addRule(array(new Links_Plugin, 'LinkExists'), _t('友链不存在'));
        }
        return $form;
    }

    public static function LinkExists($lid)
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $link = $db->fetchRow($db->select()->from($prefix . 'links')->where('lid = ?', $lid)->limit(1));
        return $link ? true : false;
    }

    /**
     * 控制输出格式
     */
    public static function output_str($widget, array $params)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $settings = $options->plugin('Links');
        if (!isset($options->plugins['activated']['Links'])) {
            return _t('友情链接插件未激活');
        }
        //验证默认参数
        $pattern = !empty($params[0]) && is_string($params[0]) ? $params[0] : 'SHOW_TEXT';
        $links_num = !empty($params[1]) && is_numeric($params[1]) ? $params[1] : 0;
        $sort = !empty($params[2]) && is_string($params[2]) ? $params[2] : null;
        $size = !empty($params[3]) && is_numeric($params[3]) ? $params[3] : $settings->dsize;
        $mode = isset($params[4]) ? $params[4] : 'FUNC';
        $context = isset($params[5]) ? trim((string)$params[5]) : '';

        // ONLYFORM 模式：只输出友链申请表单，不渲染友链
        if (strtoupper($pattern) === 'ONLYFORM') {
            $applyHtml = self::renderApplyFormHtml('shortcode-onlyform');
            if ($mode == 'HTML') {
                return $applyHtml;
            } else {
                echo $applyHtml;
            }
            return;
        }

        // 文件模板调用：TPL:template-name
        $tplManifest = null;
        $tplName = null;
        if (is_string($pattern) && stripos($pattern, 'TPL:') === 0) {
            $tplName = trim(substr($pattern, 4));
            $templates = self::listTemplates();
            if ($tplName !== '' && isset($templates[$tplName])) {
                $tplManifest = $templates[$tplName];
                $tplHtml = self::readTemplateFile($tplName, 'template.html');
                if ($tplHtml !== null && trim($tplHtml) !== '') {
                    $pattern = $tplHtml . "\n";
                }
            }
        }

        // 兼容旧模式字符串（优先模板选择，其次旧 textarea 规则）
        if ($pattern == 'SHOW_TEXT') {
            $tpl = isset($settings->template_text) ? trim((string)$settings->template_text) : '';
            if ($tpl !== '') {
                $tplName = $tpl;
                $templates = self::listTemplates();
                if (isset($templates[$tplName])) {
                    $tplManifest = $templates[$tplName];
                    $tplHtml = self::readTemplateFile($tplName, 'template.html');
                    if ($tplHtml !== null && trim($tplHtml) !== '') {
                        $pattern = $tplHtml . "\n";
                    } else {
                        $pattern = $settings->pattern_text . "\n";
                    }
                } else {
                    $pattern = $settings->pattern_text . "\n";
                }
            } else {
                $pattern = $settings->pattern_text . "\n";
            }
        } elseif ($pattern == 'SHOW_IMG') {
            $tpl = isset($settings->template_img) ? trim((string)$settings->template_img) : '';
            if ($tpl !== '') {
                $tplName = $tpl;
                $templates = self::listTemplates();
                if (isset($templates[$tplName])) {
                    $tplManifest = $templates[$tplName];
                    $tplHtml = self::readTemplateFile($tplName, 'template.html');
                    if ($tplHtml !== null && trim($tplHtml) !== '') {
                        $pattern = $tplHtml . "\n";
                    } else {
                        $pattern = $settings->pattern_img . "\n";
                    }
                } else {
                    $pattern = $settings->pattern_img . "\n";
                }
            } else {
                $pattern = $settings->pattern_img . "\n";
            }
        } elseif ($pattern == 'SHOW_MIX') {
            $tpl = isset($settings->template_mix) ? trim((string)$settings->template_mix) : '';
            if ($tpl !== '') {
                $tplName = $tpl;
                $templates = self::listTemplates();
                if (isset($templates[$tplName])) {
                    $tplManifest = $templates[$tplName];
                    $tplHtml = self::readTemplateFile($tplName, 'template.html');
                    if ($tplHtml !== null && trim($tplHtml) !== '') {
                        $pattern = $tplHtml . "\n";
                    } else {
                        $pattern = $settings->pattern_mix . "\n";
                    }
                } else {
                    $pattern = $settings->pattern_mix . "\n";
                }
            } else {
                $pattern = $settings->pattern_mix . "\n";
            }
        }
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $nopic_url = Typecho_Common::url('usr/plugins/Links/nopic.png', $options->siteUrl);
        $sql = $db->select()->from($prefix . 'links');
        if ($sort) {
            $sql = $sql->where('sort=?', $sort);
        }
        $sql = $sql->order($prefix . 'links.order', Typecho_Db::SORT_ASC);
        $links_num = intval($links_num);
        if ($links_num > 0) {
            $sql = $sql->limit($links_num);
        }
        $links = $db->fetchAll($sql);
        $str = "";
        foreach ($links as $link) {
            if ($link['image'] == null) {
                $link['image'] = $nopic_url;
                if ($link['email'] != null) {
                    $link['image'] = 'https://gravatar.helingqi.com/wavatar/' . md5($link['email']) . '?s=' . $size . '&d=mm';
                }
            }
            if ($link['state'] == 1) {
                $str .= str_replace(
                    array('{lid}', '{name}', '{url}', '{sort}', '{title}', '{description}', '{image}', '{user}', '{size}'),
                    array($link['lid'], $link['name'], $link['url'], $link['sort'], $link['description'], $link['description'], $link['image'], $link['user'], $size),
                    $pattern
                );
            }
        }

        // 若模板声明了 wrapper，则自动包裹输出（如 ul > li）
        if (!empty($tplManifest) && is_array($tplManifest) && trim($str) !== '') {
            list($wrapOpen, $wrapClose) = self::buildTemplateWrapper($tplManifest);
            if ($wrapOpen !== '' && $wrapClose !== '') {
                $str = $wrapOpen . "\n" . $str . $wrapClose;
            }
        }

        // 颜色模式强制包裹：强制暗色/亮色时为友链输出添加 data-lp-theme 属性
        $colorMode = isset($settings->apply_color_mode) ? trim((string)$settings->apply_color_mode) : 'auto';
        if ($str !== '' && ($colorMode === 'dark' || $colorMode === 'light')) {
            $str = '<div data-lp-theme="' . $colorMode . '">' . $str . '</div>';
        }

        // 注入模板资源：
        // - pattern = TPL:xxx
        // - 或 SHOW_* 映射到 template_text/img/mix 时同样需要注入
        $ajaxCompatMode = isset($settings->ajax_compat_mode) ? (string)$settings->ajax_compat_mode : 'default';
        $assetHtml = '';
        if (!empty($tplName) && !empty($tplManifest) && is_array($tplManifest)) {
            if ($mode == 'HTML') {
                ob_start();
                self::injectTemplateAssetsOnce($tplName, $tplManifest, $ajaxCompatMode);
                // 为模板 CSS 追加用户自定义亮/暗 class 的等效规则
                self::injectCustomDarkOverrideOnce($tplName, $tplManifest, $settings, $ajaxCompatMode);
                $assetHtml = ob_get_clean();
            } else {
                self::injectTemplateAssetsOnce($tplName, $tplManifest, $ajaxCompatMode);
                // 为模板 CSS 追加用户自定义亮/暗 class 的等效规则
                self::injectCustomDarkOverrideOnce($tplName, $tplManifest, $settings, $ajaxCompatMode);
            }
        }

        // 将本次渲染的模板传给 apply form（供"跟随模板"选项使用）
        self::activeRenderTplStore($tplName !== null ? $tplName : '');

        $applyHtml = self::renderApplyFormHtml($context);

        if ($mode == 'HTML') {
            return $assetHtml . $str . $applyHtml;
        } else {
            echo $str;
            if ($applyHtml !== '') {
                echo $applyHtml;
            }
        }
    }

    //输出
    public static function output($pattern = 'SHOW_TEXT', $links_num = 0, $sort = null, $size = 32, $mode = '')
    {
        return Links_Plugin::output_str('', array($pattern, $links_num, $sort, $size, $mode));
    }

    /**
     * 解析
     * 
     * @access public
     * @param array $matches 解析值
     * @return string
     */
    public static function parseCallback($matches)
    {
    // 兼容 <links></links> 这种空参数用法：
    // - 数量/分类/尺寸为空时使用默认值
    // - 标签内容为空时默认使用 SHOW_TEXT
    $linksNum = (isset($matches[1]) && $matches[1] !== '') ? $matches[1] : 0;
    $sort = (isset($matches[2]) && $matches[2] !== '') ? $matches[2] : null;
    $size = (isset($matches[3]) && $matches[3] !== '') ? $matches[3] : 0;
    $pattern = (isset($matches[4]) && trim($matches[4]) !== '') ? trim($matches[4]) : 'SHOW_TEXT';

    return Links_Plugin::output_str('', array($pattern, $linksNum, $sort, $size, 'HTML'));
    }

    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;

        // Shortcode: [links_plus] 支持（仅当在插件配置中启用）
        try {
            $options = Typecho_Widget::widget('Widget_Options');
            $settings = $options->plugin('Links');
            $shortcodes = isset($settings->enable_shortcodes) ? $settings->enable_shortcodes : array();
        } catch (Exception $e) {
            $shortcodes = array();
        }

        // 开发中，短代码
        if (is_array($shortcodes) && in_array('links_plus', $shortcodes)) {
            // 匹配 [LinksPlus /] 或 [LinksPlus/] 或带参数形式 [LinksPlus num=5 sort=friends size=48 template=SHOW_IMG]
            // 支持大小写不敏感的 LinksPlus 或 links_plus
            $text = preg_replace_callback(
                '/\[(?:LinksPlus|links_plus)(?:\s*([^\]]*))?\]/i',
                function ($m) {
                    $args = array();
                    if (!empty($m[1])) {
                        // 解析参数
                        $str = trim($m[1]);
                        
                        // OnlyForm 模式：仅输出友链申请表单，不渲染友链
                        if (strcasecmp(rtrim($str, '/ '), 'OnlyForm') === 0) {
                            return Links_Plugin::output_str('', array('ONLYFORM', 0, null, 0, 'HTML', 'shortcode-onlyform'));
                        }

                        // 特殊处理 "/" 或空格+/ 的情况（最小化调用形式）
                        if ($str === '/' || $str === '') {
                            // 使用默认配置：不指定参数则全部使用默认值
                            $args = array();
                        } else {
                            // 支持 num=5 sort=friends size=48 template=SHOW_IMG 这样的 key=value 形式
                            preg_match_all(
                                '/(\w+)\s*=\s*"([^"]*)"|(\w+)\s*=\s*\'([^\']*)\'|(\w+)\s*=\s*([^\s"\']+)/',
                                $str,
                                $ms,
                                PREG_SET_ORDER
                            );
                            foreach ($ms as $row) {
                                // 根据匹配的组合赋值（PREG_SET_ORDER 返回的结构）
                                $key = !empty($row[1]) ? $row[1] : (!empty($row[3]) ? $row[3] : (!empty($row[5]) ? $row[5] : null));
                                $val = !empty($row[2]) ? $row[2] : (!empty($row[4]) ? $row[4] : (!empty($row[6]) ? $row[6] : null));
                                if ($key && $val !== null) {
                                    $args[$key] = $val;
                                }
                            }
                            
                            // 如果没有解析到 key=value，尝试解析简写形式（仅数字表示 num）
                            if (empty($args) && preg_match('/^\d+$/', $str)) {
                                $args['num'] = (int)$str;
                            }
                        }
                    }

                    $num = isset($args['num']) ? (int)$args['num'] : 0;
                    $sort = isset($args['sort']) ? $args['sort'] : null;
                    $size = isset($args['size']) ? (int)$args['size'] : 0;
                    
                    // 先检查参数中是否指定了 template
                    if (isset($args['template']) && $args['template'] !== '') {
                        $rawPattern = trim((string)$args['template']);
                    } else {
                        $rawPattern = '';
                    }

                    // 统一把“模板名”规范为 TPL:xxx，保证会按 templates 渲染。
                    // 支持：SHOW_* / TPL:xxx / 纯模板名(如 md3-cards)
                    if ($rawPattern !== '') {
                        if (preg_match('/^SHOW_(TEXT|IMG|MIX)$/i', $rawPattern)) {
                            $pattern = strtoupper($rawPattern);
                        } elseif (stripos($rawPattern, 'TPL:') === 0) {
                            $pattern = 'TPL:' . trim(substr($rawPattern, 4));
                        } else {
                            $pattern = 'TPL:' . $rawPattern;
                        }
                    } else {
                        // 如果短代码参数未指定 template，则从配置中读取 template_shortcode
                        try {
                            $options = Typecho_Widget::widget('Widget_Options');
                            $settings = $options->plugin('Links');
                            $tpl = isset($settings->template_shortcode) ? trim((string)$settings->template_shortcode) : '';
                            if ($tpl !== '') {
                                $pattern = 'TPL:' . $tpl;
                            } else {
                                // 回退到 SHOW_TEXT
                                $pattern = 'SHOW_TEXT';
                            }
                        } catch (Exception $e) {
                            $pattern = 'SHOW_TEXT';
                        }
                    }

                    return Links_Plugin::output_str('', array($pattern, $num, $sort, $size, 'HTML', 'shortcode'));
                },
                $text
            );
        }

        if ($widget instanceof Widget_Archive || $widget instanceof Widget_Abstract_Comments) {
            // 支持：
            // <links></links>
            // <links 10></links>
            // <links 10 friends></links>
            // <links 10 friends 48>SHOW_IMG</links>
            // 分类允许使用常见 slug（字母/数字/下划线/连字符）
            // 且允许标签的 " > " 前存在空白
            // 更健壮的短标签解析：
            // 1) 允许 <links ...> 带任意 HTML 属性（比如 <links class="x">）
            // 2) 允许参数之间/标签两侧出现任意空白与换行
            // 3) 参数定义：数量(数字) 分类(非 < > 空白) 尺寸(数字)
            //    - 分类允许中文/连字符/下划线等，只要不包含空白与尖括号
            // 4) 标签内容为 pattern（SHOW_TEXT/SHOW_IMG/SHOW_MIX 或自定义模板名）
            $regex = "/<links(?:\\s+[^>]*)?\\s*(\\d*)\\s*([^\\s<>]*)\\s*(\\d*)\\s*>\\s*(.*?)\\s*<\\/links>/is";

            return preg_replace_callback(
                $regex,
                array('Links_Plugin', 'parseCallback'),
                $text ? $text : ''
            );
        } else {
            return $text;
        }
    }
}

