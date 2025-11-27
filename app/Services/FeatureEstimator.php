<?php

namespace App\Services;

use App\Models\Record;
use App\Services\WeatherService;
use Carbon\Carbon;

/**
 * Estimates agricultural features (rainfall, temperature, etc.)
 * based on location and date/season
 * Uses intelligent fallback: Location → Region → Season → Default
 */
class FeatureEstimator
{
	private const FOCUS_LOCATION = 'Palo';
	private const FOCUS_LOCATION_DISPLAY = 'Palo, Leyte';
	private const FOCUS_REGION = 'Eastern Visayas';
	private const FOCUS_PROVINCE = 'Leyte';
	private const FOCUS_CLIMATE_ZONE = 'Type II';

	private PhilippineGeography $geography;

	public function __construct()
	{
		$this->geography = new PhilippineGeography();
	}

	/**
	 * Estimate features for Palo, Leyte based on planting and harvest dates
	 * Uses intelligent geographic knowledge and optionally weather API for better predictions
	 *
	 * @param string $plantingDate Date string (Y-m-d format)
	 * @param string|null $harvestDate Optional harvest date (if not provided, calculated as planting + 120 days)
	 * @param bool $useWeatherApi Whether to try using weather API for real-time data
	 * @return array Estimated features with metadata
	 */
	public function estimateFromLocationAndDate(string $plantingDate, ?string $harvestDate = null, bool $useWeatherApi = true): array
	{
		$planting = Carbon::parse($plantingDate);
		$harvest = $harvestDate ? Carbon::parse($harvestDate) : $planting->copy()->addDays(120);
		
		// Determine season from planting date
		$season = $this->determineSeason($plantingDate);
		
		// Fixed geographic context for Palo, Leyte
		$geoContext = [
			'province' => self::FOCUS_PROVINCE,
			'region' => self::FOCUS_REGION,
			'climateZone' => self::FOCUS_CLIMATE_ZONE,
			'isMunicipality' => true,
		];
		
		// Try to get weather API data first (if enabled and available)
		$weatherData = null;
		if ($useWeatherApi) {
			$weatherService = new WeatherService();
			if ($weatherService->isConfigured()) {
				$weatherData = $weatherService->getGrowingSeasonWeather(self::FOCUS_LOCATION_DISPLAY, $planting, $harvest);
			}
		}
		
		// Priority 1: Try location-specific averages
		$locationAverages = $this->getLocationSeasonAverages(self::FOCUS_LOCATION, $season);
		if ($locationAverages) {
			$result = array_merge($locationAverages, [
				'hasLocationData' => true,
				'hasRegionData' => false,
				'province' => $geoContext['province'],
				'region' => $geoContext['region'],
				'climateZone' => $geoContext['climateZone'],
				'isMunicipality' => $geoContext['isMunicipality'] ?? false,
				'locationLabel' => self::FOCUS_LOCATION_DISPLAY,
				'plantingDate' => $plantingDate,
				'harvestDate' => $harvest->format('Y-m-d'),
				'growingDays' => $planting->diffInDays($harvest),
				'dataSource' => "Location-specific averages for ".self::FOCUS_LOCATION_DISPLAY." ({$season} season)",
			]);
			
			// Enhance with weather API data if available
			if ($weatherData) {
				$result['rainfall_mm'] = $weatherData['avg_rainfall_mm'] ?? $result['rainfall_mm'];
				$result['temperature_c'] = $weatherData['avg_temperature_c'] ?? $result['temperature_c'];
				$result['weatherApiUsed'] = true;
				$result['dataSource'] .= " + Weather API forecast";
			}
			
			return $result;
		}
		
		// Priority 2: Try regional averages (if we can identify the region)
		if ($geoContext['region']) {
			$regionAverages = $this->getRegionSeasonAverages($geoContext['region'], $season);
			if ($regionAverages) {
				$result = array_merge($regionAverages, [
					'hasLocationData' => false,
					'hasRegionData' => true,
					'province' => $geoContext['province'],
					'region' => $geoContext['region'],
					'climateZone' => $geoContext['climateZone'],
					'isMunicipality' => $geoContext['isMunicipality'] ?? false,
					'locationLabel' => self::FOCUS_LOCATION_DISPLAY,
					'plantingDate' => $plantingDate,
					'harvestDate' => $harvest->format('Y-m-d'),
					'growingDays' => $planting->diffInDays($harvest),
					'dataSource' => "Regional averages for {$geoContext['region']} region ({$season} season)",
				]);
				
				// Enhance with weather API data if available
				if ($weatherData) {
					$result['rainfall_mm'] = $weatherData['avg_rainfall_mm'] ?? $result['rainfall_mm'];
					$result['temperature_c'] = $weatherData['avg_temperature_c'] ?? $result['temperature_c'];
					$result['weatherApiUsed'] = true;
					$result['dataSource'] .= " + Weather API forecast";
				}
				
				return $result;
			}
		}
		
		// Priority 3: Fallback to overall season averages
		$seasonAverages = $this->getSeasonAverages($season);
		
		$result = array_merge($seasonAverages, [
			'hasLocationData' => false,
			'hasRegionData' => false,
			'province' => $geoContext['province'],
			'region' => $geoContext['region'],
			'climateZone' => $geoContext['climateZone'],
			'isMunicipality' => $geoContext['isMunicipality'] ?? false,
			'locationLabel' => self::FOCUS_LOCATION_DISPLAY,
			'plantingDate' => $plantingDate,
			'harvestDate' => $harvest->format('Y-m-d'),
			'growingDays' => $planting->diffInDays($harvest),
			'dataSource' => $geoContext['region'] 
				? "General {$season} season averages (location not in database, but identified as {$geoContext['region']})"
				: "General {$season} season averages (location not in database)",
		]);
		
		// Enhance with weather API data if available
		if ($weatherData) {
			$result['rainfall_mm'] = $weatherData['avg_rainfall_mm'] ?? $result['rainfall_mm'];
			$result['temperature_c'] = $weatherData['avg_temperature_c'] ?? $result['temperature_c'];
			$result['weatherApiUsed'] = true;
			$result['dataSource'] .= " + Weather API forecast";
		}
		
		return $result;
	}

