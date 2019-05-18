<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/29/18
 * Time: 4:13 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Models\Setting;
use FK3\Plugins\PluginRegistry;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class SettingsController extends Controller
{
    /**
     * Show all settings
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Create a new setting
     * @param Request $request
     * @return
     */
    public function create(Request $request)
    {
        $plugin = PluginRegistry::$registry[$request->plugin]['name'];
        return view('admin.settings.create')->withSetting(new Setting)->withPlugin($plugin);
    }

    /**
     * Edit a setting
     * @param Setting $setting
     * @return string
     */
    public function show(Setting $setting)
    {
        $plugin = $setting->plugin;
        return view('admin.settings.create')->withSetting($setting)->withPlugin($plugin);
    }

    /**
     * Create new setting
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        Setting::create($request->all());
        return ['callback' => "redirect:/admin/settings"];
    }

    /**
     * Update a setting
     * @param Setting $setting
     * @param Request $request
     * @return array
     */
    public function update(Setting $setting, Request $request)
    {
        $setting->update($request->all());
        return ['callback' => "redirect:/admin/settings"];
    }
}