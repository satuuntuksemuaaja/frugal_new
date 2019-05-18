<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:13 PM
 */

use FK3\Models\Acl;
use FK3\Models\Audit;
use FK3\Models\Group;
use FK3\Models\Setting;

define("APP_VERSION", "3.00a");

/**
 * Takes an acl dot name and returns true
 * if this group has that access.
 * @param $group
 * @param $acl
 * @return bool
 */
function groupCan(?Group $group, $acl): bool
{
    if ($group == null) return false;
    $id = Acl::whereAcl($acl)->first();
    if (!$id) return false;
    $id = $id->id;
    if ($group->acls->contains('acl_id', $id))
    {
        return true;
    }
    else return false;
}

/**
 * Digs in to the user's group and then
 * returns if the user can perform the
 * action requested.
 * @param $acl
 * @param null|User $user
 * @return bool
 */
function userCan($acl, ?User $user = null): bool
{
    if (!$user) $user = user();
    if (groupCan($user->group, $acl))
    {
        return true;
    }
    else return false;
}

/**
 * Get settings for a plugin
 * @param $plugin
 * @return mixed
 */
function settingsFor($plugin)
{
    return Setting::wherePlugin($plugin)->get();
}

/**
 * Get a setting for a plugin
 * @param $plugin
 * @param $setting
 * @return null
 */
function setting($plugin, $setting)
{
    $setting = Setting::wherePlugin($plugin)->whereSetting($setting)->first();
    if ($setting)
    {
        return $setting->value;
    }
    else return null;
}

/**
 * Create and return an audit
 * @param $page
 * @param $action
 * @param array $extras
 * @return Audit
 */
function audit($page, $action, array $extras = []): Audit
{
    if (preg_match("/\%s/", $action))
    {
        $action = sprintf($action, user()->name);
    }
    return (new Audit)->create([
        'user_id' => user()->id,
        'page'    => $page,
        'action'  => $action,
    ]);
    return true;
}

/**
 * Render the last X number of plugin logs.
 * @param $plugin
 * @param int $last
 * @return string
 */
function renderAuditsForPlugin($plugin, $last = 10) :string
{
    $data = "<ul class='list-group list-group-flush'>";
    foreach (Audit::wherePage($plugin)->orderBy('created_at', 'DESC')->limit($last)->get() as $audit)
    {
        $data .= "<li class='list-group-item d-flex align-items-center justify-content-between'>
            <div class='media-body lh-1'>{$audit->user->name} <span>$audit->action</span></div>
            <small class='text-muted'>{$audit->created_at->diffForHumans()}</small></li>";
    }
    $data .= "</ul>";
    return $data;
}