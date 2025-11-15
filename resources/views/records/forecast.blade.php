@extends('layouts.app')

@section('content')
<div x-data="{ loading:false }" class="fade-enter fade-enter-active">
<h2>Forecast Yield</h2>

<form x-on:submit="loading=true" action="{{ route('records.forecast.run') }}" method="POST" class="card">
    @csrf
    <fieldset role="group">
		<label>
			Rainfall (mm)
			<input type="number" step="0.01" name="rainfall_mm" value="{{ old('rainfall_mm', $input['rainfall_mm'] ?? '') }}">
		</label>
		<label>
			Temperature (°C)
			<input type="number" step="0.01" name="temperature_c" value="{{ old('temperature_c', $input['temperature_c'] ?? '') }}">
		</label>
    </fieldset>
    <fieldset role="group">
		<label>
			Soil pH
			<input type="number" step="0.01" name="soil_ph" value="{{ old('soil_ph', $input['soil_ph'] ?? '') }}">
		</label>
		<label>
			Fertilizer (kg)
			<input type="number" step="0.01" name="fertilizer_kg" value="{{ old('fertilizer_kg', $input['fertilizer_kg'] ?? '') }}">
		</label>
    </fieldset>
    <label>
        Area (ha)
        <input type="number" step="0.01" name="area_ha" value="{{ old('area_ha', $input['area_ha'] ?? '') }}">
    </label>
    <button :aria-busy="loading" :disabled="loading" type="submit" class="btn-brand"> <span x-show="!loading">Run Forecast</span><span x-show="loading">Running...</span></button>
</form>

@isset($prediction)
<div class="card" x-data="{ shown:false }" x-init="shown=true" x-show="shown" x-transition>
    <h3 style="margin-top:0">Result</h3>
    <p><strong>Predicted Yield (t/ha):</strong> {{ number_format($prediction, 3) }}</p>
    <details>
        <summary>Inputs</summary>
        <ul>
            <li>Rainfall: {{ $input['rainfall_mm'] ?? '-' }} mm</li>
            <li>Temperature: {{ $input['temperature_c'] ?? '-' }} °C</li>
            <li>Soil pH: {{ $input['soil_ph'] ?? '-' }}</li>
            <li>Fertilizer: {{ $input['fertilizer_kg'] ?? '-' }} kg</li>
            <li>Area: {{ $input['area_ha'] ?? '-' }} ha</li>
        </ul>
    </details>
</div>
@endisset

@if ($errors->any())
<article>
	<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>
</article>
@endif
@endsection


