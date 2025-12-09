@extends('layouts.app')

@section('content')
	<div x-data="{ q: '' }"
		x-init="$nextTick(() => { const el = document.querySelector('[data-search]'); el && el.focus(); })"
		class="fade-enter fade-enter-active animate-slide-up">
		<div style="margin-bottom: 2rem;">
			<h1 class="text-gradient-hero" style="font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 800;">📋 Records
			</h1>
			<p style="color: var(--muted-foreground); font-size: 1.1rem;">View and manage your rice yield records</p>
		</div>

		<div class="card animate-fade-in stagger-1" style="margin-bottom: 2rem; background: var(--gradient-card);">
			<div style="display: grid; grid-template-columns: 1fr auto; gap: 1.5rem; align-items: center;">
				<div style="position: relative;">
					<input data-search x-model="q" type="text" placeholder="Search by location or season..."
						style="width: 100%; padding: 1rem 1rem 1rem 3rem; font-size: 1rem; border-radius: var(--radius); border: 2px solid var(--border); background: #2d2d2d; color: #ffffff; transition: all 0.3s ease;"
						onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'"
						onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
					<span
						style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none;">🔍</span>
				</div>
				<div style="display: flex; gap: 1rem;">
					<a href="{{ route('records.data') }}" role="button" class="secondary"
						style="padding: 1rem 1.5rem; border-radius: var(--radius); font-weight: 600;">
						📊 Data Management
					</a>
					<a href="{{ route('records.create') }}" role="button" class="btn-brand"
						style="padding: 1rem 1.5rem; border-radius: var(--radius); font-weight: 600;">
						➕ Add Record
					</a>
				</div>
			</div>
		</div>

		<div class="card animate-fade-in stagger-2" style="overflow-x: auto; padding: 0; background: #2d2d2d;">
			<table style="margin: 0; width: 100%; background: #2d2d2d; color: #ffffff;">
				<thead>
					<tr style="background: var(--gradient-hero); color: white;">
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">📍 Location</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">🌾 Season</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">📏 Area (ha)</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">💧 Rainfall (mm)</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">🌡️ Temp (°C)</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">🧪 Soil pH</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">🌾 Fertilizer (kg)</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">📊 Yield (t/ha)</th>
						<th style="padding: 1.25rem 1rem; font-weight: 700; text-align: left;">⚙️ Actions</th>
					</tr>
				</thead>
				<tbody>
					@foreach($records as $r)
						<tr x-show="'{{ Str::of($r->location . ' ' . $r->season)->lower() }}'.includes(q.toLowerCase())"
							style="border-bottom: 1px solid var(--border); transition: all 0.2s ease; background: #2d2d2d;"
							onmouseover="this.style.background='#3a3a3a'; this.style.transform='scale(1.01)'"
							onmouseout="this.style.background='#2d2d2d'; this.style.transform='scale(1)'">
							<td style="padding: 1rem; font-weight: 600; color: #ffffff;">{{ $r->location }}</td>
							<td style="padding: 1rem;">
								<span
									style="display: inline-block; padding: 0.25rem 0.75rem; background: var(--primary-bg); color: var(--primary); border-radius: calc(var(--radius) / 2); font-weight: 600; font-size: 0.875rem;">
									{{ $r->season }}
								</span>
							</td>
							<td style="padding: 1rem; color: #ffffff; font-weight: 500;">{{ number_format($r->area_ha, 2) }}
							</td>
							<td style="padding: 1rem; color: #ffffff; font-weight: 500;">{{ number_format($r->rainfall_mm, 1) }}
							</td>
							<td style="padding: 1rem; color: #ffffff; font-weight: 500;">
								{{ number_format($r->temperature_c, 1) }}</td>
							<td style="padding: 1rem; color: #ffffff; font-weight: 500;">{{ number_format($r->soil_ph, 2) }}
							</td>
							<td style="padding: 1rem; color: #ffffff; font-weight: 500;">
								{{ number_format($r->fertilizer_kg, 1) }}</td>
							<td style="padding: 1rem;">
								<span
									style="font-weight: 700; color: #4ade80; font-size: 1.1rem;">{{ number_format($r->yield_t_ha, 2) }}</span>
							</td>
							<td class="table-actions" style="padding: 1rem; white-space: nowrap;">
								<a href="{{ route('records.edit', $r) }}"
									style="padding: 0.4rem 0.75rem; background: var(--secondary-bg); color: var(--secondary); border-radius: calc(var(--radius) / 2); text-decoration: none; font-weight: 600; font-size: 0.8rem; transition: all 0.2s ease; display: inline-block;"
									onmouseover="this.style.background='var(--secondary)'; this.style.color='white'; this.style.transform='translateY(-1px)'"
									onmouseout="this.style.background='var(--secondary-bg)'; this.style.color='var(--secondary)'; this.style.transform='translateY(0)'">
									✏️ Edit
								</a>
								<button type="button" @click="$refs['confirm{{ $r->id }}'].showModal()"
									style="padding: 0.4rem 0.75rem; background: hsl(0, 84%, 60%); color: white; border: none; border-radius: calc(var(--radius) / 2); font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.2s ease; margin-left: 0.5rem;"
									onmouseover="this.style.background='hsl(0, 84%, 55%)'; this.style.transform='translateY(-1px)'"
									onmouseout="this.style.background='hsl(0, 84%, 60%)'; this.style.transform='translateY(0)'">
									🗑️ Delete
								</button>
								<dialog x-ref="confirm{{ $r->id }}"
									style="border: none; border-radius: calc(var(--radius) * 2); padding: 0; max-width: 500px;">
									<article
										style="padding: 2rem; background: var(--card); border-radius: calc(var(--radius) * 2);">
										<header style="margin-bottom: 1.5rem;">
											<h3 style="margin: 0; color: var(--foreground); font-size: 1.5rem;">⚠️ Delete
												Record?</h3>
										</header>
										<p style="color: var(--muted-foreground); margin-bottom: 1.5rem;">Are you sure you want
											to delete this record?</p>
										<p style="font-weight: 600; color: var(--foreground); margin-bottom: 1.5rem;">Location:
											<strong style="color: var(--primary);">{{ $r->location }}</strong></p>
										<footer style="display: flex; gap: 1rem; justify-content: flex-end;">
											<form action="{{ route('records.destroy', $r) }}" method="POST" style="margin: 0;">
												@csrf
												@method('DELETE')
												<button type="submit"
													style="padding: 0.75rem 1.5rem; background: hsl(0, 84%, 60%); color: white; border: none; border-radius: var(--radius); font-weight: 600; cursor: pointer; transition: all 0.2s ease;"
													onmouseover="this.style.background='hsl(0, 84%, 55%)'; this.style.transform='translateY(-2px)'"
													onmouseout="this.style.background='hsl(0, 84%, 60%)'; this.style.transform='translateY(0)'">
													Confirm Delete
												</button>
											</form>
											<button @click="$refs['confirm{{ $r->id }}'].close()"
												style="padding: 0.75rem 1.5rem; background: var(--muted); color: var(--foreground); border: 1px solid var(--border); border-radius: var(--radius); font-weight: 600; cursor: pointer; transition: all 0.2s ease;"
												onmouseover="this.style.background='var(--border)'; this.style.transform='translateY(-2px)'"
												onmouseout="this.style.background='var(--muted)'; this.style.transform='translateY(0)'">
												Cancel
											</button>
										</footer>
									</article>
								</dialog>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="card animate-fade-in stagger-4"
			style="margin-top: 2rem; padding: 1.25rem; background: var(--gradient-card); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
			<div style="display: flex; align-items: center; gap: 1rem;">
				@if ($records->onFirstPage())
					<button disabled class="secondary"
						style="padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; opacity: 0.5; cursor: not-allowed;">←
						Prev</button>
				@else
					<a href="{{ $records->previousPageUrl() }}" role="button" class="secondary"
						style="padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; text-decoration: none; transition: all 0.2s ease;"
						onmouseover="this.style.transform='translateX(-2px)'" onmouseout="this.style.transform='translateX(0)'">
						← Prev
					</a>
				@endif
				<span
					style="font-size: 1rem; font-weight: 600; color: var(--foreground); padding: 0.75rem 1.25rem; background: var(--muted); border-radius: var(--radius);">
					Page <strong style="color: var(--primary);">{{ $records->currentPage() }}</strong> of <strong
						style="color: var(--primary);">{{ $records->lastPage() }}</strong>
				</span>
				@if ($records->hasMorePages())
					<a href="{{ $records->nextPageUrl() }}" role="button" class="secondary"
						style="padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; text-decoration: none; transition: all 0.2s ease;"
						onmouseover="this.style.transform='translateX(2px)'" onmouseout="this.style.transform='translateX(0)'">
						Next →
					</a>
				@else
					<button disabled class="secondary"
						style="padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; opacity: 0.5; cursor: not-allowed;">Next
						→</button>
				@endif
			</div>
			<div style="color: var(--muted-foreground); font-size: 0.9rem;">
				Showing {{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
			</div>
		</div>
	</div>
	<div class="card animate-fade-in stagger-3"
		style="margin-top:2rem;background:var(--gradient-card);border:1px solid var(--border);" x-data="{
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
						{ rainfall_mm: {{ (float) ($r->rainfall_mm ?? 0) }}, temperature_c: {{ (float) ($r->temperature_c ?? 0) }}, fertilizer_kg: {{ (float) ($r->fertilizer_kg ?? 0) }}, area_ha: {{ (float) ($r->area_ha ?? 0) }}, soil_ph: {{ (float) ($r->soil_ph ?? 0) }}, y: {{ (float) ($r->yield_t_ha ?? 0) }} },
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
				console.log('Plotted rows (client):', rows.length);
			const w = 700, h = 280, p = 44;
			if (rows.length === 0) return;
			const xs = Math.min(...rows.map(r=>r[this.field])), xM = Math.max(...rows.map(r=>r[this.field]));
			const ys = Math.min(...rows.map(r=>r.y)), yM = Math.max(...rows.map(r=>r.y));
			const sx = v => p + (w - 2*p) * (v - xs) / Math.max(1e-9, (xM - xs));
			const sy = v => h - p - (h - 2*p) * (v - ys) / Math.max(1e-9, (yM - ys));
			const make = (name)=>document.createElementNS('http://www.w3.org/2000/svg',name);
			// background
			const bg = make('rect'); bg.setAttribute('x', 0); bg.setAttribute('y', 0); bg.setAttribute('width', w); bg.setAttribute('height', h); bg.setAttribute('fill', 'hsl(140, 20%, 98%)'); svg.appendChild(bg);
			const xAxis = make('line'); xAxis.setAttribute('x1', p); xAxis.setAttribute('y1', h-p); xAxis.setAttribute('x2', w-p); xAxis.setAttribute('y2', h-p); xAxis.setAttribute('stroke', 'hsl(150, 15%, 88%)'); xAxis.setAttribute('stroke-width', '2'); svg.appendChild(xAxis);
			const yAxis = make('line'); yAxis.setAttribute('x1', p); yAxis.setAttribute('y1', p); yAxis.setAttribute('x2', p); yAxis.setAttribute('y2', h-p); yAxis.setAttribute('stroke', 'hsl(150, 15%, 88%)'); yAxis.setAttribute('stroke-width', '2'); svg.appendChild(yAxis);
			// gridlines and tick labels (4 steps)
			const steps = 4;
			for (let i=0;i<=steps;i++) {
				const tx = xs + (i/steps)*(xM-xs);
				const gx = make('line'); gx.setAttribute('x1', sx(tx)); gx.setAttribute('y1', p); gx.setAttribute('x2', sx(tx)); gx.setAttribute('y2', h-p); gx.setAttribute('stroke', 'hsl(150, 15%, 94%)'); svg.appendChild(gx);
				const tlx = make('text'); tlx.setAttribute('x', sx(tx)); tlx.setAttribute('y', h - p + 16); tlx.setAttribute('text-anchor','middle'); tlx.setAttribute('fill','hsl(150, 10%, 45%)'); tlx.setAttribute('font-size','11'); tlx.setAttribute('font-weight','500'); tlx.textContent = (Math.round(tx*100)/100).toString(); svg.appendChild(tlx);
				const ty = ys + (i/steps)*(yM-ys);
				const gy = make('line'); gy.setAttribute('x1', p); gy.setAttribute('y1', sy(ty)); gy.setAttribute('x2', w-p); gy.setAttribute('y2', sy(ty)); gy.setAttribute('stroke', 'hsl(150, 15%, 94%)'); svg.appendChild(gy);
				const tly = make('text'); tly.setAttribute('x', p - 6); tly.setAttribute('y', sy(ty)+3); tly.setAttribute('text-anchor','end'); tly.setAttribute('fill','hsl(150, 10%, 45%)'); tly.setAttribute('font-size','11'); tly.setAttribute('font-weight','500'); tly.textContent = (Math.round(ty*100)/100).toString(); svg.appendChild(tly);
			}
			// Create simple circles without tooltips
			rows.forEach(pt => { 
				const c = make('circle'); 
				c.setAttribute('cx', sx(pt[this.field])); 
				c.setAttribute('cy', sy(pt.y)); 
				c.setAttribute('r', 4); 
				c.setAttribute('fill', 'hsl(199, 89%, 48%)'); 
				c.setAttribute('opacity', '0.7');
				c.style.cursor = 'pointer';
				c.onmouseover = function() { this.setAttribute('r', 6); this.setAttribute('opacity', '1'); };
				c.onmouseout = function() { this.setAttribute('r', 4); this.setAttribute('opacity', '0.7'); };
				svg.appendChild(c); 
			});
			// regression line y = a + b x
			const n = rows.length;
			let sumx=0,sumy=0,sumxy=0,sumxx=0; rows.forEach(pt=>{ const x=pt[this.field]; const y=pt.y; sumx+=x; sumy+=y; sumxy+=x*y; sumxx+=x*x; });
			const b = (n*sumxy - sumx*sumy) / Math.max(1e-9, (n*sumxx - sumx*sumx));
			const a = (sumy - b*sumx)/n;
			const y1 = a + b*xs, y2 = a + b*xM;
			const line = make('line'); line.setAttribute('x1', sx(xs)); line.setAttribute('y1', sy(y1)); line.setAttribute('x2', sx(xM)); line.setAttribute('y2', sy(y2)); line.setAttribute('stroke', 'hsl(152, 60%, 36%)'); line.setAttribute('stroke-width','3'); line.setAttribute('opacity','0.8'); svg.appendChild(line);
			// axis labels
			const xLabel = make('text'); xLabel.setAttribute('x', (w/2)); xLabel.setAttribute('y', h - 6); xLabel.setAttribute('text-anchor','middle'); xLabel.setAttribute('fill','hsl(150, 30%, 10%)'); xLabel.setAttribute('font-size','13'); xLabel.setAttribute('font-weight','600'); xLabel.textContent = this.fields.find(f=>f.key===this.field)?.label || this.field; svg.appendChild(xLabel);
			const yLabel = make('text'); yLabel.setAttribute('x', 12); yLabel.setAttribute('y', 14); yLabel.setAttribute('fill','hsl(150, 30%, 10%)'); yLabel.setAttribute('font-size','13'); yLabel.setAttribute('font-weight','600'); yLabel.textContent = 'Yield (t/ha)'; svg.appendChild(yLabel);
			// legend
			const lgx = w - p - 140, lgy = p - 18;
			const ld = make('rect'); ld.setAttribute('x', lgx-8); ld.setAttribute('y', lgy-12); ld.setAttribute('width', 136); ld.setAttribute('height', 44); ld.setAttribute('rx',8); ld.setAttribute('fill','hsl(0, 0%, 100%)'); ld.setAttribute('stroke','hsl(150, 15%, 88%)'); ld.setAttribute('stroke-width','1'); svg.appendChild(ld);
			const dot = make('circle'); dot.setAttribute('cx', lgx); dot.setAttribute('cy', lgy); dot.setAttribute('r', 5); dot.setAttribute('fill', 'hsl(199, 89%, 48%)'); svg.appendChild(dot);
			const txt1 = make('text'); txt1.setAttribute('x', lgx+14); txt1.setAttribute('y', lgy+4); txt1.setAttribute('fill','hsl(150, 30%, 10%)'); txt1.setAttribute('font-size','12'); txt1.setAttribute('font-weight','600'); txt1.textContent = 'Observed record'; svg.appendChild(txt1);
			const ln = make('line'); ln.setAttribute('x1', lgx-4); ln.setAttribute('y1', lgy+18); ln.setAttribute('x2', lgx+10); ln.setAttribute('y2', lgy+18); ln.setAttribute('stroke', 'hsl(152, 60%, 36%)'); ln.setAttribute('stroke-width','3'); ln.setAttribute('opacity','0.8'); svg.appendChild(ln);
			const txt2 = make('text'); txt2.setAttribute('x', lgx+14); txt2.setAttribute('y', lgy+22); txt2.setAttribute('fill','hsl(150, 30%, 10%)'); txt2.setAttribute('font-size','12'); txt2.setAttribute('font-weight','600'); txt2.textContent = 'Best-fit line'; svg.appendChild(txt2);
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
	}" x-init="draw()">
		<div style="margin-bottom: 1.5rem;">
			<label style="display: block; margin-bottom: 1rem;">
				<span
					style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; color: var(--foreground); font-weight: 700; font-size: 1.1rem;">
					<span>📊</span>
					<span>Select Feature to Analyze</span>
				</span>
				<select x-model="field" @change="draw()"
					style="padding: 1rem; border-radius: var(--radius); border: 2px solid var(--border); background: var(--card); width: 100%; max-width: 400px; font-size: 1rem; font-weight: 600; color: var(--foreground); cursor: pointer; transition: all 0.3s ease;"
					onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'"
					onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
					<template x-for="f in fields" :key="f.key">
						<option :value="f.key" x-text="f.label"></option>
					</template>
				</select>
			</label>
		</div>
		<div
			style="width:100%; background: var(--card); border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border);">
			<svg x-ref="svg" viewBox="0 0 700 280" preserveAspectRatio="xMidYMid meet"
				style="width:100%;height:auto; border-radius: var(--radius);"></svg>
			<div style="margin-top:8px; font-size:0.9rem; color:var(--muted-foreground);">
				Plotted records (server): <strong>{{ isset($allRecords) ? $allRecords->count() : 0 }}</strong>
				&middot; Unique (rainfall,yield):
				<strong>{{ isset($allRecords) ? $allRecords->unique(fn($r) => ($r->rainfall_mm ?? '') . '|' . ($r->yield_t_ha ?? ''))->count() : 0 }}</strong>
			</div>
		</div>
		<p
			style="text-align: center; color: var(--foreground); margin-top: 1.5rem; font-size: 0.95rem; line-height: 1.6; padding: 1rem; background: var(--muted); border-radius: var(--radius);">
			<span style="color: var(--primary); font-weight: 600;">💡 Insight:</span> <span
				style="color: var(--foreground);">Blue dots represent all observed records in the database. <span
					style="color: var(--primary); font-weight: 600;">Green line</span> shows the linear regression trend
				between the selected feature and yield. This visualization helps understand the relationship patterns that
				the AI models learn from.</span>
		</p>
	</div>
@endsection