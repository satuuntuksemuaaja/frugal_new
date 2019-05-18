<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:10 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\User;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Vocalogic\VocalogicException;

class UserController extends Controller
{
    public $auditPage = "User Manager";
    /**
     * Show all users
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Create a new user
     * @return mixed
     */
    public function create()
    {
        return view('admin.users.create')->withUser(new User);
    }

    /**
     * Store a new user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name || !$request->email || !$request->password)
            throw new FrugalException("You must specify the name and email and a password.");
        if (!$request->group_id)
            throw new FrugalException("You must specify what group this user is part of.");
        $request->merge(['password' => bcrypt($request->password)]);
        $user = (new User)->create($request->all());
        audit($this->auditPage, "Created $request->name in Frugal 3");
        return $this->success($user->name . " has been created, Redirecting.. ", ['callback' => "redirect:/admin/users"]);
    }

    /**
     *
     * @param User $user
     * @return mixed
     */
    public function show(User $user)
    {
        return view('admin.users.create')->withUser($user);
    }

    /**
     * Update a user.
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function update(User $user, Request $request)
    {
        if ($request->password)
        {
            $request->merge(['password' => bcrypt($request->password)]);
            $user->update($request->all());
        }
        else $user->update($request->except(['password']));
        return $this->success($user->name . " has been updated.", ['callback' => "redirect:/admin/users"]);
    }
    /**
     * Activate/Deactivate Lead source
     * @param User $user
     * @return array
     */
    public function destroy(User $user)
    {
        $user->update(['active' => !$user->active]);
        $message = (!$user->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $user->name");
        return ['callback' => "redirect:/admin/users"];
    }
}
