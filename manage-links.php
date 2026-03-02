<?php

/** 初始化组件 */
Typecho_Widget::widget('Widget_Init');

/** 注册一个初始化插件 */
Typecho_Plugin::factory('admin/common.php')->begin();

Typecho_Widget::widget('Widget_Options')->to($options);
Typecho_Widget::widget('Widget_User')->to($user);
Typecho_Widget::widget('Widget_Security')->to($security);
Typecho_Widget::widget('Widget_Menu')->to($menu);

/** 初始化上下文 */
$request = $options->request;
$response = $options->response;
include 'header.php';
include 'menu.php';
?>


<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        
        <style>
            :root {
                --md-primary: #0061a4;
                --md-primary-container: #d1e4ff;
                --md-on-primary-container: #001d36;
                --md-surface: #fdfcff;
                --md-surface-variant: #e1e2ec;
                --md-outline: #74777f;
                --md-radius: 12px;
                --md-surface-1: #f0f4f8; /* 背景色微调 */
                --md-outline-variant: rgba(0,0,0,.12);
                --md-surface-container: #f3f4f7;
            }

            .md3-wrap {
                max-width: 1280px;
                margin: 0 auto;
                width: 100%;
                min-width: 0;
            }


            /* 顶部 App Bar */
            .md3-appbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 18px;
                border: 1px solid var(--md-outline-variant);
                background: var(--md-surface);
                border-radius: 16px;
                box-shadow: 0 1px 2px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.12);
                margin: 8px 0 18px;
            }
            .md3-appbar-title {
                display: flex;
                flex-direction: column;
                min-width: 0;
            }
            .md3-appbar-title b {
                font-size: 15px;
                color: #111827;
            }
            .md3-appbar-title span {
                font-size: 12px;
                color: #6b7280;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .md3-appbar-actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .md3-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                border-radius: 999px;
                border: 1px solid var(--md-outline-variant);
                background: var(--md-surface);
                color: #1f2937;
                text-decoration: none;
                font-weight: 600;
                font-size: 13px;
                transition: box-shadow .15s, background .15s, border-color .15s;
            }
            .md3-btn:hover {
                text-decoration: none;
                background: var(--md-surface-container);
                box-shadow: 0 1px 2px rgba(0,0,0,.10);
            }
            .md3-btn.primary {
                background: var(--md-primary);
                color: #fff;
                border-color: transparent;
            }
            .md3-btn.primary:hover {
                background: #055a96;
            }
            .md3-btn.danger {
                background: #b3261e;
                color: #fff;
                border-color: transparent;
            }
            .md3-btn.danger:hover {
                background: #9a201a;
            }
            .md3-btn.tonal {
                background: var(--md-primary-container);
                border-color: transparent;
                color: var(--md-on-primary-container);
            }
            .md3-btn.tonal:hover {
                background: rgba(209,228,255,.7);
            }

            /* 容器与布局 */
            .typecho-page-main {
                display: flex;
                flex-wrap: wrap;
                gap: 24px;
                min-width: 0; /* 允许子项在 flex 容器内收缩，避免把页面撑出 */
                width: 100%;  /* 强制覆盖 .row 的 100vw */
                margin: 0;    /* 强制重置 .row 的 margin */
            }
            .col-mb-12 {
                float: none;
                width: 100%;
                padding: 0;
            }
            .md3-card {
                background: var(--md-surface);
                border-radius: var(--md-radius);
                padding: 0;
                box-shadow: 0 1px 2px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.12);
                border: 1px solid var(--md-outline-variant);
                overflow: hidden;
                margin-bottom: 0;
            }

            /* 左侧列表面板 */
            .manage-list-panel {
                flex: 2;
                min-width: 0; /* 防止 flex 子项溢出 */
            }

            /* 表格外层：需要时允许横向滚动，而不是撑破页面 */
            .typecho-table-wrap {
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* 表格本身：占满容器，并允许内容在单元格内换行 */
            .typecho-list-table {
                width: 100%;
                max-width: 100%;
                table-layout: fixed;
            }

            /* URL 容易是超长不带空格的字符串：强制断行避免撑宽 */
            .typecho-list-table td:nth-child(3) {
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            .manage-list-header {
                padding: 16px 24px;
                border-bottom: 1px solid var(--md-surface-variant);
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
            }

            /* 操作栏 */
            .typecho-list-operate {
                margin: 0;
                padding: 0;
            }
            .btn-group .dropdown-toggle {
                border-radius: 20px;
                padding: 6px 16px;
                font-size: 13px;
                border: 1px solid var(--md-outline-variant);
                color: #1f2937;
                background: var(--md-surface);
                font-weight: 700;
            }
            .btn-group .dropdown-toggle:hover {
                background-color: var(--md-surface-variant);
            }
            .dropdown-menu {
                border-radius: 14px;
                border: 1px solid var(--md-outline-variant);
                box-shadow: 0 8px 24px rgba(0,0,0,.12);
                padding: 8px;
            }
            .dropdown-menu li a {
                border-radius: 10px;
                padding: 8px 10px;
                font-weight: 600;
                color: #111827;
            }
            .dropdown-menu li a:hover {
                background: var(--md-surface-container);
            }

            /* 表格优化 */
            .typecho-list-table {
                border: none;
            }
            .typecho-list-table th {
                border-bottom: 1px solid var(--md-surface-variant);
                color: #666;
                font-weight: 600;
                padding: 16px 12px;
                background: #fff;
            }
            .typecho-list-table td {
                padding: 16px 12px;
                border-bottom: 1px solid #f0f0f0;
                vertical-align: middle;
            }
            .typecho-list-table tr:hover td {
                background-color: var(--md-surface-1);
            }

            /* 一键检查：异常行高亮 */
            .typecho-list-table tr.link-check-fail td {
                background: #fde7e7 !important;
            }
            .typecho-list-table tr.link-check-fail:hover td {
                background: #fbd0d0 !important;
            }
            .typecho-list-table tr.link-check-ok td {
                background: #e9f7ef !important;
            }
            .typecho-list-table tr.link-check-redirect td {
                background: #fff4cc !important;
            }
            .typecho-list-table tr.link-check-redirect:hover td {
                background: #ffe9a3 !important;
            }
            /* 一键检查：不确定/被浏览器拦截（例如 CORS/混合内容/隐私扩展导致 fetch 失败或 opaque） */
            .typecho-list-table tr.link-check-uncertain td {
                background: #f3f4f6 !important;
            }
            .typecho-list-table tr.link-check-uncertain:hover td {
                background: #e5e7eb !important;
            }
            .link-check-hint {
                display: inline-block;
                margin-left: 8px;
                padding: 2px 8px;
                border-radius: 999px;
                font-size: 12px;
                border: 1px solid rgba(0,0,0,.12);
                background: #fff;
                color: #6b7280;
                vertical-align: middle;
                white-space: nowrap;
            }
            .link-check-hint.ok {
                border-color: rgba(30,142,62,.25);
                color: #1e8e3e;
            }
            .link-check-hint.redirect {
                border-color: rgba(185,140,0,.25);
                color: #8a6b00;
            }
            .link-check-hint.uncertain {
                border-color: rgba(107,114,128,.35);
                color: #6b7280;
            }
            .link-check-hint.fail {
                border-color: rgba(217,48,37,.25);
                color: #d93025;
            }
            
            /* 图片与状态标签 */
            .avatar {
                border-radius: 8px;
                border: 1px solid #eee;
            }
            .status-tag {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 500;
            }
            .status-normal {
                background-color: #e6f4ea;
                color: #1e8e3e;
            }
            .status-ban {
                background-color: #fce8e6;
                color: #d93025;
            }

            /* 链接样式 */
            .edit-link {
                color: var(--md-primary);
                font-weight: 500;
                text-decoration: none;
            }
            .edit-link:hover {
                text-decoration: underline;
            }

            /* 右侧编辑面板 */
            .editor-panel {
                flex: 1;
                min-width: 0; /* 小窗口时允许收缩，避免总宽度超出 */
            }
            .editor-container {
                padding: 24px;
            }
            
            /* 表单元素 MD3 化 */
            .typecho-label {
                font-size: 13px;
                color: var(--md-primary);
                margin-bottom: 8px;
                font-weight: 600;
            }
            input[type="text"], textarea {
                width: 100%;
                border: 1px solid var(--md-outline-variant);
                border-radius: 12px;
                padding: 10px 12px;
                font-size: 14px;
                transition: all 0.2s;
                background-color: #fff;
                box-sizing: border-box; /* 确保 padding 不撑大 */
            }
            input[type="text"]:focus, textarea:focus {
                border-color: var(--md-primary);
                box-shadow: 0 0 0 3px rgba(0, 97, 164, 0.18);
                outline: none;
            }
            .description {
                font-size: 12px;
                color: #666;
                margin-top: 4px;
                margin-bottom: 16px;
            }
            
            /* 按钮 */
            .btn.primary {
                background-color: var(--md-primary);
                color: #fff;
                border: none;
                border-radius: 20px;
                padding: 10px 24px;
                font-weight: 500;
                cursor: pointer;
                transition: box-shadow 0.2s;
                height: auto;
                line-height: normal;
            }
            .btn.primary:hover {
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }

            /* 小屏优化：改为上下布局 */
            @media (max-width: 960px) {
                .typecho-page-main {
                    flex-direction: column;
                }
                .editor-panel {
                    min-width: 0;
                }
                .md3-appbar {
                    flex-direction: column;
                    align-items: stretch;
                }
                .md3-appbar-actions {
                    justify-content: flex-start;
                }
            }
        </style>

        <div class="md3-wrap">
            <div class="md3-appbar">
                <div class="md3-appbar-title">
                    <b><?php _e('友情链接'); ?></b>
                    <span><?php _e('拖拽排序 / 批量操作 / 右侧编辑'); ?></span>
                </div>
                <div class="md3-appbar-actions">
                    <a class="md3-btn tonal" href="<?php $options->adminUrl('options-plugin.php?config=Links'); ?>"><?php _e('设置'); ?></a>
                    
                    <a class="md3-btn" href="https://blog.lhl.one/artical/902.html " target="_blank"><?php _e('帮助'); ?></a>
                    <a class="md3-btn danger" href="javascript:void(0)" id="linksPlusCheckAll" title="通过后端代理检测每个友链是否可访问（可获取真实状态码，避免浏览器跨域/CORS 限制）"><?php _e('一键检查'); ?></a>
                    <a class="md3-btn primary" href="<?php $security->index('/action/links-edit?do=rewrite'); ?>" title="将指定 cid 文章正文中的 {{links_plus}} 占位符替换为友链 HTML" onclick="return confirm('确认要执行正文重写吗？该操作会直接修改文章/页面正文内容。');"><?php _e('执行重写'); ?></a>
                </div>
            </div>

        <div class="row typecho-page-main manage-metas">
                <!-- 左侧：列表 -->
                <div class="manage-list-panel md3-card" role="main">
                    <?php
                        $prefix = $db->getPrefix();
                        $links = $db->fetchAll($db->select()->from($prefix.'links')->order($prefix.'links.order', Typecho_Db::SORT_ASC));
                    ?>
                    <form method="post" name="manage_categories" class="operate-form">
                    
                    <div class="manage-list-header">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=delete'); ?>"><?php _e('删除'); ?></a></li>
                                    <li><a lang="<?php _e('你确认要启用这些友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=enable'); ?>"><?php _e('启用'); ?></a></li>
                                    <li><a lang="<?php _e('你确认要禁用这些友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=prohibit'); ?>"><?php _e('禁用'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- 可以放搜索或其他 -->
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="40"/>
                                <col width="25%"/>
                                <col width=""/>
                                <col width="15%"/>
                                <col width="60"/>
                                <col width="80"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th> </th>
                                    <th><?php _e('友链名称'); ?></th>
                                    <th><?php _e('友链地址'); ?></th>
                                    <th><?php _e('分类'); ?></th>
                                    <th><?php _e('图片'); ?></th>
                                    <th><?php _e('状态'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($links)): $alt = 0;?>
                                <?php foreach ($links as $link): ?>
                                <tr id="lid-<?php echo $link['lid']; ?>" data-url="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><input type="checkbox" value="<?php echo $link['lid']; ?>" name="lid[]"/></td>
                                    <td><a class="edit-link" href="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>" title="<?php _e('点击编辑'); ?>"><?php echo $link['name']; ?></a>
                                    <td><a href="<?php echo $link['url']; ?>" target="_blank" style="color:#888; text-decoration:none;"><i class="i-exlink"></i></a> <?php echo $link['url']; ?></td>
                                    <td><?php echo $link['sort']; ?></td>
                                    <td><?php
                                        if ($link['image']) {
                                            echo '<a href="'.$link['image'].'" title="'._t('点击放大').'" target="_blank"><img class="avatar" src="'.$link['image'].'" alt="'.$link['name'].'" width="32" height="32"/></a>';
                                        } else {
                                            $options = Typecho_Widget::widget('Widget_Options');
                                            $nopic_url = Typecho_Common::url('usr/plugins/Links/nopic.png', $options->siteUrl);
                                            echo '<img class="avatar" src="'.$nopic_url.'" alt="NOPIC" width="32" height="32"/>';
                                        }
                                    ?></td>
                                    <td><?php
                                        if ($link['state'] == 1) {
                                            echo '<span class="status-tag status-normal">正常</span>';
                                        } elseif ($link['state'] == 0) {
                                            echo '<span class="status-tag status-ban">禁用</span>';
                                        }
                                    ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('没有任何友链'); ?></h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
                </div>

                <!-- 右侧：编辑表单 -->
                <div class="editor-panel md3-card" role="form">
                    <div class="editor-container">
                         <?php Links_Plugin::form()->render(); ?>
                    </div>
                </div>
        </div>
    </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script>
$('input[name="email"]').blur(function() {
    var _email = $(this).val();
    var _image = $('input[name="image"]').val();
    if (_email != '' && _image == '') {
        var k = "<?php $security->index('/action/links-edit'); ?>";
        $.post(k, {"do": "email-logo", "type": "json", "email": $(this).val()}, function (result) {
            var k = jQuery.parseJSON(result).url;
            $('input[name="image"]').val(k);
        });
    }
    return false;
});
</script>
<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table').tableDnD({
            onDrop : function () {
                var ids = [];

                $('input[type=checkbox]', table).each(function () {
                    ids.push($(this).val());
                });

                $.post('<?php $security->index('/action/links-edit?do=sort'); ?>',
                    $.param({lid : ids}));

                $('tr', table).each(function (i) {
                    if (i % 2) {
                        $(this).addClass('even');
                    } else {
                        $(this).removeClass('even');
                    }
                });
            }
        });

        table.tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.dropdown-menu button.merge').click(function () {
            var btn = $(this);
            btn.parents('form').attr('action', btn.attr('rel')).submit();
        });

        <?php if (isset($request->lid)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>

        // 一键检查：后端代理检测可用性（避免前端 CORS 影响，能拿到真实状态码）
        (function initLinksPlusCheckAll() {
            var $btn = $('#linksPlusCheckAll');
            if (!$btn.length) return;

            var checkApi = '<?php $security->index('/action/links-edit?do=check-link'); ?>';

            function setHint($tr, text, cls) {
                $tr.find('.link-check-hint').remove();
                if (!text) return;
                var $hint = $('<span class="link-check-hint" />').text(text);
                if (cls) $hint.addClass(cls);
                $tr.find('td').eq(1).append($hint);
            }

            function markState($tr, state, msg) {
                $tr.removeClass('link-check-ok link-check-fail link-check-redirect link-check-uncertain');
                if (state === 'ok') {
                    $tr.addClass('link-check-ok');
                    setHint($tr, msg || '200 可访问', 'ok');
                } else if (state === 'redirect') {
                    $tr.addClass('link-check-redirect');
                    setHint($tr, msg || '301/302 重定向', 'redirect');
                } else if (state === 'uncertain') {
                    $tr.addClass('link-check-uncertain');
                    setHint($tr, msg || '浏览器限制：无法获取状态码', 'uncertain');
                } else if (state === 'fail') {
                    $tr.addClass('link-check-fail');
                    setHint($tr, msg || '请求失败', 'fail');
                } else {
                    setHint($tr, msg || '检查中…');
                }
            }

            // 调后端接口拿真实状态码：{ ok, status, finalUrl, error }
            async function probeUrl(url, timeoutMs) {
                // jQuery ajax 返回 jqXHR（支持 abort）
                return await new Promise(function (resolve) {
                    var timer = setTimeout(function () {
                        resolve({ ok: false, status: 0, error: '超时' });
                        try { xhr && xhr.abort && xhr.abort(); } catch (e) {}
                    }, timeoutMs);

                    var xhr = $.ajax({
                        url: checkApi,
                        method: 'POST',
                        dataType: 'json',
                        data: { url: url },
                        success: function (data) {
                            clearTimeout(timer);
                            resolve(data || { ok: false, status: 0, error: '返回为空' });
                        },
                        error: function () {
                            clearTimeout(timer);
                            resolve({ ok: false, status: 0, error: '后端接口不可用' });
                        }
                    });
                });
            }

            // 简单并发控制
            async function runPool(items, worker, concurrency) {
                var index = 0;
                var running = 0;
                return new Promise(function (resolve) {
                    function next() {
                        if (index >= items.length && running === 0) return resolve();
                        while (running < concurrency && index < items.length) {
                            (function (item) {
                                running++;
                                Promise.resolve(worker(item)).finally(function () {
                                    running--;
                                    next();
                                });
                            })(items[index++]);
                        }
                    }
                    next();
                });
            }

            // 前端二次兜底：no-referrer 的 GET（弱判断，但满足“前端再次 GET + 不带 referer”）
            // - referrerPolicy: 'no-referrer' 不发送 Referer
            // - mode: 'no-cors' 避免 CORS 抛错（但响应会是 opaque，拿不到 status）
            // - 成功：仅表示“请求在浏览器侧可发出且未抛错”，不等于 200
            async function probeUrlNoReferrer(url, timeoutMs) {
                var ctrl = (window.AbortController ? new AbortController() : null);
                var timer = setTimeout(function () {
                    try { ctrl && ctrl.abort && ctrl.abort(); } catch (e) {}
                }, timeoutMs);

                try {
                    await fetch(url, {
                        method: 'GET',
                        mode: 'no-cors',
                        cache: 'no-store',
                        redirect: 'follow',
                        referrerPolicy: 'no-referrer',
                        signal: ctrl ? ctrl.signal : undefined
                    });
                    clearTimeout(timer);
                    return { ok: true };
                } catch (e) {
                    clearTimeout(timer);
                    return { ok: false, reason: 'blocked-or-network' };
                }
            }

            $btn.on('click', async function (e) {
                if (e && e.preventDefault) e.preventDefault();
                if (e && e.stopPropagation) e.stopPropagation();
                if ($btn.data('running')) return;
                $btn.data('running', true);
                $btn.addClass('disabled').attr('aria-disabled', 'true');

                var rows = [];
                $('.typecho-list-table tbody tr[id^="lid-"]').each(function () {
                    var $tr = $(this);
                    var url = ($tr.data('url') || '').toString().trim();
                    if (!url) return;
                    rows.push({ $tr: $tr, url: url });
                });

                $('.typecho-list-table tbody tr').removeClass('link-check-ok link-check-fail link-check-redirect').find('.link-check-hint').remove();

                $btn.text('检查中…');

                try {
                    await runPool(rows, async function (item) {
                        markState(item.$tr, 'pending', '检查中…');
                        var res = await probeUrl(item.url, 8000);
                        if (!res) {
                            markState(item.$tr, 'fail', '后端接口不可用');
                            return;
                        }

                        // 后端返回 status=0：表示没拿到 HTTP 状态码。
                        // 此时后端已做“TCP 80/443 连通性”兜底，并把结论映射在 error 文案里。
                        if (res.ok !== true || (typeof res.status === 'number' && res.status <= 0)) {
                            var msg = (res && res.error) ? String(res.error) : '无法获取状态码';

                            // 前端再次测试兜底：no-referrer 的 GET
                            // 按你的规则：前端可达 => 绿色；否则继续走红/灰
                            try {
                                var p = await probeUrlNoReferrer(item.url, 3500);
                                if (p && p.ok) {
                                    markState(item.$tr, 'ok', '可访问 (状态未知)');
                                    return;
                                }
                            } catch (e) {}

                            // 前端兜底 TCP 检测（支持跨域）——实际通过后端完成 TCP 探测，前端只负责展示
                            // - 主机不可达/端口不可达：明确失败
                            // - 主机可达但 HTTP 失败：灰色 uncertain
                            if (msg.indexOf('主机不可达') !== -1 || msg.indexOf('端口不可达') !== -1 || msg.indexOf('无法解析域名') !== -1) {
                                markState(item.$tr, 'fail', msg);
                            } else {
                                markState(item.$tr, 'uncertain', msg);
                            }
                            return;
                        }

                        if (res.status === 200 || res.status === 206) {
                            markState(item.$tr, 'ok', res.status + ' 可访问');
                        } else if (res.status === 301 || res.status === 302) {
                            var hint = res.status + ' 重定向';
                            if (res.finalUrl) hint += ' → ' + res.finalUrl;
                            markState(item.$tr, 'redirect', hint);
                        } else if (res.status === 404) {
                            markState(item.$tr, 'fail', '404 请求失败');
                        } else {
                            markState(item.$tr, 'fail', res.status + ' 请求失败');
                        }
                    }, 6);
                } finally {
                    $btn.text('一键检查');
                    $btn.data('running', false);
                    $btn.removeClass('disabled').removeAttr('aria-disabled');
                }
            });
        })();
    });
})();
</script>
<?php include 'footer.php'; ?>

<?php /** Links by 懵仙兔兔 */ ?>
