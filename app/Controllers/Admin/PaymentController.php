<?php

namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class PaymentController extends Controller
{
    public $auditPage = "Payment";

    /*
     * Show payment Index.
     */
    public function index()
    {
        return view('admin.payments.index');
    }

    public function store(Request $request)
    {
        $details = $request->all();
        if (!isset($details['type']))
        {
            $details['type'] = 'C';
        }    // C = Credit, A = ACH
        $details['phone'] = '';
        $details['email'] = '';
        $name = $details['type'] == 'C' ? explode(" ", $request->cc_name) : explode(" ", $request->ach_name);
        $details['first'] = $name[0];
        $details['last'] = @$name[1];

        //$bill = vl\libraries\Bluepay::init("LIVE", "100155724209", "100155724210", "1HECUX9KVB/KUAMA6WRU/TKEUUH0UZE/");
        $bill = \FK3\vl\libraries\Bluepay::init("LIVE", "100215808240", "100215808241", "ZEINCIUSPDPWFQRZMYK8VYAF75LEYMVY");

        try
        {
            if ($details['type'] == 'C')
            {
                $result = $bill->setCustomer($details)
                               ->isSale()
                               ->setAmount($request->amount)
                               ->setCard($details['cc_number'], $details['cc_cvv'], $details['cc_exp'])
                               ->memo("Frugal Credit Card Payment")
                               ->create();

                switch ($details['cc_number'][0])
                {
                    case 3:
                        $sub = "American Express";
                        break;
                    case 4:
                        $sub = "Visa";
                        break;
                    case 5:
                        $sub = "Mastercard";
                        break;
                    default:
                        $sub = "Unknown";
                        break;
                }
                $four = substr($details['cc_number'], -4);
            }
            else
            {
                $result = $bill->setCustomer($details)
                               ->isSale()
                               ->setAmount($request->amount)
                               ->setACH($details['ach_route'], $details['ach_account'], $details['ach_type'])
                               ->memo("Frugal ACH Payment")
                               ->create();
                $four = substr($details['ach_account'], -4);
                $sub = $details['ach_type'] == 'C' ? 'Checking' : 'Savings';
            }
        } catch (Exception $e)
        {
            return redirect()->back()->with('error', 'Transaction Declined! ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Transaction Successful! Transacton ID: ' . $result->transId);
    }
}
