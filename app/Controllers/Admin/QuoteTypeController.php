<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:10 PM
 */

namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;

use FK3\Models\QuoteType;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Vocalogic\VocalogicException;

class QuoteTypeController extends Controller
{
    /**
     * Show all quote types
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.quote_types.index');
    }

    /**
     * Create a new QuoteType
     * @return mixed
     */
    public function create()
    {
        return view('admin.quote_types.create')->with('quote_type', new QuoteType);
    }

    /**
     * Store a new QuoteType
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function store(Request $request)
    {
        if (!$request->name)
        {
            throw new VocalogicException("You must enter a quote type.");
        }
        (new QuoteType)->create($request->all());
        return $this->success("New Quote Type Created, Redirecting..", ['callback' => "redirect:/admin/quote_types"]);
    }

    /**
     * Show a quote type
     * @param QuoteType $quote_type
     * @return string
     */
    public function show(QuoteType $quote_type)
    {
        return view('admin.quote_types.create')->with('quote_type', $quote_type);
    }

    /**
     * Update the quote type
     * @param QuoteType $quote_type
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function update(QuoteType $quote_type, Request $request)
    {
        if ($request->contract) // we're just updating the contract, not the whole thing.
        {
            $quote_type->update(['contract' => $request->contract]);
            return $this->success("Contract Updated", ['callback' => "reload"]);
        }
        $checkboxes = [
            'cabinets',
            'countertops',
            'sinks',
            'appliances',
            'accessories',
            'hardware',
            'led',
            'tile',
            'addons',
            'responsibilities',
            'questionaire',
            'buildup'
        ];
        if ($request->acls)
        {
            $this->updateACLs($quote_type, $request);
            return $this->success("$quote_type->name Controls Updated, Redirecting..",
                ['callback' => "redirect:/admin/quote_types"]);

        }
        if (!$request->name)
        {
            throw new VocalogicException("You must select a quote type name.");
        }

        foreach ($checkboxes as $check)
        {
            if ($request->$check)
            {
                $request->merge([$check => true]);
            }
            else $request->merge([$check => 0]);
        }

        $quote_type->update($request->all());
        return $this->success("$quote_type->name updated, Redirecting..",
            ['callback' => "redirect:/admin/quote_types"]);
    }

    /**
     * Activate/Deactivate Quote Type
     * @param QuoteType $quoteType
     * @return array
     */
    public function destroy(QuoteType $quoteType)
    {
        $quoteType->update(['active' => !$quoteType->active]);
        $message = (!$quoteType->active) ? "Deactivated" : "Activated";
        //audit($this->auditPage, "$message $quote_type->name");
        return ['callback' => "redirect:/admin/quote_types"];
    }

}
