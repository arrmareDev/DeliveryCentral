<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'api_key',
        'webhook_url',
        'webhook_secret',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public static function generateApiKey(): string
    {
        return 'rst_live_' . Str::random(48);
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }
}
