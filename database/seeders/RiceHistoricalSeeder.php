<?php

namespace Database\Seeders;

use App\Models\Record;
use Illuminate\Database\Seeder;

class RiceHistoricalSeeder extends Seeder
{
	public function run(): void
	{
		// More realistic data with better correlations and more Palo, Leyte records
		// Yield formula approximation: base_yield + rainfall_factor + temp_factor + fertilizer_factor + soil_factor
		$rows = [
			// Palo, Leyte specific data (Type II climate - no dry season, pronounced wet season)
			['Palo, Leyte','Wet',1.5,380,26.5,6.2,175,5.4],
			['Palo, Leyte','Wet',1.8,420,26.2,6.3,190,5.7],
			['Palo, Leyte','Wet',2.0,360,26.8,6.1,180,5.5],
			['Palo, Leyte','Wet',1.6,400,26.4,6.2,185,5.6],
			['Palo, Leyte','Wet',1.9,390,26.6,6.3,195,5.8],
			['Palo, Leyte','Wet',2.1,370,26.7,6.2,200,5.9],
			['Palo, Leyte','Wet',1.7,410,26.3,6.4,185,5.7],
			['Palo, Leyte','Wet',1.4,350,26.9,6.0,170,5.2],
			['Palo, Leyte','Wet',2.2,385,26.5,6.3,210,6.0],
			['Palo, Leyte','Wet',1.5,395,26.6,6.1,175,5.5],
			['Palo, Leyte','Wet',1.8,375,26.8,6.2,190,5.6],
			['Palo, Leyte','Wet',2.0,405,26.4,6.3,205,5.9],
			['Palo, Leyte','Wet',1.6,365,26.7,6.1,180,5.4],
			['Palo, Leyte','Wet',1.9,415,26.3,6.4,195,5.8],
			['Palo, Leyte','Wet',2.1,380,26.5,6.2,200,5.9],
			['Palo, Leyte','Wet',1.7,390,26.6,6.3,185,5.6],
			['Palo, Leyte','Wet',1.4,340,27.0,5.9,165,5.1],
			['Palo, Leyte','Wet',2.2,400,26.4,6.3,210,6.1],
			['Palo, Leyte','Wet',1.5,370,26.8,6.1,175,5.4],
			['Palo, Leyte','Wet',1.8,410,26.2,6.4,190,5.8],
			
			// Eastern Visayas region (similar climate to Palo)
			['Tacloban, Leyte','Wet',1.6,385,26.5,6.2,180,5.5],
			['Tacloban, Leyte','Wet',1.9,395,26.4,6.3,195,5.7],
			['Ormoc, Leyte','Wet',1.7,375,26.6,6.1,185,5.6],
			['Baybay, Leyte','Wet',1.5,390,26.7,6.2,175,5.5],
			['Calbayog, Samar','Wet',1.8,400,26.3,6.3,190,5.8],
			['Catbalogan, Samar','Wet',1.6,380,26.5,6.2,180,5.6],
			
			// Other regions with realistic correlations
			['Nueva Ecija','Wet',2.0,360,26.5,6.2,180,5.4],
			['Nueva Ecija','Dry',2.0,120,28.5,6.4,200,5.8],
			['Nueva Ecija','Wet',2.2,370,26.4,6.3,195,5.6],
			['Nueva Ecija','Dry',1.8,110,28.7,6.5,205,5.9],
			['Nueva Ecija','Wet',1.9,350,26.6,6.1,185,5.5],
			
			['Isabela','Wet',1.5,410,26.0,5.9,160,5.1],
			['Isabela','Dry',1.5,140,28.8,6.0,190,5.3],
			['Isabela','Wet',1.7,420,25.9,6.0,170,5.2],
			['Isabela','Dry',1.6,135,28.9,6.1,195,5.4],
			
			['Cebu','Wet',1.2,330,27.2,6.6,170,5.0],
			['Cebu','Dry',1.2,95,29.5,6.7,210,5.2],
			['Cebu','Wet',1.4,340,27.1,6.5,175,5.1],
			['Cebu','Dry',1.3,100,29.4,6.8,215,5.3],
			
			['Davao','Wet',2.3,420,26.8,6.1,175,5.6],
			['Davao','Dry',2.3,160,28.9,6.3,205,5.7],
			['Davao','Wet',2.1,410,26.9,6.2,180,5.7],
			['Davao','Dry',2.2,155,29.0,6.4,210,5.8],
			
			['Laguna','Wet',1.8,350,26.3,5.8,165,4.9],
			['Laguna','Dry',1.8,130,28.1,6.0,185,5.1],
			['Laguna','Wet',1.9,360,26.2,5.9,170,5.0],
			
			['Pangasinan','Wet',2.5,300,27.0,6.5,150,5.3],
			['Pangasinan','Dry',2.5,110,29.0,6.6,190,5.6],
			['Pangasinan','Wet',2.3,310,26.9,6.4,155,5.4],
			
			['Tarlac','Wet',2.1,280,27.1,6.3,155,5.0],
			['Tarlac','Dry',2.1,105,29.2,6.4,185,5.3],
			['Tarlac','Wet',1.9,290,27.0,6.2,160,5.1],
			
			['Bicol','Wet',1.7,420,26.0,5.6,160,4.8],
			['Bicol','Dry',1.7,150,28.4,5.8,190,5.0],
			['Bicol','Wet',1.8,430,25.9,5.7,165,4.9],
			
			['Iloilo','Wet',2.0,390,26.6,6.2,170,5.5],
			['Iloilo','Dry',2.0,145,28.6,6.3,200,5.7],
			['Iloilo','Wet',1.9,385,26.7,6.1,175,5.6],
			
			['Zamboanga','Wet',1.4,350,26.9,6.1,160,5.2],
			['Zamboanga','Dry',1.4,120,29.1,6.2,195,5.4],
			
			['Cagayan','Wet',2.4,410,26.2,6.0,175,5.6],
			['Cagayan','Dry',2.4,135,28.9,6.1,205,5.8],
			['Cagayan','Wet',2.2,400,26.3,6.1,180,5.7],
			
			['Leyte','Wet',1.6,370,26.7,6.3,165,5.1],
			['Leyte','Dry',1.6,115,28.8,6.4,195,5.3],
			['Leyte','Wet',1.7,375,26.6,6.2,170,5.2],
			
			['South Cotabato','Wet',2.2,360,26.5,6.5,170,5.7],
			['South Cotabato','Dry',2.2,125,28.7,6.6,205,5.9],
			
			['Bulacan','Wet',1.9,320,26.9,6.4,160,5.2],
			['Bulacan','Dry',1.9,108,29.3,6.5,190,5.4],
			
			['Negros','Wet',1.5,340,27.0,6.1,155,5.0],
			['Negros','Dry',1.5,100,29.4,6.2,185,5.2],
			
			['Sorsogon','Wet',1.3,400,26.1,5.9,165,4.9],
			['Sorsogon','Dry',1.3,140,28.5,6.0,195,5.2],
			
			['Aurora','Wet',1.8,380,26.4,6.2,170,5.3],
			['Aurora','Dry',1.8,118,28.6,6.3,200,5.6],
			
			['Batangas','Wet',1.6,300,27.3,6.5,150,5.2],
			['Batangas','Dry',1.6,112,29.2,6.6,190,5.5],
			
			['Bataan','Wet',1.4,310,27.1,6.3,155,5.1],
			['Bataan','Dry',1.4,102,29.4,6.4,185,5.3],
			
			['Capiz','Wet',1.7,365,26.6,6.1,160,5.2],
			['Capiz','Dry',1.7,128,28.9,6.2,195,5.4],
		];

		foreach ($rows as $r) {
			Record::create([
				'location' => $r[0],
				'season' => $r[1],
				'area_ha' => $r[2],
				'rainfall_mm' => $r[3],
				'temperature_c' => $r[4],
				'soil_ph' => $r[5],
				'fertilizer_kg' => $r[6],
				'yield_t_ha' => $r[7],
			]);
		}
	}
}


