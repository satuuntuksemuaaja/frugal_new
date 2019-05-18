<?php namespace FK3\vl\quotes;

use FK3\Models\Accessory;
use Auth;
use BS;
use Button;
use Editable;
use FK3\Models\Extra;
use File;
use FK3\Models\Hardware;
use Panel;
use PDF;
use FK3\Models\Promotion;
use FK3\Models\Appliance;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\Cabinet;
use FK3\Models\QuoteQuestion;
use FK3\Models\QuoteQuestionAnswer;
use FK3\Models\QuoteQuestionCondition;
use FK3\Controllers\PurchaseController;
use FK3\Models\QuoteCabinet;
use FK3\Models\Setting;
use SimpleXMLElement;
use FK3\Models\Sink;
use Snapshot;
use Str;
use Table;
use View;

/**
 * @property  GTTL2
 * @property  GTTL2
 * @property  GTTL
 * @property  GTTL
 */
class QuoteGeneratorNew
{
    public $total     = 0;
    public $discounts = 0;

    public $color_info = '#5bc0de';
    public $color_success = '#6fd088';


    public function __construct(Quote $quote, $admin = true)
    {
        $this->quote = $quote;
        $meta = unserialize($quote->meta);
        $this->meta = (isset($meta['meta'])) ? $meta['meta'] : null;
        $this->settings = (isset($meta['settings'])) ? $meta['settings'] : [];
        $this->debug = [];
        $this->admin = $admin;
        $this->cabinetBuildup = 0;
        $this->granite2 = 0;
        $this->processedQuestions = [];
        $this->GTTL = 0;
        // Financial Values
        $this->cabinetPrice = 0;
        $this->cabinet2Price = 0;
        $this->tile = 0;

        // Quote Variables
        $this->forInstaller = 0;
        $this->forDesigner = 0;
        $this->forFrugal = 0;
        $this->forPlumber = 0;
        $this->forElectrician = 0;
        // Contractor Counts
        $this->appPlumber = 0;
        $this->appElectrician = 0;
        $this->appSecGroupName = [];
        $this->appSecGroupPrice = [];

        // Distributions
        $this->accFrugal = 0;
        $this->accInstaller = 0;

        // LED
        $this->electricianLED = 0;
        $this->frugalLED = 0;
        $this->designerLED = 0;

        $this->promotionalDiscount = 0;
        $this->discounts = 0;
    }

    static public function hasThisVendor($quote, $id)
    {
        foreach ($quote->cabinets AS $cabinet)
        {
            if ($cabinet->cabinet->vendor_id == $id)
            {
                return true;
            }
        }
        return false;
    }

    static public function getNotifications(Quote $quote, &$pass)
    {
        $data = null;
        $obj = self::getQuoteObject($quote);
        $meta = unserialize($quote->meta);
        if (!is_array($meta))
        {
            $meta = [];
        }
        if ($quote->accepted)
        {
            $data .= '<div class="card-body bg-success text-white">
                        <i class="fa fa-check"></i> <b>Quote Sold</b>
                        This quote has been sold and has been transferred to the job board.
                      </div>';
        }
        if (isset($obj->GTTL))
        {
            if ($quote->type && ($quote->type->name == 'Full Kitchen' && $obj->GTTL < 50))
            {
              $data .= '<br/><div class="card-body bg-warning text-white">
                          <i class="fa fa-check"></i> <b>Granite Square Footage</b>
                          The square footage for this job is ' . $obj->GTTL . ', which is below the 50 sqft threshold. Please verify your values are correct.
                        </div>';
            }

        }
        if ($quote->picking_slab == 'Yes' || $quote->picking_slab == 'Undecided')
        {
          $data .= '<br/><div class="card-body bg-danger text-white">
                      <i class="fa fa-times"></i> <b>Slab Notice</b>
                      Customer has stated that they will be either picking their own slab or are currently undecided. Inform customer that job cannot be scheduled until they decide or have picked their slab!
                    </div>';
        }

        if ($quote->type && $quote->type->name != 'Granite Only')
        {
            foreach ($quote->cabinets AS $cabinet)
            {

                $cab = ($cabinet->cabinet) ? $cabinet->cabinet->frugal_name : "Select Cabinet";
                if ($cab == 'Select Cabinet')
                {
                    $pass = false;
                }

                $color = ($cabinet->color) ? $cabinet->color : "No Color";
                $colorPulse = ($cabinet->cabinet && $cabinet->cabinet->vendor->colors && !$cabinet->color) ? "class='pulse-red'" : null;
                if ($cabinet->price == 0)
                {
                    $pass = false;
                }

                if ($colorPulse)
                {
                    $pass = false;
                }

                if ($color == 'nocolor')
                {
                    $pass = false;
                }

                if (!$pass)
                {
                  $data .= '<br/><div class="card-body bg-danger text-white">
                              <i class="fa fa-times"></i> <b>Cabinet Data is Missing</b>
                              You are either missing a color, a list price or a cabinet name. Go <a href="/quote/' . $quote->id . '/cabinets">back to cabinets and fix</a>
                            </div>';
                }
            }
        }

        if ($quote->final && !isset($meta['meta']['finance']['type']))
        {
            $pass = false;
            $data .= '<br/><div class="card-body bg-danger text-white">
                        <i class="fa fa-times"></i> <b>Financing Missing</b>
                        You cannot download a contract until financing is selected.
                      </div>';
        }

        if (!$quote->lead->showroom && $quote->lead->status_id != 28)
        {
          $data .= '<br/><div class="card-body bg-danger text-white">
                      <i class="fa fa-times"></i> <b>No Showroom Scheduled or Walkin Set</b>
                      You must enter a showroom scheduled date or the lead for this quote must be set to \'Walk-in Need to Setup Appointment
                    </div>';
        }
        if (!$quote->paperwork)
        {
            $data .= '<br/><div class="card-body bg-danger text-white">
                        <i class="fa fa-times"></i> <b>Contract Unavailable</b>
                        Contract cannot be generated until customer has paperwork.
                      </div>';
            $buttons = '<a href="/quote/' . $quote->id . '/paperwork" class="btn btn-success"><i class="fa fa-share"></i> Customer Has Paperwork</a>';
            if ($quote->lead->status_id != 11)
            {
                $buttons .= '&nbsp;<a href="/quote/' . $quote->id . '/needspaperwork" class="btn btn-warning"><i class="fa fa-thumbs-down"></i> Customer Needs Paperwork</a>';
            }
            $data .= '<br/><div class="card-body bg-danger text-white">
                        <h4><i class="fa fa-times-circle-o"></i> <b>Does customer have paperwork?</b></h4>
                        Once you have given the contract to the customer you must click the appropriate button below.<br/><br/>' . $buttons . '
                      </div>';
        }
        return $data;

    }

    static public function getQuoteObject(Quote $quote)
    {
        $details = new self($quote);
        $detailPanel = $details->getQuoteDetails();
        $cabinets = $details->getCabinets();
        $granite = $details->getGranite();
        $appliances = $details->getAppliances();
        $additional = $details->getAdditionalInfo();
        $questions = $details->getQuestions();
        $addons = $details->getAddons();
        $payouts = $details->getPayouts();
        $quote->for_designer = $details->forDesigner;
        $quote->save();
        return $details;
    }

    public function getEdgeCost($edge)
    {
        switch ($edge)
        {
            case '(Premium) Half Bull Nose ($8/lnft.)':
                return 8.00;
                break;
            case '(Premium) Half Bevel ($8/lnft.)':
                return 8.00;
                break;
            case '(Premium) Full Bull Nose ($12/lnft.)':
                return 12.00;
                break;
            case '(Premium) 2cm Ogee ($14/lnft.)':
                return 14.00;
                break;
            case '(Premium) French Ogee ($20/lnft.)':
                return 20.00;
                break;
            case '(Premium) Dupont ($24/lnft.)':
                return 24.00;
                break;
            case '(Premium) Demi Bullnose ($5/lnft.)':
                return 5.00;
                break;
            default:
                return 0;
                break;
        }
    }

    /**
     * Return a panel showing the cabinet quote
     *
     * @return [type] [description]
     */

    public function getQuoteDetails()
    {
        $types = [
            ['value' => 'Full Kitchen', 'text' => 'Full Kitchen'],
            ['value' => 'Cabinet Only', 'text' => 'Cabinet Only'],
            ['value' => 'Cabinet Small Job', 'text' => 'Cabinet Small Job'],
            ['value' => 'Cabinet and Install', 'text' => 'Cabinet and Install'],
            ['value' => 'Granite Only', 'text' => 'Granite Only'],
        ];
        if (Auth::user()->id == 5 || Auth::user()->id == 1)
        {
            $types[] = ['value' => 'Builder', 'text' => 'Builder'];
        }
        /* $type = Editable::init()->id("quote_type")->placement('right')->type('select')->title("Quote Type")
            ->linkText($this->quote->type)
            ->source($types)->url("/quote/{$this->quote->id}/type/liveupdate")->render(); */

        $type = $this->quote->type ? '<a href="#" id="quote_type" onclick="ShowModalEditQuoteType(' . $this->quote->id . ')">' . $this->quote->type->name . '</a>' : '<a href="#" id="quote_type" onclick="ShowModalEditQuoteType(' . $this->quote->id . ')">--no quote type selected yet--</a>';

        if (!$this->quote->title)
        {
            $this->quote->title = "Main Quote";
        }
        /* $title = Editable::init()->id("quote_title")->placement('right')->type('text')->title("Title")
            ->linkText($this->quote->title)
            ->url("/quote/{$this->quote->id}/title/liveupdate")->render(); */

        $title = '<a href="#" id="quote_title" onclick="ShowModalEditQuoteTitle(' . $this->quote->id . ')">' . $this->quote->title . '</a>';

        /* $markup = Editable::init()->id("quote_markup")->placement('right')->type('text')->title("Markup Percentage")
            ->linkText($this->quote->markup)
            ->url("/quote/{$this->quote->id}/markup/liveupdate")->render(); */

        $markup = '<a href="#" id="quote_markup" onclick="ShowModalEditQuoteMarkup(' . $this->quote->id . ')">' . $this->quote->markup . '</a>';

        $rows = [];
        $rows[] = ['Quote Type:', $type];
        $rows[] = ['Title (Description)', $title];
        if ($this->quote->type && $this->quote->type->name == 'Full Kitchen')
        {
            $rows[] = ['Customer Picking Slab:', $this->quote->picking_slab];
        }
        if ($this->quote->type && $this->quote->type->name == 'Builder')
        {
            $rows[] = ['Frugal Markup Percentage:', $markup];
        }
        if (isset($this->meta['finance']))
        {
            switch ($this->meta['finance']['type'])
            {
                case 'all':
                    $type = "100% Financing Option for " . $this->meta['finance']['terms'] . " months";
                    break;
                case 'partial':

                    $type = "Partial financing Option putting $" . $this->meta['finance']['downpayment'] . " down
        with ";
                    if (isset($this->meta['finance']['down_cash']) && $this->meta['finance']['down_cash'] > 0)
                    {
                        $type .= '$' . $this->meta['finance']['down_cash'] . " in cash, ";
                    }
                    if (isset($this->meta['finance']['down_credit']) && $this->meta['finance']['down_credit'] > 0)
                    {
                        $type .= '$' . $this->meta['finance']['down_credit'] . " in cash, ";
                    }
                    $type = substr($type, 0, -2);
                    $type .= " and financing for " . $this->meta['finance']['terms'] . " months";
                    break;
                case 'none':
                    $type = "No financing paying ";
                    if (isset($this->meta['finance']['method']) && $this->meta['finance']['method'] == 'split')
                    {
                        if (isset($this->meta['finance']['no_cash']) && $this->meta['finance']['no_cash'] > 0)
                        {
                            $type .= "$" . number_format($this->meta['finance']['no_cash'], 2) . " in cash, ";
                        }
                        if (isset($this->meta['finance']['no_credit']) && $this->meta['finance']['no_credit'] > 0)
                        {
                            $type .= "$" . number_format($this->meta['finance']['no_credit'], 2) . " in credit, ";
                        }
                        $type = substr($type, 0, -2);
                    }
                    else
                    {
                        $type = "No finance using " . $this->meta['finance']['method'] . " for payment";
                    }
                    break;
                default:
                    $type = "Financing Option Needed";
            }
            $rows[] = ['Financing Options', $type];
        }

        /* $table = Table::init()->rows($rows)->render();
        $panel = Panel::init('primary')
            ->header("Quote Details <small style='color:#fff'>Quote Type and Cabinet Information</small>")
            ->content($table)->render();
        return $panel; */
        return $rows;
    }

