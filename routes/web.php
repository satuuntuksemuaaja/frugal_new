<?php

use FK3\Controllers\Admin\ACLController;
use FK3\Controllers\Admin\AuthorizationController;
use FK3\Controllers\Admin\ApplianceController;
use FK3\Controllers\Admin\CountertopController;
use FK3\Controllers\Admin\GroupController;
use FK3\Controllers\Admin\LeadSourceController;
use FK3\Controllers\Admin\QuoteTypeController;
use FK3\Controllers\Admin\SettingsController;
use FK3\Controllers\Admin\SinkController;
use FK3\Controllers\Admin\UserController;
use FK3\Controllers\Admin\VendorController;
use FK3\Controllers\Admin\HardwareController;
use FK3\Controllers\Admin\CabinetController;
use FK3\Controllers\Admin\AddonController;
use FK3\Controllers\Admin\QuestionController;
use FK3\Controllers\Admin\PunchController;
use FK3\Controllers\Admin\LocationController;
use FK3\Controllers\Admin\StatusController;
use FK3\Controllers\Admin\PaymentController;
use FK3\Controllers\Admin\GraniteController;
use FK3\Controllers\Admin\AccessoryController;
use FK3\Controllers\Admin\PricingController;
use FK3\Controllers\Admin\DynamicController;
use FK3\Controllers\Admin\FaqController;
use FK3\Controllers\Admin\ResponsibilityController;
use FK3\Controllers\Admin\PromotionController;
use FK3\Controllers\ProfileController;
use FK3\Controllers\CustomerController;
use FK3\Controllers\LeadController;
use FK3\Controllers\QuoteController;
use FK3\Controllers\TaskController;
use FK3\Controllers\JobController;
use FK3\Controllers\PoController;
use FK3\Controllers\FftController;
use FK3\Controllers\PayoutController;
use FK3\Controllers\PayoutItemController;
use FK3\Controllers\FileController;

require_once(app_path() . "/helpers.php");

//Sync DB from fk2
Route::get('sync-database', 'FK3\Controllers\SyncController@sync')->name('sync_database');
Route::get('do-sync', 'FK3\Controllers\SyncController@doSync')->name('do_sync_database');

Route::get('login', 'FK3\Http\Controllers\Auth\LoginController@login');
Route::post('login', 'FK3\Http\Controllers\Auth\LoginController@attempt');

Route::group(array('namespace' => 'FK3\Controllers'), function () {
    Route::get('customer/{id}/appliances', 'QuoteController@customerAppliances')->name('quote_customer_appliances');
    Route::post('customer/{id}/appliances', 'QuoteController@customerAppliancesSave')->name('quote_customer_appliances_save');
    Route::get('customer/{id}/appliances/thanks', 'QuoteController@customerAppliancesThanks')->name('quote_customer_appliances_thanks');
    Route::get('confirm/job/{id}', 'InboundController@confirmation')->name('schedule_confirmation');
});

Route::get('logout', function () {
    auth()->logout();
    return redirect()->to('login');
});

