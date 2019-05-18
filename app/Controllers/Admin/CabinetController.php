<?php
namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Cabinet;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class CabinetController extends Controller
{
    public $auditPage = "Cabinet Manager";

    /*
     * Show cabinet Index.
     */
    public function index()
    {
        return view('admin.cabinets.index');
    }

    /**
     * Show an existing cabinet
     * @param Cabinet $cabinet
     * @return mixed
     */
    public function show(Cabinet $cabinet)
    {
        return view('admin.cabinets.create')->withCabinet($cabinet);
    }

    /**
     * Create new cabinet
     * @return mixed
     */
    public function create()
    {
        return view('admin.cabinets.create')->withCabinet(new Cabinet);
    }

    /**
     * Store a new cabinet
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        $cabinet = new Cabinet();
        if (
            !$request->name ||
            !$request->frugal_name ||
            !$request->vendor_id ||
            !$request->removal_price ||
            !$request->description
        ) {
            throw new FrugalException("You must specify a name, Frugal name, price, description and vendor.");
        }

        $request->merge(['active' => 1]);

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('cabinets');
            $request->merge(['image' => $filePath]);
        }

        $cabinet->create($request->input());

        audit($this->auditPage, "Added a new cabinet ($request->sku)");
        return ['callback' => "redirect:/admin/cabinets"];
    }

    /**
     * Update a cabinet.
     * @param Cabinet $cabinet
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Cabinet $cabinet, Request $request)
    {
        if (
            !$request->name ||
            !$request->frugal_name ||
            !$request->vendor_id ||
            !$request->removal_price ||
            !$request->description
        ) {
            throw new FrugalException("You must specify a name, Frugal name, price, description and vendor.");
        }

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('cabinets');
            $request->merge(['image' => $filePath]);
        }

        $cabinet->update($request->input());

        audit($this->auditPage, "Updated Cabinet ID $cabinet->id, SKU: $request->sku");
        return $this->success("Cabinet Updated", ['callback' => "redirect:/admin/cabinets"]);
    }

    /**
     * Activate/Deactivate Cabinet
     * @param Cabinet $cabinet
     * @return array
     */
    public function destroy(Cabinet $cabinet)
    {
        $cabinet->update(['active' => !$cabinet->active]);
        $message = (!$cabinet->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $cabinet->sku");
        return ['callback' => "redirect:/admin/cabinets"];

    }
}
