<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Quote;
use FK3\Models\Setting;
use FK3\Exceptions\FrugalException;
use FK3\vl\quotes\QuoteGeneratorNew;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class DynamicController extends Controller
{
    public $auditPage = "Dynamic";

    /*
     * Show dynamic Index.
     */
    public function index()
    {
        $quote = new QuoteGeneratorNew(new Quote);

        return view('admin.dynamic.index', compact('quote'));
    }

    /**
     * Store Dynamic Data
     * @return Array
     */

    public function store(Request $request)
    {
        foreach ($request->all() AS $key => $val)
        {
            $setting = Setting::whereName($key)->first();
            if (!$setting)
            {
                continue;
            }

            $setting->value = $val;
            $setting->save();
        }

        return redirect()->back()->with('success', 'Dynamic Updated');
    }

}
