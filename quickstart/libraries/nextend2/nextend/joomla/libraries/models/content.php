<?php

class N2ModelsContent
{

    public static function search($keyword) {

        return self::_search($keyword);
    }

    private static function _search($keyword = '') {
        $result = array();
        require_once JPATH_ROOT . '/components/com_content/helpers/route.php';
        require_once(JPATH_ADMINISTRATOR . '/components/com_content/models/articles.php');
        $a     = new ContentModelArticles();
        $db    = $a->getDbo();
        $query = $db->getQuery(true);
        // Select the required fields from the table.
        $query->select($a->getState('list.select', 'a.id, a.title, a.introtext, a.images, a.alias, a.catid, a.language'));
        $query->from('#__content AS a');

        // Join over the categories.
        $query->select('c.title AS category_title')->join('LEFT', '#__categories AS c ON c.id = a.catid');

        if (!empty($keyword)) {
            if (stripos($keyword, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($keyword, 3));
            } elseif (stripos($keyword, 'author:') === 0) {
                $keyword2 = $db->quote('%' . $db->escape(substr($keyword, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $keyword2 . ' OR ua.username LIKE ' . $keyword2 . ')');
            } else {
                $keyword2 = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($keyword), true) . '%'));
                $query->where('(a.title LIKE ' . $keyword2 . ' OR a.alias LIKE ' . $keyword2 . ')');
            }
        }


        $db->setQuery($query, 0, 10);
        $articles = $db->loadAssocList();

        foreach ($articles AS $article) {
            $images = new N2Data($article['images'], true);
            $image  = $images->get('image_fulltext', $images->get('image_intro', ''));
            if (substr($image, 0, 2) != '//' && substr($image, 0, 4) != 'http') {
                $image = JUri::root(false) . $image;
            }

            $result[] = array(
                'title'       => $article['title'],
                'description' => $article['introtext'],
                'image'       => N2ImageHelper::dynamic($image),
                'link'        => ContentHelperRoute::getArticleRoute($article['id'], $article['catid'], $article['language']),
                'info'        => $article['category_title']
            );
        }

        if (count($result) == 0 && !empty($keyword)) {
            return self::_search();
        }
        return $result;
    }
}