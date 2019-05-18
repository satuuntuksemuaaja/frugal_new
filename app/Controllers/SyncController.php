<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Accessory;
use FK3\Models\Authorization;
use FK3\Models\AuthorizationItem;
use FK3\Models\AuthorizationList;
use FK3\Models\BuildupNote;
use FK3\Models\Cabinet;
use FK3\Models\ChangeOrder;
use FK3\Models\ChangeOrderDetail;
use FK3\Models\Checklist;
use FK3\Models\Countertop;
use FK3\Models\CountertopType;
use FK3\Models\Extra;
use FK3\Models\Location;
use FK3\Models\Lead;
use FK3\Models\LeadNote;
use FK3\Models\LeadSource;
use FK3\Models\LeadUpdate;
use FK3\Models\Notification;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Followup;
use FK3\Models\Fft;
use FK3\Models\FftNote;
use FK3\Models\Faq;
use FK3\Models\Granite;
use FK3\Models\Appliance;
use FK3\Models\Quote;
use FK3\Models\QuoteAddon;
use FK3\Models\QuoteType;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteAppliance;
use FK3\Models\QuoteGranite;
use FK3\Models\QuoteQuestion;
use FK3\Models\QuoteQuestionAnswer;
use FK3\Models\QuoteQuestionCondition;
use FK3\Models\QuoteResponsibility;
use FK3\Models\QuoteTile;
use FK3\Models\QuestionCategory;
use FK3\Models\QuestionAnswer;
use FK3\Models\Setting;
use FK3\Models\Shop;
use FK3\Models\ShopCabinet;
use FK3\Models\Sink;
use FK3\Models\Stage;
use FK3\Models\Status;
use FK3\Models\StatusExpiration;
use FK3\Models\StatusExpirationAction;
use FK3\Models\Hardware;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\JobNote;
use FK3\Models\JobSchedule;
use FK3\Models\Note;
use FK3\Models\Po;
use FK3\Models\PoItem;
use FK3\Models\Promotion;
use FK3\Models\Punch;
use FK3\Models\Payout;
use FK3\Models\PayoutItem;
use FK3\Models\Responsibility;
use FK3\Models\Snapshot;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\Group;
use FK3\Models\GroupAcl;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use FK3\Models\Addon;
use FK3\Models\Acl;
use FK3\Models\AclCategory;
use FK3\Models\Vendor;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use DB;

class SyncController extends Controller
{
    public $oldDb = 'mysql_2';
    public $countLoop = 1000;

    public function sync(Request $request)
    {
        return view('sync.index');
    }

