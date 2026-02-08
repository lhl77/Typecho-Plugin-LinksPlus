# Typecho 插件：Links Plus（友情链接增强版）

[![Latest tag](https://img.shields.io/github/v/tag/lhl77/Typecho-Plugin-LinksPlus?label=tag&sort=semver)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/tags)
[![Release](https://img.shields.io/github/v/release/lhl77/Typecho-Plugin-LinksPlus?label=release&sort=semver)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/releases)
[![Stars](https://img.shields.io/github/stars/lhl77/Typecho-Plugin-LinksPlus?style=flat)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/stargazers)
[![License](https://img.shields.io/github/license/lhl77/Typecho-Plugin-LinksPlus)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/blob/main/LICENSE)
[![Typecho](https://img.shields.io/badge/Typecho-Plugin-blue)](https://typecho.org/)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.2-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Status](https://img.shields.io/badge/Status-Active-success)](#)

友情链接增强插件，支持多模板、短代码、正文重写、MD3 / Mirages 卡片展示、后台友链管理与前端版本检测。

---

## 功能清单

- 多模板输出（内置 `md3-cards`, `mirages-html`，支持自定义模板目录 `templates/`）
- 可选短代码 `[links_plus]`（动态输出友链，无需重写正文）
- 支持 `<links>...</links>` 标签与参数（向后兼容）
- 正文重写（支持按 cid 重写、块标记、可多次重写）
- 后台友链管理：添加/编辑/分类/拖拽排序/启用禁用
- 响应式卡片网格、暗色模式适配、避免主题全局 a{} 覆盖（使用 role=link）
- 模板可携带 CSS/JS 注入，支持模板级别交互（ripple、键盘访问等）
- 配置页内置 GitHub 版本检查

---

## 环境要求

- Typecho（1.2.x+）
- PHP 7.2+

---

## 安装

1. 下载本插件并解压到：
	- `usr/plugins/Links/`
2. 确认目录结构包含：
	- `usr/plugins/Links/Plugin.php`
	- `usr/plugins/Links/manage-links.php`
	- `usr/plugins/Links/templates/`（内含模板）
3. 后台 → 控制台 → 插件，启用 **Links Plus**。

---

## 配置说明

后台 → 插件 → Links Plus：

### 模板选择
- 支持文件模板（`templates/<name>/`），`manifest.json` 控制 CSS/JS 注入。

### 短代码 `[links_plus]`
- 在配置中启用后，正文内使用 `[links_plus]` 即可动态输出友链。
- 支持参数示例：`[links_plus num=6 sort=friends size=48 template=mirages-html]`

### 正文重写
- 当主题不走 `contentEx` 导致 `<links>...</links>` 无法解析时，可使用“正文重写”将占位符替换为友链 HTML。
- 支持按 `cid` 重写、块标记 `<!-- LINKS_PLUS_START -->...<!-- LINKS_PLUS_END -->`，并可选择输出模板。

### 高级自定义
- 保留旧版源码规则（SHOW_TEXT/SHOW_IMG/SHOW_MIX）用于兼容；优先推荐使用文件模板管理输出。

---

## 模板说明

- 模板目录为 `templates/<name>/`。
- 必要文件：`manifest.json`、`template.html`。
- 可选文件：`style.css`、`script.js`（`manifest.json` 中 `inject` 决定是否注入）。
- 模板占位符：`{name}` `{url}` `{image}` `{description}` `{sort}` `{lid}` 等。

---

## 短代码与标签示例

- 短代码：`[links_plus]` 或带参数 ` [links_plus num=8 sort=blog template=md3-cards]`
- 标签：`<links 10 friends 48>SHOW_IMG</links>`（向后兼容）

---

## 后台管理

- 后台 → 扩展 → 友情链接（管理界面为 MD3 风格卡片与表格管理）
- 支持批量导入/导出、按分类过滤、图片尺寸设置、默认图片配置等

---

## 更新与版本检查

- 插件配置页提供 GitHub tags 版本检查（按 tag 名称 `vX.Y.Z` 对比）。

---

## 常见问题

1. 短代码/标签未生效
- 检查是否已在插件设置中启用短代码；主题若直接打印数据库内容未走 `contentEx`，请使用正文重写功能。

2. 样式被主题覆盖
- 已尽力避免使用 `<a>` 标签直接输出，模板采用 `role="link"` + `data-href` 的跳转方案；如仍被覆盖可在自定义模板中引入更强选择器或 `!important`。

3. 模板资源未注入
- 确认模板下 `manifest.json` 中 `inject.css`/`inject.js` 设置为 `true` 并且前端没有被 CSP 等策略阻止。

---

## 仓库与帮助

- 插件仓库： https://github.com/lhl77/Typecho-Plugin-LinksPlus
- 使用帮助 / 旧文档： https://2dph.com/archives/typecho-links-help.html

如果你需要更详细的开发/模板示例，可以在仓库 Issues 或 PR 提问。

---

## 许可

MIT（以仓库 `LICENSE` 为准）
