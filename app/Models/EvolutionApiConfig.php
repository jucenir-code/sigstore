<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvolutionApiConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 
        'api_url', 
        'api_key', 
        'instance_name',
        'webhook_url',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
} 