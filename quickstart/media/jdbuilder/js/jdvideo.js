(function () {
    window.jdVideoInstances = [];
    window.jdVideoRefresh = function () {
        window.jdVideoInstances.forEach(function (_instance, index) {
            if (document.getElementById(_instance.element.id) === null) {
                window.jdVideoInstances.splice(index, 1);
            }
        });
    };

    window.addEventListener('load', doSticky);
    window.addEventListener('scroll', doSticky);

    function doSticky() {
        var _stickyVideos = {
            'top-left': [],
            'top-right': [],
            'bottom-right': [],
            'bottom-left': []
        };


        window.jdVideoInstances.forEach(function (_instance) {
            if (_instance.options.sticky && !isInView(_instance.element) && isBottomWindow(_instance.element) && !_instance.stickyClosed) {
                _stickyVideos[_instance.options.stickyPosition].push(_instance);
            } else if (_instance.options.sticky && _instance.sticked) {
                _instance.unstick();
            }
        });

        for (var _position in _stickyVideos) {
            if (_stickyVideos.hasOwnProperty(_position)) {
                var _videos = _stickyVideos[_position];
                _videos.forEach(function (_instance, _index) {
                    var _shouldStick = null;
                    if (_stickyVideos[_index + 1] !== undefined && _stickyVideos[_index].element.getBoundingClientRect().top >= _instance.element.getBoundingClientRect().top) {
                        if (_instance.sticked) {
                            _instance.unstick();
                        }
                        _shouldStick = _stickyVideos[_index];
                    } else {
                        _shouldStick = _instance;
                    }

                    if (_shouldStick !== null) {
                        if (!isInView(_shouldStick.element) && isBottomWindow(_shouldStick.element)) {
                            _shouldStick.stick();
                        } else {
                            _shouldStick.unstick();
                        }
                    }
                });
            }
        }
    }



    this.jdVideo = function () {
        window.jdVideoRefresh();
        window.jdVideoInstances.push(this);

        this.videoWidth = 0;
        this.videoHeight = 0;
        this.stickyClosed = false;
        this.sticked = false;
        this.readyForSticky = false;

        // Option defaults
        var defaults = {
            prefix: 'jd',
            type: '',
            src: '',
            thumbnail: '',
            lightbox: '',
            size: '16by9',
            overlay: '',
            host: '',
            icon: '<span>&#8227;</span>',
            animation: '',
            autoplay: false,
            loop: false,
            muted: false,
            controls: true,
            sticky: false,
            stickyPosition: 'bottom-right',
            stickyClass: '',
            vimeo: {},
            youtube: {},
            dailymotion: {}
        };

        // Element Reference
        if (arguments[0] && typeof arguments[0] === "object") {
            this.element = arguments[0];
        } else {
            throw "Call jdb video on an HTML element.";
        }

        // Options by extending defaults
        if (arguments[1] && typeof arguments[1] === "object") {
            this.options = extendDefaults(defaults, arguments[1]);
        }

        build.call(this);
    };

    function stickVideo() {
        var _this = this;
        _this.sticked = true;
        addClass(_this.element, _this.options.prefix + '-video-sticky');
        addClass(_this.element, _this.options.prefix + '-video-sticky-' + _this.options.stickyPosition);
        removeClass(_this.wrapper, 'jdb-video-zoomInInside');
        addClass(_this.wrapper, 'jdb-video-zoomInOutside');
    }

    function unStickVideo() {
        var _this = this;
        _this.sticked = false;
        removeClass(_this.element, _this.options.prefix + '-video-sticky');
        removeClass(_this.element, _this.options.prefix + '-video-sticky-' + _this.options.stickyPosition);
        removeClass(_this.wrapper, 'jdb-video-zoomInOutside');
        addClass(_this.wrapper, 'jdb-video-zoomInInside');
    }

    jdVideo.prototype.play = function () {
        this.thumbnailWrapper.style.display = 'block';
        if (this.playerWrapper !== undefined) {
            this.playerWrapper.remove();
        }
        loadPlayer.call(this, true);
    }

    jdVideo.prototype.destroy = function () {
        this.element.remove();
    }

    jdVideo.prototype.unstick = function () {
        unStickVideo.call(this, true);
    }

    jdVideo.prototype.stick = function () {
        stickVideo.call(this, true);
    }

    function build() {
        var _this = this;
        this.element.innerHTML = '';

        var wrapper, thumbnailWrapper, playerWrapper, loading, icon;
        addClass(this.element, this.options.prefix + '-video');

        // wrapper
        wrapper = document.createElement('div');
        addClass(wrapper, this.options.prefix + '-video-wrapper');
        addClass(wrapper, this.options.prefix + '-video-' + _this.options.type);
        addClass(wrapper, this.options.prefix + '-video-size-' + _this.options.size);
        this.wrapper = wrapper;

        // loading
        loading = document.createElement('div');
        addClass(loading, this.options.prefix + '-video-loading');
        loading.innerHTML = '<svg version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve"><path fill="#fff" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur=".5s" from="0 50 50" to="360 50 50" repeatCount="indefinite" /></path></svg>';
        this.loading = loading;
        wrapper.appendChild(loading);

        // thumbnail wrapper
        if (_this.options.lightbox != '') {
            thumbnailWrapper = document.createElement('a');
            _this.options.autoplay = false;
        } else {
            thumbnailWrapper = document.createElement('div');
        }
        addClass(thumbnailWrapper, _this.options.prefix + '-video-play');
        if (_this.options.overlay != '' && _this.options.thumbnail != '' && _this.options.thumbnail != false) {
            var overlay = document.createElement('span');
            addClass(overlay, _this.options.prefix + '-video-overlay');
            overlay.style.background = _this.options.overlay;
            thumbnailWrapper.appendChild(overlay);
        }
        if (_this.options.lightbox != '') {
            thumbnailWrapper.setAttribute('data-jdb-lightbox', _this.options.lightbox);
            thumbnailWrapper.setAttribute('href', '#' + _this.options.lightbox + '-content');

            var lightboxVideoContainer = document.createElement('div');
            var lightboxVideoParent = document.createElement('div');
            var lightboxVideo = document.createElement('div');

            lightboxVideoContainer.style.display = 'none';
            lightboxVideoContainer.appendChild(lightboxVideoParent);

            lightboxVideoParent.setAttribute('id', _this.options.lightbox + '-content');
            lightboxVideoParent.appendChild(lightboxVideo);

            var lightboxVideoOptions = Object.assign({}, _this.options);
            lightboxVideoOptions.autoplay = false;
            lightboxVideoOptions.lightbox = '';
            lightboxVideoOptions.icon = '';
            lightboxVideoOptions.animation = '';
            var lightboxVideoEl = new jdVideo(lightboxVideo, lightboxVideoOptions);

            this.element.parentNode.insertBefore(lightboxVideoContainer, this.element.nextSibling);
        }
        thumbnailWrapper.addEventListener('click', function () {
            if (_this.options.lightbox != '') {
                return false;
            }
            loadPlayer.call(_this, true);
        });

        this.thumbnailWrapper = thumbnailWrapper;

        // thumbnail
        getVideoThumbnail.call(this);

        // icon
        icon = document.createElement('div');
        addClass(icon, this.options.prefix + '-video-playicon');
        if (this.options.animation != '') {
            addClass(thumbnailWrapper, this.options.prefix + '-video-animation-' + this.options.animation);
        }

        if (this.options.icon != '') {
            icon.innerHTML = this.options.icon;
        }

        if (this.options.icon != '') {
            thumbnailWrapper.appendChild(icon);
        }

        if ((this.options.thumbnail != false && this.options.thumbnail != '') || this.options.type == 'youtube' || this.options.type == 'vimeo') {
            wrapper.appendChild(thumbnailWrapper);
        }
        this.element.appendChild(wrapper);

        if (_this.options.sticky) {
            var placeholder = document.createElement('div');
            addClass(placeholder, this.options.prefix + '-video-placeholder');
            addClass(placeholder, this.options.prefix + '-video-size-' + this.options.size);
            placeholder.innerHTML = '';
            this.element.appendChild(placeholder);

            var stickyClose = document.createElement('div');
            addClass(stickyClose, _this.options.prefix + '-video-sticky-close');
            stickyClose.innerHTML = '<span>&#10005;</span>';
            wrapper.appendChild(stickyClose);
            stickyClose.addEventListener('click', function () {
                _this.stickyClosed = true;
                _this.unstick();
            });

            if (_this.options.stickyClass != '') {
                _this.options.stickyClass.split(' ').forEach(function (_class) {
                    addClass(_this.element, _class);
                });
            }
        }

        // if autoplay is enabled
        if (this.options.autoplay) {
            loadPlayer.call(_this, true);
        }

        this.videoHeight = this.wrapper.offsetHeight;
        this.videoWidth = this.wrapper.offsetWidth;

        if (_this.options.lightbox != '') {
            setTimeout(function () {
                jdLightboxInstances[_this.options.lightbox].props.onOpen = function () {
                    lightboxVideoEl.play();
                }
                doSticky();
            }, 100);
        }
    }

    function loadPlayer(_play) {
        this.loading.style.display = 'block';
        if (this.options.type == 'html5') {
            __loadVideo.call(this, _play);
        } else if (this.options.type == 'vimeo') {
            __loadVimeo.call(this, _play);
        } else if (this.options.type == 'dailymotion') {
            __loadDailymotion.call(this, _play);
        } else if (this.options.type == 'youtube') {
            __loadYoutube.call(this, _play);
        }
    }

    function __loadVideo(_play) {
        var _this = this;

        playerWrapper = document.createElement('div');
        addClass(playerWrapper, _this.options.prefix + '-video-player');
        playerWrapper.style.display = "none";
        _this.wrapper.appendChild(playerWrapper);
        _this.playerWrapper = playerWrapper;

        var $video = document.createElement('video');
        $video.canPlayType('video/mp4');
        $video.setAttribute('src', _this.options.src);
        $video.setAttribute('width', '100%');
        $video.setAttribute('height', '100%');
        $video.setAttribute('allow', 'autoplay');
        $video.setAttribute('controlsList', 'nodownload');
        $video.disablePictureInPicture = true;

        $video.loop = _this.options.loop;
        $video.muted = _this.options.muted;
        $video.controls = _this.options.controls;
        if (_play) {
            $video.autoplay = true;
            if (_this.options.autoplay) {
                $video.muted = true;
            }
        }

        playerWrapper.appendChild($video);

        $video.addEventListener('loadeddata', function () {
            _this.playerWrapper.style.display = 'block';
            _this.thumbnailWrapper.style.display = "none";
            if (!_play) {
                _this.loading.style.display = 'none';
            } else {
                $video.play();
            }
            if (!_this.options.controls) {
                _this.wrapper.addEventListener('click', function () {
                    if ($video.paused) {
                        $video.play();
                    } else {
                        $video.pause();
                    }
                });
            }
        });

        $video.addEventListener('play', function () {
            _this.loading.style.display = 'none';
        });
    }

    function __loadVimeo(_play) {
        var _this = this;

        if (!(typeof window.Vimeo == 'object' && typeof window.Vimeo.Player == 'function')) {
            var tag = document.createElement('script');
            tag.id = this.options.prefix + '-vimeo-api';
            tag.src = 'https://player.vimeo.com/api/player.js';
            if (document.getElementById(tag.id) === null) {
                var firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                document.getElementById(tag.id).addEventListener('load', function () {
                    __loadVimeo.call(_this, _play);
                });
            }
            return;
        }

        playerWrapper = document.createElement('div');
        addClass(playerWrapper, _this.options.prefix + '-video-player');
        playerWrapper.style.display = "none";
        _this.wrapper.appendChild(playerWrapper);
        _this.playerWrapper = playerWrapper;

        var options = {};
        options.id = getVimeoId(_this.options.src);
        options.loop = _this.options.loop;
        options.muted = _this.options.muted;
        options.controls = _this.options.controls;
        options.title = false;
        options.portrait = false;
        options.byline = false;

        options = extendDefaults(options, _this.options.vimeo);
        var $vimeo = new Vimeo.Player(playerWrapper, options);

        $vimeo.on('loaded', function () {
            playerWrapper.style.display = 'block'
            _this.thumbnailWrapper.style.display = "none";
            if (_play) {
                $vimeo.play();
            } else {
                _this.loading.style.display = 'none';
            }
            if (!_this.options.controls) {
                _this.wrapper.addEventListener('click', function () {
                    if ($vimeo.getPaused()) {
                        $vimeo.play();
                    } else {
                        $vimeo.pause();
                    }
                });
            }
        });

        $vimeo.on('play', function () {
            _this.loading.style.display = 'none';
        });
    }

    function __loadDailymotion(_play) {
        var _this = this;

        if (!(typeof window.DM == 'object' && typeof window.DM.player == 'function')) {
            var tag = document.createElement('script');
            tag.id = this.options.prefix + '-dailymotion-api';
            tag.src = 'https://api.dmcdn.net/all.js';
            if (document.getElementById(tag.id) === null) {
                var firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                document.getElementById(tag.id).addEventListener('load', function () {
                    __loadDailymotion.call(_this, _play);
                });
            }
            return;
        }

        playerWrapper = document.createElement('div');
        addClass(playerWrapper, _this.options.prefix + '-video-player');
        playerWrapper.style.display = "none";
        _this.wrapper.appendChild(playerWrapper);
        _this.playerWrapper = playerWrapper;

        var $video = document.createElement('div');
        playerWrapper.appendChild($video);

        var options = {};
        options.video = getDailymotionId(_this.options.src);

        var params = {};
        params.loop = _this.options.loop;
        params.mute = _this.options.muted;
        params.controls = _this.options.controls;

        params = extendDefaults(params, _this.options.dailymotion);
        options.params = params;

        var $dailymotion = new DM.player($video, options);

        $dailymotion.addEventListener('apiready', function () {
            playerWrapper.style.display = 'block'
            _this.thumbnailWrapper.style.display = "none";
            if (_play) {
                $dailymotion.play();
            } else {
                _this.loading.style.display = 'none';
            }

            if (!_this.options.controls) {
                _this.wrapper.addEventListener('click', function () {
                    if ($dailymotion.paused) {
                        $dailymotion.play();
                    } else {
                        $dailymotion.pause();
                    }
                });
            }
        });

        $dailymotion.addEventListener('playing', function () {
            _this.loading.style.display = 'none';
        });
    }

    function __loadYoutube(_play) {

        var _this = this;

        if (!(typeof window.YT == 'object' && typeof window.YT.Player == 'function')) {
            window.onYouTubeIframeAPIReady = function () {
                __loadYoutube.call(_this, _play);
            };

            var tag = document.createElement('script');
            tag.id = _this.options.prefix + '-youtube-api';
            if (document.getElementById(tag.id) === null) {
                tag.src = 'https://www.youtube.com/iframe_api';
                var firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }
            return;
        }

        playerWrapper = document.createElement('div');
        addClass(playerWrapper, _this.options.prefix + '-video-player');
        playerWrapper.style.display = "none";
        _this.wrapper.appendChild(playerWrapper);
        _this.playerWrapper = playerWrapper;

        var $video = document.createElement('div');
        var options = {};

        if (_this.options.host != '') {
            options.host = _this.options.host;
        }
        options.videoId = getYoutubeId(_this.options.src);
        options.playerVars = {
            loop: _this.options.loop ? 1 : 0,
            mute: _this.options.muted ? 1 : 0,
            controls: _this.options.controls ? 1 : 0,
            enablejsapi: 1,
            rel: 0
        };
        if (window.location.hostname != '') {
            options.playerVars.origin = window.location.hostname;
        }
        options.events = {
            onReady: function (event) {
                playerWrapper.style.display = 'block'
                _this.thumbnailWrapper.style.display = "none";

                if (_play) {
                    event.target.playVideo();
                } else {
                    _this.loading.style.display = 'none';
                }
            },
            onStateChange: function (event) {
                if (event.data === 1) {
                    _this.loading.style.display = 'none';
                }
            }
        }

        options.playerVars = extendDefaults(options.playerVars, _this.options.youtube);

        playerWrapper.appendChild($video);

        var $youtube = new window.YT.Player($video, options);
    }

    function getVimeoId(url) {
        if (url == '') {
            return null;
        }

        if (!Number.isNaN(Number(url))) {
            return url;
        }

        var regex = /^.*(vimeo.com\/|video\/)(\d+).*/;
        return url.match(regex) ? RegExp.$2 : url;
    }

    function getDailymotionId(url) {
        if (url == '') {
            return null;
        }

        var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator

        if (!(!!pattern.test(url))) {
            return url;
        }

        var m = url.match(/^.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/);
        if (m !== null) {
            if (m[4] !== undefined) {
                return m[4];
            }
            return m[2];
        }
        return null;
    }

    function getYoutubeId(url) {
        if (url == '') {
            return null;
        }

        var regex = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        return url.match(regex) ? RegExp.$2 : url;
    }

    function isInView(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    function isBottomWindow(el) {
        var rect = el.getBoundingClientRect();
        return rect.top < 0;
    }

    // Method to extend defaults
    function extendDefaults(source, properties) {
        var property;
        for (property in properties) {
            if (properties.hasOwnProperty(property)) {
                source[property] = properties[property];
            }
        }
        return source;
    }

    function hasClass(el, className) {
        if (el.classList)
            return el.classList.contains(className)
        else
            return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))
    }

    function addClass(el, className) {
        if (el.classList)
            el.classList.add(className)
        else if (!hasClass(el, className)) el.className += " " + className
    }

    function removeClass(el, className) {
        if (el.classList) el.classList.remove(className);
        else el.className = el.className.replace(new RegExp('\\b' + className + '\\b', 'g'), '');
    }

    function getJSON(src, options) {
        var options = options || {},
            callback_name = options.callbackName || 'callback',
            on_success = options.onSuccess || function () { },
            on_timeout = options.onTimeout || function () { },
            timeout = options.timeout || 10;

        var timeout_trigger = window.setTimeout(function () {
            window[callback_name] = function () { };
            on_timeout();
        }, timeout * 1000);

        window[callback_name] = function (data) {
            window.clearTimeout(timeout_trigger);
            on_success(data);
        };

        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = src;

        document.getElementsByTagName('head')[0].appendChild(script);
    }

    function getVideoThumbnail() {
        var _this = this;
        if (this.options.thumbnail === '' && this.options.type == 'html5') {
            var $video = document.createElement('video');
            $video.controls = false;
            $video.src = this.options.src;
            $video.preload = 'metadata';
            this.thumbnailWrapper.appendChild($video);
        } else {
            var $thumbnail = this.options.thumbnail;
            if ($thumbnail === '') {
                if (this.options.type === 'vimeo') {
                    var _id = getVimeoId(this.options.src);
                    var promise = new Promise(function (resolve, reject) {
                        getJSON('https://www.vimeo.com/api/v2/video/' + _id + '.json?callback=' + _this.options.prefix + 'VimeoCallback', {
                            callbackName: _this.options.prefix + 'VimeoCallback',
                            onSuccess: function (json) {
                                resolve(json[0].thumbnail_large);
                            },
                            timeout: 2,
                            onTimeout: function () {
                                resolve('https://i.vimeocdn.com/video/' + _id + '_640.webp');
                            }
                        });
                    });

                    promise.then(function (url) {
                        __applyThumbnail.call(_this, url);
                    });
                }
                if (this.options.type === 'dailymotion') {
                    if ($thumbnail === false) {
                        __applyThumbnail.call(this, $thumbnail);
                        return;
                    }
                    var _id = getDailymotionId(this.options.src);
                    __applyThumbnail.call(this, 'https://www.dailymotion.com/thumbnail/video/' + _id);
                }
                if (this.options.type === 'youtube') {
                    if ($thumbnail === false) {
                        __applyThumbnail.call(this, $thumbnail);
                        return;
                    }
                    var _id = getYoutubeId(this.options.src);
                    __applyThumbnail.call(this, 'https://i.ytimg.com/vi/' + _id + '/' + _this.options.thumbnailSize + '.jpg');
                }
            } else {
                __applyThumbnail.call(this, $thumbnail);
            }
        }
    }

    function __applyThumbnail($thumbnail) {
        if ($thumbnail === false) {
            loadPlayer.call(this, this.options.autoplay);
            return;
        }
        var thumbnail = document.createElement('div');
        var thumbnailImg = document.createElement('img');
        thumbnailImg.src = $thumbnail;
        thumbnailImg.style.opacity = 0;
        thumbnailImg.style.transition = 'linear .2s opacity';
        addClass(thumbnail, this.options.prefix + '-video-thumbnail');
        thumbnailImg.addEventListener('load', function () {
            thumbnailImg.style.opacity = 1;
        });
        thumbnail.appendChild(thumbnailImg);
        this.thumbnailWrapper.appendChild(thumbnail);
    }
}());