@extends('layouts.app')

@section('content')
<h2>Import CSV</h2>
<p>Upload a CSV with headers: <code>location,season,area_ha,rainfall_mm,temperature_c,soil_ph,fertilizer_kg,yield_t_ha</code></p>
<form action="{{ route('records.import.run') }}" method="POST" enctype="multipart/form-data" class="card">
	@csrf
	<input type="file" name="csv" accept=".csv,text/csv">
	<button type="submit" class="btn-brand">Upload and Import</button>
</form>
@endsection


