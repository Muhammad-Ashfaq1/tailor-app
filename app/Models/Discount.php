<?php
namespace App\Models;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
class Discount extends Model { use BelongsToOrganization; protected $fillable=['name','code','description','discount_type','applies_to','value','max_discount_amount','starts_at','ends_at','usage_limit','is_active','is_combinable','requires_reason','requires_manager_approval','created_by','updated_by']; protected function casts(): array { return ['value'=>'decimal:2','max_discount_amount'=>'decimal:2','starts_at'=>'datetime','ends_at'=>'datetime','is_active'=>'boolean','is_combinable'=>'boolean','requires_reason'=>'boolean','requires_manager_approval'=>'boolean']; } public static function typeOptions(): array { return ['fixed'=>'Fixed','percentage'=>'Percentage']; } public static function appliesToOptions(): array { return ['bill'=>'Bill','item'=>'Item','customer_profile'=>'Customer Profile','voucher'=>'Voucher','promotion'=>'Promotion']; } }
