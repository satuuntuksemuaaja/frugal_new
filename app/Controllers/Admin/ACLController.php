<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:11 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\Models\Acl;
use FK3\Models\AclCategory;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class ACLController extends Controller
{
    /**
     * Show all Access Control Definitions
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.acls.index');
    }

    /**
     * Create a new ACL
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $category = AclCategory::find($request->category);
        return view('admin.acls.create')->withCategory($category)->withAcl(new Acl);
    }

    /**
     * Show an ACL.
     * @param Acl $acl
     * @param Request $request
     * @return mixed
     */
    public function show(Acl $acl, Request $request)
    {
        return view('admin.acls.create')->withCategory($acl->category)->withAcl($acl);
    }

    /**
     * Create new ACL
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->acl || !$request->acl_category_id)
        {
            throw new FrugalException("You must enter an ACL and a category");
        }
        (new Acl)->create($request->all());
        return ['callback' => "redirect:/admin/acls"];
    }

    /**
     * Update an ACL
     * @param Acl $acl
     * @param Request $request
     * @throws FrugalException
     */
    public function update(Acl $acl, Request $request)
    {
        if (!$request->acl || !$request->acl_category_id)
        {
            throw new FrugalException("You must enter an ACL and a category");
        }
    }

    /**
     * Delete an ACL
     * @param Acl $acl
     * @return array
     */
    public function destroy(Acl $acl)
    {
        $acl->delete();
        return ['callback' => "redirect:/admin/acls"];
    }

}