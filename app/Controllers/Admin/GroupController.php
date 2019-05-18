<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:10 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\Models\Acl;
use FK3\Models\Group;
use FK3\Models\GroupAcl;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Vocalogic\VocalogicException;

class GroupController extends Controller
{
    /**
     * Show all groups
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.groups.index');
    }

    /**
     * Create a new Group
     * @return mixed
     */
    public function create()
    {
        return view('admin.groups.create')->withGroup(new Group);
    }

    /**
     * Store a new Group
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function store(Request $request)
    {
        if (!$request->name)
        {
            throw new VocalogicException("You must select a group name.");
        }
        (new Group)->create($request->all());
        return $this->success("New Group Created, Redirecting..", ['callback' => "redirect:/admin/groups"]);
    }

    /**
     * Show a group
     * @param Group $group
     * @return string
     */
    public function show(Group $group)
    {
        return view('admin.groups.create')->withGroup($group);
    }

    /**
     * Update the group
     * @param Group $group
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws FrugalException
     */
    public function update(Group $group, Request $request)
    {
        if ($request->acls)
        {
            $this->updateACLs($group, $request);
            return $this->success("$group->name Controls Updated, Redirecting..",
                ['callback' => "redirect:/admin/groups"]);

        }
        if (!$request->name)
        {
            throw new VocalogicException("You must select a group name.");
        }
        $group->update($request->all());
        return $this->success("$group->name updated, Redirecting..", ['callback' => "redirect:/admin/groups"]);
    }

    /**
     * Update what the group can do.
     * @param Group $group
     * @param Request $request
     */
    private function updateACLs(Group $group, Request $request)
    {
        foreach (Acl::all() as $acl)
        {
            $id = "acl_{$acl->id}";
            if ($request->$id)
            {
                // We checked this one.
                if (!GroupAcl::whereGroupId($group->id)->whereAclId($acl->id)->first())
                {
                    (new GroupAcl)->create([
                        'acl_id'   => $acl->id,
                        'group_id' => $group->id
                    ]);
                }
            }
            else
            {
                // not checked - remove if exists.
               GroupAcl::whereGroupId($group->id)->whereAclId($acl->id)->delete();
            }
        }
    }
}