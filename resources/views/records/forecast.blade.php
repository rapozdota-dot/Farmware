@extends('layouts.app')

@section('content')
<div x-data="{ loading:false }" class="fade-enter fade-enter-active">
	<div style="text-align: center; margin-bottom: 2rem;">
		<h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">🌾 AI Rice Yield Forecast</h1>
		<p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Predict your rice yield using advanced AI models. Simply enter your planting date and get accurate yield predictions.</p>
	</div>

	<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 4px solid #0ea5e9; max-width: 600px; margin-left: auto; margin-right: auto;">
		<p style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
			<span style="font-size: 1.2rem;">📍</span>
			<span><strong>Focus Area:</strong> {{ $focusLocation ?? 'Palo, Leyte' }} &middot; Eastern Visayas &middot; Type II climate</span>
		</p>
	</div>

	<form x-on:submit="loading=true" action="{{ route('records.forecast.run') }}" method="POST" class="card" style="max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
    @csrf
		<fieldset role="group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;" class="forecast-dates">
			<label>
				<span style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
					<span>🌱</span>
					<span><strong>Planting Date</strong></span>
				</span>
				<input type="date" name="planting_date" value="{{ old('planting_date', $input['planting_date'] ?? date('Y-m-d')) }}" required style="font-size: 1rem; padding: 0.75rem;">
				<small style="color: #64748b; display: block; margin-top: 0.5rem;">When you plant the rice seeds</small>
			</label>
			<label>
				<span style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
					<span>✂️</span>
					<span><strong>Harvest Date</strong> <small style="font-weight: normal;">(Optional)</small></span>
				</span>
				<input type="date" name="harvest_date" value="{{ old('harvest_date', $input['harvest_date'] ?? '') }}" min="{{ old('planting_date', $input['planting_date'] ?? date('Y-m-d')) }}" style="font-size: 1rem; padding: 0.75rem;">
				<small style="color: #64748b; display: block; margin-top: 0.5rem;">Defaults to ~120 days after planting</small>
			</label>
		</fieldset>
		
		@if($weatherApiAvailable ?? false)
		<label style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 1rem;">
			<input type="checkbox" name="use_weather_api" value="1" {{ old('use_weather_api', true) ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem;">
			<div style="flex: 1;">
				<span style="font-weight: 500;">🌤️ Use Weather API</span>
				<small style="color: #64748b; display: block; margin-top: 0.25rem;">Improves accuracy for near-term predictions</small>
			</div>
		</label>
		@endif
		
		<label style="margin-bottom: 1.5rem;">
			<span style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
				<span>🤖</span>
				<span><strong>AI Model</strong></span>
			</span>
			<select name="model_type" style="font-size: 1rem; padding: 0.75rem;">
				<option value="all" {{ old('model_type', $modelType ?? 'all') === 'all' ? 'selected' : '' }}>All Models (Compare All 3)</option>
				<option value="neural" {{ old('model_type', $modelType ?? 'all') === 'neural' ? 'selected' : '' }}>Neural Network (Advanced) - Most Accurate</option>
				<option value="decision_tree" {{ old('model_type', $modelType ?? 'all') === 'decision_tree' ? 'selected' : '' }}>Decision Tree (Medium) - Interpretable</option>
				<option value="linear" {{ old('model_type', $modelType ?? 'all') === 'linear' ? 'selected' : '' }}>Linear Regression (Simple) - Fastest</option>
			</select>
		</label>
		
		<button :aria-busy="loading" :disabled="loading" type="submit" class="btn-brand" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600; margin-top: 0.5rem;">
			<span x-show="!loading">🚀 Predict Yield</span>
			<span x-show="loading">⏳ AI is analyzing...</span>
		</button>
</form>

@isset($error)
<div class="card">
    <article>
        <p>{{ $error }}</p>
    </article>
</div>
@endisset

@isset($predictions)
<div class="card" x-data="{ shown:false }" x-init="shown=true" x-show="shown" x-transition style="max-width: 800px; margin: 2rem auto 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
    <h3 style="margin-top:0; text-align: center; color: #0c4a6e; font-size: 1.75rem;">📊 Prediction Results</h3>
    
    @if(isset($predictions['neural']))
    <div style="margin-bottom: 1rem; padding: 1rem; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #1e40af;">🤖 Neural Network (Advanced AI)</h4>
        <p style="font-size: 1.2em; margin: 0.5rem 0;"><strong>Predicted Yield:</strong> {{ number_format($predictions['neural'], 3) }} t/ha</p>
        <small style="color: #64748b;">Multi-layer perceptron with backpropagation - Most accurate, learns complex patterns</small>
    </div>
    @endif
    
    @if(isset($predictions['decision_tree']))
    <div style="margin-bottom: 1rem; padding: 1rem; background: #ecfdf5; border-left: 4px solid #10b981; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #065f46;">🌳 Decision Tree (Medium AI)</h4>
        <p style="font-size: 1.2em; margin: 0.5rem 0;"><strong>Predicted Yield:</strong> {{ number_format($predictions['decision_tree'], 3) }} t/ha</p>
        <small style="color: #64748b;">CART algorithm - Interpretable, handles non-linear relationships</small>
    </div>
    @endif
    
    @if(isset($predictions['linear']))
    <div style="margin-bottom: 1rem; padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #92400e;">📊 Linear Regression (Simple)</h4>
        <p style="font-size: 1.2em; margin: 0.5rem 0;"><strong>Predicted Yield:</strong> {{ number_format($predictions['linear'], 3) }} t/ha</p>
        <small style="color: #64748b;">Ridge regression - Fastest, simplest statistical model</small>
    </div>
    @endif
    
    @if(count($predictions) > 1)
    <div style="margin-top: 1rem; padding: 1rem; background: #f3f4f6; border-radius: 4px;">
        <p><strong>Model Comparison:</strong></p>
        <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
            @if(isset($predictions['neural']) && isset($predictions['linear']))
            <li>Neural Network vs Linear: {{ number_format(abs($predictions['neural'] - $predictions['linear']), 3) }} t/ha difference</li>
            @endif
            @if(isset($predictions['decision_tree']) && isset($predictions['linear']))
            <li>Decision Tree vs Linear: {{ number_format(abs($predictions['decision_tree'] - $predictions['linear']), 3) }} t/ha difference</li>
            @endif
            @if(isset($predictions['neural']) && isset($predictions['decision_tree']))
            <li>Neural Network vs Decision Tree: {{ number_format(abs($predictions['neural'] - $predictions['decision_tree']), 3) }} t/ha difference</li>
            @endif
        </ul>
    </div>
    @endif
    
    @if(isset($historicalRecords) && $historicalRecords->count() > 0)
    @php
        $histYields = $historicalRecords->pluck('yield_t_ha')->filter(fn($v) => $v > 0)->values();
        $minHist = $histYields->min();
        $maxHist = $histYields->max();
        $avgHist = $histYields->avg();
        $allYields = $histYields->merge(collect($predictions)->filter(fn($v) => is_numeric($v) && $v > 0))->values();
        $minAll = $allYields->min();
        $maxAll = $allYields->max();
    @endphp
    
    <div class="card" style="margin-top: 2rem; background: #f8fafc;">
        <h4 style="margin-top: 0; text-align: center; color: #0c4a6e;">📈 Prediction vs Historical Data</h4>
        <p style="text-align: center; color: #64748b; margin-bottom: 1.5rem; font-size: 0.9rem;">See how your prediction compares to historical yields</p>
        
        <div style="max-width: 700px; margin: 0 auto;">
            <!-- Historical Statistics -->
            <div style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #e5e7eb;">
                <h5 style="margin-top: 0; color: #374151; margin-bottom: 1rem;">📊 Historical Data Summary</h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Average Yield</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #0ea5e9;">{{ number_format($avgHist, 2) }} t/ha</div>
                    </div>
                    <div>
                        <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Minimum</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #64748b;">{{ number_format($minHist, 2) }} t/ha</div>
                    </div>
                    <div>
                        <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Maximum</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #64748b;">{{ number_format($maxHist, 2) }} t/ha</div>
                    </div>
                    <div>
                        <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Records</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #64748b;">{{ $histYields->count() }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Visual Comparison Bar Chart -->
            <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                <h5 style="margin-top: 0; color: #374151; margin-bottom: 1.5rem;">📊 Visual Comparison</h5>
                
                @php
                    $range = max($maxAll - $minAll, 0.1);
                @endphp
                
                @if(isset($predictions['neural']))
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="width: 120px; font-weight: 600; color: #1e40af;">Neural Network:</div>
                        <div style="flex: 1; position: relative;">
                            <div style="background: #e5e7eb; height: 30px; border-radius: 4px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%); height: 100%; width: {{ min(100, max(5, (($predictions['neural'] - $minAll) / $range) * 100)) }}%; border-radius: 4px; display: flex; align-items: center; justify-content: flex-end; padding-right: 0.5rem;">
                                    <span style="color: white; font-weight: bold; font-size: 0.875rem;">{{ number_format($predictions['neural'], 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                        <span>{{ number_format($minAll, 2) }} t/ha</span>
                        <span>{{ number_format($maxAll, 2) }} t/ha</span>
                    </div>
                </div>
                @endif
                
                @if(isset($predictions['decision_tree']))
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="width: 120px; font-weight: 600; color: #065f46;">Decision Tree:</div>
                        <div style="flex: 1; position: relative;">
                            <div style="background: #e5e7eb; height: 30px; border-radius: 4px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #10b981 0%, #065f46 100%); height: 100%; width: {{ min(100, max(5, (($predictions['decision_tree'] - $minAll) / $range) * 100)) }}%; border-radius: 4px; display: flex; align-items: center; justify-content: flex-end; padding-right: 0.5rem;">
                                    <span style="color: white; font-weight: bold; font-size: 0.875rem;">{{ number_format($predictions['decision_tree'], 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                        <span>{{ number_format($minAll, 2) }} t/ha</span>
                        <span>{{ number_format($maxAll, 2) }} t/ha</span>
                    </div>
                </div>
                @endif
                
                @if(isset($predictions['linear']))
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="width: 120px; font-weight: 600; color: #92400e;">Linear Regression:</div>
                        <div style="flex: 1; position: relative;">
                            <div style="background: #e5e7eb; height: 30px; border-radius: 4px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #f59e0b 0%, #92400e 100%); height: 100%; width: {{ min(100, max(5, (($predictions['linear'] - $minAll) / $range) * 100)) }}%; border-radius: 4px; display: flex; align-items: center; justify-content: flex-end; padding-right: 0.5rem;">
                                    <span style="color: white; font-weight: bold; font-size: 0.875rem;">{{ number_format($predictions['linear'], 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                        <span>{{ number_format($minAll, 2) }} t/ha</span>
                        <span>{{ number_format($maxAll, 2) }} t/ha</span>
                    </div>
                </div>
                @endif
                
                <!-- Historical Average Reference -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px dashed #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="width: 120px; font-weight: 600; color: #64748b;">Historical Avg:</div>
                        <div style="flex: 1; position: relative;">
                            <div style="background: #e5e7eb; height: 30px; border-radius: 4px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #94a3b8 0%, #64748b 100%); height: 100%; width: {{ min(100, max(5, (($avgHist - $minAll) / $range) * 100)) }}%; border-radius: 4px; display: flex; align-items: center; justify-content: flex-end; padding-right: 0.5rem;">
                                    <span style="color: white; font-weight: bold; font-size: 0.875rem;">{{ number_format($avgHist, 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comparison Summary -->
            <div style="background: white; padding: 1.5rem; border-radius: 8px; margin-top: 1.5rem; border: 1px solid #e5e7eb;">
                <h5 style="margin-top: 0; color: #374151; margin-bottom: 1rem;">💡 Insights</h5>
                <ul style="margin: 0; padding-left: 1.5rem; color: #64748b;">
                    @if(isset($predictions['neural']))
                    <li style="margin-bottom: 0.5rem;">
                        <strong>Neural Network:</strong> 
                        @if($predictions['neural'] > $avgHist)
                            {{ number_format((($predictions['neural'] - $avgHist) / $avgHist) * 100, 1) }}% above historical average
                        @else
                            {{ number_format((($avgHist - $predictions['neural']) / $avgHist) * 100, 1) }}% below historical average
                        @endif
                    </li>
                    @endif
                    @if(isset($predictions['decision_tree']))
                    <li style="margin-bottom: 0.5rem;">
                        <strong>Decision Tree:</strong> 
                        @if($predictions['decision_tree'] > $avgHist)
                            {{ number_format((($predictions['decision_tree'] - $avgHist) / $avgHist) * 100, 1) }}% above historical average
                        @else
                            {{ number_format((($avgHist - $predictions['decision_tree']) / $avgHist) * 100, 1) }}% below historical average
                        @endif
                    </li>
                    @endif
                    @if(isset($predictions['linear']))
                    <li style="margin-bottom: 0.5rem;">
                        <strong>Linear Regression:</strong> 
                        @if($predictions['linear'] > $avgHist)
                            {{ number_format((($predictions['linear'] - $avgHist) / $avgHist) * 100, 1) }}% above historical average
                        @else
                            {{ number_format((($avgHist - $predictions['linear']) / $avgHist) * 100, 1) }}% below historical average
                        @endif
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
    
    <details style="margin-top: 1rem;">
        <summary>Estimated Parameters (Auto-calculated from Location & Date)</summary>
        <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
            <p><strong>Location:</strong> {{ $focusLocation ?? 'Palo, Leyte' }} (fixed)</p>
            <p><strong>Planting Date:</strong> {{ isset($input['planting_date']) ? \Carbon\Carbon::parse($input['planting_date'])->format('F d, Y') : '-' }}</p>
            <p><strong>Harvest Date:</strong> {{ isset($estimatedFeatures['harvestDate']) ? \Carbon\Carbon::parse($estimatedFeatures['harvestDate'])->format('F d, Y') : '-' }} 
                <small style="color: #64748b;">({{ $estimatedFeatures['growingDays'] ?? 120 }} days growing period)</small>
            </p>
            <p><strong>Season:</strong> {{ $estimatedFeatures['season'] ?? '-' }}</p>
            @if(isset($estimatedFeatures['weatherApiUsed']) && $estimatedFeatures['weatherApiUsed'])
            <p style="color: #059669; font-weight: bold;">🌤️ Weather API data used for forecast</p>
            @endif
            
            @if(isset($estimatedFeatures['hasLocationData']) && $estimatedFeatures['hasLocationData'])
            <div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 0.75rem; margin: 0.5rem 0; border-radius: 4px;">
                <p style="margin: 0; color: #065f46; font-weight: bold;">✓ Location-Specific Data Found</p>
                <small style="color: #047857;">Using historical averages specific to this location and season for more accurate predictions.</small>
                @if(isset($estimatedFeatures['province']))
                <p style="margin: 0.25rem 0 0 0; color: #047857; font-size: 0.875rem;"><strong>Province:</strong> {{ $estimatedFeatures['province'] }}</p>
                @endif
                @if(isset($estimatedFeatures['region']))
                <p style="margin: 0.25rem 0 0 0; color: #047857; font-size: 0.875rem;"><strong>Region:</strong> {{ $estimatedFeatures['region'] }}</p>
                @endif
            </div>
            @elseif(isset($estimatedFeatures['hasRegionData']) && $estimatedFeatures['hasRegionData'])
            <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 0.75rem; margin: 0.5rem 0; border-radius: 4px;">
                <p style="margin: 0; color: #1e40af; font-weight: bold;">🗺️ Using Regional Averages</p>
                <small style="color: #1e3a8a;">
                    @if(isset($estimatedFeatures['isMunicipality']) && $estimatedFeatures['isMunicipality'])
                        Municipality <strong>{{ $focusLocation ?? 'Palo' }}</strong> identified in <strong>{{ $estimatedFeatures['province'] ?? '' }}</strong> province, 
                    @endif
                    Location not in database, but AI identified it as <strong>{{ $estimatedFeatures['region'] ?? 'Unknown' }}</strong> region. Using regional averages from similar locations in this area for better predictions.
                </small>
                @if(isset($estimatedFeatures['province']))
                <p style="margin: 0.25rem 0 0 0; color: #1e3a8a; font-size: 0.875rem;"><strong>Province:</strong> {{ $estimatedFeatures['province'] }}</p>
                @endif
                @if(isset($estimatedFeatures['region']))
                <p style="margin: 0.25rem 0 0 0; color: #1e3a8a; font-size: 0.875rem;"><strong>Region:</strong> {{ $estimatedFeatures['region'] }}</p>
                @endif
                @if(isset($estimatedFeatures['climateZone']))
                <p style="margin: 0.25rem 0 0 0; color: #1e3a8a; font-size: 0.875rem;"><strong>Climate Zone:</strong> {{ $estimatedFeatures['climateZone'] }}</p>
                @endif
            </div>
            @elseif(isset($estimatedFeatures['hasLocationData']) && !$estimatedFeatures['hasLocationData'])
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 0.75rem; margin: 0.5rem 0; border-radius: 4px;">
                <p style="margin: 0; color: #92400e; font-weight: bold;">ℹ️ Using General Season Averages</p>
                <small style="color: #78350f;">
                    @if(isset($estimatedFeatures['region']))
                        Location not in database, but identified as <strong>{{ $estimatedFeatures['region'] }}</strong>. Using general {{ $estimatedFeatures['season'] ?? '' }} season averages.
                    @else
                        Location not recognized. Using general {{ $estimatedFeatures['season'] ?? '' }} season averages from all locations.
                    @endif
                </small>
            </div>
            @endif
            
            <hr style="margin: 0.5rem 0;">
            <p><strong>Estimated Rainfall:</strong> {{ number_format($estimatedFeatures['rainfall_mm'] ?? 0, 1) }} mm</p>
            <p><strong>Estimated Temperature:</strong> {{ number_format($estimatedFeatures['temperature_c'] ?? 0, 1) }} °C</p>
            <p><strong>Estimated Soil pH:</strong> {{ number_format($estimatedFeatures['soil_ph'] ?? 0, 2) }}</p>
            <p><strong>Estimated Fertilizer:</strong> {{ number_format($estimatedFeatures['fertilizer_kg'] ?? 0, 1) }} kg</p>
            <p><strong>Estimated Area:</strong> {{ number_format($estimatedFeatures['area_ha'] ?? 0, 2) }} ha</p>
            <small style="color: #64748b; display: block; margin-top: 0.5rem;">{{ $estimatedFeatures['dataSource'] ?? 'These values are based on historical averages.' }}</small>
        </div>
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

@isset($yieldJustification)
<div class="card" style="margin-top: 1rem; background:#fff7ed; border-left:4px solid #f97316;">
	<h4 style="margin-top:0;">Why this yield?</h4>
	<p style="margin-bottom:0;">{{ $yieldJustification }}</p>
</div>
@endisset
@endsection


