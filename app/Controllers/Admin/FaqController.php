<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Faq;
use FK3\Models\QuoteType;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class FaqController extends Controller
{
    public $auditPage = "Faq";

    /*
     * Show authorization Index.
     */
    public function index()
    {
        $quoteTypes = QuoteType::all();
        return view('admin.faqs.index', compact('quoteTypes'));
    }

    /**
     * Display Faqs Data
     * @return Array
     */

    public function displayFaqs()
    {
        $items = Faq::all();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditFaq(' . $item->id . ');">' . $item->question . '</a>';
              $objItems[] = $item->quote_type->name;
              $objItems[] = '<img src="' . url('app') . '/' . $item->image . '" width="100px" height="100px"></img>';
              $objItems[] = '<a href="#" onclick="DeleteFaq(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Faq::count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function store(Request $request)
    {
        $question = $request->question;
        $answer = $request->answer;
        $figure = $request->figure;
        $quote_type_id = $request->quote_type_id;

        $faq = new Faq();
        $faq->question = $question;
        $faq->answer = $answer;
        $faq->figure = $figure;
        $faq->quote_type_id = $quote_type_id;

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('faqs');
            $faq->image = $filePath;
        }

        $faq->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Faq Added'
          ]
        );
    }

    public function getFaq(Request $request)
    {
        $faq = Faq::find($request->faq_id);

        return Response::json(
          [
            'response' => 'success',
            'question' => $faq->question,
            'answer' => $faq->answer,
            'image' => $faq->image,
            'figure' => $faq->figure,
            'quote_type_id' => $faq->quote_type_id,
            'faq_id' => $faq->id
          ]
        );
    }

    public function updateFaq(Request $request)
    {
        $question = $request->question;
        $answer = $request->answer;
        $figure = $request->figure;
        $quote_type_id = $request->quote_type_id;

        $faq = Faq::find($request->faq_id);
        $faq->question = $question;
        $faq->answer = $answer;
        $faq->figure = $figure;
        $faq->quote_type_id = $quote_type_id;

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('faqs');
            $faq->image = $filePath;
        }

        $faq->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Faq Updated'
          ]
        );
    }

    public function deleteFaq(Request $request)
    {
        $faq = Faq::find($request->faq_id);
        $faq->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Faq Deleted'
          ]
        );
    }
}
