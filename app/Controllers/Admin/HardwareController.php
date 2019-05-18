<?php
namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Hardware;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class HardwareController extends Controller
{
    public $auditPage = "Hardware Manager";

    /*
     * Show hardware Index.
     */
    public function index()
    {
        return view('admin.hardware.index');
    }

    /**
     * Show an existing hardware
     * @param Hardware $hardware
     * @return mixed
     */
    public function show(Hardware $hardware)
    {
        return view('admin.hardware.create')->withHardware($hardware);
    }

    /**
     * Create new hardware
     * @return mixed
     */
    public function create()
    {
        return view('admin.hardware.create')->withHardware(new Hardware);
    }

    /**
     * Store a new hardware
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        $hardware = new Hardware();
        if (! $request->sku || ! $request->price || ! $request->vendor_id || ! $request->description)
            throw new FrugalException("You must specify a sku, price, description and vendor.");

        $request->merge(['active' => 1]);

        $skuExists = (bool)Hardware::whereSku($request->sku)->whereVendorId($request->vendor_id)->count();

        if ($skuExists) {
            throw new FrugalException("That SKU already exists for this vendor.");
        }

        if ($request->hasFile('image'))
        {
            $path = public_path() . "/hardware_images/";
            $imageFile = $request->file('image');
            $originalName = $imageFile->getClientOriginalName();
            $imageFile->move($path, $originalName);
            $request->merge(['image' => $originalName]);
        }

        $hardware->create($request->input());

        audit($this->auditPage, "Added a new hardware ($request->sku)");
        return ['callback' => "redirect:/admin/hardware"];
    }

    /**
     * Update a hardware.
     * @param Hardware $hardware
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Hardware $hardware, Request $request)
    {
        if ($request->hasFile('image'))
        {
            $path = public_path() . "/hardware_images/";
            $imageFile = $request->file('image');
            $originalName = $imageFile->getClientOriginalName();
            $imageFile->move($path, $originalName);
            $request->merge(['image' => $originalName]);
        }

        $hardware->update($request->input());
        audit($this->auditPage, "Updated Hardware ID $hardware->id, SKU: $request->sku");
        return $this->success("Hardware Updated", ['callback' => "redirect:/admin/hardware"]);
    }

    /**
     * Activate/Deactivate Hardware
     * @param Hardware $hardware
     * @return array
     */
    public function destroy(Hardware $hardware)
    {
        $hardware->update(['active' => !$hardware->active]);
        $message = (!$hardware->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $hardware->sku");
        return ['callback' => "redirect:/admin/hardware"];
    }
}