	/**
	 * Determine season (Wet or Dry) from date
	 * For Philippines: Wet season is typically June-October, Dry is November-May
	 */
	private function determineSeason(string $date): string
	{
		try {
			$carbon = Carbon::parse($date);
			$month = $carbon->month;
			
			// Wet season: June (6) to October (10)
			// Dry season: November (11) to May (5)
			if ($month >= 6 && $month <= 10) {
				return 'Wet';
			}
			return 'Dry';
		} catch (\Exception $e) {
			// Default to Dry if date parsing fails
			return 'Dry';
		}
	}

	/**
	 * Get averages for a specific location and season
	 * Uses case-insensitive matching for better location detection
	 */
	private function getLocationSeasonAverages(string $location, string $season): ?array
	{
		$locationLower = strtolower($location);

		$records = Record::where('season', $season)
			->whereNotNull('rainfall_mm')
			->whereNotNull('temperature_c')
			->whereNotNull('soil_ph')
			->whereNotNull('fertilizer_kg')
			->whereNotNull('area_ha')
			->where(function ($query) use ($location, $locationLower) {
				$query->whereRaw('LOWER(location) = LOWER(?)', [$location])
					->orWhereRaw('LOWER(location) LIKE ?', ["%{$locationLower}%"]);
			})
			->get();

		if ($records->isEmpty()) {
			return null;
		}

		$count = $records->count();
		
		return [
			'rainfall_mm' => $records->avg('rainfall_mm') ?? 0,
			'temperature_c' => $records->avg('temperature_c') ?? 0,
			'soil_ph' => $records->avg('soil_ph') ?? 0,
			'fertilizer_kg' => $records->avg('fertilizer_kg') ?? 0,
			'area_ha' => $records->avg('area_ha') ?? 0,
		];
	}

	/**
	 * Get regional averages for a specific region and season
	 * Uses geographic knowledge to find locations in the same region
	 */
	private function getRegionSeasonAverages(string $region, string $season): ?array
	{
		// Get all provinces in this region
		$provinces = $this->geography->getProvincesInRegion($region);
		
		if (empty($provinces)) {
			return null;
		}
		
		// Find records from locations in this region
		$records = Record::where('season', $season)
			->whereNotNull('rainfall_mm')
			->whereNotNull('temperature_c')
			->whereNotNull('soil_ph')
			->whereNotNull('fertilizer_kg')
			->whereNotNull('area_ha')
			->where(function ($query) use ($provinces, $region) {
				// Match by province names
				foreach ($provinces as $province) {
					$query->orWhereRaw('LOWER(location) LIKE LOWER(?)', ["%{$province}%"]);
				}
				// Also match by region name
				$query->orWhereRaw('LOWER(location) LIKE LOWER(?)', ["%{$region}%"]);
			})
			->get();

		if ($records->isEmpty()) {
			return null;
		}

		return [
			'rainfall_mm' => $records->avg('rainfall_mm') ?? 0,
			'temperature_c' => $records->avg('temperature_c') ?? 0,
			'soil_ph' => $records->avg('soil_ph') ?? 0,
			'fertilizer_kg' => $records->avg('fertilizer_kg') ?? 0,
			'area_ha' => $records->avg('area_ha') ?? 0,
		];
	}

	/**
	 * Get overall averages for a season (across all locations)
	 */
	private function getSeasonAverages(string $season): array
	{
		$records = Record::where('season', $season)
			->whereNotNull('rainfall_mm')
			->whereNotNull('temperature_c')
			->whereNotNull('soil_ph')
			->whereNotNull('fertilizer_kg')
			->whereNotNull('area_ha')
			->get();

		if ($records->isEmpty()) {
			// Default values if no data exists
			return [
				'rainfall_mm' => $season === 'Wet' ? 350.0 : 120.0,
				'temperature_c' => $season === 'Wet' ? 26.5 : 28.8,
				'soil_ph' => 6.2,
				'fertilizer_kg' => 175.0,
				'area_ha' => 1.8,
			];
		}

		return [
			'rainfall_mm' => $records->avg('rainfall_mm') ?? 0,
			'temperature_c' => $records->avg('temperature_c') ?? 0,
			'soil_ph' => $records->avg('soil_ph') ?? 0,
			'fertilizer_kg' => $records->avg('fertilizer_kg') ?? 0,
			'area_ha' => $records->avg('area_ha') ?? 0,
		];
	}

}

