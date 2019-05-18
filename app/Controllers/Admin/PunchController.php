<?php

namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Punch;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class PunchController extends Controller
{
    public $auditPage = "Punch Manager";

    /*
     * Show punches Index.
     */
    public function index()
    {
        return view('admin.punches.index');
    }

    /**
     * Show an existing punch
     * @param Punch $punch
     * @return mixed
     */
    public function show(Punch $punch)
    {
        return view('admin.punches.create')->withPunch($punch);
    }

    /**
     * Create new punch
     * @return mixed
     */
    public function create()
    {
        return view('admin.punches.create')->withPunch(new Punch);
    }

    /**
     * Store a new punch
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->question || empty($request->question) || !$request->group_id) {
            throw new FrugalException("You must specify a punch question, and a group.");
        }
        $request->merge(['active' => 1]);
        (new Punch)->create($request->all());

        audit($this->auditPage, "Created $request->question as a new punch");
        return ['callback' => "redirect:/admin/punches"];
    }

    /**
     * Update a punch.
     * @param Punch $punch
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Punch $punch, Request $request)
    {
        if (!$request->question || empty($request->question))
            throw new FrugalException("You must specify a punch option question.");
        $punch->update($request->all());

        audit($this->auditPage, "Updated $request->question");
        return ['callback' => "redirect:/admin/punches"];
    }

    /**
     * Activate/Deactivate Lead source
     * @param Punch $punch
     * @return array
     */
    public function destroy(Punch $punch)
    {
        $punch->update(['active' => !$punch->active]);
        $message = (!$punch->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $punch->question");
        return ['callback' => "redirect:/admin/punches"];
    }
}
