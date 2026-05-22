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
$licenseState = Links_Plugin::getLicenseState();
$licensed = !empty($licenseState['authorized']);
$editMode = $licensed && isset($request->lid) && intval($request->lid) > 0;
$manageLinksUrl = $options->adminUrl . 'extending.php?panel=' . urlencode('Links/manage-links.php');
include 'header.php';
include 'menu.php';
?>


<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        
        <?php echo '<style id="links-plus-admin-manage-style">' . Links_Plugin::getAssetContent('LinksAdminManage.css') . '</style>'; ?>

        <div class="md3-wrap">
            <div class="md3-notice-stack">
                <?php echo Links_Plugin::renderRuntimeHookNotice(); ?>
                <?php if (!$licensed): ?>
                <?php echo Links_Plugin::renderLicenseUpsellCardHtml('manage', $licenseState); ?>
                <?php endif; ?>
            </div>
            <div class="md3-appbar">
                <div class="md3-appbar-title">
                    <b><?php _e($editMode ? '编辑友链' : ($licensed ? '友情链接' : '友情链接（只读）')); ?></b>
                    <span><?php $editMode ? _e('修改后点击编辑友链') : ($licensed ? _e('拖拽排序 / 批量操作') : _e('未授权状态下仅可查看友链列表')); ?></span>
                </div>
                <div class="md3-appbar-actions">
                    <?php if ($licensed && $editMode): ?>
                    <a class="md3-btn" href="<?php echo htmlspecialchars($manageLinksUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php _e('← 返回列表'); ?></a>
                    <?php endif; ?>
                    <a class="md3-btn tonal" href="<?php $options->adminUrl('options-plugin.php?config=Links'); ?>"><?php _e('设置'); ?></a>
                    
                    <a class="md3-btn" href="https://blog.lhl.one/artical/902.html " target="_blank"><?php _e('帮助'); ?></a>
                    <?php if ($licensed): ?>
                    <a class="md3-btn danger" href="javascript:void(0)" id="linksPlusCheckAll" title="通过后端代理检测每个友链是否可访问（可获取真实状态码，避免浏览器跨域/CORS 限制）"><?php _e('一键检查'); ?></a>
                    <a class="md3-btn primary" href="<?php $security->index('/action/links-edit?do=rewrite'); ?>" title="将指定 cid 文章正文中的 {{links_plus}} 占位符替换为友链 HTML" onclick="return confirm('确认要执行正文重写吗？该操作会直接修改文章/页面正文内容。[更推荐使用短代码功能]');"><?php _e('执行重写'); ?></a>
                    <?php else: ?>
                    <a class="md3-btn primary" href="<?php echo htmlspecialchars(Links_Plugin::getLicensePurchaseUrl(), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php _e('购买 Links+'); ?></a>
                    <?php endif; ?>
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
                            <?php if ($licensed): ?>
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
                            <?php else: ?>
                            <span class="description" style="margin:0"><?php _e('当前为只读模式：可查看列表，购买并填写授权码后可新增、编辑、审核、重写和启用短代码/申请功能。'); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($licensed): ?>
                        <button type="button" class="lp-add-btn" id="lpShowAddForm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            <?php _e('添加友链'); ?>
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="typecho-table-wrap">
                        <div class="lp-card-grid" id="lpCardGrid">
                            <?php if (!empty($links)): $alt = 0;?>
                            <?php foreach ($links as $link): ?>
                            <article class="lp-link-card<?php echo $licensed ? '' : ' lp-drag-disabled'; ?>" id="lid-<?php echo $link['lid']; ?>" data-lid="<?php echo intval($link['lid']); ?>" data-url="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" data-state="<?php echo intval($link['state']); ?>" draggable="<?php echo $licensed ? 'true' : 'false'; ?>">
                                <div class="lp-link-card-head">
                                    <?php if ($licensed): ?>
                                    <label class="lp-link-card-check">
                                        <input type="checkbox" class="lp-card-check" value="<?php echo $link['lid']; ?>" name="lid[]"/>
                                    </label>
                                    <?php endif; ?>
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
                                            <?php if ($licensed): ?>
                                            <a class="edit-link lp-link-card-title" href="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>" title="<?php _e('点击编辑'); ?>"><?php echo $link['name']; ?></a>
                                            <?php else: ?>
                                            <span class="lp-link-card-title"><?php echo $link['name']; ?></span>
                                            <?php endif; ?>
                                            <div class="lp-link-card-action-group">
                                                <?php if ($licensed && (int)$link['state'] === 2): ?>
                                                <a class="md3-btn tonal" href="<?php $security->index('/action/links-edit?do=approve&lid=' . intval($link['lid'])); ?>" onclick="return confirm('确认通过该友链申请吗？');"><?php _e('通过'); ?></a>
                                                <a class="md3-btn danger" href="<?php $security->index('/action/links-edit?do=reject&lid=' . intval($link['lid'])); ?>" onclick="lpRejectSingle(event, this); return false;"><?php _e('驳回'); ?></a>
                                                <?php endif; ?>
                                                <a class="md3-btn" href="<?php echo $link['url']; ?>" target="_blank" rel="noopener"><?php _e('访问'); ?></a>
                                                <?php if ($licensed): ?>
                                                <a class="md3-btn" href="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>"><?php _e('编辑'); ?></a>
                                                <?php endif; ?>
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
                                <?php if ($licensed): ?>
                                <span class="lp-link-card-drag-hint"><?php _e('⠿ 拖拽'); ?></span>
                                <?php endif; ?>
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
                <?php if ($licensed): ?>
                <div class="editor-panel md3-card" role="form">
                    <div class="editor-panel-placeholder" id="lpEditorPlaceholder">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        <span><?php _e('点击「添加友链」或列表中的「编辑」按钮'); ?></span>
                    </div>
                    <div class="editor-container" id="lpEditorContainer">
                         <?php Links_Plugin::form()->render(); ?>
                    </div>
                </div>
                <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';

$linksPlusManageConfig = array(
    'emailLogoApi' => Helper::security()->getIndex('/action/links-edit'),
    'sortApi' => Helper::security()->getIndex('/action/links-edit?do=sort'),
    'checkApi' => Helper::security()->getIndex('/action/links-edit?do=check-link'),
    'messages' => array(
        'rejectReasonPrompt' => _t('请输入驳回原因（可留空，最多 120 字）：'),
        'selectAtLeastOne' => _t('请先选择至少一个友链'),
        'rejectBulkConfirm' => _t('你确认要驳回这些待审核友链吗?'),
    ),
    'readonly' => !$licensed,
    'editMode' => isset($request->lid),
);
?>
<script id="links-plus-admin-manage-script">window.LinksPlusManageConfig=<?php echo json_encode($linksPlusManageConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;<?php echo Links_Plugin::getAssetContent('LinksAdminManage.js'); ?>;if(window.LinksPlusManageAsset&&typeof window.LinksPlusManageAsset.init==="function"){window.LinksPlusManageAsset.init(window.LinksPlusManageConfig);}</script>
<?php include 'footer.php'; ?>

<?php /** Links by 懵仙兔兔 */ ?>