Route::get('storage/{folder}/{filename}', function ($folder, $filename)
{
    $path = storage_path('app/' . $folder . '/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
})->name('view_image');

// Authenticated Routes.
Route::group(['middleware' => "authenticated"], function () {
    Route::get('/', 'FK3\Controllers\WelcomeController@welcome')->name('welcome');

    Route::group(['prefix' => 'admin'], function () {


        Route::group(array('namespace' => 'FK3\Controllers\Admin'), function () {

          //Locations
          Route::get('/locations/display_locations', 'LocationController@displayLocations')->name('display_locations');
          Route::get('/locations/{id}/destroy', 'LocationController@destroy')->name('destroy_location');

          //Statuses
          Route::get('/statuses/display_statuses', 'StatusController@displayStatuses')->name('display_statuses');
          Route::get('/statuses/get_status', 'StatusController@getStatus')->name('get_status');
          Route::post('/statuses/update', 'StatusController@updateStatus')->name('update_status');
          Route::get('/statuses/get_expirations', 'StatusController@getExpirations')->name('get_expirations');
          Route::post('/statuses/save_expiration', 'StatusController@saveExpiration')->name('save_status_expiration');
          Route::post('/statuses/updateexpiration', 'StatusController@updateExpiration')->name('update_status_expiration');
          Route::post('/statuses/delete_expiration', 'StatusController@deleteExpiration')->name('delete_status_expiration');
          Route::get('/statuses/get_expiration', 'StatusController@getExpiration')->name('get_expiration');
          Route::get('/statuses/get_expiration_actions', 'StatusController@getExpirationActions')->name('get_expiration_actions');
          Route::get('/statuses/get_expiration_action', 'StatusController@getExpirationAction')->name('get_expiration_action');
          Route::post('/statuses/save_expiration_action', 'StatusController@saveExpirationAction')->name('save_status_expiration_action');
          Route::post('/statuses/delete_expiration_action', 'StatusController@deleteExpirationAction')->name('delete_status_expiration_action');
          Route::post('/statuses/updateexpirationaction', 'StatusController@updateExpirationAction')->name('update_status_expiration_action');
          Route::post('/statuses/upload_attachment', 'StatusController@uploadAttachment')->name('status_upload_attachment');
          Route::get('/status_expiration_action/{id}/download', 'StatusController@downloadFile')->name('status_expiration_action_download_file');

          //Granites
          Route::get('/granites/display_granites', 'GraniteController@displayGranites')->name('display_granites');
          Route::get('/granites/get_granite', 'GraniteController@getGranite')->name('get_granite');
          Route::post('/granites/update', 'GraniteController@updateGranite')->name('update_granite');
          Route::post('/granites/delete', 'GraniteController@deleteGranite')->name('delete_granite');

          //Accessories
          Route::get('/accessories/display_accessories', 'AccessoryController@displayAccessories')->name('display_accessories');
          Route::get('/accessories/get_accessory', 'AccessoryController@getAccessory')->name('get_accessory');
          Route::post('/accessories/update', 'AccessoryController@updateAccessory')->name('update_accessory');
          Route::post('/accessories/delete', 'AccessoryController@deleteAccessory')->name('delete_accessory');

          //Sinks
          Route::get('/sinks/display_sinks', 'SinkController@displaySinks')->name('display_sinks');
          Route::get('/sinks/display_inactive_sinks', 'SinkController@displayInactiveSinks')->name('display_inactive_sinks');
          Route::get('/sinks/get_sink', 'SinkController@getSink')->name('get_sink');
          Route::post('/sinks/update', 'SinkController@updateSink')->name('update_sink');
          Route::post('/sinks/redeactivate', 'SinkController@redeactivateSink')->name('redeactivate_sink');
          Route::post('/sinks/delete', 'SinkController@deleteSink')->name('delete_sink');

          //Faqs
          Route::get('/faqs/display_faqs', 'FaqController@displayFaqs')->name('display_faqs');
          Route::get('/faqs/get_faq', 'FaqController@getFaq')->name('get_faq');
          Route::post('/faqs/update', 'FaqController@updateFaq')->name('update_faq');
          Route::post('/faqs/delete', 'FaqController@deleteFaq')->name('delete_faq');

          //Promotions
          Route::get('/promotions/display_promotions', 'PromotionController@displayPromotions')->name('display_promotions');
          Route::get('/promotions/get_promotion', 'PromotionController@getPromotion')->name('get_promotion');
          Route::post('/promotions/update', 'PromotionController@updatePromotion')->name('update_promotion');
          Route::post('/promotions/delete', 'PromotionController@deletePromotion')->name('delete_promotion');

          //Responsibilities
          Route::get('/responsibilities/display_responsibilities', 'ResponsibilityController@displayResponsibilities')->name('display_responsibilities');
          Route::get('/responsibilities/get_responsibility', 'ResponsibilityController@getResponsibility')->name('get_responsibility');
          Route::post('/responsibilities/update', 'ResponsibilityController@updateResponsibility')->name('update_responsibility');
          Route::post('/responsibilities/delete', 'ResponsibilityController@deleteResponsibility')->name('delete_responsibility');

          //Pricing
          Route::get('/pricing/display_pricing', 'PricingController@displayPricing')->name('display_pricing');
          Route::get('/pricing/get_pricing', 'PricingController@getPricing')->name('get_pricing');
          Route::post('/pricing/update', 'PricingController@updatePricing')->name('update_pricing');
          Route::post('/pricing/delete', 'PricingController@deletePricing')->name('delete_pricing');

          //Authorizations
          Route::get('/authorizations/display_authorizations', 'AuthorizationController@displayAuthorizations')->name('display_authorizations');
          Route::get('/authorizations/get_authorization', 'AuthorizationController@getAuthorization')->name('get_authorization');
          Route::post('/authorizations/update', 'AuthorizationController@updateAuthorization')->name('update_authorization');
          Route::post('/authorizations/delete', 'AuthorizationController@deleteAuthorization')->name('delete_authorization');
        });

        Route::resources([
            'users'            => UserController::class,
            'groups'           => GroupController::class,
            'acls'             => ACLController::class,
            'lead_sources'     => LeadSourceController::class,
            'settings'         => SettingsController::class,
            'vendors'          => VendorController::class,
            'countertops'      => CountertopController::class,
            'sinks'            => SinkController::class,
            'quote_types'      => QuoteTypeController::class,
            'appliances'       => ApplianceController::class,
            'hardware'         => HardwareController::class,
            'cabinets'         => CabinetController::class,
            'addons'           => AddonController::class,
            'questions'        => QuestionController::class,
            'punches'          => PunchController::class,
            'stores'           => LocationController::class,
            'statuses'         => StatusController::class,
            'payments'         => PaymentController::class,
            'granites'         => GraniteController::class,
            'accessories'      => AccessoryController::class,
            'pricing'          => PricingController::class,
            'dynamic'          => DynamicController::class,
            'authorizations'   => AuthorizationController::class,
            'faqs'             => FaqController::class,
            'responsibilities' => ResponsibilityController::class,
            'promotions'       => PromotionController::class,
        ]);
    });

    //Route custom
    Route::group(array('namespace' => 'FK3\Controllers'), function () {
      //Leads
      Route::get('/leads/display_leads', 'LeadController@displayLeads')->name('display_leads');
      Route::post('/leads/set_showroom_schedule', 'LeadController@setShowroomSchedule')->name('set_showroom_schedule');
      Route::get('/leads/get_showroom_schedule', 'LeadController@getShowroomSchedule')->name('get_showroom_schedule');
      Route::post('/leads/set_showroom_location', 'LeadController@setShowroomLocation')->name('set_showroom_location');
      Route::get('/leads/get_showroom_location', 'LeadController@getShowroomLocation')->name('get_showroom_location');
      Route::post('/leads/set_showroom_user', 'LeadController@setShowroomUser')->name('set_showroom_user');
      Route::get('/leads/get_showroom_user', 'LeadController@getShowroomUser')->name('get_showroom_user');
      Route::post('/leads/set_designer', 'LeadController@setDesigner')->name('set_designer');
      Route::get('/leads/get_designer', 'LeadController@getDesigner')->name('get_designer');
      Route::post('/leads/set_digital_schedule', 'LeadController@setDigitalSchedule')->name('set_digital_schedule');
      Route::get('/leads/get_digital_schedule', 'LeadController@getDigitalSchedule')->name('get_digital_schedule');
      Route::post('/leads/set_digital_user', 'LeadController@setDigitalUser')->name('set_digital_user');
      Route::get('/leads/get_digital_user', 'LeadController@getDigitalUser')->name('get_digital_user');
      Route::post('/leads/set_closing_schedule', 'LeadController@setClosingSchedule')->name('set_closing_schedule');
      Route::get('/leads/get_closing_schedule', 'LeadController@getClosingSchedule')->name('get_closing_schedule');
      Route::post('/leads/set_closing_user', 'LeadController@setClosingUser')->name('set_closing_user');
      Route::get('/leads/get_closing_user', 'LeadController@getClosingUser')->name('get_closing_user');
      Route::post('/leads/set_lead_status', 'LeadController@setStatus')->name('set_lead_status');
      Route::get('/leads/get_lead_status', 'LeadController@getStatus')->name('get_lead_status');
      Route::post('/leads/set_lead_source', 'LeadController@setSource')->name('set_lead_source');
      Route::get('/leads/get_lead_source', 'LeadController@getSource')->name('get_lead_source');
      Route::post('/leads/set_archived', 'LeadController@setArchived')->name('set_lead_archived');
      Route::get('/leads/display_lead_notes', 'LeadController@displayLeadNotes')->name('display_lead_notes');
      Route::post('/leads/save_lead_notes', 'LeadController@saveLeadNotes')->name('save_lead_notes');
      Route::get('/leads/display_lead_followup', 'LeadController@displayLeadFollowUp')->name('display_lead_follow_up');
      Route::post('/leads/set_close_follow_up', 'LeadController@setCloseFollowup')->name('set_close_follow_up');
      Route::post('/leads/save_lead_quote', 'LeadController@saveLeadQuote')->name('save_lead_quote');
      //Quote
      Route::get('/quotes/display_quotes', 'QuoteController@displayQuotes')->name('display_quotes');
      Route::post('/quotes/set_archived', 'QuoteController@setArchived')->name('set_quote_archived');
      Route::get('/quotes/{id}/start', 'QuoteController@start')->name('start_quote');
      Route::get('/quotes/{id}/financing', 'QuoteController@financing')->name('quote_financing');
      Route::post('quote/{id}/financing/{type}', 'QuoteController@financingSave')->name('quote_financing_save');
      Route::get('/quotes/{id}/view', 'QuoteController@view')->name('quote_view');
      Route::post('/quotes/duplicate', 'QuoteController@duplicate')->name('duplicate_quote');
      Route::get('/quotes/{id}/file/{file_id}/download', 'QuoteController@downloadFile')->name('quote_download_file');
      Route::post('/quotes/file_delete', 'QuoteController@deleteFile')->name('quote_delete_file');
      Route::post('/quotes/delete', 'QuoteController@delete')->name('quote_delete');
      Route::post('/quotes/{id}/upload_file', 'QuoteController@uploadFile')->name('quote_upload_file');
      Route::post('/quotes/save', 'QuoteController@saveQuote')->name('save_quote');
      Route::post('/quotes/set_archived', 'QuoteController@setArchived')->name('set_quote_archived');
      Route::get('/quotes/display_files', 'QuoteController@displayFiles')->name('display_quote_files');
      Route::get('/quotes/display_quote_tiles', 'QuoteController@displayTiles')->name('display_quote_tiles');
      Route::post('/quotes/save_quote_tiles', 'QuoteController@saveTiles')->name('save_quote_tiles');
      Route::post('/quotes/delete_quote_tiles', 'QuoteController@deleteTiles')->name('delete_quote_tiles');
      Route::get('/quotes/display_quote_details', 'QuoteController@displayQuoteDetails')->name('display_quote_details');
      Route::get('quote/{id}/cabinets', 'QuoteController@cabinets')->name('quote_cabinets');
      Route::get('/quotes/display_quote_cabinets', 'QuoteController@displayQuoteCabinets')->name('display_quote_cabinets');
      Route::get('quote/{id}/paperwork', 'QuoteController@paperwork');
      Route::get('quote/{id}/needspaperwork', 'QuoteController@needsPaperwork');
      Route::get('quote/{id}/led', 'QuoteController@led')->name('quote_led');
      Route::post('/quote/set_quote_type', 'QuoteController@setQuoteType')->name('set_quote_type');
      Route::get('/quote/get_quote_type', 'QuoteController@getQuoteType')->name('get_quote_type');
      Route::post('/quote/set_quote_title', 'QuoteController@setQuoteTitle')->name('set_quote_title');
      Route::get('/quote/get_quote_title', 'QuoteController@getQuoteTitle')->name('get_quote_title');
      Route::post('/quote/{id}/save_led', 'QuoteController@saveLed')->name('quote_save_led');
      Route::get('quote/{id}/addons', 'QuoteController@addons')->name('quote_addons');
      Route::get('/quotes/display_quote_addons', 'QuoteController@displayAddons')->name('display_quote_addons');
      Route::post('/quote/save_quote_addons', 'QuoteController@saveAddons')->name('quote_save_addons');
      Route::post('/quote/update_quote_addons', 'QuoteController@updateAddons')->name('quote_update_addons');
      Route::post('/quote/delete_quote_addons', 'QuoteController@deleteAddons')->name('quote_delete_addons');
      Route::get('/quote/get_quote_addons', 'QuoteController@getQuoteAddons')->name('get_quote_addons');
      Route::get('/quotes/display_quote_responsibility', 'QuoteController@displayResponsibility')->name('display_quote_responsibility');
      Route::post('/quote/{id}/quote_save_responsibility', 'QuoteController@saveResponsibilty')->name('quote_save_responsibility');
      Route::get('quote/{id}/questionaire', 'QuoteController@questionaire')->name('quote_questionaire');
      Route::post('quote/{id}/questionaire', 'QuoteController@saveQuestionaire')->name('save_quote_questionaire');
      Route::get('quote/{id}/additional', 'QuoteController@additional')->name('quote_additional');
      Route::post('quote/{id}/additional', 'QuoteController@additionalSave')->name('save_quote_additional');
      Route::get('quote/{id}/hardware', 'QuoteController@hardware')->name('quote_hardware');
      Route::get('quote/{id}/contract', 'QuoteController@contract')->name('quote_contract');
      Route::get('quote/{id}/appliances', 'QuoteController@appliances')->name('quote_appliances');
      Route::get('quote/display_quote_appliances', 'QuoteController@displayAppliances')->name('quote_display_appliances');
      Route::post('quote/{id}/appliances', 'QuoteController@saveAppliance')->name('quote_save_appliance');
      Route::get('quote/{id}/quote_get_sink_data', 'QuoteController@getSinkData')->name('quote_get_sink_data');
      Route::post('quote/{id}/sinks/save', 'QuoteController@saveSink')->name('quote_save_sink');
      Route::get('quote/{id}/sink/{instance}/remove', 'QuoteController@sinkDelete')->name('quote_remove_sink');
      Route::get('quote/{id}/accessories', 'QuoteController@accessories')->name('quote_accessories');
      Route::get('quote/display_accessories', 'QuoteController@displayAccessories')->name('quote_display_accessories');
      Route::get('quote/display_accessories_in_quote', 'QuoteController@displayAccessoriesInQuote')->name('quote_display_accessories_in_quote');
      Route::post('quote/{id}/accessories', 'QuoteController@accessoriesSave')->name('quote_save_accessories');
      Route::get('quote/{id}/accessory/{aid}/delete', 'QuoteController@accessoryRemove')->name('quote_remove_accessories');
      Route::get('quote/{id}/granite', 'QuoteController@granite')->name('quote_granite');
      Route::get('quote/display_granite', 'QuoteController@displayGranite')->name('quote_display_granite');
      Route::post('quote/{id}/granite', 'QuoteController@graniteSave')->name('quote_save_granite');
      //Route::get('quote/{id}/granite/{type}/remove', 'QuoteController@graniteDelete')->name('quote_delete_granite');

      Route::get('quote/get_quote_snapshots', 'QuoteController@getQuoteSnapshots')->name('get_quote_snapshots');
      Route::get('quote/get_quote_appliances', 'QuoteController@getQuoteAppliances')->name('get_quote_appliances');
      Route::post('quote/{id}/hardware', 'QuoteController@hardwareSave')->name('quote_hardware_save');
      Route::get('quote/{id}/hardware/{type}/{hid}/delete', 'QuoteController@hardwareDelete')->name('quote_hardware_delete');
      //Cabinet
      Route::post('/quotes/{id}/cabinets/save', 'QuoteController@saveCabinet')->name('save_cabinet');
      Route::post('/quotes/{id}/upload_file_cabinet', 'QuoteController@uploadFileCabinet')->name('upload_file_cabinet');
      Route::post('/quotes/set_quote_final', 'QuoteController@setQuoteFinal')->name('set_quote_final');
      Route::post('/quotes/save_task', 'QuoteController@quoteSaveTask')->name('save_quote_task');
      Route::get('/quotes/get_quote_cabinet_data', 'QuoteController@getQuoteCabinetData')->name('get_quote_cabinet_data');
      Route::get('/quotes/check_next_step', 'QuoteController@getCheckNextStep')->name('check_next_step');
      Route::get('/quotes/get_quote_cabinet_xml', 'QuoteController@getQuoteCabinetXml')->name('get_quote_cabinet_xml');
      Route::post('/quotes/save_quote_cabinet_data', 'QuoteController@saveQuoteCabinetData')->name('save_quote_cabinet_data');
      Route::post('/quotes/delete_quote_cabinet_data', 'QuoteController@deleteQuoteCabinetData')->name('delete_quote_cabinet_data');
      Route::post('quotes/{id}/appsettings', 'QuoteController@appSettingsSave')->name('quote_save_appsettings');
      Route::get('quotes/{id}/appsettings/send', 'QuoteController@appSettingsSend')->name('quote_send_appsettings');
      //Task
      Route::get('/tasks/display_tasks', 'TaskController@displayTasks')->name('display_tasks');
      Route::post('/tasks/save_task', 'TaskController@SaveTask')->name('save_task');
      Route::get('/tasks/get_task_note', 'TaskController@getTaskNote')->name('get_task_note');
      Route::post('/tasks/save_task_note', 'TaskController@SaveTaskNote')->name('save_task_note');
      Route::post('/tasks/close_task', 'TaskController@closeTask')->name('close_task');
      //Job
      Route::get('/jobs/display_jobs', 'JobController@displayJobs')->name('display_jobs');
      Route::get('/jobs/display_job_items', 'JobController@displayJobItems')->name('display_job_items');
      Route::post('/jobs/save_job_items', 'JobController@saveJobItems')->name('save_job_items');
      Route::post('/jobs/set_verify_item', 'JobController@setVerifyItem')->name('set_verify_job_item');
      Route::post('/jobs/delete_item', 'JobController@deleteItem')->name('delete_job_item');
      Route::post('/jobs/file_delete', 'JobController@deleteFile')->name('job_delete_file');
      Route::post('/jobs/{id}/upload_file', 'JobController@uploadFile')->name('job_upload_file');
      Route::get('/jobs/display_files', 'JobController@displayFiles')->name('display_job_files');
      Route::get('/jobs/{id}/file/{file_id}/download', 'JobController@downloadFile')->name('job_download_file');
      Route::get('/jobs/display_job_notes', 'JobController@displayJobNotes')->name('display_job_notes');
      Route::post('/jobs/save_job_notes', 'JobController@saveJobNotes')->name('save_job_notes');
      Route::post('/jobs/save_task', 'JobController@jobSaveTask')->name('save_job_task');
      Route::get('/jobs/{id}/construction', 'JobController@construction')->name('set_job_construction');
      Route::get('/jobs/{id}/review', 'JobController@review')->name('set_job_review');
      Route::get('/jobs/{id}/arrival', 'JobController@arrival')->name('set_job_arrival');
      Route::get('/jobs/{id}/unlock', 'JobController@unlock')->name('set_job_unlock');
      Route::get('/job/{id}/schedules', 'JobController@schedules')->name('job_schedules');
      Route::get('/job/{id}/send_schedule', 'JobController@sendSchedules')->name('job_send_schedule');
      Route::post('/job/{id}/final_send_schedule', 'JobController@finalSendSchedules')->name('job_final_send_schedule');
      Route::get('/jobs/{id}/paid', 'JobController@markPaid')->name('job_paid');
      Route::post('job/close', 'JobController@close')->name('job_close');
      Route::post('job/new_aux_schedule', 'JobController@createAuxSchedule')->name('new_aux_schedule');
      Route::get('/job/display_job_schedules', 'JobController@displayJobSchedules')->name('display_job_schedules');
      Route::get('/jobs/display_job_appliances', 'JobController@displayJobAppliances')->name('display_job_appliances');
      Route::post('/jobs/save_job_appliances', 'JobController@saveJobAppliances')->name('save_job_appliances');
      Route::post('/jobs/send_quote_appliances', 'JobController@sendQuoteAppliances')->name('send_quote_appliances');
      Route::get('/jobs/{job_id}/auth', 'JobController@auth')->name('job_auth');
      Route::get('/jobs/display_job_auth_items', 'JobController@displayJobAuthItems')->name('display_job_auth_items');
      Route::post('/jobs/auth/delete', 'JobController@deleteAuthItem')->name('delete_job_auth_items');
      Route::post('/jobs/auth/save_item', 'JobController@saveAuthItem')->name('save_job_auth_item');
      Route::post('/job/authsend', 'JobController@authSend')->name('send_job_auth');
      Route::get('/jobs/display_job_auth__sign_items', 'JobController@displayJobAuthSignItems')->name('display_job_auth_sign_items');
      Route::post('/jobs/auth/save_auth_sign', 'JobController@saveAuthSign')->name('save_job_auth_sign');
      Route::post('/jobs/auth/remove_auth_sign', 'JobController@removeAuthSign')->name('remove_job_auth_sign');
      Route::get('/jobs/{job_id}/checklist', 'JobController@checklist')->name('job_checklist');
      Route::get('/jobs/export', 'JobController@export')->name('job_export');
      Route::get('/jobs/{job_id}/destroy', 'JobController@destroy')->name('job_destroy');
      Route::post('/jobs/{id}/xml', 'JobController@xmlSave')->name('job_xml_save');
      Route::get('/jobs/{job_id}/get_job_override_xml_data', 'JobController@getOverrideXmlData')->name('get_job_override_xml_data');
      Route::get('/jobs/get_schedule_date', 'JobController@getStartDate')->name('get_job_start_date');
      Route::post('/jobs/set_schedule_date', 'JobController@setStartDate')->name('set_job_start_date');
      //Schedules
      Route::get('schedule/{id}/lock', 'JobController@lockToggle')->name('schedule_lock');
      Route::get('schedule/{id}/delete', 'JobController@scheduleDelete')->name('schedule_delete');
      Route::get('schedule/{id}/send', 'JobController@scheduleSend')->name('schedule_send');
      Route::get('schedule/{id}/default', 'JobController@defaultEmail')->name('schedule_default_email');
      Route::post('schedule/schedule_close', 'JobController@scheduleClose')->name('schedule_close');
      Route::post('schedule/schedule_save_installer', 'JobController@scheduleSaveInstaller')->name('schedule_save_installer');
      Route::get('schedule/schedule_get_from_contractor_notes', 'JobController@getFromContractorNotes')->name('schedule_get_from_contractor_notes');
      Route::get('schedule/schedule_get_installer', 'JobController@getInstaller')->name('schedule_get_installer');
      Route::get('schedule/get_schedule_date', 'JobController@getScheduleDate')->name('get_schedule_date');
      Route::post('schedule/set_schedule_date', 'JobController@setScheduleDate')->name('set_schedule_date');
      Route::get('schedule/get_schedule_notes', 'JobController@getScheduleNotes')->name('get_schedule_notes');
      Route::post('schedule/set_schedule_notes', 'JobController@setScheduleNotes')->name('set_schedule_notes');
      Route::get('schedule/get_schedule_customer_notes', 'JobController@getScheduleCustomerNotes')->name('get_schedule_customer_notes');
      Route::post('schedule/set_schedule_customer_notes', 'JobController@setScheduleCustomerNotes')->name('set_schedule_customer_notes');
      //Po
      Route::get('/pos/display_po', 'PoController@displayPo')->name('display_po');
      Route::post('/pos/set_archived', 'PoController@setArchived')->name('set_po_archived');
      Route::get('/pos/get_po_type', 'PoController@getPoType')->name('get_po_type');
      Route::post('/pos/set_po_type', 'PoController@setPoType')->name('set_po_type');
      Route::get('/pos/get_po_company_invoice', 'PoController@getCompanyInvoice')->name('get_po_company_invoice');
      Route::post('/pos/set_po_company_invoice', 'PoController@setCompanyInvoice')->name('set_po_company_invoice');
      Route::get('/pos/get_po_projected_ship', 'PoController@getProjectedShip')->name('get_po_projected_ship');
      Route::post('/pos/set_po_projected_ship', 'PoController@setProjectedShip')->name('set_po_projected_ship');
      Route::post('/pos/save_po', 'PoController@savePo')->name('save_po');
      Route::get('po/{id}/order', 'PoController@order')->name('order_po');
      Route::get('po/{id}/confirm', 'PoController@confirm')->name('confirm_po');
      Route::post('po/delete_po', 'PoController@delete')->name('delete_po');
      Route::get('/pos/display_po_item', 'PoController@displayPoItem')->name('display_po_item');
      Route::get('item/{id}/unverify', 'PoController@unverify')->name('po_item_unverify');
      Route::get('po/{id}/item/{iid}/receive', 'PoController@receive')->name('po_item_receive');
      Route::get('po/{id}/item/{iid}/remove', 'PoController@removeItem')->name('po_item_remove');
      Route::post('po/new_po_item', 'PoController@newItem')->name('po_item_new');
      Route::post('po/delete_item', 'PoController@removeItem')->name('po_item_delete');
      Route::get('po/{id}/child', 'PoController@spawn')->name('po_child');
      Route::get('po/export', 'PoController@export')->name('po_export');
      Route::get('/po/{id}', 'PoController@viewPo')->name('view_po');
      //Receiving
      Route::get('receiving', 'ReceivingController@index')->name('receiving');
      Route::get('receiving/display_receiving', 'ReceivingController@displayReceiving')->name('display_receiving');
      Route::get('receiving/display_receiving_po', 'ReceivingController@displayReceivingPo')->name('display_receiving_po');
      Route::get('receiving/{id}', 'ReceivingController@view')->name('view_receiving');
      Route::get('receiving/{id}/item/{iid}/receive', 'ReceivingController@receive')->name('receiving_receive');
      Route::get('receiving/item/{id}/unverify', 'ReceivingController@unverify')->name('receiving_unverify');
      // Buildup
      Route::get('buildup', 'BuildController@index')->name('buildup');
      Route::get('buildup/display_job_sold', 'BuildController@displayJobSold')->name('buildup_job_sold');
      Route::get('build/{id}/build', 'BuildController@build')->name('buildup_job_build');
      Route::get('build/{id}/load', 'BuildController@load')->name('buildup_job_load');
      Route::get('build/{id}/left', 'BuildController@left')->name('buildup_job_left');
      Route::post('/build/{quote_id}/upload_file', 'BuildController@uploadFile')->name('build_upload_file');
      Route::post('/build/save_note', 'BuildController@saveNote')->name('save_buildup_note');
      Route::get('buildup/display_job_cabinet', 'BuildController@displayJobCabinet')->name('buildup_job_cabinet');
      Route::get('buildup/get_cabinet_notes', 'BuildController@getCabinetNotes')->name('get_cabinet_notes');
      Route::post('buildup/save_cabinet_notes', 'BuildController@saveCabinetNotes')->name('save_cabinet_notes');

      //Final Touch (Fft)
      Route::post('/ffts/set_visit_assigned_user', 'FftController@setVisitAssignedUser')->name('set_visit_assigned_user');
      Route::get('/ffts/get_visit_assigned_user', 'FftController@getVisitAssignedUser')->name('get_visit_assigned_user');
      Route::post('/ffts/set_punch_assigned_user', 'FftController@setPunchAssignedUser')->name('set_punch_assigned_user');
      Route::get('/ffts/get_punch_assigned_user', 'FftController@getPunchAssignedUser')->name('get_punch_assigned_user');
      Route::get('/ffts/display_ffts', 'FftController@displayFfts')->name('display_ffts');
      Route::get('/ffts/display_fft_notes', 'FftController@displayFftNotes')->name('display_fft_notes');
      Route::post('/ffts/save_fft_notes', 'FftController@saveFftNote')->name('save_fft_notes');
      Route::post('/ffts/delete_fft_notes', 'FftController@deleteFftNote')->name('delete_fft_notes');
      Route::get('/ffts/{id}/shop', 'FftController@shopFromFft')->name('shop_fft');
      Route::get('/ffts/{id}/payment', 'FftController@payment')->name('payment_fft');
      Route::get('/ffts/{id}/close', 'FftController@close')->name('close_fft');
      Route::get('/ffts/{id}/signature', 'FftController@signature')->name('signature_fft');
      Route::get('/ffts/{id}/signoff', 'FftController@signoff')->name('sigoff_fft');
      Route::get('/ffts/display_fft_job_items', 'FftController@displayJobItems')->name('display_fft_job_items');
      Route::post('/ffts/save_fft_signature', 'FftController@saveSign')->name('save_fft_signature');
      Route::post('/ffts/save_fft_signoff', 'FftController@saveSignOff')->name('save_fft_signoff');
      Route::post('/ffts/set_fft_pre_schedule', 'FftController@SetPreSchedule')->name('set_fft_pre_schedule');
      Route::get('/ffts/get_fft_pre_schedule', 'FftController@getPreSchedule')->name('get_fft_pre_schedule');
      Route::post('/ffts/set_fft_schedule_start', 'FftController@SetScheduleStart')->name('set_fft_schedule_start');
      Route::get('/ffts/get_fft_schedule_start', 'FftController@getScheduleStart')->name('get_fft_schedule_start');
      Route::get('/ffts/{id}/signature/pdf', 'FftController@signaturePdf')->name('fft_signature_pdf');
      Route::get('/ffts/{id}/signature/send', 'FftController@signatureSend')->name('fft_signature_send');
      Route::get('/ffts/{id}/signoff/pdf', 'FftController@signoffPdf')->name('fft_signoff_pdf');
      Route::get('/fft/{id}/item/{item}/update', 'FftController@trackItem')->name('fft_track_item');
      Route::get('fft/{id}/signature/pdf', 'FftController@signaturePdf')->name('fft_signature_pdf');
      Route::get('fft/{id}/punch/send', 'FftController@emailPunch')->name('fft_email_punch');
      Route::post('fft/{id}/pay', 'FftController@pay')->name('fft_pay');
      Route::get('warranties', 'FftController@warrantyIndex')->name('fft_warranty');
      Route::post('warranty/new', 'FftController@newWarranty')->name('fft_warranty_new');
      Route::get('service', 'FftController@serviceIndex')->name('fft_service');
      Route::post('service/new', 'FftController@newService')->name('fft_service_new');
      //Punches
      Route::get('/punches/display_punches_items', 'PunchController@displayPunchItems')->name('display_punch_items');
      Route::get('/punches/get_job_item', 'PunchController@getJobItem')->name('get_job_item');
      Route::post('/punches/set_job_item', 'PunchController@setJobItem')->name('set_job_item');
      Route::post('/punches/set_group', 'PunchController@setGroup')->name('set_group');
      Route::get('/punches/get_group', 'PunchController@getGroup')->name('get_group');
      Route::post('/punches/set_notes', 'PunchController@setNotes')->name('set_notes');
      Route::get('/punches/get_notes', 'PunchController@getNotes')->name('get_notes');
      Route::post('/punches/set_contractor_notes', 'PunchController@setContractorNotes')->name('set_contractor_notes');
      Route::get('/punches/get_contractor_notes', 'PunchController@getContractorNotes')->name('get_contractor_notes');
      Route::get('/punches/{id}', 'PunchController@index')->name('punch_job');
      Route::post('punches/fft/{id}/item/create', 'FftController@createItem');
      //Items
      Route::get('/item/{id}/delete', 'FftController@deleteItem')->name('item_delete');
      Route::get('item/{id}/replacement', 'FftController@toggleReplacement')->name('item_replacement');
      Route::get('item/{id}/orderable', 'FftController@toggleOrderable')->name('item_orderable');
      Route::get('item/{id}/contractor_complete', 'FftController@contractorComplete');
      Route::get('/item/{id}/file/{file_number}/download', 'FftController@downloadFile')->name('fft_download_file');
      //Change Orders
      Route::get('changes', 'ChangeController@index')->name('changes');
      Route::get('/changes/display_changes', 'ChangeController@displayChanges')->name('display_changes');
      Route::post('changes/new', 'ChangeController@create')->name('create_changes');
      Route::get('change/display_change_items', 'ChangeController@displayChangesItems')->name('display_changes_items');
      Route::post('change_item/delete', 'ChangeController@deleteItem')->name('delete_changes_items');
      Route::post('change_item/save_item', 'ChangeController@saveDetailItem')->name('save_detail_item');
      Route::get('/change/display_order_auth_items', 'ChangeController@displayOrderAuthItems')->name('display_order_auth_sign_items');
      Route::get('change/{id}', 'ChangeController@view')->name('view_changes');
      Route::get('change/{id}/send', 'ChangeController@send')->name('changes_send');
      Route::get('change/{id}/close', 'ChangeController@close');
      Route::post('/change/auth/save_auth_sign', 'ChangeController@saveAuthSign')->name('save_order_auth_sign');
      Route::post('/change/auth/remove_auth_sign', 'ChangeController@removeAuthSign')->name('remove_order_auth_sign');
      Route::get('change/{id}/decline', 'ChangeController@decline');
      //Shop
      Route::get('shop', 'ShopController@index')->name('shop');
      Route::post('shop/new', 'ShopController@saveShop')->name('save_shop');
      Route::get('shopitem/{id}/{type}', 'ShopController@setType');
      //Dashboard
      Route::get('dashboard', 'IndexController@dashboard')->name('dashboard');
      Route::get('dashboard/load_data_weekly', 'IndexController@weekly')->name('get_dashboard_weekly');
      Route::get('dashboard/load_data_monthly', 'IndexController@monthly')->name('get_dashboard_monthly');
      Route::get('dashboard/load_data_yearly', 'IndexController@yearly')->name('get_dashboard_yearly');
      Route::get('dashboard/load_data_lead_updates', 'IndexController@showleadUpdates')->name('get_dashboard_lead_updates');
      //Customer
      Route::get('customer/display_customer', 'CustomerController@displayCustomer')->name('display_customer');
      Route::get('customer/display_customer_job_notes', 'CustomerController@displayCustomerJobNotes')->name('display_customer_job_notes');
      Route::get('customer/get_customer_quotes', 'CustomerController@getCustomerQuotes')->name('customer_get_customer_quotes');
      Route::get('customer/{id}/job_multiple_auth', 'CustomerController@jobMultipleAuth')->name('customer_job_multiple_auth');
      Route::get('customer/{id}/get_job_auth_sign', 'CustomerController@getJobAuthSign')->name('customer_get_job_auth_sign');
      Route::get('customer/{id}/get_job_auth_status', 'CustomerController@getJobAuthStatus')->name('customer_get_job_auth_status');
      Route::post('customer/save_customer_job_notes', 'CustomerController@saveCustomerJobNotes')->name('save_customer_job_notes');
      Route::post('customer/save_jobs_auth_sign', 'CustomerController@saveCustomerJobAuthSign')->name('save_customer_jobs_auth_sign');
      Route::post('customer/remove_jobs_auth_sign', 'CustomerController@removeCustomerJobAuthSign')->name('remove_customer_jobs_auth_sign');
      //Profile
      Route::get('profile/{id}/view', 'ProfileController@view')->name('view_profile');
      Route::get('profile/get_customer_details', 'ProfileController@getCustomerDetails')->name('get_customer_details');
      Route::get('profile/get_customer_contacts', 'ProfileController@getCustomerContacts')->name('get_customer_contacts');
      Route::post('/profile/set_customer_name', 'ProfileController@setCustomerName')->name('set_customer_name');
      Route::get('/profile/get_customer_name', 'ProfileController@getCustomerName')->name('get_customer_name');
      Route::post('/profile/set_customer_address', 'ProfileController@setCustomerAddress')->name('set_customer_address');
      Route::get('/profile/get_customer_address', 'ProfileController@getCustomerAddress')->name('get_customer_address');
      Route::post('/profile/set_customer_city', 'ProfileController@setCustomerCity')->name('set_customer_city');
      Route::get('/profile/get_customer_city', 'ProfileController@getCustomerCity')->name('get_customer_city');
      Route::post('/profile/set_customer_state', 'ProfileController@setCustomerState')->name('set_customer_state');
      Route::get('/profile/get_customer_state', 'ProfileController@getCustomerState')->name('get_customer_state');
      Route::post('/profile/set_customer_zip', 'ProfileController@setCustomerZip')->name('set_customer_zip');
      Route::get('/profile/get_customer_zip', 'ProfileController@getCustomerZip')->name('get_customer_zip');
      Route::post('/profile/set_customer_job_address', 'ProfileController@setCustomerJobAddress')->name('set_customer_job_address');
      Route::get('/profile/get_customer_job_address', 'ProfileController@getCustomerJobAddress')->name('get_customer_job_address');
      Route::post('/profile/set_customer_job_city', 'ProfileController@setCustomerJobCity')->name('set_customer_job_city');
      Route::get('/profile/get_customer_job_city', 'ProfileController@getCustomerJobCity')->name('get_customer_job_city');
      Route::post('/profile/set_customer_job_state', 'ProfileController@setCustomerJobState')->name('set_customer_job_state');
      Route::get('/profile/get_customer_job_state', 'ProfileController@getCustomerJobState')->name('get_customer_job_state');
      Route::post('/profile/set_customer_job_zip', 'ProfileController@setCustomerJobZip')->name('set_customer_job_zip');
      Route::get('/profile/get_customer_job_zip', 'ProfileController@getCustomerJobZip')->name('get_customer_job_zip');
      Route::post('/profile/set_contact_name', 'ProfileController@setContactName')->name('set_contact_name');
      Route::get('/profile/get_contact_name', 'ProfileController@getContactName')->name('get_contact_name');
      Route::post('/profile/set_contact_email', 'ProfileController@setContactEmail')->name('set_contact_email');
      Route::get('/profile/get_contact_email', 'ProfileController@getContactEmail')->name('get_contact_email');
      Route::post('/profile/set_contact_mobile', 'ProfileController@setContactMobile')->name('set_contact_mobile');
      Route::get('/profile/get_contact_mobile', 'ProfileController@getContactMobile')->name('get_contact_mobile');
      Route::post('/profile/set_contact_home', 'ProfileController@setContactHome')->name('set_contact_home');
      Route::get('/profile/get_contact_home', 'ProfileController@getContactHome')->name('get_contact_home');
      Route::post('/profile/set_contact_alternate', 'ProfileController@setContactAlternate')->name('set_contact_alternate');
      Route::get('/profile/get_contact_alternate', 'ProfileController@getContactAlternate')->name('get_contact_alternate');
      Route::get('profile/get_customer_leads', 'ProfileController@getCustomerLeads')->name('get_customer_leads');
      Route::post('/profile/set_lead_source', 'ProfileController@setLeadSource')->name('set_lead_source');
      Route::get('/profile/get_lead_source', 'ProfileController@getLeadSource')->name('get_lead_source');
      Route::get('profile/get_customer_quotes', 'ProfileController@getCustomerQuotes')->name('get_customer_quotes');
      Route::get('profile/get_customer_jobs', 'ProfileController@getCustomerJobs')->name('get_customer_jobs');
      Route::get('profile/get_customer_final_touch_warranty', 'ProfileController@getCustomerFinalTouchWarranty')->name('get_customer_final_touch_warranty');
      Route::get('profile/get_customer_notes', 'ProfileController@getCustomerNotes')->name('get_customer_notes');
      Route::post('profile/save_customer_notes', 'ProfileController@saveCustomerNotes')->name('save_customer_notes');
      Route::get('profile/get_customer_tasks', 'ProfileController@getCustomerTasks')->name('get_customer_tasks');
      //Reports
      Route::get('reports', 'ReportController@index')->name('reports');
      Route::get('reports/get_leads_report', 'ReportController@getLeadsReport')->name('get_leads_report');
      Route::get('reports/get_users_report', 'ReportController@getUsersReport')->name('get_users_report');
      Route::get('reports/get_dashboard_source_type', 'ReportController@getSourceType')->name('get_dashboard_source_type');
      Route::get('reports/get_dashboard_user_type', 'ReportController@getUserType')->name('get_dashboard_user_type');
      Route::get('report/all/leads', 'ReportController@exportLeads')->name('export_leads');
      Route::get('report/all/zips', 'ReportController@exportZips')->name('export_zips');
      Route::get('report/frugal', 'ReportController@frugal')->name('frugal_report');
      Route::get('report/cabinets', 'ReportController@cabinets')->name('cabinet_report');
      Route::get('report/get_cabinets_report', 'ReportController@cabinetsReport')->name('get_cabinets_report');
      Route::get('report/get_cabinets_detail_report', 'ReportController@cabinetsDetailReport')->name('get_cabinets_detail_report');
      Route::get('report/designers', 'ReportController@designers')->name('designers_report');
      Route::get('report/get_designers_report', 'ReportController@designersReport')->name('get_designers_report');
      Route::get('report/get_designers_detail_report', 'ReportController@designersDetailReport')->name('get_designers_detail_report');
      Route::get('report/locations', 'ReportController@locations')->name('locations_report');
      Route::get('report/get_locations_report', 'ReportController@locationsReport')->name('get_locations_report');
      Route::get('report/get_locations_detail_report', 'ReportController@locationsDetailReport')->name('get_locations_detail_report');
      Route::get('report/promotions', 'ReportController@promotions')->name('promotions_report');
      Route::get('report/get_promotions_report', 'ReportController@promotionsReport')->name('get_promotions_report');
      Route::get('report/get_promotions_detail_report', 'ReportController@promotionsDetailReport')->name('get_promotions_detail_report');
      Route::get('report/finished_job', 'ReportController@finishedJob')->name('finished_job_report');
      Route::get('report/get_finished_job_report', 'ReportController@finishedJobReport')->name('get_finished_job_report');
      Route::get('report/get_lead_to_close_detail_report', 'ReportController@leadToCloseDetailReport')->name('get_lead_to_close_detail_report');
      Route::get('report/get_cabinet_install_date_detail_report', 'ReportController@cabinetInstallDateDetailReport')->name('get_cabinet_install_date_detail_report');
      Route::get('report/get_final_payment_date_detail_report', 'ReportController@finalPaymentDateDetailReport')->name('get_final_payment_date_detail_report');
      Route::get('report/get_closeout_date_detail_report', 'ReportController@closeoutDateDetailReport')->name('get_closeout_date_detail_report');
      //Payouts
      Route::get('payouts/load_payouts', 'PayoutController@loadPayouts')->name('load_payouts');
      Route::get('payouts/{id}/delete', 'PayoutController@deletePayout')->name('delete_payout');
      Route::get('payouts/{id}/approve', 'PayoutController@approvePayout')->name('approve_payout');
      Route::get('payouts/report/get_report_payouts', 'PayoutController@getReportPayouts')->name('get_report_payouts');
      Route::get('payouts/report/{user_id}', 'PayoutController@reportPayout')->name('report_payout');
      Route::post('payouts/report/{user_id}', 'PayoutController@createReportPayout')->name('create_report_payout');
      Route::post('payouts/{id}/update', 'PayoutController@updatePayout')->name('update_payout');

      //Payout Items
      Route::get('/payout_items/display_payout_items', 'PayoutItemController@displayPayoutItems')->name('display_payout_items');
      Route::get('/payout_items/get_payout_item', 'PayoutItemController@getPayoutItem')->name('get_payout_item');
      Route::post('/payout_items/update', 'PayoutItemController@updatePayoutItem')->name('update_payout_item');
      Route::post('/payout_items/delete', 'PayoutItemController@deletePayoutItem')->name('delete_payout_item');

      //Files
      Route::get('/files/display_files', 'FileController@displayFiles')->name('display_files');
      Route::get('/files/{id}/delete', 'FileController@deleteFile')->name('delete_file');
      Route::get('/files/{id}/download', 'FileController@downloadFile')->name('download_file');
      Route::post('/files/upload', 'FileController@upload')->name('upload_file');
    });

    // Main Resource Routes
    Route::resources([
        'customers'     => CustomerController::class,
        'leads'         => LeadController::class,
        'quotes'        => QuoteController::class,
        'tasks'         => TaskController::class,
        'jobs'          => JobController::class,
        'pos'           => PoController::class,
        'ffts'          => FftController::class,
        'payouts'       => PayoutController::class,
        'payout_items'  => PayoutItemController::class,
        'files'         => FileController::class,
    ]);

});
