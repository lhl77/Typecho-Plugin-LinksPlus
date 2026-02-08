# Typecho 插件：Links Plus（友链管理增强版）

[![Latest tag](https://img.shields.io/github/v/tag/lhl77/Typecho-Plugin-LinksPlus?label=tag&sort=semver)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/tags)
[![Release](https://img.shields.io/github/v/release/lhl77/Typecho-Plugin-LinksPlus?label=release&sort=semver)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/releases)
[![Stars](https://img.shields.io/github/stars/lhl77/Typecho-Plugin-LinksPlus?style=flat)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/stargazers)
[![License](https://img.shields.io/github/license/lhl77/Typecho-Plugin-LinksPlus)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/blob/main/LICENSE)
[![Typecho](https://img.shields.io/badge/Typecho-Plugin-blue)](https://typecho.org/)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.2-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Status](https://img.shields.io/badge/Status-Active-success)](#)

友情链接增强插件，支持多模板、短代码、正文重写、内置友链卡片展示主题、后台友链管理。

修改自第三方维护者版本v1.2.7: https://github.com/Mejituu/Links

原作者：https://www.imhan.com/archives/typecho-links/

内置主题如：

![截图](/templates/md3-cards/image.png)

更多主题请查看 https://blog.lhl.one/artical/892.html

---

## 目录

- [功能](#增强版功能清单)
- [环境要求](#环境要求)
- [安装](#安装)
- [说明](#配置说明)
	- [模板选择](#模板选择)
	- [正文重写](#正文重写)
	- [高级自定义](#高级自定义)
    - [后台管理](#后台管理)
- [主题](#主题)
- [常见问题](#常见问题)
- [仓库与帮助](#仓库与帮助)
- [许可](#许可)

---

## 增强版功能清单

- 多模板输出（内置主题，支持自定义模板目录 `templates/`）
- 模板可携带 CSS/JS 注入，支持模板级别交互
- 正文重写（支持按 cid 重写、块标记、可多次重写）

## 原版功能保留
- 支持 `<links>...</links>` 标签与参数（向后兼容）
- 后台友链管理：添加/编辑/分类/拖拽排序/启用禁用

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

### 正文重写
- 当主题不走 `contentEx` 导致 `<links>...</links>` 无法解析时，可使用“正文重写”将占位符替换为友链 HTML。
- 支持按 `cid` 重写、块标记 `<!-- LINKS_PLUS_START -->...<!-- LINKS_PLUS_END -->`，并可选择输出模板。

### 高级自定义
- 保留旧版源码规则（SHOW_TEXT/SHOW_IMG/SHOW_MIX）用于兼容；优先推荐使用文件模板管理输出。

---

## 后台管理

- 后台 → 扩展 → 友情链接（管理界面为 MD3 风格卡片与表格管理）
- 支持批量导入/导出、按分类过滤、图片尺寸设置、默认图片配置等

---

## 主题

- 模板目录为 `templates/<name>/`。
- 必要文件：`manifest.json`、`template.html`。
- 可选文件：`style.css`、`script.js`（`manifest.json` 中 `inject` 决定是否注入）。
- 模板占位符：`{name}` `{url}` `{image}` `{description}` `{sort}` `{lid}` 等。

### 主题开发请查阅：https://blog.lhl.one/artical/902.html

---

## 常见问题

1. 样式被主题覆盖
- 尽力避免使用 `<a>` 标签直接输出，模板采用 `role="link"` + `data-href` 的跳转方案；如仍被覆盖可在自定义模板中引入更强选择器或 `!important`。

2. 模板资源未注入
- 确认模板下 `manifest.json` 中 `inject.css`/`inject.js` 设置为 `true` 并且前端没有被 CSP 等策略阻止。

---

## 仓库与帮助

- 插件仓库： https://github.com/lhl77/Typecho-Plugin-LinksPlus
- 使用帮助： https://blog.lhl.one/artical/902.html 

如果你需要更详细的开发/模板示例，可以在仓库 Issues 或 PR 提问。

---

## 许可

MIT（以仓库 `LICENSE` 为准）
