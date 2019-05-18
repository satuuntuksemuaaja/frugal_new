<?php
namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Addon;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class AddonController extends Controller
{
    public $auditPage = "Addon Manager";

    /*
     * Show addon Index.
     */
    public function index()
    {
        return view('admin.addons.index');
    }

    /**
     * Show an existing addon
     * @param Addon $addon
     * @return mixed
     */
    public function show(Addon $addon)
    {
        return view('admin.addons.create')->withAddon($addon);
    }

    /**
     * Create new addon
     * @return mixed
     */
    public function create()
    {
        return view('admin.addons.create')->withAddon(new Addon);
    }

    /**
     * Store a new addon
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        $addon = new Addon();
        if (
            !$request->item ||
            !$request->price ||
            !$request->group_id ||
            !$request->contract
        ) {
            throw new FrugalException("You must specify an item, price, group, and contract.");
        }
        $request->merge(['active' => 1, 'automatic' => 0]);

        (new Addon)->create($request->all());

        audit($this->auditPage, "Added a new addon ($request->item)");
        return $this->success("Addon Created", ['callback' => "redirect:/admin/addons"]);
    }

    /**
     * Update a addon.
     * @param Addon $addon
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Addon $addon, Request $request)
    {
        if(!$request->automatic) $request->merge(['automatic' => 0]);
        $addon->update($request->all());
        audit($this->auditPage, "Updated Addon ID $addon->id, SKU: $request->item");
        return $this->success("Addon Updated", ['callback' => "redirect:/admin/addons"]);
    }

    /**
     * Activate/Deactivate Addon
     * @param Addon $addon
     * @return array
     */
    public function destroy(Addon $addon)
    {
        $addon->update(['active' => !$addon->active]);
        $message = (!$addon->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $addon->item");
        return ['callback' => "redirect:/admin/addons"];

    }
}
