/**
 * ============================================================
 * CNK Framework - GetPost Load More (v3.2 Optimized)
 * ------------------------------------------------------------
 * - Load More bằng AJAX hoặc Infinite Scroll
 * - Tích hợp Bootstrap 5 (spinner, responsive)
 * - Hỗ trợ data-target để xác định vùng append chính xác
 * - Fallback thông minh, không bao giờ lỗi "Không tìm thấy .cnk-post-list"
 * ============================================================
 */

(function ($) {
    'use strict';

    /**
     * Hiển thị spinner Bootstrap 5 khi đang tải
     */
    function cnkShowSpinner($btn) {
        const spinner = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        `;
        $btn.data('old-text', $btn.html());
        $btn.prop('disabled', true).html(spinner + 'Đang tải...');
    }

    /**
     * Khôi phục lại nút sau khi tải xong
     */
    function cnkHideSpinner($btn) {
        const oldText = $btn.data('old-text') || 'Tải thêm';
        $btn.prop('disabled', false).html(oldText);
    }

    /**
     * Lấy vùng append từ data-target hoặc fallback tự động
     */
    function cnkGetTarget($wrap) {
        const target = $wrap.data('target');

        // Ưu tiên data-target
        if (target && $(target).length) return $(target);

        // Nếu không có, thử tìm vùng gần nhất có class .cnk-post-list
        const $fallback = $wrap.closest('.cnk-getpost').find('.cnk-post-list').first();

        if ($fallback.length) return $fallback;

        // Cuối cùng, fallback toàn cục để tránh lỗi
        console.warn('[CNK Framework] Không tìm thấy vùng append, fallback toàn trang.');
        const $any = $('.cnk-post-list').last();
        if ($any.length) return $any;

        // Nếu không có gì, tạo tạm div để tránh lỗi JS
        const $temp = $('<div class="cnk-post-list"></div>').appendTo('body');
        return $temp;
    }

    /**
     * Hàm xử lý Load More bằng AJAX
     */
    function cnkLoadMore($wrap) {
        if (!$wrap.length) return;

        const action   = $wrap.data('action') || 'cnk_load_more_posts';
        const widget   = $wrap.data('widget') || 'get-post';
        const template = $wrap.data('template') || 'default';
        const paged    = parseInt($wrap.data('paged')) || 1;
        const type     = $wrap.data('type') || 'ajax';
        let query      = $wrap.data('query') || {};

        // Parse query nếu là chuỗi JSON
        if (typeof query === 'string') {
            try {
                query = JSON.parse(query);
            } catch (e) {
                console.warn('[CNK Framework] Không thể parse query JSON:', e);
                query = {};
            }
        }

        const $btn  = $wrap.find('.cnk-load-more-btn');
        const $root = cnkGetTarget($wrap);

        // Spinner
        cnkShowSpinner($btn);

        const data = {
            action: action,
            nonce: (typeof cnk_ajax !== 'undefined' ? cnk_ajax.nonce : ''),
            paged: paged + 1,
            query: query,
            widget: widget,
            template: template,
        };
        $.ajax({
            url: (typeof cnk_ajax !== 'undefined' ? cnk_ajax.url : ajaxurl),
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (res, status, xhr) {
                if (typeof res !== 'object') {
                    console.error('[CNK Framework] Response không phải JSON:', xhr.responseText);
                    cnkHideSpinner($btn);
                    return;
                }

                if (res.success && res.data && res.data.html) {
                    $root.append(res.data.html);
                    $wrap.data('paged', res.data.paged).attr('data-paged', res.data.paged);

                    $('html, body').animate({
                        scrollTop: $root.offset().top + $root.outerHeight() - window.innerHeight / 2,
                    }, 600, 'swing');

                    if (!res.data.has_more) {
                        $wrap.fadeOut(400, function () { $(this).remove(); });
                    } else {
                        cnkHideSpinner($btn);
                    }
                } else {
                    console.error('[CNK Framework] AJAX trả về lỗi hoặc rỗng:', res);
                    cnkHideSpinner($btn);
                }
            },
            error: function (xhr) {
                console.error('[CNK Framework] Response lỗi:\n', xhr.responseText);
                cnkHideSpinner($btn);
            }
        });

    }

    /**
     * ============================================================
     * Sự kiện click vào nút "Tải thêm"
     * ============================================================
     */
    $(document).on('click', '.cnk-load-more-btn', function (e) {
        e.preventDefault();
        const $wrap = $(this).closest('.cnk-load-more-wrap');
        cnkLoadMore($wrap);
    });

    /**
     * ============================================================
     * Infinite Scroll: Tự động load khi gần đáy viewport
     * ============================================================
     */
    let cnkScrollTimer;
    $(window).on('scroll', function () {
        clearTimeout(cnkScrollTimer);
        cnkScrollTimer = setTimeout(function () {
            $('.cnk-load-more-wrap[data-type="infinite"]').each(function () {
                const $wrap = $(this);
                const $btn = $wrap.find('.cnk-load-more-btn');
                if ($wrap.is(':hidden') || $btn.prop('disabled')) return;

                const rect = $wrap[0].getBoundingClientRect();
                const winH = window.innerHeight || document.documentElement.clientHeight;

                if (rect.top < winH - 150) {
                    cnkLoadMore($wrap);
                }
            });
        }, 200);
    });

})(jQuery);
