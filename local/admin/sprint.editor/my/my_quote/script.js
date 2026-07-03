sprint_editor.registerBlock('my_quote', function ($, $el, data, settings) {

        settings = settings || {};

        data = $.extend({
            value: '',
            whose: '',
            position: '',
            file: {}
        }, data);

        this.getData = function () {
            return data;
        };

        this.collectData = function () {
            if (!$.fn.trumbowyg) {
                return data;
            }

            data.value = $el.find('.sp-quote').val();
            data.whose = $el.find('.sp-quote-whose').val();
            data.position = $el.find('.sp-quote-position').val();

            return data;
        };

        this.afterRender = function () {


            renderfiles();

            var $btn = $el.find('.sp-x-btn-file');
            var $btninput = $btn.find('input[type=file]');
            var $label = $btn.find('label');
            var labeltext = $label.text();

            $btninput.fileupload({
                dropZone: $el,
                url: sprint_editor.getBlockWebPath('image') + '/upload.php',
                dataType: 'json',
                done: function (e, result) {
                    let found = false;
                    $.each(result.result.file, function (index, file) {
                        data.file = file;
                        found = true;
                    });

                    if (found) {
                        renderfiles();
                        togglepanel(false);
                    }

                },
                progressall: function (e, result) {
                    var progress = parseInt(result.loaded / result.total * 100, 10);

                    $label.text('Загрузка: ' + progress + '%');

                    if (progress >= 100) {
                        $label.text(labeltext);
                    }
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');


            $el.find('.sp-download-url').bindWithDelay('input', function () {
                var $urltext = $(this);

                var urlvalue = $.trim(
                    $urltext.val()
                );

                if (urlvalue.length <= 0) {
                    return false;
                }


                $.ajax({
                    url: sprint_editor.getBlockWebPath('image') + '/download.php',
                    type: 'post',
                    data: {
                        url: urlvalue
                    },
                    dataType: 'json',
                    success: function (result) {
                        if (result.image) {
                            data.file = result.image;

                            renderfiles();

                            togglepanel(false);
                        }

                        $urltext.val('');


                    }
                });
            }, 500);

            $el.on('click', '.sp-item-del', function () {
                data.file = {};

                renderfiles();

                togglepanel(true);
            });

            if (!data.file || !data.file.SRC) {
                togglepanel(true);
            } else {
                togglepanel(false);
            }

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
                    ['link', 'specialChars'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat']
                ];

            } else {
                btns = [
                    ['viewHTML'],
                    ['formatting'],
                    ['strong', 'em', 'underline', 'del'],
                    ['link', 'specialChars'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat']
                ]
            }


            $el.find('.sp-quote').trumbowyg({
                svgPath: '/bitrix/admin/sprint.editor/assets/trumbowyg/ui/icons.svg',
                lang: 'ru',
                resetCss: true,
                removeformatPasted: true,
                autogrow: true,
                btns: btns,
                plugins: plugins
            });

        };
    var togglepanel = function (show) {
        if (show) {
            $el.addClass('sp-show');
        } else {
            $el.removeClass('sp-show');
        }
    }

    var renderfiles = function () {
        $el.find('.sp-result').html(
            sprint_editor.renderTemplate('image-image', data)
        );
    };

    }
);
