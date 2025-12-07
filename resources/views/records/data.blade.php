@extends('layouts.app')

@section('content')
<div class="fade-enter fade-enter-active">
	<h2>Data Management</h2>
	<p style="color: #64748b; margin-bottom: 2rem;">Import or export your rice yield records in CSV format.</p>

	<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
		<div class="card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 4px solid #0ea5e9;">
			<h3 style="margin-top: 0; color: #0c4a6e;">📥 Import Records</h3>
			<p style="color: #64748b; margin-bottom: 1rem;">Upload a CSV file to add multiple records at once.</p>
			<a href="{{ route('records.import.form') }}" role="button" class="btn-brand" style="width: 100%; text-align: center;">Import CSV</a>
		</div>

		<div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #10b981;">
			<h3 style="margin-top: 0; color: #065f46;">📤 Export Records</h3>
			<p style="color: #64748b; margin-bottom: 1rem;">Download all your records as a CSV file.</p>
			<a href="{{ route('records.export') }}" role="button" class="secondary" style="width: 100%; text-align: center; background: #10b981; border-color: #10b981; color: white;">Export CSV</a>
		</div>
	</div>

	<div class="card" style="background: #f8fafc;">
		<h4 style="margin-top: 0;">CSV Format Requirements</h4>
		<p style="margin-bottom: 0.5rem;">Your CSV file should include the following columns:</p>
		<code style="display: block; padding: 0.75rem; background: white; border-radius: 4px; margin-bottom: 0.5rem;">location, season, area_ha, rainfall_mm, temperature_c, soil_ph, fertilizer_kg, yield_t_ha</code>
		<small style="color: #64748b;">All columns are optional except that at least one field must have a value.</small>
	</div>
</div>
@endsection

