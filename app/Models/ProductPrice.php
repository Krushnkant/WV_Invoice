<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPrice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_prices';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'price',
        'estatus',
    ];

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function product(){
        return $this->hasOne(Product::class,'id','product_id');
    }

}
