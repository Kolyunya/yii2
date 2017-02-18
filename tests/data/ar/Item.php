<?php

namespace yiiunit\data\ar;

/**
 * Class Item
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 */
class Item extends ActiveRecord
{
    public static function tableName()
    {
        return 'item';
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}
