var astroidFramework = angular.module("astroid-framework", ["ng-sortable", "ngAnimate"]);
astroidFramework.directive("tooltip", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs) {
         $(element).hover(function () {
            $(element).tooltip("show")
         }, function () {
            $(element).tooltip("hide")
         })
      }
   }
});
astroidFramework.directive("astroidmediagallery", ["$http", function ($http) {
   return {
      restrict: "A",
      scope: true,
      require: "ngModel",
      link: function ($scope, $element, $attrs, ngModel) {
         $scope.gallery = {};
         $scope.back = "";
         $scope.folder = "";
         $scope.MEDIA_URL = SITE_URL + $scope.Imgpath +"/";
         $scope.iconsize = 130;
         $scope.getImageUrl = function () {
            if (ngModel.$modelValue == "" || typeof ngModel.$modelValue == "undefined") {
               $scope.clearImage();
               return ""
            }
            return $scope.MEDIA_URL + ngModel.$modelValue
         };
         $scope.getFileName = function () {
            if (ngModel.$modelValue == "" || typeof ngModel.$modelValue == "undefined") {
               $scope.clearImage();
               return ""
            }
            return ngModel.$modelValue
         };
         $scope.getImgUrl = function (_url) {
            return $scope.MEDIA_URL + _url
         };
         $scope.clearImage = function (_id) {
            ngModel.$setViewValue("");
            try {
               Admin.refreshScroll()
            } catch (e) {}
         };
         $scope.selectImage = function (_id, _value) {
            ngModel.$setViewValue(_value);
            $scope.selectMedia = false;
            try {
               Admin.refreshScroll()
            } catch (e) {}
         };
         $scope.getLibrary = function (folder, tab) {
            $("a#" + tab).tab("show");
            $scope.loading = true;
            $scope.folder = folder;
            $scope.bradcrumb = [];
            $.ajax({
               method: "GET",
               url: BASE_URL + "index.php?option=com_ajax&astroid=media&action=library&folder=" + folder + "&asset=com_templates&author=",
               success: function (response) {
                  if (response.status == "error") {
                     $.notify(response.message, {
                        className: "error",
                        globalPosition: "bottom right"
                     });
                     $scope.selectMedia = false;
                     $scope.$apply();
                     return false
                  }
                  $scope.gallery = response.data;
                  $scope.loading = false;
                  var folders = folder.split("/");
                  var bradcrumb = [];
                  var _url = [];
                  var _obj = {
                     name: "Library",
                     url: ""
                  };
                  bradcrumb.push(_obj);
                  folders.forEach(function (_u, _i) {
                     _url.push(_u);
                     var _path = _url.join("/");
                     var _obj = {
                        name: _path == "" ? "Library" : _u,
                        url: _path
                     };
                     bradcrumb.push(_obj)
                  });
                  $scope.bradcrumb = bradcrumb;
                  $scope.$apply()
               }
            })
         };
         $scope.newFolder = function (_id) {
            var name = prompt(TPL_ASTROID_NEW_FOLDER_NAME_LBL, "");
            if (name === "") {
               return false
            } else if (name) {
               var re = /^[0-9a-zA-Z].*/;
               if (!re.test(name) || /\s/.test(name)) {
                  Admin.notify(TPL_ASTROID_NEW_FOLDER_NAME_INVALID, "error");
                  return false
               }
               $scope.loading = true;
               $.ajax({
                  method: "GET",
                  url: BASE_URL + "index.php?option=com_ajax&astroid=media&action=create-folder&dir=" + $scope.gallery.current_folder + "&name=" + name,
                  success: function (response) {
                     if (response.status == "error") {
                        $.notify(response.message, {
                           className: "error",
                           globalPosition: "bottom right"
                        })
                     } else {
                        $.notify(response.data.message, {
                           className: "success",
                           globalPosition: "bottom right"
                        });
                        $scope.getLibrary(response.data.folder, _id)
                     }
                     $scope.loading = false;
                     $scope.$apply()
                  }
               })
            } else {
               return false
            }
         }
      }
   }
}]);
astroidFramework.directive("astroidsocialprofiles", ["$http", function ($http) {
   return {
      restrict: "A",
      scope: true,
      link: function ($scope, $element, $attrs) {
         $scope.getId = function (_title) {
            return _title.toLowerCase().replace(/ /g, "-").replace(/[^\w-]+/g, "")
         };
         AstroidSocialProfiles.forEach(function (_sp) {
            _sp.id = $scope.getId(_sp.title)
         });
         $scope.astroidsocialprofiles = AstroidSocialProfiles;
         $scope.profiles = AstroidSocialProfilesSelected;
         $scope.addProfile = function () {
            var _profiles = $scope.astroidsocialprofiles;
            _profiles.push({
               title: "",
               icon: "",
               link: "",
               id: $scope.getId()
            });
            $scope.astroidsocialprofiles = _profiles
         };
         $scope.selectSocialProfile = function (_profile) {
            var _profiles = $scope.profiles;
            _profiles.push(angular.copy(_profile));
            $scope.profiles = _profiles
         };
         $scope.removeSocialProfile = function (_index) {
            var _c = confirm("Are you sure?");
            if (_c) {
               var _profiles = $scope.profiles;
               _profiles.splice(_index, 1);
               $scope.profiles = _profiles
            }
         };
         $scope.refreshScroll = function (_this, profile) {
            profile.enabled = $("#" + _this).is(":checked");
            try {
               $scope.setProfiles();
               Admin.refreshScroll()
            } catch (e) {}
         };
         $scope.setProfiles = function () {
            var _profiles = [];
            $scope.astroidsocialprofiles.forEach(function (_profile) {
               if (_profile.enabled) {
                  _profiles.push(_profile)
               }
            });
            $scope.profiles = _profiles
         };
         $scope.addCustomProfile = function () {
            var _profile = {
               color: "#495057",
               enabled: false,
               icon: "",
               icons: [],
               id: "custom",
               link: "#",
               title: "Custom social profile"
            };
            var _profiles = $scope.profiles;
            _profiles.push(angular.copy(_profile));
            $scope.profiles = _profiles
         }
      }
   }
}]);
astroidFramework.directive("astroidsassoverrides", ["$http", function () {
   return {
      restrict: "A",
      scope: true,
      link: function ($scope, $element, $attrs) {
         $scope.overrides = AstroidSassOverrideVariables;
         $scope.addOverride = function () {
            var _overrides = $scope.overrides;
            _overrides.push({
               variable: "",
               value: "",
               color: true
            });
            $scope.overrides = _overrides;
            setTimeout(function () {
               $(".sass-variable-" + (_overrides.length - 1) + "-value").spectrum(spectrumConfig)
            }, 50)
         };
         $scope.removeOverride = function (_index) {
            var _c = confirm("Are you sure?");
            if (_c) {
               var _overrides = $scope.overrides;
               _overrides.splice(_index, 1);
               $scope.overrides = _overrides
            }
         };
         $scope.initSassColorPicker = function (_index, _status) {
            if (_status) {
               $(".sass-variable-" + _index + "-value").spectrum(spectrumConfig)
            } else {
               $(".sass-variable-" + _index + "-value").spectrum("destroy")
            }
         };
         setTimeout(function () {
            $scope.overrides.forEach(function (_variable, _index) {
               if (typeof _variable.color != "undefined" && _variable.color == true) {
                  $(".sass-variable-" + _index + "-value").spectrum(spectrumConfig)
               } else {
                  _variable.color = false
               }
            })
         }, 500)
      }
   }
}]);
astroidFramework.directive("dropzone", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs) {
         var _id = $(element).data("dropzone-id");
         var _dir = $(element).data("dropzone-dir");
         var _media = $(element).data("media");
         $(element).dropzone({
            url: "index.php?option=com_ajax&astroid=media&action=upload&media=" + _media,
            clickable: true,
            success: function (file, response) {
               if (response.status == "success") {
                  scope.getLibrary(response.data.folder, "astroid-media-tab-library-" + _id)
               } else {
                  Admin.notify(response.message, "error")
               }
               try {
                  Admin.refreshScroll()
               } catch (e) {}
            },
            complete: function (file) {
               this.removeAllFiles(true);
               try {
                  Admin.refreshScroll()
               } catch (e) {}
            },
            sending: function (file, xhr, formData) {
               if (_dir) {
                  formData.append("dir", $("#dropzone_folder_" + _id).val())
               } else {
                  formData.append("dir", "")
               }
            }
         })
      }
   }
});
astroidFramework.directive("rangeSlider", function () {
   return {
      restrict: "A",
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         setTimeout(function () {
            ngModel.$setViewValue(parseFloat($(element).data("slider-value")));
            scope.$apply()
         }, 50);
         setTimeout(function () {
            $(element).slider(rangeConfig)
         }, 100);
         setTimeout(function () {
            var _prefix = $(element).data("prefix");
            var _postfix = $(element).data("postfix");
            $(element).on("slide", function (e) {
               $(element).siblings(".range-slider-value").text(_prefix + e.value + _postfix)
            });
            $(element).siblings(".range-slider-value").text(_prefix + $(element).val() + _postfix);
            var setRange = function () {
               $(element).slider("setValue", ngModel.$modelValue)
            };
            scope.$watch(attrs["ngModel"], setRange)
         }, 200)
      }
   }
});
astroidFramework.directive("colorPicker", function ($parse) {
   return {
      restrict: "A",
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         if ($(element).hasClass("color-picker-lg")) {
            var spectrumConfigExtend = angular.copy(spectrumConfig);
            spectrumConfigExtend.replacerClassName = "color-picker-lg";
            $(element).spectrum(spectrumConfigExtend)
         } else {
            $(element).spectrum(spectrumConfig)
         }
         $(element).on("move.spectrum", function (e, tinycolor) {
            $(element).spectrum("set", tinycolor.toRgbString())
         });
         var setColor = function () {
            $(element).spectrum("set", ngModel.$modelValue)
         };
         setTimeout(function () {
            var _value = $(element).val();
            $(element).spectrum("set", _value);
            scope.$watch(attrs["ngModel"], setColor)
         }, 200)
      }
   }
});
astroidFramework.directive("colorSelector", function ($parse) {
   return {
      restrict: "A",
      link: function (scope, element, attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         $(element).spectrum(spectrumConfig);
         $(element).on("move.spectrum", function (e, tinycolor) {
            $(element).spectrum("set", tinycolor.toRgbString()).trigger("change")
         })
      }
   }
});
astroidFramework.directive("animationSelector", function () {
   return {
      restrict: "A",
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         setTimeout(function () {
            $(element).addClass("astroid-animation-selector");
            $(element).astroidAnimationSelector()
         }, 100)
      }
   }
});
astroidFramework.directive("selectUi", function () {
   return {
      restrict: "A",
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         var _value = $(element).val();
         setTimeout(function () {
            var _placeholder = $(element).data("placeholder");
            _placeholder = typeof _placeholder == "undefined" ? false : _placeholder;
            $(element).addClass("astroid-select-ui search selection").dropdown({
               placeholder: _placeholder,
               fullTextSearch: true
            })
         }, 200)
      }
   }
});
astroidFramework.directive("selectUiAddable", function () {
   return {
      restrict: "A",
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         setTimeout(function () {
            var _placeholder = $(element).data("placeholder");
            _placeholder = typeof _placeholder == "undefined" ? false : _placeholder;
            $(element).addClass("astroid-select-ui search selection").dropdown({
               placeholder: _placeholder,
               fullTextSearch: true
            })
         }, 200)
      }
   }
});
astroidFramework.directive("selectUiDiv", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         setTimeout(function () {
            $(element).dropdown({
               placeholder: false,
               fullTextSearch: true
            })
         }, 200)
      }
   }
});
astroidFramework.directive("astroidSwitch", function () {
   return {
      restrict: "A",
      transclude: true,
      replace: false,
      require: "ngModel",
      link: function ($scope, $element, $attr, require) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         var ngModel = require;
         var updateModelFromElement = function (checked) {
            if (checked && ngModel.$viewValue != 1) {
               ngModel.$setViewValue(1);
               $scope.$apply()
            } else if (!checked && ngModel.$viewValue != 0) {
               ngModel.$setViewValue(0);
               $scope.$apply()
            } else if (ngModel.$viewValue != 0 && ngModel.$viewValue != 1) {
               ngModel.$setViewValue(0)
            }
            try {
               Admin.refreshScroll()
            } catch (e) {}
         };
         var updateElementFromModel = function () {
            if (ngModel.$viewValue == 1) {
               $element.siblings(".custom-toggle").children(".custom-control-input").prop("checked", true);
               $element.val(1)
            } else {
               $element.siblings(".custom-toggle").children(".custom-control-input").prop("checked", false);
               $element.val(0)
            }
         };
         var initElementFromModel = function () {
            if ($element.val() == 1) {
               $element.siblings(".custom-toggle").children(".custom-control-input").prop("checked", true);
               ngModel.$setViewValue(1)
            } else {
               $element.siblings(".custom-toggle").children(".custom-control-input").prop("checked", false);
               ngModel.$setViewValue(0)
            }
         };
         $scope.$watch($attr["ngModel"], updateElementFromModel);
         var _id = $element.attr("id");
         $element.attr("id", "");
         $element.wrap("<div/>");
         var _container = $element.parent("div");
         $(_container).append('<div class="custom-control custom-toggle"><input type="checkbox" id="' + _id + '" class="custom-control-input" /><label class="custom-control-label" for="' + _id + '"></label></div>');
         $(_container).find(".custom-control-input").bind("change", function (e) {
            var _checked = $(this).is(":checked");
            updateModelFromElement(_checked)
         });
         setTimeout(function () {
            initElementFromModel()
         }, 250)
      }
   }
});
astroidFramework.directive("draggable", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         $(element).draggable({
            revert: "invalid",
            helper: "clone",
            cursor: "move"
         })
      }
   }
});
astroidFramework.directive("astroidDatetimepicker", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         $(element).datetimepicker({
            icons: {
               time: "far fa-clock",
               date: "far fa-calendar-alt",
               up: "fa fa-angle-up",
               down: "fa fa-angle-down",
               next: "fa fa-angle-right",
               previous: "fa fa-angle-left",
               today: "fa fa-bullseye",
               clear: "far fa-trash-alt",
               close: "fa fa-times"
            },
            format: "MMMM Do YYYY, h:mm a",
            timeZone: TIMEZONE
         })
      }
   }
});
astroidFramework.directive("droppable", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         $(element).droppable({
            classes: {
               "ui-droppable-active": "has-module",
               "ui-droppable-hover": "hovered"
            },
            drop: function (event, ui) {
               var _id = $(ui.draggable).data("module-id");
               var _title = $(ui.draggable).data("module-title");
               var _name = $(ui.draggable).data("module-name");
               var _colIndex = $(this).data("col");
               var _rowIndex = $(this).data("row");
               scope.rows[_rowIndex].cols[_colIndex].module.id = _id;
               scope.rows[_rowIndex].cols[_colIndex].module.title = _title;
               scope.rows[_rowIndex].cols[_colIndex].module.name = _name;
               scope.$apply()
            }
         })
      }
   }
});
astroidFramework.directive("popover", function () {
   return {
      restrict: "A",
      link: function (scope, element, attrs) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         setTimeout(function () {
            $(element).popover()
         }, 100)
      }
   }
});
astroidFramework.directive("convertToNumber", function () {
   return {
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         ngModel.$parsers.push(function (val) {
            return val != null ? parseInt(val, 10) : null
         });
         ngModel.$formatters.push(function (val) {
            return val != null ? "" + val : null
         })
      }
   }
});
astroidFramework.directive("convertToString", function () {
   return {
      require: "ngModel",
      link: function (scope, element, attrs, ngModel) {
         ngModel.$parsers.push(function (val) {
            return val != null ? val + "" : null
         });
         ngModel.$formatters.push(function (val) {
            return val != null ? "" + val : null
         })
      }
   }
});
astroidFramework.directive("astroidresponsive", ["$http", function ($http) {
   return {
      restrict: "A",
      scope: true,
      require: "ngModel",
      link: function ($scope, $element, $attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         $($element).parent(".astroidresponsive").append($("#column-responsive-field-template").html());
         var bindFields = function () {
            $($element).parent(".astroidresponsive").find(".responsive-field").bind("change", function () {
               var _params = [];
               $(".responsive-field").each(function () {
                  var _param = {};
                  _param.name = $(this).data("name");
                  if ($(this).hasClass("jd-switch")) {
                     _param.value = $(this).is(":checked") ? 1 : 0
                  } else {
                     _param.value = $(this).val()
                  }
                  _params.push(_param)
               });
               var _params = JSON.stringify(_params);
               ngModel.$setViewValue(_params);
               $($element).val(_params);
               $scope.$apply()
            })
         };
         var _initValue = false;
         var setValue = function () {
            if (!_initValue) {
               _initValue = true;
               if (typeof ngModel.$modelValue != "undefined") {
                  try {
                     var _params = JSON.parse(ngModel.$modelValue)
                  } catch (e) {
                     var _params = []
                  }
               } else {
                  var _params = []
               }
               _params.forEach(function (_param) {
                  var _field = $($element).parent(".astroidresponsive").find('.responsive-field[data-name="' + _param.name + '"]');
                  if (_field.hasClass("jd-switch")) {
                     if (_param.value) {
                        _field.prop("checked", true)
                     } else {
                        _field.prop("checked", false)
                     }
                  } else {
                     _field.val(_param.value)
                  }
               });
               setTimeout(function () {
                  bindFields()
               }, 50)
            }
         };
         setTimeout(function () {
            setValue()
         }, 100)
      }
   }
}]);
astroidFramework.directive("astroidgradient", ["$http", function ($http) {
   return {
      restrict: "A",
      scope: true,
      require: "ngModel",
      link: function ($scope, $element, $attrs, ngModel) {
         if (typeof $ == "undefined") {
            var $ = jQuery
         }
         var _gradeintPicker = $($element).parent(".astroid-gradient");
         var _typeInput = $(_gradeintPicker).find(".gradient-type");
         var _startInput = $(_gradeintPicker).find(".start-color");
         var _stopInput = $(_gradeintPicker).find(".stop-color");
         var _preview = $(_gradeintPicker).find(".gradient-preview");
         var updatePreview = function () {
            var _start = _startInput.val();
            var _stop = _stopInput.val();
            var _type = "linear";
            _typeInput.each(function () {
               if ($(this).is(":checked")) {
                  _type = $(this).val()
               }
            });
            if (_type == "radial") {
               var _gradiant = "radial-gradient(" + _start + ", " + _stop + ")"
            } else {
               var _gradiant = "linear-gradient(to bottom, " + _start + " 0%, " + _stop + " 100%)"
            }
            _preview.css("background-image", _gradiant);
            var _params = {
               type: "linear",
               start: "transparent",
               stop: "transparent"
            };
            _params.type = _type;
            _params.start = _start;
            _params.stop = _stop;
            _params = JSON.stringify(_params);
            ngModel.$setViewValue(_params);
            $($element).val(_params);
            $scope.$apply()
         };
         _typeInput.bind("change", updatePreview);
         _startInput.bind("change", updatePreview);
         _stopInput.bind("change", updatePreview);
         var _initValue = false;
         var setValue = function () {
            if (!_initValue) {
               _initValue = true;
               if (typeof ngModel.$modelValue != "undefined") {
                  try {
                     var _params = JSON.parse(ngModel.$modelValue)
                  } catch (e) {
                     var _params = {
                        type: "linear",
                        start: "transparent",
                        stop: "transparent"
                     }
                  }
               } else {
                  var _params = {
                     type: "linear",
                     start: "transparent",
                     stop: "transparent"
                  }
               }
               $(_gradeintPicker).find(".gradient-type[value=" + _params.type + "]").prop("checked", true);
               _startInput.spectrum("set", _params.start);
               _stopInput.spectrum("set", _params.stop);
               updatePreview()
            }
         };
         setTimeout(function () {
            setValue()
         }, 100)
      }
   }
}]);