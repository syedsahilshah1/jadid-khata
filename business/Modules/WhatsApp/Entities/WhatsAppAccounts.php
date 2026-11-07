<?php

namespace Modules\WhatsApp\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Repair\Entities\JobSheet;

/**
 * @method static create($input)
 * @method static findOrFail($id)
 * @method static find($id)
 */
class WhatsAppAccounts extends Model
{
 /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'whatsapp_gateway';


    public static function customerDataRepair($id) : mixed {
        $results = JobSheet::join('contacts', 'contacts.id', '=', 'repair_job_sheets.contact_id')
        ->join('business_locations', 'business_locations.id', '=' , 'repair_job_sheets.location_id')
        ->join('repair_statuses', 'repair_statuses.id', '=' , 'repair_job_sheets.status_id')
        ->join('repair_device_models', 'repair_device_models.id', '=' , 'repair_job_sheets.device_model_id')
        ->where('repair_job_sheets.id', $id)
        ->select('repair_statuses.id as repair_status','business_locations.name as location','contacts.name as name','contacts.mobile as mobile', 
                    'repair_job_sheets.job_sheet_no as job_id','repair_statuses.name as status', 'repair_device_models.name as device','repair_job_sheets.created_at as date','repair_job_sheets.comment_by_ss')
        ->first();
     return $results;
    }

    /** TODO DONT TOUCH THIS FUNCTION ANYMORE
     * IM USING THIS FUNCTION FOR PERSONAL DEVELOPMENT ONLY
     * Please use your own models / entities
     * Don't remove this line
     **/
    public static function customerDataRepairTransfers($id) : mixed
    {
      $results = JobSheet::join('contacts', 'contacts.id', '=', 'repair_job_sheets.contact_id')
         ->join('business_locations', 'business_locations.id', '=' , 'repair_job_sheets.location_id')
         ->join('business_locations as bl_transfer', 'bl_transfer.id', '=' , 'repair_job_sheets.location_id_transfered')
         ->join('repair_statuses', 'repair_statuses.id', '=' , 'repair_job_sheets.status_id')
         ->join('repair_device_models', 'repair_device_models.id', '=' , 'repair_job_sheets.device_model_id')
         ->where('repair_job_sheets.id', $id)
         ->select('bl_transfer.name as location_transfer', 'repair_statuses.id as repair_status','business_locations.name as location','contacts.name as name','contacts.mobile as mobile', 
                     'repair_job_sheets.job_sheet_no as job_id','repair_statuses.name as status', 'repair_device_models.name as device','repair_job_sheets.created_at as date', 'repair_job_sheets.comment_by_ss')
         ->first();
      return $results;
    }

}
