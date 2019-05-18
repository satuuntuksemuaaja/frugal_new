<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/2/18
 * Time: 5:15 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\Models\Appliance;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class ApplianceController extends Controller
{
    public $auditPage = "Appliance Manager";

    /*
     * Show appliances Index.
     */
    public function index()
    {
        return view('admin.appliances.index');
    }

    /**
     * Show an existing appliance
     * @param Appliance $appliance
     * @return mixed
     */
    public function show(Appliance $appliance)
    {
        return view('admin.appliances.create')->withAppliance($appliance);
    }

    /**
     * Create new appliance
     * @return mixed
     */
    public function create()
    {
        return view('admin.appliances.create')->withAppliance(new Appliance);
    }

    /**
     * Store a new appliance
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name || !$request->price)
            throw new FrugalException("You must specify a appliance name and price.");
        $request->merge(['active' => 1]);
        (new Appliance)->create($request->all());
        audit($this->auditPage, "Added a new appliance ($request->name)");
        return ['callback' => "redirect:/admin/appliances"];
    }

    /**
     * Update a appliance.
     * @param Appliance $appliance
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Appliance $appliance, Request $request)
    {
        if (!$request->name)
            throw new FrugalException("You must specify a appliance name.");
        $appliance->update($request->all());
        audit($this->auditPage, "Updated $request->name");
        return $this->success("Appliance Updated", ['callback' => "redirect:/admin/appliances"]);
    }

    /**
     * Activate/Deactivate Lead source
     * @param Appliance $appliance
     * @return array
     */
    public function destroy(Appliance $appliance)
    {
        $appliance->update(['active' => !$appliance->active]);
        $message = (!$appliance->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $appliance->name");
        return ['callback' => "redirect:/admin/appliances"];
    }
}
