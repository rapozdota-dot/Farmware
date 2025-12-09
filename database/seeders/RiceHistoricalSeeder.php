<?php

namespace Database\Seeders;

use App\Models\Record;
use Illuminate\Database\Seeder;

class RiceHistoricalSeeder extends Seeder
{
	public function run(): void
	{
		// Diverse Philippine rice production data (100-150 records)
		// Yield formula approximation: base_yield + rainfall_factor + temp_factor + fertilizer_factor + soil_factor
		// Palo, Leyte is the main focus but balanced with other regions
		$rows = [
			// Palo, Leyte - Main focus area (Type II climate - no dry season, pronounced wet season)
			// Reduced to 20 records for balance while maintaining focus
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
			['Maasin, Leyte','Wet',1.5,385,26.5,6.2,175,5.5],
			['Borongan, Samar','Wet',1.6,400,26.3,6.3,180,5.8],
			
			// Central Luzon - Rice Granary of the Philippines
			['Tarlac City, Tarlac','Wet',2.1,280,27.1,6.3,155,5.0],
			['Tarlac City, Tarlac','Dry',2.1,105,29.2,6.4,185,5.3],
			['San Jose, Nueva Ecija','Wet',2.0,360,26.5,6.2,180,5.4],
			['San Jose, Nueva Ecija','Dry',2.0,120,28.5,6.4,200,5.8],
			['Cabanatuan, Nueva Ecija','Wet',2.2,370,26.4,6.3,195,5.6],
			['Cabanatuan, Nueva Ecija','Dry',1.8,110,28.7,6.5,205,5.9],
			['Malolos, Bulacan','Wet',1.9,320,26.9,6.4,160,5.2],
			['Malolos, Bulacan','Dry',1.9,108,29.3,6.5,190,5.4],
			['San Fernando, Pampanga','Wet',2.0,320,27.2,6.4,165,5.3],
			['San Fernando, Pampanga','Dry',2.0,115,29.1,6.5,195,5.5],
			['Baliuag, Bulacan','Wet',1.8,315,27.0,6.3,158,5.1],
			['Baliuag, Bulacan','Dry',1.8,106,29.2,6.4,188,5.3],
			
			// Ilocos Region
			['Laoag, Ilocos Norte','Wet',1.8,280,26.8,6.2,150,5.1],
			['Laoag, Ilocos Norte','Dry',1.8,95,29.0,6.3,180,5.4],
			['Vigan, Ilocos Sur','Wet',1.7,290,26.9,6.1,155,5.0],
			['Vigan, Ilocos Sur','Dry',1.7,100,28.9,6.2,185,5.3],
			['San Fernando, La Union','Wet',1.9,300,27.0,6.2,160,5.2],
			['San Fernando, La Union','Dry',1.9,105,29.1,6.3,190,5.5],
			
			// Cagayan Valley
			['Tuguegarao, Cagayan','Wet',2.4,410,26.2,6.0,175,5.6],
			['Tuguegarao, Cagayan','Dry',2.4,135,28.9,6.1,205,5.8],
			['Ilagan, Isabela','Wet',1.5,410,26.0,5.9,160,5.1],
			['Ilagan, Isabela','Dry',1.5,140,28.8,6.0,190,5.3],
			['Santiago, Isabela','Wet',1.7,420,25.9,6.0,170,5.2],
			['Santiago, Isabela','Dry',1.6,135,28.9,6.1,195,5.4],
			
			// CALABARZON
			['Calamba, Laguna','Wet',1.8,350,26.3,5.8,165,4.9],
			['Calamba, Laguna','Dry',1.8,130,28.1,6.0,185,5.1],
			['Los Baños, Laguna','Wet',1.9,360,26.2,5.9,170,5.0],
			['Los Baños, Laguna','Dry',1.9,125,28.2,6.1,190,5.2],
			['Lipa, Batangas','Wet',1.6,300,27.3,6.5,150,5.2],
			['Lipa, Batangas','Dry',1.6,112,29.2,6.6,190,5.5],
			['Lucena, Quezon','Wet',1.8,380,26.4,6.0,170,5.4],
			['Lucena, Quezon','Dry',1.8,125,28.5,6.1,200,5.6],
			['Antipolo, Rizal','Wet',1.7,330,26.5,6.1,160,5.1],
			['Antipolo, Rizal','Dry',1.7,115,28.8,6.2,190,5.4],
			
			// MIMAROPA
			['Calapan, Oriental Mindoro','Wet',1.6,340,27.1,6.2,165,5.2],
			['Calapan, Oriental Mindoro','Dry',1.6,120,29.0,6.3,195,5.5],
			['Puerto Princesa, Palawan','Wet',1.5,380,27.0,6.1,160,5.3],
			['Puerto Princesa, Palawan','Dry',1.5,130,29.2,6.2,190,5.6],
			
			// Bicol Region
			['Naga, Camarines Sur','Wet',1.5,400,26.2,5.9,160,5.1],
			['Naga, Camarines Sur','Dry',1.5,140,28.4,6.0,190,5.4],
			['Legazpi, Albay','Wet',1.6,410,26.1,5.8,165,5.0],
			['Legazpi, Albay','Dry',1.6,145,28.3,5.9,195,5.3],
			['Sorsogon City, Sorsogon','Wet',1.3,400,26.1,5.9,165,4.9],
			['Sorsogon City, Sorsogon','Dry',1.3,140,28.5,6.0,195,5.2],
			
			// Western Visayas
			['Iloilo City, Iloilo','Wet',2.0,390,26.6,6.2,170,5.5],
			['Iloilo City, Iloilo','Dry',2.0,145,28.6,6.3,200,5.7],
			['Bacolod, Negros Occidental','Wet',1.5,340,27.0,6.1,155,5.0],
			['Bacolod, Negros Occidental','Dry',1.5,100,29.4,6.2,185,5.2],
			['Roxas, Capiz','Wet',1.7,365,26.6,6.1,160,5.2],
			['Roxas, Capiz','Dry',1.7,128,28.9,6.2,195,5.4],
			['Kalibo, Aklan','Wet',1.6,370,26.7,6.0,165,5.3],
			['Kalibo, Aklan','Dry',1.6,125,28.8,6.1,195,5.5],
			
			// Central Visayas
			['Cebu City, Cebu','Wet',1.2,330,27.2,6.6,170,5.0],
			['Cebu City, Cebu','Dry',1.2,95,29.5,6.7,210,5.2],
			['Tagbilaran, Bohol','Wet',1.4,350,27.1,6.2,168,5.1],
			['Tagbilaran, Bohol','Dry',1.4,98,29.3,6.3,205,5.3],
			['Dumaguete, Negros Oriental','Wet',1.3,345,27.0,6.1,162,5.0],
			['Dumaguete, Negros Oriental','Dry',1.3,96,29.4,6.2,198,5.2],
			
			// Eastern Visayas (additional)
			['Tacloban, Leyte','Wet',1.7,390,26.6,6.2,185,5.6],
			['Ormoc, Leyte','Wet',1.9,380,26.5,6.2,195,5.7],
			['Baybay, Leyte','Wet',1.6,385,26.6,6.3,180,5.6],
			['Calbayog, Samar','Wet',2.0,395,26.4,6.2,200,5.9],
			['Catbalogan, Samar','Wet',1.7,375,26.6,6.1,185,5.7],
			['Maasin, Leyte','Wet',1.8,390,26.6,6.3,190,5.7],
			['Borongan, Samar','Wet',1.9,395,26.4,6.2,195,5.9],
			
			// Northern Mindanao
			['Cagayan de Oro, Misamis Oriental','Wet',1.8,380,26.7,6.3,170,5.6],
			['Cagayan de Oro, Misamis Oriental','Dry',1.8,130,28.8,6.4,200,5.8],
			['Iligan, Lanao del Norte','Wet',1.7,370,26.8,6.1,165,5.5],
			['Iligan, Lanao del Norte','Dry',1.7,120,29.0,6.2,195,5.7],
			['Valencia, Bukidnon','Wet',2.1,380,25.8,6.4,180,5.8],
			['Valencia, Bukidnon','Dry',2.1,140,28.2,6.5,210,6.0],
			['Malaybalay, Bukidnon','Wet',2.0,390,25.9,6.3,185,5.9],
			['Malaybalay, Bukidnon','Dry',2.0,135,28.3,6.4,205,6.1],
			
			// Davao Region
			['Davao City, Davao del Sur','Wet',2.3,420,26.8,6.1,175,5.6],
			['Davao City, Davao del Sur','Dry',2.3,160,28.9,6.3,205,5.7],
			['Tagum, Davao del Norte','Wet',2.2,415,26.7,6.2,180,5.7],
			['Tagum, Davao del Norte','Dry',2.2,155,29.0,6.4,210,5.8],
			['Digos, Davao del Sur','Wet',2.1,410,26.9,6.2,170,5.6],
			['Digos, Davao del Sur','Dry',2.1,150,29.1,6.3,200,5.7],
			
			// SOCCSKSARGEN
			['Koronadal, South Cotabato','Wet',2.2,360,26.5,6.5,170,5.7],
			['Koronadal, South Cotabato','Dry',2.2,125,28.7,6.6,205,5.9],
			['General Santos, South Cotabato','Wet',2.3,355,26.6,6.4,175,5.8],
			['General Santos, South Cotabato','Dry',2.3,120,28.8,6.5,210,6.0],
			['Kidapawan, North Cotabato','Wet',2.0,350,26.7,6.4,170,5.7],
			['Kidapawan, North Cotabato','Dry',2.0,118,28.9,6.5,200,5.9],
			['Tacurong, Sultan Kudarat','Wet',1.9,340,26.8,6.3,165,5.6],
			['Tacurong, Sultan Kudarat','Dry',1.9,115,29.1,6.4,195,5.8],
			
			// CARAGA
			['Butuan, Agusan del Norte','Wet',2.1,420,26.3,6.2,175,5.7],
			['Butuan, Agusan del Norte','Dry',2.1,150,28.6,6.3,205,5.9],
			['Surigao, Surigao del Norte','Wet',1.6,400,26.4,6.0,170,5.6],
			['Surigao, Surigao del Norte','Dry',1.6,140,28.7,6.1,200,5.8],
			['Tandag, Surigao del Sur','Wet',1.7,400,26.2,6.1,170,5.6],
			['Tandag, Surigao del Sur','Dry',1.7,135,28.5,6.2,200,5.8],
			
			// Zamboanga Peninsula
			['Zamboanga City, Zamboanga del Sur','Wet',1.4,350,26.9,6.1,160,5.2],
			['Zamboanga City, Zamboanga del Sur','Dry',1.4,120,29.1,6.2,195,5.4],
			['Dipolog, Zamboanga del Norte','Wet',1.5,345,27.0,6.0,165,5.3],
			['Dipolog, Zamboanga del Norte','Dry',1.5,118,29.2,6.1,198,5.5],
			
			// Bangsamoro
			['Cotabato City, Maguindanao','Wet',1.8,330,26.9,6.2,160,5.5],
			['Cotabato City, Maguindanao','Dry',1.8,110,29.2,6.3,190,5.7],
			['Marawi, Lanao del Sur','Wet',1.6,360,26.7,6.1,155,5.4],
			['Marawi, Lanao del Sur','Dry',1.6,125,29.0,6.2,188,5.6],
			
			// Cordillera Administrative Region
			['Baguio, Benguet','Wet',1.3,450,22.5,6.2,150,4.8],
			['Baguio, Benguet','Dry',1.3,180,24.5,6.3,180,5.1],
			['Bontoc, Mountain Province','Wet',1.5,420,24.5,6.0,160,5.2],
			['Bontoc, Mountain Province','Dry',1.5,150,27.0,6.1,190,5.4],
			['Lagawe, Ifugao','Wet',1.4,410,24.6,5.9,155,5.1],
			['Lagawe, Ifugao','Dry',1.4,145,27.1,6.0,185,5.3],
			['Tabuk, Kalinga','Wet',1.6,400,24.8,6.1,165,5.3],
			['Tabuk, Kalinga','Dry',1.6,140,27.3,6.2,195,5.5],
			['Bangued, Abra','Wet',1.7,380,25.2,6.2,170,5.4],
			['Bangued, Abra','Dry',1.7,130,27.7,6.3,200,5.6],
			
			// Additional Luzon locations
			['Dagupan, Pangasinan','Wet',2.5,300,27.0,6.5,150,5.3],
			['Dagupan, Pangasinan','Dry',2.5,110,29.0,6.6,190,5.6],
			['Urdaneta, Pangasinan','Wet',2.3,310,26.9,6.4,155,5.4],
			['Urdaneta, Pangasinan','Dry',2.3,108,29.1,6.5,192,5.7],
			['Balanga, Bataan','Wet',1.4,310,27.1,6.3,155,5.1],
			['Balanga, Bataan','Dry',1.4,102,29.4,6.4,185,5.3],
			['Baler, Aurora','Wet',1.8,380,26.4,6.2,170,5.3],
			['Baler, Aurora','Dry',1.8,118,28.6,6.3,200,5.6],
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


