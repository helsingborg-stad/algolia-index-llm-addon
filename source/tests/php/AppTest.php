<?php
namespace AlgoliaIndexLLMAddon;

use AlgoliaIndexLLMAddon\App;

use Brain\Monkey\Functions;
use Mockery;

class AppTest extends \PluginTestCase\PluginTestCase
{
    public function testAddHooks()
    {
        new App();
    
        self::assertNotFalse(has_action('admin_enqueue_scripts', 'AlgoliaIndexLLMAddon\App->enqueueStyles()'));
        self::assertNotFalse(has_action('admin_enqueue_scripts', 'AlgoliaIndexLLMAddon\App->enqueueScripts()'));
    }

    public function testEnqueueStyles()
    {
        Functions\expect('wp_register_style')->once();
        Functions\expect('wp_enqueue_style')->once()->with('algolia-index-llm-addon-css');

        $app = new App();

        $app->enqueueStyles();
    }

    public function testEnqueueScripts()
    {
        Functions\expect('wp_register_script')->once();
        Functions\expect('wp_enqueue_script')->once()->with('algolia-index-llm-addon-js');

        $app = new App();

        $app->enqueueScripts();
    }
}
