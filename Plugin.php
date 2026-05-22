<?php

/**
 * 友情链接插件 by LHL (增强版)
 * 
 * @package Links+
 * @author LHL
 * @version 2.0.0
 * @link https://github.com/lhl77/Typecho-Plugin-LinksPlus
 * 
 * version 2.0.0 at 2026-05-22 by LHL
 * 重构插件架构，插件变更为付费插件
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

require_once __DIR__ . '/core.php';

class Links_Plugin implements Typecho_Plugin_Interface
{
    use Links_Core_Trait;

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

        return '<div class="md3-card md3-state-card md3-state-danger" style="border-color:rgba(239,68,68,.35);background:#fff5f5">'
            . '<div class="md3-title" style="color:#b91c1c">&#9888; 插件完整性检查失败</div>'
            . '<div class="md3-body">'
            . '<ul style="margin:0;padding:0 0 0 4px;list-style:none">' . $listHtml . '</ul>'
            . $detail
            . '</div></div>';
    }

    private static function addLicenseConfigSection(Typecho_Widget_Helper_Form $form, array $licenseState, $showUpsell)
    {
        if ($showUpsell) {
            $upsellCard = new Typecho_Widget_Helper_Layout('div');
            $upsellCard->html(self::renderLicenseUpsellCardHtml('config', $licenseState));
            $form->addItem($upsellCard);
        }

        $hostId = $showUpsell ? 'lp-license-section-host-top' : 'lp-license-section-host-bottom';
        $binding = isset($licenseState['binding']) && $licenseState['binding'] !== ''
            ? htmlspecialchars((string)$licenseState['binding'], ENT_QUOTES, 'UTF-8')
            : htmlspecialchars(_t('未识别'), ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars(isset($licenseState['message']) ? (string)$licenseState['message'] : '', ENT_QUOTES, 'UTF-8');
        $statusText = !empty($licenseState['authorized']) ? _t('已授权') : _t('未授权');
        $statusStyle = !empty($licenseState['authorized'])
            ? 'background:#e6f4ea;color:#1e8e3e;border-color:rgba(30,142,62,.18);'
            : 'background:#fff4cc;color:#8a6b00;border-color:rgba(185,140,0,.2);';

        $card = new Typecho_Widget_Helper_Layout('div', array('class' => 'md3-card md3-fold-card'));
        $card->html(
            '<details class="md3-fold"' . ($showUpsell ? ' open' : '') . '>'
            . '<summary><span class="md3-fold-title">授权校验</span><span class="md3-fold-hint">点击展开/收起</span></summary>'
            . '<div class="md3-fold-body">'
            . '<p><span class="md3-chip" style="' . $statusStyle . '">' . htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') . '</span></p>'
            . '<p>当前站点标识：<span class="md3-chip" style="margin-left:8px">' . $binding . '</span></p>'
            . '<p>' . $message . '</p>'
            . '<div id="' . $hostId . '"></div>'
            . '</div>'
            . '</details>'
        );
        $form->addItem($card);

        $licenseValue = isset($licenseState['licenseKey']) ? (string)$licenseState['licenseKey'] : '';
        $licenseField = new Typecho_Widget_Helper_Form_Element_Text(
            'license_code',
            null,
            $licenseValue,
            _t('授权码'),
            _t('请输入购买后获得的授权码，格式为 XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX。填入后点击保存自动激活，再次进入插件设置即可进行配置。')
        );
        $licenseField->input->setAttribute('class', 'w-50');
        $licenseField->input->setAttribute('placeholder', 'XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX');
        $form->addInput($licenseField);

        echo '<script>(function(){function place(){var host=document.getElementById(' . json_encode($hostId) . ');var field=document.querySelector("[name=\"license_code\"]");if(!host||!field){return;}var option=field.closest(".typecho-option");if(!option){return;}host.appendChild(option);}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",place);}place();})();</script>';
    }

    private static function addLegacyConfigInputs(Typecho_Widget_Helper_Form $form, $settings = null)
    {
        if ($settings === null) {
            $settings = self::getPluginSettings();
        }

        if (is_object($settings)) {
            if (method_exists($settings, 'toArray')) {
                $settings = $settings->toArray();
            } elseif ($settings instanceof Traversable) {
                $settings = iterator_to_array($settings);
            } else {
                $settings = get_object_vars($settings);
            }
        }

        if (!is_array($settings) || empty($settings)) {
            return;
        }

        $inputs = $form->getInputs();
        foreach ($settings as $name => $value) {
            if (!is_string($name) || $name === '' || isset($inputs[$name])) {
                continue;
            }

            if (is_array($value)) {
                $options = array();
                foreach ($value as $item) {
                    if (is_bool($item)) {
                        $item = $item ? '1' : '0';
                    } elseif ($item === null) {
                        $item = '';
                    } elseif (!is_scalar($item)) {
                        continue;
                    } else {
                        $item = (string) $item;
                    }

                    $options[$item] = '';
                }

                if (empty($options)) {
                    $options['__lp_legacy__'] = '';
                    $value = array();
                }

                $legacyInput = new Typecho_Widget_Helper_Form_Element_Checkbox($name, $options, $value);
                $legacyInput->setAttribute('style', 'display:none');
            } else {
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } elseif ($value === null) {
                    $value = '';
                } elseif (!is_scalar($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $value = (string) $value;
                }

                $legacyInput = new Typecho_Widget_Helper_Form_Element_Hidden($name, null, $value);
            }

            $form->addInput($legacyInput);
            $inputs[$name] = $legacyInput;
        }
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
        self::renderPluginConfig($form);
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
                $script = trim((string)$script);
                if ($script === '') {
                    continue;
                }
                $installDb->query($script, Typecho_Db::WRITE);
            }
            return _t('友情链接数据表创建成功');
        } catch (Exception $e) {
            return _t('友情链接数据表已存在，继续使用现有数据');
        } catch (Throwable $e) {
            return _t('友情链接数据表已存在，继续使用现有数据');
        }
    }
}

