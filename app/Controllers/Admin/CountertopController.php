<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/2/18
 * Time: 5:15 PM
 */

namespace FK3\Controllers\Admin;


use FK3\Exceptions\FrugalException;
use FK3\Models\Countertop;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class CountertopController extends Controller
{
    public $auditPage = "Countertop Manager";

    /*
     * Show countertops Index.
     */
    public function index()
    {
        return view('admin.countertops.index');
    }

    /**
     * Show an existing countertop
     * @param Countertop $countertop
     * @return mixed
     */
    public function show(Countertop $countertop)
    {
        return view('admin.countertops.create')->withCountertop($countertop);
    }

    /**
     * Create new countertop
     * @return mixed
     */
    public function create()
    {
        return view('admin.countertops.create')->withCountertop(new Countertop);
    }

    /**
     * Store a new countertop
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name)
            throw new FrugalException("You must specify a countertop name.");
        $request->merge(['active' => 1]);
        (new Countertop)->create($request->all());
       // audit($this->auditPage, "Created $request->name as a new countertop");
        return ['callback' => "redirect:/admin/countertops"];
    }

    /**
     * Update a countertop.
     * @param Countertop $countertop
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Countertop $countertop, Request $request)
    {
        if (!$request->name)
            throw new FrugalException("You must specify a countertop option name.");
        $countertop->update($request->all());
        //audit($this->auditPage, "Updated $request->name");
        return ['callback' => "redirect:/admin/countertops"];
    }

    /**
     * Activate/Deactivate Lead source
     * @param Countertop $countertop
     * @return array
     */
    public function destroy(Countertop $countertop)
    {
        $countertop->update(['active' => !$countertop->active]);
        $message = (!$countertop->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $countertop->name");
        return ['callback' => "redirect:/admin/countertops"];
    }
}
