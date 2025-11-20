(function ($) {
    'use strict';

    $(window).on("elementor:init", function () {

        elementor.hooks.addAction("panel/open_editor/widget", function (panel, model) {

            const postTypeField = panel.$el.find("[data-setting=post_type]");
            const taxonomyField = panel.$el.find("[data-setting=taxonomy]");
            const termField = panel.$el.find("[data-setting=terms]");

            if (!postTypeField.length) return;

            let ajaxRequest = null;

            /**
             * ============================================================
             * Hàm tiện ích: Spinner inline cho control
             * ============================================================
             */
            const showSpinner = (field) => {
                const label = field.closest(".elementor-control").find(".elementor-control-title");
                if (!label.find(".cnk-spinner").length) {
                    const spinner = $('<span class="cnk-spinner" style="margin-left:6px;display:inline-block;width:14px;height:14px;border:2px solid #bbb;border-top-color:#333;border-radius:50%;animation:cnkSpin 0.7s linear infinite;"></span>');
                    label.append(spinner);
                }
            };

            const hideSpinner = (field) => {
                field.closest(".elementor-control").find(".cnk-spinner").remove();
            };

            /**
             * ============================================================
             * Hiển thị mô tả hướng dẫn động
             * ============================================================
             */
            const addDescription = (field, message, color = "#888") => {
                const control = field.closest(".elementor-control");
                let desc = control.find(".cnk-field-desc");
                if (!desc.length) {
                    desc = $('<div class="cnk-field-desc" style="font-size:11px;margin-top:3px;"></div>');
                    control.append(desc);
                }
                desc.css("color", color).text(message);
            };

            /**
             * ============================================================
             * Hiển thị placeholder khi đang tải
             * ============================================================
             */
            const showLoading = (select, text = "Đang tải...") => {
                select.prop("disabled", true).html(`<option value="">${text}</option>`);
            };

            /**
             * ============================================================
             * Đảm bảo select2 luôn hoạt động mượt
             * ============================================================
             */
            const ensureSelect2 = (select) => {
                if (!select.length) return;
                if (typeof select.select2 === 'function' && !select.data('select2')) {
                    select.select2({
                        width: '100%',
                        placeholder: select.find('option:first').text() || ''
                    });
                }
            };

            /**
             * ============================================================
             * Load taxonomy theo Post Type
             * ============================================================
             */
            function loadTaxonomies(postType, callback) {
                if (!postType) {
                    taxonomyField.empty().append($('<option/>').val('').text('Chọn loại nội dung trước')).prop("disabled", true);
                    termField.empty().append($('<option/>').val('').text('Chọn phân loại trước')).prop("disabled", true);
                    addDescription(taxonomyField, "Chọn loại nội dung để hiển thị danh sách phân loại.");
                    addDescription(termField, "Chọn phân loại để hiển thị danh mục.");
                    return;
                }

                showLoading(taxonomyField, "Đang tải danh sách phân loại...");
                showSpinner(taxonomyField);

                if (ajaxRequest && typeof ajaxRequest.abort === 'function') ajaxRequest.abort();

                ajaxRequest = $.ajax({
                    url: cnk_ajax.ajax_url,
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "cnk_load_taxonomy",
                        nonce: cnk_ajax.nonce,
                        post_type: postType,
                    },
                    success: function (res) {
                        taxonomyField.empty();
                        hideSpinner(taxonomyField);

                        if (res.success && Array.isArray(res.data) && res.data.length) {
                            taxonomyField.append($('<option/>').val('').text('Chọn phân loại'));
                            res.data.forEach(tax => {
                                taxonomyField.append($('<option/>').val(tax.slug).text(tax.label));
                            });
                            taxonomyField.prop("disabled", false);
                            addDescription(taxonomyField, "Chọn phân loại để tải danh sách danh mục.");
                        } else {
                            taxonomyField.append($('<option/>').val('').text('Không có Taxonomy')).prop("disabled", true);
                            addDescription(taxonomyField, "Post Type này không có Taxonomy khả dụng.", "#d9534f");
                        }

                        termField.empty().append($('<option/>').val('').text('Chọn phân loại trước')).prop("disabled", true);
                        addDescription(termField, "Chọn phân loại để hiển thị danh mục.");
                        ensureSelect2(taxonomyField);

                        if (typeof callback === "function") callback(res);
                    },
                    error: function () {
                        hideSpinner(taxonomyField);
                        taxonomyField.empty().append($('<option/>').val('').text('Lỗi tải phân loại')).prop("disabled", true);
                        addDescription(taxonomyField, "Không thể tải phân loại. Kiểm tra AJAX.", "#d9534f");
                    }
                });
            }

            /**
             * ============================================================
             * Load terms theo Taxonomy
             * ============================================================
             */
            function loadTerms(taxonomy, selected = []) {
                if (!taxonomy) {
                    termField.empty().append($('<option/>').val('').text('Chọn phân loại trước')).prop("disabled", true);
                    addDescription(termField, "Chọn phân loại để hiển thị danh mục.");
                    return;
                }

                showLoading(termField, "Đang tải danh sách danh mục...");
                showSpinner(termField);

                if (ajaxRequest && typeof ajaxRequest.abort === 'function') ajaxRequest.abort();

                ajaxRequest = $.ajax({
                    url: cnk_ajax.ajax_url,
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "cnk_load_terms",
                        nonce: cnk_ajax.nonce,
                        taxonomy: taxonomy,
                    },
                    success: function (res) {
                        termField.empty();
                        hideSpinner(termField);

                        if (res.success && Array.isArray(res.data) && res.data.length) {
                            res.data.forEach(term => {
                                termField.append($('<option/>').val(term.id).text(term.name));
                            });

                            const selectedStr = selected.map(v => String(v));
                            ensureSelect2(termField);
                            termField.val(selectedStr).trigger("change");
                            termField.prop("disabled", false);
                            addDescription(termField, "Chọn một hoặc nhiều danh mục để lọc bài viết.");
                        } else {
                            termField.append($('<option/>').val('').text('Không có danh mục')).prop("disabled", true);
                            addDescription(termField, "Phân loại này chưa có danh mục nào.", "#d9534f");
                        }
                    },
                    error: function () {
                        hideSpinner(termField);
                        termField.empty().append($('<option/>').val('').text('Lỗi tải danh mục')).prop("disabled", true);
                        //addDescription(termField, "Không thể tải danh sách danh mục con.", "#d9534f");
                    }
                });
            }

            /**
             * ============================================================
             * Event binding
             * ============================================================
             */
            postTypeField.on("change", function () {
                const val = $(this).val();
                model.setSetting("taxonomy", "");
                model.setSetting("terms", []);
                loadTaxonomies(val);
            });

            taxonomyField.on("change", function () {
                const taxonomy = $(this).val();
                model.setSetting("terms", []);
                if (taxonomy) {
                    loadTerms(taxonomy);
                } else {
                    termField.prop("disabled", true).empty().append($('<option/>').val('').text('Chọn phân loại trước'));
                    addDescription(termField, "Chọn phân loại để hiển thị danh mục.");
                }
            });

            /**
             * ============================================================
             * Khi mở widget (load dữ liệu hiện tại)
             * ============================================================
             */
            const settings = model.get("settings");
            const curPostType = settings.get("post_type");
            const curTax = settings.get("taxonomy");
            const curTerms = settings.get("terms") || [];

            ensureSelect2(taxonomyField);
            ensureSelect2(termField);

            if (curPostType) {
                loadTaxonomies(curPostType, function (res) {
                    if (res.success && curTax) {
                        taxonomyField.val(curTax).trigger('change');
                        loadTerms(curTax, curTerms);
                    }
                });
            } else {
                addDescription(taxonomyField, "Chọn loại nội dung để hiển thị phân loại.");
                addDescription(termField, "Chọn phân loại để hiển thị danh mục.");
                taxonomyField.prop("disabled", true);
                termField.prop("disabled", true);
            }
        });
    });

    /**
     * ============================================================
     * CSS spinner animation
     * ============================================================
     */
    const style = `
        @keyframes cnkSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }`;
    $('<style/>').text(style).appendTo('head');

})(jQuery);
