<?php

namespace yiiunit\framework\behaviors;

use Yii;
use yiiunit\TestCase;
use yii\db\Connection;
use yii\db\ActiveRecord;
use yii\behaviors\AttributeBehavior;

/**
 * Unit test for [[\yii\behaviors\AttributeBehavior]].
 * @see AttributeBehavior
 *
 * @group behaviors
 */
class AttributeBehaviorTest extends TestCase
{
    /**
     * @var Connection test db connection
     */
    protected $dbConnection;

    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ]);

        $columns = [
            'id' => 'pk',
            'name' => 'string',
            'alias' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_attribute', $columns)->execute();
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
    }

    // Tests :

    /**
     * @return array
     */
    public function preserveNonEmptyValuesDataProvider()
    {
        return [
            [
                'John Doe',
                false,
                'John Doe',
                null,
            ],
            [
                'John Doe',
                false,
                'John Doe',
                'Johnny',
            ],
            [
                'John Doe',
                true,
                'John Doe',
                null,
            ],
            [
                'Johnny',
                true,
                'John Doe',
                'Johnny',
            ],
        ];
    }

    /**
     * @dataProvider preserveNonEmptyValuesDataProvider
     */
    public function testPreserveNonEmptyValues(
        $aliasExpected,
        $preserveNonEmptyValues,
        $name,
        $alias
    )
    {
        $model = new ActiveRecordWithAttributeBehavior();
        $model->attributeBehavior->preserveNonEmptyValues = $preserveNonEmptyValues;
        $model->name = $name;
        $model->alias = $alias;
        $model->validate();

        $this->assertEquals($aliasExpected, $model->alias);
    }
}

/**
 * Test Active Record class with [[AttributeBehavior]] behavior attached.
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 *
 * @property AttributeBehavior $attributeBehavior
 */
class ActiveRecordWithAttributeBehavior extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'attribute' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_VALIDATE => 'alias',
                ],
                'value' => function ($event) {
                    return $event->sender->name;
                },
            ],
        ];
    }

    public static function tableName()
    {
        return 'test_attribute';
    }

    /**
     * @return AttributeBehavior
     */
    public function getAttributeBehavior()
    {
        return $this->getBehavior('attribute');
    }
}
