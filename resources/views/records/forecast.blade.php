@extends('layouts.app')

@section('content')
<div x-data="{ loading:false }" class="fade-enter fade-enter-active">
<h2>AI Rice Yield Forecast</h2>
<p style="color: #64748b; margin-bottom: 1rem;">This version of the tool focuses solely on <strong>Palo, Leyte</strong>. Provide the planting date (and optional harvest date) and the AI will simulate the growing season based on Palo’s historical climate and agronomic patterns.</p>

<div class="card" style="margin-bottom: 1rem; background:#f1f5f9">
	<p style="margin:0;"><strong>Focus Area:</strong> {{ $focusLocation ?? 'Palo, Leyte' }} &middot; Eastern Visayas &middot; Type II climate</p>
</div>

<form x-on:submit="loading=true" action="{{ route('records.forecast.run') }}" method="POST" class="card">
    @csrf
    <fieldset role="group">
		<label>
			Planting Date
			<input type="date" name="planting_date" value="{{ old('planting_date', $input['planting_date'] ?? date('Y-m-d')) }}" required>
			<small style="color: #64748b; display: block; margin-top: 0.25rem;">When you plant the rice seeds</small>
		</label>
		<label>
			Harvest Date (Optional)
			<input type="date" name="harvest_date" value="{{ old('harvest_date', $input['harvest_date'] ?? '') }}" min="{{ old('planting_date', $input['planting_date'] ?? date('Y-m-d')) }}">
			<small style="color: #64748b; display: block; margin-top: 0.25rem;">Optional. If empty, we assume ~120 days after planting.</small>
		</label>
    </fieldset>
    @if($weatherApiAvailable ?? false)
    <label style="display: flex; align-items: center; gap: 0.5rem;">
        <input type="checkbox" name="use_weather_api" value="1" {{ old('use_weather_api', true) ? 'checked' : '' }}>
        <span>Use Weather API for real-time forecast data</span>
        <small style="color: #64748b; margin-left: auto;">(Improves accuracy for near-term predictions)</small>
    </label>
    @endif
    <label>
        AI Model
        <select name="model_type">
            <option value="all" {{ old('model_type', $modelType ?? 'all') === 'all' ? 'selected' : '' }}>All Models (Compare All 3)</option>
            <option value="neural" {{ old('model_type', $modelType ?? 'all') === 'neural' ? 'selected' : '' }}>Neural Network (Advanced) - Most Accurate</option>
            <option value="decision_tree" {{ old('model_type', $modelType ?? 'all') === 'decision_tree' ? 'selected' : '' }}>Decision Tree (Medium) - Interpretable</option>
            <option value="linear" {{ old('model_type', $modelType ?? 'all') === 'linear' ? 'selected' : '' }}>Linear Regression (Simple) - Fastest</option>
        </select>
    </label>
    <button :aria-busy="loading" :disabled="loading" type="submit" class="btn-brand"> <span x-show="!loading">🤖 Predict Yield</span><span x-show="loading">AI is analyzing...</span></button>
</form>

@isset($error)
<div class="card">
    <article>
        <p>{{ $error }}</p>
    </article>
</div>
@endisset

@isset($predictions)
<div class="card" x-data="{ shown:false }" x-init="shown=true" x-show="shown" x-transition>
    <h3 style="margin-top:0">Prediction Results</h3>
    
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


