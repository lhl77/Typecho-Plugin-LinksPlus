/* eslint-disable */
(function () {
    'use strict';

    function stripUrl(text) {
        return (text || '').replace(/^https?:\/\//, '').replace(/\/$/, '');
    }

    /* 按 data-sort 对卡片分组，注入 feed-title */
    function groupCards(container, cards) {
        var groups  = [];   // 有序 sort 列表
        var groupMap = {};  // sort -> card[]

        cards.forEach(function (card) {
            var sort = (card.getAttribute('data-sort') || '').trim();
            if (!groupMap[sort]) {
                groups.push(sort);
                groupMap[sort] = [];
            }
            groupMap[sort].push(card);
        });

        /* 超过 1 个分组才启用分组结构 */
        var hasMultipleGroups = groups.length > 1 ||
            (groups.length === 1 && groups[0] !== '');
        if (!hasMultipleGroups) return;

        container.setAttribute('data-grouped', '');
        /* 清空容器 */
        while (container.firstChild) {
            container.removeChild(container.firstChild);
        }

        groups.forEach(function (sort) {
            var section = document.createElement('div');
            section.className = 'lp-cl-group';

            /* feed-title：有 sort 名称才显示 */
            if (sort) {
                var title = document.createElement('h3');
                title.className = 'lp-cl-feed-title';
                title.textContent = sort;
                section.appendChild(title);
            }

            var grid = document.createElement('div');
            grid.className = 'lp-cl-grid';

            groupMap[sort].forEach(function (card) {
                grid.appendChild(card);
            });

            section.appendChild(grid);
            container.appendChild(section);
        });
    }

    function setupPopups(cards) {
        var POPUP_W  = 220;
        var MARGIN   = 8;
        var GAP      = 10;

        cards.forEach(function (card) {
            var popup = card.querySelector('.lp-cl-popup');
            if (!popup) return;

            /* Portal：把 popup 挂到 body，彻底逃脱任何祖先的
               overflow/transform/stacking context 限制（预览与前台均适用）*/
            document.body.appendChild(popup);

            function position() {
                var rect = card.getBoundingClientRect();

                /* 水平居中，超出视口时夹紧 */
                var left = rect.left + rect.width / 2 - POPUP_W / 2;
                left = Math.max(MARGIN, Math.min(left, window.innerWidth - POPUP_W - MARGIN));
                popup.style.left = left + 'px';

                /* 先放到视口外量高度 */
                popup.style.top = '-9999px';
                var ph = popup.offsetHeight;

                /* 上方空间足够则向上弹，否则向下弹 */
                if (rect.top - ph - GAP >= 0) {
                    popup.style.top = (rect.top - ph - GAP + window.scrollY) + 'px';
                } else {
                    popup.style.top = (rect.bottom + GAP + window.scrollY) + 'px';
                }
            }

            card.addEventListener('mouseenter', function () {
                position();
                popup.classList.add('lp-cl-popup-show');
            });

            card.addEventListener('mouseleave', function () {
                popup.classList.remove('lp-cl-popup-show');
            });
        });
    }

    function run() {
        var containers = Array.prototype.slice.call(
            document.querySelectorAll('.lp-cl-container')
        );

        containers.forEach(function (container) {
            var cards = Array.prototype.slice.call(
                container.querySelectorAll('.lp-cl-card')
            );

            /* 1. 域名显示：去掉协议前缀和末尾斜杠 */
            cards.forEach(function (card) {
                var domain = card.querySelector('.lp-cl-domain');
                if (domain) domain.textContent = stripUrl(domain.textContent);

                var popUrl = card.querySelector('.lp-cl-pop-url');
                if (popUrl) popUrl.textContent = stripUrl(popUrl.textContent);
            });

            /* 2. 按 sort 分组，注入巨型描边标题 */
            groupCards(container, cards);

            /* 3. popup 定位（position:fixed 逃脱 overflow:hidden）*/
            setupPopups(cards);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
