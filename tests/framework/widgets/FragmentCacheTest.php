<?php

namespace yiiunit\framework\widgets;

use Yii;
use yii\caching\ArrayCache;
use yii\base\View;
use yii\widgets\Breadcrumbs;

/**
 * @group widgets
 * @group caching
 */
class FragmentCacheTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
        Yii::$app->set('cache', [
            'class' => ArrayCache::className(),
        ]);
    }

    public function testCacheEnabled()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test'));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertFalse($view->beginCache('test'));
        $this->assertEquals("cached fragment", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testCacheDisabled1()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment";
        $view->endCache();
        $this->assertEquals("cached fragment", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testCacheDisabled2()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test'));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment other";
        $view->endCache();
        $this->assertEquals("cached fragment other", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testSingleDynamicFragment()
    {
        Yii::$app->params['counter'] = 0;

        $view = new View();

        for ($counter = 0; $counter < 42; $counter++) {
            ob_start();
            ob_implicit_flush(false);

            $cacheUnavailable = $view->beginCache('test');

            if ($counter === 0) {
                $this->assertTrue($cacheUnavailable);
            } else {
                $this->assertFalse($cacheUnavailable);
            }

            if ($cacheUnavailable) {
                echo 'dynamic cached fragment ';
                echo $view->renderDynamic('return \Yii::$app->params["counter"]++;');
                $view->endCache();
            }

            $expectedContent = vsprintf('dynamic cached fragment %d', [
                $counter,
            ]);
            $this->assertEquals($expectedContent, ob_get_clean());
        }
   }

    public function testMultipleDynamicFragments()
    {
        Yii::$app->params['counter'] = 0;

        $view = new View();

        for ($counter = 0; $counter < 42; $counter++) {
            ob_start();
            ob_implicit_flush(false);

            $cacheUnavailable = $view->beginCache('test');

            if ($counter === 0) {
                $this->assertTrue($cacheUnavailable);
            } else {
                $this->assertFalse($cacheUnavailable);
            }

            if ($cacheUnavailable) {
                echo 'dynamic cached fragment ';
                echo $view->renderDynamic('return \Yii::$app->params["counter"] * 42;');
                echo $view->renderDynamic('return \Yii::$app->params["counter"]++;');
                $view->endCache();
            }

            $expectedContent = vsprintf('dynamic cached fragment %d%d', [
                $counter * 42,
                $counter,
            ]);
            $this->assertEquals($expectedContent, ob_get_clean());
        }
   }

    // TODO test dynamic replacements
}