    public function doSync(Request $request)
    {
        $time_start = microtime(true);

        try
        {
            //FK3 - Table Accessories
            Accessory::truncate();
            $fk2_accessories_total = DB::connection($this->oldDb)->select('select count(*) as total_data from accessories');
            $loop = (integer)ceil($fk2_accessories_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_accessories = DB::connection($this->oldDb)->select('select * from accessories order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_accessories as $fk2_accessory)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_accessory->id;
                  $insertDataArr['sku'] = $fk2_accessory->sku;
                  $insertDataArr['description'] = $fk2_accessory->description;
                  $insertDataArr['name'] = $fk2_accessory->name;
                  $insertDataArr['price'] = $fk2_accessory->price;
                  $insertDataArr['vendor_id'] = $fk2_accessory->vendor_id;
                  $insertDataArr['on_site'] = $fk2_accessory->on_site;
                  $insertDataArr['active'] = $fk2_accessory->active;
                  $insertDataArr['image'] = $fk2_accessory->image;
                  $insertDataArr['deleted_at'] = $fk2_accessory->deleted_at;
                  $insertDataArr['created_at'] = $fk2_accessory->created_at;
                  $insertDataArr['updated_at'] = $fk2_accessory->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Accessory::insert($insertArr);
            }

            //FK3 - Table Acls
            Acl::truncate();
            $acl = new Acl();
            $acl->acl = 'admin.main';
            $acl->action = 'Access Main Area';
            $acl->description = 'This ACL will enable the user to see the admin menu option to the left.';
            $acl->acl_category_id = '1';
            $acl->save();

            $acl = new Acl();
            $acl->acl = 'admin.users';
            $acl->action = 'Manage Users';
            $acl->description = 'This this allows access to manage the users.';
            $acl->acl_category_id = '1';
            $acl->save();

            //FK3 - Table Acl Category
            AclCategory::truncate();
            $aclCategory = new AclCategory();
            $aclCategory->name = 'Administrative Functions';
            $aclCategory->save();

            //FK3 - Table Addons
            Addon::truncate();
            $fk2_addons_total = DB::connection($this->oldDb)->select('select count(*) as total_data from addons');
            $loop = (integer)ceil($fk2_addons_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_addons = DB::connection($this->oldDb)->select('select * from addons order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_addons as $fk2_addon)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_addon->id;
                  $insertDataArr['item'] = $fk2_addon->item;
                  $insertDataArr['price'] = $fk2_addon->price;
                  $insertDataArr['active'] = $fk2_addon->active;
                  $insertDataArr['automatic'] = '1';
                  $insertDataArr['contract'] = $fk2_addon->contract;
                  $insertDataArr['group_id'] = $fk2_addon->designation_id;
                  $insertDataArr['created_at'] = $fk2_addon->created_at;
                  $insertDataArr['updated_at'] = $fk2_addon->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Addon::insert($insertArr);
            }

            //FK3 - Table Appliances
            Appliance::truncate();
            $fk2_appliances_total = DB::connection($this->oldDb)->select('select count(*) as total_data from appliances');
            $loop = (integer)ceil($fk2_appliances_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_appliances = DB::connection($this->oldDb)->select('select * from appliances order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_appliances as $fk2_appliance)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_appliance->id;
                  $insertDataArr['name'] = $fk2_appliance->name;
                  $insertDataArr['price'] = $fk2_appliance->price;
                  $insertDataArr['count_as'] = $fk2_appliance->countas;
                  $insertDataArr['group_id'] = $fk2_appliance->designation_id;
                  $insertDataArr['active'] = $fk2_appliance->active;
                  $insertDataArr['created_at'] = $fk2_appliance->created_at;
                  $insertDataArr['updated_at'] = $fk2_appliance->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Appliance::insert($insertArr);
            }

            //FK3 - Table Authorization
            Authorization::truncate();
            $fk2_authorizations_total = DB::connection($this->oldDb)->select('select count(*) as total_data from authorizations');
            $loop = (integer)ceil($fk2_authorizations_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_authorizations = DB::connection($this->oldDb)->select('select * from authorizations order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_authorizations as $fk2_authorization)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_authorization->id;
                  $insertDataArr['job_id'] = $fk2_authorization->job_id;
                  $insertDataArr['signature'] = $fk2_authorization->signature;
                  ($fk2_authorization->signed_on != '0000-00-00 00:00:00') ? $insertDataArr['signed_on'] = $fk2_authorization->signed_on : $insertDataArr['signed_on'] = null;
                  $insertDataArr['created_at'] = $fk2_authorization->created_at;
                  $insertDataArr['updated_at'] = $fk2_authorization->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Authorization::insert($insertArr);
            }

            //FK3 - Table Authorization_Items
            AuthorizationItem::truncate();
            $fk2_authorization_items_total = DB::connection($this->oldDb)->select('select count(*) as total_data from authorization_items');
            $loop = (integer)ceil($fk2_authorization_items_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_authorization_items = DB::connection($this->oldDb)->select('select * from authorization_items order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_authorization_items as $fk2_authorization_item)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_authorization_item->id;
                  $insertDataArr['authorization_id'] = $fk2_authorization_item->authorization_id;
                  $insertDataArr['item'] = $fk2_authorization_item->item;
                  $insertDataArr['created_at'] = $fk2_authorization_item->created_at;
                  $insertDataArr['updated_at'] = $fk2_authorization_item->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                AuthorizationItem::insert($insertArr);
            }

            //FK3 - Table Authorization_Lists
            AuthorizationList::truncate();
            $fk2_authorization_lists_total = DB::connection($this->oldDb)->select('select count(*) as total_data from authorization_lists');
            $loop = (integer)ceil($fk2_authorization_lists_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_authorization_lists = DB::connection($this->oldDb)->select('select * from authorization_lists order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_authorization_lists as $fk2_authorization_list)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_authorization_list->id;
                  $insertDataArr['item'] = $fk2_authorization_list->item;
                  $insertDataArr['active'] = $fk2_authorization_list->active;
                  $insertDataArr['created_at'] = $fk2_authorization_list->created_at;
                  $insertDataArr['updated_at'] = $fk2_authorization_list->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                AuthorizationList::insert($insertArr);
            }

            //FK3 - Table BuildupNote
            BuildupNote::truncate();
            $fk2_builupNotes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from buildup_notes');
            $loop = (integer)ceil($fk2_builupNotes_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_builupNotes = DB::connection($this->oldDb)->select('select * from buildup_notes order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_builupNotes as $fk2_builupNote)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_builupNote->id;
                  $insertDataArr['user_id'] = $fk2_builupNote->user_id;
                  $insertDataArr['job_id'] = $fk2_builupNote->job_id;
                  $insertDataArr['note'] = $fk2_builupNote->note;
                  $insertDataArr['created_at'] = $fk2_builupNote->created_at;
                  $insertDataArr['updated_at'] = $fk2_builupNote->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                BuildupNote::insert($insertArr);
            }

            //FK3 - Table Cabinet
            Cabinet::truncate();
            $fk2_cabinets_total = DB::connection($this->oldDb)->select('select count(*) as total_data from cabinets');
            $loop = (integer)ceil($fk2_cabinets_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_cabinets = DB::connection($this->oldDb)->select('select * from cabinets order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_cabinets as $fk2_cabinet)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_cabinet->id;
                  $insertDataArr['frugal_name'] = $fk2_cabinet->frugal_name;
                  $insertDataArr['name'] = $fk2_cabinet->name;
                  $insertDataArr['vendor_id'] = $fk2_cabinet->vendor_id;
                  $insertDataArr['active'] = $fk2_cabinet->active;
                  $insertDataArr['description'] = $fk2_cabinet->description;
                  $insertDataArr['image'] = $fk2_cabinet->image;
                  $insertDataArr['created_at'] = $fk2_cabinet->created_at;
                  $insertDataArr['updated_at'] = $fk2_cabinet->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Cabinet::insert($insertArr);
            }

            //FK3 - Table Change Orders
            ChangeOrder::truncate();
            $fk2_change_orders_total = DB::connection($this->oldDb)->select('select count(*) as total_data from change_orders');
            $loop = (integer)ceil($fk2_change_orders_total[0]->total_data / 50);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_change_orders = DB::connection($this->oldDb)->select('select * from change_orders order by id asc LIMIT 50 OFFSET ' . ($x * 50));

                $insertArr = array();
                foreach($fk2_change_orders as $fk2_change_order)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_change_order->id;
                  $insertDataArr['job_id'] = $fk2_change_order->job_id;
                  $insertDataArr['user_id'] = $fk2_change_order->user_id;
                  $insertDataArr['signature'] = $fk2_change_order->signature;
                  ($fk2_change_order->signed_on != '0000-00-00 00:00:00') ? $insertDataArr['signed_on'] = $fk2_change_order->signed_on : $insertDataArr['signed_on'] = null;
                  $insertDataArr['signed'] = $fk2_change_order->signed;
                  $insertDataArr['billed'] = $fk2_change_order->billed;
                  $insertDataArr['closed'] = $fk2_change_order->closed;
                  $insertDataArr['sent'] = $fk2_change_order->sent;
                  ($fk2_change_order->sent_on != '0000-00-00 00:00:00') ? $insertDataArr['sent_on'] = $fk2_change_order->sent_on : $insertDataArr['sent_on'] = null;
                  $insertDataArr['declined'] = $fk2_change_order->declined;
                  $insertDataArr['created_at'] = $fk2_change_order->created_at;
                  $insertDataArr['updated_at'] = $fk2_change_order->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                ChangeOrder::insert($insertArr);
            }

            //FK3 - Table Change Order Details
            ChangeOrderDetail::truncate();
            $fk2_change_order_details_total = DB::connection($this->oldDb)->select('select count(*) as total_data from change_order_details');
            $loop = (integer)ceil($fk2_change_order_details_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_change_order_details = DB::connection($this->oldDb)->select('select * from change_order_details order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_change_order_details as $fk2_change_order_detail)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_change_order_detail->id;
                  $insertDataArr['change_order_id'] = $fk2_change_order_detail->change_order_id;
                  $insertDataArr['description'] = $fk2_change_order_detail->description;
                  $insertDataArr['price'] = $fk2_change_order_detail->price;
                  $insertDataArr['user_id'] = $fk2_change_order_detail->user_id;
                  $insertDataArr['orderable'] = $fk2_change_order_detail->orderable;
                  ($fk2_change_order_detail->ordered_on != '0000-00-00 00:00:00') ? $insertDataArr['ordered_on'] = $fk2_change_order_detail->ordered_on : $insertDataArr['ordered_on'] = null;
                  $insertDataArr['ordered_by'] = $fk2_change_order_detail->ordered_by;
                  $insertDataArr['created_at'] = $fk2_change_order_detail->created_at;
                  $insertDataArr['updated_at'] = $fk2_change_order_detail->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                ChangeOrderDetail::insert($insertArr);
            }

            //FK3 - Table Checklists
            Checklist::truncate();
            $fk2_checklists_total = DB::connection($this->oldDb)->select('select count(*) as total_data from checklists');
            $loop = (integer)ceil($fk2_checklists_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_checklists = DB::connection($this->oldDb)->select('select * from checklists order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_checklists as $fk2_checklist)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_checklist->id;
                  $insertDataArr['question'] = $fk2_checklist->question;
                  $insertDataArr['category'] = $fk2_checklist->category;
                  ($fk2_checklist->created_at != '0000-00-00 00:00:00') ? $insertDataArr['created_at'] = $fk2_checklist->created_at : $insertDataArr['created_at'] = null;
                  ($fk2_checklist->updated_at != '0000-00-00 00:00:00') ? $insertDataArr['updated_at'] = $fk2_checklist->updated_at : $insertDataArr['updated_at'] = null;
                  $insertArr[] = $insertDataArr;
                }
                Checklist::insert($insertArr);
            }

            //FK3 - Table Contacts
            Contact::truncate();
            $fk2_contacts_total = DB::connection($this->oldDb)->select('select count(*) as total_data from contacts');
            $loop = (integer)ceil($fk2_contacts_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_contacts = DB::connection($this->oldDb)->select('select * from contacts order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_contacts as $fk2_contact)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_contact->id;
                  $insertDataArr['customer_id'] = $fk2_contact->customer_id;
                  $insertDataArr['name'] = $fk2_contact->name;
                  $insertDataArr['email'] = $fk2_contact->email;
                  $insertDataArr['mobile'] = $fk2_contact->mobile;
                  $insertDataArr['home'] = $fk2_contact->home;
                  $insertDataArr['alternate'] = $fk2_contact->alternate;
                  $insertDataArr['primary'] = $fk2_contact->primary;
                  $insertDataArr['deleted_at'] = $fk2_contact->deleted_at;
                  $insertDataArr['created_at'] = $fk2_contact->created_at;
                  $insertDataArr['updated_at'] = $fk2_contact->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Contact::insert($insertArr);
            }

            //FK3 - Table Countertops
            Countertop::truncate();
            DB::insert("
            INSERT INTO `countertops` (`id`, `created_at`, `updated_at`, `name`, `price`, `removal_price`, `active`, `type_id`) VALUES
              (1, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Santa Cecilia', 32, 0, 1, 1),
              (2, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Napoli / Giallo Rio', 32, 0, 1, 1),
              (3, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Giallo Napoli', 32, 0, 1, 1),
              (4, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Uba Tuba', 32, 0, 1, 1),
              (5, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Golden King', 34, 0, 1, 1),
              (6, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'St Cecelia', 32, 0, 1, 1),
              (7, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Peacock Green', 32, 0, 1, 1),
              (8, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Giallo Bahia', 40, 0, 0, 1),
              (9, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Medina Brown', 36, 0, 1, 1),
              (10, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'New Venetian Gold', 32, 0, 1, 1),
              (11, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Samoa', 36, 0, 1, 1),
              (12, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Giallo Ornamental', 36, 0, 1, 1),
              (13, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Tan Brown', 32, 0, 1, 1),
              (14, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Tropic Brown', 35, 0, 1, 1),
              (15, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Golden Flower', 38, 0, 1, 1),
              (16, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Black Aracruz', 40, 0, 1, 1),
              (17, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Branco Itaunas', 35, 0, 1, 1),
              (18, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Manasses (when available)', 40, 0, 1, 1),
              (19, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Kashmir White', 42, 0, 1, 1),
              (20, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Kashmir Gold', 42, 0, 1, 1),
              (21, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Colonial Cream', 42, 0, 1, 1),
              (22, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Colonial Gold', 42, 0, 1, 1),
              (23, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Madura Gold', 45, 0, 1, 1),
              (24, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'New River White', 50, 0, 1, 1),
              (25, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Absolute Black', 48, 0, 1, 1),
              (26, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Black Galaxy', 52, 0, 1, 1),
              (27, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Nepture Bordeaux', 54, 0, 1, 1),
              (28, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Absolute Cream', 54, 0, 1, 1),
              (29, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sucuri', 54, 0, 1, 1),
              (30, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Springs', 54, 0, 1, 1),
              (31, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sienna Bordeaux', 51, 0, 1, 1),
              (32, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bianco Antiquo', 56, 0, 0, 1),
              (33, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Romano', 56, 0, 1, 1),
              (34, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Galaxy', 56, 0, 1, 1),
              (35, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Solarius', 56, 0, 1, 1),
              (36, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Typhoon Bordeaux', 51, 0, 1, 1),
              (37, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cold Springs', 58, 0, 1, 1),
              (38, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Delicatus White', 60, 0, 1, 1),
              (39, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Delicatus Splendor', 60, 0, 1, 1),
              (40, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Carrara (Marble)', 52, 0, 1, 3),
              (41, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Crema Marfil (Marble)', 52, 0, 1, 3),
              (42, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Dark Emperador (Marble)', 52, 0, 1, 3),
              (43, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'San Fransisco', 32, 0, 1, 1),
              (44, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Other', 0, 0, 1, 1),
              (45, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Accacia Brown (Quartz)', 49, 0, 1, 2),
              (46, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Almond Roca (Quartz)', 49, 0, 1, 2),
              (47, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Alpine  (Quartz)', 49, 0, 1, 2),
              (48, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Carrara Marmi (Quartz)', 42, 0, 1, 2),
              (49, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Athenia Gold  (Quartz)', 49, 0, 1, 2),
              (50, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cascade White  (Quartz)', 49, 0, 1, 2),
              (51, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Chakra Beige  (Quartz)', 44, 0, 1, 2),
              (52, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Concerto  (Quartz)', 44, 0, 1, 2),
              (53, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Coronado  (Quartz)', 49, 0, 1, 2),
              (54, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Fairy White  (Quartz)', 49, 0, 1, 2),
              (55, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Iced White  (Quartz)', 39, 0, 1, 2),
              (56, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Lagos Azul  (Quartz)', 49, 0, 1, 2),
              (57, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Mojave  (Quartz)', 49, 0, 1, 2),
              (58, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Rushmore  (Quartz)', 49, 0, 1, 2),
              (59, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sahara Beige  (Quartz)', 58, 0, 1, 2),
              (60, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Santa Felicita  (Quartz)', 49, 0, 1, 2),
              (61, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Shadow Gray  (Quartz)', 49, 0, 1, 2),
              (62, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sherwood Forest  (Quartz)', 49, 0, 1, 2),
              (63, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Snow White  (Quartz)', 49, 0, 1, 2),
              (64, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sparkling White  (Quartz)', 49, 0, 1, 2),
              (65, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sparkling Ruby  (Quartz)', 49, 0, 1, 2),
              (66, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sparkling White  (Quartz)', 49, 0, 1, 2),
              (67, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Toasted Almond  (Quartz)', 49, 0, 1, 2),
              (68, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Aberdeen  (Quartz)', 73, 0, 1, 2),
              (69, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Armitage  (Quartz)', 73, 0, 1, 2),
              (70, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Aragon  (Quartz)', 73, 0, 1, 2),
              (71, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Ashford  (Quartz)', 73, 0, 1, 2),
              (72, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bala Blue  (Quartz)', 73, 0, 1, 2),
              (73, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bellingham  (Quartz)', 73, 0, 1, 2),
              (74, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Braemar  (Quartz)', 73, 0, 1, 2),
              (75, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Berkely  (Quartz)', 73, 0, 1, 2),
              (76, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Blackwood  (Quartz)', 73, 0, 1, 2),
              (77, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bradford  (Quartz)', 73, 0, 1, 2),
              (78, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bradshaw  (Quartz)', 65, 0, 1, 2),
              (79, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Brecon Brown  (Quartz)', 73, 0, 1, 2),
              (80, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Brentwood', 73, 0, 1, 1),
              (81, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bristol Blue  (Quartz)', 73, 0, 1, 2),
              (82, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Brownhill  (Quartz)', 73, 0, 1, 2),
              (83, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Buckingham  (Quartz)', 73, 0, 1, 2),
              (84, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Burnbury  (Quartz)', 73, 0, 1, 2),
              (85, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Burton Brown  (Quartz)', 73, 0, 1, 2),
              (86, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Caerphilly Green  (Quartz)', 73, 0, 1, 2),
              (87, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cambrian Black  (Quartz)', 73, 0, 1, 2),
              (88, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cambrian Gold  (Quartz)', 73, 0, 1, 2),
              (89, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Canterbury  (Quartz)', 73, 0, 1, 2),
              (90, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cardiff Cream  (Quartz)', 73, 0, 1, 2),
              (91, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cardigan Red  (Quartz)', 73, 0, 1, 2),
              (92, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Carlisle Gray  (Quartz)', 73, 0, 1, 2),
              (93, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Carmanthen Brown  (Quartz)', 73, 0, 1, 2),
              (94, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Castell  (Quartz)', 73, 0, 1, 2),
              (95, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Charston  (Quartz)', 73, 0, 1, 2),
              (96, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Chatham  (Quartz)', 73, 0, 1, 2),
              (97, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cherrybrook  (Quartz)', 73, 0, 1, 2),
              (98, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Clyde  (Quartz)', 73, 0, 1, 2),
              (99, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Collybrooke  (Quartz)', 73, 0, 1, 2),
              (100, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Coswell Cream  (Quartz)', 73, 0, 1, 2),
              (101, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cranbrook  (Quartz)', 73, 0, 1, 2),
              (102, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cuddington  (Quartz)', 73, 0, 1, 2),
              (103, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Darlington  (Quartz)', 73, 0, 1, 2),
              (104, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Devon  (Quartz)', 73, 0, 1, 2),
              (105, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Dovedale  (Quartz)', 73, 0, 1, 2),
              (106, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Dover  (Quartz)', 73, 0, 1, 2),
              (107, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Durham   (Quartz)', 73, 0, 1, 2),
              (108, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Edinburough  (Quartz)', 73, 0, 1, 2),
              (109, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Ferndale  (Quartz)', 73, 0, 1, 2),
              (110, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Fieldstone  (Quartz)', 73, 0, 1, 2),
              (111, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Flint Black  (Quartz)', 73, 0, 1, 2),
              (112, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Greystone  (Quartz)', 73, 0, 1, 2),
              (113, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Halstead  (Quartz)', 73, 0, 1, 2),
              (114, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Hamilton  (Quartz)', 73, 0, 1, 2),
              (115, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Hazelford  (Quartz)', 73, 0, 1, 2),
              (116, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Henley  (Quartz)', 73, 0, 1, 2),
              (117, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Hollinsbrook  (Quartz)', 73, 0, 1, 2),
              (118, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Hide Park  (Quartz)', 73, 0, 1, 2),
              (119, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Kensington  (Quartz)', 73, 0, 1, 2),
              (120, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Kingston  (Quartz)', 73, 0, 1, 2),
              (121, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Lancaster  (Quartz)', 73, 0, 1, 2),
              (122, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Laneshaw  (Quartz)', 73, 0, 1, 2),
              (123, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Linconshire  (Quartz)', 73, 0, 1, 2),
              (124, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Linwood  (Quartz)', 73, 0, 1, 2),
              (125, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Minera  (Quartz)', 73, 0, 1, 2),
              (126, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'New Brighton  (Quartz)', 73, 0, 1, 2),
              (127, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'New Castle  (Quartz)', 73, 0, 1, 2),
              (128, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'New Quay  (Quartz)', 65, 0, 1, 2),
              (129, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Newhaven  (Quartz)', 73, 0, 1, 2),
              (130, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Newport  (Quartz)', 65, 0, 1, 2),
              (131, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Nottingham  (Quartz)', 73, 0, 1, 2),
              (132, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Oakhampton  (Quartz)', 73, 0, 1, 2),
              (133, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Oxford  (Quartz)', 73, 0, 1, 2),
              (134, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Oxwhich Green  (Quartz)', 73, 0, 1, 2),
              (135, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Park Gate  (Quartz)', 73, 0, 1, 2),
              (136, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Parys  (Quartz)', 73, 0, 1, 2),
              (137, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Praa Sands  (Quartz)', 73, 0, 1, 2),
              (138, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Preston  (Quartz)', 73, 0, 1, 2),
              (139, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Reading  (Quartz)', 73, 0, 1, 2),
              (140, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Rosslyn  (Quartz)', 73, 0, 1, 2),
              (141, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sanford  (Quartz)', 73, 0, 1, 2),
              (142, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sharpham  (Quartz)', 73, 0, 1, 2),
              (143, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sheffield  (Quartz)', 73, 0, 1, 2),
              (144, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Shirebrook  (Quartz)', 73, 0, 1, 2),
              (145, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Snowdon White  (Quartz)', 73, 0, 1, 2),
              (146, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Somerset  (Quartz)', 73, 0, 1, 2),
              (147, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Southhampton  (Quartz)', 73, 0, 1, 2),
              (148, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Stafford Brown  (Quartz)', 73, 0, 1, 2),
              (149, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sussex  (Quartz)', 73, 0, 1, 2),
              (150, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sutton  (Quartz)', 73, 0, 1, 2),
              (151, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Talbot Gray  (Quartz)', 73, 0, 1, 2),
              (152, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Templeton  (Quartz)', 73, 0, 1, 2),
              (153, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Tenby Cream  (Quartz)', 73, 0, 1, 2),
              (154, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Torquay  (Quartz)', 73, 0, 1, 2),
              (155, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Victoria  (Quartz)', 73, 0, 1, 2),
              (156, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Waterford  (Quartz)', 73, 0, 1, 2),
              (157, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Waverton  (Quartz)', 65, 0, 1, 2),
              (158, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Wellington  (Quartz)', 73, 0, 1, 2),
              (159, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Welshpool Black  (Quartz)', 73, 0, 1, 2),
              (160, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Westminster  (Quartz)', 73, 0, 1, 2),
              (161, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Cliff  (Quartz)', 73, 0, 1, 2),
              (162, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Whitehall  (Quartz)', 73, 0, 1, 2),
              (163, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Whitney  (Quartz)', 73, 0, 1, 2),
              (164, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Williston  (Quartz)', 73, 0, 1, 2),
              (165, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Wilshire Red', 73, 0, 1, 1),
              (166, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Winchester  (Quartz)', 73, 0, 1, 2),
              (167, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Windermere  (Quartz)', 73, 0, 1, 2),
              (168, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Windsor  (Quartz)', 73, 0, 1, 2),
              (169, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Yorkshire  (Quartz)', 73, 0, 1, 2),
              (170, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Creama Bordeaux', 56, 0, 1, 1),
              (171, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Reuse existing', 23, 0, 1, 1),
              (172, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Customer Using Own Granite Company', 0, 0, 1, 1),
              (173, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Mont Claire Dandy (Marble)', 52, 0, 1, 3),
              (174, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Cashmere Carrara (quartz)', 49, 0, 0, 2),
              (175, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Glazier White (Quartz)', 49, 0, 1, 2),
              (176, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Boletus (Quartz)', 49, 0, 1, 2),
              (177, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Mocato Brown (Quartz)', 49, 0, 1, 2),
              (178, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sparkling Black ', 49, 0, 1, 1),
              (179, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Customer yet to decide', 32, 0, 1, 1),
              (180, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Verona Ornamental', 36, 0, 1, 1),
              (181, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Montclair Damby (Honed) ', 49, 0, 1, 1),
              (182, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Arabescato Venato (Honed)', 53, 0, 1, 1),
              (183, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Carrarra', 42, 0, 0, 1),
              (184, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Edge Profiles Per LN FT / Full Bullnose', 10, 0, 1, 1),
              (185, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Carrara Grigio Quartz', 58, 0, 1, 2),
              (186, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Montclair White (Quartz)', 59, 0, 1, 2),
              (187, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Romano White (Quartz)', 59, 0, 1, 2),
              (188, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Tropical White Quartz', 70, 0, 1, 2),
              (189, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Moonlight', 36, 0, 1, 1),
              (190, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Ipanema White', 36, 0, 1, 1),
              (191, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Ming Gold', 36, 0, 1, 1),
              (192, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Via Lactea', 38, 0, 1, 1),
              (193, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Aspen White', 38, 0, 1, 1),
              (194, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Ornamental White', 36, 0, 1, 1),
              (195, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Butterfly Beige', 38, 0, 1, 1),
              (196, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Black Stripe', 38, 0, 1, 1),
              (197, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Napoleone', 38, 0, 1, 1),
              (198, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Sapphire Brown', 38, 0, 1, 1),
              (199, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Black Impala', 38, 0, 1, 1),
              (200, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Black Beauty', 38, 0, 1, 1),
              (201, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Giallo Bahia', 40, 0, 1, 1),
              (202, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'UbaTuba Leather', 40, 0, 1, 1),
              (203, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Aqua Marine', 42, 0, 1, 1),
              (204, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Colonial White', 42, 0, 1, 1),
              (205, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Colonial Gold', 42, 0, 0, 1),
              (206, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Gibhlee', 42, 0, 1, 1),
              (207, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Piracema White', 42, 0, 1, 1),
              (208, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Vyara Gold', 42, 0, 1, 1),
              (209, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Giallo Vizencia', 45, 0, 1, 1),
              (210, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'White Taupe', 50, 0, 1, 1),
              (211, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Silver Wave', 38, 0, 1, 1),
              (212, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Andino White', 50, 0, 1, 1),
              (213, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Astoria', 42, 0, 1, 1),
              (214, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Fantasy Brown ', 45, 0, 1, 1),
              (215, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Absolute Black – Honed', 53, 0, 1, 1),
              (216, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Himalaya White', 53, 0, 1, 1),
              (217, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Blue Pearl', 54, 0, 1, 1),
              (218, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Everest White', 54, 0, 1, 1),
              (219, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Neptune Bordeaux', 54, 0, 1, 1),
              (220, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'River Bordeaux', 51, 0, 1, 1),
              (221, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Arctic White ', 42, 0, 1, 1),
              (222, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'Bianco Antico', 42, 0, 1, 1),
              (223, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Nacarado', 90, 0, 1, 1),
              (224, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Crazy Horse', 51, 0, 1, 1),
              (225, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Crema Bordeaux', 56, 0, 1, 1),
              (226, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Lennon', 56, 0, 1, 1),
              (227, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Viscount White', 38, 0, 1, 1),
              (228, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Magma Gold', 58, 0, 1, 1),
              (229, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Delicatus Cream', 60, 0, 1, 1),
              (230, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Sienna Beige', 60, 0, 1, 1),
              (231, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Alaska White', 64, 0, 0, 1),
              (232, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Monte Carlo', 64, 0, 1, 1),
              (233, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Golden Crystal', 64, 0, 1, 1),
              (234, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Super White (Quartzite)', 96, 0, 1, 2),
              (235, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Blanc - Matte / 12mm', 131, 0, 1, 1),
              (236, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Palomino (Quartzite)', 78, 0, 1, 2),
              (237, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Roma Imperial (Quartzite)', 78, 0, 1, 2),
              (238, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Diane Royale', 46, 0, 0, 1),
              (239, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Vermont Damby', 46, 0, 1, 1),
              (240, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Napoli Classic', 32, 0, 1, 1),
              (241, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arabescus White', 56, 0, 1, 1),
              (242, '2018-05-22 01:36:11', '2018-05-22 01:36:11', '15 Year sealer – Per Sq Ft', 8, 0, 0, 1),
              (243, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tear Out Only / Granite', 3, 0, 1, 1),
              (244, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Re-Install', 23, 0, 1, 1),
              (245, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tear Out Only / Tiles', 4, 0, 1, 1),
              (246, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tear Out Only / Laminate or Corian Countertops', 3, 0, 1, 1),
              (247, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arena (Quartz)', 59, 0, 1, 2),
              (248, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Aspen (Quartz)', 59, 0, 1, 2),
              (249, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Aurum Ivory (Quartz)', 59, 0, 1, 2),
              (250, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Caramel Lux (Quartz)', 59, 0, 1, 2),
              (251, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Carrara Classic (Quartz)', 59, 0, 1, 2),
              (252, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Cinder (Quartz)', 59, 0, 1, 2),
              (253, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Cobble Stone (Quartz)', 59, 0, 1, 2),
              (254, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Dark Silt (Quartz)', 59, 0, 1, 2),
              (255, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Dove Grey (Quartz)', 59, 0, 1, 2),
              (256, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Espresso (Quartz)', 59, 0, 1, 2),
              (257, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Galation (Quartz)', 59, 0, 1, 2),
              (258, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Heirloom Grey (Quartz)', 59, 0, 1, 2),
              (259, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Hotch (Quartz)', 59, 0, 1, 2),
              (260, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Kona Dragon (Quartz)', 59, 0, 1, 2),
              (261, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Light Silt (Quartz)', 59, 0, 1, 2),
              (262, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Linen (Quartz)', 59, 0, 1, 2),
              (263, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Madison Black (Quartz)', 59, 0, 1, 2),
              (264, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Marbella White (Quartz)', 58, 0, 1, 2),
              (265, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Matterhorn (Quartz', 59, 0, 1, 2),
              (266, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Matterhorn (Quartz)', 59, 0, 1, 2),
              (267, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Midnight Black (Quartz)', 59, 0, 1, 2),
              (268, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Miele (Quartz)', 59, 0, 1, 2),
              (269, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Mocha (Quartz)', 59, 0, 1, 2),
              (270, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Namaste (Quartz)', 59, 0, 1, 2),
              (271, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Pebble Beach (Quartz)', 59, 0, 1, 2),
              (272, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Perlato (Quartz)', 59, 0, 1, 2),
              (273, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Roxy (Quartz)', 59, 0, 1, 2),
              (274, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Silver Mountain (Quartz)', 59, 0, 1, 2),
              (275, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Silver Water (Quartz)', 59, 0, 1, 2),
              (276, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Spring Valley (Quartz)', 59, 0, 1, 2),
              (277, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Stary Night (Quartz)', 59, 0, 1, 2),
              (278, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Summer Rain  (Quartz)', 59, 0, 1, 2),
              (279, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Sundance  (Quartz)', 59, 0, 1, 2),
              (280, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Toffee  (Quartz)', 59, 0, 1, 2),
              (281, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Thunder Road (Quartz)', 59, 0, 1, 2),
              (282, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tropic Storm (Quartz)', 59, 0, 1, 2),
              (283, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tropical White (Quartz)', 59, 0, 1, 2),
              (284, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Alpine Spring (Quartz)', 59, 0, 1, 2),
              (285, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Venato Extra (Quartz)', 59, 0, 1, 2),
              (286, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'White Lace (Quartz)', 59, 0, 1, 2),
              (287, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'White Zen (Quartz)', 59, 0, 1, 2),
              (288, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Yukon Gold (Quartz)', 59, 0, 1, 2),
              (289, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Dallas White', 32, 0, 1, 1),
              (290, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Baltic Brown', 32, 0, 1, 1),
              (291, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Peppercorn White ', 27, 0, 0, 1),
              (292, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Cariooca Gold (Golden Antique)', 36, 0, 1, 1),
              (293, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Golden Coast', 34, 0, 1, 1),
              (294, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'New Caledonia', 32, 0, 1, 1),
              (295, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Verde Butterfly', 32, 0, 1, 1),
              (296, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arabesco', 34, 0, 1, 1),
              (297, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Butterfly Gold', 34, 0, 1, 1),
              (298, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Toffee', 34, 0, 1, 1),
              (299, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Yellow Supreme (Giallo Vicenza', 34, 0, 1, 1),
              (300, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Venitian Ice', 34, 0, 1, 1),
              (301, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Cristalino', 35, 0, 1, 1),
              (302, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Azul Platino', 35, 0, 1, 1),
              (303, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Black Pearl / Black Aracruz', 40, 0, 1, 1),
              (304, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Suede Brown', 48, 0, 1, 1),
              (305, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Angola Black', 48, 0, 1, 1),
              (306, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'River White/New River White', 50, 0, 1, 1),
              (307, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Green (Soapstone)', 74, 0, 1, 1),
              (308, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Travertino', 45, 0, 1, 1),
              (309, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Gray Lagoon (Quartz)', 58, 0, 1, 2),
              (310, '2018-05-22 01:36:11', '2018-05-22 01:36:11', '15 Year Sealer - Per SF', 8, 0, 1, 1),
              (311, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Santa Cecilia Light', 32, 0, 1, 1),
              (312, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Santa Cecilia White', 32, 0, 1, 1),
              (313, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Napoli White', 32, 0, 1, 1),
              (314, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Colonial White Leathered', 50, 0, 1, 1),
              (315, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Lapidus', 70, 0, 1, 1),
              (316, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Britannica', 90, 0, 1, 1),
              (317, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arabescato', 70, 0, 1, 1),
              (318, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Demi Bullnose Edge', 5, 0, 1, 1),
              (319, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Calacatta Vintage', 59, 0, 1, 1),
              (320, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Botticino Quartz', 50, 0, 1, 2),
              (321, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Crystal Pepper Quartz', 50, 0, 1, 2),
              (322, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Crystal Reef Quartz', 50, 0, 1, 2),
              (323, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Crystal Salt Quartz', 50, 0, 1, 2),
              (324, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Sahara Quartz', 50, 0, 1, 2),
              (325, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Ornamental', 32, 0, 1, 1),
              (326, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Ogee Bullnose', 20, 0, 1, 1),
              (327, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Calacatta Gold Suede Finish', 105, 0, 1, 1),
              (328, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Torroncino', 55, 0, 1, 1),
              (329, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Salinas White', 54, 0, 1, 1),
              (330, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Misterio Quartz', 78, 0, 1, 2),
              (331, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'White Attica', 88, 0, 1, 1),
              (332, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Athena (Vadera Quart)', 75, 0, 1, 1),
              (333, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Silver Cloud', 42, 0, 1, 1),
              (334, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Alaskan White ', 45, 0, 1, 1),
              (335, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Brazilian Travertine ', 45, 0, 0, 1),
              (336, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tuscany Classic ', 45, 0, 0, 1),
              (337, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tuscany Ivory ', 45, 0, 0, 1),
              (338, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Durango / Travertine Classic', 45, 0, 1, 1),
              (339, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Black / Soapstone', 74, 0, 1, 1),
              (340, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Durango / Travertine Classic', 45, 0, 0, 1),
              (341, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Grey / Soapstone', 74, 0, 1, 1),
              (342, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Bianco Romano', 51, 0, 1, 1),
              (343, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Blue Dunes', 45, 0, 1, 1),
              (344, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Luna Pearl ', 32, 0, 1, 1),
              (345, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Black Pearl ', 35, 0, 1, 1),
              (346, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Taj Mahal', 106, 0, 1, 1),
              (347, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Madreperola', 106, 0, 1, 1),
              (348, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Fusion', 125, 0, 1, 1),
              (349, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'White Macaubas', 125, 0, 1, 1),
              (350, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Bianco Carrara', 131, 0, 1, 1),
              (351, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Travertino Navona', 131, 0, 1, 1),
              (352, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Aura', 131, 0, 1, 1),
              (353, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'TriLium', 131, 0, 1, 1),
              (354, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Calacatta - Matte', 131, 0, 1, 1),
              (355, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Fossil Gray', 42, 0, 1, 1),
              (356, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tuscany Ivory / Travertine', 45, 0, 1, 1),
              (357, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Tuscany Classic / Travertine', 45, 0, 1, 1),
              (358, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Brazilian / Travertine', 45, 0, 1, 1),
              (359, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Blanca Statuarietto Quartz ', 59, 0, 1, 2),
              (360, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'White Ice', 45, 0, 1, 1),
              (361, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Frost White (Quartz)', 39, 0, 1, 2),
              (362, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Pebble Rock (Quartz)', 39, 0, 1, 2),
              (363, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Armond Roca (Quartz)', 39, 0, 1, 2),
              (364, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arctic White (Quartz)', 42, 0, 1, 2),
              (365, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Peppercorn White (Quartz)', 42, 0, 1, 2),
              (366, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Blanco Maple Jumbo (Quartz)', 46, 0, 1, 2),
              (367, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Gray Expo (Quartz)', 46, 0, 1, 2),
              (368, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Miami White (Quartz)', 46, 0, 1, 2),
              (369, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Artic White (Marble)', 46, 0, 1, 3),
              (370, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Babylon Gray', 58, 0, 1, 1),
              (371, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Diana Royal (Marble)', 46, 0, 1, 3),
              (372, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Cashmere Carrara (Quartz)', 58, 0, 1, 2),
              (373, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arabescato Venato (Marble)', 46, 0, 1, 3),
              (374, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Pietra (Quartz)', 68, 0, 1, 2),
              (375, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Lusso (Quartz)', 68, 0, 1, 2),
              (376, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Pearl Jasmine (Quartz)', 68, 0, 1, 2),
              (377, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Lyra (Quartz)', 68, 0, 1, 2),
              (378, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Stellar Snow  (Quartz)', 68, 0, 1, 2),
              (379, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Eternal Calacatta Gold (Quartz)', 84, 0, 1, 2),
              (380, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Charcoal Soapstone Suede (Quartz)', 84, 0, 1, 2),
              (381, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'New Super White (Marble)', 46, 0, 1, 3),
              (382, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Shadow Storm (Marble)', 52, 0, 1, 3),
              (383, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Arctic White / Quartz', 42, 0, 0, 2),
              (384, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Fossil Gray / Quartz', 42, 0, 0, 2),
              (385, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Carrara Marmi / Quartz', 42, 0, 0, 2),
              (386, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Peppercorn White / Quartz', 42, 0, 0, 2),
              (387, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Blanco Maple Jumbo / Quartz', 46, 0, 0, 2),
              (388, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Gray Expo / Quartz', 46, 0, 0, 2),
              (389, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Miami White / Quartz', 46, 0, 0, 2),
              (390, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Sahara Beige / Quartz', 58, 0, 0, 2),
              (391, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Babylon Gray / Quartz', 58, 0, 0, 2),
              (392, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'White Supreme', 56, 0, 1, 1),
              (393, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Venatino (Vicostone) ', 85, 0, 1, 1),
              (394, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Blanca Arabescato', 59, 0, 1, 1),
              (395, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Calacatta Laza Quartz (MSI)', 59, 0, 1, 2),
              (396, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Giallo Victoria   ', 36, 0, 1, 1),
              (397, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Giallo Verona PTC only', 32, 0, 1, 1),
              (398, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Normandy ', 55, 0, 1, 1),
              (399, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Alpine White', 50, 0, 1, 1),
              (400, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Bianco Venous', 38, 0, 1, 1),
              (401, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Walnut Luster', 80, 0, 1, 1),
              (402, '2018-05-22 01:36:11', '2018-05-22 01:36:11', 'Napoli Light', 32, 0, 1, 1);
            ");

            //FK3 - Table Countertop_types
            CountertopType::truncate();
            DB::insert("
              INSERT INTO `countertop_types` (`id`, `created_at`, `updated_at`, `name`) VALUES
              (1, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'granite'),
              (2, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'quartz'),
              (3, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'marble'),
              (4, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'concrete'),
              (5, '2018-05-22 01:36:10', '2018-05-22 01:36:10', 'wood');
            ");

            //FK3 - Table Customers
            Customer::truncate();
            $fk2_customers_total = DB::connection($this->oldDb)->select('select count(*) as total_data from customers');
            $loop = (integer)ceil($fk2_customers_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_customers = DB::connection($this->oldDb)->select('select customers.*,  contacts.email, contacts.mobile, contacts.home, contacts.alternate from customers left join contacts on customers.id = contacts.customer_id order by customers.id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_customers as $fk2_customer)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_customer->id;
                  $insertDataArr['name'] = $fk2_customer->name;
                  $insertDataArr['address'] = $fk2_customer->address;
                  $insertDataArr['city'] = $fk2_customer->city;
                  $insertDataArr['state'] = $fk2_customer->state;
                  $insertDataArr['zip'] = $fk2_customer->zip;
                  $insertDataArr['email'] = $fk2_customer->email;
                  $insertDataArr['mobile'] = $fk2_customer->mobile;
                  $insertDataArr['home'] = $fk2_customer->home;
                  $insertDataArr['alternate'] = $fk2_customer->alternate;
                  $insertDataArr['job_address'] = $fk2_customer->job_address;
                  $insertDataArr['job_city'] = $fk2_customer->job_city;
                  $insertDataArr['job_state'] = $fk2_customer->job_state;
                  $insertDataArr['job_zip'] = $fk2_customer->job_zip;
                  $insertDataArr['created_at'] = $fk2_customer->created_at;
                  $insertDataArr['updated_at'] = $fk2_customer->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Customer::insert($insertArr);
            }

            //FK3 - Table Extras
            Extra::truncate();
            $fk2_extras_total = DB::connection($this->oldDb)->select('select count(*) as total_data from extras');
            $loop = (integer)ceil($fk2_extras_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_extras = DB::connection($this->oldDb)->select('select * from extras order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_extras as $fk2_extra)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_extra->id;
                  $insertDataArr['name'] = $fk2_extra->name;
                  $insertDataArr['price'] = $fk2_extra->price;
                  $insertDataArr['active'] = $fk2_extra->active;
                  $insertDataArr['user_id'] = '0';
                  $insertDataArr['group_id'] = $fk2_extra->designation_id;
                  $insertDataArr['deleted_at'] = $fk2_extra->deleted_at;
                  $insertDataArr['created_at'] = $fk2_extra->created_at;
                  $insertDataArr['updated_at'] = $fk2_extra->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Extra::insert($insertArr);
            }

            //FK3 - Table Quote Types
            QuoteType::truncate();
            DB::insert("
              INSERT INTO `quote_types` (`id`, `created_at`, `updated_at`, `name`, `active`, `cabinets`, `countertops`, `sinks`, `appliances`, `accessories`, `hardware`, `led`, `tile`, `addons`, `responsibilities`, `questionaire`, `buildup`, `contract`, `default_days`) VALUES
                (1, '2018-05-30 01:03:07', '2018-05-30 01:03:53', 'Full Kitchen', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 0),
                (2, '2018-05-30 01:03:07', '2018-05-30 01:03:53', 'Cabinet and Install', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 0),
                (3, '2018-05-30 01:03:07', '2018-05-30 01:03:53', 'Granite Only', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 0),
                (4, '2018-07-30 17:00:00', '2018-07-30 17:00:00', 'Cabinet Only', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 0),
                (5, '2018-07-30 17:00:00', '2018-07-30 17:00:00', 'Cabinet Small Job', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 0),
                (6, '2018-07-30 17:00:00', '2018-07-30 17:00:00', 'Builder', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 0);
                      ");

            //FK3 - Table Faqs
            Faq::truncate();
            $fk2_faqs_total = DB::connection($this->oldDb)->select('select count(*) as total_data from faqs');
            $loop = (integer)ceil($fk2_faqs_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_faqs = DB::connection($this->oldDb)->select('select * from faqs order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_faqs as $fk2_faq)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_faq->id;
                  $insertDataArr['question'] = $fk2_faq->question;
                  $insertDataArr['answer'] = $fk2_faq->answer;
                  $insertDataArr['image'] = $fk2_faq->image;
                  $insertDataArr['figure'] = $fk2_faq->figure;

                  $quoteType = QuoteType::where('name', $fk2_faq->type)->first();
                  if(!$quoteType) $insertDataArr['quote_type_id'] = '0';
                  else $insertDataArr['quote_type_id'] = $quoteType->id;

                  $insertDataArr['active'] = $fk2_faq->active;
                  $insertDataArr['created_at'] = $fk2_faq->created_at;
                  $insertDataArr['updated_at'] = $fk2_faq->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Faq::insert($insertArr);
            }

            //FK3 - Ffts
            Fft::truncate();
            $fk2_ffts_total = DB::connection($this->oldDb)->select('select count(*) as total_data from ffts');
            $loop = (integer)ceil($fk2_ffts_total[0]->total_data / 100);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_ffts = DB::connection($this->oldDb)->select('select * from ffts order by id asc LIMIT 100 OFFSET ' . ($x * 100));

                $insertArr = array();
                foreach($fk2_ffts as $fk2_fft)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_fft->id;
                  $insertDataArr['user_id'] = $fk2_fft->user_id;
                  $insertDataArr['job_id'] = $fk2_fft->job_id;
                  $insertDataArr['warranty'] = $fk2_fft->warranty;
                  $insertDataArr['notes'] = $fk2_fft->notes;
                  $insertDataArr['closed'] = $fk2_fft->closed;
                  ($fk2_fft->schedule_start != '0000-00-00 00:00:00') ? $insertDataArr['schedule_start'] = $fk2_fft->schedule_start : $insertDataArr['schedule_start'] = null;
                  ($fk2_fft->schedule_end != '0000-00-00 00:00:00') ? $insertDataArr['schedule_end'] = $fk2_fft->schedule_end : $insertDataArr['schedule_end'] = null;
                  ($fk2_fft->pre_schedule_start != '0000-00-00 00:00:00') ? $insertDataArr['pre_schedule_start'] = $fk2_fft->pre_schedule_start : $insertDataArr['pre_schedule_start'] = null;
                  ($fk2_fft->pre_schedule_end != '0000-00-00 00:00:00') ? $insertDataArr['pre_schedule_end'] = $fk2_fft->pre_schedule_end : $insertDataArr['pre_schedule_end'] = null;
                  $insertDataArr['pre_assigned'] = $fk2_fft->pre_assigned;
                  ($fk2_fft->signed != '0000-00-00 00:00:00') ? $insertDataArr['signed'] = $fk2_fft->signed : $insertDataArr['signed'] = null;
                  $insertDataArr['signature'] = $fk2_fft->signature;
                  $insertDataArr['hours'] = $fk2_fft->hours;
                  $insertDataArr['customer_id'] = $fk2_fft->customer_id;
                  $insertDataArr['payment'] = $fk2_fft->payment;
                  $insertDataArr['ordered_email'] = $fk2_fft->ordered_email;
                  $insertDataArr['signoff'] = $fk2_fft->signoff;
                  ($fk2_fft->signoff_stamp != '0000-00-00 00:00:00') ? $insertDataArr['signoff_stamp'] = $fk2_fft->signoff_stamp : $insertDataArr['signoff_stamp'] = null;
                  $insertDataArr['warranty_notes'] = $fk2_fft->warranty_notes;
                  $insertDataArr['paid'] = $fk2_fft->paid;
                  $insertDataArr['paid_reason'] = $fk2_fft->paid_reason;
                  $insertDataArr['punch_reminder_emailed'] = $fk2_fft->punch_reminder_emailed;
                  $insertDataArr['service'] = $fk2_fft->service;
                  $insertDataArr['deleted_at'] = $fk2_fft->deleted_at;
                  $insertDataArr['created_at'] = $fk2_fft->created_at;
                  $insertDataArr['updated_at'] = $fk2_fft->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Fft::insert($insertArr);
            }

            //FK3 - Table Fft Notes
            FftNote::truncate();
            $fk2_fft_notes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from fft_notes');
            $loop = (integer)ceil($fk2_fft_notes_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_fft_notes = DB::connection($this->oldDb)->select('select * from fft_notes order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_fft_notes as $fk2_fft_note)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_fft_note->id;
                  $insertDataArr['user_id'] = $fk2_fft_note->user_id;
                  $insertDataArr['fft_id'] = $fk2_fft_note->fft_id;
                  $insertDataArr['note'] = $fk2_fft_note->note;
                  $insertDataArr['created_at'] = $fk2_fft_note->created_at;
                  $insertDataArr['updated_at'] = $fk2_fft_note->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                FftNote::insert($insertArr);
            }

            //FK3 - Table Files
            File::truncate();
            $fk2_files_total = DB::connection($this->oldDb)->select('select count(*) as total_data from files');
            $loop = (integer)ceil($fk2_files_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_files = DB::connection($this->oldDb)->select('select * from files order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_files as $fk2_file)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_file->id;
                  $insertDataArr['location'] = $fk2_file->location;
                  $insertDataArr['description'] = $fk2_file->description;
                  $insertDataArr['user_id'] = $fk2_file->user_id;
                  $insertDataArr['quote_id'] = $fk2_file->quote_id;
                  $insertDataArr['attached'] = $fk2_file->attached;
                  $insertDataArr['deleted_at'] = $fk2_file->deleted_at;
                  $insertDataArr['created_at'] = $fk2_file->created_at;
                  $insertDataArr['updated_at'] = $fk2_file->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                File::insert($insertArr);
            }

            //FK3 - Table Followup
            Followup::truncate();
            $fk2_followups_total = DB::connection($this->oldDb)->select('select count(*) as total_data from followups');
            $loop = (integer)ceil($fk2_followups_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_followups = DB::connection($this->oldDb)->select('select * from followups order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_followups as $fk2_followup)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_followup->id;
                  $insertDataArr['stage'] = $fk2_followup->stage;
                  $insertDataArr['lead_id'] = $fk2_followup->lead_id;
                  $insertDataArr['status_id'] = $fk2_followup->status_id;
                  $insertDataArr['user_id'] = $fk2_followup->user_id;
                  $insertDataArr['comments'] = $fk2_followup->comments;
                  $insertDataArr['closed'] = $fk2_followup->closed;
                  $insertDataArr['created_at'] = $fk2_followup->created_at;
                  $insertDataArr['updated_at'] = $fk2_followup->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Followup::insert($insertArr);
            }

            //FK3 - Table Granites
            Granite::truncate();
            $fk2_granites_total = DB::connection($this->oldDb)->select('select count(*) as total_data from granites');
            $loop = (integer)ceil($fk2_granites_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_granites = DB::connection($this->oldDb)->select('select * from granites order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_granites as $fk2_granite)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_granite->id;
                  $insertDataArr['name'] = $fk2_granite->name;
                  $insertDataArr['price'] = $fk2_granite->price;
                  $insertDataArr['removal_price'] = $fk2_granite->removal_price;
                  $insertDataArr['active'] = $fk2_granite->active;
                  $insertDataArr['deleted_at'] = $fk2_granite->deleted_at;
                  $insertDataArr['created_at'] = $fk2_granite->created_at;
                  $insertDataArr['updated_at'] = $fk2_granite->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Granite::insert($insertArr);
            }

            //FK3 - Table Groups
            Group::truncate();
            DB::insert("
              INSERT INTO `groups` (`id`, `created_at`, `updated_at`, `name`) VALUES
                (1, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Plumber'),
                (2, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Electrician'),
                (3, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Granite'),
                (4, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Cabinet Installer'),
                (5, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'FFT Contractor'),
                (6, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Follow up'),
                (7, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Customer'),
                (8, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Frugal Kitchens Contractor'),
                (9, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Shipping'),
                (10, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'SubContractor'),
                (11, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Flooring Contractor/Backsplash Contractor'),
                (12, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Orders'),
                (13, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Admin'),
                (14, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Digital Measure'),
                (15, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Painter'),
                (16, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Sheetrock'),
                (17, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Demo'),
                (18, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Construction PM'),
                (19, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Office Manger'),
                (20, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Designers'),
                (21, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Carpenter'),
                (22, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Customer Service'),
                (23, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Scheduler'),
                (24, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Frugal Contractor'),
                (25, '2018-05-22 01:36:07', '2018-05-22 01:36:07', 'Duct Work');
            ");

            //FK3 - Table Group Acls
            GroupAcl::truncate();
            DB::insert("
            INSERT INTO `group_acls` (`id`, `created_at`, `updated_at`, `group_id`, `acl_id`, `read`, `write`, `delete`) VALUES
              (1, '2018-05-22 01:53:04', '2018-05-22 01:53:04', 13, 1, 0, 0, 0),
              (2, '2018-05-22 01:53:04', '2018-05-22 01:53:04', 13, 2, 0, 0, 0);
            ");

            //FK3 - Table Hardwares
            Hardware::truncate();
            $fk2_hardwares_total = DB::connection($this->oldDb)->select('select count(*) as total_data from hardwares');
            $loop = (integer)ceil($fk2_hardwares_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_hardwares = DB::connection($this->oldDb)->select('select * from hardwares order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_hardwares as $fk2_hardware)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_hardware->id;
                  $insertDataArr['sku'] = $fk2_hardware->sku;
                  $insertDataArr['description'] = $fk2_hardware->description;
                  $insertDataArr['vendor_id'] = $fk2_hardware->vendor_id;
                  $insertDataArr['price'] = $fk2_hardware->price;
                  $insertDataArr['active'] = $fk2_hardware->active;
                  $insertDataArr['image'] = $fk2_hardware->image;
                  $insertDataArr['created_at'] = $fk2_hardware->created_at;
                  $insertDataArr['updated_at'] = $fk2_hardware->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Hardware::insert($insertArr);
            }

            //FK3 - Table Jobs
            Job::truncate();
            $fk2_jobs_total = DB::connection($this->oldDb)->select('select count(*) as total_data from jobs');
            $loop = (integer)ceil($fk2_jobs_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_jobs = DB::connection($this->oldDb)->select('select * from jobs order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_jobs as $fk2_job)
                {
                  $insertDataArr = array();
                  $insertDataArr['id'] = $fk2_job->id;
                  $insertDataArr['quote_id'] = $fk2_job->quote_id;
                  ($fk2_job->contract_date != '0000-00-00') ? $insertDataArr['contract_date'] = $fk2_job->contract_date : $insertDataArr['contract_date'] = null;
                  ($fk2_job->start_date != '0000-00-00') ? $insertDataArr['start_date'] = $fk2_job->start_date : $insertDataArr['start_date'] = null;
                  ($fk2_job->closed_on != '0000-00-00 00:00:00') ? $insertDataArr['closed_on'] = $fk2_job->closed_on : $insertDataArr['closed_on'] = null;
                  $insertDataArr['meta'] = $fk2_job->meta;
                  $insertDataArr['paid'] = $fk2_job->paid;
                  $insertDataArr['locked'] = $fk2_job->locked;
                  $insertDataArr['has_money'] = $fk2_job->has_money;
                  $insertDataArr['construction'] = $fk2_job->construction;
                  $insertDataArr['schedules_sent'] = $fk2_job->schedules_sent;
                  $insertDataArr['schedules_confirmed'] = $fk2_job->schedules_confirmed;
                  $insertDataArr['built'] = $fk2_job->built;
                  $insertDataArr['loaded'] = $fk2_job->loaded;
                  ($fk2_job->schedule_sent_on != '0000-00-00 00:00:00') ? $insertDataArr['schedule_sent_on'] = $fk2_job->schedule_sent_on : $insertDataArr['schedule_sent_on'] = null;
                  $insertDataArr['truck_left'] = $fk2_job->truck_left;
                  $insertDataArr['reviewed'] = $fk2_job->reviewed;
                  $insertDataArr['payout_additionals'] = $fk2_job->payout_additionals;
                  $insertDataArr['sent_cabinet_arrival'] = $fk2_job->sent_cabinet_arrival;
                  $insertDataArr['deleted_at'] = $fk2_job->deleted_at;
                  $insertDataArr['created_at'] = $fk2_job->created_at;
                  $insertDataArr['updated_at'] = $fk2_job->updated_at;
                  $insertArr[] = $insertDataArr;
                }
                Job::insert($insertArr);
            }

            //FK3 - Table Job Items
            JobItem::truncate();
            $fk2_job_items_total = DB::connection($this->oldDb)->select('select count(*) as total_data from job_items');
            $loop = (integer)ceil($fk2_job_items_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_job_items = DB::connection($this->oldDb)->select('select * from job_items order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_job_items as $fk2_job_item)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_job_item->id;
                    $insertDataArr['job_id'] = $fk2_job_item->job_id;
                    $insertDataArr['instanceof'] = $fk2_job_item->instanceof;
                    $insertDataArr['reference'] = $fk2_job_item->reference;
                    ($fk2_job_item->ordered != '0000-00-00') ? $insertDataArr['ordered'] = $fk2_job_item->ordered : $insertDataArr['ordered'] = null;
                    ($fk2_job_item->confirmed != '0000-00-00') ? $insertDataArr['confirmed'] = $fk2_job_item->confirmed : $insertDataArr['confirmed'] = null;
                    ($fk2_job_item->received != '0000-00-00') ? $insertDataArr['received'] = $fk2_job_item->received : $insertDataArr['received'] = null;
                    ($fk2_job_item->verified != '0000-00-00') ? $insertDataArr['verified'] = $fk2_job_item->verified : $insertDataArr['verified'] = null;
                    $insertDataArr['orderable'] = $fk2_job_item->orderable;
                    $insertDataArr['meta'] = $fk2_job_item->meta;
                    $insertDataArr['hours'] = $fk2_job_item->hours;
                    $insertDataArr['replacement'] = $fk2_job_item->replacement;
                    $insertDataArr['notes'] = $fk2_job_item->notes;
                    $insertDataArr['contractor_notes'] = $fk2_job_item->contractor_notes;
                    $insertDataArr['contractor_complete'] = $fk2_job_item->contractor_complete;
                    $insertDataArr['image1'] = $fk2_job_item->image1;
                    $insertDataArr['image2'] = $fk2_job_item->image2;
                    $insertDataArr['image3'] = $fk2_job_item->image3;
                    $insertDataArr['po_item_id'] = $fk2_job_item->po_item_id;
                    $insertDataArr['group_id'] = $fk2_job_item->designation_id;
                    $insertDataArr['deleted_at'] = $fk2_job_item->deleted_at;
                    $insertDataArr['created_at'] = $fk2_job_item->created_at;
                    $insertDataArr['updated_at'] = $fk2_job_item->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                JobItem::insert($insertArr);
            }

            //FK3 - Table Job Notes
            JobNote::truncate();
            $fk2_job_notes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from job_notes');
            $loop = (integer)ceil($fk2_job_notes_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_job_notes = DB::connection($this->oldDb)->select('select * from job_notes order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_job_notes as $fk2_job_note)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_job_note->id;
                    $insertDataArr['user_id'] = $fk2_job_note->user_id;
                    $insertDataArr['job_id'] = $fk2_job_note->job_id;
                    $insertDataArr['note'] = $fk2_job_note->note;
                    $insertDataArr['created_at'] = $fk2_job_note->created_at;
                    $insertDataArr['updated_at'] = $fk2_job_note->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                JobNote::insert($insertArr);
            }

            //FK3 - Table Job Schedule
            JobSchedule::truncate();
            $fk2_job_schedules_total = DB::connection($this->oldDb)->select('select count(*) as total_data from job_schedules');
            $loop = (integer)ceil($fk2_job_schedules_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_job_schedules = DB::connection($this->oldDb)->select('select * from job_schedules order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_job_schedules as $fk2_job_schedule)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_job_schedule->id;
                    ($fk2_job_schedule->start != '0000-00-00 00:00:00') ? $insertDataArr['start'] = $fk2_job_schedule->start : $insertDataArr['start'] = null;
                    ($fk2_job_schedule->end != '0000-00-00 00:00:00') ? $insertDataArr['end'] = $fk2_job_schedule->end : $insertDataArr['end'] = null;
                    $insertDataArr['group_id'] = $fk2_job_schedule->designation_id;
                    $insertDataArr['user_id'] = $fk2_job_schedule->user_id;
                    $insertDataArr['job_id'] = $fk2_job_schedule->job_id;
                    $insertDataArr['complete'] = $fk2_job_schedule->complete;
                    $insertDataArr['sent'] = $fk2_job_schedule->sent;
                    $insertDataArr['notes'] = $fk2_job_schedule->notes;
                    $insertDataArr['aux'] = $fk2_job_schedule->aux;
                    $insertDataArr['customer_notes'] = $fk2_job_schedule->customer_notes;
                    $insertDataArr['default_email'] = $fk2_job_schedule->default_email;
                    $insertDataArr['locked'] = $fk2_job_schedule->locked;
                    $insertDataArr['contractor_notes'] = $fk2_job_schedule->contractor_notes;
                    $insertDataArr['created_at'] = $fk2_job_schedule->created_at;
                    $insertDataArr['updated_at'] = $fk2_job_schedule->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                JobSchedule::insert($insertArr);
            }

            //FK3 - Table Lead Sources
            LeadSource::truncate();
            $fk2_lead_sources_total = DB::connection($this->oldDb)->select('select count(*) as total_data from sources');
            $loop = (integer)ceil($fk2_lead_sources_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_lead_sources = DB::connection($this->oldDb)->select('select * from sources order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_lead_sources as $fk2_lead_source)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_lead_source->id;
                    $insertDataArr['name'] = $fk2_lead_source->type;
                    $insertDataArr['active'] = $fk2_lead_source->active;
                    $insertDataArr['created_at'] = $fk2_lead_source->created_at;
                    $insertDataArr['updated_at'] = $fk2_lead_source->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                LeadSource::insert($insertArr);
            }

            //FK3 - Table Locations
            Location::truncate();
            $fk2_showrooms = DB::connection($this->oldDb)->select('select distinct(location) from showrooms where location <> "" order by location asc');

            foreach($fk2_showrooms as $fk2_showroom)
            {
                $location = new Location();
                $location->name = $fk2_showroom->location;
                $location->address = '';
                $location->save();
            }

            //FK3 - Table Leads
            Lead::truncate();
            $fk2_leads_total = DB::connection($this->oldDb)->select('select count(*) as total_data from leads left join measures on leads.id = measures.lead_id left join closings on leads.id = closings.lead_id left join showrooms on leads.id = showrooms.lead_id');
            $loop = (integer)ceil($fk2_leads_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_leads = DB::connection($this->oldDb)->select('select leads.*, measures.scheduled as digital_scheduled, measures.measurer_id as digital_user_id, closings.scheduled as closing_scheduled, closings.user_id as closing_user_id, showrooms.scheduled as showroom_scheduled, showrooms.user_id as showroom_user_id, showrooms.location as showroom_location from leads left join measures on leads.id = measures.lead_id left join closings on leads.id = closings.lead_id left join showrooms on leads.id = showrooms.lead_id ORDER BY leads.id ASC LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_leads as $fk2_lead)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_lead->id;
                    $insertDataArr['customer_id'] = $fk2_lead->customer_id;
                    $insertDataArr['source_id'] = $fk2_lead->source_id;
                    $insertDataArr['user_id'] = $fk2_lead->user_id;
                    $insertDataArr['status_id'] = $fk2_lead->status_id;
                    $insertDataArr['showroom_user_id'] = ($fk2_lead->showroom_user_id ?: 0);
                    ($fk2_lead->showroom_scheduled != '0000-00-00 00:00:00') ? $insertDataArr['showroom_scheduled'] = $fk2_lead->showroom_scheduled : $insertDataArr['showroom_scheduled'] = null;
                    $location = Location::where('name', $fk2_lead->showroom_location)->first();
                    if($location) $insertDataArr['showroom_location_id'] = $location->id;
                    else $insertDataArr['showroom_location_id'] = null;
                    $insertDataArr['closing_user_id'] = ($fk2_lead->closing_user_id ?: 0);
                    $insertDataArr['closing_scheduled'] = $fk2_lead->closing_scheduled;
                    $insertDataArr['digital_user_id'] = ($fk2_lead->digital_user_id ?: 0);
                    ($fk2_lead->digital_scheduled != '0000-00-00 00:00:00') ? $insertDataArr['digital_scheduled'] = $fk2_lead->digital_scheduled : $insertDataArr['digital_scheduled'] = null;
                    $insertDataArr['title'] = $fk2_lead->title;
                    $insertDataArr['closed'] = $fk2_lead->closed;
                    $insertDataArr['archived'] = $fk2_lead->archived;
                    $insertDataArr['provided'] = $fk2_lead->provided;
                    $insertDataArr['last_status_by'] = $fk2_lead->last_status_by;
                    ($fk2_lead->last_note != '0000-00-00 00:00:00') ? $insertDataArr['last_note'] = $fk2_lead->last_note : $insertDataArr['last_note'] = null;
                    $insertDataArr['warning'] = $fk2_lead->warning;
                    $insertDataArr['created_at'] = $fk2_lead->created_at;
                    $insertDataArr['updated_at'] = $fk2_lead->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Lead::insert($insertArr);
            }

            //FK3 - Table Lead Notes
            LeadNote::truncate();
            $fk2_lead_notes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from lead_notes');
            $loop = (integer)ceil($fk2_lead_notes_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_lead_notes = DB::connection($this->oldDb)->select('select * from lead_notes order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_lead_notes as $fk2_lead_note)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_lead_note->id;
                    $insertDataArr['lead_id'] = $fk2_lead_note->lead_id;
                    $insertDataArr['note'] = $fk2_lead_note->note;
                    $insertDataArr['user_id'] = $fk2_lead_note->user_id;
                    $insertDataArr['created_at'] = $fk2_lead_note->created_at;
                    $insertDataArr['updated_at'] = $fk2_lead_note->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                LeadNote::insert($insertArr);
            }

            //FK3 - Table Lead updates
            LeadUpdate::truncate();
            $fk2_lead_updates_total = DB::connection($this->oldDb)->select('select count(*) as total_data from lead_updates');
            $loop = (integer)ceil($fk2_lead_updates_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_lead_updates = DB::connection($this->oldDb)->select('select * from lead_updates order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_lead_updates as $fk2_lead_update)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_lead_update->id;
                    $insertDataArr['lead_id'] = $fk2_lead_update->lead_id;
                    $insertDataArr['old_status'] = $fk2_lead_update->old_status;
                    $insertDataArr['status'] = $fk2_lead_update->status;
                    $insertDataArr['user_id'] = $fk2_lead_update->user_id;
                    $insertDataArr['created_at'] = $fk2_lead_update->created_at;
                    $insertDataArr['updated_at'] = $fk2_lead_update->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                LeadUpdate::insert($insertArr);
            }

            //FK3 - Table Notes
            Note::truncate();
            $fk2_notes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from notes');
            $loop = (integer)ceil($fk2_notes_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_notes = DB::connection($this->oldDb)->select('select * from notes order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_notes as $fk2_note)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_note->id;
                    $insertDataArr['note'] = $fk2_note->note;
                    $insertDataArr['user_id'] = $fk2_note->user_id;
                    $insertDataArr['customer_id'] = $fk2_note->customer_id;
                    $insertDataArr['created_at'] = $fk2_note->created_at;
                    $insertDataArr['updated_at'] = $fk2_note->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Note::insert($insertArr);
            }

            //FK3 - Table Notifications
            Notification::truncate();
            $fk2_notifications_total = DB::connection($this->oldDb)->select('select count(*) as total_data from notifications');
            $loop = (integer)ceil($fk2_notifications_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_notifications = DB::connection($this->oldDb)->select('select * from notifications order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_notifications as $fk2_notification)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_notification->id;
                    $insertDataArr['isFor'] = $fk2_notification->isFor;
                    $insertDataArr['reference'] = $fk2_notification->reference;
                    $insertDataArr['status_id'] = $fk2_notification->status_id;
                    $insertDataArr['expiration_id'] = $fk2_notification->expiration_id;
                    $insertDataArr['set'] = $fk2_notification->set;
                    ($fk2_notification->expires != '0000-00-00 00:00:00') ? $insertDataArr['expires'] = $fk2_notification->expires : $insertDataArr['expires'] = null;
                    $insertDataArr['followup_id'] = $fk2_notification->followup_id;
                    $insertDataArr['deleted_at'] = $fk2_notification->deleted_at;
                    $insertDataArr['created_at'] = $fk2_notification->created_at;
                    $insertDataArr['updated_at'] = $fk2_notification->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Notification::insert($insertArr);
            }

            //FK3 - Table Payouts
            Payout::truncate();
            $fk2_payouts_total = DB::connection($this->oldDb)->select('select count(*) as total_data from payouts');
            $loop = (integer)ceil($fk2_payouts_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_payouts = DB::connection($this->oldDb)->select('select * from payouts order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_payouts as $fk2_payout)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_payout->id;
                    $insertDataArr['user_id'] = $fk2_payout->user_id;
                    $insertDataArr['job_id'] = $fk2_payout->job_id;
                    $insertDataArr['paid'] = $fk2_payout->paid;
                    $insertDataArr['archived'] = $fk2_payout->archived;
                    $insertDataArr['approved'] = $fk2_payout->approved;
                    ($fk2_payout->paid_on != '0000-00-00 00:00:00') ? $insertDataArr['paid_on'] = $fk2_payout->paid_on : $insertDataArr['paid_on'] = null;
                    $insertDataArr['notes'] = $fk2_payout->notes;
                    $insertDataArr['check'] = $fk2_payout->check;
                    $insertDataArr['invoice'] = $fk2_payout->invoice;
                    $insertDataArr['total'] = $fk2_payout->total;
                    $insertDataArr['group_id'] = $fk2_payout->designation_id;
                    $insertDataArr['created_at'] = $fk2_payout->created_at;
                    $insertDataArr['updated_at'] = $fk2_payout->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Payout::insert($insertArr);
            }


            //FK3 - Table Payout Items
            PayoutItem::truncate();
            $fk2_payout_items_total = DB::connection($this->oldDb)->select('select count(*) as total_data from payout_items');
            $loop = (integer)ceil($fk2_payout_items_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_payout_items = DB::connection($this->oldDb)->select('select * from payout_items order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_payout_items as $fk2_payout_item)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_payout_item->id;
                    $insertDataArr['payout_id'] = $fk2_payout_item->payout_id;
                    $insertDataArr['item'] = $fk2_payout_item->item;
                    $insertDataArr['amount'] = $fk2_payout_item->amount;
                    $insertDataArr['created_at'] = $fk2_payout_item->created_at;
                    $insertDataArr['updated_at'] = $fk2_payout_item->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                PayoutItem::insert($insertArr);
            }

            //FK3 - Table Pos
            Po::truncate();
            $fk2_pos_total = DB::connection($this->oldDb)->select('select count(*) as total_data from pos');
            $loop = (integer)ceil($fk2_pos_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_pos = DB::connection($this->oldDb)->select('select * from pos order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_pos as $fk2_po)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_po->id;
                    $insertDataArr['number'] = $fk2_po->number;
                    $insertDataArr['customer_id'] = $fk2_po->customer_id;
                    $insertDataArr['title'] = $fk2_po->title;
                    $insertDataArr['user_id'] = $fk2_po->user_id;
                    $insertDataArr['status'] = $fk2_po->status;
                    ($fk2_po->submitted != '0000-00-00 00:00:00') ? $insertDataArr['submitted'] = $fk2_po->submitted : $insertDataArr['submitted'] = null;
                    ($fk2_po->committed != '0000-00-00 00:00:00') ? $insertDataArr['committed'] = $fk2_po->committed : $insertDataArr['committed'] = null;
                    $insertDataArr['archived'] = $fk2_po->archived;
                    $insertDataArr['vendor_id'] = $fk2_po->vendor_id;
                    $insertDataArr['type'] = $fk2_po->type;
                    $insertDataArr['job_id'] = $fk2_po->job_id;
                    $insertDataArr['company_invoice'] = $fk2_po->company_invoice;
                    $insertDataArr['projected_ship'] = $fk2_po->projected_ship;
                    $insertDataArr['object_id'] = $fk2_po->object_id;
                    $insertDataArr['emailed'] = $fk2_po->emailed;
                    $insertDataArr['parent_id'] = $fk2_po->parent_id;
                    $insertDataArr['created_at'] = $fk2_po->created_at;
                    $insertDataArr['updated_at'] = $fk2_po->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Po::insert($insertArr);
            }

            //FK3 - Table PoItem
            PoItem::truncate();
            $fk2_po_items_total = DB::connection($this->oldDb)->select('select count(*) as total_data from po_items');
            $loop = (integer)ceil($fk2_po_items_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_po_items = DB::connection($this->oldDb)->select('select * from po_items order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_po_items as $fk2_po_item)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_po_item->id;
                    $insertDataArr['po_id'] = $fk2_po_item->po_id;
                    $insertDataArr['job_item_id'] = $fk2_po_item->job_item_id;
                    $insertDataArr['item'] = $fk2_po_item->item;
                    ($fk2_po_item->received != '0000-00-00 00:00:00') ? $insertDataArr['received'] = $fk2_po_item->received : $insertDataArr['received'] = null;
                    $insertDataArr['received_by'] = $fk2_po_item->received_by;
                    $insertDataArr['user_id'] = $fk2_po_item->user_id;
                    $insertDataArr['notes'] = $fk2_po_item->notes;
                    $insertDataArr['qty'] = $fk2_po_item->qty;
                    $insertDataArr['punch'] = $fk2_po_item->punch;
                    $insertDataArr['fft_id'] = $fk2_po_item->fft_id;
                    $insertDataArr['service_id'] = $fk2_po_item->service_id;
                    $insertDataArr['warranty_id'] = $fk2_po_item->warranty_id;
                    $insertDataArr['created_at'] = $fk2_po_item->created_at;
                    $insertDataArr['updated_at'] = $fk2_po_item->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                PoItem::insert($insertArr);
            }

            //FK3 - Table Promotions
            Promotion::truncate();
            $fk2_promotions_total = DB::connection($this->oldDb)->select('select count(*) as total_data from promotions');
            $loop = (integer)ceil($fk2_promotions_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_promotions = DB::connection($this->oldDb)->select('select * from promotions order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_promotions as $fk2_promotion)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_promotion->id;
                    $insertDataArr['name'] = $fk2_promotion->name;
                    $insertDataArr['active'] = $fk2_promotion->active;
                    $insertDataArr['modifier'] = $fk2_promotion->modifier;
                    $insertDataArr['condition'] = $fk2_promotion->condition;
                    $insertDataArr['qualifier'] = $fk2_promotion->qualifier;
                    $insertDataArr['discount_amount'] = $fk2_promotion->discount_amount;
                    $insertDataArr['verbiage'] = $fk2_promotion->verbiage;
                    $insertDataArr['deleted_at'] = null;
                    $insertDataArr['created_at'] = $fk2_promotion->created_at;
                    $insertDataArr['updated_at'] = $fk2_promotion->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Promotion::insert($insertArr);
            }

            //FK3 - Table Punches
            Punch::truncate();
            $fk2_punches_total = DB::connection($this->oldDb)->select('select count(*) as total_data from punches');
            $loop = (integer)ceil($fk2_punches_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_punches = DB::connection($this->oldDb)->select('select * from punches order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_punches as $fk2_punch)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_punch->id;
                    $insertDataArr['group_id'] = $fk2_punch->designation_id;
                    $insertDataArr['question'] = $fk2_punch->question;
                    $insertDataArr['created_at'] = $fk2_punch->created_at;
                    $insertDataArr['updated_at'] = $fk2_punch->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Punch::insert($insertArr);
            }

            //FK3 - Table Question Categories
            QuestionCategory::truncate();
            $fk2_question_categories_total = DB::connection($this->oldDb)->select('select count(*) as total_data from question_categories');
            $loop = (integer)ceil($fk2_question_categories_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_question_categories = DB::connection($this->oldDb)->select('select * from question_categories order by id asc LIMIT ' .  $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_question_categories as $fk2_question_category)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_question_category->id;
                    $insertDataArr['name'] = $fk2_question_category->name;
                    $insertDataArr['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    $insertDataArr['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    $insertArr[] = $insertDataArr;
                }
                QuestionCategory::insert($insertArr);
            }

            //FK3 - Table Quotes
            Quote::truncate();
            $fk2_quotes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quotes');
            $loop = (integer)ceil($fk2_quotes_total[0]->total_data / 100);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quotes = DB::connection($this->oldDb)->select('select * from quotes order by id asc LIMIT 100 OFFSET ' . ($x * 100));

                $insertArr = array();
                foreach($fk2_quotes as $fk2_quote)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote->id;
                    $insertDataArr['accepted'] = $fk2_quote->accepted;
                    $insertDataArr['final'] = $fk2_quote->final;
                    $insertDataArr['meta'] = $fk2_quote->meta;
                    $quoteType = QuoteType::where('name', $fk2_quote->type)->first();
                    if(!$quoteType) $insertDataArr['quote_type_id'] = '0';
                    else $insertDataArr['quote_type_id'] = $quoteType->id;
                    $insertDataArr['lead_id'] = $fk2_quote->lead_id;
                    $insertDataArr['closed'] = $fk2_quote->closed;
                    $insertDataArr['suspended'] = $fk2_quote->suspended;
                    $insertDataArr['price'] = $fk2_quote->price;
                    $insertDataArr['title'] = $fk2_quote->title;
                    $insertDataArr['paperwork'] = $fk2_quote->paperwork;
                    $insertDataArr['finance_total'] = $fk2_quote->finance_total;
                    $insertDataArr['for_designer'] = $fk2_quote->for_designer;
                    $insertDataArr['markup'] = $fk2_quote->markup;
                    $insertDataArr['picking_slab'] = $fk2_quote->picking_slab;
                    $insertDataArr['picked_slab'] = $fk2_quote->picked_slab;
                    $insertDataArr['promotion_id'] = $fk2_quote->promotion_id ?: '0';
                    $insertDataArr['deleted_at'] = null;
                    $insertDataArr['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    $insertDataArr['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    $insertArr[] = $insertDataArr;
                }
                Quote::insert($insertArr);
            }

            //FK3 - Table Quote Addons
            QuoteAddon::truncate();
            $fk2_quote_addons_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_addons');
            $loop = (integer)ceil($fk2_quote_addons_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_addons = DB::connection($this->oldDb)->select('select * from quote_addons order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_addons as $fk2_quote_addon)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_addon->id;
                    $insertDataArr['quote_id'] = $fk2_quote_addon->quote_id;
                    $insertDataArr['addon_id'] = $fk2_quote_addon->addon_id;
                    $insertDataArr['price'] = $fk2_quote_addon->price;
                    $insertDataArr['qty'] = $fk2_quote_addon->qty;
                    $insertDataArr['description'] = $fk2_quote_addon->description;
                    $insertDataArr['created_at'] = $fk2_quote_addon->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_addon->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteAddon::insert($insertArr);
            }

            //FK3 - Table Quote Appliances
            QuoteAppliance::truncate();
            $fk2_quote_appliances_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_appliances');
            $loop = (integer)ceil($fk2_quote_appliances_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_appliances = DB::connection($this->oldDb)->select('select * from quote_appliances order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_appliances as $fk2_quote_appliance)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_appliance->id;
                    $insertDataArr['quote_id'] = $fk2_quote_appliance->quote_id;
                    $insertDataArr['appliance_id'] = $fk2_quote_appliance->appliance_id;
                    $insertDataArr['brand'] = $fk2_quote_appliance->brand;
                    $insertDataArr['model'] = $fk2_quote_appliance->model;
                    $insertDataArr['size'] = $fk2_quote_appliance->size;
                    $insertDataArr['created_at'] = $fk2_quote_appliance->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_appliance->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteAppliance::insert($insertArr);
            }

            //FK3 - Table Quote Cabinet
            QuoteCabinet::truncate();
            $fk2_quote_cabinets_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_cabinets');
            $loop = (integer)ceil($fk2_quote_cabinets_total[0]->total_data / 100);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_cabinets = DB::connection($this->oldDb)->select('select * from quote_cabinets order by id asc LIMIT 100 OFFSET ' . ($x * 100));

                $insertArr = array();
                foreach($fk2_quote_cabinets as $fk2_quote_cabinet)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_cabinet->id;
                    $insertDataArr['quote_id'] = $fk2_quote_cabinet->quote_id;
                    $insertDataArr['data'] = $fk2_quote_cabinet->data;
                    $insertDataArr['override'] = $fk2_quote_cabinet->override;
                    $insertDataArr['location'] = $fk2_quote_cabinet->location;
                    $insertDataArr['measure'] = $fk2_quote_cabinet->measure;
                    $insertDataArr['color'] = $fk2_quote_cabinet->color;
                    $insertDataArr['cabinet_id'] = $fk2_quote_cabinet->cabinet_id;
                    $insertDataArr['name'] = $fk2_quote_cabinet->name;
                    $insertDataArr['inches'] = $fk2_quote_cabinet->inches;
                    $insertDataArr['price'] = $fk2_quote_cabinet->price;
                    $insertDataArr['delivery'] = $fk2_quote_cabinet->delivery;
                    $insertDataArr['wood_xml'] = $fk2_quote_cabinet->wood_xml;
                    $insertDataArr['description'] = $fk2_quote_cabinet->description;
                    $insertDataArr['are_we_removing_cabinets'] = '0';
                    $insertDataArr['deleted_at'] = $fk2_quote_cabinet->deleted_at;
                    $insertDataArr['created_at'] = $fk2_quote_cabinet->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_cabinet->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteCabinet::insert($insertArr);
            }

            //FK3 - Table Quote Granite
            QuoteGranite::truncate();
            $fk2_quote_granites_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_granites');
            $loop = (integer)ceil($fk2_quote_granites_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_granites = DB::connection($this->oldDb)->select('select * from quote_granites order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_granites as $fk2_quote_granite)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_granite->id;
                    $insertDataArr['quote_id'] = $fk2_quote_granite->quote_id;
                    $insertDataArr['description'] = $fk2_quote_granite->description;
                    $insertDataArr['granite_id'] = $fk2_quote_granite->granite_id;
                    $insertDataArr['granite_override'] = $fk2_quote_granite->granite_override;
                    $insertDataArr['pp_sqft'] = $fk2_quote_granite->pp_sqft;
                    $insertDataArr['removal_type'] = $fk2_quote_granite->removal_type;
                    $insertDataArr['measurements'] = $fk2_quote_granite->measurements;
                    $insertDataArr['counter_edge'] = $fk2_quote_granite->counter_edge;
                    $insertDataArr['counter_edge_ft'] = $fk2_quote_granite->counter_edge_ft;
                    $insertDataArr['backsplash_height'] = $fk2_quote_granite->backsplash_height;
                    $insertDataArr['raised_bar_length'] = $fk2_quote_granite->raised_bar_length;
                    $insertDataArr['raised_bar_depth'] = $fk2_quote_granite->raised_bar_depth;
                    $insertDataArr['island_width'] = $fk2_quote_granite->island_width;
                    $insertDataArr['island_length'] = $fk2_quote_granite->island_length;
                    $insertDataArr['island_width'] = $fk2_quote_granite->island_width;
                    $insertDataArr['granite_jo'] = $fk2_quote_granite->granite_jo;
                    $insertDataArr['created_at'] = $fk2_quote_granite->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_granite->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteGranite::insert($insertArr);
            }

            //FK3 - Table Quote Questions
            QuoteQuestion::truncate();
            $fk2_quote_questions_total = DB::connection($this->oldDb)->select('select count(*) as total_data from questions');
            $loop = (integer)ceil($fk2_quote_questions_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_questions = DB::connection($this->oldDb)->select('select * from questions order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_questions as $fk2_quote_question)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_question->id;
                    $insertDataArr['question'] = $fk2_quote_question->question;
                    $insertDataArr['response_type'] = $fk2_quote_question->response_type;
                    $insertDataArr['stage'] = $fk2_quote_question->stage;
                    $insertDataArr['group_id'] = $fk2_quote_question->designation_id;
                    $insertDataArr['contract'] = $fk2_quote_question->contract;
                    $insertDataArr['contract_format'] = $fk2_quote_question->contract_format;
                    $insertDataArr['active'] = $fk2_quote_question->active;
                    $insertDataArr['question_category_id'] = $fk2_quote_question->question_category_id;
                    $insertDataArr['vendor_id'] = $fk2_quote_question->vendor_id;
                    $insertDataArr['small_job'] = $fk2_quote_question->small_job;
                    $insertDataArr['on_checklist'] = $fk2_quote_question->on_checklist;
                    $insertDataArr['on_job_board'] = $fk2_quote_question->on_job_board;
                    $insertDataArr['created_at'] = $fk2_quote_question->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_question->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteQuestion::insert($insertArr);
            }

            //FK3 - Table Quote Questions
            QuoteQuestionAnswer::truncate();
            $fk2_quote_questions_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_questions');
            $loop = (integer)ceil($fk2_quote_questions_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_questions = DB::connection($this->oldDb)->select('select * from quote_questions order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_questions as $fk2_quote_question)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_question->id;
                    $insertDataArr['question_id'] = $fk2_quote_question->question_id;
                    $insertDataArr['quote_id'] = $fk2_quote_question->quote_id;
                    $insertDataArr['group_id'] = '2';
                    $insertDataArr['answer'] = $fk2_quote_question->answer;
                    $insertDataArr['active'] = '1';
                    $insertDataArr['created_at'] = $fk2_quote_question->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_question->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteQuestionAnswer::insert($insertArr);
            }

            //FK3 - Table Quote Question Condition
            QuoteQuestionCondition::truncate();
            DB::insert("
            INSERT INTO `quote_question_conditions` (`id`, `created_at`, `updated_at`, `question_id`, `answer`, `operand`, `amount`, `once`, `active`, `percentage`) VALUES
            (1, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 1, '*', 'Add', 75.00, 0, 1, 100),
            (2, '2014-08-11 12:06:53', '2016-10-28 15:59:24', 3, '*', 'Add', 40.00, 0, 1, 100),
            (3, '2014-08-11 12:06:53', '2014-10-17 17:22:27', 14, '*', 'Add', 100.00, 0, 1, 100),
            (4, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 15, '*', 'Add', 20.00, 0, 1, 100),
            (5, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 17, '*', 'Add', 10.00, 0, 1, 100),
            (6, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 19, '*', 'Add', 20.00, 0, 1, 100),
            (7, '2014-08-11 12:06:53', '2016-10-28 16:04:19', 22, '*', 'Add', 125.00, 0, 1, 100),
            (8, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 24, '*', 'Add', 35.00, 0, 1, 100),
            (9, '2014-08-11 12:06:53', '2015-08-20 17:05:52', 27, '*', 'Add', 125.00, 0, 1, 100),
            (10, '2014-08-11 12:06:53', '2016-10-28 16:03:06', 28, '*', 'Add', 75.00, 0, 1, 100),
            (11, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 29, '*', 'Add', 85.00, 0, 1, 100),
            (12, '2014-08-11 12:06:53', '2014-09-22 17:39:16', 30, '*', 'Add', 75.00, 0, 1, 100),
            (13, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 31, '*', 'Add', 75.00, 0, 1, 100),
            (14, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 33, '*', 'Add', 75.00, 0, 1, 100),
            (15, '2014-08-11 12:06:53', '2016-11-03 17:10:59', 36, '*', 'Add', 125.00, 0, 1, 100),
            (16, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 34, '*', 'Add', 50.00, 0, 1, 100),
            (17, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 35, '*', 'Add', 75.00, 0, 1, 100),
            (18, '2014-08-11 12:06:53', '2014-10-03 11:20:12', 32, '*', 'Add', 10.00, 0, 1, 100),
            (19, '2014-08-11 12:06:53', '2016-10-20 16:19:23', 55, 'Y', 'Add', 350.00, 1, 1, 100),
            (20, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 56, 'no', 'Subtract', 350.00, 1, 1, 100),
            (21, '2014-08-11 12:06:53', '2014-09-17 13:27:03', 58, '*', 'Subtract', 1.00, 0, 1, 100),
            (22, '2014-08-11 12:06:53', '2014-10-17 11:29:14', 59, 'Y', 'Add', 30.00, 1, 1, 100),
            (23, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 59, 'N', 'Add', 0.00, 1, 1, 100),
            (24, '2014-08-11 12:06:53', '2014-08-11 12:06:53', 55, 'N', 'Add', 0.00, 1, 1, 100),
            (25, '2014-08-18 15:13:31', '2014-08-18 15:13:31', 16, '', 'Add', 0.00, 0, 1, 100),
            (26, '2014-08-18 15:13:41', '2014-08-18 15:13:41', 18, '', 'Add', 0.00, 0, 1, 100),
            (27, '2014-08-18 15:13:50', '2014-08-18 15:13:50', 20, '', 'Add', 0.00, 0, 1, 100),
            (28, '2014-08-18 15:40:20', '2014-08-18 15:40:20', 5, '', 'Add', 0.00, 0, 1, 100),
            (29, '2014-08-18 15:40:28', '2014-08-18 15:40:28', 6, '', 'Add', 0.00, 0, 1, 100),
            (30, '2014-08-18 15:40:37', '2014-08-18 15:40:37', 7, '', 'Add', 0.00, 0, 1, 100),
            (31, '2014-08-18 15:40:44', '2014-08-18 15:40:44', 10, '', 'Add', 0.00, 0, 1, 100),
            (32, '2014-08-18 15:40:49', '2014-08-18 15:40:49', 11, '', 'Add', 0.00, 0, 1, 100),
            (33, '2014-08-18 15:40:54', '2014-08-18 15:40:54', 12, '', 'Add', 0.00, 0, 1, 100),
            (34, '2014-08-18 15:41:01', '2014-08-18 15:41:01', 13, '', 'Add', 0.00, 0, 1, 100),
            (35, '2014-08-18 15:41:28', '2014-08-18 15:41:28', 26, '', 'Add', 0.00, 0, 1, 100),
            (36, '2014-08-18 15:41:35', '2014-08-18 15:41:35', 23, '', 'Add', 0.00, 0, 1, 100),
            (37, '2014-08-18 15:41:46', '2014-08-18 15:41:46', 25, '', 'Add', 0.00, 0, 1, 100),
            (38, '2014-08-18 15:42:01', '2014-08-18 15:42:01', 37, '', 'Add', 0.00, 0, 1, 100),
            (39, '2014-08-18 15:42:24', '2014-08-18 15:42:24', 38, '', 'Add', 0.00, 0, 1, 100),
            (40, '2014-08-18 15:42:52', '2014-08-18 15:42:52', 40, '', 'Add', 0.00, 0, 1, 100),
            (41, '2014-08-18 15:43:00', '2014-08-18 15:43:00', 41, '', 'Add', 0.00, 0, 1, 100),
            (42, '2014-08-18 15:43:08', '2014-08-18 15:43:08', 42, '', 'Add', 0.00, 0, 1, 100),
            (43, '2014-08-18 15:43:15', '2014-08-18 15:43:15', 43, '', 'Add', 0.00, 0, 1, 100),
            (44, '2014-08-18 15:43:25', '2014-08-18 15:43:25', 44, '', 'Add', 0.00, 0, 1, 100),
            (45, '2014-08-18 15:43:57', '2014-08-18 15:43:57', 46, '', 'Add', 0.00, 0, 1, 100),
            (46, '2014-08-18 15:44:05', '2014-08-18 15:44:05', 47, '', 'Add', 0.00, 0, 1, 100),
            (47, '2014-08-18 15:44:12', '2014-08-18 15:44:12', 48, '', 'Add', 0.00, 0, 1, 100),
            (48, '2014-08-18 15:44:34', '2014-08-18 15:44:34', 52, '', 'Add', 0.00, 0, 1, 100),
            (49, '2014-08-18 15:44:42', '2014-08-18 15:44:42', 53, '', 'Add', 0.00, 0, 1, 100),
            (50, '2014-08-18 15:44:49', '2014-08-18 15:44:49', 54, '', 'Add', 0.00, 0, 1, 100),
            (51, '2014-08-18 15:46:12', '2014-08-18 15:46:12', 62, '', 'Add', 0.00, 0, 1, 100),
            (52, '2014-09-16 18:49:07', '2014-09-16 18:49:07', 21, '', 'Add', 0.00, 0, 1, 100),
            (53, '2014-09-16 18:54:47', '2014-09-16 18:54:47', 63, '', 'Add', 0.00, 0, 1, 100),
            (54, '2014-09-16 18:55:54', '2014-09-16 18:55:54', 64, '', 'Add', 0.00, 0, 1, 100),
            (55, '2014-09-16 18:58:18', '2014-09-16 18:58:18', 65, '', 'Add', 0.00, 0, 1, 100),
            (56, '2014-09-16 19:09:39', '2014-09-16 19:10:35', 66, '*', 'Add', 25.00, 0, 1, 100),
            (57, '2014-09-18 13:16:34', '2014-09-18 13:16:34', 67, '', 'Add', 0.00, 0, 1, 100),
            (58, '2014-09-18 13:17:16', '2014-09-18 13:17:16', 68, '', 'Add', 0.00, 0, 1, 100),
            (59, '2014-10-17 11:30:38', '2016-10-28 16:02:07', 69, '*', 'Add', 75.00, 1, 1, 100),
            (60, '2014-10-30 11:19:17', '2016-01-26 11:31:46', 70, 'N', 'Add', 75.00, 1, 1, 100),
            (61, '2015-03-25 11:46:17', '2015-03-25 11:48:49', 71, '*', 'Add', 10.00, 0, 1, 100),
            (62, '2015-03-25 11:53:12', '2015-03-25 11:53:33', 72, '*', 'Add', 20.00, 0, 1, 100),
            (63, '2015-03-25 11:54:33', '2015-03-25 11:54:49', 73, '*', 'Add', 30.00, 0, 1, 100),
            (64, '2016-01-22 15:15:19', '2016-01-22 15:16:05', 74, '*', 'Add', 150.00, 0, 1, 100),
            (65, '2016-06-09 14:50:49', '2016-06-10 20:51:16', 75, '*', 'Add', 15.00, 0, 1, 100),
            (66, '2016-09-15 14:38:45', '2016-09-15 14:38:45', 76, '', 'Add', 0.00, 0, 1, 100),
            (67, '2016-09-15 14:39:32', '2016-09-15 14:39:32', 77, '', 'Add', 0.00, 0, 1, 100),
            (68, '2016-10-26 14:53:15', '2016-10-26 14:53:15', 78, '', 'Add', 0.00, 0, 1, 100),
            (69, '2017-02-09 12:11:00', '2017-02-09 12:11:00', 79, '', 'Add', 0.00, 0, 1, 100),
            (70, '2017-02-17 12:29:00', '2017-02-17 12:29:35', 80, '*', 'Add', 150.00, 0, 1, 100),
            (71, '2017-08-04 12:57:09', '2017-10-19 20:11:43', 81, 'y', 'Add', 100.00, 1, 1, 100),
            (72, '2018-01-05 18:07:26', '2018-01-15 19:38:33', 82, 'yes', 'Add', 0.00, 0, 1, 100),
            (73, '2018-02-06 22:02:25', '2018-02-06 22:02:25', 83, '', 'Add', 0.00, 0, 1, 100),
            (74, '2018-02-06 22:03:34', '2018-02-06 22:06:38', 84, '*', 'Add', 0.00, 0, 1, 100),
            (75, '2018-02-06 22:04:23', '2018-02-06 22:06:00', 85, '*', 'Add', 25.00, 0, 1, 100),
            (76, '2018-02-06 22:05:12', '2018-02-06 22:05:32', 86, '', 'Add', 25.00, 0, 1, 100),
            (77, '2018-03-28 20:12:17', '2018-05-10 22:08:28', 87, 'Y', 'Add', 300.00, 1, 1, 100);
            ");

            //FK3 - Table Quote Responsibilities
            QuoteResponsibility::truncate();
            $fk2_quote_responsibilities_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_responsibilities');
            $loop = (integer)ceil($fk2_quote_responsibilities_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_responsibilities = DB::connection($this->oldDb)->select('select * from quote_responsibilities order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_responsibilities as $fk2_quote_responsibility)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_responsibility->id;
                    $insertDataArr['quote_id'] = $fk2_quote_responsibility->quote_id;
                    $insertDataArr['responsibility_id'] = $fk2_quote_responsibility->responsibility_id;
                    $insertDataArr['created_at'] = $fk2_quote_responsibility->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_responsibility->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteResponsibility::insert($insertArr);
            }

            //FK3 - Table Quote Tiles
            QuoteTile::truncate();
            $fk2_quote_tiles_total = DB::connection($this->oldDb)->select('select count(*) as total_data from quote_tiles');
            $loop = (integer)ceil($fk2_quote_tiles_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_quote_tiles = DB::connection($this->oldDb)->select('select * from quote_tiles order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_quote_tiles as $fk2_quote_tile)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_quote_tile->id;
                    $insertDataArr['quote_id'] = $fk2_quote_tile->quote_id;
                    $insertDataArr['description'] = $fk2_quote_tile->description;
                    $insertDataArr['linear_feet_counter'] = $fk2_quote_tile->linear_feet_counter;
                    $insertDataArr['backsplash_height'] = $fk2_quote_tile->backsplash_height;
                    $insertDataArr['pattern'] = $fk2_quote_tile->pattern;
                    $insertDataArr['sealed'] = $fk2_quote_tile->sealed;
                    $insertDataArr['created_at'] = $fk2_quote_tile->created_at;
                    $insertDataArr['updated_at'] = $fk2_quote_tile->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                QuoteTile::insert($insertArr);
            }

            //FK3 - Table Responsibilities
            Responsibility::truncate();
            $fk2_responsibilities_total = DB::connection($this->oldDb)->select('select count(*) as total_data from responsibilities');
            $loop = (integer)ceil($fk2_responsibilities_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_responsibilities = DB::connection($this->oldDb)->select('select * from responsibilities order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_responsibilities as $fk2_responsibility)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_responsibility->id;
                    $insertDataArr['name'] = $fk2_responsibility->name;
                    $insertDataArr['active'] = $fk2_responsibility->active;
                    $insertDataArr['created_at'] = $fk2_responsibility->created_at;
                    $insertDataArr['updated_at'] = $fk2_responsibility->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Responsibility::insert($insertArr);
            }

            //FK3 - Table Settings
            Setting::truncate();
            $fk2_settings_total = DB::connection($this->oldDb)->select('select count(*) as total_data from settings');
            $loop = (integer)ceil($fk2_settings_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_settings = DB::connection($this->oldDb)->select('select * from settings order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_settings as $fk2_setting)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_setting->id;
                    $insertDataArr['setting'] = '';
                    $insertDataArr['name'] = $fk2_setting->name;
                    $insertDataArr['value'] = $fk2_setting->val;
                    $insertDataArr['description'] = '';
                    $insertDataArr['plugin'] = '';
                    $insertDataArr['meta'] = null;
                    $insertDataArr['type'] = '';
                    $insertDataArr['created_at'] = $fk2_setting->created_at;
                    $insertDataArr['updated_at'] = $fk2_setting->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Setting::insert($insertArr);
            }

            //FK3 - Table Shops
            Shop::truncate();
            $fk2_shops_total = DB::connection($this->oldDb)->select('select count(*) as total_data from shops');
            $loop = (integer)ceil($fk2_shops_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_shops = DB::connection($this->oldDb)->select('select * from shops order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_shops as $fk2_shop)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_shop->id;
                    $insertDataArr['user_id'] = $fk2_shop->user_id;
                    $insertDataArr['active'] = $fk2_shop->active;
                    $insertDataArr['job_id'] = $fk2_shop->job_id;
                    $insertDataArr['job_item_id'] = $fk2_shop->job_item_id;
                    $insertDataArr['created_at'] = $fk2_shop->created_at;
                    $insertDataArr['updated_at'] = $fk2_shop->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Shop::insert($insertArr);
            }

            //FK3 - Table Shop Cabinets
            ShopCabinet::truncate();
            $fk2_shop_cabinets_total = DB::connection($this->oldDb)->select('select count(*) as total_data from shop_cabinets');
            $loop = (integer)ceil($fk2_shop_cabinets_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_shop_cabinets = DB::connection($this->oldDb)->select('select * from shop_cabinets order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_shop_cabinets as $fk2_shop_cabinet)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_shop_cabinet->id;
                    $insertDataArr['quote_cabinet_id'] = $fk2_shop_cabinet->quote_cabinet_id;
                    $insertDataArr['shop_id'] = $fk2_shop_cabinet->shop_id;
                    $insertDataArr['notes'] = $fk2_shop_cabinet->notes;
                    $insertDataArr['approved'] = $fk2_shop_cabinet->approved;
                    $insertDataArr['started'] = $fk2_shop_cabinet->started;
                    $insertDataArr['completed'] = $fk2_shop_cabinet->completed;
                    $insertDataArr['created_at'] = $fk2_shop_cabinet->created_at;
                    $insertDataArr['updated_at'] = $fk2_shop_cabinet->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                ShopCabinet::insert($insertArr);
            }

            //FK3 - Table Sinks
            Sink::truncate();
            $fk2_sinks_total = DB::connection($this->oldDb)->select('select count(*) as total_data from sinks');
            $loop = (integer)ceil($fk2_sinks_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_sinks = DB::connection($this->oldDb)->select('select * from sinks order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_sinks as $fk2_sink)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_sink->id;
                    $insertDataArr['active'] = $fk2_sink->active;
                    $insertDataArr['name'] = $fk2_sink->name;
                    $insertDataArr['price'] = $fk2_sink->price;
                    $insertDataArr['material'] = $fk2_sink->material;
                    $insertDataArr['image'] = null;
                    $insertDataArr['created_at'] = $fk2_sink->created_at;
                    $insertDataArr['updated_at'] = $fk2_sink->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Sink::insert($insertArr);
            }

            //FK3 - Table Snapshots
            Snapshot::truncate();
            $fk2_snapshots_total = DB::connection($this->oldDb)->select('select count(*) as total_data from snapshots');
            $loop = (integer)ceil($fk2_snapshots_total[0]->total_data / 10);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_snapshots = DB::connection($this->oldDb)->select('select * from snapshots order by id asc LIMIT 10 OFFSET ' . ($x * 10));

                $insertArr = array();
                foreach($fk2_snapshots as $fk2_snapshot)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_snapshot->id;
                    $insertDataArr['quote_id'] = $fk2_snapshot->quote_id;
                    $insertDataArr['quote'] = $fk2_snapshot->quote;
                    $insertDataArr['debug'] = $fk2_snapshot->debug;
                    $insertDataArr['location'] = $fk2_snapshot->location;
                    $insertDataArr['created_at'] = $fk2_snapshot->created_at;
                    $insertDataArr['updated_at'] = $fk2_snapshot->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Snapshot::insert($insertArr);
            }

            //FK3 - Table Stages
            Stage::truncate();
            $fk2_stages_total = DB::connection($this->oldDb)->select('select count(*) as total_data from stages');
            $loop = (integer)ceil($fk2_stages_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_stages = DB::connection($this->oldDb)->select('select * from stages order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_stages as $fk2_stage)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_stage->id;
                    $insertDataArr['name'] = $fk2_stage->name;
                    $insertDataArr['deleted_at'] = $fk2_stage->deleted_at;
                    $insertDataArr['created_at'] = $fk2_stage->created_at;
                    $insertDataArr['updated_at'] = $fk2_stage->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Stage::insert($insertArr);
            }

            //FK3 - Table Statuses
            Status::truncate();
            $fk2_statuses_total = DB::connection($this->oldDb)->select('select count(*) as total_data from stages');
            $loop = (integer)ceil($fk2_statuses_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_statuses = DB::connection($this->oldDb)->select('select * from statuses order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_statuses as $fk2_status)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_status->id;
                    $insertDataArr['stage_id'] = $fk2_status->stage_id;
                    $insertDataArr['name'] = $fk2_status->name;
                    $insertDataArr['active'] = $fk2_status->active;
                    $insertDataArr['followup_status'] = $fk2_status->followup_status;
                    $insertDataArr['followup_expiration'] = $fk2_status->followup_expiration;
                    $insertDataArr['followup_lock'] = $fk2_status->followup_lock;
                    $insertDataArr['deleted_at'] = $fk2_status->deleted_at;
                    $insertDataArr['created_at'] = $fk2_status->created_at;
                    $insertDataArr['updated_at'] = $fk2_status->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Status::insert($insertArr);
            }

            //FK3 - Table Status Expirations
            StatusExpiration::truncate();
            $fk2_status_expirations_total = DB::connection($this->oldDb)->select('select count(*) as total_data from status_expirations');
            $loop = (integer)ceil($fk2_status_expirations_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_status_expirations = DB::connection($this->oldDb)->select('select * from status_expirations order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_status_expirations as $fk2_status_expiration)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_status_expiration->id;
                    $insertDataArr['status_id'] = $fk2_status_expiration->status_id;
                    $insertDataArr['name'] = $fk2_status_expiration->name;
                    $insertDataArr['expires'] = $fk2_status_expiration->expires;
                    $insertDataArr['active'] = $fk2_status_expiration->active;
                    $insertDataArr['warning'] = $fk2_status_expiration->warning;
                    $insertDataArr['type'] = $fk2_status_expiration->type;
                    $insertDataArr['expires_before'] = $fk2_status_expiration->expires_before;
                    $insertDataArr['expires_after'] = $fk2_status_expiration->expires_after;
                    $insertDataArr['deleted_at'] = $fk2_status_expiration->deleted_at;
                    $insertDataArr['created_at'] = $fk2_status_expiration->created_at;
                    $insertDataArr['updated_at'] = $fk2_status_expiration->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                StatusExpiration::insert($insertArr);
            }

            //FK3 - Table Status Expiration Actions
            StatusExpirationAction::truncate();
            $fk2_status_expiration_actions_total = DB::connection($this->oldDb)->select('select count(*) as total_data from status_expiration_actions');
            $loop = (integer)ceil($fk2_status_expiration_actions_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_status_expiration_actions = DB::connection($this->oldDb)->select('select * from status_expiration_actions order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_status_expiration_actions as $fk2_status_expiration_action)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_status_expiration_action->id;
                    $insertDataArr['status_expiration_id'] = $fk2_status_expiration_action->status_expiration_id;
                    $insertDataArr['description'] = $fk2_status_expiration_action->description;
                    $insertDataArr['sms'] = $fk2_status_expiration_action->sms;
                    $insertDataArr['email_subject'] = $fk2_status_expiration_action->email_subject;
                    $insertDataArr['email'] = $fk2_status_expiration_action->email;
                    $insertDataArr['email_content'] = $fk2_status_expiration_action->email_content;
                    $insertDataArr['sms_content'] = $fk2_status_expiration_action->sms_content;
                    $insertDataArr['group_id'] = $fk2_status_expiration_action->designation_id;
                    $insertDataArr['meta'] = $fk2_status_expiration_action->meta;
                    $insertDataArr['active'] = $fk2_status_expiration_action->active;
                    $insertDataArr['attachment'] = $fk2_status_expiration_action->attachment;
                    $insertDataArr['deleted_at'] = $fk2_status_expiration_action->deleted_at;
                    $insertDataArr['created_at'] = $fk2_status_expiration_action->created_at;
                    $insertDataArr['updated_at'] = $fk2_status_expiration_action->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                StatusExpirationAction::insert($insertArr);
            }

            //FK3 - Table Tasks
            Task::truncate();
            $fk2_tasks_total = DB::connection($this->oldDb)->select('select count(*) as total_data from tasks');
            $loop = (integer)ceil($fk2_tasks_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_tasks = DB::connection($this->oldDb)->select('select * from tasks order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_tasks as $fk2_task)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_task->id;
                    $insertDataArr['user_id'] = $fk2_task->user_id;
                    $insertDataArr['assigned_id'] = $fk2_task->assigned_id;
                    $insertDataArr['subject'] = $fk2_task->subject;
                    $insertDataArr['body'] = $fk2_task->body;
                    $insertDataArr['job_id'] = $fk2_task->job_id;
                    $insertDataArr['customer_id'] = $fk2_task->customer_id;
                    $insertDataArr['closed'] = $fk2_task->closed;
                    ($fk2_task->due != '0000-00-00 00:00:00') ? $insertDataArr['due'] = $fk2_task->due : $insertDataArr['due'] = null;
                    $insertDataArr['urgent'] = $fk2_task->urgent;
                    $insertDataArr['satisfied'] = $fk2_task->satisfied;
                    $insertDataArr['deleted_at'] = $fk2_task->deleted_at;
                    $insertDataArr['created_at'] = $fk2_task->created_at;
                    $insertDataArr['updated_at'] = $fk2_task->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Task::insert($insertArr);
            }

            //FK3 - Table Task Notes
            TaskNote::truncate();
            $fk2_task_notes_total = DB::connection($this->oldDb)->select('select count(*) as total_data from task_notes');
            $loop = (integer)ceil($fk2_task_notes_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_task_notes = DB::connection($this->oldDb)->select('select * from task_notes order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_task_notes as $fk2_task_note)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_task_note->id;
                    $insertDataArr['task_id'] = $fk2_task_note->task_id;
                    $insertDataArr['user_id'] = $fk2_task_note->user_id;
                    $insertDataArr['body'] = $fk2_task_note->body;
                    $insertDataArr['deleted_at'] = $fk2_task_note->deleted_at;
                    $insertDataArr['created_at'] = $fk2_task_note->created_at;
                    $insertDataArr['updated_at'] = $fk2_task_note->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                TaskNote::insert($insertArr);
            }

            //FK3 - Table Users
            User::truncate();
            $fk2_users_total = DB::connection($this->oldDb)->select('select count(distinct email) as total_data from users');
            $loop = (integer)ceil($fk2_users_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_users = DB::connection($this->oldDb)->select('select * from users group by email order by id ASC LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_users as $fk2_user)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_user->id;
                    $insertDataArr['name'] = $fk2_user->name;
                    $insertDataArr['email'] = $fk2_user->email;
                    $insertDataArr['password'] = $fk2_user->password;
                    $insertDataArr['remember_token'] = $fk2_user->remember_token;
                    $insertDataArr['group_id'] = $fk2_user->designation_id;
                    $insertDataArr['customer_id'] = '0';
                    $insertDataArr['active'] = $fk2_user->active;
                    $insertDataArr['mobile'] = $fk2_user->mobile;
                    $insertDataArr['hash'] = $fk2_user->bypass;
                    $insertDataArr['google_token'] = $fk2_user->google;
                    $insertDataArr['task_id'] = $fk2_user->task_id;
                    $insertDataArr['frugal_number'] = $fk2_user->frugal_number;
                    $insertDataArr['superuser'] = $fk2_user->superuser;
                    $insertDataArr['manager'] = $fk2_user->manager;
                    $insertDataArr['created_at'] = $fk2_user->created_at;
                    $insertDataArr['updated_at'] = $fk2_user->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                User::insert($insertArr);
            }

            //FK3 - Table Vendors
            Vendor::truncate();
            $fk2_vendors_total = DB::connection($this->oldDb)->select('select count(*) as total_data from vendors');
            $loop = (integer)ceil($fk2_vendors_total[0]->total_data / $this->countLoop);

            for($x = 0; $x < $loop; $x++)
            {
                $fk2_vendors = DB::connection($this->oldDb)->select('select * from vendors order by id asc LIMIT ' . $this->countLoop . ' OFFSET ' . ($x * $this->countLoop));

                $insertArr = array();
                foreach($fk2_vendors as $fk2_vendor)
                {
                    $insertDataArr = array();
                    $insertDataArr['id'] = $fk2_vendor->id;
                    $insertDataArr['name'] = $fk2_vendor->name;
                    $insertDataArr['shipping_days'] = $fk2_vendor->tts;
                    $insertDataArr['multiplier'] = $fk2_vendor->multiplier;
                    $insertDataArr['freight'] = $fk2_vendor->freight;
                    $insertDataArr['build_up'] = $fk2_vendor->buildup;
                    $insertDataArr['colors'] = $fk2_vendor->colors;
                    $insertDataArr['active'] = $fk2_vendor->active;
                    $insertDataArr['wood_products'] = $fk2_vendor->wood_products;
                    $insertDataArr['confirmation_days'] = $fk2_vendor->confirmation_days;
                    $insertDataArr['created_at'] = $fk2_vendor->created_at;
                    $insertDataArr['updated_at'] = $fk2_vendor->updated_at;
                    $insertArr[] = $insertDataArr;
                }
                Vendor::insert($insertArr);
            }

            $time_end = microtime(true);
            $execution_time_min = (integer)floor(($time_end - $time_start) / 60);
            $execution_time_sec = (integer)floor($time_end - $time_start) - ($execution_time_min * 60);

            $timeMsg = 'Total Execution Time: ' . $execution_time_min . ' minutes, ' . $execution_time_sec . ' secs.';

            return Response::json(
              [
                'response' => 'success',
                'message' => 'Sync Complete. ' . $timeMsg
              ]
            );
        }
        catch(\Exception $e)
        {
          $time_end = microtime(true);
          $execution_time_min = (integer)floor(($time_end - $time_start) / 60);
          $execution_time_sec = (integer)floor($time_end - $time_start) - ($execution_time_min * 60);

          $timeMsg = 'Total Execution Time: ' . $execution_time_min . ' minutes, ' . $execution_time_sec . ' secs.';

          return Response::json(
            [
              'response' => 'error',
              'message' => $e->getMessage() . '. ' . $timeMsg
            ]
          );
        }
    }
}
