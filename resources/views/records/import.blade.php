@extends('layouts.app')

@section('content')
<div class="fade-enter fade-enter-active animate-slide-up">
	<div style="margin-bottom: 2rem;">
		<a href="{{ route('records.data') }}" 
		   style="color: var(--muted-foreground); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--muted); border-radius: var(--radius); transition: all 0.3s ease; font-weight: 600;"
		   onmouseover="this.style.background='var(--border)'; this.style.color='var(--primary)'; this.style.transform='translateX(-4px)'"
		   onmouseout="this.style.background='var(--muted)'; this.style.color='var(--muted-foreground)'; this.style.transform='translateX(0)'">
			← Back to Data Management
		</a>
	</div>
	
	<div style="margin-bottom: 2rem;">
		<h1 class="text-gradient-hero" style="font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 800;">📥 Import CSV</h1>
		<p style="color: var(--muted-foreground); font-size: 1.1rem;">Upload a CSV file to import multiple records at once</p>
	</div>
	
	<div class="card animate-fade-in stagger-1" style="max-width: 700px; margin: 0 auto; background: var(--gradient-card);">
		<form action="{{ route('records.import.run') }}" method="POST" enctype="multipart/form-data" x-data="{ fileName: '' }">
			@csrf
			<label style="margin-bottom: 1.5rem; display: block;">
				<span style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; color: var(--foreground); font-weight: 700; font-size: 1.1rem;">
					<span style="font-size: 1.5rem;">📄</span>
					<span>CSV File</span>
				</span>
				<div style="position: relative;">
					<input type="file" name="csv" accept=".csv,text/csv" required 
						   x-on:change="fileName = $event.target.files[0]?.name || ''"
						   style="padding: 1.25rem; border-radius: var(--radius); border: 2px dashed var(--border); background: var(--card); width: 100%; cursor: pointer; transition: all 0.3s ease;"
						   onfocus="this.style.borderColor='var(--primary)'; this.style.borderStyle='solid'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'" 
						   onblur="this.style.borderColor='var(--border)'; this.style.borderStyle='dashed'; this.style.boxShadow='none'">
					<div x-show="fileName" style="margin-top: 0.75rem; padding: 0.75rem; background: var(--primary-bg); border-radius: var(--radius); color: var(--primary-dark); font-weight: 600;">
						📎 Selected: <span x-text="fileName"></span>
					</div>
				</div>
				<small style="color: var(--muted-foreground); display: block; margin-top: 0.75rem; font-size: 0.9rem;">Accepted formats: .csv, .txt</small>
			</label>
			
			<div class="card" style="background: var(--muted); margin: 1.5rem 0; border: 1px solid var(--border);">
				<p style="margin: 0 0 1rem 0; font-weight: 700; color: var(--foreground); font-size: 1.05rem;">📋 Required CSV Format:</p>
				<code style="display: block; padding: 1.25rem; background: var(--card); border-radius: var(--radius); font-family: 'Courier New', monospace; font-size: 0.95rem; color: var(--primary-dark); border: 2px solid var(--border); font-weight: 600; word-break: break-all;">
					location, season, area_ha, rainfall_mm, temperature_c, soil_ph, fertilizer_kg, yield_t_ha
				</code>
			</div>
			
			<button type="submit" class="btn-brand" style="width: 100%; padding: 1.25rem; font-size: 1.15rem; font-weight: 700; border-radius: var(--radius); margin-top: 1rem;">
				📥 Upload and Import
			</button>
		</form>
	</div>
</div>
@endsection


