<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/28/18
 * Time: 4:50 PM
 */

namespace FK3\Plugins;


use Illuminate\Http\Request;

interface PluginConfiguratorInterface
{
    /**
     * Show the plugin's administrative interface.
     * @return mixed
     */
    public function index();

    /**
     * Save method for the plugin.
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request);
}