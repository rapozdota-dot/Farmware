<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Services\LinearRegression;
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
		$model = new LinearRegression();
		$model->fit($featuresTrain, $targetsTrain, 1e-4);

		$errors = [];
		foreach ($test as $row) {
			$pred = $model->predict([ $row['rainfall_mm'],$row['temperature_c'],$row['soil_ph'],$row['fertilizer_kg'],$row['area_ha'] ]);
			$errors[] = $pred - $row['yield_t_ha'];
		}
		$mae = count($errors) ? array_sum(array_map('abs', $errors)) / count($errors) : null;
		$rmse = count($errors) ? sqrt(array_sum(array_map(fn($e) => $e*$e, $errors)) / count($errors)) : null;

		return view('records.evaluate', [
			'mae' => $mae,
			'rmse' => $rmse,
			'testSize' => count($errors),
		]);
	}

	private static function toNum($v): ?float
	{
		if ($v === null || $v === '') return null;
		return is_numeric($v) ? (float)$v : (float)str_replace([',',' '], ['', ''], $v);
	}

	public function forecastForm(): View
	{
		return view('records.forecast');
	}

	public function forecast(Request $request): View
	{
		$payload = $request->validate([
			'rainfall_mm' => ['nullable','numeric'],
			'temperature_c' => ['nullable','numeric'],
			'soil_ph' => ['nullable','numeric'],
			'fertilizer_kg' => ['nullable','numeric'],
			'area_ha' => ['nullable','numeric'],
		]);

		$records = Record::whereNotNull('yield_t_ha')->get();
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

		$model = new LinearRegression();
		$model->fit($features, $targets, 1e-4);

		$inputFeature = [
			(float)($payload['rainfall_mm'] ?? 0),
			(float)($payload['temperature_c'] ?? 0),
			(float)($payload['soil_ph'] ?? 0),
			(float)($payload['fertilizer_kg'] ?? 0),
			(float)($payload['area_ha'] ?? 0),
		];
		$prediction = $model->predict($inputFeature);

		return view('records.forecast', [
			'prediction' => $prediction,
			'input' => $payload,
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


