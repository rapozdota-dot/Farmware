<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Weather Service for fetching real-time and forecast weather data
 * Uses OpenWeatherMap API (free tier available)
 */
class WeatherService
{
	private ?string $apiKey;
	private string $baseUrl = 'https://api.openweathermap.org/data/2.5';

	public function __construct()
	{
		// Get API key from environment (user needs to add WEATHER_API_KEY to .env)
		$this->apiKey = env('WEATHER_API_KEY');
	}

	/**
	 * Get current weather for a location
	 */
	public function getCurrentWeather(string $location, string $country = 'PH'): ?array
	{
		if (!$this->apiKey) {
			return null;
		}

		$cacheKey = "weather_current_{$location}_{$country}";
		
		return Cache::remember($cacheKey, 3600, function () use ($location, $country) {
			try {
				$response = Http::timeout(5)->get("{$this->baseUrl}/weather", [
					'q' => "{$location},{$country}",
					'appid' => $this->apiKey,
					'units' => 'metric',
				]);

				if ($response->successful()) {
					$data = $response->json();
					return [
						'temperature_c' => $data['main']['temp'] ?? null,
						'humidity' => $data['main']['humidity'] ?? null,
						'pressure' => $data['main']['pressure'] ?? null,
						'rainfall_mm' => $data['rain']['1h'] ?? ($data['rain']['3h'] ?? 0) / 3,
						'wind_speed' => $data['wind']['speed'] ?? null,
						'description' => $data['weather'][0]['description'] ?? null,
						'timestamp' => now(),
					];
				}
			} catch (\Exception $e) {
				// Log error but don't break the application
				\Log::warning("Weather API error: " . $e->getMessage());
			}
			
			return null;
		});
	}

	/**
	 * Get weather forecast for a location (5-day forecast)
	 */
	public function getForecast(string $location, string $country = 'PH'): ?array
	{
		if (!$this->apiKey) {
			return null;
		}

		$cacheKey = "weather_forecast_{$location}_{$country}";
		
		return Cache::remember($cacheKey, 1800, function () use ($location, $country) {
			try {
				$response = Http::timeout(5)->get("{$this->baseUrl}/forecast", [
					'q' => "{$location},{$country}",
					'appid' => $this->apiKey,
					'units' => 'metric',
				]);

				if ($response->successful()) {
					$data = $response->json();
					$forecast = [];
					
					foreach ($data['list'] ?? [] as $item) {
						$forecast[] = [
							'date' => Carbon::parse($item['dt'])->format('Y-m-d'),
							'temperature_c' => $item['main']['temp'] ?? null,
							'rainfall_mm' => $item['rain']['3h'] ?? 0,
							'humidity' => $item['main']['humidity'] ?? null,
						];
					}
					
					return $forecast;
				}
			} catch (\Exception $e) {
				\Log::warning("Weather Forecast API error: " . $e->getMessage());
			}
			
			return null;
		});
	}

	/**
	 * Get average weather for a date range (for growing season)
	 */
	public function getAverageWeatherForPeriod(string $location, Carbon $startDate, Carbon $endDate, string $country = 'PH'): ?array
	{
		if (!$this->apiKey) {
			return null;
		}

		// For historical data, we'd need a paid API or use forecast if dates are in future
		// For now, we'll use current weather as approximation
		$current = $this->getCurrentWeather($location, $country);
		
		if ($current) {
			return [
				'avg_temperature_c' => $current['temperature_c'],
				'avg_rainfall_mm' => $current['rainfall_mm'] * 24, // Estimate daily from hourly
				'data_source' => 'current_weather_approximation',
			];
		}
		
		return null;
	}

	/**
	 * Check if weather API is configured
	 */
	public function isConfigured(): bool
	{
		return !empty($this->apiKey);
	}

	/**
	 * Get weather data for growing season (planting to harvest)
	 * Uses forecast if dates are in future, otherwise uses historical estimates
	 */
	public function getGrowingSeasonWeather(string $location, Carbon $plantingDate, Carbon $harvestDate, string $country = 'PH'): ?array
	{
		if (!$this->isConfigured()) {
			return null;
		}

		$now = Carbon::now();
		
		// If planting is in the future or very recent, try to use forecast
		if ($plantingDate->isFuture() || $plantingDate->diffInDays($now) < 5) {
			$forecast = $this->getForecast($location, $country);
			
			if ($forecast) {
				// Filter forecast for growing season period
				$seasonForecast = array_filter($forecast, function ($item) use ($plantingDate, $harvestDate) {
					$itemDate = Carbon::parse($item['date']);
					return $itemDate->between($plantingDate, $harvestDate);
				});
				
				if (!empty($seasonForecast)) {
					$temperatures = array_column($seasonForecast, 'temperature_c');
					$rainfalls = array_column($seasonForecast, 'rainfall_mm');
					
					return [
						'avg_temperature_c' => array_sum($temperatures) / count($temperatures),
						'total_rainfall_mm' => array_sum($rainfalls),
						'avg_rainfall_mm' => array_sum($rainfalls) / count($rainfalls),
						'data_source' => 'weather_forecast',
						'forecast_days' => count($seasonForecast),
					];
				}
			}
		}
		
		// Fallback to current weather as approximation
		return $this->getAverageWeatherForPeriod($location, $plantingDate, $harvestDate, $country);
	}
}

