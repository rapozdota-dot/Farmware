<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Services\DecisionTree;
use App\Services\FeatureEstimator;
use App\Services\LinearRegression;
use App\Services\NeuralNetwork;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecordController extends Controller
{
	private const FOCUS_LOCATION = 'Palo, Leyte';

	public function index(): View
	{
		try {
			// Optimized: Select only needed columns and use pagination
			$records = Record::select(['id', 'location', 'season', 'area_ha', 'rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'yield_t_ha', 'created_at'])
				->orderByDesc('created_at')
				->paginate(10);
			
			// Optimized: Cache all records for graph (cache for 5 minutes)
			$allRecords = Cache::remember('records_all_yield', 300, function () {
				return Record::select(['rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'area_ha', 'yield_t_ha'])
					->whereNotNull('yield_t_ha')
					->get();
			});
		} catch (\Exception $e) {
			// Fallback to empty paginated collection if database connection fails
			$records = new \Illuminate\Pagination\LengthAwarePaginator(
				collect([]), // empty collection
				0, // total
				10, // per page
				1, // current page
				['path' => request()->url(), 'pageName' => 'page']
			);
			$allRecords = collect([]);
		}
		return view('records.index', compact('records', 'allRecords'));
	}

	public function create(): View
	{
		return view('records.create');
	}

	public function store(Request $request): RedirectResponse
	{
		$data = $this->validateRecord($request);
		Record::create($data);
		// Clear all caches when new record is added
		Cache::forget('records_all_yield');
		Cache::forget('historical_records_viz');
		$this->clearFeatureEstimatorCache();
		return redirect()->route('records.index')->with('status', 'Record created');
	}

	public function edit(Record $record): View
	{
		return view('records.edit', compact('record'));
	}

	public function update(Request $request, Record $record): RedirectResponse
	{
		$data = $this->validateRecord($request);
		$record->update($data);
		// Clear all caches when record is updated
		Cache::forget('records_all_yield');
		Cache::forget('historical_records_viz');
		$this->clearFeatureEstimatorCache();
		return redirect()->route('records.index')->with('status', 'Record updated');
	}

	public function destroy(Record $record): RedirectResponse
	{
		$record->delete();
		// Clear all caches when record is deleted
		Cache::forget('records_all_yield');
		Cache::forget('historical_records_viz');
		$this->clearFeatureEstimatorCache();
		return redirect()->route('records.index')->with('status', 'Record deleted');
	}

	public function dataManagement(): View
	{
		return view('records.data');
	}

	public function importForm(): View
	{
		return view('records.import');
	}

	public function import(Request $request): RedirectResponse
	{
		$request->validate([
			'csv' => ['required','file','mimes:csv,txt'],
		]);
		$path = $request->file('csv')->getRealPath();
		$handle = fopen($path, 'r');
		if ($handle === false) {
			return back()->with('status', 'Failed to read CSV');
		}
		$header = null;
		$count = 0;
		while (($row = fgetcsv($handle)) !== false) {
			// Skip empty lines
			if ($row === null || (count($row) === 1 && trim((string)$row[0]) === '')) {
				continue;
			}
			if ($header === null) {
				// Normalize header: lowercase, trim, remove BOM on first cell
				if (isset($row[0])) {
					$row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$row[0]);
				}
				$header = array_map(function ($h) { return strtolower(trim((string)$h)); }, $row);
				// Ensure we only keep known columns; others are ignored
				$header = array_map(function ($h) {
					return $h;
				}, $header);
				continue;
			}
			// Tolerate row length != header length by mapping min length
			$values = $row;
			$minLen = min(count($header), count($values));
			if ($minLen === 0) continue;
			$keys = array_slice($header, 0, $minLen);
			$vals = array_slice($values, 0, $minLen);
			$data = @array_combine($keys, $vals);
			if ($data === false) continue;
			Record::create([
				'location' => $data['location'] ?? null,
				'season' => $data['season'] ?? null,
				'area_ha' => self::toNum($data['area_ha'] ?? null),
				'rainfall_mm' => self::toNum($data['rainfall_mm'] ?? null),
				'temperature_c' => self::toNum($data['temperature_c'] ?? null),
				'soil_ph' => self::toNum($data['soil_ph'] ?? null),
				'fertilizer_kg' => self::toNum($data['fertilizer_kg'] ?? null),
				'yield_t_ha' => self::toNum($data['yield_t_ha'] ?? null),
			]);
			$count++;
		}
		fclose($handle);
		// Clear all caches when records are imported
		Cache::forget('records_all_yield');
		Cache::forget('historical_records_viz');
		$this->clearFeatureEstimatorCache();
		return redirect()->route('records.data')->with('status', "Imported $count rows");
	}

	public function export(): StreamedResponse
	{
		// Optimized: Select only needed columns for export
		$records = Record::select(['location', 'season', 'area_ha', 'rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'yield_t_ha'])
			->orderBy('id')
			->get();
		$headers = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename="records.csv"',
		];
		$callback = function () use ($records) {
			$out = fopen('php://output', 'w');
			fputcsv($out, ['location','season','area_ha','rainfall_mm','temperature_c','soil_ph','fertilizer_kg','yield_t_ha']);
			foreach ($records as $r) {
				fputcsv($out, [
					$r->location,
					$r->season,
					$r->area_ha,
					$r->rainfall_mm,
					$r->temperature_c,
					$r->soil_ph,
					$r->fertilizer_kg,
					$r->yield_t_ha,
				]);
			}
			fclose($out);
		};
		return response()->stream($callback, 200, $headers);
	}

	public function evaluate(): View
	{
		// Optimized: Select only needed columns
		$records = Record::select(['rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'area_ha', 'yield_t_ha'])
			->whereNotNull('yield_t_ha')
			->get();
		if ($records->count() < 6) {
			return view('records.evaluate', [
				'message' => 'Need at least 6 records with yield to evaluate.',
			]);
		}
		$all = $records->map(function ($r) {
			return [
				'rainfall_mm' => (float)$r->rainfall_mm,
				'temperature_c' => (float)$r->temperature_c,
				'soil_ph' => (float)$r->soil_ph,
				'fertilizer_kg' => (float)$r->fertilizer_kg,
				'area_ha' => (float)$r->area_ha,
				'yield_t_ha' => (float)$r->yield_t_ha,
			];
		})->shuffle()->values()->all();
		$split = (int)floor(count($all) * 0.8);
		$train = array_slice($all, 0, $split);
		$test = array_slice($all, $split);

		$featuresTrain = array_map(fn($x) => [ $x['rainfall_mm'],$x['temperature_c'],$x['soil_ph'],$x['fertilizer_kg'],$x['area_ha'] ], $train);
		$targetsTrain = array_map(fn($x) => $x['yield_t_ha'], $train);

		// Evaluate Linear Regression (Simplest)
		$lrModel = new LinearRegression();
		$lrModel->fit($featuresTrain, $targetsTrain, 1e-4);
		$lrErrors = [];
		foreach ($test as $row) {
			$pred = $lrModel->predict([ $row['rainfall_mm'],$row['temperature_c'],$row['soil_ph'],$row['fertilizer_kg'],$row['area_ha'] ]);
			$lrErrors[] = $pred - $row['yield_t_ha'];
		}
		$lrMae = count($lrErrors) ? array_sum(array_map('abs', $lrErrors)) / count($lrErrors) : null;
		$lrRmse = count($lrErrors) ? sqrt(array_sum(array_map(fn($e) => $e*$e, $lrErrors)) / count($lrErrors)) : null;

		// Evaluate Decision Tree (Medium Complexity)
		$dtModel = new DecisionTree(5, 5);
		$dtModel->fit($featuresTrain, $targetsTrain);
		$dtErrors = [];
		foreach ($test as $row) {
			$pred = $dtModel->predict([ $row['rainfall_mm'],$row['temperature_c'],$row['soil_ph'],$row['fertilizer_kg'],$row['area_ha'] ]);
			$dtErrors[] = $pred - $row['yield_t_ha'];
		}
		$dtMae = count($dtErrors) ? array_sum(array_map('abs', $dtErrors)) / count($dtErrors) : null;
		$dtRmse = count($dtErrors) ? sqrt(array_sum(array_map(fn($e) => $e*$e, $dtErrors)) / count($dtErrors)) : null;

		// Evaluate Neural Network (Most Advanced)
		$nnModel = new NeuralNetwork(5, [10, 8], 0.01, 500);
		$nnModel->fit($featuresTrain, $targetsTrain);
		$nnErrors = [];
		foreach ($test as $row) {
			$pred = $nnModel->predict([ $row['rainfall_mm'],$row['temperature_c'],$row['soil_ph'],$row['fertilizer_kg'],$row['area_ha'] ]);
			$nnErrors[] = $pred - $row['yield_t_ha'];
		}
		$nnMae = count($nnErrors) ? array_sum(array_map('abs', $nnErrors)) / count($nnErrors) : null;
		$nnRmse = count($nnErrors) ? sqrt(array_sum(array_map(fn($e) => $e*$e, $nnErrors)) / count($nnErrors)) : null;

		return view('records.evaluate', [
			'linearRegression' => [
				'mae' => $lrMae,
				'rmse' => $lrRmse,
			],
			'decisionTree' => [
				'mae' => $dtMae,
				'rmse' => $dtRmse,
			],
			'neuralNetwork' => [
				'mae' => $nnMae,
				'rmse' => $nnRmse,
			],
			'testSize' => count($test),
		]);
	}

	private static function toNum($v): ?float
	{
		if ($v === null || $v === '') return null;
		return is_numeric($v) ? (float)$v : (float)str_replace([',',' '], ['', ''], $v);
	}

	public function forecastForm(): View
	{
		$weatherService = new WeatherService();
		$weatherApiAvailable = $weatherService->isConfigured();
		
		return view('records.forecast', [
			'focusLocation' => self::FOCUS_LOCATION,
			'weatherApiAvailable' => $weatherApiAvailable,
		]);
	}

	public function forecast(Request $request): View
	{
		$estimator = new FeatureEstimator();
		$weatherService = new WeatherService();
		$weatherApiAvailable = $weatherService->isConfigured();
		
		$validator = Validator::make($request->all(), [
			'planting_date' => ['required','date'],
			'harvest_date' => ['nullable','date'],
			'model_type' => ['nullable','string','in:linear,decision_tree,neural,all'],
		]);

		$validator->after(function ($validator) use ($request) {
			$data = $validator->getData();
			
			// Validate planting date
			try {
				$planting = Carbon::parse($data['planting_date']);
			} catch (\Exception $e) {
				$validator->errors()->add('planting_date', 'Planting date format is invalid. Please use a valid date.');
				return;
			}

			// Check if planting date is in the future (too far)
			$now = Carbon::now();
			$earliest = Carbon::create(2015, 1, 1);
			$latest = $now->copy()->addYear();

			if ($planting->lt($earliest)) {
				$validator->errors()->add('planting_date', 'Planting date cannot be before January 1, 2015. Historical data is only available from 2015 onwards.');
				return;
			}

			if ($planting->gt($latest)) {
				$validator->errors()->add('planting_date', 'Planting date cannot be more than one year in the future. Please select a date up to ' . $latest->format('F d, Y') . '.');
				return;
			}

			// Validate harvest date if provided
			if (!empty($data['harvest_date'])) {
				try {
					$harvest = Carbon::parse($data['harvest_date']);
				} catch (\Exception $e) {
					$validator->errors()->add('harvest_date', 'Harvest date format is invalid. Please use a valid date.');
					return;
				}

				// Check if harvest date is before or equal to planting date
				if ($harvest->lte($planting)) {
					$validator->errors()->add('harvest_date', 'Harvest date must be after the planting date. Please select a date after ' . $planting->format('F d, Y') . '.');
					return;
				}

				// Check if harvest date is too soon (less than minimum growing period)
				$growingDays = $planting->diffInDays($harvest);
				if ($growingDays < 60) {
					$minHarvest = $planting->copy()->addDays(60);
					$validator->errors()->add('harvest_date', 'Harvest date is too early. Rice typically requires at least 60 days to grow. The earliest harvest date should be around ' . $minHarvest->format('F d, Y') . ' (60 days after planting).');
					return;
				}

				// Check if harvest date is too far (more than maximum growing period)
				if ($growingDays > 210) {
					$maxHarvest = $planting->copy()->addDays(210);
					$validator->errors()->add('harvest_date', 'Harvest date is unrealistic. Rice in Palo, Leyte typically matures within 210 days. The latest realistic harvest date would be around ' . $maxHarvest->format('F d, Y') . ' (210 days after planting).');
					return;
				}

				// Check if harvest date is in the past (for historical validation)
				if ($harvest->gt($now->copy()->addMonths(6))) {
					$validator->errors()->add('harvest_date', 'Harvest date is too far in the future. Please select a date within 6 months from now.');
					return;
				}
			}
		});

		$payload = $validator->validate();

		$modelType = $payload['model_type'] ?? 'all';

		// Optimized: Select only needed columns and cache training data
		// Cache key includes season for better cache hits
		$season = (Carbon::parse($payload['planting_date'])->month >= 6 && Carbon::parse($payload['planting_date'])->month <= 10) ? 'Wet' : 'Dry';
		$cacheKey = 'forecast_training_data_' . $season;
		$records = Cache::remember($cacheKey, 1800, function () {
			return Record::select(['rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'area_ha', 'yield_t_ha'])
				->whereNotNull('yield_t_ha')
				->get();
		});
		
		if ($records->count() < 3) {
			return view('records.forecast', [
				'error' => 'Need at least 3 records with yield to make predictions.',
				'input' => $payload,
				'focusLocation' => self::FOCUS_LOCATION,
				'weatherApiAvailable' => $weatherApiAvailable,
			]);
		}

		// Automatically use weather API when available for better accuracy
		$useWeatherApi = $weatherApiAvailable;

		$estimatedFeatures = $estimator->estimateFromLocationAndDate(
			$payload['planting_date'],
			$payload['harvest_date'] ?? null,
			$useWeatherApi
		);
		
		// Determine season for display
		try {
			$carbon = Carbon::parse($payload['planting_date']);
			$month = $carbon->month;
			$estimatedFeatures['season'] = ($month >= 6 && $month <= 10) ? 'Wet' : 'Dry';
		} catch (\Exception $e) {
			$estimatedFeatures['season'] = 'Unknown';
		}
		
		// Optimized: Prepare training data efficiently using array_map for better performance
		$features = $records->map(function ($r) {
			return [
				(float)$r->rainfall_mm,
				(float)$r->temperature_c,
				(float)$r->soil_ph,
				(float)$r->fertilizer_kg,
				(float)$r->area_ha,
			];
		})->all();
		
		$targets = $records->pluck('yield_t_ha')->map(fn($v) => (float)$v)->all();

		// Use estimated features for prediction
		$inputFeature = [
			(float)$estimatedFeatures['rainfall_mm'],
			(float)$estimatedFeatures['temperature_c'],
			(float)$estimatedFeatures['soil_ph'],
			(float)$estimatedFeatures['fertilizer_kg'],
			(float)$estimatedFeatures['area_ha'],
		];

		$predictions = [];

		if ($modelType === 'linear' || $modelType === 'all') {
			$lrModel = new LinearRegression();
			$lrModel->fit($features, $targets, 1e-4);
			$predictions['linear'] = $lrModel->predict($inputFeature);
		}

		if ($modelType === 'decision_tree' || $modelType === 'all') {
			// Improved decision tree: deeper tree (6 levels) and more samples for better accuracy
			$dtModel = new DecisionTree(6, 4);
			$dtModel->fit($features, $targets);
			$predictions['decision_tree'] = $dtModel->predict($inputFeature);
		}

		if ($modelType === 'neural' || $modelType === 'all') {
			// Improved neural network: more epochs and better architecture for better accuracy
			// Increased epochs from 500 to 1200 for better convergence
			// Using [12, 10] hidden layers instead of [10, 8] for more capacity
			$nnModel = new NeuralNetwork(5, [12, 10], 0.01, 1200);
			$nnModel->fit($features, $targets);
			$predictions['neural'] = $nnModel->predict($inputFeature);
		}

		$yieldJustification = $this->buildYieldJustification($estimatedFeatures, $predictions);

		// Optimized: Cache historical records for visualization
		$historicalRecords = Cache::remember('historical_records_viz', 600, function () {
			return Record::select(['rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'area_ha', 'yield_t_ha'])
				->whereNotNull('yield_t_ha')
				->orderBy('yield_t_ha')
				->get()
				->map(function ($r) {
					return [
						'rainfall_mm' => (float)$r->rainfall_mm,
						'temperature_c' => (float)$r->temperature_c,
						'fertilizer_kg' => (float)$r->fertilizer_kg,
						'area_ha' => (float)$r->area_ha,
						'soil_ph' => (float)$r->soil_ph,
						'yield_t_ha' => (float)$r->yield_t_ha,
					];
				});
		});

		return view('records.forecast', [
			'predictions' => $predictions,
			'modelType' => $modelType,
			'input' => $payload,
			'estimatedFeatures' => $estimatedFeatures,
			'yieldJustification' => $yieldJustification,
			'focusLocation' => self::FOCUS_LOCATION,
			'weatherApiAvailable' => $weatherApiAvailable,
			'historicalRecords' => $historicalRecords,
		]);
	}

	/**
	 * Clear FeatureEstimator cache when records change
	 */
	private function clearFeatureEstimatorCache(): void
	{
		// Clear location averages cache (for both Wet and Dry seasons)
		foreach (['Wet', 'Dry'] as $season) {
			Cache::forget("location_avg_Palo, Leyte_{$season}_" . md5("Palo, Leyte" . $season));
			Cache::forget("location_avg_Palo_{$season}_" . md5("Palo" . $season));
		}
		// Clear region averages cache
		foreach (['Wet', 'Dry'] as $season) {
			Cache::forget("region_avg_Eastern Visayas_{$season}_" . md5("Eastern Visayas" . $season));
		}
		// Clear season averages cache
		Cache::forget('season_avg_Wet');
		Cache::forget('season_avg_Dry');
		// Clear forecast training data cache
		Cache::flush(); // This clears all forecast-related caches
	}

	private function validateRecord(Request $request): array
	{
		return $request->validate([
			'location' => ['nullable','string','max:255'],
			'season' => ['nullable','string','max:255'],
			'area_ha' => ['nullable','numeric'],
			'rainfall_mm' => ['nullable','numeric'],
			'temperature_c' => ['nullable','numeric'],
			'soil_ph' => ['nullable','numeric'],
			'fertilizer_kg' => ['nullable','numeric'],
			'yield_t_ha' => ['nullable','numeric'],
		]);
	}

	private function buildYieldJustification(array $features, array $predictions): string
	{
		$season = $features['season'] ?? 'local';
		$rainfall = isset($features['rainfall_mm']) ? number_format((float)$features['rainfall_mm'], 1) : '0.0';
		$temperature = isset($features['temperature_c']) ? number_format((float)$features['temperature_c'], 1) : '0.0';
		$growingDays = $features['growingDays'] ?? 120;

		try {
			$plantingRange = Carbon::parse($features['plantingDate'])->format('M d, Y');
			$harvestRange = Carbon::parse($features['harvestDate'])->format('M d, Y');
			$dateRange = "{$plantingRange} to {$harvestRange}";
		} catch (\Exception $e) {
			$dateRange = 'the current growing window';
		}

		$referencePrediction = null;
		if (isset($predictions['neural'])) {
			$referencePrediction = $predictions['neural'];
		} elseif (!empty($predictions)) {
			$referencePrediction = reset($predictions);
		}

		$predictionFragment = $referencePrediction !== null
			? 'The AI expects roughly '.number_format((float)$referencePrediction, 2).' t/ha under these conditions.'
			: 'These conditions align with the historical samples used by the AI.';

		return "Using historical ".strtolower($season)." season patterns for Palo, Leyte ({$dateRange}), the model factors in about {$rainfall} mm of rainfall, {$temperature} °C average temperature, and a {$growingDays}-day growth cycle. {$predictionFragment}";
	}
}