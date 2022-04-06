<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'invoices';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'language',
        'invoice_no',
        'user_id',
        'invoice_date',
        'total_price',
        'total_qty',
        'total_discount',
        'final_amount',
        'estatus',
    ];

    public function invoice_item(){
        return $this->hasMany(InvoiceItem::class,'invoice_id','id');
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
}
