/* eslint-disable */
(function () {
    'use strict';

    function run() {
        var container = document.querySelector('.lp-miz-container');
        if (!container) return;

        var cards = Array.prototype.slice.call(
            container.querySelectorAll('.lp-miz-card')
        );

        /* 1. 域名显示：去掉协议前缀和末尾斜杠 */
        cards.forEach(function (card) {
            var el = card.querySelector('.lp-miz-url');
            if (el) {
                el.textContent = el.textContent
                    .replace(/^https?:\/\//, '')
                    .replace(/\/$/, '');
            }
        });

        /* 2. 复制按钮逻辑 */
        cards.forEach(function (card) {
            var btn = card.querySelector('.lp-miz-copy');
            if (!btn) return;

            btn.addEventListener('click', function () {
                var url = card.getAttribute('data-url') || '';

                var done = function () {
                    btn.classList.add('lp-miz-done');
                    setTimeout(function () {
                        btn.classList.remove('lp-miz-done');
                    }, 2000);
                };

                var fallback = function () {
                    var ta = document.createElement('textarea');
                    ta.value = url;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); } catch (e) { }
                    document.body.removeChild(ta);
                    done();
                };

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(done).catch(fallback);
                } else {
                    fallback();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
