<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/28/18
 * Time: 4:40 PM
 */

namespace FK3\Plugins;

use FK3\Plugins\Leads\LeadConfigurator;


/**
 * Class PluginRegistry
 * @package FK3\Plugins
 */
class PluginRegistry
{
    /**
     * Define the route prefixes for each of the plugins
     * @var array
     */
    static public $registry = [
        'leads' => [
            'name'     => 'Lead Board',                 // Human Readable name
            'routes'   => true,                         // Include Plugins/{Plugin}/routes.php ?
            'quotable' => false                         // This is not part of the quote plugins

        ],
    ];

    /**
     * Get an instance of the administration controller for the
     * plugin.
     * @param $plugin
     * @return mixed
     */
    static public function adminFor($plugin)
    {
        return new self::$registry[$plugin]['admin'];
    }
}