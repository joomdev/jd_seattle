<?php

namespace Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Sources;

use JEventDispatcher;
use JPluginHelper;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\Mixed\GeneratorOrder;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Elements\JoomlaContentCategories;
use Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Elements\JoomlaContentTags;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3\Slider\Slider;
use stdClass;


class JoomlaContentCategory extends AbstractGenerator {

    protected $layout = 'article';

    public function getDescription() {
        return n2_('Creates slides from your Joomla categories. (Not the articles inside them.)');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new JoomlaContentCategories($source, 'sourcecategory', n2_('Category'), 0);
        new JoomlaContentTags($source, 'sourcetags', n2_('Tags'), 0, array(
            'isMultiple' => true
        ));

        $languageRow = $filterGroup->createRow('language-row');
        new Text($languageRow, 'sourcelanguage', n2_('Language'), '*');

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'joomlaorder', 'cat.created_time|*|desc', array(
            'options' => array(
                ''                  => n2_('None'),
                'cat.title'         => n2_('Title'),
                'cat.lft'           => n2_('Ordering'),
                'cat.created_time'  => n2_('Creation time'),
                'cat.modified_time' => n2_('Modification time'),
                'cat.hits'          => n2_('Hits')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        $category = array_map('intval', explode('||', $this->data->get('sourcecategory', '')));
        $tags     = array_map('intval', explode('||', $this->data->get('sourcetags', '0')));

        $query = 'SELECT ';
        $query .= 'cat.id, ';
        $query .= 'cat.title, ';
        $query .= 'cat.alias, ';
        $query .= 'cat.description, ';
        $query .= 'cat.params, ';
        $query .= 'cat_parent.id AS parent_id, ';
        $query .= 'cat_parent.title AS parent_title ';

        $query .= 'FROM #__categories AS cat ';

        $query .= 'LEFT JOIN #__categories AS cat_parent ON cat_parent.id = cat.parent_id ';

        $where = array(
            'cat.parent_id IN (' . implode(',', $category) . ') ',
            'cat.published = 1 '
        );

        if (!in_array(0, $tags)) {
            $where[] = 'cat.id IN (SELECT content_item_id FROM #__contentitem_tag_map WHERE type_alias = \'com_content.category\'  AND tag_id IN (' . implode(',', $tags) . ')) ';
        }

        $language = $this->data->get('sourcelanguage', '*');
        if ($language) {
            $where[] = 'cat.language = ' . Database::quote($language) . ' ';
        }

        if (count($where) > 0) {
            $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $order = Common::parse($this->data->get('joomlaorder', 'cat.created_time|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';

        $result = Database::queryAll($query);

        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $r = Array(
                'title' => $result[$i]['title']
            );

            $article       = new stdClass();
            $article->text = Slider::removeShortcode($result[$i]['description']);
            $_p            = array();
            $dispatcher->trigger('onContentPrepare', array(
                'com_smartslider3',
                &$article,
                &$_p,
                0
            ));
            if (!empty($article->text)) {
                $r['description'] = $article->text;
            } else {
                $r['description'] = '';
            }
            $params = (array)json_decode($result[$i]['params'], true);

            $r['image'] = $r['thumbnail'] = ImageFallback::fallback(array(@$params['image']), array($r['description']));

            $r += array(
                'url'       => 'index.php?option=com_content&view=category&id=' . $result[$i]['id'],
                'url_label' => n2_('View category'),
                'url_blog'  => 'index.php?option=com_content&view=category&layout=blog&id=' . $result[$i]['id']
            );

            if ($result[$i]['parent_title'] != 'ROOT') {
                $r += array(
                    'parent_title'    => $result[$i]['parent_title'],
                    'parent_url'      => 'index.php?option=com_content&view=category&id=' . $result[$i]['parent_id'],
                    'parent_url_blog' => 'index.php?option=com_content&view=category&layout=blog&id=' . $result[$i]['parent_id']
                );
            } else {
                $r += array(
                    'parent_title'    => '',
                    'parent_url'      => '',
                    'parent_url_blog' => ''
                );
            }

            $r += array(
                'alias'     => $result[$i]['alias'],
                'id'        => $result[$i]['id'],
                'parent_id' => $result[$i]['parent_id']
            );

            $data[] = $r;
        }

        return $data;
    }

}
