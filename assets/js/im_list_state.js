(function () {
    'use strict';

    var KEY_PREFIX = 'im_table_state::';
    var SCROLL_PREFIX = 'im_scroll_state::';
    var WAIT_MAX = 120; // ~12s
    var waited = 0;

    function pageKey() {
        return window.location.pathname + window.location.search;
    }

    function tableKey(tableId) {
        return KEY_PREFIX + pageKey() + '::' + tableId;
    }

    function scrollKey() {
        return SCROLL_PREFIX + pageKey();
    }

    function getJQ() {
        return window.jQuery || window.$ || null;
    }

    function saveJson(key, obj) {
        try {
            sessionStorage.setItem(key, JSON.stringify(obj || {}));
        } catch (e) {}
    }

    function loadJson(key, fallback) {
        try {
            var raw = sessionStorage.getItem(key);
            if (!raw) return fallback;
            return JSON.parse(raw);
        } catch (e) {
            return fallback;
        }
    }

    function getScrollableElements($) {
        var items = [];

        $('.dataTables_scrollBody').each(function (i) {
            items.push({
                key: 'dt_scroll_' + i,
                el: this
            });
        });

        $('.table-responsive').each(function (i) {
            items.push({
                key: 'table_resp_' + i,
                el: this
            });
        });

        return items;
    }

    function saveScroll($) {
        var data = {
            winTop: $(window).scrollTop(),
            winLeft: $(window).scrollLeft(),
            els: {}
        };

        getScrollableElements($).forEach(function (item) {
            var $el = $(item.el);
            data.els[item.key] = {
                top: $el.scrollTop(),
                left: $el.scrollLeft()
            };
        });

        saveJson(scrollKey(), data);
    }

    function restoreScroll($, delay) {
        var data = loadJson(scrollKey(), null);
        if (!data) return;

        setTimeout(function () {
            if (typeof data.winTop !== 'undefined') {
                $(window).scrollTop(data.winTop);
            }
            if (typeof data.winLeft !== 'undefined') {
                $(window).scrollLeft(data.winLeft);
            }

            var els = data.els || {};
            getScrollableElements($).forEach(function (item) {
                if (!els[item.key]) return;
                var $el = $(item.el);
                $el.scrollTop(els[item.key].top || 0);
                $el.scrollLeft(els[item.key].left || 0);
            });
        }, delay || 120);
    }

    function ensureIds($) {
        $('table').each(function (idx) {
            if (!this.id) {
                this.id = 'im_auto_table_' + idx;
            }
        });
    }

    function saveTableState($, dt) {
        try {
            var node = dt.table().node();
            if (!node || !node.id) return;

            var info = dt.page.info();

            saveJson(tableKey(node.id), {
                page: info.page || 0,
                length: dt.page.len(),
                order: dt.order(),
                search: dt.search()
            });
        } catch (e) {}
    }

    function restoreTableState($, dt) {
        try {
            var node = dt.table().node();
            if (!node || !node.id) return false;

            var saved = loadJson(tableKey(node.id), null);
            if (!saved) return false;

            // Chỉ restore thứ tự nếu đã có state cũ.
            if (typeof saved.length !== 'undefined' && parseInt(saved.length, 10) !== dt.page.len()) {
                dt.page.len(parseInt(saved.length, 10));
            }

            if (typeof saved.search !== 'undefined' && saved.search !== dt.search()) {
                dt.search(saved.search);
            }

            if (Array.isArray(saved.order) && saved.order.length) {
                var hasMeaningfulOrder = true;
            
                // nếu state cũ bị lưu kiểu mặc định asc ở cột ID thì bỏ qua,
                // để trang dùng sort mặc định từ data-order-col/data-order-type
                if (
                    saved.order.length === 1 &&
                    parseInt(saved.order[0][0], 10) === 0 &&
                    String(saved.order[0][1]).toLowerCase() === 'asc'
                ) {
                    hasMeaningfulOrder = false;
                }
            
                if (hasMeaningfulOrder) {
                    dt.order(saved.order);
                }
            }

            dt.draw(false);

            var info = dt.page.info();
            var targetPage = parseInt(saved.page || 0, 10);
            var maxPage = Math.max(0, (info.pages || 1) - 1);

            if (targetPage > maxPage) {
                targetPage = maxPage;
            }

            if (targetPage !== info.page) {
                dt.page(targetPage).draw('page');
            }

            return true;
        } catch (e) {
            return false;
        }
    }

    function bindOneTable($, dt) {
        var node = dt.table().node();
        if (!node) return;

        var $table = $(node);
        if ($table.data('im-state-bound')) return;
        $table.data('im-state-bound', 1);

        // restore ngay khi bind
        restoreTableState($, dt);
        restoreScroll($, 220);

        dt.on('page.dt.imstate length.dt.imstate order.dt.imstate search.dt.imstate', function () {
            saveTableState($, dt);
            saveScroll($);
        });

        dt.on('draw.dt.imstate', function () {
            saveTableState($, dt);
            restoreScroll($, 80);
        });
    }

    function attachAllCurrentTables($) {
        if (!$.fn || !$.fn.dataTable) return false;

        var apiTables = $.fn.dataTable.tables({ api: true });
        if (!apiTables || !apiTables.length) return false;

        apiTables.every(function () {
            bindOneTable($, this);
        });

        return true;
    }

    function boot() {
        var $ = getJQ();
        if (!$ || !$.fn || !$.fn.dataTable) {
            waited++;
            if (waited < WAIT_MAX) {
                setTimeout(boot, 100);
            }
            return;
        }

        ensureIds($);

        // bind cho các table đã init sẵn
        attachAllCurrentTables($);

        // bind cho các table init về sau
        $(document).on('init.dt.imstate', function (e, settings) {
            try {
                var dt = new $.fn.dataTable.Api(settings);
                bindOneTable($, dt);
            } catch (err) {}
        });

        // lưu scroll
        $(window).on('beforeunload.imstate', function () {
            saveScroll($);
        });

        $(window).on('scroll.imstate', function () {
            saveScroll($);
        });

        $(document).on('scroll.imstate', '.dataTables_scrollBody, .table-responsive', function () {
            saveScroll($);
        });

        $(document).on('click.imstate', '.dataTables_paginate a, .paginate_button', function () {
            saveScroll($);
        });

        $(document).on('change.imstate keyup.imstate', '.dataTables_length select, .dataTables_filter input', function () {
            saveScroll($);
        });

        // fallback cho page không dùng dt hoặc dt attach chậm
        restoreScroll($, 300);

        // quét lại vài lần để bắt những DataTable init muộn
        setTimeout(function () { attachAllCurrentTables($); }, 300);
        setTimeout(function () { attachAllCurrentTables($); }, 800);
        setTimeout(function () { attachAllCurrentTables($); }, 1500);
    }

    boot();
})();