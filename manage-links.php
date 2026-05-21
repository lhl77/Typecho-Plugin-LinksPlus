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
$editMode = isset($request->lid) && intval($request->lid) > 0;
$manageLinksUrl = $options->adminUrl . 'extending.php?panel=' . urlencode('Links/manage-links.php');
include 'header.php';
include 'menu.php';
?>


<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <?php echo Links_Plugin::renderRuntimeHookNotice(); ?>
        
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
                color: #1f2937!important;
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
                background: #fff;
                color: #0061a4!important;
                border-color: #0061a4;
            }
            .md3-btn.primary:hover {
                background: #f0f8ff;
            }
            .md3-btn.danger {
                background: #fff;
                color: #b3261e!important;
                border-color: #b3261e;
            }
            .md3-btn.danger:hover {
                background: #fff5f5;
            }
            .md3-btn.tonal {
                background: var(--md-primary-container);
                border-color: transparent;
                color: var(--md-on-primary-container);
            }
            .md3-btn.tonal:hover {
                background: rgba(209,228,255,.7);
            }

            /* Appbar 四个按钮颜色写死（避免被 AdminBeautify 覆盖） */
            .md3-appbar-actions .md3-btn.tonal {
                background-color: #d1e4ff !important;
                color: #001d36 !important;
            }
            .md3-appbar-actions .md3-btn:not(.tonal):not(.danger):not(.primary) {
                background-color: #ffffff !important;
                color: #1f2937 !important;
            }
            .md3-appbar-actions .md3-btn.danger {
                background-color: #ffffff !important;
                color: #b3261e !important;
            }
            .md3-appbar-actions .md3-btn.primary {
                background-color: #ffffff !important;
                color: #0061a4 !important;
            }

            /* AdminBeautify 暗色模式适配 */
            [data-theme="dark"] .md3-wrap,
            body.dark .md3-wrap,
            body.dark-mode .md3-wrap,
            html.dark .md3-wrap,
            html.dark-mode .md3-wrap {
                --md-primary: #d0bcff;
                --md-primary-container: #3f2f58;
                --md-on-primary-container: #f1dcff;
                --md-surface: #1c1b1f;
                --md-surface-variant: #49454f;
                --md-surface-container: #211f26;
                --md-surface-1: #2b2930;
                --md-outline: #938f99;
                --md-outline-variant: #49454f;
            }

            [data-theme="dark"] .md3-appbar,
            body.dark .md3-appbar,
            body.dark-mode .md3-appbar,
            html.dark .md3-appbar,
            html.dark-mode .md3-appbar,
            [data-theme="dark"] .md3-card,
            body.dark .md3-card,
            body.dark-mode .md3-card,
            html.dark .md3-card,
            html.dark-mode .md3-card {
                background: var(--md-surface) !important;
                border-color: var(--md-outline-variant) !important;
                box-shadow: 0 1px 2px rgba(0,0,0,.36), 0 4px 16px rgba(0,0,0,.28) !important;
            }

            [data-theme="dark"] .md3-appbar-title b,
            body.dark .md3-appbar-title b,
            body.dark-mode .md3-appbar-title b,
            html.dark .md3-appbar-title b,
            html.dark-mode .md3-appbar-title b {
                color: #e6e1e5 !important;
            }
            [data-theme="dark"] .md3-appbar-title span,
            body.dark .md3-appbar-title span,
            body.dark-mode .md3-appbar-title span,
            html.dark .md3-appbar-title span,
            html.dark-mode .md3-appbar-title span {
                color: #cac4d0 !important;
            }

            [data-theme="dark"] .manage-list-header,
            body.dark .manage-list-header,
            body.dark-mode .manage-list-header,
            html.dark .manage-list-header,
            html.dark-mode .manage-list-header {
                background: linear-gradient(180deg, #25232a 0%, #1f1d24 100%) !important;
                border-bottom-color: var(--md-outline-variant) !important;
            }

            [data-theme="dark"] .typecho-list-table th,
            body.dark .typecho-list-table th,
            body.dark-mode .typecho-list-table th,
            html.dark .typecho-list-table th,
            html.dark-mode .typecho-list-table th {
                background: #211f26 !important;
                color: #cac4d0 !important;
                border-bottom-color: var(--md-outline-variant) !important;
            }
            [data-theme="dark"] .typecho-list-table td,
            body.dark .typecho-list-table td,
            body.dark-mode .typecho-list-table td,
            html.dark .typecho-list-table td,
            html.dark-mode .typecho-list-table td {
                color: #e6e1e5 !important;
                border-bottom-color: var(--md-outline-variant) !important;
            }
            [data-theme="dark"] .typecho-list-table tr:hover td,
            body.dark .typecho-list-table tr:hover td,
            body.dark-mode .typecho-list-table tr:hover td,
            html.dark .typecho-list-table tr:hover td,
            html.dark-mode .typecho-list-table tr:hover td {
                background-color: var(--md-surface-1) !important;
            }

            [data-theme="dark"] input[type="text"],
            [data-theme="dark"] textarea,
            body.dark input[type="text"],
            body.dark textarea,
            body.dark-mode input[type="text"],
            body.dark-mode textarea,
            html.dark input[type="text"],
            html.dark textarea,
            html.dark-mode input[type="text"],
            html.dark-mode textarea,
            [data-theme="dark"] .dropdown-menu,
            body.dark .dropdown-menu,
            body.dark-mode .dropdown-menu,
            html.dark .dropdown-menu,
            html.dark-mode .dropdown-menu {
                background-color: #211f26 !important;
                color: #e6e1e5 !important;
                border-color: var(--md-outline-variant) !important;
            }
            [data-theme="dark"] .dropdown-menu li a,
            body.dark .dropdown-menu li a,
            body.dark-mode .dropdown-menu li a,
            html.dark .dropdown-menu li a,
            html.dark-mode .dropdown-menu li a {
                color: #e6e1e5 !important;
            }
            [data-theme="dark"] .dropdown-menu li a:hover,
            body.dark .dropdown-menu li a:hover,
            body.dark-mode .dropdown-menu li a:hover,
            html.dark .dropdown-menu li a:hover,
            html.dark-mode .dropdown-menu li a:hover {
                background: #2b2930 !important;
            }

            [data-theme="dark"] .edit-link,
            body.dark .edit-link,
            body.dark-mode .edit-link,
            html.dark .edit-link,
            html.dark-mode .edit-link {
                color: #d0bcff !important;
            }
            [data-theme="dark"] .description,
            body.dark .description,
            body.dark-mode .description,
            html.dark .description,
            html.dark-mode .description {
                color: #cac4d0 !important;
            }

            [data-theme="dark"] .md3-appbar-actions .md3-btn.tonal,
            body.dark .md3-appbar-actions .md3-btn.tonal,
            body.dark-mode .md3-appbar-actions .md3-btn.tonal,
            html.dark .md3-appbar-actions .md3-btn.tonal,
            html.dark-mode .md3-appbar-actions .md3-btn.tonal {
                background-color: #3f2f58 !important;
                color: #f1dcff !important;
            }
            [data-theme="dark"] .md3-appbar-actions .md3-btn:not(.tonal):not(.danger):not(.primary),
            body.dark .md3-appbar-actions .md3-btn:not(.tonal):not(.danger):not(.primary),
            body.dark-mode .md3-appbar-actions .md3-btn:not(.tonal):not(.danger):not(.primary),
            html.dark .md3-appbar-actions .md3-btn:not(.tonal):not(.danger):not(.primary),
            html.dark-mode .md3-appbar-actions .md3-btn:not(.tonal):not(.danger):not(.primary) {
                background-color: #2b2930 !important;
                color: #e6e1e5 !important;
            }
            [data-theme="dark"] .md3-appbar-actions .md3-btn.danger,
            body.dark .md3-appbar-actions .md3-btn.danger,
            body.dark-mode .md3-appbar-actions .md3-btn.danger,
            html.dark .md3-appbar-actions .md3-btn.danger,
            html.dark-mode .md3-appbar-actions .md3-btn.danger {
                background-color: #3d1f25 !important;
                color: #ffb4ab !important;
            }
            [data-theme="dark"] .md3-appbar-actions .md3-btn.primary,
            body.dark .md3-appbar-actions .md3-btn.primary,
            body.dark-mode .md3-appbar-actions .md3-btn.primary,
            html.dark .md3-appbar-actions .md3-btn.primary,
            html.dark-mode .md3-appbar-actions .md3-btn.primary {
                background-color: #12344f !important;
                color: #d1e4ff !important;
            }

            /* 卡片按钮暗黑模式 (默认/未分类样式重写) */
            [data-theme="dark"] .lp-link-card-action-group .md3-btn,
            body.dark .lp-link-card-action-group .md3-btn,
            body.dark-mode .lp-link-card-action-group .md3-btn,
            html.dark .lp-link-card-action-group .md3-btn,
            html.dark-mode .lp-link-card-action-group .md3-btn {
                background-color: #2b2930 !important;
                color: #e6e1e5 !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
            }
            [data-theme="dark"] .lp-link-card-action-group .md3-btn:hover,
            body.dark .lp-link-card-action-group .md3-btn:hover,
            body.dark-mode .lp-link-card-action-group .md3-btn:hover,
            html.dark .lp-link-card-action-group .md3-btn:hover,
            html.dark-mode .lp-link-card-action-group .md3-btn:hover {
                background-color: #3f3d43 !important;
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

            /* 卡片列表容器 */
            .typecho-table-wrap {
                width: 100%;
                max-width: 100%;
                padding: 16px;
                box-sizing: border-box;
            }
            .lp-card-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
                gap: 10px;
                align-items: stretch;
            }
            .lp-link-card {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 0;
                min-width: 0;
                padding: 10px 12px 18px;
                border: 1px solid var(--md-outline-variant);
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
                transition: transform .18s, box-shadow .18s, border-color .18s, background-color .18s;
            }
            .lp-link-card[hidden] {
                display: none !important;
            }
            .lp-link-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,0,0,.08), 0 12px 28px rgba(0,0,0,.08);
            }
            .lp-link-card.is-selected {
                border-color: rgba(0,97,164,.45);
                box-shadow: 0 0 0 3px rgba(0,97,164,.08), 0 6px 18px rgba(0,0,0,.05);
            }
            .lp-link-card.dragging {
                opacity: .62;
                transform: scale(.985);
                box-shadow: 0 12px 28px rgba(0,0,0,.12);
            }
            .lp-link-card.lp-drag-disabled,
            .lp-link-card.lp-drag-disabled:hover {
                transform: none;
            }
            .lp-link-card-head {
                display: flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
            }
            .lp-link-card-check {
                display: flex;
                align-items: center;
            }
            .lp-link-card-check input {
                margin: 0;
            }
            .lp-link-card-avatar {
                width: 34px;
                height: 34px;
                flex-shrink: 0;
                overflow: hidden;
                border-radius: 8px;
                border: 1px solid rgba(0,0,0,.08);
                background: #f3f4f6;
            }
            .lp-link-card-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            .lp-link-card-main {
                flex: 1;
                min-width: 0;
                display: flex;
                flex-direction: column;
                gap: 3px;
            }
            .lp-link-card-title-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 6px;
            }
            .lp-link-card-title {
                font-size: 14px;
                font-weight: 700;
                line-height: 1.3;
                color: #111827;
                text-decoration: none;
                word-break: break-word;
                flex: 1;
                min-width: 0;
            }
            .lp-link-card-url {
                display: block;
                color: #6b7280;
                font-size: 12px;
                line-height: 1.4;
                text-decoration: none;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .lp-link-card-url:hover {
                color: #374151;
                text-decoration: none;
            }
            .lp-link-card-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
                align-items: center;
            }
            .lp-card-chip {
                display: inline-flex;
                align-items: center;
                padding: 1px 6px;
                border-radius: 999px;
                background: var(--md-surface-container);
                color: #4b5563;
                font-size: 11px;
                font-weight: 600;
                max-width: 100%;
                word-break: break-word;
            }
            .lp-link-card-hint-slot {
                min-height: 0;
            }
            .lp-link-card-hint-slot .link-check-hint {
                margin-left: 0;
            }
            /* 操作按钮组（已移至标题行） */
            .lp-link-card-action-group {
                display: flex;
                flex-wrap: nowrap;
                gap: 4px;
                flex-shrink: 0;
            }
            .lp-link-card-action-group .md3-btn {
                padding: 3px 8px;
                font-size: 11px;
            }
            .lp-link-card-drag-hint {
                position: absolute;
                bottom: 4px;
                right: 8px;
                color: #9ca3af;
                font-size: 10px;
                white-space: nowrap;
                cursor: grab;
                user-select: none;
                pointer-events: none;
            }
            .lp-link-card.lp-drag-disabled .lp-link-card-drag-hint {
                cursor: default;
            }
            .lp-empty-state {
                padding: 28px 18px;
                border: 1px dashed var(--md-outline-variant);
                border-radius: 18px;
                background: linear-gradient(180deg, #ffffff 0%, #f7f8fb 100%);
                text-align: center;
                color: #6b7280;
            }
            [data-theme="dark"] .lp-link-card,
            body.dark .lp-link-card,
            body.dark-mode .lp-link-card,
            html.dark .lp-link-card,
            html.dark-mode .lp-link-card {
                background: #211f26 !important;
                border-color: var(--md-outline-variant) !important;
                box-shadow: 0 1px 2px rgba(0,0,0,.26), 0 10px 24px rgba(0,0,0,.18) !important;
            }
            [data-theme="dark"] .lp-link-card-title,
            body.dark .lp-link-card-title,
            body.dark-mode .lp-link-card-title,
            html.dark .lp-link-card-title,
            html.dark-mode .lp-link-card-title {
                color: #e6e1e5 !important;
            }
            [data-theme="dark"] .lp-link-card-url,
            body.dark .lp-link-card-url,
            body.dark-mode .lp-link-card-url,
            html.dark .lp-link-card-url,
            html.dark-mode .lp-link-card-url,
            [data-theme="dark"] .lp-link-card-drag-hint,
            body.dark .lp-link-card-drag-hint,
            body.dark-mode .lp-link-card-drag-hint,
            html.dark .lp-link-card-drag-hint,
            html.dark-mode .lp-link-card-drag-hint {
                color: #cac4d0 !important;
            }
            [data-theme="dark"] .lp-card-chip,
            body.dark .lp-card-chip,
            body.dark-mode .lp-card-chip,
            html.dark .lp-card-chip,
            html.dark-mode .lp-card-chip {
                background: #2b2930 !important;
                color: #cac4d0 !important;
            }
            [data-theme="dark"] .lp-empty-state,
            body.dark .lp-empty-state,
            body.dark-mode .lp-empty-state,
            html.dark .lp-empty-state,
            html.dark-mode .lp-empty-state {
                background: linear-gradient(180deg, #25232a 0%, #1f1d24 100%) !important;
                color: #cac4d0 !important;
                border-color: var(--md-outline-variant) !important;
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

            /* 一键检查：异常行高亮 */
            .lp-link-card.link-check-fail {
                background: #fde7e7 !important;
                border-color: rgba(217,48,37,.22) !important;
            }
            .lp-link-card.link-check-ok {
                background: #e9f7ef !important;
                border-color: rgba(30,142,62,.22) !important;
            }
            .lp-link-card.link-check-redirect {
                background: #fff4cc !important;
                border-color: rgba(185,140,0,.22) !important;
            }
            /* 一键检查：不确定/被浏览器拦截（例如 CORS/混合内容/隐私扩展导致 fetch 失败或 opaque） */
            .lp-link-card.link-check-uncertain {
                background: #f3f4f6 !important;
                border-color: rgba(107,114,128,.18) !important;
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
                white-space: nowrap;
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

            /* 筛选 Tab 栏 */
            .lp-filter-bar {
                display: flex;
                gap: 4px;
                padding: 12px 16px;
                border-bottom: 1px solid var(--md-outline-variant);
                flex-wrap: wrap;
                background: var(--md-surface);
            }
            .lp-filter-tab {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 16px;
                border-radius: 999px;
                border: 1px solid transparent;
                background: transparent;
                color: #374151;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: background .15s, color .15s;
                white-space: nowrap;
                line-height: 1.4;
            }
            .lp-filter-tab:hover {
                background: var(--md-surface-container);
            }
            .lp-filter-tab.active {
                background: var(--md-primary);
                color: #fff;
                border-color: var(--md-primary);
            }
            .lp-filter-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                background: rgba(255,255,255,.25);
                color: inherit;
                line-height: 1;
            }
            .lp-filter-tab:not(.active) .lp-filter-badge {
                background: var(--md-surface-variant);
                color: #374151;
            }
            /* 暗色模式 */
            [data-theme="dark"] .lp-filter-bar,
            body.dark .lp-filter-bar,
            body.dark-mode .lp-filter-bar,
            html.dark .lp-filter-bar,
            html.dark-mode .lp-filter-bar {
                background: var(--md-surface) !important;
                border-bottom-color: var(--md-outline-variant) !important;
            }
            [data-theme="dark"] .lp-filter-tab,
            body.dark .lp-filter-tab,
            body.dark-mode .lp-filter-tab,
            html.dark .lp-filter-tab,
            html.dark-mode .lp-filter-tab {
                color: #cac4d0;
            }
            [data-theme="dark"] .lp-filter-tab:hover,
            body.dark .lp-filter-tab:hover,
            body.dark-mode .lp-filter-tab:hover,
            html.dark .lp-filter-tab:hover,
            html.dark-mode .lp-filter-tab:hover {
                background: rgba(255,255,255,.06);
            }
            [data-theme="dark"] .lp-filter-tab.active,
            body.dark .lp-filter-tab.active,
            body.dark-mode .lp-filter-tab.active,
            html.dark .lp-filter-tab.active,
            html.dark-mode .lp-filter-tab.active {
                background: var(--md-primary);
                color: #fff;
            }
            [data-theme="dark"] .lp-filter-tab:not(.active) .lp-filter-badge,
            body.dark .lp-filter-tab:not(.active) .lp-filter-badge,
            body.dark-mode .lp-filter-tab:not(.active) .lp-filter-badge,
            html.dark .lp-filter-tab:not(.active) .lp-filter-badge,
            html.dark-mode .lp-filter-tab:not(.active) .lp-filter-badge {
                background: rgba(255,255,255,.1);
                color: #cac4d0;
            }
            @media (max-width: 600px) {
                .lp-filter-bar {
                    gap: 6px;
                    padding: 10px 12px;
                }
                .lp-filter-tab {
                    padding: 5px 12px;
                    font-size: 12px;
                }
            }

            /* 添加面板默认隐藏 */
            .editor-panel .editor-container {
                display: none;
            }
            .editor-panel .editor-container.lp-editor-visible {
                display: block;
            }
            .editor-panel-placeholder {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 12px;
                padding: 48px 24px;
                color: var(--md-outline);
                font-size: 14px;
                text-align: center;
            }
            .editor-panel-placeholder svg {
                opacity: .35;
            }
            .lp-add-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 18px;
                border-radius: 999px;
                border: 1px solid var(--md-outline-variant);
                background: var(--md-surface);
                color: #1f2937;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: background .15s;
                text-decoration: none;
            }
            .lp-add-btn:hover {
                background: var(--md-surface-container);
                text-decoration: none;
                color: #1f2937;
            }
            [data-theme="dark"] .lp-add-btn,
            body.dark .lp-add-btn,
            body.dark-mode .lp-add-btn,
            html.dark .lp-add-btn,
            html.dark-mode .lp-add-btn {
                background: #2b2930;
                color: #e6e1e5;
                border-color: var(--md-outline-variant);
            }

            /* 审核中状态 */
            .status-pending {
                background-color: #fef9c3;
                color: #854d0e;
            }
            [data-theme="dark"] .status-pending,
            body.dark .status-pending,
            body.dark-mode .status-pending,
            html.dark .status-pending,
            html.dark-mode .status-pending {
                background-color: #422006;
                color: #fde68a;
            }
            [data-theme="dark"] .lp-link-card.link-check-fail,
            body.dark .lp-link-card.link-check-fail,
            body.dark-mode .lp-link-card.link-check-fail,
            html.dark .lp-link-card.link-check-fail,
            html.dark-mode .lp-link-card.link-check-fail {
                background: #3d2327 !important;
            }
            [data-theme="dark"] .lp-link-card.link-check-ok,
            body.dark .lp-link-card.link-check-ok,
            body.dark-mode .lp-link-card.link-check-ok,
            html.dark .lp-link-card.link-check-ok,
            html.dark-mode .lp-link-card.link-check-ok {
                background: #1e3026 !important;
            }
            [data-theme="dark"] .lp-link-card.link-check-redirect,
            body.dark .lp-link-card.link-check-redirect,
            body.dark-mode .lp-link-card.link-check-redirect,
            html.dark .lp-link-card.link-check-redirect,
            html.dark-mode .lp-link-card.link-check-redirect {
                background: #3a3118 !important;
            }
            [data-theme="dark"] .lp-link-card.link-check-uncertain,
            body.dark .lp-link-card.link-check-uncertain,
            body.dark-mode .lp-link-card.link-check-uncertain,
            html.dark .lp-link-card.link-check-uncertain,
            html.dark-mode .lp-link-card.link-check-uncertain {
                background: #2b2930 !important;
            }

            /* 响应式：960px 上下布局 */
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
            /* 700px：缩紧卡片与工具栏 */
            @media (max-width: 700px) {
                .typecho-table-wrap {
                    padding: 10px;
                }
                .lp-link-card {
                    padding: 9px 10px 16px;
                    border-radius: 12px;
                }
                .lp-link-card-head {
                    gap: 6px;
                }
                .manage-list-header {
                    padding: 10px;
                    flex-wrap: wrap;
                    gap: 8px;
                }
            }
            /* 480px：小屏幕按钮换行 */
            @media (max-width: 480px) {
                .lp-link-card-avatar {
                    width: 30px;
                    height: 30px;
                    border-radius: 6px;
                }
                .lp-link-card-title-row {
                    flex-wrap: wrap;
                }
                .lp-link-card-action-group {
                    width: 100%;
                    justify-content: flex-end;
                }
                .lp-link-card-action-group .md3-btn {
                    flex: 1;
                    justify-content: center;
                }
                .md3-appbar-actions {
                    flex-wrap: wrap;
                    gap: 6px;
                }
                .md3-appbar-actions .md3-btn {
                    padding: 6px 10px;
                    font-size: 12px;
                }
            }
        </style>

        <div class="md3-wrap">
            <div class="md3-appbar">
                <div class="md3-appbar-title">
                    <b><?php _e($editMode ? '编辑友链' : '友情链接'); ?></b>
                    <span><?php $editMode ? _e('修改后点击编辑友链') : _e('拖拽排序 / 批量操作'); ?></span>
                </div>
                <div class="md3-appbar-actions">
                    <?php if ($editMode): ?>
                    <a class="md3-btn" href="<?php echo htmlspecialchars($manageLinksUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php _e('← 返回列表'); ?></a>
                    <?php endif; ?>
                    <a class="md3-btn tonal" href="<?php $options->adminUrl('options-plugin.php?config=Links'); ?>"><?php _e('设置'); ?></a>
                    
                    <a class="md3-btn" href="https://blog.lhl.one/artical/902.html " target="_blank"><?php _e('帮助'); ?></a>
                    <a class="md3-btn danger" href="javascript:void(0)" id="linksPlusCheckAll" title="通过后端代理检测每个友链是否可访问（可获取真实状态码，避免浏览器跨域/CORS 限制）"><?php _e('一键检查'); ?></a>
                    <a class="md3-btn primary" href="<?php $security->index('/action/links-edit?do=rewrite'); ?>" title="将指定 cid 文章正文中的 {{links_plus}} 占位符替换为友链 HTML" onclick="return confirm('确认要执行正文重写吗？该操作会直接修改文章/页面正文内容。[更推荐使用短代码功能]');"><?php _e('执行重写'); ?></a>
                </div>
            </div>

        <?php if ($editMode): ?>
        <!-- 专门的编辑页面：全宽编辑表单 -->
        <div class="row typecho-page-main">
            <div class="col-mb-12">
                <div class="md3-card" role="form" style="max-width:700px;margin:0 auto;">
                    <div class="editor-container">
                        <?php Links_Plugin::form()->render(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row typecho-page-main manage-metas">
                <!-- 左侧：列表 -->
                <div class="manage-list-panel md3-card" role="main">
                    <?php
                        $prefix = $db->getPrefix();
                        $links = $db->fetchAll($db->select()->from($prefix.'links')->order($prefix.'links.order', Typecho_Db::SORT_ASC));
                        $cnt_all     = count($links);
                        $cnt_enabled = 0; $cnt_pending = 0; $cnt_disabled = 0;
                        foreach ($links as $lk) {
                            if ($lk['state'] == 1)     $cnt_enabled++;
                            elseif ($lk['state'] == 2) $cnt_pending++;
                            elseif ($lk['state'] == 0) $cnt_disabled++;
                        }
                    ?>

                    <!-- 筛选 Tab -->
                    <div class="lp-filter-bar">
                        <button class="lp-filter-tab active" data-filter="all"><?php _e('全部'); ?></button>
                        <button class="lp-filter-tab" data-filter="1"><?php _e('已启用'); ?> <span class="lp-filter-badge"><?php echo $cnt_enabled; ?></span></button>
                        <button class="lp-filter-tab" data-filter="2"><?php _e('待审核'); ?> <span class="lp-filter-badge"><?php echo $cnt_pending; ?></span></button>
                        <button class="lp-filter-tab" data-filter="0"><?php _e('已禁用'); ?></button>
                    </div>

                    <form method="post" name="manage_categories" class="operate-form">
                    <input type="hidden" name="reason" id="lpRejectReasonInput">

                    <div class="manage-list-header">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=delete'); ?>"><?php _e('删除'); ?></a></li>
                                    <li><a lang="<?php _e('你确认要通过这些待审核友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=approve'); ?>"><?php _e('通过审核'); ?></a></li>
                                    <li><a data-lp-reject-bulk="1" href="<?php $security->index('/action/links-edit?do=reject'); ?>"><?php _e('驳回审核'); ?></a></li>
                                    <li><a lang="<?php _e('你确认要启用这些友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=enable'); ?>"><?php _e('启用'); ?></a></li>
                                    <li><a lang="<?php _e('你确认要禁用这些友链吗?'); ?>" href="<?php $security->index('/action/links-edit?do=prohibit'); ?>"><?php _e('禁用'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="lp-add-btn" id="lpShowAddForm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            <?php _e('添加友链'); ?>
                        </button>
                    </div>

                    <div class="typecho-table-wrap">
                        <div class="lp-card-grid" id="lpCardGrid">
                            <?php if (!empty($links)): $alt = 0;?>
                            <?php foreach ($links as $link): ?>
                            <article class="lp-link-card" id="lid-<?php echo $link['lid']; ?>" data-lid="<?php echo intval($link['lid']); ?>" data-url="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" data-state="<?php echo intval($link['state']); ?>" draggable="true">
                                <div class="lp-link-card-head">
                                    <label class="lp-link-card-check">
                                        <input type="checkbox" class="lp-card-check" value="<?php echo $link['lid']; ?>" name="lid[]"/>
                                    </label>
                                    <div class="lp-link-card-avatar">
                                        <?php
                                            if ($link['image']) {
                                                echo '<a href="'.$link['image'].'" title="'._t('点击放大').'" target="_blank"><img class="avatar" src="'.$link['image'].'" alt="'.$link['name'].'" width="48" height="48"/></a>';
                                            } else {
                                                $options = Typecho_Widget::widget('Widget_Options');
                                                $nopic_url = Typecho_Common::url('usr/plugins/Links/nopic.png', $options->siteUrl);
                                                echo '<img class="avatar" src="'.$nopic_url.'" alt="NOPIC" width="48" height="48"/>';
                                            }
                                        ?>
                                    </div>
                                    <div class="lp-link-card-main">
                                        <div class="lp-link-card-title-row">
                                            <a class="edit-link lp-link-card-title" href="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>" title="<?php _e('点击编辑'); ?>"><?php echo $link['name']; ?></a>
                                            <div class="lp-link-card-action-group">
                                                <?php if ((int)$link['state'] === 2): ?>
                                                <a class="md3-btn tonal" href="<?php $security->index('/action/links-edit?do=approve&lid=' . intval($link['lid'])); ?>" onclick="return confirm('确认通过该友链申请吗？');"><?php _e('通过'); ?></a>
                                                <a class="md3-btn danger" href="<?php $security->index('/action/links-edit?do=reject&lid=' . intval($link['lid'])); ?>" onclick="lpRejectSingle(event, this); return false;"><?php _e('驳回'); ?></a>
                                                <?php endif; ?>
                                                <a class="md3-btn" href="<?php echo $link['url']; ?>" target="_blank" rel="noopener"><?php _e('访问'); ?></a>
                                                <a class="md3-btn" href="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>"><?php _e('编辑'); ?></a>
                                            </div>
                                        </div>
                                        <a class="lp-link-card-url" href="<?php echo $link['url']; ?>" target="_blank" rel="noopener"><i class="i-exlink"></i> <?php echo $link['url']; ?></a>
                                        <div class="lp-link-card-meta">
                                            <?php if (!empty($link['sort'])): ?>
                                            <span class="lp-card-chip"><?php echo $link['sort']; ?></span>
                                            <?php endif; ?>
                                            <?php
                                                if ($link['state'] == 1) {
                                                    echo '<span class="status-tag status-normal">已启用</span>';
                                                } elseif ($link['state'] == 2) {
                                                    echo '<span class="status-tag status-pending">待审核</span>';
                                                } elseif ($link['state'] == 0) {
                                                    echo '<span class="status-tag status-ban">已禁用</span>';
                                                }
                                            ?>
                                        </div>
                                        <div class="lp-link-card-hint-slot"></div>
                                    </div>
                                </div>
                                <span class="lp-link-card-drag-hint"><?php _e('⠿ 拖拽'); ?></span>
                            </article>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="lp-empty-state"><h6 class="typecho-list-table-title"><?php _e('没有任何友链'); ?></h6></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    </form>
                </div>

                <!-- 右侧：编辑表单（添加新友链）-->
                <div class="editor-panel md3-card" role="form">
                    <div class="editor-panel-placeholder" id="lpEditorPlaceholder">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        <span><?php _e('点击「添加友链」或列表中的「编辑」按钮'); ?></span>
                    </div>
                    <div class="editor-container" id="lpEditorContainer">
                         <?php Links_Plugin::form()->render(); ?>
                    </div>
                </div>
        </div>
        <?php endif; ?>
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
        var $form = $('.operate-form');
        var $cardGrid = $('#lpCardGrid');
        var $selectAll = $('.typecho-table-select-all');
        var sortApi = '<?php $security->index('/action/links-edit?do=sort'); ?>';
        var currentFilter = 'all';

        function getCards() {
            return $cardGrid.find('.lp-link-card');
        }

        function getVisibleCards() {
            return getCards().filter(function () {
                return !this.hidden;
            });
        }

        function updateSelectAllState() {
            var $visibleChecks = getVisibleCards().find('.lp-card-check');
            var total = $visibleChecks.length;
            var checked = $visibleChecks.filter(':checked').length;
            $selectAll.prop('checked', total > 0 && checked === total);
            $selectAll.prop('indeterminate', checked > 0 && checked < total);
        }

        function setCardSelected($card, selected) {
            $card.toggleClass('is-selected', !!selected);
            $card.find('.lp-card-check').prop('checked', !!selected);
        }

        function updateDragAvailability() {
            var enabled = currentFilter === 'all';
            getCards().each(function () {
                this.draggable = enabled;
                $(this)
                    .toggleClass('lp-drag-disabled', !enabled)
                    .find('.lp-link-card-drag-hint')
                    .text(enabled ? '\u28ff \u62d6\u62fd' : '');
            });
        }

        function submitSortOrder() {
            var ids = [];
            getCards().each(function () {
                ids.push($(this).data('lid'));
            });
            $.post(sortApi, $.param({lid: ids}));
        }

        function getDragAfterElement(container, y) {
            var cards = [].slice.call(container.querySelectorAll('.lp-link-card:not(.dragging):not([hidden])'));
            var closest = { offset: Number.NEGATIVE_INFINITY, element: null };
            cards.forEach(function (card) {
                var box = card.getBoundingClientRect();
                var offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    closest = { offset: offset, element: card };
                }
            });
            return closest.element;
        }

        if ($cardGrid.length) {
            $cardGrid.on('change', '.lp-card-check', function () {
                $(this).closest('.lp-link-card').toggleClass('is-selected', this.checked);
                updateSelectAllState();
            });

            $selectAll.on('change', function () {
                var checked = this.checked;
                getVisibleCards().each(function () {
                    setCardSelected($(this), checked);
                });
                updateSelectAllState();
            });

            var draggedCard = null;
            $cardGrid.on('dragstart', '.lp-link-card', function (e) {
                if (currentFilter !== 'all') {
                    e.preventDefault();
                    return false;
                }
                draggedCard = this;
                this.classList.add('dragging');
                if (e.originalEvent && e.originalEvent.dataTransfer) {
                    e.originalEvent.dataTransfer.effectAllowed = 'move';
                    try {
                        e.originalEvent.dataTransfer.setData('text/plain', String($(this).data('lid')));
                    } catch (err) {}
                }
            });

            $cardGrid.on('dragend', '.lp-link-card', function () {
                this.classList.remove('dragging');
                if (draggedCard) {
                    submitSortOrder();
                    draggedCard = null;
                }
            });

            $cardGrid.get(0).addEventListener('dragover', function (e) {
                if (currentFilter !== 'all' || !draggedCard) {
                    return;
                }
                e.preventDefault();
                var afterElement = getDragAfterElement(this, e.clientY);
                if (!afterElement) {
                    this.appendChild(draggedCard);
                } else if (afterElement !== draggedCard) {
                    this.insertBefore(draggedCard, afterElement);
                }
            });

            updateDragAvailability();
            updateSelectAllState();
        }

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.dropdown-menu button.merge').click(function () {
            var btn = $(this);
            btn.parents('form').attr('action', btn.attr('rel')).submit();
        });

        // AdminBeautify 兼容：confirm/prompt Promise 包装
        // AdminBeautify 会接管 window.confirm/prompt（同步返回 false/null），
        // 改为调用其 Promise API；若未安装则回退到原生方法。
        function lpConfirm(message) {
            if (window.AdminBeautify && typeof AdminBeautify.confirm === 'function') {
                return AdminBeautify.confirm(message);
            }
            var ok = (window.AdminBeautify && typeof AdminBeautify.nativeConfirm === 'function')
                ? AdminBeautify.nativeConfirm(message)
                : window.confirm(message);
            return Promise.resolve(ok);
        }
        function lpPrompt(message, defaultVal) {
            if (window.AdminBeautify && typeof AdminBeautify.prompt === 'function') {
                return AdminBeautify.prompt(message, defaultVal || '');
            }
            var val = (window.AdminBeautify && typeof AdminBeautify.nativePrompt === 'function')
                ? AdminBeautify.nativePrompt(message, defaultVal || '')
                : window.prompt(message, defaultVal || '');
            return Promise.resolve(val);
        }

        // 单条驳回：弹出理由输入框后跳转 GET URL
        window.lpRejectSingle = function (e, anchor) {
            e.preventDefault();
            var url = anchor.getAttribute('href');
            lpPrompt('<?php _e('请输入驳回原因（可留空，最多 120 字）：'); ?>', '').then(function (reason) {
                if (reason === null) return;
                if (reason.length > 120) reason = reason.substring(0, 120);
                window.location.href = url + (reason ? '&reason=' + encodeURIComponent(reason) : '');
            });
        };

        $('.dropdown-menu a').on('click', function (e) {
            var href = $(this).attr('href');
            var confirmText = $(this).attr('lang');
            var isReject = !!$(this).data('lpRejectBulk');
            var $selected = $cardGrid.find('.lp-card-check:checked');

            e.preventDefault();

            if (!$selected.length) {
                window.alert('<?php _e('请先选择至少一个友链'); ?>');
                return false;
            }

            if (isReject) {
                lpConfirm('<?php _e('你确认要驳回这些待审核友链吗?'); ?>').then(function (ok) {
                    if (!ok) return;
                    lpPrompt('<?php _e('请输入驳回原因（可留空，最多 120 字）：'); ?>', '').then(function (reason) {
                        if (reason === null) return;
                        if (reason.length > 120) reason = reason.substring(0, 120);
                        var $ri = $form.find('#lpRejectReasonInput');
                        if ($ri.length) $ri.val(reason);
                        if ($form.length) {
                            $form.attr('action', href);
                            $form.get(0).submit();
                        }
                    });
                });
            } else if (confirmText) {
                lpConfirm(confirmText).then(function (ok) {
                    if (!ok) return;
                    if ($form.length) {
                        $form.attr('action', href);
                        $form.get(0).submit();
                    }
                });
            } else {
                if ($form.length) {
                    $form.attr('action', href);
                    $form.get(0).submit();
                }
            }
            return false;
        });

        <?php if (isset($request->lid)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>

        // 筛选 Tab
        (function () {
            var tabs = document.querySelectorAll('.lp-filter-tab');
            if (!tabs.length) return;
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    tab.classList.add('active');
                    var filter = tab.getAttribute('data-filter');
                    currentFilter = filter;
                    var cards = document.querySelectorAll('.lp-link-card[data-state]');
                    cards.forEach(function (card) {
                        var visible = filter === 'all' || card.getAttribute('data-state') === filter;
                        card.hidden = !visible;
                        if (!visible) {
                            setCardSelected($(card), false);
                        }
                    });
                    updateDragAvailability();
                    updateSelectAllState();
                });
            });
        })();

        // 添加友链按钮展开右侧表单
        (function () {
            var addBtn = document.getElementById('lpShowAddForm');
            var container = document.getElementById('lpEditorContainer');
            var placeholder = document.getElementById('lpEditorPlaceholder');
            if (!addBtn || !container) return;
            addBtn.addEventListener('click', function () {
                if (placeholder) placeholder.style.display = 'none';
                container.classList.add('lp-editor-visible');
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        })();

        // 一键检查：后端代理检测可用性（避免前端 CORS 影响，能拿到真实状态码）
        (function initLinksPlusCheckAll() {
            var $btn = $('#linksPlusCheckAll');
            if (!$btn.length) return;

            var checkApi = '<?php $security->index('/action/links-edit?do=check-link'); ?>';

            function setHint($card, text, cls) {
                var $slot = $card.find('.lp-link-card-hint-slot');
                $slot.find('.link-check-hint').remove();
                if (!text) return;
                var $hint = $('<span class="link-check-hint" />').text(text);
                if (cls) $hint.addClass(cls);
                $slot.append($hint);
            }

            function markState($card, state, msg) {
                $card.removeClass('link-check-ok link-check-fail link-check-redirect link-check-uncertain');
                if (state === 'ok') {
                    $card.addClass('link-check-ok');
                    setHint($card, msg || '200 可访问', 'ok');
                } else if (state === 'redirect') {
                    $card.addClass('link-check-redirect');
                    setHint($card, msg || '301/302 重定向', 'redirect');
                } else if (state === 'uncertain') {
                    $card.addClass('link-check-uncertain');
                    setHint($card, msg || '浏览器限制：无法获取状态码', 'uncertain');
                } else if (state === 'fail') {
                    $card.addClass('link-check-fail');
                    setHint($card, msg || '请求失败', 'fail');
                } else {
                    setHint($card, msg || '检查中…');
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
                $cardGrid.find('.lp-link-card[id^="lid-"]').each(function () {
                    var $card = $(this);
                    var url = ($card.data('url') || '').toString().trim();
                    if (!url) return;
                    rows.push({ $card: $card, url: url });
                });

                $cardGrid.find('.lp-link-card').removeClass('link-check-ok link-check-fail link-check-redirect link-check-uncertain');
                $cardGrid.find('.link-check-hint').remove();

                $btn.text('检查中…');

                try {
                    await runPool(rows, async function (item) {
                        markState(item.$card, 'pending', '检查中…');
                        var res = await probeUrl(item.url, 8000);
                        if (!res) {
                            markState(item.$card, 'fail', '后端接口不可用');
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
                                    markState(item.$card, 'ok', '可访问 (状态未知)');
                                    return;
                                }
                            } catch (e) {}

                            // 前端兜底 TCP 检测（支持跨域）——实际通过后端完成 TCP 探测，前端只负责展示
                            // - 主机不可达/端口不可达：明确失败
                            // - 主机可达但 HTTP 失败：灰色 uncertain
                            if (msg.indexOf('主机不可达') !== -1 || msg.indexOf('端口不可达') !== -1 || msg.indexOf('无法解析域名') !== -1) {
                                markState(item.$card, 'fail', msg);
                            } else {
                                markState(item.$card, 'uncertain', msg);
                            }
                            return;
                        }

                        if (res.status === 200 || res.status === 206) {
                            markState(item.$card, 'ok', res.status + ' 可访问');
                        } else if (res.status === 301 || res.status === 302) {
                            var hint = res.status + ' 重定向';
                            if (res.finalUrl) hint += ' → ' + res.finalUrl;
                            markState(item.$card, 'redirect', hint);
                        } else if (res.status === 404) {
                            markState(item.$card, 'fail', '404 请求失败');
                        } else {
                            markState(item.$card, 'fail', res.status + ' 请求失败');
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
