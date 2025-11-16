<?php

namespace App\Services;

/**
 * Philippine Geography Service
 * Maps locations to regions and provides geographic context for better predictions
 */
class PhilippineGeography
{
	/**
	 * Major Philippine regions and their provinces
	 */
	private array $regions = [
		'Ilocos Region' => ['Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'],
		'Cagayan Valley' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'],
		'Central Luzon' => ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'],
		'CALABARZON' => ['Batangas', 'Cavite', 'Laguna', 'Quezon', 'Rizal'],
		'MIMAROPA' => ['Marinduque', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Romblon'],
		'Bicol Region' => ['Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes', 'Masbate', 'Sorsogon'],
		'Western Visayas' => ['Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'],
		'Central Visayas' => ['Bohol', 'Cebu', 'Negros Oriental', 'Siquijor'],
		'Eastern Visayas' => ['Biliran', 'Eastern Samar', 'Leyte', 'Northern Samar', 'Samar', 'Southern Leyte'],
		'Zamboanga Peninsula' => ['Zamboanga del Norte', 'Zamboanga del Sur', 'Zamboanga Sibugay', 'Zamboanga'],
		'Northern Mindanao' => ['Bukidnon', 'Camiguin', 'Lanao del Norte', 'Misamis Occidental', 'Misamis Oriental'],
		'Davao Region' => ['Davao del Norte', 'Davao del Sur', 'Davao Oriental', 'Davao de Oro', 'Davao Occidental', 'Davao'],
		'SOCCSKSARGEN' => ['Cotabato', 'Sarangani', 'South Cotabato', 'Sultan Kudarat'],
		'Caraga' => ['Agusan del Norte', 'Agusan del Sur', 'Dinagat Islands', 'Surigao del Norte', 'Surigao del Sur'],
		'BARMM' => ['Basilan', 'Lanao del Sur', 'Maguindanao', 'Sulu', 'Tawi-Tawi'],
		'Cordillera' => ['Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province'],
		'National Capital Region' => ['Manila', 'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque', 'Pasay', 'Pasig', 'Pateros', 'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'],
	];

	/**
	 * Municipality to Province mapping
	 * Maps municipalities/cities to their provinces
	 */
	private array $municipalities = [
		// Eastern Visayas - Leyte
		'Leyte' => ['Tacloban', 'Ormoc', 'Palo', 'Baybay', 'Maasin', 'Calbayog', 'Catbalogan', 'Borongan', 'Catarman', 'Abuyog', 'Carigara', 'Dulag', 'Hilongos', 'Kananga', 'Matalom', 'Merida', 'Palompon', 'Tanauan', 'Tolosa', 'Babatngon', 'Barugo', 'Burauen', 'Capoocan', 'Dagami', 'Jaro', 'Javier', 'Julita', 'La Paz', 'Leyte', 'Macarthur', 'Mayorga', 'Pastrana', 'San Isidro', 'San Miguel', 'Santa Fe', 'Tabontabon', 'Tunga', 'Villaba'],
		
		// Eastern Visayas - Samar
		'Samar' => ['Calbayog', 'Catbalogan', 'Basey', 'Calbiga', 'Daram', 'Gandara', 'Hinabangan', 'Jiabong', 'Marabut', 'Motiong', 'Pagsanghan', 'Paranas', 'Pinabacdao', 'San Jorge', 'San Jose de Buan', 'San Sebastian', 'Santa Margarita', 'Santo Niño', 'Talalora', 'Tarangnan', 'Villareal', 'Zumarraga'],
		
		// Eastern Visayas - Eastern Samar
		'Eastern Samar' => ['Borongan', 'Guiuan', 'Dolores', 'Can-avid', 'General MacArthur', 'Giporlos', 'Hernani', 'Jipapad', 'Lawaan', 'Llorente', 'Maslog', 'Maydolong', 'Mercedes', 'Oras', 'Quinapondan', 'Salcedo', 'San Julian', 'San Policarpo', 'Sulat', 'Taft'],
		
		// Central Luzon - Nueva Ecija
		'Nueva Ecija' => ['Cabanatuan', 'Palayan', 'Gapan', 'San Jose', 'Muñoz', 'Talavera', 'Guimba', 'Quezon', 'Aliaga', 'Bongabon', 'Cabiao', 'Carranglan', 'Cuyapo', 'Gabaldon', 'General Mamerto Natividad', 'General Tinio', 'Jaen', 'Laur', 'Llanera', 'Lupao', 'Nampicuan', 'Pantabangan', 'Peñaranda', 'Rizal', 'San Antonio', 'San Isidro', 'San Leonardo', 'Santa Rosa', 'Santo Domingo', 'Science City of Muñoz', 'Talugtug', 'Zaragoza'],
		
		// Central Luzon - Pampanga
		'Pampanga' => ['Angeles', 'San Fernando', 'Mabalacat', 'Apalit', 'Arayat', 'Bacolor', 'Candaba', 'Floridablanca', 'Guagua', 'Lubao', 'Macabebe', 'Magalang', 'Masantol', 'Mexico', 'Minalin', 'Porac', 'San Luis', 'San Simon', 'Santa Ana', 'Santa Rita', 'Santo Tomas', 'Sasmuan'],
		
		// Central Luzon - Bulacan
		'Bulacan' => ['Malolos', 'Meycauayan', 'San Jose del Monte', 'Baliuag', 'Calumpit', 'Marilao', 'Obando', 'Pandi', 'Paombong', 'Plaridel', 'Pulilan', 'San Ildefonso', 'San Miguel', 'San Rafael', 'Santa Maria', 'Angat', 'Balagtas', 'Bocaue', 'Bulakan', 'Bustos', 'Doña Remedios Trinidad', 'Guiguinto', 'Hagonoy', 'Norzagaray', 'San Jose del Monte'],
		
		// CALABARZON - Laguna
		'Laguna' => ['Calamba', 'San Pablo', 'Santa Rosa', 'Biñan', 'Cabuyao', 'Los Baños', 'San Pedro', 'Alaminos', 'Bay', 'Calauan', 'Cavinti', 'Famy', 'Kalayaan', 'Liliw', 'Luisiana', 'Lumban', 'Mabitac', 'Magdalena', 'Majayjay', 'Nagcarlan', 'Paete', 'Pagsanjan', 'Pakil', 'Pangil', 'Pila', 'Rizal', 'Santa Cruz', 'Santa Maria', 'Siniloan', 'Victoria'],
		
		// CALABARZON - Batangas
		'Batangas' => ['Batangas City', 'Lipa', 'Tanauan', 'Calaca', 'Lemery', 'Nasugbu', 'Balayan', 'Bauan', 'Calatagan', 'Cuenca', 'Ibaan', 'Laurel', 'Lian', 'Lobo', 'Mabini', 'Malvar', 'Mataasnakahoy', 'Padre Garcia', 'Rosario', 'San Jose', 'San Juan', 'San Luis', 'San Nicolas', 'San Pascual', 'Santa Teresita', 'Santo Tomas', 'Taal', 'Talisay', 'Taysan', 'Tingloy', 'Tuy'],
		
		// Central Visayas - Cebu
		'Cebu' => ['Cebu City', 'Lapu-Lapu', 'Mandaue', 'Talisay', 'Toledo', 'Danao', 'Bogo', 'Carcar', 'Naga', 'Consolacion', 'Liloan', 'Compostela', 'Cordova', 'Minglanilla', 'San Fernando', 'Alcantara', 'Alcoy', 'Alegria', 'Aloguinsan', 'Argao', 'Asturias', 'Badian', 'Balamban', 'Bantayan', 'Barili', 'Boljoon', 'Borbon', 'Carmen', 'Catmon', 'Daanbantayan', 'Dalaguete', 'Dumanjug', 'Ginatilan', 'Liloan', 'Madridejos', 'Malabuyoc', 'Medellin', 'Mogpog', 'Oslob', 'Pilar', 'Pinamungajan', 'Poro', 'Ronda', 'Samboan', 'San Francisco', 'San Remigio', 'Santa Fe', 'Santander', 'Sibonga', 'Sogod', 'Tabogon', 'Tabuelan', 'Tuburan', 'Tudela'],
		
		// Central Visayas - Bohol
		'Bohol' => ['Tagbilaran', 'Jagna', 'Talibon', 'Ubay', 'Cortes', 'Dauis', 'Panglao', 'Alicia', 'Anda', 'Antequera', 'Baclayon', 'Balilihan', 'Batuan', 'Bien Unido', 'Bilar', 'Buenavista', 'Calape', 'Candijay', 'Carmen', 'Catigbian', 'Clarin', 'Corella', 'Dagohoy', 'Danao', 'Dauis', 'Dimiao', 'Duero', 'Garcia Hernandez', 'Getafe', 'Guindulman', 'Inabanga', 'Jagna', 'Lila', 'Loay', 'Loboc', 'Loon', 'Mabini', 'Maribojoc', 'Panglao', 'Pilar', 'Pres. Carlos P. Garcia', 'Sagbayan', 'San Isidro', 'San Miguel', 'Sevilla', 'Sierra Bullones', 'Sikatuna', 'Talibon', 'Trinidad', 'Tubigon', 'Ubay', 'Valencia'],
		
		// Western Visayas - Iloilo
		'Iloilo' => ['Iloilo City', 'Passi', 'Roxas', 'Calinog', 'Miagao', 'Oton', 'San Joaquin', 'Santa Barbara', 'Tigbauan', 'Ajuy', 'Alimodian', 'Anilao', 'Badiangan', 'Balasan', 'Banate', 'Barotac Nuevo', 'Barotac Viejo', 'Batad', 'Bingawan', 'Cabatuan', 'Calinog', 'Carles', 'Concepcion', 'Dingle', 'Dueñas', 'Dumangas', 'Estancia', 'Guimbal', 'Igbaras', 'Janiuay', 'Lambunao', 'Leganes', 'Lemery', 'Leon', 'Maasin', 'New Lucena', 'Pavia', 'Pototan', 'San Dionisio', 'San Enrique', 'San Miguel', 'San Rafael', 'Santa Barbara', 'Sara', 'Tigbauan', 'Tubungan', 'Zarraga'],
		
		// Bicol Region - Albay
		'Albay' => ['Legazpi', 'Ligao', 'Tabaco', 'Daraga', 'Camalig', 'Guinobatan', 'Malilipot', 'Malinao', 'Manito', 'Oas', 'Pio Duran', 'Polangui', 'Rapu-Rapu', 'Santo Domingo', 'Tiwi'],
		
		// Bicol Region - Camarines Sur
		'Camarines Sur' => ['Naga', 'Iriga', 'Calabanga', 'Libmanan', 'Sipocot', 'Baao', 'Bato', 'Bombon', 'Buhi', 'Bula', 'Cabusao', 'Camaligan', 'Canaman', 'Caramoan', 'Del Gallego', 'Gainza', 'Garchitorena', 'Goa', 'Lagonoy', 'Magarao', 'Milaor', 'Minalabac', 'Nabua', 'Ocampo', 'Pamplona', 'Pasacao', 'Pili', 'Presentacion', 'Ragay', 'Sagñay', 'San Fernando', 'San Jose', 'Sipocot', 'Siruma', 'Tigaon', 'Tinambac'],
		
		// Davao Region - Davao del Sur
		'Davao del Sur' => ['Davao City', 'Digos', 'Santa Cruz', 'Bansalan', 'Hagonoy', 'Magsaysay', 'Malalag', 'Matanao', 'Padada', 'Sulop'],
		
		// Davao Region - Davao del Norte
		'Davao del Norte' => ['Tagum', 'Panabo', 'Island Garden City of Samal', 'Asuncion', 'Braulio E. Dujali', 'Carmen', 'Kapalong', 'New Corella', 'San Isidro', 'Santo Tomas', 'Talaingod'],
		
		// Ilocos Region - Pangasinan
		'Pangasinan' => ['Dagupan', 'San Carlos', 'Urdaneta', 'Alaminos', 'Lingayen', 'Mangaldan', 'Calasiao', 'Malasiqui', 'Bayambang', 'Binmaley', 'Bolinao', 'Bugallon', 'Burgos', 'Dasol', 'Infanta', 'Labrador', 'Laoac', 'Mabini', 'Mapandan', 'Natividad', 'Pozorrubio', 'Rosales', 'San Fabian', 'San Jacinto', 'San Manuel', 'San Nicolas', 'Sison', 'Sual', 'Tayug', 'Villasis'],
		
		// Cagayan Valley - Isabela
		'Isabela' => ['Santiago', 'Cauayan', 'Ilagan', 'Alicia', 'Angadanan', 'Aurora', 'Benito Soliven', 'Burgos', 'Cabagan', 'Cabatuan', 'Cordon', 'Delfin Albano', 'Dinapigue', 'Divilacan', 'Echague', 'Gamu', 'Jones', 'Luna', 'Maconacon', 'Mallig', 'Naguilian', 'Palanan', 'Quezon', 'Quirino', 'Ramon', 'Reina Mercedes', 'Roxas', 'San Agustin', 'San Guillermo', 'San Isidro', 'San Manuel', 'San Mariano', 'San Mateo', 'San Pablo', 'Santa Maria', 'Santo Tomas', 'Tumauini'],
	];

	/**
	 * Climate zones and their characteristics
	 */
	private array $climateZones = [
		'Type I' => ['description' => 'Two pronounced seasons: dry from November to April, wet during the rest of the year', 'regions' => ['Ilocos Region', 'Central Luzon', 'CALABARZON', 'Western Visayas', 'Northern Mindanao']],
		'Type II' => ['description' => 'No dry season with a very pronounced maximum rain period from December to February', 'regions' => ['Bicol Region', 'Eastern Visayas', 'Caraga']],
		'Type III' => ['description' => 'Seasons are not very pronounced, relatively dry from November to April, and wet during the rest of the year', 'regions' => ['Cagayan Valley', 'MIMAROPA', 'Central Visayas', 'Zamboanga Peninsula', 'Davao Region']],
		'Type IV' => ['description' => 'Rainfall more or less evenly distributed throughout the year', 'regions' => ['SOCCSKSARGEN', 'BARMM', 'Cordillera', 'National Capital Region']],
	];

	/**
	 * Get province for a municipality
	 */
	private function getProvinceFromMunicipality(string $location): ?string
	{
		$locationLower = strtolower(trim($location));
		
		foreach ($this->municipalities as $province => $municipalities) {
			foreach ($municipalities as $municipality) {
				$municipalityLower = strtolower($municipality);
				// Exact match or contains
				if ($locationLower === $municipalityLower || 
					stripos($locationLower, $municipalityLower) !== false ||
					stripos($municipalityLower, $locationLower) !== false) {
					return $province;
				}
			}
		}
		
		return null;
	}

	/**
	 * Get region for a location
	 * Priority: Municipality → Province → Region → Variations
	 */
	public function getRegion(string $location): ?string
	{
		$location = trim($location);
		$locationLower = strtolower($location);
		
		// Priority 1: Check if it's a municipality
		$province = $this->getProvinceFromMunicipality($location);
		if ($province) {
			// Find which region this province belongs to
			foreach ($this->regions as $region => $provinces) {
				foreach ($provinces as $prov) {
					if (stripos($province, $prov) !== false || stripos($prov, $province) !== false) {
						return $region;
					}
				}
			}
		}
		
		// Priority 2: Direct location name matching (provinces and regions)
		foreach ($this->regions as $region => $provinces) {
			// Check if location matches a province name
			foreach ($provinces as $province) {
				if (stripos($location, $province) !== false || stripos($province, $location) !== false) {
					return $region;
				}
			}
			
			// Check if location matches region name
			if (stripos($location, $region) !== false || stripos($region, $location) !== false) {
				return $region;
			}
		}
		
		// Priority 3: Fuzzy matching for common variations
		$variations = [
			'nueva ecija' => 'Central Luzon',
			'isabela' => 'Cagayan Valley',
			'cebu' => 'Central Visayas',
			'davao' => 'Davao Region',
			'iloilo' => 'Western Visayas',
			'laguna' => 'CALABARZON',
			'pangasinan' => 'Ilocos Region',
			'tarlac' => 'Central Luzon',
			'bicol' => 'Bicol Region',
			'leyte' => 'Eastern Visayas',
			'zamboanga' => 'Zamboanga Peninsula',
			'south cotabato' => 'SOCCSKSARGEN',
			'bulacan' => 'Central Luzon',
			'batangas' => 'CALABARZON',
			'aurora' => 'Central Luzon',
			'bataan' => 'Central Luzon',
			'capiz' => 'Western Visayas',
			'negros' => 'Central Visayas', // Could be Occidental or Oriental, default to Central
			'sorsogon' => 'Bicol Region',
			// Municipality-specific shortcuts
			'tacloban' => 'Eastern Visayas',
			'palo' => 'Eastern Visayas',
			'ormoc' => 'Eastern Visayas',
			'calbayog' => 'Eastern Visayas',
			'catbalogan' => 'Eastern Visayas',
		];
		
		foreach ($variations as $key => $region) {
			if (stripos($locationLower, $key) !== false) {
				return $region;
			}
		}
		
		return null;
	}

	/**
	 * Get climate zone for a region
	 */
	public function getClimateZone(string $region): ?string
	{
		foreach ($this->climateZones as $zone => $data) {
			if (in_array($region, $data['regions'])) {
				return $zone;
			}
		}
		return null;
	}

	/**
	 * Get all provinces in a region
	 */
	public function getProvincesInRegion(string $region): array
	{
		return $this->regions[$region] ?? [];
	}

	/**
	 * Get geographic context for a location
	 */
	public function getGeographicContext(string $location): array
	{
		$province = $this->getProvinceFromMunicipality($location);
		$region = $this->getRegion($location);
		$climateZone = $region ? $this->getClimateZone($region) : null;
		
		return [
			'province' => $province,
			'region' => $region,
			'climateZone' => $climateZone,
			'hasRegionData' => $region !== null,
			'isMunicipality' => $province !== null,
		];
	}

	/**
	 * Get all available regions
	 */
	public function getAllRegions(): array
	{
		return array_keys($this->regions);
	}
}

