<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class acymtagClass extends acymClass
{
    var $table = 'tag';
    var $pkey = 'id';

    public function setTags($type, $elementId, $newTags)
    {
        acym_query('DELETE FROM #__acym_tag WHERE `type` = '.acym_escapeDB($type).' AND id_element = '.intval($elementId));

        $tagsToInsertQuery = [];

        foreach ($newTags as $oneTag) {

            $newTag = new stdClass();
            $newTag->type = $type;

            if (strpos($oneTag, "acy_new_tag_") !== false) {
                $tagName = substr($oneTag, strlen("acy_new_tag_"));
                if (empty($tagName)) {
                    continue;
                }
                $newTag->name = $tagName;
            } else {
                $newTag->name = $oneTag;
            }
            $tagsToInsertQuery[] = '('.acym_escapeDB($newTag->name).','.acym_escapeDB($newTag->type).', '.intval($elementId).')';
        }
        if (!empty($tagsToInsertQuery)) {
            acym_query('INSERT INTO #__acym_tag (`name`, `type`, `id_element`) VALUES '.implode(',', $tagsToInsertQuery));
        }
    }

    public function getAllTagsByType($type)
    {
        $query = 'SELECT `name` as value, `name` FROM #__acym_tag WHERE `type` = '.acym_escapeDB($type).' GROUP BY `name`';

        return acym_loadObjectList($query);
    }

    public function getAllTagsByElementId($type, $id)
    {
        if (empty($id)) return [];

        $query = 'SELECT * FROM #__acym_tag WHERE type = '.acym_escapeDB($type).' AND id_element = '.intval($id);
        $tags = acym_loadResultArray($query);

        return empty($tags) ? [] : $tags;
    }

    public function getAllTagsByTypeAndElementIds($type, $ids)
    {
        acym_arrayToInteger($ids);
        if (empty($ids)) {
            return [];
        }

        $query = 'SELECT * FROM #__acym_tag WHERE `type` = '.acym_escapeDB($type).' AND `id_element` IN ('.implode(',', $ids).')';

        return acym_loadObjectList($query);
    }
}

