# Links 主题开发说明

本文档说明当前仓库中 Links 插件主题模板的目录结构、manifest.json 字段、占位符规则，以及开发时最容易踩到的格式问题。

以当前仓库代码行为为准。

## 目录结构

主题目录固定为 `templates/<name>/`。

最小可用结构如下：

```text
templates/my-theme/
├── manifest.json
├── template.html
├── style.css
└── script.js
```

说明：

- `manifest.json`：必须存在，用于注册模板元信息。
- `template.html`：必须存在，用于单条友链的输出模板。
- `style.css`：可选，是否注入由 `manifest.json` 中的 `inject.css` 决定。
- `script.js`：可选，是否注入由 `manifest.json` 中的 `inject.js` 决定。
- `image.png`：不是当前代码的硬性要求，只是主题展示或仓库管理时可选携带的预览图。

## manifest.json 最新格式

当前仓库实际支持的格式如下：

```json
{
  "name": "my-theme",
  "title": "我的主题",
  "version": "1.0.0",
  "description": "主题简介",
  "wrapper": {
    "tag": "div",
    "class": "lp-my-theme-list",
    "id": "lp-my-theme-list",
    "attrs": {
      "role": "list"
    }
  },
  "inject": {
    "css": true,
    "js": false
  }
}
```

字段说明：

- `name`：建议视为必填，且必须与主题目录名一致。例如目录是 `templates/my-theme/`，这里就应写 `my-theme`。
- `title`：后台显示名称。未填写时会回退为 `name`。
- `version`：建议使用 `x.y.z` 格式。模板同步更新时会按这个字段比较版本。
- `description`：主题简介，建议填写，便于后续维护。
- `wrapper`：可选。用于在整组友链外层自动包一层容器。
- `inject.css`：布尔值。为 `true` 时插件会尝试注入 `style.css`。
- `inject.js`：布尔值。为 `true` 时插件会尝试注入 `script.js`。

## wrapper 的限制

`wrapper` 不是自由结构，当前实现只有下面这些键会生效：

- `tag`
- `class`
- `id`
- `attrs`

其中 `tag` 只支持以下三个值：

- `ul`
- `ol`
- `div`

如果 `tag` 写成别的值，例如 `section`、`article`、`main`，当前实现会直接忽略整个 wrapper。

`attrs` 必须是对象，例如：

```json
{
  "wrapper": {
    "tag": "ul",
    "class": "lp-card-list",
    "attrs": {
      "role": "list",
      "data-layout": "grid"
    }
  }
}
```

## template.html 的写法要求

`template.html` 应只描述单条友链的模板内容。插件会遍历所有友链，把模板重复渲染后，再根据 `wrapper` 决定是否包裹整组输出。

推荐写法：

```html
<li class="lp-my-theme-item">
    <a class="lp-my-theme-link" href="{url}" title="{description}" target="_blank" rel="noopener">
        <img class="lp-my-theme-avatar" src="{image}" alt="{name}" width="{size}" height="{size}">
        <span class="lp-my-theme-name">{name}</span>
        <span class="lp-my-theme-desc">{description}</span>
    </a>
</li>
```

不推荐在 `template.html` 里直接包整组列表容器，例如直接写完整的 `<ul>...</ul>`，除非你明确不打算使用 `wrapper`。

## 可用占位符

当前模板可用占位符如下：

- `{lid}`：数据库中的友链 ID。
- `{name}`：友链名称。
- `{url}`：友链地址。
- `{sort}`：友链分类。
- `{title}`：当前实现中等价于友链描述。
- `{description}`：友链描述。
- `{image}`：友链图片地址。
- `{user}`：自定义扩展字段。
- `{size}`：调用时传入的图片尺寸值。

注意：

- 当前实现里 `{title}` 和 `{description}` 会被替换成同一个值，也就是友链描述。
- 如果你真正需要显示站点名称，应使用 `{name}`，不要把 `{title}` 当成独立字段理解。

## style.css 和 script.js 的约定

如果 `manifest.json` 中写了：

```json
{
  "inject": {
    "css": true,
    "js": true
  }
}
```

那么插件会尝试读取当前主题目录下的 `style.css` 和 `script.js`。

需要注意：

- `inject.css` 为 `true` 但没有 `style.css`，不会报 schema 错误，但前台不会有样式注入。
- `inject.js` 为 `true` 但没有 `script.js`，不会报 schema 错误，但前台不会有脚本注入。
- 样式类名建议统一加前缀，例如 `lp-my-theme-*`，避免污染主题本身页面。
- 如果没有脚本需求，建议明确写成 `"js": false`。

## 常见格式问题

### 1. manifest.json 存在，但 JSON 非法

如果 `manifest.json` 不是合法 JSON，模板不会被识别到后台列表中。

### 2. name 和目录名不一致

这是最容易踩的坑之一。

例如目录是：

```text
templates/fancy-theme/
```

但 manifest 写成：

```json
{
  "name": "my-theme"
}
```

这种情况下，模板可能出现在后台选择项里，但 `template.html`、`style.css`、`script.js` 的实际读取会按 `my-theme` 这个名字去拼路径，最终导致资源找不到。

结论：目录名和 `name` 必须保持一致。

### 3. template.html 缺失或为空

即使 `manifest.json` 正常，`template.html` 为空时：

- 后台预览会读取失败。
- 前台 `SHOW_TEXT`、`SHOW_IMG`、`SHOW_MIX` 模式下可能回退到旧配置中的输出规则。

所以不要只写 `manifest.json` 而漏掉 `template.html`。

### 4. wrapper 和模板结构不匹配

例如：

- `wrapper.tag` 写的是 `ul`
- `template.html` 里输出的却是完整的 `<div class="card-grid">...</div>`

这类结构虽然未必报错，但最终 HTML 语义和布局可能会混乱。

一般建议：

- `wrapper.tag = "ul"` 时，`template.html` 输出单个 `<li>`。
- `wrapper.tag = "div"` 时，`template.html` 输出单个卡片 `<div>`。

### 5. 把 image.png 当成必需文件

当前代码不会因为缺少 `image.png` 而让模板失效。是否放预览图，只取决于你是否需要额外展示。

## 最小示例

### manifest.json

```json
{
  "name": "demo-grid",
  "title": "Demo Grid",
  "version": "1.0.0",
  "description": "一个最小可用的示例主题",
  "wrapper": {
    "tag": "ul",
    "class": "lp-demo-grid",
    "attrs": {
      "role": "list"
    }
  },
  "inject": {
    "css": true,
    "js": false
  }
}
```

### template.html

```html
<li class="lp-demo-grid__item">
    <a class="lp-demo-grid__link" href="{url}" title="{description}" target="_blank" rel="noopener">
        <img class="lp-demo-grid__avatar" src="{image}" alt="{name}" width="{size}" height="{size}">
        <span class="lp-demo-grid__name">{name}</span>
        <span class="lp-demo-grid__desc">{description}</span>
    </a>
</li>
```

### style.css

```css
.lp-demo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    padding: 0;
    margin: 0;
    list-style: none;
}

.lp-demo-grid__item {
    margin: 0;
}

.lp-demo-grid__link {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 16px;
    border-radius: 16px;
    text-decoration: none;
}
```

## 建议

- 新主题优先从现有主题目录复制一个最接近的示例再改。
- 如果模板需要列表语义，优先使用 `wrapper` 管整组容器，不要把整组结构硬写进 `template.html`。
- 提交或分发主题前，至少检查一次 `manifest.json` 是否合法、`name` 是否与目录一致、`template.html` 是否能单条正确输出。