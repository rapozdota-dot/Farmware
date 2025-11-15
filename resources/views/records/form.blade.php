<label>
	Location
	<input type="text" name="location" value="{{ old('location', $record->location ?? '') }}">
</label>
<label>
	Season
	<input type="text" name="season" value="{{ old('season', $record->season ?? '') }}">
</label>
<label>
	Area (ha)
	<input type="number" step="0.01" name="area_ha" value="{{ old('area_ha', $record->area_ha ?? '') }}">
</label>
<label>
	Rainfall (mm)
	<input type="number" step="0.01" name="rainfall_mm" value="{{ old('rainfall_mm', $record->rainfall_mm ?? '') }}">
</label>
<label>
	Temperature (°C)
	<input type="number" step="0.01" name="temperature_c" value="{{ old('temperature_c', $record->temperature_c ?? '') }}">
</label>
<label>
	Soil pH
	<input type="number" step="0.01" name="soil_ph" value="{{ old('soil_ph', $record->soil_ph ?? '') }}">
</label>
<label>
	Fertilizer (kg)
	<input type="number" step="0.01" name="fertilizer_kg" value="{{ old('fertilizer_kg', $record->fertilizer_kg ?? '') }}">
</label>
<label>
	Yield (t/ha)
	<input type="number" step="0.001" name="yield_t_ha" value="{{ old('yield_t_ha', $record->yield_t_ha ?? '') }}">
</label>

@if ($errors->any())
<article>
	<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>
</article>
@endif


