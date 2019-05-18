<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/2/18
 * Time: 5:15 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\Models\Vendor;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class VendorController extends Controller
{
    public $auditPage = "Vendor Manager";

    /*
     * Show Vendors Index.
     */
    public function index()
    {
        return view('admin.vendors.index');
    }

    /**
     * Show an existing vendor
     * @param Vendor $vendor
     * @return mixed
     */
    public function show(Vendor $vendor)
    {
        return view('admin.vendors.create')->withVendor($vendor);
    }

    /**
     * Create new vendor
     * @return mixed
     */
    public function create()
    {
        return view('admin.vendors.create')->withVendor(new Vendor);
    }

    /**
     * Store a new Vendor
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name)
            throw new FrugalException("You must specify a vendor name.");
        $request->merge(['active' => 1]);
        (new Vendor)->create($request->all());
        audit($this->auditPage, "Created $request->name as a new vendor");
        return ['callback' => "redirect:/admin/vendors"];
    }

    /**
     * Update a vendor.
     * @param Vendor $vendor
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Vendor $vendor, Request $request)
    {
        if (!$request->name)
            throw new FrugalException("You must specify a vendor name.");
        $vendor->update($request->all());
        audit($this->auditPage, "Updated $request->name");
        return ['callback' => "redirect:/admin/vendors"];
    }

    /**
     * Activate/Deactivate Lead source
     * @param Vendor $vendor
     * @return array
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->update(['active' => !$vendor->active]);
        $message = (!$vendor->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $vendor->name");
        return ['callback' => "redirect:/admin/vendors"];
    }
}