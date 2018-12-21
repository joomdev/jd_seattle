"use strict";

function _classCallCheck(instance, Constructor) {
   if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
   }
}

// Plugin Configs Constants 
var spectrumConfig = {
   flat: $(undefined).data('flat') ? true : false,
   showInput: true,
   showInitial: false,
   allowEmpty: true,
   showAlpha: true,
   disabled: false,
   showPalette: true,
   showPaletteOnly: false,
   showSelectionPalette: true,
   showButtons: false,
   preferredFormat: "rgb",
   localStorageKey: "astroid.colors",
   palette: [["#fff", "#f8f9fa", "#dee2e6", "#adb5bd", "#495057", "#343a40", "#212529", "#000"], ["#007bff", "#8445f7", "#ff4169", "#c4183c", "#fb7906", "#ffb400", "#17c671", "#00b8d8"]],
   change: function change(color) {}
};

var dropdownConfig = {placeholder: false, fullTextSearch: true};

var rangeConfig = {};

// Custom Plugins
(function ($) {
   $.fn.astroidAnimationSelector = function (options) {
      var settings = $.extend({
         actor: ".animation-actor",
         createActor: true
      }, options);

      return this.each(function () {
         var _select = $(this);
         var _animate = false;
         if (settings.createActor) {
            _select.after('<span title="Animate it" class="animation-actor"><span>').append('<span><span>');
            var _actor = _select.next('.animation-actor');
         }

         _select.addClass('search selection');
         _select.dropdown({
            placeholder: false,
            fullTextSearch: true,
            onChange: function onChange(value, text, $choice) {
               clearTimeout(_animate);
               _actor.children('span').removeClass();
               var _animation = value;
               _animation = _animation.replace('string:', '');
               _actor.children('span').removeClass().addClass('animated').addClass(_animation);
               _animate = setTimeout(function () {
                  _actor.children('span').removeClass();
               }, 1500);
            }
         });

         _actor.bind('click', function () {
            clearTimeout(_animate);
            _actor.children('span').removeClass();
            var _animation = _select.val();
            _animation = _animation.replace('string:', '');
            _actor.children('span').removeClass().addClass('animated').addClass(_animation);
            _animate = setTimeout(function () {
               _actor.children('span').removeClass();
            }, 1500);
         });
      });
   };
})(jQuery);

// Classes

var AstroidForm = function AstroidForm(form) {
   _classCallCheck(this, AstroidForm);

   this.form = $(form);
   this.init = function () {};
   this.reload = function () {};
};

var AstroidContentLayout = function AstroidContentLayout() {
   _classCallCheck(this, AstroidContentLayout);

   this.positions = $('[data-astroid-content-layout]');
   this.loads = $('[data-astroid-content-layout-load]');
   this.input = $('.astroidcontentlayouts');
   this.layouts = [];
   this.save = function () {
      var _row = [];
      var _layouts = this.layouts;
      _layouts.forEach(function (_l) {
         _row.push(_l.join(':'));
      });
      this.input.val(_row.join(','));
   };
   this.refresh = function () {
      var _layouts = [];
      var _this = this;
      _this.positions.each(function () {
         var _field = $(this);
         var _layout = _field.data('astroid-content-layout');
         var _fieldname = _field.data('fieldname');
         var _load = $('[data-astroid-content-layout-load="' + _fieldname + '"]').val();
         if (typeof _load == 'undefined' || _load == '' || _load == null || _load != 'after' && _load != 'before') {
            _load = 'after';
         } else {
            _load = _load;
         }
         if (_layout != '' && _field.val() != '') {
            _layouts.push([_layout, _field.val(), _load]);
         }
      });
      _this.layouts = _layouts;
      _this.save();
   };
   this.init = function () {
      var _this = this;
      _this.refresh();
      _this.loads.change(function () {
         _this.refresh();
      });
      _this.positions.change(function () {
         _this.refresh();
      });
   };
};

