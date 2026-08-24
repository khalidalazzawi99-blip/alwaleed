<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherAttachment extends Model
{
    protected $fillable = ['company_id', 'voucher_type', 'voucher_id', 'original_name', 'path', 'mime_type', 'size'];
}
