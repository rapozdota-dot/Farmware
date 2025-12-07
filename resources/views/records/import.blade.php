@extends('layouts.app')

@section('content')
<div class="fade-enter fade-enter-active">
	<div style="margin-bottom: 1.5rem;">
		<a href="{{ route('records.data') }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
			← Back to Data Management
		</a>
	</div>
	
	<h2>Import CSV</h2>
	<p style="color: #64748b; margin-bottom: 1.5rem;">Upload a CSV file to import multiple records at once.</p>
	
	<div class="card" style="max-width: 600px; margin: 0 auto;">
		<form action="{{ route('records.import.run') }}" method="POST" enctype="multipart/form-data">
			@csrf
			<label>
				<strong>CSV File</strong>
				<input type="file" name="csv" accept=".csv,text/csv" required style="padding: 0.75rem; margin-top: 0.5rem;">
				<small style="color: #64748b; display: block; margin-top: 0.5rem;">Accepted formats: .csv, .txt</small>
			</label>
			
			<div class="card" style="background: #f8fafc; margin: 1rem 0;">
				<p style="margin: 0 0 0.5rem 0;"><strong>Required CSV Format:</strong></p>
				<code style="display: block; padding: 0.75rem; background: white; border-radius: 4px; font-size: 0.9rem;">location,season,area_ha,rainfall_mm,temperature_c,soil_ph,fertilizer_kg,yield_t_ha</code>
			</div>
			
			<button type="submit" class="btn-brand" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600;">
				📥 Upload and Import
			</button>
		</form>
	</div>
</div>
@endsection