    public function setDebug($item, $amount)
    {
        $this->debug[] = [
            $item,
            @number_format($amount, 2),
            @number_format($this->total, 2)
        ];
    }

    public function getCabinets()
    {
        $cabinetPrice = 0;
        $headers = ['Item', 'Details', 'XML', 'Price'];
        if ($this->admin)
        {
            array_push($headers, 'Total');
        }
        else
        {
            array_push($headers, 'Additional');
        }
        $rows = [];
        foreach ($this->quote->cabinets AS $cabinet)
        {
            $add = ($cabinet->customer_removed) ? "<br/><small>** Cabinet being removed by customer! ** </small>" : null;

            if ($cabinet->cabinet)
            {
                $rows[] = [
                    'Cabinet Type',
                    "<a href='/quote/{$this->quote->id}/cabinets'>{$cabinet->cabinet->frugal_name}</a> {$add}",
                    '<a href="#" onclick="ShowModalCabinetXml(' . $this->quote->id . ', ' . $cabinet->id . ');"><i class="fa fa-arrow-up"></i></a>',
                    null,
                    null
                ];
            }
            $rows[] = [
                'Description',
                "<a href='/quote/{$this->quote->id}/cabinets'>$cabinet->description</a>",
                null,
                null,
                null
            ];

            $rows[] = [
                'Cabinet Color',
                "<a href='/quote/{$this->quote->id}/cabinets'>$cabinet->color</a>",
                null,
                null,
                null
            ];
            $vendor = $cabinet->cabinet && $cabinet->cabinet->vendor ? $cabinet->cabinet->vendor->name : "Unknown Vendor";
            $rows[] = [
                'Cabinet Vendor',
                "<a href='/quote/{$this->quote->id}/cabinets'>$vendor</a>",
                null,
                null,
                null
            ];

            if ($cabinet->cabinet && $cabinet->cabinet->vendor)
            {
                $baseCabinet = $cabinet->price;
                $baseCabinet += $this->getTotalAmountFromWoodItems();
                $cabinetPrice = $baseCabinet * $cabinet->cabinet->vendor->multiplier;
            }
            else
            {
                $cabinetPrice = $cabinet->price;
                $baseCabinet = $cabinetPrice;
                $cabinetPrice += $this->getTotalAmountFromWoodItems();
            }
            $baseCabForFreight = $cabinetPrice;
            $tax = $cabinetPrice * .07;
            $cabinetPrice += $tax;
            $this->total += $cabinetPrice;
            $rows[] = [
                'Cabinet List Price',
                "$" . number_format($baseCabinet, 2),
                ($this->admin) ? "$" . number_format($cabinetPrice, 2) : null,
                null,
                ($this->admin) ? "$" . number_format($this->total, 2) : null
            ];
            $freight = (isset($cabinet->cabinet->vendor)) ? $baseCabForFreight * $cabinet->cabinet->vendor->freight / 100 : 0;
            if ($freight)
            {
                $this->setDebug("Freight for {$cabinet->cabinet->frugal_name}", $freight);
            }
            $this->total += $freight;
            if ($cabinet->cabinet)
            {
                $this->setDebug($cabinet->cabinet->name . " (" . $cabinet->cabinet->frugal_name . ")", $cabinetPrice);
            }

            $rows[] = [
                'Cabinet Freight',
                "$" . number_format($freight, 2),
                null,
                ($this->admin) ? "$" . number_format($freight, 2) : null,
                ($this->admin) ? "$" . number_format($this->total, 2) : null
            ];
            if (!$cabinet->are_we_removing_cabinets)
            {
                $this->total += 500;
                $rows[] = [
                    'Cabinet Removal',
                    "$" . number_format(500, 2),
                    null,
                    ($this->admin) ? "$" . number_format(500, 2) : null,
                    ($this->admin) ? "$" . number_format($this->total, 2) : null
                ];
            }
        }

        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel = Panel::init('primary')
            ->header("<a href='/quote/{$this->quote->id}/cabinets'>Cabinet Order(s) (click to edit)</a>")
            ->content($table)->render();
        return $panel; */
        return $rows;
    }

    public function getGraniteHeader()
    {
        $headers = ['Location', 'Item', 'Details', 'Price'];
        if ($this->admin)
        {
            array_push($headers, 'Total');
        }
        else
        {
            array_push($headers, 'Additional');
        }
        return $headers;
    }

    public function getGranite($report = null)
    {
        $rows = [];
        // Master Loop for Granite Items
        foreach ($this->quote->granites AS $g)
        {
            if ($g->granite_jo)
            {
                $jo = " <b>** OVERRIDE TO: $g->granite_jo **</b> ";
            }
            else $jo = null;
            $rows[] = [
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$g->description</a>",
                null,
                $jo,
                null,
                null
            ];
            // Measurements

            $measures = explode("\n", $g->measurements);
            $Cttl = 0;
            foreach ($measures AS $measure)
            {
                if (is_numeric($measure))
                {
                    $Cttl += $measure;
                }
            }
            $Csq = ceil(($Cttl * 25.5) / 144);
            if (!$Csq)
            {
                $counter_measurement = "Edit Quote";
            }
            else
            {
                $counter_measurement = implode(", ", $measures) . " = ($Csq sq.ft)";
            }
            $rows[] = [
                null,
                'Counter Measurements',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$counter_measurement</a>",
                null,
                null
            ];

            // Edge and Edge Cost

            $ecost = $this->getEdgeCost($g->counter_edge);
            if ($this->quote->type && $this->quote->type->name == 'Granite Only')
            {
                $edgeDifference = $ecost * .25;
                $ecost += $edgeDifference; // Granite Only Alteration
            }
            $edgeCost = $ecost * $g->counter_edge_ft;
            $this->total += $edgeCost;
            $this->setDebug("[$g->description] Edge Cost", $edgeCost);
            $quote_edge = $g->counter_edge ? $g->counter_edge : 'Edit Quote';
            $rows[] = [
                null,
                'Counter Edge Type',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$quote_edge</a>",
                ($this->admin) ? "$" . number_format($edgeCost, 2) . '</a>' : null,
                ($this->admin) ? "$" . number_format($this->total, 2) : null
            ];


            //Backsplash and stuff
            $backsplash_h = $g->backsplash_height ?: 'Update Backsplash Height';
            $rows[] = [
                null,
                'Backsplash Height',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$backsplash_h in.</a>",
                null,
                null
            ];
            $raised_l = $g->raised_bar_length ?: "Update Raised Bar Length";
            $rows[] = [
                null,
                'Raised Bar Length',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$raised_l in.</a>",
                null,
                null
            ];
            $raised_d = $g->raised_bar_depth ?: "Update Raised Bar Depth";
            $rows[] = [
                null,
                'Raised Bar Depth',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$raised_d in.</a>",
                null,
                null
            ];
            $island_w = $g->island_width ?: "Update Island Granite Width";
            $rows[] = [
                null,
                'Island Granite Width',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$island_w in.</a>",
                null,
                null
            ];
            $island_l = $g->island_length ?: "Update Island Granite Length";
            $rows[] = [
                null,
                'Island Granite Length',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$island_l in.</a>",
                null,
                null
            ];
            $edge_ft = $g->counter_edge_ft ?: "Add Feet of Premium Granite";
            $rows[] = [
                null,
                'Feet of Premium Granite (<b>edge</b>)',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>$edge_ft</a>",
                ($this->admin) ? "$" . number_format($edgeCost, 2) : null,
                ($this->admin) ? "$" . number_format($this->total, 2) : null,
            ];

            //Removal
            if ($g->removal_type)
            {
                switch ($g->removal_type)
                {
                    case 'Corian':
                        $cRemoval = 100;
                        break;
                    case 'Granite':
                        $cRemoval = 150;
                        break;
                    case 'Tile':
                        $cRemoval = 250;
                        break;
                    default:
                        $cRemoval = 0;
                }
                $this->total += $cRemoval;
                $this->setDebug("[$g->description] Countertop Removal", $cRemoval);
                $rows[] = [
                    null,
                    'Countertop Removal',
                    "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>" . $g->removal_type . "</a>",
                    "$" . number_format($cRemoval, 2),
                    ($this->admin) ? "$" . number_format($this->total, 2) : null
                ];
            } // if removal type

            //Granite Calculations
            // Get countertop square footage
            $GTTL = $Csq;
            /*Length x Width / 144 -> Length X 7 / 144 -> add those together to get the sq.ft of the raised bar*/
            $rbar = ($g->raised_bar_length && $g->raised_bar_depth) ? ($g->raised_bar_length * $g->raised_bar_depth) / 144 : 0;
            $rbar = ceil($rbar);
            $rbarL = ($g->raised_bar_length * 7) / 144;
            $rbarL = ceil($rbarL);
            $rbar += $rbarL;
            $GTTL += $rbar;
            $island = ceil(($g->island_length * $g->island_width) / 144);
            if ($island)
            {
                $rows[] = [
                    null,
                    'Island Configuration',
                    "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>" . $g->island_length . " x " . $g->island_width . " = " . $island . " sq.ft" . "</a>",
                    null,
                    null
                ];
            }
            $GTTL += $island;
            $gPrice = 0;
            // Take backsplash height x (total in. of countertop measurements) / 144 .. sq.ft of backsplash
            // 71 (for tom's)
            $bslash = ceil(($g->backsplash_height * $Cttl) / 144);
            $GTTL += $bslash;
            $this->GTTL += $GTTL;
            // Do secondary Granite now and Add to total
            $granite_name = $g->granite && !$g->granite_override ? $g->granite->name : $g->granite_override;
            $granite_ppsqft = 0;

            if ($g->granite_override)
            {
                $granite_ppsqft = $g->pp_sqft;
            }
            elseif ($g->granite)
            {
                $granite_ppsqft = $g->granite->price;
            }
            $message = null;
            if ($this->quote->type && $this->quote->type->name == "Full Kitchen" && !preg_match("/no promo|nopromo/i", $g->description))
            {
                $newAmt = $this->checkPromo($this->quote->promotion, $granite_ppsqft);
                \Log::info("new is $newAmt original was $granite_ppsqft");

                $this->promotionalDiscount = $newAmt;
                if ($newAmt != $granite_ppsqft)
                {
                    $message = "<br/><small class='text-info'>Promotion discounted granite to $newAmt/sqft</small>";
                    $diff = $granite_ppsqft - $newAmt;
                    $this->setDebug("A promotion was was added to Granite (discount $diff)", $newAmt);
                } // Alex - 678.469.7335
                $granite_ppsqft = $newAmt;
            }

            // Now figure price.
            $gPrice = $GTTL * $granite_ppsqft;


            $this->granite_ppsqft = $granite_ppsqft;


            if ($this->quote->type && $this->quote->type->name == 'Granite Only') // Granite Only Alteration
            {
                $ogPrice = $gPrice; // Store the original granite price for comparison.
                $gPriceDifference = $gPrice * .25;
                $gPrice += $gPriceDifference;
                $granite_ppsqft = $granite_ppsqft + ($granite_ppsqft * .25);
                // $forDesigner += $gPrice * .10; // Removed from issue #29
                // ogPrice is original and gPrice is what customer is being charged.
                $diff = $gPrice - $ogPrice; // say 2000 - 1500 (500)
                $perc = $diff * .3; // Difference * .3 (30% of Difference)
                $this->forDesigner += number_format($perc, 2); // Designer gets added 30% of difference value
                $this->setDebug("[$g->description] ** Granite Only ** Designer was given 30%", $perc);
            }
            $gTax = $gPrice * .07;
            $gPrice += $gTax;
            $this->total += $gPrice;
            $this->setDebug("[$g->description] Granite was figured at $GTTL sq.ft", $gPrice);
            $granite_name = ($granite_name) ? $granite_name : 'Select Granite';
            $rows[] = [
                null,
                'Granite Type',
                "<a href='/quote/{$this->quote->id}/granite?granite_id=$g->id'>" . $granite_name . "</a>{$message}",
                "$" . number_format($gPrice, 2),
                ($this->admin) ? "$" . number_format($this->total, 2) : null
            ];
        } // end master loop

        $rows[] = ['Total Granite SQFT', $this->GTTL, null, null, null];
        /* $table = Table::init()->headers($headers)->rows($rows)->render(); */
        if ($this->quote->type && $this->quote->type->name == 'Builder') return; // No Granite options on builder.
        /* $panel = Panel::init('default')->header("Granite Options <small style='color:#fff'>
          Granite, Island, and Countertops</small>")->content($table)->render(); */

        if($report) return $this->GTTL;

        return $rows;
      }

