<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 9:19 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\Models\LeadSource;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class LeadSourceController extends Controller
{
    public $auditPage = "Lead Source Admin";

    /**
     * Show all lead sources.
     */
    public function index()
    {
        return view('admin.sources.index');
    }

    /**
     * Create new lead source
     * @return mixed
     */
    public function create()
    {
        return view('admin.sources.create')->withSource(new LeadSource);
    }

    /**
     * Store a new lead source
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name)
        {
            throw new FrugalException("You must enter a source name.");
        }
        (new LeadSource)->create($request->all());
        audit($this->auditPage, "Created $request->name as a lead source");
        return ['callback' => "redirect:/admin/lead_sources"];
    }

    /**
     * Edit a lead source.
     * @param LeadSource $leadSource
     * @return mixed
     */
    public function show(LeadSource $leadSource)
    {
        return view('admin.sources.create')->withSource($leadSource);
    }

    /**
     * Update
     * @param LeadSource $leadSource
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(LeadSource $leadSource, Request $request)
    {
        if (!$request->name)
        {
            throw new FrugalException("You must enter a source name.");
        }
        if ($leadSource->name != $request->name)
        {
            audit($this->auditPage, "Changed $leadSource->name to $request->name");
        }
        $leadSource->update($request->all());
        return ['callback' => "redirect:/admin/lead_sources"];
    }

    /**
     * Activate/Deactivate Lead source
     * @param LeadSource $leadSource
     * @return array
     */
    public function destroy(LeadSource $leadSource)
    {
        $leadSource->update(['active' => !$leadSource->active]);
        $message = (!$leadSource->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $leadSource->name");
        return ['callback' => "redirect:/admin/lead_sources"];
    }

}