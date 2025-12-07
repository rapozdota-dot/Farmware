@extends('layouts.app')

@section('content')
<div x-data="{ q: '' }" x-init="$nextTick(() => { const el = document.querySelector('[data-search]'); el && el.focus(); })" class="fade-enter fade-enter-active">
<h2>Records</h2>

<div class="grid">
	<div>
		<input data-search x-model="q" type="search" placeholder="Search by location or season...">
	</div>
	<div class="text-right">
		<a href="{{ route('records.data') }}" role="button" class="secondary">Data Management</a>
		<a href="{{ route('records.create') }}" role="button" class="btn-brand">Add Record</a>
	</div>
</div>

<table>
	<thead>
		<tr>
			<th>Location</th>
			<th>Season</th>
			<th>Area (ha)</th>
			<th>Rainfall (mm)</th>
			<th>Temp (°C)</th>
			<th>Soil pH</th>
			<th>Fertilizer (kg)</th>
			<th>Yield (t/ha)</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		@foreach($records as $r)
		<tr x-show="'{{ Str::of($r->location.' '.$r->season)->lower() }}'.includes(q.toLowerCase())">
			<td>{{ $r->location }}</td>
			<td>{{ $r->season }}</td>
			<td>{{ $r->area_ha }}</td>
			<td>{{ $r->rainfall_mm }}</td>
			<td>{{ $r->temperature_c }}</td>
			<td>{{ $r->soil_ph }}</td>
			<td>{{ $r->fertilizer_kg }}</td>
			<td>{{ $r->yield_t_ha }}</td>
			<td class="table-actions">
				<a href="{{ route('records.edit', $r) }}">Edit</a>
				<button type="button" @click="$refs['confirm{{ $r->id }}'].showModal()">Delete</button>
				<dialog x-ref="confirm{{ $r->id }}">
					<article>
						<header>Delete record?</header>
						<p>Location: <strong>{{ $r->location }}</strong></p>
						<footer>
							<form action="{{ route('records.destroy', $r) }}" method="POST">
								@csrf
								@method('DELETE')
								<button type="submit" class="contrast">Confirm</button>
							</form>
							<button @click="$refs['confirm{{ $r->id }}'].close()" class="secondary">Cancel</button>
						</footer>
					</article>
				</dialog>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>

<div class="pagination-lite" style="display:flex;align-items:center;gap:.5rem;justify-content:flex-end;margin-top:.5rem;">
	@if ($records->onFirstPage())
		<button disabled class="secondary">Prev</button>
	@else
		<a href="{{ $records->previousPageUrl() }}" role="button" class="secondary">Prev</a>
	@endif
	<span style="font-size:.9rem;opacity:.8">Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>
	@if ($records->hasMorePages())
		<a href="{{ $records->nextPageUrl() }}" role="button" class="secondary">Next</a>
	@else
		<button disabled class="secondary">Next</button>
	@endif
