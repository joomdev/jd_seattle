<?php

N2Loader::import('libraries.renderable.layers.itemFactory', 'smartslider');

class N2SSItemYouTube extends N2SSItemAbstract {

    protected $type = 'youtube';

    public function render() {
        $owner = $this->layer->getOwner();
        /**
         * @var $this ->data N2Data
         */
        $this->data->fillDefault(array(
            'image'    => '',
            'start'    => 0,
            'volume'   => -1,
            'autoplay' => 0,
            'controls' => 1,
            'center'   => 0,
            'loop'     => 0,
            'reset'    => 0,
            'related'  => 1,
        ));

        $rawYTUrl = $owner->fill($this->data->get('youtubeurl', ''));

        $url_parts = parse_url($rawYTUrl);
        if (!empty($url_parts['query'])) {
            parse_str($url_parts['query'], $query);
            if (isset($query['v'])) {
                unset($query['v']);
            }
            $this->data->set("query", $query);
        }

        $youTubeUrl = $this->parseYoutubeUrl($rawYTUrl);

        $start = $owner->fill($this->data->get('start', ''));
        $this->data->set("youtubecode", $youTubeUrl);
        $this->data->set("start", $start);

        $end = $owner->fill($this->data->get('end', ''));
        $this->data->set("youtubecode", $youTubeUrl);
        $this->data->set("end", $end);

        $style = '';

        $hasImage = 0;
        $image    = $owner->fill($this->data->get('image'));

        $coverImage = '';
        if (!empty($image)) {
            $style     = 'cursor:pointer; background: URL(' . N2ImageHelper::fixed($image) . ') no-repeat 50% 50%; background-size: cover';
            $hasImage  = 1;
            $playImage = '';

            if ($this->data->get('playbutton', 1) == 1) {

                $playWidth  = intval($this->data->get('playbuttonwidth', '48'));
                $playHeight = intval($this->data->get('playbuttonheight', '48'));
                if ($playWidth > 0 && $playHeight > 0) {

                    $attributes = array(
                        'style' => ''
                    );

                    $attributes['style'] .= 'width:' . $playWidth . 'px;';
                    $attributes['style'] .= 'height:' . $playHeight . 'px;';
                    $attributes['style'] .= 'margin-left:' . ($playWidth / -2) . 'px;';
                    $attributes['style'] .= 'margin-top:' . ($playHeight / -2) . 'px;';

                    $playButtonImage = $this->data->get('playbuttonimage', '');
                    if (!empty($playButtonImage)) {
                        $src = N2ImageHelper::fixed($this->data->get('playbuttonimage', ''));
                    } else {
                        $src = N2ImageHelperAbstract::SVGToBase64('$ss$/images/play.svg');
                    }

                    $playImage = N2Html::image($src, 'Play', $attributes);
                }
            }

            $coverImage = N2Html::tag('div', array(
                'class' => 'n2-ss-layer-player n2-ss-layer-player-cover',
                'style' => $style
            ), $playImage);
        }

        $this->data->set('privacy-enhanced', intval(N2SmartSliderSettings::get('youtube-privacy-enhanced', 0)));

        $owner->addScript('new N2Classes.FrontendItemYouTube(this, "' . $this->id . '", ' . $this->data->toJSON() . ', ' . $hasImage . ');');

        return N2Html::tag('div', array(
            'id'    => $this->id,
            'class' => 'n2-ss-layer-player n2-ss-item-content n2-ow-all'
        ), N2Html::tag('div', array(
                'id' => $this->id . '-frame',
            ), '') . $coverImage);
    }

    public function _renderAdmin() {

        $image = $this->layer->getOwner()
                             ->fill($this->data->get('image'));
        $this->data->set('image', $image);

        return N2Html::tag('div', array(
            'class' => 'n2-ow',
            "style" => 'width: 100%; height: 100%; background: URL(' . N2ImageHelper::fixed($this->data->getIfEmpty('image', '$system$/images/placeholder/video.png')) . ') no-repeat 50% 50%; background-size: cover;'
        ), $this->data->get('playbutton', 1) ? '<div class="n2-ss-layer-player n2-ss-layer-player-cover">' . N2Html::image(N2ImageHelperAbstract::SVGToBase64('$ss$/images/play.svg')) . '</div>' : '');

    }

    private function parseYoutubeUrl($youTubeUrl) {
        preg_match('/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/', $youTubeUrl, $matches);

        if ($matches && isset($matches[7]) && strlen($matches[7]) == 11) {
            return $matches[7];
        }

        return $youTubeUrl;
    }

    public function needSize() {
        return true;
    }
}