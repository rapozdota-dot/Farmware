<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
	use HasFactory;

	protected $fillable = [
		'location',
		'season',
		'area_ha',
		'rainfall_mm',
		'temperature_c',
		'soil_ph',
		'fertilizer_kg',
		'yield_t_ha',
	];
}