var AstroidAdmin = function AstroidAdmin() {
   _classCallCheck(this, AstroidAdmin);

   this.saved = true;
   /*
    this.initAstroidHeaderSwitch = function () {
    setTimeout(function () {
    $('.astroid-header-switch').bind('change', function () {
    var _val = $(this).is(':checked');
    $('.astroid-header-switch').each(function () {
    if ($(this).is(':checked') != _val) {
    $(this).trigger('click');
    }
    });
    if (_val) {
    var _header = angular.element(document.getElementById('layout-app')).scope().has_header_element();
    if (!_header) {
    var _layout = angular.element(document.getElementById('layout-app')).scope().layout;
    var _header = angular.element(document.getElementById('layout-app')).scope().add_element(_layout, 'header', 12, 0);
    _header.data.enabled = true;
    }
    }
    this.initAstroidSwitchPop();
    this.initAstroidHeaderSwitch();
    });
    }, 150);
    };
    this.checkHeaderSwitch = function () {
    setTimeout(function () {
    $('.astroid-header-switch').trigger('change');
    }, 250);
    };
    */
   this.notify = function (message, type) {
      $.notify(message, {
         className: type,
         globalPosition: 'bottom right'
      });
   };

   this.ringLoading = function (_el, _st) {
      if (_st) {
         $(_el).children('.astroid-ring-loading').show();
      } else {
         $(_el).children('.astroid-ring-loading').hide();
      }
   };

   // Sidebar functions
   this.initSidebar = function () {
      var _class = this;
      $('.sidebar-nav > li > a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
         $('body').removeClass('show-options');
         if ($('body').hasClass('astroid-live-preview')) {
            _class.refreshPreviewScrolls();
         }

         if ($(e.target).attr('data-target') == '#astroid-tab-astroid_layout') {
            $('body').addClass('astroid-layout-tab-selected');
         } else {
            $('body').removeClass('astroid-layout-tab-selected');
         }

         $.cookie("astroid-default-tab", $(e.target).attr('data-target'));
         $('.sidebar-submenu').slideUp(500);
         $(e.target).siblings('.sidebar-submenu').slideDown(500);
         setTimeout(function () {
            $('body, html').animate({
               scrollTop: 0
            }, 0);
            if (!$('body').hasClass('astroid-live-preview')) {
               _class.refreshScroll();
            }
         }, 510);
         $('body, html').animate({
            scrollTop: 2
         }, 0);
      });
   };

   this.initTabs = function () {
      $('.hash-link').click(function (e) {
         e.preventDefault();
         var _group = $(this).attr('href');
         $('body, html').animate({
            scrollTop: $(_group).offset().top - 68
         }, 100);
         setTimeout(function () {
            $(window).trigger('scroll');
         }, 110);
      });
   };

   this.initPop = function () {
      $('.compress').click(function () {
         $(this).parent('.ezlb-pop-header').parent('.ezlb-pop-body').addClass('left-push');
         $(this).parent('.ezlb-pop-header').parent('div').parent('.ezlb-pop-body').addClass('left-push');
      });
      $('.expand').click(function () {
         $(this).parent('.ezlb-pop-header').parent('.ezlb-pop-body').removeClass('left-push');
         $(this).parent('.ezlb-pop-header').parent('div').parent('.ezlb-pop-body').removeClass('left-push');
      });
      $('.ezlb-pop-header .nav-tabs li a').on('click', function (e) {
         e.preventDefault();
         var _this = $(this);
         if (!_this.hasClass('active')) {
            Admin.ringLoading($('#element-settings').children('.ezlb-pop-body'), true);
         }
      });

      $('.ezlb-pop-header .nav-tabs li a').on('shown.bs.tab', function (e) {
         e.preventDefault();
         setTimeout(function () {
            Admin.ringLoading($('#element-settings').children('.ezlb-pop-body'), false);
         }, 100);
      });
   };

   // Scrollbar functions
   this.initScroll = function () {
      $('#astroid-sidebar-wrapper').niceScroll({autohidemode: 'leave', cursoropacitymin: 0.4, background: 'rgba(255,255,255,0.5)', cursorcolor: '#4A5768', cursorwidth: '7px', cursorborderradius: 0, cursorborder: 'none'});
      $('body').niceScroll({autohidemode: 'leave', cursoropacitymin: 0.4, background: 'rgba(255,255,255,0.5)', cursorcolor: '#4A5768', cursorwidth: '7px', cursorborderradius: 0, cursorborder: 'none'});
   };

   this.initScrollSpy = function () {
      $('body').scrollspy({
         target: '#astroid-menu',
         offset: 101
      });
   };

   this.refreshScroll = function () {
      setTimeout(function () {
         $("#astroid-sidebar-wrapper").getNiceScroll().resize();
         $("body").getNiceScroll().resize();
      }, 300);
   };

   this.livePreview = function () {
      $('body').addClass('astroid-live-preview');
      $('body').addClass('show-options');
      setTimeout(function () {
         Admin.livePreviewScrolls();
         //Admin.refreshScroll();
      }, 220);
      Admin.hideAllTabs();
   };

   this.reloadPreview = function () {
      if ($('body').hasClass('astroid-live-preview')) {
         var iframe = document.getElementById('live-preview');
         iframe.src = iframe.src + '?ts=' + generateID();
      }
   };

   this.hideAllTabs = function () {
      $('#astroid-menu li a').removeClass('active');
      $('#astroid-menu li a').removeClass('show');
      $('#astroid-menu li a').prop('aria-selected', false);
   };

   this.closeLivePreview = function () {
      $('body').removeClass('astroid-live-preview');
      $('body').removeClass('show-options');
      setTimeout(function () {
         $('#astroid-content-wrapper').getNiceScroll().remove();
         Admin.refreshScroll();
      }, 220);
   };

   this.livePreviewScrolls = function () {
      $('#astroid-content-wrapper').niceScroll({autohidemode: 'leave', cursoropacitymin: 0.4, background: 'rgba(243,243,243,1)', cursorcolor: '#4A5768', cursorwidth: '7px', cursorborderradius: 0, cursorborder: 'none'});
      Admin.refreshPreviewScrolls();
   };

   this.setPreviewViewport = function (_class, _obj) {
      $('#live-preview-viewport').removeClass().addClass(_class);
      $('.viewport-options').find('a').removeClass('active');
      $(_obj).addClass('active');
   };

   this.refreshPreviewScrolls = function () {
      setTimeout(function () {
         $('#astroid-content-wrapper').getNiceScroll().resize();
         $("#astroid-sidebar-wrapper").getNiceScroll().resize();
      }, 50);
   };

   this.showOptions = function () {
      $('body').addClass('show-options');
      $('body').removeClass('astroid-layout-tab-selected');
      Admin.refreshPreviewScrolls();
      Admin.hideAllTabs();
   };

   // form functions
   this.initForm = function () {
      $('#astroid-form').parsley({
         focus: 'last'
      }).on('field:error', function () {
         var fieldset = $(this.$element).parent('div').data('fieldset');
         $('[data-target="#' + fieldset + '"]').tab("show");
      }).on('form:submit', function () {
         var data = $('#astroid-form').serializeArray();
         var _export = parseInt($('#export-form').val());
         $('#astroid-manager-disabled').show();
         if (!_export) {
            $('#save-options').addClass('d-none');
            $('#saving-options').removeClass('d-none');
         }

         $('#save-options').prop('disabled', true);
         $('#export-options').prop('disabled', true);
         $('#import-options').prop('disabled', true);
         $('#save-options').addClass('disabled');
         $('#export-options').addClass('disabled');
         $('#import-options').addClass('disabled');

         $.ajax({
            method: "POST",
            url: $('#astroid-form').attr('action'),
            data: data,
            dataType: 'json',
            success: function success(response) {
               $('#astroid-manager-disabled').hide();

               $('#save-options').removeClass('d-none');
               $('#saving-options').addClass('d-none');

               $('#save-options').prop('disabled', false);
               $('#export-options').prop('disabled', false);
               $('#import-options').prop('disabled', false);
               $('#save-options').removeClass('disabled');
               $('#export-options').removeClass('disabled');
               $('#import-options').removeClass('disabled');
               $('#export-form').val(0);
               if (response.status == 'error') {
                  Admin.notify(response.message, 'error');
                  return false;
               }
               Admin.saved = true;
               Admin.reloadPreview();
               if (!_export) {
                  Admin.notify('Template Saved.', 'success');
               } else {
                  Admin.exportSettings(response.data);
               }
            }
         });
         return false;
      });
      $('#save-options').click(function () {
         $('#astroid-form').submit();
         return false;
      });
      $('#export-options').click(function () {
         $('#export-form').val(1);
         $('#astroid-form').submit();
         return false;
      });
      $('#import-options').click(function () {
         $('#astroid-settings-import').click();
         return false;
      });
      $('#astroid-settings-import').on('change', function () {
         var input = document.getElementById('astroid-settings-import');
         if (!input) {
         } else if (!input.files) {
         } else if (!input.files[0]) {
         } else {
            var file = input.files[0];
            var reader = new FileReader();
            reader.addEventListener("load", function () {
               var _json = Admin.checkUploadedSettings(reader.result);
               if (_json !== false) {
                  Admin.saveImportedSettings(_json);
               }
            }, false);
            if (file) {
               reader.readAsText(file);
            }
         }
         $("#astroid-settings-import").val("");
      });
   };

   this.saveImportedSettings = function (_params) {
      $('#astroid-manager-disabled').show();
      $('#save-options').addClass('d-none');
      $('#saving-options').removeClass('d-none');

      $('#save-options').prop('disabled', true);
      $('#export-options').prop('disabled', true);
      $('#import-options').prop('disabled', true);
      $('#save-options').addClass('disabled');
      $('#export-options').addClass('disabled');
      $('#import-options').addClass('disabled');
      var _token = $('#astroid-admin-token').val();
      var _data = {params: _params};
      _data[_token] = 1;
      $.ajax({
         method: "POST",
         url: $('#astroid-form').attr('action'),
         data: _data,
         dataType: 'json',
         success: function success(response) {
            if (response.status == 'error') {
               Admin.notify(response.message, 'error');
            } else {
               Admin.saved = true;
               Admin.reloadPreview();
               Admin.notify('Settings Imported.', 'success');
            }
            setTimeout(function () {
               window.location = window.location;
            }, 1000);
         }
      });
   };

   this.checkUploadedSettings = function (text) {
      if (/^[\],:{}\s]*$/.test(text.replace(/\\["\\\/bfnrtu]/g, '@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
         var json = JSON.parse(text);
      } else {
         Admin.notify('Invalid JSON');
         return false;
      }
      return json;
   };

   this.exportSettings = function (_settings) {
      var dataStr = JSON.stringify(_settings);
      var dataUri = 'data:text/json;charset=utf-8,' + encodeURIComponent(dataStr);
      var date = new Date();
      var year = date.getFullYear();
      var month = date.getMonth() + 1;
      var day = date.getDate();
      var hours = date.getHours();
      var minutes = date.getMinutes();
      var seconds = date.getSeconds();
      var exportFileDefaultName = $('#export-link').data('template-name') + ' ' + (year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds) + '.json';
      $('#export-link').attr('href', dataUri);
      $('#export-link').attr('download', exportFileDefaultName);
      $('#export-link')[0].click();
   };

   this.watchForm = function () {
      var _this = this;
      $("form#astroid-form :input").change(function () {
         _this.saved = false;
         try {
            Admin.refreshScroll();
         } catch (e) {
         }
         ;
      });
   };

   this.initClearCache = function () {
      var _this = this;
      $('#clear-cache').click(function () {
         $('#clear-cache').addClass('d-none');
         $('#clearing-cache').removeClass('d-none');
         $.ajax({
            method: "GET",
            dataType: 'json',
            url: BASE_URL + 'index.php?option=com_ajax&astroid=clear-cache&template=' + TEMPLATE_NAME,
            success: function success(response) {
               $('#clear-cache').removeClass('d-none');
               $('#clearing-cache').addClass('d-none');
               _this.notify(response.message, response.status);
            }
         });
         return false;
      });
   };

   // Fields functions
   // fields
   this.initSelect = function () {
      $('.astroid-select-ui').addClass('search selection').dropdown({placeholder: false, fullTextSearch: true});
   };

   this.initSelectGrouping = function () {
      $('.ui.dropdown').has('optgroup').each(function () {
         var $menu = $('<div/>').addClass('menu');
         $(this).find('optgroup').each(function () {
            $menu.append("<div class=\"header\">" + this.label + "</div><div class=\"divider\"></div>");
            return $(this).children().each(function () {
               return $menu.append("<div class=\"item\" data-value=\"" + this.value + "\">" + this.innerHTML + "</div>");
            });
         });
         return $(this).find('.menu').html($menu.html());
      });
   };

   this.initAnimationSelector = function () {
      $('.astroid-animation-selector').astroidAnimationSelector();
   };

   this.initColorPicker = function () {
      $('.astroid-color-picker').each(function () {
         if ($(this).hasClass('color-picker-lg')) {
            var spectrumConfigExtend = spectrumConfig;
            spectrumConfigExtend.replacerClassName = 'color-picker-lg';
            $(this).spectrum(spectrumConfigExtend);
         } else {
            $(this).spectrum(spectrumConfig);
         }
      });
   };

   this.initCodeArea = function () {
      $('[data-code]').each(function () {
         var _id = $(this).attr('id');
         var _textarea = $(this);
         $(_textarea).hide();
         var _editor = ace.edit(_id + '_editor');
         _editor.session.setMode("ace/mode/" + _textarea.data('code'));
         _editor.setOption("showPrintMargin", false);
         _editor.getSession().setValue($(_textarea).val());
         _editor.getSession().on('change', function () {
            $(_textarea).val(_editor.getSession().getValue());
         });
      });
   };

   // Main
   this.init = function () {
      // scrollbar
      this.initScroll();
      this.initScrollSpy();

      // sidebar
      this.initSidebar();
      this.initTabs();

      // form
      this.initForm();
      this.watchForm();
      this.initClearCache();

      // Pop
      this.initPop();

      // fields
      this.initSelect();
      this.initSelectGrouping();
      //this.initAnimationSelector();
      //this.initColorPicker();
   };

   this.load = function () {
      var _this = this;

      var _defaultTab = $.cookie("astroid-default-tab");

      if (_defaultTab == '#astroid-tab-astroid_layout') {
         $('body').addClass('astroid-layout-tab-selected');
      } else {
         $('body').removeClass('astroid-layout-tab-selected');
      }

      if (typeof _defaultTab == 'undefined') {
         $('#astroid-menu li:first-child a').tab('show');
      } else {
         if ($('#astroid-menu li a[data-target="' + _defaultTab + '"]').length == 0) {
            $('#astroid-menu li:first-child a').tab('show');
         } else {
            $('#astroid-menu li a[data-target="' + _defaultTab + '"]').tab('show');
         }
      }
      //Admin.livePreview();
      setTimeout(function () {
         Admin.saved = true;
      }, 150);
      setTimeout(function () {
         _this.loading(false);
      }, 500);
      this.initCodeArea();
   };

   this.loading = function (_start) {
      if (typeof _start == 'undefined') {
         _start = true;
      }
      if (_start) {
         $('.astroid-loading').fadeIn(500);
      } else {
         $('.astroid-loading').fadeOut(500);
      }
   };
};

var Admin = new AstroidAdmin();

// jquery
(function ($) {
   var docReady = function docReady() {
      Admin.init();
      getGoogleFonts();
      initAstroidUploader();
      $('.astroid-code-editor-exit-fs').click(function () {
         $(this).parent('.head').parent('.astroid-code-editor').removeClass('full-screen');
      });
      $('.astroid-code-editor-fs').click(function () {
         $(this).parent('.astroid-code-editor').addClass('full-screen');
         setTimeout(function () {
            $(window).resize();
         }, 100);
      });
      $('.astroid-preloader-field-select').click(function () {
         $(this).parent('.astroid-preloader-field').children('.astroid-preloaders-selector').addClass('open');
      });
      $('.astroid-preloaders-selector-exit-fs').click(function () {
         $(this).parent('.head').parent('.astroid-preloaders-selector').removeClass('open');
      });
      $('.astroid-preloader-select').click(function () {
         var _value = $(this).data('value');
         $(this).parent('div').parent('.body').parent('.astroid-preloaders-selector').parent('.astroid-preloader-field').children('input[type="hidden"]').val(_value);
         $(this).parent('div').parent('.body').parent('.astroid-preloaders-selector').parent('.astroid-preloader-field').children('.select-preloader').html($(this).html());
         $(this).parent('div').parent('.body').parent('.astroid-preloaders-selector').removeClass('open');
      });
      initAstroidUnitPicker();
   };

   var initAstroidTypographyField = function initAstroidTypographyField() {
      $('[data-typography-field]').each(function () {
         var _field = $(this);
         var _id = _field.data('typography-field');
         var _preview = $('.astroid-typography-preview.' + _id + '-astroid-typography-preview');
         var _property = _field.data('typography-property');
         var _unit = _field.data('unit');

         if (_property == 'font-style') {
            if ($(this).is(':checked')) {
               _value = $(this).val();
               switch (_value) {
                  case 'italic':
                     _preview.css('font-style', 'italic');
                     break;
                  case 'underline':
                     _preview.css('text-decoration', 'underline');
                     _preview.children('*').css('text-decoration', 'underline');
                     break;
               }
            } else {
               _value = $(this).val();
               switch (_value) {
                  case 'italic':
                     _preview.css('font-style', 'normal');
                     break;
                  case 'underline':
                     _preview.css('text-decoration', 'none');
                     _preview.children('*').css('text-decoration', 'none');
                     break;
               }
            }
            _field.change(function () {
               if ($(this).is(':checked')) {
                  _value = $(this).val();
                  switch (_value) {
                     case 'italic':
                        _preview.css('font-style', 'italic');
                        break;
                     case 'underline':
                        _preview.css('text-decoration', 'underline');
                        _preview.children('*').css('text-decoration', 'underline');
                        break;
                  }
               } else {
                  _value = $(this).val();
                  switch (_value) {
                     case 'italic':
                        _preview.css('font-style', 'normal');
                        break;
                     case 'underline':
                        _preview.css('text-decoration', 'none');
                        _preview.children('*').css('text-decoration', 'none');
                        break;
                  }
               }
            });
         } else if (_property == 'color') {
            var _value = _field.val();
            _preview.css(_property, _value);
            _field.change(function () {
               var _value = _field.val();
               _preview.css(_property, _value);
            });
         } else {
            if (typeof _unit == 'undefined') {
               _unit = '';
            }
            var _value = _field.val();
            _preview.css(_property, _value + _unit);
            _field.change(function () {
               var _u = $(this).attr('data-unit');
               if (typeof _u == 'undefined') {
                  _u = '';
               }
               var _value = _field.val();
               _preview.css(_property, _value + _u);
            });
         }
      });
   };

   var setAstroidRange = function setAstroidRange(_range) {
      try {
         var _value = $(_range).val();
         var _post = $(_range).data('postfix');
         var _pre = $(_range).data('prefix');
         var _per = (_value - $(_range).attr('min')) * 100 / ($(_range).attr('max') - $(_range).attr('min'));
         var _left = 20 * _per / 100 + 10 - 40 - 0.4 * _per;
         $(_range).css('background-size', _per + '%');
         $(_range).siblings('.astroid-range-value').css('left', _per + '%');
         $(_range).siblings('.astroid-range-value').css('margin-left', _left + 'px');
         $(_range).siblings('.astroid-range-value').text(_pre + _value + _post);
         $(_range).siblings('.astroid-range-min-value').text(_pre + _value + _post);
      } catch (e) {
      }
   };

   var initAstroidFontSelector = function initAstroidFontSelector() {
      $('.astroid-font-preview').find('.more').click(function () {
         var _target = $(this).data('target');
         $('.' + _target).addClass('expand');
      });
      $('.astroid-font-preview').find('.less').click(function () {
         var _target = $(this).data('target');
         $('.' + _target).removeClass('expand');
      });

      $('.astroid-font-selector').addClass('search selection').dropdown({
         placeholder: false,
         fullTextSearch: true,
         onChange: function onChange(value, text, $choice) {
            _dropdown = $(this);
            var _preview = _dropdown.data('preview');
            loadGoogleFont(value, _dropdown, $('.' + _preview));
         }
      });

      $('.astroid-font-selector').each(function () {
         var _select = $(this).children('[type="hidden"]');
         var _dropdown = $(this);
         var value = _select.val();
         if (value != '' && typeof value != 'undefined') {
            var _preview = _dropdown.data('preview');
            loadGoogleFont(value, _dropdown, $('.' + _preview));
         }
      });
   };

   var loadGoogleFont = function loadGoogleFont(_font, _dropdown, _preview) {
      var _isSystemFont = false;
      SYSTEM_FONTS.forEach(function (_sfont) {
         if (_font == _sfont) {
            _isSystemFont = true;
            return false;
         }
      });

      if (_isSystemFont) {
         if (_preview !== null) {
            _preview.css('font-family', _font);
         }
         return false;
      }

      var _family = _font.split(':');
      _family = _family[0];
      _family = _family.replace(/\+/g, ' ');

      var _id = _font.replace(/\+/g, '-');
      _id = _id.replace(/\:/g, '-');
      _id = _id.replace(/\,/g, '-');
      var _loaded = $('link#' + _id);
      if (_loaded.length) {
         if (_preview !== null) {
            _preview.css('font-family', _family);
         }
         return false;
      }
      _dropdown.addClass('loading');
      $("<link/>", {
         rel: "stylesheet",
         type: "text/css",
         id: _id,
         href: "https://fonts.googleapis.com/css?family=" + _font
      }).appendTo("head");

      $('link#' + _id).bind('load', function () {
         setTimeout(function () {
            _dropdown.removeClass('loading');
            if (_preview !== null) {
               _preview.css('font-family', _family);
            }
         }, 200);
      });
   };

   var getGoogleFonts = function getGoogleFonts() {
      $.ajax({
         method: "GET",
         url: BASE_URL + 'index.php?option=com_ajax&astroid=google-fonts',
         success: function success(response) {
            $('.astroid-font-selector').find('.menu').html(response);
            setTimeout(function () {
               $('.astroid-font-selector').each(function () {
                  $(this).val($(this).data('value'));
               });
               initAstroidFontSelector();
            }, 100);
         }
      });
   };

   var initAstroidUploader = function initAstroidUploader() {
      Dropzone.autoDiscover = false;
   };

   var initAstroidUnitPicker = function initAstroidUnitPicker() {
      $('.unit-picker').children('li').children('label').children('input[type=radio]').change(function () {
         var _sliderid = $(this).data('sid');
         $('[data-slider-id="' + _sliderid + '"]').attr('data-unit', $(this).val()).trigger('change');
      });
   };

   var winLoad = function winLoad() {
      initAstroidTypographyField();
      Admin.load();
   };

   docReady();
   $(window).on("load", winLoad);
})(jQuery);

window.onbeforeunload = function () {
   if (!Admin.saved) {
      return "Are you sure you want to leave before save?";
   }
};

var ContentLayout = new AstroidContentLayout();
ContentLayout.init();