</div>
</div>
<div class="card" style="margin-top:1rem;background:#f8fafc" x-data="{
	field: 'rainfall_mm',
	fields: [
		{key:'rainfall_mm', label:'Rainfall (mm)'},
		{key:'temperature_c', label:'Temperature (°C)'},
		{key:'fertilizer_kg', label:'Fertilizer (kg)'},
		{key:'area_ha', label:'Area (ha)'},
		{key:'soil_ph', label:'Soil pH'}
	],
	draw() {
		try {
			const svg = this.$refs.svg;
			if (!svg) {
				console.error('SVG element not found');
				return;
			}
			while (svg.firstChild) svg.removeChild(svg.firstChild);
		const rows = [
			@if(isset($allRecords) && $allRecords->count() > 0)
				@foreach($allRecords as $r)
				{ rainfall_mm: {{ (float)($r->rainfall_mm ?? 0) }}, temperature_c: {{ (float)($r->temperature_c ?? 0) }}, fertilizer_kg: {{ (float)($r->fertilizer_kg ?? 0) }}, area_ha: {{ (float)($r->area_ha ?? 0) }}, soil_ph: {{ (float)($r->soil_ph ?? 0) }}, y: {{ (float)($r->yield_t_ha ?? 0) }} },
				@endforeach
			@else
				// Fallback data for testing
				{ rainfall_mm: 360, temperature_c: 26.5, fertilizer_kg: 180, area_ha: 2.0, soil_ph: 6.2, y: 5.4 },
				{ rainfall_mm: 120, temperature_c: 28.5, fertilizer_kg: 200, area_ha: 2.0, soil_ph: 6.4, y: 5.8 },
				{ rainfall_mm: 410, temperature_c: 26.0, fertilizer_kg: 160, area_ha: 1.5, soil_ph: 5.9, y: 5.1 },
				{ rainfall_mm: 140, temperature_c: 28.8, fertilizer_kg: 190, area_ha: 1.5, soil_ph: 6.0, y: 5.3 },
				{ rainfall_mm: 330, temperature_c: 27.2, fertilizer_kg: 170, area_ha: 1.2, soil_ph: 6.6, y: 5.0 }
			@endif
		].filter(d => (typeof d[this.field] === 'number' && !isNaN(d[this.field])) && (typeof d.y === 'number' && !isNaN(d.y)));
		const w = 700, h = 280, p = 44;
		if (rows.length === 0) return;
		const xs = Math.min(...rows.map(r=>r[this.field])), xM = Math.max(...rows.map(r=>r[this.field]));
		const ys = Math.min(...rows.map(r=>r.y)), yM = Math.max(...rows.map(r=>r.y));
		const sx = v => p + (w - 2*p) * (v - xs) / Math.max(1e-9, (xM - xs));
		const sy = v => h - p - (h - 2*p) * (v - ys) / Math.max(1e-9, (yM - ys));
		const make = (name)=>document.createElementNS('http://www.w3.org/2000/svg',name);
		// background
		const bg = make('rect'); bg.setAttribute('x', 0); bg.setAttribute('y', 0); bg.setAttribute('width', w); bg.setAttribute('height', h); bg.setAttribute('fill', '#f8fafc'); svg.appendChild(bg);
		const xAxis = make('line'); xAxis.setAttribute('x1', p); xAxis.setAttribute('y1', h-p); xAxis.setAttribute('x2', w-p); xAxis.setAttribute('y2', h-p); xAxis.setAttribute('stroke', '#e5e7eb'); svg.appendChild(xAxis);
		const yAxis = make('line'); yAxis.setAttribute('x1', p); yAxis.setAttribute('y1', p); yAxis.setAttribute('x2', p); yAxis.setAttribute('y2', h-p); yAxis.setAttribute('stroke', '#e5e7eb'); svg.appendChild(yAxis);
		// gridlines and tick labels (4 steps)
		const steps = 4;
		for (let i=0;i<=steps;i++) {
			const tx = xs + (i/steps)*(xM-xs);
			const gx = make('line'); gx.setAttribute('x1', sx(tx)); gx.setAttribute('y1', p); gx.setAttribute('x2', sx(tx)); gx.setAttribute('y2', h-p); gx.setAttribute('stroke', '#edf2f7'); svg.appendChild(gx);
			const tlx = make('text'); tlx.setAttribute('x', sx(tx)); tlx.setAttribute('y', h - p + 16); tlx.setAttribute('text-anchor','middle'); tlx.setAttribute('fill','#6b7280'); tlx.setAttribute('font-size','10'); tlx.textContent = (Math.round(tx*100)/100).toString(); svg.appendChild(tlx);
			const ty = ys + (i/steps)*(yM-ys);
			const gy = make('line'); gy.setAttribute('x1', p); gy.setAttribute('y1', sy(ty)); gy.setAttribute('x2', w-p); gy.setAttribute('y2', sy(ty)); gy.setAttribute('stroke', '#edf2f7'); svg.appendChild(gy);
			const tly = make('text'); tly.setAttribute('x', p - 6); tly.setAttribute('y', sy(ty)+3); tly.setAttribute('text-anchor','end'); tly.setAttribute('fill','#6b7280'); tly.setAttribute('font-size','10'); tly.textContent = (Math.round(ty*100)/100).toString(); svg.appendChild(tly);
		}
		// Create simple circles without tooltips
		rows.forEach(pt => { 
			const c = make('circle'); 
			c.setAttribute('cx', sx(pt[this.field])); 
			c.setAttribute('cy', sy(pt.y)); 
			c.setAttribute('r', 3); 
			c.setAttribute('fill', '#0ea5e9'); 
			svg.appendChild(c); 
		});
		// regression line y = a + b x
		const n = rows.length;
		let sumx=0,sumy=0,sumxy=0,sumxx=0; rows.forEach(pt=>{ const x=pt[this.field]; const y=pt.y; sumx+=x; sumy+=y; sumxy+=x*y; sumxx+=x*x; });
		const b = (n*sumxy - sumx*sumy) / Math.max(1e-9, (n*sumxx - sumx*sumx));
		const a = (sumy - b*sumx)/n;
		const y1 = a + b*xs, y2 = a + b*xM;
		const line = make('line'); line.setAttribute('x1', sx(xs)); line.setAttribute('y1', sy(y1)); line.setAttribute('x2', sx(xM)); line.setAttribute('y2', sy(y2)); line.setAttribute('stroke', '#ef4444'); line.setAttribute('stroke-width','2'); svg.appendChild(line);
		// axis labels
		const xLabel = make('text'); xLabel.setAttribute('x', (w/2)); xLabel.setAttribute('y', h - 6); xLabel.setAttribute('text-anchor','middle'); xLabel.setAttribute('fill','#111827'); xLabel.setAttribute('font-size','12'); xLabel.textContent = this.fields.find(f=>f.key===this.field)?.label || this.field; svg.appendChild(xLabel);
		const yLabel = make('text'); yLabel.setAttribute('x', 12); yLabel.setAttribute('y', 14); yLabel.setAttribute('fill','#111827'); yLabel.setAttribute('font-size','12'); yLabel.textContent = 'Yield (t/ha)'; svg.appendChild(yLabel);
		// legend
		const lgx = w - p - 130, lgy = p - 18;
		const ld = make('rect'); ld.setAttribute('x', lgx-8); ld.setAttribute('y', lgy-12); ld.setAttribute('width', 128); ld.setAttribute('height', 40); ld.setAttribute('rx',6); ld.setAttribute('fill','#ffffffcc'); ld.setAttribute('stroke','#e5e7eb'); svg.appendChild(ld);
		const dot = make('circle'); dot.setAttribute('cx', lgx); dot.setAttribute('cy', lgy); dot.setAttribute('r', 4); dot.setAttribute('fill', '#0ea5e9'); svg.appendChild(dot);
		const txt1 = make('text'); txt1.setAttribute('x', lgx+12); txt1.setAttribute('y', lgy+3); txt1.setAttribute('fill','#374151'); txt1.setAttribute('font-size','12'); txt1.textContent = 'Observed record'; svg.appendChild(txt1);
		const ln = make('line'); ln.setAttribute('x1', lgx-4); ln.setAttribute('y1', lgy+16); ln.setAttribute('x2', lgx+8); ln.setAttribute('y2', lgy+16); ln.setAttribute('stroke', '#ef4444'); ln.setAttribute('stroke-width','2'); svg.appendChild(ln);
		const txt2 = make('text'); txt2.setAttribute('x', lgx+12); txt2.setAttribute('y', lgy+20); txt2.setAttribute('fill','#374151'); txt2.setAttribute('font-size','12'); txt2.textContent = 'Best-fit line'; svg.appendChild(txt2);
		} catch (error) {
			console.error('Error drawing chart:', error);
			// Show error message in SVG
			const errorText = make('text');
			errorText.setAttribute('x', '50%');
			errorText.setAttribute('y', '50%');
			errorText.setAttribute('text-anchor', 'middle');
			errorText.setAttribute('fill', '#ef4444');
			errorText.setAttribute('font-size', '14');
			errorText.textContent = 'Error loading chart data';
			svg.appendChild(errorText);
		}
	}
}"
x-init="draw()"
>
	<div class="grid">
		<div>
			<label>
				X-axis feature
				<select x-model="field" @change="draw()">
					<template x-for="f in fields" :key="f.key">
						<option :value="f.key" x-text="f.label"></option>
					</template>
				</select>
			</label>
		</div>
	</div>
	<div style="width:100%;">
		<svg x-ref="svg" viewBox="0 0 700 280" preserveAspectRatio="xMidYMid meet" style="width:100%;height:auto;"></svg>
	</div>
	<p class="text-sm">Blue dots represent all observed records in the database. Red line shows the linear regression trend between the selected feature and yield. This visualization helps understand the relationship patterns that the AI models learn from.</p>
</div>
@endsection


