<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'products';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title_english',
        'title_hindi',
        'title_gujarati',
        'description',
        'image',
        'price',
        'stock',
    ];

    public function invoice_item(){
        return $this->hasMany(InvoiceItem::class,'product_id','id');
    }

    



}
