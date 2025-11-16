<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Services\DecisionTree;
use App\Services\FeatureEstimator;
use App\Services\LinearRegression;
use App\Services\NeuralNetwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RecordController extends Controller
{
	public function index(): View
	{
		try {
			$records = Record::orderByDesc('created_at')->paginate(10);
		} catch (\Exception $e) {
			// Fallback to empty paginated collection if database connection fails
			$records = new \Illuminate\Pagination\LengthAwarePaginator(
				collect([]), // empty collection
				0, // total
				10, // per page
				1, // current page
				['path' => request()->url(), 'pageName' => 'page']
			);
		}
		return view('records.index', compact('records'));
	}

	public function create(): View
	{
		return view('records.create');
	}

	public function store(Request $request): RedirectResponse
	{
		$data = $this->validateRecord($request);
		Record::create($data);
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
		return redirect()->route('records.index')->with('status', 'Record updated');
	}

	public function destroy(Record $record): RedirectResponse
	{
		$record->delete();
		return redirect()->route('records.index')->with('status', 'Record deleted');
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
		return redirect()->route('records.index')->with('status', "Imported $count rows");
	}

	public function export(): Response
	{
		$records = Record::orderBy('id')->get();
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
		$records = Record::whereNotNull('yield_t_ha')->get();
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
		$estimator = new FeatureEstimator();
		$availableLocations = $estimator->getAvailableLocations();
		$weatherService = new \App\Services\WeatherService();
		$weatherApiAvailable = $weatherService->isConfigured();
		
		return view('records.forecast', [
			'availableLocations' => $availableLocations,
			'weatherApiAvailable' => $weatherApiAvailable,
		]);
	}

	public function forecast(Request $request): View
	{
		$estimator = new FeatureEstimator();
		$availableLocations = $estimator->getAvailableLocations();
		
		$payload = $request->validate([
			'location' => ['required','string','max:255'],
			'date' => ['required','date'], // Planting date
			'harvest_date' => ['nullable','date','after:date'],
			'use_weather_api' => ['nullable','boolean'],
			'model_type' => ['nullable','string','in:linear,decision_tree,neural,all'],
		]);

		$modelType = $payload['model_type'] ?? 'all';

		$records = Record::whereNotNull('yield_t_ha')->get();
		if ($records->count() < 3) {
			return view('records.forecast', [
				'error' => 'Need at least 3 records with yield to make predictions.',
				'input' => $payload,
				'availableLocations' => $availableLocations,
			]);
		}

		// Estimate features from location and date (with optional weather API)
		$useWeatherApi = $payload['use_weather_api'] ?? true;
		$estimatedFeatures = $estimator->estimateFromLocationAndDate(
			$payload['location'],
			$payload['date'],
			$payload['harvest_date'] ?? null,
			$useWeatherApi
		);
		
		// Determine season for display
		try {
			$carbon = \Carbon\Carbon::parse($payload['date']);
			$month = $carbon->month;
			$estimatedFeatures['season'] = ($month >= 6 && $month <= 10) ? 'Wet' : 'Dry';
		} catch (\Exception $e) {
			$estimatedFeatures['season'] = 'Unknown';
		}
		
		// Check if weather API is available
		$weatherService = new \App\Services\WeatherService();
		$weatherApiAvailable = $weatherService->isConfigured();

		// Prepare training data
		$features = [];
		$targets = [];
		foreach ($records as $r) {
			$features[] = [
				(float)$r->rainfall_mm,
				(float)$r->temperature_c,
				(float)$r->soil_ph,
				(float)$r->fertilizer_kg,
				(float)$r->area_ha,
			];
			$targets[] = (float)$r->yield_t_ha;
		}

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
			$dtModel = new DecisionTree(5, 5);
			$dtModel->fit($features, $targets);
			$predictions['decision_tree'] = $dtModel->predict($inputFeature);
		}

		if ($modelType === 'neural' || $modelType === 'all') {
			$nnModel = new NeuralNetwork(5, [10, 8], 0.01, 500);
			$nnModel->fit($features, $targets);
			$predictions['neural'] = $nnModel->predict($inputFeature);
		}

		return view('records.forecast', [
			'predictions' => $predictions,
			'modelType' => $modelType,
			'input' => $payload,
			'estimatedFeatures' => $estimatedFeatures,
			'availableLocations' => $availableLocations,
			'weatherApiAvailable' => $weatherApiAvailable,
		]);
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
}