      public function getTileHeader()
      {
          $headers = ['Description', 'Counter', 'BS', 'Pattern', 'Sealed', 'Price', 'TTL'];
          return $headers;
      }

      public function getTile()
      {
        // Tile Panel
        $rows = array();
        foreach ($this->quote->tiles as $tile)
        {
            $in = $tile->linear_feet_counter * 12;
            $calc1 = ($in * $tile->backsplash_height) / 144;
            $tileTally = 0;
            if ($calc1 < 24)
            {
                $tileTally = 500;
            } // Under 24 sqft
            else
            {
                $tileTally = 600;
            } // Anything above.
            if ($tile->pattern == 'Pattern')
            {
                $tileTally = $tileTally + 100;
            } // Add $100 if a pattern.
            if ($tile->sealed == 'Yes')
            {
                $tileTally = $tileTally + 100;
            } // Add $100 for sealed tile.

            // Markup
            $tileTally = $tileTally + ($tileTally * .20); // 100 = 100 + (100 * .2)
            $this->total += $tileTally;
            $this->setDebug("[Tile] $tile->description was added", $tileTally);

            $rows[] = [
                "<a href='/quote/{$this->quote->id}/led?tile=$tile->id'>$tile->description</a>",
                $tile->linear_feet_counter,
                $tile->backsplash_height,
                $tile->pattern,
                $tile->sealed,
                "$" . number_format($tileTally, 2),
                "$" . number_format($this->total, 2),
            ];
        }
        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel .= Panel::init('primary')->header("Tile Configurations <small style='color:#fff'>
          <a href='/quote/{$this->quote->id}/led'>Add Multiple Tiles</a></small>")->content($table)->render(); */
        return $rows;
    }

    public function getAppliancesHeader()
    {
        $headers = ['Item', 'Details', 'Price'];
        if ($this->admin)
        {
            array_push($headers, 'Total');
        }
        else
        {
            array_push($headers, 'Additional');
        }
        return $headers;
    }

    public function getAppliances()
    {
        $rows = [];
        if (isset($this->quote->type->name) && ($this->quote->type->name != 'Cabinet Only' && $this->quote->type->name != 'Cabinet and Install'))
        {
            $appids = (isset($this->meta['quote_appliances'])) ? $this->meta['quote_appliances'] : [];
            $field = null;
            $appTTL = 0;
            $appCost = 0;
            if ($appids)
            {
                foreach ($appids AS $app)
                {
                    $appliance = Appliance::find($app);
                    $counts = ($appliance->count_as) ?: 1;
                    if ($appliance->price)
                    {
                        $appCost += $appliance->price;

                        if ($appliance->group_id == 2)
                        {
                            $this->appElectrician += ($appliance->price * $appliance->percentage);
                            $this->setDebug("Electrician was given $appliance->name", $appliance->price);
                        }
                        else if ($appliance->group_id == 1)
                        {
                            $this->appPlumber += ($appliance->price * $appliance->percentage);
                            $this->setDebug("Plumber was given $appliance->name", $appliance->price);
                        }
                        else if($appliance->group && $appliance->group_id != 0 && $appliance->group_id > 2)
                        {
                            $this->appFirstGroupName[] = $appliance->group->name;
                            $this->appFirstGroupPrice[] = ($appliance->price * ($appliance->percentage / 100));
                            $this->setDebug($this->appFirstGroupName[count($this->appFirstGroupName) - 1] . " was given " . $appliance->name, $this->appFirstGroupPrice[count($this->appFirstGroupPrice) - 1]);
                        }

                        //Second group
                        if($appliance->split_group)
                        {
                            $this->appSecGroupName[] = $appliance->split_group->name;
                            $this->appSecGroupPrice[] = ($appliance->price * ($appliance->second_group_percentage / 100));
                            $this->setDebug($this->appSecGroupName[count($this->appSecGroupName) - 1] . " was given " . $appliance->name, $this->appSecGroupPrice[count($this->appSecGroupPrice) - 1]);
                        }
                    }
                    $appTTL += $counts;
                    $field .= $appliance->name . ", ";
                }
            }
            // Determine total based on count.
            $this->setDebug("Appliance Base Cost (Plumber)", $this->getSetting('fPlumber'));
            $this->setDebug("Appliance Base Cost (Electrician)", $this->getSetting('fElectrician'));
            if ($appTTL > 5)
            {
                $extra = $appTTL - 5;
                //$appCost = $appCost + ($extra * 75);
                $this->appPlumber += round(($extra * 75) / 2);
                $this->appElectrician += round(($extra * 75) / 2);
                $this->setDebug("Appliance count was more than 5 ($appTTL) (Plumber)", round(($extra * 75) / 2));
                $this->setDebug("Appliance count was more than 5 ($appTTL) (Electrician)", round(($extra * 75) / 2));
            }
            $field = ($field) ? substr($field, 0, -2) : "Add Appliances";
            $rows[] = [
                'Appliances',
                "<a href='/quote/{$this->quote->id}/appliances'>" . $field . "</a>",
                ($this->admin) ? "$" . number_format($appCost, 2) : null,
                ($this->admin) ? "$" . number_format($this->total, 2) : null
            ];
        } // only if full kitchen show appliances.

        // --------------- Accessories
        $accids = (isset($this->meta['quote_accessories'])) ? $this->meta['quote_accessories'] : [];
        $field = null;
        $accessoriesCost = 0;
        foreach ($accids AS $acc => $qty)
        {
            $acccost = 0;
            $accessory = Accessory::find($acc);
            $mult = $accessory->vendor->multiplier;
            $field .= "<a class='tooltiped' href='/quote/{$this->quote->id}/accessories' data-placement='top' class='tip' data-toggle='tooltip' title=\"" . str_replace('"',
                    "'", $accessory->name) . "\">" . $accessory->sku . "</a> x $qty, ";
            if ($mult == 0)
            {
                $acccost = $accessory->price * $qty;
            }
            else
            {
                $acccost = ($accessory->price * $mult) * $qty;
            }
            $tax = $acccost * .07;
            $acccost += $tax;
            $accessoriesCost += $acccost;
            if ($accessory->on_site)
            {
                $this->accFrugal += (20 * $qty);
                $this->setDebug("<b>Frugal</b> 20 * ($accessory->sku - $accessory->name) for on site Installation",
                    20 * $qty);
            }
            else
            {
                $this->accInstaller += (20 * $qty);
                $this->setDebug("<b>Installer</b> 20 * ($accessory->sku - $accessory->name)", 20 * $qty);
            }
        } // fe
        $this->total += $accessoriesCost;
        $this->setDebug("Accessory Factor to Total", $accessoriesCost);
        $field = ($field) ? substr($field, 0,
            -2) : "<a href='/quote/{$this->quote->id}/accessories'>Add Accessories</a>";
        $rows[] = [
            'Accessory List',
            $field,
            ($this->admin) ? "$" . number_format($accessoriesCost, 2) : null,
            ($this->admin) ? "$" . number_format($this->total, 2) : null
        ];

        // Hardware
        //

        $hwids = (isset($this->meta['quote_pulls'])) ? $this->meta['quote_pulls'] : [];
        $field = null;
        $hwttl = 0;
        $hwmax = 3.00;
        foreach ($hwids AS $hw => $qty)
        {
            if (preg_match('/\:/', $qty))
            {
                $x = explode(":", $qty);
                $qty = $x[0];
                $location = " (" . $x[1] . ")";
            }
            else $location = null;
            $hardware = Hardware::find($hw);
            if ($hardware && $hardware->price > $hwmax)
            {
                $sub = $hardware->price - $hwmax;
                $hwttl += ($sub * (integer)$qty);
            }
            if ($hardware)
            {
                $field .= "<a class='tooltiped' href='/quote/{$this->quote->id}/hardware' data-placement='top' class='tip'
      data-toggle='tooltip' title=\"" . str_replace('"', "'",
                        $hardware->description) . "\">" . $hardware->sku . "</a> x $qty $location, ";
            }
        }

        $field = ($field) ? substr($field, 0, -2) : "<a href='/quote/{$this->quote->id}/hardware'>Add Pulls</a>";
        $rows[] = ['Hardware List (Pulls)', $field, null, null];

        // Knobs
        $hwids = (isset($this->meta['quote_knobs'])) ? $this->meta['quote_knobs'] : [];
        $field = null;
        foreach ($hwids AS $hw => $qty)
        {
            if (preg_match('/\:/', $qty))
            {
                $x = explode(":", $qty);
                $qty = $x[0];
                $location = " (" . $x[1] . ")";
            }
            else $location = null;
            $hardware = Hardware::find($hw);
            if ($hardware && $hardware->price > $hwmax)
            {
                $sub = $hardware->price - $hwmax;
                $hwttl += ($sub * $qty);
            }
            if ($hardware)
            {
                $field .= "<a class='tooltiped' href='/quote/{$this->quote->id}/hardware' data-placement='top' class='tip'
      data-toggle='tooltip' title=\"" . str_replace('"', "'",
                        $hardware->description) . "\">" . $hardware->sku . "</a> x $qty $location, ";
            }
        }
        $field = ($field) ? substr($field, 0, -2) : "<a href='/quote/{$this->quote->id}/hardware'>Add Knobs</a>";
        $rows[] = ['Hardware List (Knobs)', $field, null, null];
        if ($hwttl > 0)
        {
            $this->total += $hwttl;
            $this->setDebug("Additional Hardware Costs", $hwttl);
            $rows[] = [
                'Additional Hardware Costs',
                "$" . number_format($hwttl, 2),
                ($this->admin) ? "$" . number_format($hwttl, 2) : null,
                ($this->admin) ? "$" . number_format($this->total, 2) : null
            ];
        }
        // Step 9 --Count Pulls/Knobs
        $knobttl = 0;
        $knobs = (isset($this->meta['quote_knobs'])) ? $this->meta['quote_knobs'] : [];
        foreach ($knobs AS $key => $val)
        {
            if (preg_match('/\:/', $val))
            {
                $x = explode(":", $val);
                $val = $x[0];
                $location = $x[1];
            }
            $knobttl += $val;
        }

        $pullttl = 0;
        $pulls = (isset($this->meta['quote_pulls'])) ? $this->meta['quote_pulls'] : [];
        foreach ($pulls AS $key => $val)
        {
            if (preg_match('/\:/', $val))
            {
                $x = explode(":", $val);
                $val = $x[0];
                $location = $x[1];
            }
            $pullttl += (integer)$val;
        }

        $rows[] = ['Number of Pulls', "<a href='/quote/{$this->quote->id}/hardware'>$pullttl</a>", null, null];
        $rows[] = ['Number of Knobs', "<a href='/quote/{$this->quote->id}/hardware'>$knobttl</a>", null, null];
        $sinkTTL = 0;
        if (isset($this->meta['sinks']))
        {
            foreach ($this->meta['sinks'] AS $sink)
            {
                if (!$sink)
                {
                    continue;
                }

                $sink = Sink::find($sink);
                $this->total += $sink->price;
                $this->setDebug("(Sink) $sink->name Added", $sink->price);
                $sink_type = ($sink) ? $sink->name : 'Edit Sink';
                $rows[] = [
                    'Sink Type',
                    "<a href='/quote/{$this->quote->id}/appliances'>" . $sink_type . "</a>",
                    "$" . number_format($sink->price, 2),
                    ($this->admin) ? "$" . number_format($this->total, 2) : null
                ];
                // #178 - If sink needs a plumber from a small job add it here.
                if (!isset($this->meta['sink_plumber']))
                {
                    $this->meta['sink_plumber'] = [];
                }
                if (in_array($sink->id, $this->meta['sink_plumber']))
                {
                    $sinkTTL++;
                    $amt = $sinkTTL > 1 ? 125 : 350;
                    $this->total += $amt;
                    $rows[] = [
                        'Plumber Required (Sink ' . $sinkTTL . ' )',
                        "Plumber Required",
                        "$" . number_format($amt, 2),
                        ($this->admin) ? "$" . number_format($this->total, 2) : null
                    ];
                }
            } // fe sinks
        }
        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel = Panel::init('default')->header("Sinks, Appliances, Accessories and Hardware")->content($table)
            ->render();
        return $panel; */

        return $rows;
    }

    public function getAdditionalInfoHeader()
    {
        $headers = ['Item', 'Details', 'Price'];
        if ($this->admin)
        {
            array_push($headers, 'Total');
        }
        else
        {
            array_push($headers, 'Additional');
        }

        return $headers;
    }

    public function getAdditionalInfo()
    {
        $rows = [];
        $this->electricianLED = 0;
        // Step 10 - Misc Line Items
        if ($this->quote->type && $this->quote->type->name == 'Full Kitchen')
        {
            $rows[] = ['LED Lighting', "<a href='/quote/{$this->quote->id}/led'>Edit LED Options</a>", null, null];
        }

        if (isset($this->meta['quote_puck_lights']) && $this->meta['quote_puck_lights'] > 0)
        {
            // Price for puck lights is $70. $35 for Electrician and $35 for frugal.
            // $this->total += ($this->meta['quote_puck_lights'] * 70);
			try
			{
				$this->forElectrician += ($this->meta['quote_puck_lights'] * 35);
				$this->forFrugal += ($this->meta['quote_puck_lights'] * 35);
				$this->setDebug("(Electrician) Puck Lights", $this->meta['quote_puck_lights'] * 35);
				$this->setDebug("(Frugal) Puck Lights", $this->meta['quote_puck_lights'] * 35);
				$rows[] = [
					'Number of Puck Lights',
					"<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_puck_lights'] . "</a>",
					"$" . number_format($this->meta['quote_puck_lights'] * 70, 2),
					"$" . number_format($this->total, 2)
				];
			}
			catch(\Exception $e)
			{
				
			}
        }
        $allled = 0;

        if (isset($this->meta['quote_led_12']) && $this->meta['quote_led_12'] > 0)
        {
            $led = $this->meta['quote_led_12'] * 12;
            $allled += $led;
            $led += $led * .07;
            $this->total += $led;
            $this->setDebug("(Frugal) LED 12\" Strips", $led);
            $rows[] = [
                'How many 12" LED Strip Lights are needed',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_12'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }

        if (isset($this->meta['quote_led_60']) && $this->meta['quote_led_60'])
        {
            $led = $this->meta['quote_led_60'] * 60;
            $allled += $led;
            $led += $led * .07;
            $this->total += $led;
            $this->setDebug("(Frugal) LED 60\" Strips", $led);
            $rows[] = [
                'How many 60" LED Strip Lights are needed',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_60'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }

        if (isset($this->meta['quote_led_connections']) && $this->meta['quote_led_connections'])
        {
            $led = $this->meta['quote_led_connections'] * 16;
            $allled += $led;
            $led += $led * .07;
            $this->total += $led;
            $this->setDebug("(Frugal) LED Connections", $led);
            $rows[] = [
                'How many LED Strip Light connections are needed?',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_connections'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }

        if (isset($this->meta['quote_led_transformers']) && $this->meta['quote_led_transformers'])
        {
            $led = $this->meta['quote_led_transformers'] * 70;
            $allled += $led;
            $led += $led * .07;
            $this->total += $led;
            $this->setDebug("(Frugal) LED Transformers", $led);
            $rows[] = [
                'How many LED Strip Light transformers are needed?',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_transformers'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }

        if (isset($this->meta['quote_led_couplers']) && $this->meta['quote_led_couplers'])
        {
            $led = $this->meta['quote_led_couplers'] * 14;
            $allled += $led;
            $led += $led * .07;
            $this->total += $led;
            $this->setDebug("(Frugal) LED Couplers", $led);
            $rows[] = [
                'How many LED Strip Light couplers are needed?',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_couplers'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }

        if (isset($this->meta['quote_led_switches']) && $this->meta['quote_led_switches'])
        {
            $led = $this->meta['quote_led_switches'] * 75;
            $allled += $led;
            $led += $led * .07;
            // $this->total += $led;
            $this->electricianLED += $led;
            $this->setDebug("(Electrician) Electrician was given for LED Switches", $led);
            $rows[] = [
                'How many LED Strip Light switches are needed?',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_switches'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }
        if (isset($this->meta['quote_led_feet']) && $this->meta['quote_led_feet'])
        {
            $led = $this->meta['quote_led_feet'] * 30;
            if ($led < 250)
            {
                $led = 250;
            }

            $allled += $led;
            $this->electricianLED += $led;
            $this->setDebug("(Electrician) Electrician given for LED Feet", $led);
            $rows[] = [
                'How many feet of LED Strip Light is being installed?',
                "<a href='/quote/{$this->quote->id}/led'>" . $this->meta['quote_led_feet'] . "</a>",
                "$" . number_format($led, 2),
                "$" . number_format($this->total, 2)
            ];
        }
        $this->designerLED = $allled * .10;
        $this->frugalLED = $allled * .20;
        $this->setDebug("<b>(LED)</b> Designers get $allled * 10", $allled * .10);
        $this->setDebug("<b>(LED)</b> Frugal gets $allled * 20", $allled * .20);

        $items = (isset($this->meta['quote_misc'])) ? explode("\n", $this->meta['quote_misc']) : [];
        $miscdata = null;
        $aprice = 0;
        foreach ($items AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[1]))
            {
                continue;
            }

            $miscdata .= $idata[0] . ", ";
            $idata[1] = str_replace("$", null, $idata[1]);
            $idata[1] = str_replace(",", null, $idata[1]);
            $aprice += floatval($idata[1]);
            if (isset($idata[0]))
            {
                $this->setDebug("Miscellaneous Item ($idata[0])", $idata[1]);
            }
        }
        $miscdata = (isset($this->meta['quote_misc'])) ? substr($miscdata, 0, -2) : "Add Miscelaneous Items";
        $this->total += $aprice;


        $plumbing_items = (isset($this->meta['quote_plumbing_extras'])) ?
            explode("\n", $this->meta['quote_plumbing_extras']) : [];
        $pdata = null;
        $this->plumbing_additional = 0;
        foreach ($plumbing_items AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[1]))
            {
                continue;
            }

            $pdata .= $idata[0] . ", ";
            $idata[1] = str_replace('$', null, $idata[1]);
            $idata[1] = str_replace(",", null, $idata[1]);
            $this->plumbing_additional += (integer)$idata[1];
            if (isset($idata[0]))
            {
                $this->setDebug("Plumber Item ($idata[0])", $idata[1]);
            }
            $aprice += (integer)$idata[1];
        }
        $pdata = (isset($this->meta['quote_plumbing_extras'])) ? ' , ' . substr($pdata, 0, -2) : "";

        $installer_items = isset($this->meta['quote_installer_extras']) ?
            explode("\n", $this->meta['quote_installer_extras']) : [];
        $indata = null;
        $this->installer_additional = 0;
        foreach ($installer_items AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[1]))
            {
                continue;
            }

            $indata .= $idata[0] . ", ";
            $idata[1] = str_replace('$', null, $idata[1]);
            $idata[1] = str_replace(",", null, $idata[1]);
            $this->installer_additional += (integer)$idata[1];
            if (isset($idata[0]))
            {
                $this->setDebug("Installer Item ($idata[0])", $idata[1]);
            }
            $aprice += (integer)$idata[1];
        }
        $indata = (isset($this->meta['quote_installer_extras'])) ? ' , ' . substr($indata, 0, -2) : "";

        $electrical_items = isset($this->meta['quote_electrical_extras']) ?
            explode("\n", $this->meta['quote_electrical_extras']) : [];
        $edata = null;
        $this->electrical_additional = 0;
        foreach ($electrical_items AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[1]))
            {
                continue;
            }

            $edata .= trim($idata[0]) . ", ";
            $idata[1] = str_replace('$', null, $idata[1]);
            $idata[1] = str_replace(",", null, $idata[1]);
            $this->electrical_additional += trim($idata[1]);
            if (isset($idata[0]))
            {
                $this->setDebug("Electrician Item ($idata[0])", $idata[1]);
            }
            $aprice += (double)$idata[1];
        }
        $edata = (isset($this->meta['quote_electrical_extras'])) ? ' , ' . substr($edata, 0, -2) : "";
        $rows[] = [
            'Additional Items',
            "<a href='/quote/{$this->quote->id}/additional'>" . $miscdata . $pdata . $edata . $indata . '</a>',
            "$" . number_format($aprice, 2),
            ($this->admin) ? "$" . number_format($this->total, 2) : null
        ];

        // Coupons

        $coupon = (isset($this->meta['quote_coupon']) && $this->meta['quote_coupon'] > 0) ? $this->meta['quote_coupon'] : 0;
        $this->discounts += $coupon;
        $rows[] = [
            'Coupon',
            "<a href='/quote/{$this->quote->id}/additional'>$" . number_format($coupon, 2) . "</a>",
            null,
            null
        ];
        if (isset($this->meta['quote_discount']) && is_numeric($this->meta['quote_discount']))
        {
            $this->discounts += $this->meta['quote_discount'];
            $rows[] = [
                'Additional Discounts',
                "<a href='/quote/{$this->quote->id}/additional'>$" . number_format($this->meta['quote_discount'],
                    2) . "</a>",
                $this->meta['quote_discount'],
                null
            ];
            $rows[] = [
                'Discount Reason',
                "<a href='/quote/{$this->quote->id}/additional'>{$this->meta['quote_discount_reason']}</a>",
                null,
                null
            ];
        }

        if ($this->quote->promotion)
        {
            $rows[] = [
                'Promotion',
                "<a href='/quote/{$this->quote->id}/additional'>{$this->quote->promotion->name}</a>",
                null,
                null,
                $this->color_success
            ];
        }

        $special = (isset($this->meta['quote_special']) && $this->meta['quote_special']) ? $this->meta['quote_special'] : "Edit Special Instructions";

        $rows[] = [
            'Special Instructions/Needs ',
            "<a href='/quote/{$this->quote->id}/additional'>$special</a>",
            null,
            null
        ];

        if ($this->quote->type && $this->quote->type->name != 'Granite Only' && $this->quote->type->name != 'Cabinet Only')
        {
            if ($this->quote->type->name == 'Builder')
            {
                $extras = Extra::whereActive(true)->where('name', 'LIKE', '%builder%')->get();
            }
            else
            {
                $extras = Extra::whereActive(true)->where('name', 'NOT LIKE', '%builder%')->get();
            }
            foreach ($extras AS $extra)
            {
                $this->setDebug("An Extra Item from Admin Added ($extra->name)", $extra->price);
                switch ($extra->group_id)
                {
                    case 1:
                        $this->forPlumber += $extra->price;
                        $this->setDebug("Plumber gets $extra->name", $extra->price);
                        break;
                    case 2:
                        $this->forElectrician += $extra->price;
                        $this->setDebug("Electrician gets $extra->name", $extra->price);
                        break;
                    case 4:
                        $this->forInstaller += $extra->price;
                        $this->setDebug("Installer gets $extra->name", $extra->price);
                        break;
                    default:
                        $this->total += $extra->price;
                        break;
                } // switch

                if ($this->admin)
                {
                    $rows[] = [
                        $extra->name,
                        "$" . number_format($extra->price),
                        "$" . number_format($extra->price),
                        "$" . number_format($this->total, 2)
                    ];
                }
            }
        } // only extras if granite only or cabinet only.
        // Add delivery item for cabinet only

        // Check all cabinet items and see if there is an assembly charge. If so add it
        foreach ($this->quote->cabinets as $cabinet)
        {
            $x = unserialize($cabinet->data);
            foreach ($x as $id => $item)
            {
                if (!empty($item['description']) && preg_match("/assembly/i", $item['description']))
                {
                    $this->total += $item['price'];
                    $rows[] = [
                        "Cabinet Assembly",
                        $cabinet->cabinet ? $cabinet->cabinet->name : "Unknown",
                        "$" . number_format($item['price']),
                        "$" . number_format($this->total, 2)
                    ];
                }
            }
        }

        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel = Panel::init('default')->header("Additional Items and Requirements")->content($table)->render();
        return $panel; */

        return $rows;

    } // additionalitems

    public function getAddonsHeader()
    {
        $headers = ['Item', 'QTY', 'Price', 'Ext. Price', 'Total'];
        return $headers;
    }

    public function getAddons()
    {
        $rows = [];
        foreach ($this->quote->addons AS $addon)
        {
            $this->setDebug("$addon->qty x {$addon->addon->item} addon added ", $addon->price * $addon->qty);
            if ($addon->addon->group_id)
            {
                switch ($addon->addon->group_id)
                {
                    case 1:
                        $this->forPlumber += $addon->price * $addon->qty;
                        $this->setDebug("Plumber gets ADDON {$addon->addon->item}", $addon->price * $addon->qty);
                        break;
                    case 2:
                        $this->forElectrician += $addon->price * $addon->qty;
                        $this->setDebug("Electrician gets ADDON {$addon->addon->item}", $addon->price * $addon->qty);
                        break;
                    case 4:
                        $this->forInstaller += $addon->price * $addon->qty;
                        $this->setDebug("Installer gets ADDON {$addon->addon->item}", $addon->price * $addon->qty);
                        break;
                    default:
                        $this->forFrugal += $addon->price * $addon->qty;
                        $this->setDebug("Frugal gets ADDON {$addon->addon->item}", $addon->price * $addon->qty);
                        break;

                }
            }
            else // No Designation
            {
                $this->total += $addon->price * $addon->qty;
            }

            $rows[] = [
                $addon->addon->item,
                $addon->qty,
                "$" . number_format($addon->price, 2),
                "$" . number_format($addon->price * $addon->qty, 2),
                "$" . number_format($this->total, 2)
            ];
        }

        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel = Panel::init('info')->header("Addons (<a href='/quote/{$this->quote->id}/addons'>edit</a>)")
            ->content($table)->render(); */

          return $rows;

      }

      public function getCustomerResponsibilityHeader()
      {
          $headers = ['Responsibility'];
          return $headers;
      }

      public function getCustomerResponsibility()
      {
        // Add on a customer responsibility
        $rows = [];
        foreach ($this->quote->responsibilities as $r)
        {
            $rows[] = [
                $r->responsibility->name
            ];
        }
        /* $table = Table::init()->headers($headers)->rows($rows)->render();

        $panel .= Panel::init('info')
            ->header("Customer Responsibilities (<a href='/quote/{$this->quote->id}/addons'>edit</a>)")
            ->content($table)->render();
        return $panel; */

        return $rows;
    }

    public function getQuestionsHeader()
    {
        $headers = ['Question', 'Answer', 'Price'];
        $final = 1;
        $initial = 0;
        if ($this->admin)
        {
            array_push($headers, 'Total');
        }
        else
        {
            array_push($headers, 'Additional');
        }
        return $headers;
    }

    public function getQuestions()
    {
        $rows = [];
        foreach ($this->quote->answers AS $answer)
        {
            if (!$answer->question)
            {
                continue;
            }

            if (!$answer->question->active)
            {
                continue;
            }

            if ($answer->answer == 'on')
            {
                $answer->answer = 'Y';
            }

            if ($answer->question->vendor_id > 0 && !self::hasThisVendor($this->quote, $answer->question->vendor_id))
            {
                continue;
            }

            switch ($answer->question->stage)
            {

                case 'F':
                    if ($this->quote->final)
                    {
                        $price = number_format($this->evaluateAnswer($answer), 2);
                        $this->total += $price;
                        $color = ($price > 0) ? $this->color_info : null;
                        $color = ($price < 0) ? $this->color_success : $color;
                        if (!$answer->question->group_id)
                        {
                            $this->total += (double)$price;    // No group goes to total
                            if ($price > 0)
                            {
                                $this->setDebug("{$answer->question->question} ($answer->answer)  goes to <b>Frugal</b>",
                                    $price);
                            }
                        }
                        $rows[] = [
                            $answer->question->question,
                            $answer->answer,
                            $price,
                            "$" . number_format($this->total, 2),
                            $color
                        ];
                    }
                    break;
                case 'B':
                    $price = number_format($this->evaluateAnswer($answer), 2);
                    $this->total += (double)$price;
                    if (!$answer->question->group_id)
                    {
                        $this->total += (double)$price;    // No group goes to total
                        if ($price > 0)
                        {
                            $this->setDebug("{$answer->question->question} ($answer->answer) goes to <b>Frugal</b>",
                                $price);
                        }
                    }

                    $color = ($price > 0) ? $this->color_info : null;
                    $color = ($price < 0) ? $this->color_success : $color;
                    $rows[] = [
                        $answer->question->question,
                        $answer->answer,
                        $price,
                        "$" . number_format($this->total, 2),
                        $color
                    ];
                    break;
                case 'I':
                    if (!$this->quote->final)
                    {
                        $price = number_format($this->evaluateAnswer($answer), 2);
                        $this->total += (double)$price;
                        if (!$answer->question->group_id)
                        {
                            $this->total += (double)$price;    // No group goes to total
                            if ($price > 0)
                            {
                                $this->setDebug("{$answer->question->question} ($answer->answer)  goes to <b>Frugal</b>",
                                    $price);
                            }
                        }
                        $color = ($price > 0) ? '#0097a7' : null;
                        $color = ($price < 0) ? '#6fd088' : $color;
                        $rows[] = [
                            $answer->question->question,
                            $answer->answer,
                            $price,
                            "$" . number_format($this->total, 2),
                            $color
                        ];
                    }
                    break;
            } // switch
        } // fe answer
        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel = Panel::init('info')
            ->header("Questions and Answers <a href='/quote/{$this->quote->id}/questionaire'>(click to edit)</a>")
            ->content($table)->render();
        if ($this->quote->type == 'Full Kitchen')
        {
            return $panel;
        } */
        return $rows;
    }

    public function evaluateAnswer(QuoteQuestionAnswer $answer)
    {

        $condition = $answer->question->condition;
        if (!$condition)
        {
            return 0;
        }

        if (!$answer->answer)
        {
            return 0;
        }

        if ($condition->answer == '*' ||
            ($condition->answer == 'Y' && $answer->answer == 'Y') ||
            ($condition->answer == 'N' && $answer->answer == 'N')
        )
        {
            // If the condition we are working with is for anything then
            switch ($condition->operand)
            {
                case 'Add':
                    return (!$condition->once) ? ((double)$answer->answer * $condition->amount) : $condition->amount;
                    break;
                case 'Subtract':
                    $value = (!$condition->once) ? ((double)$answer->answer * $condition->amount) : $condition->amount;
                    return -1 * abs($value);
                    break;
            }
        }
        return 0;
    }

    public function getSetting($var)
    {
        if (isset($this->settings[$var]))
        {
            return $this->settings[$var];
        }
        else
        {
            $setting = Setting::whereName($var)->first();
            if (!$setting)
            {
                $s = new Setting;
                $s->name = $var;
                $s->setting = '';
                $s->description = '';
                $s->plugin = '';
                $s->type = '';
                $s->save();
                return null;
            }
            else
            {
                return $setting->value;
            }
        }
    }

    public function checkCustomerSupplyingAppliances()
    {
        $appids = (isset($this->meta['quote_appliances'])) ? $this->meta['quote_appliances'] : [];
        $field = null;
        $appTTL = 0;
        $appCost = 0;
        if ($appids)
        {
            foreach ($appids AS $app)
            {
                if ($app == 30)
                {
                    $this->forPlumber = 0;
                    $this->forElectrician = 0;
                    $this->setDebug("Plumber and Electrician Payouts ZERO'd for Customer Supplying Appliances", 0);
                }
            }
        }
        if (count($appids) == 0)
        {
            $this->forPlumber = 0;
            $this->forElectrician = 0;
            $this->setDebug("No appliances found. Plumber and Electrician Payouts have been ZERO'd", 0);
        }
    }

    public function getPayoutHeaders()
    {
        $headers = ['Item', 'Value', 'Amount', 'Total'];
        return $headers;
    }



    public function getPayouts($report = null)
    {
        $this->forElectrician += $this->getSetting('fElectrician');
        $this->forPlumber += $this->getSetting('fPlumber');
        // If this is final quote we need the quote_questionaire from the initial.
        $this->forElectrician += $this->electrical_additional; // This is from the misc. items
        // #125 - Zero out plumber and electrician in the event customer is supplying own appliances.
        $this->checkCustomerSupplyingAppliances();
        $this->forPlumber += $this->plumbing_additional; // this is for the misc. items
        $this->forInstaller += $this->installer_additional;
        // Pass off to the Contractor Adjustments based on the question and designations.
        $rows = [];
        if ($this->quote->final)
        {
            $this->contractorAdjustmentBuilder(Quote::whereLeadId($this->quote->lead_id)->whereFinal(0)->first());
        }
        $this->contractorAdjustmentBuilder($this->quote, true);

        // Cabinet Item Time
        $this->processContractorAdjustments($rows);
        $this->buildCabinetItems();
        $this->refactorBasedonItems();
        foreach ($this->quote->cabinets AS $cabinet)
        {
            if ($cabinet->cabinet && $cabinet->cabinet->vendor && $cabinet->cabinet->vendor->build_up)
            {
                $this->total += $cabinet->cabinet->vendor->build_up;
                $this->setDebug("{$cabinet->cabinet->frugal_name} has a buildup", $cabinet->cabinet->vendor->build_up);
                $this->cabinetBuildup += $cabinet->cabinet->vendor->build_up;
            }
            if ($cabinet->cabinet)
            {
                $rows[] = [
                    "Cabinet Buildup ({$cabinet->cabinet->frugal_name})",
                    null,
                    "$" . number_format($cabinet->cabinet->vendor->build_up, 2),
                    "$" . number_format($this->total, 2)
                ];
            }
            if ($cabinet->delivery == 'Custom Delivery')
            {
                $this->total += 250.00;
                if (!$cabinet->cabinet)
                {
                    print("ERROR: Cabinet Not Found - What happened here? You should never see this message.");
                    continue;
                }
                $rows[] = [
                    "Custom Delivery ({$cabinet->cabinet->frugal_name})",
                    null,
                    "$250.00",
                    "$" . number_format($this->total, 2)
                ];
            }
        }
        if (isset($this->meta['quote_coupon']) && is_numeric($this->meta['quote_coupon']))
        {
            $this->total = $this->total - $this->meta['quote_coupon'];
            $this->coupon = $this->meta['quote_coupon'];
        }
        if (isset($this->meta['quote_discount']) && is_numeric($this->meta['quote_discount']))
        {
            $this->total = $this->total - $this->meta['quote_discount'];
            $rows[] = [
                "Additional Discount",
                "-$" . number_format($this->meta['quote_discount'], 2),
                "-$" . number_format($this->meta['quote_discount'], 2),
                "$" . number_format($this->total, 2),
                $this->color_success
            ];
        }

        // Final Markup
        if ($this->quote->type && $this->quote->type->name != 'Builder')
        {
            $this->setDebug("Adding 3% to Final Markup", $this->total * .03);
            $this->total = $this->total + ($this->total * .03);
        }
        if (isset($this->meta['quote_coupon']) && is_numeric($this->meta['quote_coupon']))
        {
            $this->beforeCoupon = $this->total - $this->meta['quote_coupon'];
        }
        else
        {
            $this->beforeCoupon = 0;
        }

        $rows[] = ['For Cabinet Installer', "$" . number_format($this->forInstaller, 2), null, null];
        $rows[] = ['For Electrician', "$" . number_format($this->forElectrician, 2), null, null];
        $rows[] = ['For Plumber', "$" . number_format($this->forPlumber, 2), null, null];
        $rows[] = ['For Frugal', "$" . number_format($this->forFrugal, 2), null, null];
        $rows[] = ['For Designer', "$" . number_format($this->forDesigner, 2), null, null];

        $x = 0;
        if (!isset($this->appFirstGroupName)) $this->appFirstGroupName = [];
        foreach($this->appFirstGroupName as $firstGroupName)
        {
            if($firstGroupName == 'Electrician' || $firstGroupName == 'Plumber') continue;

            $y = 0;
            foreach($this->appSecGroupName as $secGroupName)
            {
                if($secGroupName == 'Electrician' || $secGroupName == 'Plumber') continue;
                if($firstGroupName == $secGroupName)
                {
                    $firstGroupPrice[$x] += $this->appSecGroupPrice[$y];
                }
                else if(!in_array($secGroupName, $this->appFirstGroupName))
                {
                    $this->appFirstGroupName[] = $secGroupName;
                    $this->appFirstGroupPrice[] = $this->appSecGroupPrice[$y];
                }
                $y++;
            }

            $rows[] = ['For ' . $firstGroupName, '$' . number_format($this->appFirstGroupPrice[$x], 2), null, null];
            $x++;
        }

        $rows[] = [
            'Cabinet(s): ',
            'Cabinet Items: ' . $this->cabItems,
            'Attachments: ' . $this->attCount,
            'Installer Items ' . $this->instItems
        ];

        if($report) return number_format($this->forFrugal, 2);

        /* $table = Table::init()->headers($headers)->rows($rows)->render();
        $panel = Panel::init('warning')->header("Custom Payout Modifiers")->content($table)->render(); */
        if (Auth::check() && !Auth::user()->superuser)
        {
            return null;
        }
        // return $panel;

        return $rows;
    }

    /**
     * Get Total Price for Adding to Cabinet List Price
     * @return [type] [description]
     */
    public function getTotalAmountFromWoodItems()
    {
        $total = 0;
        // #179 - Add Wood Products if they exist to Cabinet Items.
        foreach ($this->quote->cabinets AS $cabinet)
        {
            if ($cabinet->wood_xml)
            {
                $woods = self::returnWoodArray($cabinet);
                foreach ($woods AS $wood)
                {

                    $total += $wood['qty'] * $wood['price'];
                }
            }
        }
        return $total;
    }

    public function refactorBasedonItems()
    {
        // #179 - Add Wood Products if they exist to Cabinet Items.
        foreach ($this->quote->cabinets AS $cabinet)
        {
            if ($cabinet->wood_xml)
            {
                $woods = self::returnWoodArray($cabinet);
                foreach ($woods AS $wood)
                {
                    $this->cabItems += $wood['qty'];
                    $this->instItems += $wood['qty'];
                }
            }
        }

        /*
                if ($this->cabItems < 30)
                {
                    $this->cabItems = 30;
                }

                if ($this->instItems < 30 && $this->quote->type != 'Cabinet Small Job')
                {
                    $this->instItems = 30;
                }


                if ($this->instItems > 35 && $this->instItems < 40)
                {
                    $this->instItems = 40;
                }

                // Start Staging Price Blocks.
                if ($this->cabItems > 30 && $this->cabItems < 40)
                {
                    $this->cabItems = 40;
                }
        */

        // For Designer
        if ($this->quote->type && ($this->quote->type->name == 'Full Kitchen' || $this->quote->type->name == 'Cabinet and Install'))
        {
            if ($this->cabItems <= 35)
            {
                $this->forDesigner = $this->getSetting('dL35');
            }
            else
            {
                if ($this->cabItems > 35 && $this->cabItems <= 55)
                {
                    $this->forDesigner = $this->getSetting('dG35L55');
                }
                else
                {
                    if ($this->cabItems > 55 && $this->cabItems <= 65)
                    {
                        $this->forDesigner = $this->getSetting('dG55L65');
                    }
                    else
                    {
                        if ($this->cabItems > 65 && $this->cabItems <= 75)
                        {
                            $this->forDesigner = $this->getSetting('dG65L75');
                        }
                        else
                        {
                            if ($this->cabItems > 75 && $this->cabItems <= 85)
                            {
                                $this->forDesigner = $this->getSetting('dG75L85');
                            }
                            else
                            {
                                if ($this->cabItems > 85 && $this->cabItems <= 94)
                                {
                                    $this->forDesigner = $this->getSetting('dG85L94');
                                }
                                else
                                {
                                    if ($this->cabItems > 94 && $this->cabItems <= 110)
                                    {
                                        $this->forDesigner = $this->getSetting('dG94L110');
                                    }
                                    else
                                    {
                                        if ($this->cabItems > 110)
                                        {
                                            $this->forDesigner = $this->getSetting('dG110');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }
        else
        {
            if ($this->quote->type && ($this->quote->type->name == 'Cabinet Small Job' || $this->quote->type->name == 'Builder'))
            {
                $this->forDesigner = 250;
            }
        }

        // ----------------------- Quote Type Specific values

        // For Frugal + on Quote and remove plumber and electrician rates.
        if ($this->quote->type && $this->quote->type->name == 'Cabinet Only')
        {
            foreach ($this->quote->cabinets AS $cabinet)
            {
                if (!$cabinet->cabinet || !$cabinet->cabinet->vendor)
                {
                    echo \BS::alert("danger", "Unable To Determine Cabinets",
                        "Cabinet type has not been selected! Unable to figure multiplier.");
                    return;
                }
                $add = ($cabinet->price * $cabinet->cabinet->vendor->multiplier) * .40;
                $this->forFrugal += $add; // Frugal gets 40% of the cabprice
                $this->setDebug("40% of {$cabinet->cabinet->frugal_name} to Frugal", $add);
                $amt = ($cabinet->measure) ? 500 : 250;
                $this->forDesigner += $amt;
                $this->setDebug("Field Measure for Designer (Y=500/N=250)", $amt);
                $this->forFrugal += 250; // for cabinet buildup.
                $this->setDebug("Frugal got $250 for Buildup", 250);
                switch ($cabinet->location)
                {
                    case 'North':
                        $this->forFrugal += 300;
                        $this->setDebug("Delivery North", 300);
                        break;
                    case 'South':
                        $this->forFrugal += 200;
                        $this->setDebug("Delivery South", 200);
                        break;
                    default:
                        $this->forFrugal += 500;
                        $this->setDebug("Further than 50m", 500);
                        break;
                }
            } // fe
        }
        else
        {
            if ($this->quote->type && $this->quote->type->name == 'Granite Only')
            {
                $this->forInstaller = 0;
                $this->forPlumber = 350;
                $this->forElectrician = 0;
                //$this->appElectrician = 0 ;
                //$this->forDesigner = 0;
            }
            else
            {
                if ($this->quote->type && ($this->quote->type->name == "Cabinet and Install" || $this->quote->type->name == 'Builder'))
                {
                    if ($this->quote->type->name == 'Cabinet and Install')
                    {
                        foreach ($this->quote->cabinets AS $cabinet)
                        {
                            $add = ($cabinet->price * $cabinet->cabinet->vendor->multiplier) * .40;
                            $this->forFrugal += $add; // Frugal gets 40% of the cabprice
                            $this->setDebug("40% of {$cabinet->cabinet->frugal_name} to Frugal", $add);
                        }

                        $this->forInstaller += $this->instItems * 20; // get 20 per installable item not attach
                        $this->forFrugal += 250; // For delivery
                        $this->forFrugal += 250; // for cabinet buildup.
                        $this->forFrugal += $this->instItems * 10; // Cabinet + Install gets $10 for frugal.
                        $this->forFrugal += $this->attCount * 30; // Attachment Count.
                    } // If it's cabinet install
                    else
                    {
                        if ($this->instItems < 40)
                        {
                            $this->forInstaller += 500;
                        }
                        else
                        {
                            $remainder = $this->instItems - 40;
                            $this->forInstaller += 500;
                            $this->forInstaller += $remainder * 10;
                        } // more than 40
                    } // If builder
                } // if cabinet and install or builder
                else
                {
                    if ($this->cabItems <= 35)
                    {
                        $this->forFrugal += $this->getSetting('fL35');
                    }
                    else
                    {
                        if ($this->cabItems > 35 && $this->cabItems <= 55)
                        {
                            $this->forFrugal += $this->getSetting('fG35L55');
                        }
                        else
                        {
                            if ($this->cabItems > 55 && $this->cabItems <= 65)
                            {
                                $this->forFrugal += $this->getSetting('fG55L65');
                            }
                            else
                            {
                                if ($this->cabItems > 65 && $this->cabItems <= 75)
                                {
                                    $this->forFrugal += $this->getSetting('fG65L75');
                                }
                                else
                                {
                                    if ($this->cabItems > 75 && $this->cabItems <= 85)
                                    {
                                        $this->forFrugal += $this->getSetting('fG75L85');
                                    }
                                    else
                                    {
                                        if ($this->cabItems > 85 && $this->cabItems <= 94)
                                        {
                                            $this->forFrugal += $this->getSetting('fG85L94');
                                        }
                                        else
                                        {
                                            if ($this->cabItems > 94 && $this->cabItems <= 110)
                                            {
                                                $this->forFrugal += $this->getSetting('fG94L110');
                                            }
                                            else
                                            {
                                                if ($this->cabItems > 110)
                                                {
                                                    $this->forFrugal += $this->getSetting('fG110');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $this->forFrugal += ($this->attCount * 20);
                    $this->setDebug("Frugal gets Attachment count * 20", $this->attCount * 20);
                    $this->forInstaller += ($this->instItems * 20);
                    $this->setDebug("Installer gets Cabinet Installable ($this->instItems * 20)",
                        $this->instItems * 20);
                }
            }
        }

        $this->forElectrician += $this->appElectrician;
        $this->forPlumber += $this->appPlumber;
        $this->forInstaller += $this->accInstaller;
        $this->forFrugal += $this->accFrugal;
        // Additional - LED Lighting let's add this.
        $this->forDesigner += $this->designerLED;
        $this->forFrugal += $this->frugalLED;
        $this->forElectrician += $this->electricianLED;

        // No electrician or plumber if cabinet + install
        // For Frugal + on Quote and remove plumber and electrician rates.
        if ($this->quote->type && ($this->quote->type->name == 'Cabinet and Install' || $this->quote->type->name == 'Cabinet Only'))
        {
            $this->forPlumber = 0;
            $this->forElectrician = 0;
        }
        if ($this->quote->type && $this->quote->type->name == 'Cabinet Only')
        {
            $this->forInstaller = 0;
        }

        if ($this->quote->type && $this->quote->type->name == 'Granite Only')
        {
            $this->forElectrician = 0;
        }
        /*
                if ($this->quote->type == 'Cabinet Small Job')
                {
                    if (!isset($this->gPrice))
                    {
                        $this->gPrice = 0;
                    }

                    $gMarkup = $this->gPrice * .2;
                    $this->forFrugal += $gMarkup;

                    $this->setDebug("Small Job Marks up Granite 20%", $gMarkup);
                    $sTotal = 0;
                    if (isset($this->meta['sinks']))
                    {
                        foreach ($this->meta['sinks'] AS $sink)
                        {
                            if (!$sink)
                            {
                                continue;
                            }
                            $sink = Sink::find($sink);
                            $sTotal += $sink->price;
                        }
                        $sMarkup = $sTotal * .2;
                        $this->setDebug("Small Job Marks up Sink Costs 20%", $sMarkup);
                        $this->forFrugal += $sMarkup;
                    }
                }
        */
        if ($this->quote->type && $this->quote->type->name == 'Cabinet Small Job')
        {
            $markup = $this->total * .4;
            $this->setDebug("Applying 40% Markup to Frugal for Cabinet Small Job", $markup);
            $this->forFrugal = $markup;
        }

        // Add for installer to total
        $this->total += $this->forInstaller;
        $this->setDebug("Applying Installer Payouts to Quote", $this->forInstaller);
        $this->total += $this->forElectrician;
        $this->setDebug("Applying Electrician Payouts to Quote", $this->forElectrician);
        $this->total += $this->forPlumber;
        $this->setDebug("Applying Plumber Payouts to Quote", $this->forPlumber);
        if ($this->quote->type && $this->quote->type->name != 'Builder')
        {
            $this->total += $this->forFrugal;
            $this->setDebug("Applying Frugal Payouts to Quote", $this->forFrugal);
        }
        $this->total += $this->forDesigner;
        $this->setDebug("Applying Designer Payouts to Quote", $this->forDesigner);
        if ($this->quote->type && $this->quote->type->name == 'Builder')
        {
            if ($this->quote->markup == 0)
            {
                $this->quote->markup = 30;
                $this->quote->save();
            }

            $perc = $this->quote->markup / 100;
            $markup = $this->total * $perc;
            $this->total += $markup;
            $this->setDebug("Applying {$this->quote->markup}% to Total For Builder Markup", $markup);
            $this->forFrugal = $markup;
        }

    }

    /**
     * Return an array of items from the xml
     *
     * @param  QuoteCabinet $cabinet [description]
     * @return [type]                [description]
     */
    public static function returnWoodArray(QuoteCabinet $cabinet)
    {
        $xml = $cabinet->wood_xml;
        try
        {
            $xml = new SimpleXMLElement($xml);
        } catch (Exception $e)
        {
            return 0;
        }
        $data = [];
        $price = 0;
        foreach ($xml->catalogs->catalog->Items->ItemInfo AS $item)
        {
            $data[] = [
                'qty'         => (string)$item->qty,
                'sku'         => (string)$item->sku,
                'price'       => (string)$item->price,
                'height'      => (string)$item->height,
                'depth'       => (string)$item->depth,
                'description' => (string)$item->description
            ];
        }
        return $data;
    }

    public function buildCabinetItems()
    {
        $rows = [];
        $headers = ['Cabinet Item List', null];
        $this->instItems = 0; // Installer Items
        $this->cabItems = 0; // Cabinet Items
        $this->attCount = 0; // Attachment count
        $redact = $this->getSetting('xmlignore');
        $redact = str_replace("\r\n","",$redact);
        $redactArr = explode('|', $redact);
        foreach ($this->quote->cabinets AS $cabinet)
        {
            $cabdata = unserialize($cabinet->data);
            if (!is_array($cabdata))
            {
                continue;
            }

            foreach ($cabdata AS $item)
            {
                $item['description'] = (isset($item['description'])) ? $item['description'] : '';
                if (!isset($item['attachment']))
                {
                    $rows[] = [
                        "(" . $item['sku'] . ") - " . $item['description'],
                        ($this->admin) ? "$" . number_format($item['price'],
                                2) . " x " . $item['qty'] : "QTY: " . $item['qty'],
                        $this->color_info
                    ];

                    if ($item['sku'] != 'N/A' && !in_array($item['sku'], $redactArr))
                    {
                        $this->cabItems += $item['qty'];
                        $this->instItems += $item['qty']; // Installer items.
                    }
                    else
                    {
                        $this->setDebug("<span class='text-danger'>Item $item[sku] ($item[description]) was not added to Cabinet Items</span>",
                            0);
                    }
                }
                else
                {
                    $rows[] = [
                        "Attachment: (" . $item['sku'] . ") - " . $item['description'],
                        ($this->admin) ? "$" . number_format($item['price'],
                                2) . " x " . $item['qty'] : "QTY: " . $item['qty'],
                        $this->color_info
                    ];
                    if ($item['sku'] != 'N/A' && !preg_match("/{$redact}/i", $item['sku']))
                    {
                        if ($item['qty'] <= 0)
                        {
                            $this->setDebug($item['sku'] . " had a quantity of zero, ignoring", 0.00);
                        }
                        else
                        {
                            $this->attCount += $item['qty'];
                            $this->cabItems += $item['qty'];
                        }
                    }
                    else
                    {
                        $this->setDebug($item['sku'] . " has been ignored as a cabinet/attachment", 0.00);
                    }
                }
            }


        } // fe cabinet

        return $rows;
        /*
    FEB-W
    FER-W|FEL-W|FER-B|FEL-B|FEB-B|DW|IH|IW|DH|DD|ID|ESL|ESR|FEL-V|FER-V|FEB-V*/
    }

    public function determineValidQuestion(QuoteQuestionAnswer $answer, Quote $quote, $force = false)
    {
        // So this lovely routine needs to store all of our question ids and answers. If the
        // same question id is found, it's answer needs to be overwritten in memory.

        if (array_key_exists($answer->question->id, $this->processedQuestions))
        {
            if ($force)
            {
                $this->processedQuestions[$answer->question->id] = $answer;

            }
        }
        else
        {
            $this->processedQuestions[$answer->question->id] = $answer;
        }

    }

    public function contractorAdjustmentBuilder($quote, $force = false)
    {
        if (!$quote)
        {
            return;
        }

        foreach ($quote->answers AS $answer)
        {
            if (!isset($answer->question_id))
            {
                continue;
            }

            if (!$answer->question || !$answer->question->active)
            {
                continue;
            }

            if (!$answer->question)
            {
                $answer->delete();
                continue;
            }
            if (!$answer->question)
            {
                continue;
            }

            $this->determineValidQuestion($answer, $quote, $force);
        } // main FE loop.

    }

    public function processContractorAdjustments(&$rows)
    {
        // If this is NOT the final quote we need to work with then...

        foreach ($this->processedQuestions AS $question => $answer)
        {
            $question = QuoteQuestion::find($question);
            $value = $this->evaluateAnswer($answer);
            if ($value != 0)
            {
                if ($value < 0)
                {
                    $this->total += $value;
                }
                $rows[] = [
                    $question->question,
                    "<a href='" . route('quote_questionaire', ['id' => $this->quote->id]) . "'>" . $answer->answer . "</a>",
                    ($this->admin) ? "$" . number_format($value, 2) : null,
                    ($this->admin) ? "$" . number_format($this->total, 2) : null,
                    $this->color_info
                ];
                switch ($answer->question->group_id)
                {
                    case 1:
                        $this->forPlumber += $value;
                        $this->setDebug("Plumber gets {$answer->question->question} ($answer->answer)", $value);
                        break;
                    case 2:
                        $this->forElectrician += $value;
                        $this->setDebug("Electrician gets {$answer->question->question} ($answer->answer)", $value);
                        break;
                    case 4:
                        $this->forInstaller += $value;
                        $this->setDebug("Installer gets {$answer->question->question} ($answer->answer)", $value);
                        break;
                    default:
                        $this->setDebug("Nobody gets {$answer->question->question} ($answer->answer) (Couldn't find designation {$answer->question->group_id})",
                            $value);
                        break;
                } // switch

            } // fe value

        } //fe
        return $rows;
    } // process

    static public function setCabinetData(Quote $quote, $xml, $override = false)
    {
        try
        {
            $xml = new SimpleXMLElement($xml);
        } catch (Exception $e)
        {
            return 0;
        }

        $price = 0;
        foreach ($xml->catalogs->catalog->Items->ItemInfo AS $item)
        {
            if ($item->AttachmentInfo)
            {
                foreach ($item->AttachmentInfo AS $attach)
                {
                    $cabData[] = [
                        'qty'        => (string)$attach->qty,
                        'sku'        => (string)$attach->sku,
                        'price'      => (string)$attach->price,
                        'attachment' => true,
                    ];
                    $price += ((double)$attach->price * (int)$attach->qty);
                }

            }
            $cabData[] = [
                'qty'         => (string)$item->qty,
                'sku'         => (string)$item->sku,
                'price'       => (string)$item->price,
                'height'      => (string)$item->height,
                'depth'       => (string)$item->depth,
                'description' => (string)$item->description
            ];
            $price += ((double)$item->price * (int)$item->qty);
        }

        // Check to see if $xml->catalogs->catalog->TotalPage exists then loop through TotalPageOption for name and price
        if (isset($xml->catalogs->catalog->TotalPage->TotalPageOption))
        {
            foreach ($xml->catalogs->catalog->TotalPage->TotalPageOption AS $option)
            {
                $cabData[] = [
                    'qty'         => '1',
                    'sku'         => 'N/A',
                    'price'       => (string)$option->price,
                    'height'      => 'N/A',
                    'depth'       => 'N/A',
                    'description' => (string)$option->name
                ];
                $price += ((double)$option->price);
            }
        }

        $cabinet = new QuoteCabinet;
        $cabinet->quote_id = $quote->id;
        $cabinet->override = '';
        $cabinet->location = '';
        $cabinet->measure = '0';
        $cabinet->color = '';
        $cabinet->name = '';
        $cabinet->delivery = '';
        $cabinet->wood_xml = '';
        $cabinet->description = '';
        $cabinet->inches = '0';
        $cabinet->cabinet_id = '0';

        if ($override)
        {
            $q = QuoteCabinet::find($override);
            $q->override = serialize($cabData);
            $q->save();
            PurchaseController::overridePO($quote, $q);
        }
        else
        {
            $cabinet->data = serialize($cabData);
            $cabinet->price = $price;
            $cabinet->save();
        }
        $quote->save();
    }

    static public function financing(Quote $quote, $details, $returnAmount = false, $minified = false)
    {

        $meta = unserialize($quote->meta)['meta'];
        if (!isset($meta['finance']))
        {
            return null;
        }
        $finance = $meta['finance'];
        switch ($finance['type'])
        {
            case 'all':    // Customer is financing the entire amount
                if ($finance['terms'] == '12')
                {
                    $payment = $details->total * .035;
                }
                elseif ($finance['terms'] == '65')
                {
                    $payment = $details->total * .02;
                }
                elseif ($finance['terms'] == '12G')
                {
                    $payment = 0;
                    $details->total += ($details->total * .03); // Add 3% for GS
                }
                elseif ($finance['terms'] == '84G')
                {
                    $details->total += ($details->total * .03); // Add 3% for GS
                    $payment = $details->total * .0173;
                }
                break;
            case 'none':    //Customer is not financing anything
                $firstPayment = $details->total * .50;
                $secondPayment = $details->total * .45;
                $finalPayment = $details->total - ($firstPayment + $secondPayment);
                break;
            case 'partial':
                $total = $details->total;
                $financed = $total - $finance['downpayment'];    // < -- how much is being financed?
                $cash = $finance['downpayment'];    // < -- How much is in cash (downpayment)
                $terms = $finance['terms'];    // < -- For the remainder how is it being financed?

                if ($finance['terms'] == '12')
                {
                    $payment = $financed * .035;
                }
                elseif ($finance['terms'] == '65')
                {
                    $payment = $financed * .02;
                }
                elseif ($finance['terms'] == '12G')
                {
                    $payment = 0;
                }
                elseif ($finance['terms'] == '84G')
                {
                    $payment = $financed * .0173;
                }
                $fiddy = $total * .5;
                if ($cash >= $fiddy)
                {
                    $firstPayment = $fiddy;    // Our first payment is now 50% of the total
                    $secondPayment = $cash - $fiddy;    // Second payment is whatever was left.
                    $finalPayment = 0.00;    //$total - ($firstPayment + $secondPayment);
                }
                else
                {
                    // We aren't putting enough down for 50% but take what we can get.
                    $firstPayment = $cash;
                    $secondPayment = 0.00;
                    $finalPayment = 0.00;    // $total - $firstPayment;
                }
                break;

        } //sw

        // at this point, we have a $payment (if applicable) and $qData['total'] has our new total amount
        // and it also has our financed amount if partial.  Starting to get a headache.

        // Step 3: Figure out based on our totals what the first, second and final payments are.
        /*
        1. First payment is $       which is 50% of the total amount of sale.
        2. Second payment is $       which is 45% due after granite is installed.
        3. Balance $       is due on completion of Frugals Final Touch (See Attachment G).
        */
        // ok well that was easier than I thought.. lets put it on the PDF.
        // Start a new case basis switch
        switch ($finance['type'])
        {
            case 'all':    // Customer is financing the entire amount
                $financingText = "<br/>You have chosen to finance the entire amount of $" . number_format($details->total,
                        2) . ". The financing terms you have selected are: ";
                if ($finance['terms'] == '12')
                {
                    $financingText .= "0% interest for 12 months with Wells Fargo; resulting in an approximate monthly payment of $" . number_format($payment,
                            2) . ".";
                }
                elseif ($finance['terms'] == '65')
                {
                    $financingText .= "9.9% interest for 65 months with Wells Fargo; resulting in an approximate monthly payment of $" . number_format($payment,
                            2) . ".";
                }
                elseif ($finance['terms'] == '12G')
                {
                    $financingText .= "0% interest, no payments for 12 months with GreenSky Financial. This includes a 3% Processing Fee.";

                }
                elseif ($finance['terms'] == '84G')
                {
                    $financingText .= "9.9% interest for 84 Months with GreenSky Financial; resulting in an approximate monthly payment of $" .
                        number_format($payment, 2) . ". This includes a 3% Processing Fee.";
                }
                $firstPayment = 0;
                $secondPayment = 0;
                $thirdPayment = 0;
                $finalPayment = 0;
                break;
            case 'none':
                if ($finance['method'] != 'split')
                {
                    $financingText = "<br/>You have chosen to not finance and pay the entire amount of $" . number_format($details->total,
                            2) . ".";
                }
                else
                {
                    // Split Payments.
                    $financingText = "<br/>You have chosen to not finance and split the entire amount
                        of $" . number_format($details->total, 2) . " in the following way: ";
                    $b = $details->total;
                    $tdisc = 0;
                    if (isset($finance['no_cash']) && $finance['no_cash'] > 0)
                    {
                        $financingText .= "$ " . number_format($finance['no_cash'], 2) . " in cash ";
                    }
                    if (isset($finance['no_credit']) && $finance['no_credit'] > 0)
                    {
                        $financingText .= "$" . number_format($finance['no_credit'], 2) . " in credit ";
                    }
                    $financingText = substr($financingText, 0, -1);
                    $financingText .= ".";
                }
                break;
            case 'partial':
                $financingText = "<br/><br/>You have elected to finance a partial amount. You are putting down $" . number_format($cash,
                        2) . "
                         and financing the remaining balance. Your downpayment will be paid in the following way(s): ";

                if (isset($finance['down_cash']) && $finance['down_cash'] > 0)
                {
                    $financed = $financed - ($finance['down_cash'] * .05);
                    $financingText .= "$" . number_format($finance['down_cash'], 2) . " in cash ";
                }
                if (isset($finance['down_credit']) && $finance['down_credit'] > 0)
                {
                    $financed = $financed - ($finance['down_credit'] * .025);
                    $financingText .= "$" . number_format($finance['down_credit'], 2) . " on a credit card ";
                }
                $financingText = substr($financingText, 0, -1);

                $financingText .= ". The financing terms you have selected are: ";

                if ($finance['terms'] == '12')
                {
                    $financingText .= "0% interest for 12 months with Wells Fargo; resulting in an approximate monthly payment of $" . number_format($payment,
                            2) . ".";
                }
                elseif ($finance['terms'] == '65')
                {
                    $financingText .= "9.9% interest for 65 months with Wells Fargo; resulting in an approximate monthly payment of $" . number_format($payment,
                            2) . ".";
                }
                elseif ($finance['terms'] == '12G')
                {
                    $financingText .= "0% interest, no payments for 12 months with GreenSky Financial";

                }
                elseif ($finance['terms'] == '84G')
                {
                    $financingText .= "9.9% interest for 84 Months with GreenSky Financial; resulting in an approximate monthly payment of $" .
                        number_format($payment, 2) . ".";
                }

                break;
        }
        $what = (preg_match('/Cabinet and /i', $quote->type->name)) ? "cabinets are installed" : "during walkthrough";
        $what = (preg_match('/Cabinet Only/', $quote->type->name)) ? "cabinets are delivered" : $what;
        if ($quote->type->name == 'Cabinet Only')
        {
            $secondPayment = $secondPayment + $finalPayment;
        }
        if ($firstPayment > 0)
        {
            $financingText .= "<br/><br/>Your first payment is 50% of the total price: $" . number_format($firstPayment,
                    2) . " (due upon contract signing)";
        }
        if ($secondPayment > 0)
        {
            $financingText .= "<br/><br/>Your second payment is 45% of the total price: $" . number_format($secondPayment,
                    2) . " (due after {$what})";
        }
        if ($quote->type->name != 'Cabinet Only')
        {
            $financingText .= "<br/><br/>The final balance (5% of the total price, in addition to any changes agreed upon not included in this agreement), is due upon completion of Frugal's Final Touch. ";
        }

        if ($minified)
        {
            return $financingText;
        }
        // Static Content
        // Signers line
        $financingText .= "<br/><br/>You may cancel this transaction, without any penalty or obligation, within three
    business days from the date of the signed contract. If you cancel after three business days you are obligated
    to pay 50% of the quoted price, plus any legal fees incurred by Frugal Kitchens. Upon receiving the cabinets
    Frugal Kitchens will then deliver them to your property. By signing here you agree to the above terms.
<br/><Br/>
<b>Buyers Signature__________________________________ &nbsp; &nbsp; &nbsp;    Date: _______________________</b>";
        if ($returnAmount)
        {
            return $details->total;
        }
        else
        {
            return $financingText;
        }

    } //fn

    static public function createSnapshot(Quote $quote)
    {
        $obj = self::getQuoteObject($quote);
        $ss = new Snapshot;
        $ss->quote_id = $quote->id;
        $ss->quote = serialize($quote);
        $ss->debug = serialize($obj->debug);
        $file = Str::random(9) . ".pdf";
        File::makeDirectory("snapshots/$quote->id", $mode = 0777, true, true);
        $pdfPath = "snapshots/$quote->id/$file";
        $data = View::make('pdf.contract')->withQuote($quote)->render();
        try
        {
            File::put($pdfPath, PDF::load($data, 'A4', 'portrait')->output());
        } catch (\Exception $e)
        {

        }
        $ss->location = $file;
        $ss->save();
    }

    /**
     * If we have a promo assigned, we'll use this to check the type of promo (modifier)
     * and send in the amount to check against the qualifier.
     * @param Promotion $promotion
     * @param $amount
     * @return mixed
     */
    public function checkPromo(Promotion $promotion = null, $amount)
    {
        if (!$promotion) return $amount;
        // By default we will return the same amount that was given.
        // Since we don't know what a promotion is supposed to do.
        switch ($promotion->modifier)
        {
            case 'GRANITE_SQFT' :
                if ($promotion->condition == ">" && $amount > $promotion->qualifier)
                {
                    return $amount - $promotion->discount_amount;
                }
                elseif ($promotion->condition == "<" && $amount < $promotion->qualifier) return $amount - $promotion->discount_amount;
                elseif ($promotion->condition == "=" && $amount == $promotion->qualifier) return $amount - $promotion->discount_amount;
                break;
        }
        return $amount;
    }
}
