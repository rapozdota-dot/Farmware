@extends('layouts.app')

@section('content')
<div class="fade-enter fade-enter-active animate-slide-up">
	<div style="margin-bottom: 2rem;">
		<h1 class="text-gradient-hero" style="font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 800;">📊 Data Management</h1>
		<p style="color: var(--muted-foreground); font-size: 1.1rem;">Import or export your rice yield records in CSV format</p>
	</div>

	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
		<div class="card animate-fade-in stagger-1" 
			 style="background: var(--gradient-card); border: 2px solid var(--secondary); cursor: pointer; transition: all 0.3s ease;"
			 onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='var(--shadow-card)'; this.style.borderColor='var(--secondary-dark)'"
			 onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='var(--shadow-soft)'; this.style.borderColor='var(--secondary)'"
			 onclick="window.location.href='{{ route('records.import.form') }}'">
			<div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
				<div style="width: 64px; height: 64px; background: var(--gradient-neural); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: var(--shadow-soft);">
					📥
				</div>
				<div>
					<h3 style="margin: 0; color: var(--secondary-dark); font-size: 1.5rem; font-weight: 700;">Import Records</h3>
					<p style="margin: 0.25rem 0 0 0; color: var(--muted-foreground); font-size: 0.9rem;">Upload CSV file</p>
				</div>
			</div>
			<p style="color: var(--muted-foreground); margin-bottom: 1.5rem; line-height: 1.6;">Upload a CSV file to add multiple records at once. Quick and efficient bulk import.</p>
			<a href="{{ route('records.import.form') }}" role="button" class="btn-brand" style="width: 100%; text-align: center; padding: 1rem; border-radius: var(--radius); font-weight: 700; display: block; text-decoration: none;">
				📥 Import CSV
			</a>
		</div>

		<div class="card animate-fade-in stagger-2" 
			 style="background: var(--gradient-card); border: 2px solid var(--primary); cursor: pointer; transition: all 0.3s ease;"
			 onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='var(--shadow-card)'; this.style.borderColor='var(--primary-dark)'"
			 onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='var(--shadow-soft)'; this.style.borderColor='var(--primary)'"
			 onclick="window.location.href='{{ route('records.export') }}'">
			<div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
				<div style="width: 64px; height: 64px; background: var(--gradient-tree); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: var(--shadow-soft);">
					📤
				</div>
				<div>
					<h3 style="margin: 0; color: var(--primary-dark); font-size: 1.5rem; font-weight: 700;">Export Records</h3>
					<p style="margin: 0.25rem 0 0 0; color: var(--muted-foreground); font-size: 0.9rem;">Download CSV file</p>
				</div>
			</div>
			<p style="color: var(--muted-foreground); margin-bottom: 1.5rem; line-height: 1.6;">Download all your records as a CSV file for backup or analysis in external tools.</p>
			<a href="{{ route('records.export') }}" role="button" 
			   style="width: 100%; text-align: center; padding: 1rem; border-radius: var(--radius); font-weight: 700; display: block; text-decoration: none; background: var(--gradient-tree); color: white; border: none; transition: all 0.3s ease;"
			   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow-card)'"
			   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-soft)'">
				📤 Export CSV
			</a>
		</div>
	</div>

	<div class="card animate-fade-in stagger-3" style="background: var(--gradient-card); border: 1px solid var(--border);">
		<h4 style="margin-top: 0; color: var(--foreground); font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
			<span>📋</span>
			<span>CSV Format Requirements</span>
		</h4>
		<p style="color: var(--muted-foreground); margin-bottom: 1rem; line-height: 1.6;">Your CSV file should include the following columns:</p>
		<code style="display: block; padding: 1.25rem; background: var(--card); border-radius: var(--radius); margin-bottom: 1rem; font-family: 'Courier New', monospace; font-size: 0.95rem; color: var(--primary-dark); border: 2px solid var(--border); font-weight: 600;">
			location, season, area_ha, rainfall_mm, temperature_c, soil_ph, fertilizer_kg, yield_t_ha
		</code>
		<div style="padding: 1rem; background: var(--primary-bg); border-radius: var(--radius); border-left: 4px solid var(--primary);">
			<small style="color: var(--primary-dark); font-weight: 600; line-height: 1.6;">
				💡 <strong>Note:</strong> All columns are optional except that at least one field must have a value.
			</small>
		</div>
	</div>
</div>
@endsection

