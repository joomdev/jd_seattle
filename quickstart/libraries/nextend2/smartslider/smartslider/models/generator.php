<?php
N2Loader::import("libraries.slider.abstract", "smartslider");

class N2SmartsliderGeneratorModel extends N2Model {

    private static function getLayout($type) {

        N2Loader::import('libraries.slidebuilder.component', 'smartslider');
        N2Loader::importAll('libraries.slidebuilder', 'smartslider');

        $slideBuilder = new N2SmartSliderSlideBuilder();

        switch ($type) {
            case 'image':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'thumbnail'       => "{thumbnail}",
                    'backgroundImage' => "{image}",
                    'background-type' => 'image'
                ));
                break;

            case 'image_extended':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'thumbnail'       => "{thumbnail}",
                    'backgroundImage' => "{image}",
                    'background-type' => 'image'
                ));
              
                $slideBuilder->content->set(array(
                    'verticalalign'             => 'flex-end',
                    'desktopportraitpadding'    => '0|*|0|*|0|*|0|*|px+'
                ));
                $row = new N2SmartSliderSlideBuilderRow($slideBuilder->content);
                $row->set(array(
                    'bgcolor' => '00000080',
                ));
                $col = new N2SmartSliderSlideBuilderColumn($row, '1');
                $col->set(array(
                    'desktopportraitinneralign' => "left"
                ));
                $heading = new N2SmartSliderSlideBuilderLayer($col, 'heading');
                $heading->set(array(
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $heading->item->set(array(
                    'heading' => '{title/1}',
                ));
                break;

            case 'article':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'description'     => '{description}',
                    'href'            => '{url}',
                    'thumbnail'       => "{thumbnail}",
                    'backgroundImage' => "{image}",
                    'background-type' => 'image'
                ));
                
                $slideBuilder->content->set(array(
                    'verticalalign'             => 'flex-end',
                    'desktopportraitpadding'    => '0|*|0|*|0|*|0|*|px+',
                ));
                $row = new N2SmartSliderSlideBuilderRow($slideBuilder->content);
                $row->set(array(
                    'bgcolor' => '00000080',
                ));
                $col = new N2SmartSliderSlideBuilderColumn($row, '1');
                $col->set(array(
                    'desktopportraitinneralign' => "left",
                ));
                $heading = new N2SmartSliderSlideBuilderLayer($col, 'heading');
                $heading->set(array(
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $heading->item->set(array(
                    'heading'   => '{title}',
                    'font'      => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"36||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}')
                ));
                break;

            case 'product':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'description'     => '{description}',
                    'href'            => '{url}',
                    'thumbnail'       => "{thumbnail}",
                    'backgroundImage' => "{image}",
                    'background-type' => 'image'
                ));

                $slideBuilder->content->set(array(
                    'verticalalign'             => 'flex-end',
                    'desktopportraitpadding'    => '0|*|0|*|0|*|0|*|px+',
                ));
                $row = new N2SmartSliderSlideBuilderRow($slideBuilder->content);
                $row->set(array(
                    'bgcolor'                   => '00000080',
                ));
                $col = new N2SmartSliderSlideBuilderColumn($row, '1/2');
                $col->set(array(
                    'desktopportraitinneralign' => "left",
                ));
                $heading = new N2SmartSliderSlideBuilderLayer($col, 'heading');
                $heading->set(array(
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $heading->item->set(array(
                    'heading'   => '{title}',
                    'font'      => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"36||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}'),
                ));
                $col2 = new N2SmartSliderSlideBuilderColumn($row, '1/2');
                $col2->set(array(
                    'desktopportraitinneralign' => "right",
                ));
                $text = new N2SmartSliderSlideBuilderLayer($col2, 'text');
                $text->set(array(
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $text->item->set(array(
                    'content'   => '{price}',
                    'font'      => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"36||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}'),
                ));

                break;

            case 'event':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'description'     => '{description}',
                    'href'            => '{url}',
                    'thumbnail'       => "{thumbnail}",
                    'backgroundImage' => "{image}",
                    'background-type' => 'image'
                ));
                $slideBuilder->content->set(array(
                    'verticalalign'          => 'flex-end',
                    'desktopportraitpadding' => '0|*|0|*|0|*|0|*|px+',
                ));
                $row = new N2SmartSliderSlideBuilderRow($slideBuilder->content);
                $row->set(array(
                    'bgcolor'                => '00000080',
                ));
                $col = new N2SmartSliderSlideBuilderColumn($row, '1/2');
                $col->set(array(
                    'desktopportraitinneralign' => "left",
                ));
                $heading = new N2SmartSliderSlideBuilderLayer($col, 'heading');
                $heading->set(array(
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $heading->item->set(array(
                    'heading' => '{title}',
                    'font'    => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"36||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}'),
                ));
                $col2 = new N2SmartSliderSlideBuilderColumn($row, '1/2');
                $col2->set(array(
                    'desktopportraitinneralign' => "right",
                ));
                $heading = new N2SmartSliderSlideBuilderLayer($col2, 'heading');
                $heading->set(array(
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $heading->item->set(array(
                    'heading'   => '{start_date}',
                    'font'      => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"36||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}'),
                ));
            
                break;

            case 'youtube':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'description'     => '{description}',
                    'href'            => '{url}',
                    'thumbnail'       => "{thumbnail}",
                    'backgroundColor' => "ffffff00",
                    'background-type' => 'color',
                ));

                $youtube = new N2SmartSliderSlideBuilderLayer($slideBuilder, 'youtube');
                $youtube->set(array(
                    'desktopportraitwidth'  => '100%',
                    'desktopportraitheight' => '100%',
                    'desktopportraitalign'  => 'left',
                    'desktopportraitvalign' => 'top'
                ));
                $youtube->item->set(array(
                    "youtubeurl" => "{video_url}",
                ));
                break;

            case 'vimeo':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'description'     => '{description}',
                    'href'            => '{url}',
                    'thumbnail'       => "{image200x150/1}",
                    'backgroundColor' => "ffffff00",
                    'background-type' => 'color',
                ));

                $vimeo = new N2SmartSliderSlideBuilderLayer($slideBuilder, 'vimeo');
                $vimeo->set(array(
                    'desktopportraitwidth'  => '100%',
                    'desktopportraitheight' => '100%',
                    'desktopportraitalign'  => 'left',
                    'desktopportraitvalign' => 'top'
                ));
                $vimeo->item->set(array(
                    "vimeourl" => "{url}",
                    'image'    => ''
                ));

                break;

            case 'social_post':
                $slideBuilder->set(array(
                    'title'           => "{title}",
                    'description'     => '{description}',
                    'href'            => '{url}',
                    'thumbnail'       => "{author_image}",
                    'backgroundColor' => "ffffff00",
                    'background-type' => 'color',
                ));

                $slideBuilder->content->set(array(
                    'verticalalign'          => 'center',
                    'desktopportraitpadding' => '0|*|0|*|0|*|0|*|px+',
                    'desktopportraitmargin'  => '0|*|0|*|0|*|0|*|px+'
                ));

                $row = new N2SmartSliderSlideBuilderRow($slideBuilder->content);
                $row->set(array(
                    'bgcolor'                => '00000080',
                    'desktopportraitpadding' => '10|*|10|*|10|*|10|*|px+',
                    'desktopportraitmargin'  => '0|*|0|*|0|*|0|*|px+'
                ));
                $col = new N2SmartSliderSlideBuilderColumn($row, '1');
                $col->set(array(
                    'desktopportraitinneralign' => "left",
                    'desktopportraitmargin'     => '0|*|0|*|0|*|0|*|px+',
                    'desktopportraitpadding'    => '10|*|10|*|10|*|10|*|px+'
                ));
                $heading = new N2SmartSliderSlideBuilderLayer($col, 'heading');
                $heading->set(array(
                    'desktopportraitmargin'     => '0|*|0|*|0|*|0|*|px+',
                    'desktopportraitselfalign'  => 'inherit'
                ));
                $heading->item->set(array(
                    'heading' => '{message}',
                ));
                $image = new N2SmartSliderSlideBuilderLayer($col, 'image');
                $image->set(array(
                    'desktopportraitmargin'    => '0|*|0|*|0|*|0|*|px+',
                    'desktopportraitselfalign' => 'inherit'
                ));
                $image->item->set(array(
                    'image' => '{author_image}',
                ));
                $button = new N2SmartSliderSlideBuilderLayer($col, 'button');
                $button->set(array(
                    'desktopportraitmargin'    => '0|*|0|*|0|*|0|*|px+',
                    'desktopportraitselfalign' => 'inherit'
                ));
                $button->item->set(array(
                    'content' => '{url_label}',
                ));
            
                break;

            default:
                return $slideBuilder->set(array(
                    'title'           => "title",
                    'description'     => '',
                    'backgroundColor' => "ffffff00",
                    'background-type' => 'color',
                ));
        }

        return $slideBuilder->getData();
    }

    public function __construct() {
        parent::__construct("nextend2_smartslider3_generators");
    }

    public function createGenerator($sliderId, $params) {

        $data = new N2Data($params);

        unset($params['type']);
        unset($params['group']);
        unset($params['record-slides']);

        try {
            $generatorId = $this->_create($data->get('type'), $data->get('group'), json_encode($params));


            $source = $this->getGeneratorGroup($data->get('group'))
                           ->getSource($data->get('type'));

            $slideData = self::getLayout($source->getLayout());

            $slideData['published']     = '1';
            $slideData['publishdates']  = '|*|';
            $slideData['generator_id']  = $generatorId;
            $slideData['record-slides'] = intval($data->get('record-slides', 5));
            $slideData['slide']         = json_encode($slideData['slide']);
            $slidesModel                = new N2SmartsliderSlidesModel();
            $slideId                    = $slidesModel->create($sliderId, $slideData, false);


            return array(
                'slideId'     => $slideId,
                'generatorId' => $generatorId
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function generatorCommonForm($data = array()) {

        N2Loader::import('libraries.form.form');
        $form = new N2Form();
        $form->set('class', 'nextend-smart-slider-admin');
        $form->loadArray($data);

        $settings = new N2Tab($form, 'generator', n2_('Generator settings'));

        new N2ElementNumber($settings, 'record-slides', n2_('Slides'), 5, array(
            'unit' => n2_('slides'),
            'wide' => 4,
        ));

        if (N2SSPRO) {
            $record = new N2ElementGroup($settings, 'generator-record-offset', n2_('Record'), array(
                'rowClass' => 'n2-expert'
            ));
            new N2ElementNumber($record, 'record-start', n2_('Start index'), 1, array(
                'wide' => 3
            ));
            new N2ElementNumber($record, 'record-group', n2_('Group result'), 1, array(
                'wide' => 3
            ));
        }//N2SSPRO


        new N2ElementNumber($settings, 'cache-expiration', n2_('Cache expiration'), 24, array(
            'wide' => 3,
            'unit' => n2_('hours')
        ));

        new N2ElementButton($settings, 'record-viewer', n2_('Record viewer'), n2_('View records'));
        new N2ElementToken($settings);

        echo $form->render('generator');
    }

    /**
     * @param $type
     *
     * @return N2SliderGeneratorPluginAbstract
     */
    public function getGeneratorGroup($type) {

        return N2SSGeneratorFactory::getGenerator($type);
    }

    public function get($id) {
        return $this->db->queryRow("SELECT * FROM " . $this->getTable() . " WHERE id = :id", array(
            ":id" => $id
        ));
    }

    public function import($generator) {
        $this->db->insert(array(
            'type'   => $generator['type'],
            'group'  => $generator['group'],
            'params' => $generator['params']
        ));

        return $this->db->insertId();
    }

    private function _create($type, $group, $params) {
        $this->db->insert(array(
            'type'   => $type,
            'group'  => $group,
            'params' => $params
        ));

        return $this->db->insertId();
    }

    public function save($generatorId, $params) {

        $this->db->update(array(
            'params' => json_encode($params)
        ), array('id' => $generatorId));

        return $generatorId;
    }

    public function delete($id) {
        $this->db->deleteByAttributes(array(
            "id" => intval($id)
        ));
    }

    public function duplicate($id) {
        $generatorRow = $this->get($id);
        $generatorId  = $this->_create($generatorRow['type'], $generatorRow['group'], $generatorRow['params']);

        return $generatorId;
    }

    public function getSliderId($generatorId) {

        $slidesModal = new N2SmartsliderSlidesModel();
        $slideData   = $this->db->queryRow("SELECT slider FROM " . $slidesModal->getTable() . " WHERE generator_id = :id", array(
            ":id" => $generatorId
        ));

        return $slideData['slider'];
    }
}