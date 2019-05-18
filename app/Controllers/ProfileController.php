<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 3/16/18
 * Time: 5:50 PM
 */

namespace FK3\Controllers;


use FK3\Exceptions\FrugalException;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\Lead;
use FK3\Models\Job;
use FK3\Models\Fft;
use FK3\Models\Note;
use FK3\Models\Task;
use FK3\Models\User;
use FK3\Models\LeadSource;
use FK3\vl\core\Formatter;
use FK3\Models\Traits\DataTrait;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Auth;

class ProfileController extends Controller
{
    use DataTrait;

    public function view($id, Request $request)
    {
        $customer = Customer::find($id);
        $leadSources = LeadSource::orderBy('name', 'asc')->get();
        $users = User::orderBy('name')->where('active', 1)->get();

        return view('profile.view', compact('customer', 'users', 'leadSources', 'request'));
    }

    public function getCustomerDetails(Request $request)
    {
        $customer_id = $request->customer_id;
        $customer = Customer::find($customer_id);
        $total = 0;

        $data = '<tr>\
                  <td><b>Customer Number:</b></td>\
                  <td>' . $customer->id . '</td>\
                </tr>\
                <tr>\
                  <td><b>Name:</b></td>\
                  <td><a href="#" onclick="ShowModalEditCustomerName(' . $customer_id . ')">' . $customer->name . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>Address:</b></td>\
                  <td><a href="#" onclick="ShowModalEditCustomerAddress(' . $customer_id . ')">' . $customer->address . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>City / State / Zip:</b></td>\
                  <td><a href="#" onclick="ShowModalEditCustomerCity(' . $customer_id . ')">' . $customer->city . '</a> / <a href="#" onclick="ShowModalEditCustomerState(' . $customer_id . ')">' . $customer->state . '</a> / <a href="#" onclick="ShowModalEditCustomerZip(' . $customer_id . ')">' . $customer->zip . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>Job Address:</b></td>\
                  <td><a href="#" onclick="ShowModalEditCustomerJobAddress(' . $customer_id . ')">' . ($customer->job_address ?  $customer->job_address : "Empty") . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>Job City / State / Zip:</b></td>\
                  <td><a href="#" onclick="ShowModalEditCustomerJobCity(' . $customer_id . ')">' . ($customer->job_city ? $customer->job_city : "Empty") . '</a> / <a href="#" onclick="ShowModalEditCustomerJobState(' . $customer_id . ')">' . ($customer->job_state ? $customer->job_state : "Empty") . '</a>';

        $data .= ' / <a href="#" onclick="ShowModalEditCustomerJobZip(' . $customer_id . ')">' . ($customer->job_zip ? $customer->job_zip : "Empty") . '</a></td>\
                </tr>\
                ';

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    /**
     * Get Customer Name
     * @return json
     */
    public function getCustomerName(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'name' => $customer->name
              ]
            );
        }
    }

    /**
     * Set Customer Name
     * @return json
     */
    public function setCustomerName(Request $request)
    {
        $name = $request->name;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the name.'
              ]
            );
        }
        $customer->name = $name;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Name set.'
          ]
        );
    }

    /**
     * Get Customer Address
     * @return json
     */
    public function getCustomerAddress(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'address' => $customer->address
              ]
            );
        }
    }

    /**
     * Set Customer Address
     * @return json
     */
    public function setCustomerAddress(Request $request)
    {
        $address = $request->address;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($address == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the address.'
              ]
            );
        }
        $customer->address = $address;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Address set.'
          ]
        );
    }

    /**
     * Get Customer City
     * @return json
     */
    public function getCustomerCity(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'city' => $customer->city
              ]
            );
        }
    }

    /**
     * Set Customer City
     * @return json
     */
    public function setCustomerCity(Request $request)
    {
        $city = $request->city;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($city == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the city.'
              ]
            );
        }
        $customer->city = $city;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer City set.'
          ]
        );
    }

    /**
     * Get Customer State
     * @return json
     */
    public function getCustomerState(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'state' => $customer->state
              ]
            );
        }
    }

    /**
     * Set Customer State
     * @return json
     */
    public function setCustomerState(Request $request)
    {
        $state = $request->state;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($state == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the state.'
              ]
            );
        }
        $customer->state = $state;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer State set.'
          ]
        );
    }

    /**
     * Get Customer Zip
     * @return json
     */
    public function getCustomerZip(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'zip' => $customer->zip
              ]
            );
        }
    }

    /**
     * Set Customer Zip
     * @return json
     */
    public function setCustomerZip(Request $request)
    {
        $zip = $request->zip;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($zip == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the zip.'
              ]
            );
        }
        $customer->zip = $zip;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Zip set.'
          ]
        );
    }

    /**
     * Get Customer JobAddress
     * @return json
     */
    public function getCustomerJobAddress(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'job_address' => $customer->job_address ? $customer->job_address : "Empty"
              ]
            );
        }
    }

    /**
     * Set Customer JobAddress
     * @return json
     */
    public function setCustomerJobAddress(Request $request)
    {
        $job_address = $request->job_address;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($job_address == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the job address.'
              ]
            );
        }
        $customer->job_address = $job_address;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Job Address set.'
          ]
        );
    }

    /**
     * Get Customer JobCity
     * @return json
     */
    public function getCustomerJobCity(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'job_city' => $customer->job_city ? $customer->job_city : "Empty"
              ]
            );
        }
    }

    /**
     * Set Customer JobCity
     * @return json
     */
    public function setCustomerJobCity(Request $request)
    {
        $job_city = $request->job_city;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($job_city == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the job city.'
              ]
            );
        }
        $customer->job_city = $job_city;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Job City set.'
          ]
        );
    }

    /**
     * Get Customer JobState
     * @return json
     */
    public function getCustomerJobState(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'job_state' => $customer->job_state ? $customer->job_state : "Empty"
              ]
            );
        }
    }

    /**
     * Set Customer JobState
     * @return json
     */
    public function setCustomerJobState(Request $request)
    {
        $job_state = $request->job_state;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($job_state == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the job state.'
              ]
            );
        }
        $customer->job_state = $job_state;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Job State set.'
          ]
        );
    }

    /**
     * Get Customer JobZip
     * @return json
     */
    public function getCustomerJobZip(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'job_zip' => $customer->job_zip ? $customer->job_zip : "Empty"
              ]
            );
        }
    }

    /**
     * Set Customer JobZip
     * @return json
     */
    public function setCustomerJobZip(Request $request)
    {
        $job_zip = $request->job_zip;

        $customer = Customer::find($request->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($job_zip == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the job zip.'
              ]
            );
        }
        $customer->job_zip = $job_zip;
        $customer->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Customer Job Zip set.'
          ]
        );
    }

    public function getCustomerContacts(Request $request)
    {
        $customer_id = $request->customer_id;
        $contact = Contact::leftJoin('customers', 'contacts.customer_id', '=', 'customers.id')
                            ->where('contacts.customer_id', $customer_id)
                            ->select(
                                      'contacts.*',
                                      'customers.email as cust_email',
                                      'customers.email2 as cust_email2',
                                      'customers.email3 as cust_email3'
                                    )
                            ->first();
        $total = 0;

        $data = '<tr>\
                  <td><b>Contact:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactName(' . $contact->id . ')">' . $contact->name . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>E-mail:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactEmail(' . $contact->id . ', 1)">' . ($contact->cust_email ?: '--No Email Set--') . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>E-mail 2:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactEmail(' . $contact->id . ', 2)">' . ($contact->cust_email2 ?: '--No Email Set--') . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>E-mail 3:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactEmail(' . $contact->id . ', 3)">' . ($contact->cust_email3 ?: '--No Email Set--') . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>Mobile:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactMobile(' . $contact->id . ')">' . Formatter::numberFormat($contact->mobile) . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>Home:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactHome(' . $contact->id . ')">' . Formatter::numberFormat($contact->home) . '</a></td>\
                </tr>\
                <tr>\
                  <td><b>Alternate:</b></td>\
                  <td><a href="#" onclick="ShowModalEditContactAlternate(' . $contact->id . ')">' . Formatter::numberFormat($contact->alternate) . '</a></td>\
                </tr>\
                ';

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    /**
     * Get Contact Name
     * @return json
     */
    public function getContactName(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'name' => $contact->name
              ]
            );
        }
    }

    /**
     * Set Contact Name
     * @return json
     */
    public function setContactName(Request $request)
    {
        $name = $request->name;

        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the name.'
              ]
            );
        }
        $contact->name = $name;
        $contact->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contact Name set.'
          ]
        );
    }

    /**
     * Get Contact Email
     * @return json
     */
    public function getContactEmail(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        $number = $request->number;
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            $customer = Customer::find($contact->customer_id);
            if($number == 1)
            {
                return Response::json(
                  [
                    'response' => 'success',
                    'email' => $contact->email
                  ]
                );
            }
            if($number == 2)
            {
                return Response::json(
                  [
                    'response' => 'success',
                    'email' => $customer->email2
                  ]
                );
            }
            if($number == 3)
            {
                return Response::json(
                  [
                    'response' => 'success',
                    'email' => $customer->email3
                  ]
                );
            }
        }
    }

    /**
     * Set Contact Email
     * @return json
     */
    public function setContactEmail(Request $request)
    {
        $email = $request->email;
        $number = $request->number;

        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($email == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the email.'
              ]
            );
        }

        $customer = Customer::find($contact->customer_id);
        if($number == 1)
        {
            $customer->email = $email;
            $contact->email = $email;
        }
        if($number == 2)
        {
            $customer->email2 = $email;
        }
        if($number == 3)
        {
            $customer->email3 = $email;
        }
        $customer->save();
        $contact->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contact Email set.'
          ]
        );
    }

    /**
     * Get Contact Mobile
     * @return json
     */
    public function getContactMobile(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'mobile' => $contact->mobile
              ]
            );
        }
    }

    /**
     * Set Contact Mobile
     * @return json
     */
    public function setContactMobile(Request $request)
    {
        $mobile = $request->mobile;

        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($mobile == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the mobile.'
              ]
            );
        }
        $contact->mobile = $mobile;
        $contact->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contact Mobile set.'
          ]
        );
    }

    /**
     * Get Contact Home
     * @return json
     */
    public function getContactHome(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'home' => $contact->home
              ]
            );
        }
    }

    /**
     * Set Contact Home
     * @return json
     */
    public function setContactHome(Request $request)
    {
        $home = $request->home;

        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($home == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the home.'
              ]
            );
        }
        $contact->home = $home;
        $contact->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contact Home set.'
          ]
        );
    }

    /**
     * Get Contact Alternate
     * @return json
     */
    public function getContactAlternate(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'alternate' => $contact->alternate
              ]
            );
        }
    }

    /**
     * Set Contact Alternate
     * @return json
     */
    public function setContactAlternate(Request $request)
    {
        $alternate = $request->alternate;

        $contact = Contact::find($request->contact_id);
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($alternate == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please enter the alternate.'
              ]
            );
        }
        $contact->alternate = $alternate;
        $contact->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contact Alternate set.'
          ]
        );
    }

    public function getCustomerLeads(Request $request)
    {
        $customer_id = $request->customer_id;
        $customer = Customer::find($customer_id);

        $data = '';
        foreach ($customer->leads AS $lead)
        {
            $data .= '
                        <tr>\
                          <td>' . $lead->created_at->diffInDays() . '</td>\
                          <td>' . (($lead->status) ? $lead->status->name : "New") . '</td>\
                          <td>' . (($lead->user) ? $lead->user->name : "No Designer") . '</td>\
                          <td><a href="#" onclick="ShowModalEditLeadSource(' . $lead->id . ');">' . ($lead->source ? $lead->source->name : null) . '</a></td>\
                        </tr>\
                     ';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    /**
     * Get Lead Source
     * @return json
     */
    public function getLeadSource(Request $request)
    {
        $lead = Lead::find($request->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            $source = LeadSource::find($lead->source_id);
            return Response::json(
              [
                'response' => 'success',
                'source_id' => ($source) ? $source->id : null
              ]
            );
        }
    }

    /**
     * Set Lead Source
     * @return json
     */
    public function setLeadSource(Request $request)
    {
        $source_id = $request->source_id;

        $lead = Lead::find($request->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        if($source_id == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please select source.'
              ]
            );
        }
        $lead->source_id = $source_id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Lead Source set.'
          ]
        );
    }

    public function getCustomerQuotes(Request $request)
    {
        $customer_id = $request->customer_id;
        $customer = Customer::find($customer_id);

        $data = '';
        foreach ($customer->quotes AS $quote)
        {
            $title = ($quote->title) ? $quote->title : $quote->id;
            $data .= '
                        <tr>\
                          <td><a href="' . route('quote_view', ['id' => $quote->id]) . '">' . $title . '</a></td>\
                          <td>' . $quote->type->name . '</td>\
                          <td>' . (($quote->final) ? "Final Quote" : "Initial Quote") . '</td>\
                          <td>' . (($quote->accepted) ? "Yes" : "No") . '</td>\
                        </tr>\
                     ';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    static public function changeOrders(Job $job)
    {
        if ($job->orders->count() == 0) return null;
        $data = null;
        foreach ($job->orders AS $order)
        {
            $data .= "<b>Date: " . $order->created_at->format("m/d/y") . "</b><br/><br/>";
            foreach ($order->items AS $item)
                $data .= $item->description . " - $" . number_format($item->price, 2) . "<br/>";
            $data .= "<hr/>";
        }
        return $data;
    }

    public function getCustomerJobs(Request $request)
    {
        $customer_id = $request->customer_id;
        $customer = Customer::find($customer_id);

        $data = '';
        foreach ($customer->quotes AS $quote)
        {
            if ($quote->job)
            {
                if (!$cData = self::changeOrders($quote->job))
                {
                    $pop = null;
                }
                else
                {
                  $pop = '<a href="' . route('job_schedules', ['id' => $quote->job->id]) . '" data-toggle="popover" title="Job Schedules" data-placement="left" data-html="true" data-trigger="hover" data-content="' . $cData . '" class="popovered">' . $quote->job->id . '</a>';

                  $data .= '\
                              <tr>\
                                <td>'  . $pop . '</td>\
                                <td></td>\
                                <td>' . Carbon::parse($quote->job->start_date)->format('m/d/y') . '</a></td>\
                                <td><a href="#" class="btn btn-primary" onclick="ShowModalJobNotes(' . $quote->job->id . ')">Show Notes</a></td>\
                                <td></td>\
                              </tr>\
                           ';
                }

                foreach ($quote->job->schedules AS $schedule)
                {
                    $data .= '\
                                <tr>\
                                  <td></td>\
                                  <td>' . (($schedule->user) ? $schedule->user->name : "Unassigned") . '</td>\
                                  <td>' . Carbon::parse($schedule->start)->format("m/d/y") . '</td>\
                                  <td>' . nl2br($schedule->notes) . '</td>\
                                  <td>' . nl2br($schedule->contractor_notes) . '</td>\
                                </tr>\
                             ';
                } // ea schedule
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function getCustomerFinalTouchWarranty(Request $request)
    {
        $warranty = $request->warranty;
        $customer_id = $request->customer_id;

        $customer = Customer::find($customer_id);

        $ffts = Fft::whereClosed(false)->whereWarranty($warranty)->get();
        $data = '';
        foreach ($ffts AS $fft)
        {
            if (@$fft->job->quote->lead->customer->id == $customer->id)
            {
                $preassign = ($fft->pre_assigned) ? $fft->preassigned->name : "Unassigned";
                $assigned = ($fft->assigned) ? $fft->assigned->name : "Unassigned";
                $preschedule = ($fft->pre_schedule_start != '0000-00-00 00:00:00') ?
                    Carbon::parse($fft->pre_schedule_start)->format("m/d/y h:i a") : "Unscheduled";
                $scheduled = ($fft->schedule_start != '0000-00-00 00:00:00') ?
                    Carbon::parse($fft->schedule_start)->format("m/d/y h:i a") : "Unscheduled";

                $data .= '\
                            <tr>\
                              <td>' . $preassign . '</td>\
                              <td>' . $preschedule . '</td>\
                              <td>' . $assigned . '</td>\
                              <td>' . $scheduled . '</td>\
                              <td>' . nl2br($fft->notes) . '</td>\
                            </tr>\
                         ';
            }
        }
        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function getCustomerNotes(Request $request)
    {
        $customer_id = $request->customer_id;

        $customer = Customer::find($customer_id);

        $data = '';
        foreach ($customer->notes AS $note)
        {
            $data .= '\
                        <tr>\
                          <td>' . $note->user->name . " ( " . $note->created_at->format('m/d/y') . " )" . '</td>\
                          <td>' . nl2br($note->note) . '</td>\
                        </tr>\
                     ';
        }
        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function saveCustomerNotes(Request $request)
    {
        $customer_id = $request->customer_id;
        $note = $request->note;

        $note = new Note();
        $note->customer_id = $customer_id;
        $note->note = $request->note;
        $note->user_id = Auth::user()->id;
        $note->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Note Added.'
          ]
        );
    }

    public function getCustomerTasks(Request $request)
    {
        $customer_id = $request->customer_id;

        $customer = Customer::find($customer_id);

        $data = '';
        $tasks = Task::whereClosed(false)->get();
        foreach ($tasks AS $task)
        {
            if (
                ($task->job && $task->job->quote->lead->customer->id == $customer->id) ||
                ($task->customer_id == $customer->id)
            )
            {
                /* $rows[] = ["<a href='/task/$task->id/view'>$task->subject</a>", ($task->assigned) ? $task->assigned->name : "Unassigned",
                    ($task->due != '0000-00-00') ? Carbon::parse($task->due)->format('m/d/y') : "No Due Date"]; */

                $data .= '\
                            <tr>\
                              <td>' . $task->subject . '</td>\
                              <td>' . (($task->assigned) ? $task->assigned->name : "Unassigned") . '</td>\
                              <td>' . (($task->due != '0000-00-00') ? Carbon::parse($task->due)->format('m/d/y') : "No Due Date") . '</td>\
                            </tr>\
                         ';
            }
        }
        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }
}
