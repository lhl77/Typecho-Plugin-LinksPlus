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

## 受保护分发

如果你要把 `core.php`、`Action.php` 和后台资产映射一并做受保护分发，可以继续使用仓库内置的 `build_distribution.php`，再配合 `build_protected_distribution.php` 作为一键包装入口。

### 1. 准备本地编码配置

1. 复制 `usr/plugins/Links/build_encoder.example.php` 为本地文件 `usr/plugins/Links/build_encoder.local.php`
2. 按照你的编码器厂商文档，把 `encoderCommand` 改成真实可执行命令
3. 命令模板里必须保留 `{input}` 和 `{output}` 占位符

`build_encoder.local.php` 已加入插件目录下的 `.gitignore`，不会被默认提交。

### 2. 一键生成受保护分发包

在插件目录执行：

```bash
php build_protected_distribution.php --version=1.4.1 --target=protected
```

默认会调用：

- `build_distribution.php`
- 编码 `core.php`
- 编码 `Action.php`
- 编码 `assets/LinksAssetMap.php`
- 产出 `dist/` 目录下的单包分发结果

### 3. 额外参数

你也可以在命令行临时覆盖本地配置中的默认值：

```bash
php build_protected_distribution.php --version=1.4.1 --target=release --zip=1
```

若只想检查最终命令而不实际执行，可使用：

```bash
php build_protected_distribution.php --dry-run=1
```

### 4. 注意事项

- 仓库不会硬编码 ionCube 或 SourceGuardian 的 CLI 语法；请以你购买/安装的编码器官方文档为准填写 `encoderCommand`
- 旧版 Zend Guard 7（如 `zendenc55`、`zendenc56`）只适用于 PHP 5.5/5.6，不适用于本插件要求的 PHP 7.2+
- 任何本地分发加密都只能提高逆向成本，不能承诺绝对不可逆；如果要进一步提高授权校验安全性，建议把签发逻辑放到服务端

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

开启：

变量拆分混淆：开，次数 1
变量名编码：开，长度 8 到 10
字符串编码：开，长度 10 左右
数字编码：开
GZ 压缩编码：开
插入无用代码：开
移除注释：开
代码压缩：开
控制流混淆：开，复杂度选 medium
目标 PHP 版本：选 7
重复加密次数：1
关闭：

函数名混淆：关
函数调用混淆：关
函数调用编码：关
类名混淆：关
HTML 编码：关
保留换行符：关
GOTO 混淆：关
VM 虚拟机壳：关
EVAL 虚拟机壳：关
调试模式：关