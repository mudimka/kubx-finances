sprint_editor.registerBlock('my_video_with_text', function ($, $el, data, settings) {

    settings = settings || {};

    var enabled_iblocks = [];
    if (settings.enabled_iblocks && settings.enabled_iblocks.value && Array.isArray(settings.enabled_iblocks.value)) {
        enabled_iblocks = settings.enabled_iblocks.value;
    }

    var multiple = false;

    data = $.extend({
        iblock_id: settings.enabled_iblocks.value[0] ?? [],
        element_ids: [],
        description: ''
    }, data);

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        data.description = $el.find('.sp-my_video_with_text-description').val();

        data.element_ids = findElementIds();
        return data;
    };

    this.afterRender = function () {

        var popupIds = [];

        var uid = sprint_editor.makeUid();
        window[uid] = {
            AddValue: function (newid) {
                newid = intval(newid);
                if (newid > 0) {
                    popupIds.push(newid);
                }
            },

            Complete: function () {
                var oldids = [];
                if (multiple) {
                    oldids = findElementIds();
                }
                sendrequest({
                    iblock_id: findIblockId(),
                    element_ids: $.merge(oldids, popupIds),
                    enabled_iblocks: enabled_iblocks
                });

                popupIds = [];
            }
        };


        $el.on('click', '.sp-open', function () {
            var iblockId = findIblockId();
            if (iblockId > 0) {

                var width = 900;
                var height = 700;
                var url = '/bitrix/admin/iblock_element_search.php?' + decodeURIComponent($.param({
                    lang: 'ru',
                    IBLOCK_ID: iblockId,
                    iblockfix: 'y',
                    lookup: uid,
                    m: multiple ? 'y' : 'n'
                }));


                var w = $(window).width(), h = $(window).height();
                var sizes = '';

                sizes += 'status=no,scrollbars=yes,resizable=yes,';
                sizes += 'width=' + width + ',height=' + height;
                sizes += +',top=' + Math.floor((h - height) / 2 - 14) + ',left=' + Math.floor((w - width) / 2 - 5);

                var popup = window.open(url, '', sizes);

                $(popup).on('unload', function () {
                    window[uid].Complete();
                });

            }
        });

        $el.on('change', '.sp-select-iblock', function () {
            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                enabled_iblocks: enabled_iblocks
            });
        });

        sendrequest({
            iblock_id: data.iblock_id,
            element_ids: data.element_ids,
            enabled_iblocks: enabled_iblocks
        });

        $el.on('click', '.delete-item', function () {
            var id = $(this).data('id');

            delElementIds(id);

            $(this).closest('.sp-item').remove();

            sendrequest({
                iblock_id: findIblockId(),
                element_ids: findElementIds(),
                enabled_iblocks: enabled_iblocks
            });
        });


        if (!$.fn.trumbowyg) {
            return false;
        }

        var btns = [];
        var cssList = {};
        var plugins = {};

        if (settings.csslist && settings.csslist.value) {
            cssList = settings.csslist.value;

            plugins = {
                mycss: {
                    cssList: cssList
                }
            };

            btns = [
                ['viewHTML'],
                ['formatting'],
                ['myCss'],
                ['strong', 'em', 'underline', 'del'],
                ['link','specialChars'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['removeformat']
            ];

        } else {
            btns = [
                ['viewHTML'],
                ['formatting'],
                ['strong', 'em', 'underline', 'del'],
                ['link','specialChars'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['removeformat']
            ]
        }

        $el.find('.sp-my_video_with_text-description').trumbowyg({
            svgPath: '/bitrix/admin/sprint.editor/assets/trumbowyg/ui/icons.svg',
            lang: 'ru',
            resetCss: true,
            removeformatPasted: true,
            autogrow: true,
            btns: btns,
            plugins: plugins
        });
    };

    var findIblockId = function () {
        return intval(
            $el.find('.sp-select-iblock').val()
        );
    };

    var findElementIds = function () {
        var $obj = $el.find('.sp-elements');

        var values = [];
        $obj.find('.sp-item').each(function () {
            var val = intval(
                $(this).data('id')
            );
            if (val > 0) {
                values.push(val);
            }
        });
        return values;
    };

    var delElementIds = function (val) {
        data.element_ids = data.element_ids.filter(id => id !== val);
        return data.element_ids;
    };

    var intval = function (val) {
        val = (val) ? val : 0;
        val = parseInt(val, 10);
        return isNaN(val) ? 0 : val;
    };


    var sendrequest = function (requestParams, callback) {
        var $jresult = $el.find('.sp-result');

        $.ajax({
            url: sprint_editor.getBlockWebPath('my_video_with_text') + '/ajax.php',
            type: 'post',
            data: requestParams,
            dataType: 'json',
            success: function (result) {

                $jresult.html(
                    sprint_editor.renderTemplate('my_video_with_text-select', result)
                );

                if (callback) {
                    callback();
                }
            },
            error: function () {

            }
        });
    };

});
