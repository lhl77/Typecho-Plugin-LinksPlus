# Typecho 插件：Links+（友链管理增强版）

[![Latest tag](https://img.shields.io/github/v/tag/lhl77/Typecho-Plugin-LinksPlus?label=tag&sort=semver)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/tags)
[![License](https://img.shields.io/github/license/lhl77/Typecho-Plugin-LinksPlus)](https://github.com/lhl77/Typecho-Plugin-LinksPlus/blob/main/LICENSE)
[![Typecho](https://img.shields.io/badge/Typecho-Plugin-blue)](https://typecho.org/)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.2-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Status](https://img.shields.io/badge/Status-Active-success)](#)

友情链接增强插件，支持多模板、短代码、正文重写、内置友链卡片展示主题、后台友链管理、前台友链申请。

修改自第三方维护者版本v1.2.7: https://github.com/Mejituu/Links

原作者：https://www.imhan.com/archives/typecho-links/

增强版文档：https://see.lhl.one/Typecho-LinksPlus

---

## 环境要求

- Typecho（1.2.x+）
- PHP 7.2+

---

## 安装

**方法一** 通过AB Store安装
详见：https://github.com/lhl77/Typecho-Plugin-AdminBeautify

**方法二** 手动安装
1. 下载本插件并解压到：
	- `usr/plugins/Links/`
2. 确认目录结构包含：
	- `usr/plugins/Links/Plugin.php`
	- `usr/plugins/Links/manage-links.php`
	- `usr/plugins/Links/templates/`（内含模板）
3. 后台 → 控制台 → 插件，启用 **Links+**。

---

## 主题

- 模板目录为 `templates/<name>/`。
- 必要文件：`manifest.json`、`template.html`。
- 可选文件：`style.css`、`script.js`（`manifest.json` 中 `inject` 决定是否注入）。
- 模板占位符：`{name}` `{url}` `{image}` `{description}` `{sort}` `{lid}` 等。

### 主题开发请查阅：https://blog.lhl.one/artical/902.html

---

## 仓库与帮助

- 插件仓库： https://github.com/lhl77/Typecho-Plugin-LinksPlus
- 使用帮助： https://blog.lhl.one/artical/902.html 

如果你需要更详细的开发/模板示例，可以在仓库 Issues 或 PR 提问。
