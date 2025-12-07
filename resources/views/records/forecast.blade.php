@extends('layouts.app')

@section('content')
<div x-data="{ loading:false, showDetails: false }" class="fade-enter fade-enter-active">
	<div style="text-align: center; margin-bottom: 3rem;" class="animate-fade-in">
		<h1 class="text-gradient-hero" style="font-size: 3.5rem; margin-bottom: 1rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.1;">
			🌾 AI Rice Yield Forecast
		</h1>
		<p style="color: var(--muted-foreground); font-size: 1.25rem; max-width: 700px; margin: 0 auto; line-height: 1.6;">
			Predict your rice yield using advanced AI models. Simply enter your planting date and get accurate yield predictions powered by simple machine learning.
		</p>
	</div>

	<div class="card animate-fade-in stagger-1" 
		 style="margin-bottom: 2rem; background: var(--gradient-card); border: 2px solid var(--primary); max-width: 700px; margin-left: auto; margin-right: auto; cursor: pointer; transition: all 0.3s ease;"
		 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-card)'"
		 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-soft)'">
		<p style="margin:0; display: flex; align-items: center; gap: 0.75rem; font-size: 1.05rem;">
			<span style="font-size: 1.5rem;">📍</span>
			<span><strong style="color: var(--primary-dark);">Focus Area:</strong> <span style="color: var(--foreground);">{{ $focusLocation ?? 'Palo, Leyte' }} &middot; Eastern Visayas &middot; Type II climate</span></span>
		</p>
</div>

	<form x-on:submit="loading=true" action="{{ route('records.forecast.run') }}" method="POST" class="card animate-fade-in stagger-2" style="max-width: 700px; margin: 0 auto; box-shadow: var(--shadow-card); border: none; background: var(--gradient-card);">
    @csrf
		<fieldset role="group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; border: none; padding: 0;" class="forecast-dates">
			<label style="margin-bottom: 0;">
				<span style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; color: var(--foreground);">
					<span style="font-size: 1.5rem;">🌱</span>
					<span style="font-weight: 700; font-size: 1.05rem;">Planting Date</span>
				</span>
                <input type="date" name="planting_date" value="{{ old('planting_date', $input['planting_date'] ?? date('Y-m-d')) }}" required 
                       style="font-size: 1rem; padding: 1rem; border-radius: var(--radius); border: 2px solid var(--border); background: #ffffff; color: #000000; transition: all 0.3s ease; width: 100%; cursor: pointer; position: relative; z-index: 20; pointer-events: auto;" 
                       onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'" 
                       onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'" aria-label="Planting date">
				<small style="color: var(--muted-foreground); display: block; margin-top: 0.5rem; font-size: 0.875rem;">When you plant the rice seeds</small>
				@error('planting_date')
					<small style="color: #ef4444; display: block; margin-top: 0.25rem; font-size: 0.875rem;">{{ $message }}</small>
				@enderror
		</label>
			<label style="margin-bottom: 0;">
				<span style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; color: var(--foreground);">
					<span style="font-size: 1.5rem;">✂️</span>
					<span style="font-weight: 700; font-size: 1.05rem;">Harvest Date <span style="font-weight: 400; font-size: 0.9rem; color: var(--muted-foreground);">(Optional)</span></span>
				</span>
                <input type="date" name="harvest_date" value="{{ old('harvest_date', $input['harvest_date'] ?? '') }}" min="{{ old('planting_date', $input['planting_date'] ?? date('Y-m-d')) }}" 
                       style="font-size: 1rem; padding: 1rem; border-radius: var(--radius); border: 2px solid var(--border); background: #ffffff; color: #000000; transition: all 0.3s ease; width: 100%; cursor: pointer; position: relative; z-index: 20; pointer-events: auto;" 
                       onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'" 
                       onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'" aria-label="Harvest date">
				<small style="color: var(--muted-foreground); display: block; margin-top: 0.5rem; font-size: 0.875rem;">Defaults to ~120 days after planting</small>
				@error('harvest_date')
					<small style="color: #ef4444; display: block; margin-top: 0.25rem; font-size: 0.875rem;">{{ $message }}</small>
				@enderror
		</label>
    </fieldset>
		
    @if($weatherApiAvailable ?? false)
        <label tabindex="0" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: #e0f2fe; border-radius: calc(var(--radius) * 2); margin-bottom: 1.5rem; border: 2px solid #0284c7; cursor: pointer; transition: all 0.3s ease;" 
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow-card)'; this.style.borderColor='#0369a1'" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-soft)'; this.style.borderColor='#0284c7'"
               onclick="(function(e){ e.stopPropagation(); var cb=document.getElementById('use_weather_api_cb'); if(cb && e.target !== cb){ cb.checked = !cb.checked; cb.dispatchEvent(new Event('change',{bubbles:true})); } })(event)"
               onkeydown="(function(e){ if(e.key=== ' ' || e.key === 'Enter'){ e.preventDefault(); var cb=document.getElementById('use_weather_api_cb'); if(cb){ cb.checked = !cb.checked; cb.dispatchEvent(new Event('change',{bubbles:true})); } } })(event)">
            <input type="checkbox" name="use_weather_api" value="1" {{ old('use_weather_api', true) ? 'checked' : '' }} style="width: 1.5rem; height: 1.5rem; cursor: pointer; accent-color: #0284c7; position: relative; z-index: 30; pointer-events: auto;" id="use_weather_api_cb">
			<div style="flex: 1;">
				<span style="font-weight: 700; color: #0369a1; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
					<span style="font-size: 1.25rem;">🌤️</span>
					<span>Use Weather API</span>
				</span>
				<small style="color: #0c4a6e; display: block; margin-top: 0.5rem; font-size: 0.9rem;">Improves accuracy for near-term predictions</small>
			</div>
            </div>
            <button type="button" aria-controls="use_weather_api_cb" aria-pressed="false" title="Toggle Weather API" 
                style="background: transparent; border: none; color: #0369a1; font-weight: 700; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 6px; position: relative; z-index: 40;"
                onclick="(function(e){ e.stopPropagation(); var cb=document.getElementById('use_weather_api_cb'); if(cb){ cb.checked = !cb.checked; cb.dispatchEvent(new Event('change',{bubbles:true})); e.currentTarget.setAttribute('aria-pressed', cb.checked ? 'true' : 'false'); } })(event)">
                Toggle
            </button>
        </label>
    @endif
		
		<label style="margin-bottom: 1.5rem;">
			<span style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; color: var(--foreground);">
				<span style="font-size: 1.5rem;">🤖</span>
				<span style="font-weight: 700; font-size: 1.05rem;">AI Model</span>
			</span>
			<select name="model_type" 
					style="font-size: 1rem; padding: 1rem; border-radius: var(--radius); border: 2px solid var(--border); background: var(--card); transition: all 0.3s ease; width: 100%; cursor: pointer;"
					onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'" 
					onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            <option value="all" {{ old('model_type', $modelType ?? 'all') === 'all' ? 'selected' : '' }}>All Models (Compare All 3)</option>
            <option value="neural" {{ old('model_type', $modelType ?? 'all') === 'neural' ? 'selected' : '' }}>Neural Network (Advanced) - Most Accurate</option>
            <option value="decision_tree" {{ old('model_type', $modelType ?? 'all') === 'decision_tree' ? 'selected' : '' }}>Decision Tree (Medium) - Interpretable</option>
            <option value="linear" {{ old('model_type', $modelType ?? 'all') === 'linear' ? 'selected' : '' }}>Linear Regression (Simple) - Fastest</option>
        </select>
    </label>
		
		<button :aria-busy="loading" :disabled="loading" type="submit" class="btn-brand" 
				style="width: 100%; padding: 1.25rem; font-size: 1.15rem; font-weight: 700; margin-top: 1rem; border-radius: var(--radius); letter-spacing: 0.5px;"
				:style="loading ? 'opacity: 0.7; cursor: not-allowed;' : ''">
			<span x-show="!loading">🚀 Predict Yield</span>
			<span x-show="loading">⏳ AI is analyzing...</span>
		</button>
</form>

@isset($error)
<div class="card animate-fade-in" style="margin-top: 2rem; background: hsl(0, 84%, 97%); border: 2px solid hsl(0, 84%, 60%); border-left: 4px solid hsl(0, 84%, 60%);">
    <article style="background: transparent; padding: 0; margin: 0;">
        <p style="margin: 0; color: hsl(0, 84%, 40%); font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.25rem;">⚠️</span>
            <span>{{ $error }}</span>
        </p>
    </article>
</div>
@endisset

@isset($predictions)
<div class="card animate-scale-in" style="max-width: 1000px; margin: 3rem auto 0; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12); border: none;">
    <h3 style="margin-top:0; text-align: center; color: var(--foreground); font-size: 2rem; font-weight: 800; margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
        <span style="font-size: 2rem;">📊</span>
        <span>Prediction Results</span>
    </h3>
    
    <!-- Prediction Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    @if(isset($predictions['neural']))
        <div class="prediction-card neural animate-fade-in" style="animation-delay: 0.1s;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🤖</div>
                <div>
                    <h4 style="margin: 0; color: #1e40af; font-size: 1.1rem; font-weight: 700;">Neural Network</h4>
                    <small style="color: var(--muted-foreground); font-size: 0.85rem;">Advanced AI</small>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-bottom: 0.5rem;">Predicted Yield</div>
                <div style="font-size: 2.5rem; font-weight: 800; color: #1e40af; line-height: 1;">
                    {{ number_format($predictions['neural'], 2) }}
                    <span style="font-size: 1.25rem; font-weight: 500; color: var(--muted-foreground);">t/ha</span>
                </div>
            </div>
            <p style="margin-top: 1rem; margin-bottom: 0; color: var(--muted-foreground); font-size: 0.875rem; line-height: 1.5;">Multi-layer perceptron with backpropagation - Most accurate, learns complex patterns</p>
    </div>
    @endif
    
    @if(isset($predictions['decision_tree']))
        <div class="prediction-card tree animate-fade-in" style="animation-delay: 0.2s;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--emerald) 0%, var(--emerald-dark) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🌳</div>
                <div>
                    <h4 style="margin: 0; color: var(--emerald-dark); font-size: 1.1rem; font-weight: 700;">Decision Tree</h4>
                    <small style="color: var(--muted-foreground); font-size: 0.85rem;">Medium AI</small>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-bottom: 0.5rem;">Predicted Yield</div>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--emerald-dark); line-height: 1;">
                    {{ number_format($predictions['decision_tree'], 2) }}
                    <span style="font-size: 1.25rem; font-weight: 500; color: var(--muted-foreground);">t/ha</span>
                </div>
            </div>
            <p style="margin-top: 1rem; margin-bottom: 0; color: var(--muted-foreground); font-size: 0.875rem; line-height: 1.5;">CART algorithm - Interpretable, handles non-linear relationships</p>
    </div>
    @endif
    
    @if(isset($predictions['linear']))
        <div class="prediction-card linear animate-fade-in" style="animation-delay: 0.3s;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📊</div>
                <div>
                    <h4 style="margin: 0; color: #92400e; font-size: 1.1rem; font-weight: 700;">Linear Regression</h4>
                    <small style="color: var(--muted-foreground); font-size: 0.85rem;">Simple</small>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-bottom: 0.5rem;">Predicted Yield</div>
                <div style="font-size: 2.5rem; font-weight: 800; color: #92400e; line-height: 1;">
                    {{ number_format($predictions['linear'], 2) }}
                    <span style="font-size: 1.25rem; font-weight: 500; color: var(--muted-foreground);">t/ha</span>
                </div>
            </div>
            <p style="margin-top: 1rem; margin-bottom: 0; color: var(--muted-foreground); font-size: 0.875rem; line-height: 1.5;">Ridge regression - Fastest, simplest statistical model</p>
        </div>
        @endif
    </div>
    
    @if(count($predictions) > 1)
    <div style="margin-top: 2rem; padding: 1.5rem; background: var(--gradient-card); border-radius: calc(var(--radius) * 2); border: 1px solid var(--border);">
        <h4 style="margin-top: 0; margin-bottom: 1rem; color: var(--foreground); font-weight: 700; font-size: 1.25rem;">Model Comparison</h4>
        <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.75rem;">
            @if(isset($predictions['neural']) && isset($predictions['linear']))
            <li style="padding: 0.75rem; background: white; border-radius: 8px; border-left: 3px solid #3b82f6;">
                <span style="color: var(--muted-foreground);">Neural Network vs Linear:</span>
                <strong style="color: var(--foreground); margin-left: 0.5rem;">{{ number_format(abs($predictions['neural'] - $predictions['linear']), 3) }} t/ha</strong>
                <span style="color: var(--muted-foreground); margin-left: 0.5rem;">difference</span>
            </li>
            @endif
            @if(isset($predictions['decision_tree']) && isset($predictions['linear']))
            <li style="padding: 0.75rem; background: white; border-radius: 8px; border-left: 3px solid var(--emerald);">
                <span style="color: var(--muted-foreground);">Decision Tree vs Linear:</span>
                <strong style="color: var(--foreground); margin-left: 0.5rem;">{{ number_format(abs($predictions['decision_tree'] - $predictions['linear']), 3) }} t/ha</strong>
                <span style="color: var(--muted-foreground); margin-left: 0.5rem;">difference</span>
            </li>
            @endif
            @if(isset($predictions['neural']) && isset($predictions['decision_tree']))
            <li style="padding: 0.75rem; background: white; border-radius: 8px; border-left: 3px solid #0ea5e9;">
                <span style="color: var(--muted-foreground);">Neural Network vs Decision Tree:</span>
                <strong style="color: var(--foreground); margin-left: 0.5rem;">{{ number_format(abs($predictions['neural'] - $predictions['decision_tree']), 3) }} t/ha</strong>
                <span style="color: var(--muted-foreground); margin-left: 0.5rem;">difference</span>
            </li>
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
    
    <div class="card animate-fade-in" style="margin-top: 2.5rem; background: var(--gradient-card); border: 1px solid var(--border); animation-delay: 0.4s;">
        <h4 style="margin-top: 0; text-align: center; color: var(--foreground); font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem;">📈 Prediction vs Historical Data</h4>
        <p style="text-align: center; color: var(--muted-foreground); margin-bottom: 2rem; font-size: 1rem;">See how your prediction compares to historical yields</p>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <!-- Historical Statistics -->
            <div style="background: var(--card); padding: 2rem; border-radius: calc(var(--radius) * 2); margin-bottom: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-soft);">
                <h5 style="margin-top: 0; color: var(--foreground); margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.5rem;">📊</span>
                    <span>Historical Data Summary</span>
                </h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.5rem;">
                    <div style="text-align: center; padding: 1rem; background: var(--secondary-bg); border-radius: var(--radius); border: 2px solid var(--secondary);">
                        <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Average Yield</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--secondary-dark); line-height: 1;">
                            {{ number_format($avgHist, 2) }}
                            <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">t/ha</span>
                        </div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: var(--muted); border-radius: var(--radius); border: 2px solid var(--border);">
                        <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Minimum</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--foreground); line-height: 1;">
                            {{ number_format($minHist, 2) }}
                            <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">t/ha</span>
                        </div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: var(--muted); border-radius: var(--radius); border: 2px solid var(--border);">
                        <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Maximum</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--foreground); line-height: 1;">
                            {{ number_format($maxHist, 2) }}
                            <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">t/ha</span>
                        </div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: var(--primary-bg); border-radius: var(--radius); border: 2px solid var(--primary);">
                        <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Records</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--primary-dark); line-height: 1;">{{ $histYields->count() }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Visual Comparison Bar Chart -->
            <div style="background: var(--card); padding: 2rem; border-radius: calc(var(--radius) * 2); border: 1px solid var(--border); box-shadow: var(--shadow-soft);">
                <h5 style="margin-top: 0; color: var(--foreground); margin-bottom: 2rem; font-size: 1.25rem; font-weight: 700;">📊 Visual Comparison</h5>
                
                @php
                    $range = max($maxAll - $minAll, 0.1);
                @endphp
                
                @if(isset($predictions['neural']))
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 140px; font-weight: 700; color: #1e40af; font-size: 1rem;">Neural Network:</div>
                        <div style="flex: 1; position: relative;">
                            <div class="comparison-bar">
                                <div class="comparison-bar-fill" style="background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%); width: {{ min(100, max(5, (($predictions['neural'] - $minAll) / $range) * 100)) }}%;">
                                    <span>{{ number_format($predictions['neural'], 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--muted-foreground); margin-top: 0.5rem; padding: 0 0.5rem;">
                        <span>{{ number_format($minAll, 2) }} t/ha</span>
                        <span>{{ number_format($maxAll, 2) }} t/ha</span>
                    </div>
                </div>
                @endif
                
                @if(isset($predictions['decision_tree']))
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 140px; font-weight: 700; color: var(--emerald-dark); font-size: 1rem;">Decision Tree:</div>
                        <div style="flex: 1; position: relative;">
                            <div class="comparison-bar">
                                <div class="comparison-bar-fill" style="background: linear-gradient(90deg, var(--emerald) 0%, var(--emerald-dark) 100%); width: {{ min(100, max(5, (($predictions['decision_tree'] - $minAll) / $range) * 100)) }}%;">
                                    <span>{{ number_format($predictions['decision_tree'], 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--muted-foreground); margin-top: 0.5rem; padding: 0 0.5rem;">
                        <span>{{ number_format($minAll, 2) }} t/ha</span>
                        <span>{{ number_format($maxAll, 2) }} t/ha</span>
                    </div>
                </div>
                @endif
                
                @if(isset($predictions['linear']))
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 140px; font-weight: 700; color: #92400e; font-size: 1rem;">Linear Regression:</div>
                        <div style="flex: 1; position: relative;">
                            <div class="comparison-bar">
                                <div class="comparison-bar-fill" style="background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); width: {{ min(100, max(5, (($predictions['linear'] - $minAll) / $range) * 100)) }}%;">
                                    <span>{{ number_format($predictions['linear'], 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--muted-foreground); margin-top: 0.5rem; padding: 0 0.5rem;">
                        <span>{{ number_format($minAll, 2) }} t/ha</span>
                        <span>{{ number_format($maxAll, 2) }} t/ha</span>
                    </div>
                </div>
                @endif
                
                <!-- Historical Average Reference -->
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px dashed #cbd5e1;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 140px; font-weight: 700; color: #475569; font-size: 1rem;">Historical Avg:</div>
                        <div style="flex: 1; position: relative;">
                            <div class="comparison-bar">
                                <div class="comparison-bar-fill" style="background: linear-gradient(90deg, hsl(150, 10%, 55%) 0%, hsl(150, 10%, 45%) 100%); width: {{ min(100, max(5, (($avgHist - $minAll) / $range) * 100)) }}%;">
                                    <span>{{ number_format($avgHist, 2) }} t/ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comparison Summary -->
            <div style="background: var(--card); padding: 2rem; border-radius: calc(var(--radius) * 2); margin-top: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-soft);">
                <h5 style="margin-top: 0; color: var(--foreground); margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.5rem;">💡</span>
                    <span>Insights</span>
                </h5>
                <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 1rem;">
                    @if(isset($predictions['neural']))
                    <li style="padding: 1rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; border-left: 4px solid #3b82f6;">
                        <strong style="color: #1e40af; font-size: 1.05rem;">Neural Network:</strong>
                        <span style="color: var(--muted-foreground); margin-left: 0.5rem;">
                            @if($predictions['neural'] > $avgHist)
                                {{ number_format((($predictions['neural'] - $avgHist) / $avgHist) * 100, 1) }}% above historical average
                            @else
                                {{ number_format((($avgHist - $predictions['neural']) / $avgHist) * 100, 1) }}% below historical average
                            @endif
                        </span>
                    </li>
                    @endif
                    @if(isset($predictions['decision_tree']))
                    <li style="padding: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; border-left: 4px solid var(--emerald);">
                        <strong style="color: var(--emerald-dark); font-size: 1.05rem;">Decision Tree:</strong>
                        <span style="color: var(--muted-foreground); margin-left: 0.5rem;">
                            @if($predictions['decision_tree'] > $avgHist)
                                {{ number_format((($predictions['decision_tree'] - $avgHist) / $avgHist) * 100, 1) }}% above historical average
                            @else
                                {{ number_format((($avgHist - $predictions['decision_tree']) / $avgHist) * 100, 1) }}% below historical average
                            @endif
                        </span>
                    </li>
                    @endif
                    @if(isset($predictions['linear']))
                    <li style="padding: 1rem; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; border-left: 4px solid #f59e0b;">
                        <strong style="color: #92400e; font-size: 1.05rem;">Linear Regression:</strong>
                        <span style="color: var(--muted-foreground); margin-left: 0.5rem;">
                            @if($predictions['linear'] > $avgHist)
                                {{ number_format((($predictions['linear'] - $avgHist) / $avgHist) * 100, 1) }}% above historical average
                            @else
                                {{ number_format((($avgHist - $predictions['linear']) / $avgHist) * 100, 1) }}% below historical average
                            @endif
                        </span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
    
    <details style="margin-top: 2rem;" x-data="{ open: false }" @toggle="open = $event.target.open">
        <summary style="cursor: pointer; padding: 1.5rem; background: var(--gradient-card); border-radius: calc(var(--radius) * 2); border: 1px solid var(--border); font-weight: 600; font-size: 1.1rem; color: var(--foreground); list-style: none; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease; user-select: none;" 
                onmouseover="this.style.background='var(--muted)'; this.style.borderColor='var(--primary)'"
                onmouseout="this.style.background='var(--gradient-card)'; this.style.borderColor='var(--border)'">
            <span>📋 Estimated Parameters (Auto-calculated from Location & Date)</span>
            <span x-show="!open" style="font-size: 1.25rem; color: var(--muted-foreground); transition: transform 0.3s ease;">▼</span>
            <span x-show="open" style="font-size: 1.25rem; color: var(--primary); transition: transform 0.3s ease;">▲</span>
        </summary>
        <div style="background: var(--card); padding: 2rem; border-radius: calc(var(--radius) * 2); margin-top: 1rem; border: 1px solid var(--border); box-shadow: var(--shadow-soft);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div style="padding: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; border-left: 4px solid var(--emerald);">
                    <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">📍 Location</div>
                    <div style="font-weight: 700; color: var(--emerald-dark);">{{ $focusLocation ?? 'Palo, Leyte' }} <span style="font-weight: 400; color: #64748b; font-size: 0.9rem;">(fixed)</span></div>
                </div>
                <div style="padding: 1rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; border-left: 4px solid #0ea5e9;">
                    <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">🌱 Planting Date</div>
                    <div style="font-weight: 700; color: #0c4a6e;">{{ isset($input['planting_date']) ? \Carbon\Carbon::parse($input['planting_date'])->format('F d, Y') : '-' }}</div>
                </div>
                <div style="padding: 1rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; border-left: 4px solid #0ea5e9;">
                    <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">✂️ Harvest Date</div>
                    <div style="font-weight: 700; color: #0c4a6e;">{{ isset($estimatedFeatures['harvestDate']) ? \Carbon\Carbon::parse($estimatedFeatures['harvestDate'])->format('F d, Y') : '-' }}</div>
                    <div style="color: #64748b; font-size: 0.8rem; margin-top: 0.25rem;">({{ $estimatedFeatures['growingDays'] ?? 120 }} days growing period)</div>
                </div>
                <div style="padding: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; border-left: 4px solid var(--emerald);">
                    <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">🌾 Season</div>
                    <div style="font-weight: 700; color: var(--emerald-dark);">{{ $estimatedFeatures['season'] ?? '-' }}</div>
                </div>
            </div>
            
            <hr style="border: none; border-top: 2px solid #e5e7eb; margin: 1.5rem 0;" />
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
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
                <div style="padding: 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e5e7eb;">
                    <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">💧 Rainfall</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--foreground);">{{ number_format($estimatedFeatures['rainfall_mm'] ?? 0, 1) }} <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">mm</span></div>
                </div>
                <div style="padding: 1rem; background: var(--muted); border-radius: var(--radius); border: 1px solid var(--border);">
                    <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">🌡️ Temperature</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--foreground);">{{ number_format($estimatedFeatures['temperature_c'] ?? 0, 1) }} <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">°C</span></div>
                </div>
                <div style="padding: 1rem; background: var(--muted); border-radius: var(--radius); border: 1px solid var(--border);">
                    <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">🧪 Soil pH</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--foreground);">{{ number_format($estimatedFeatures['soil_ph'] ?? 0, 2) }}</div>
                </div>
                <div style="padding: 1rem; background: var(--muted); border-radius: var(--radius); border: 1px solid var(--border);">
                    <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">🌾 Fertilizer</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--foreground);">{{ number_format($estimatedFeatures['fertilizer_kg'] ?? 0, 1) }} <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">kg</span></div>
                </div>
                <div style="padding: 1rem; background: var(--muted); border-radius: var(--radius); border: 1px solid var(--border);">
                    <div style="color: var(--muted-foreground); font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">📏 Area</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--foreground);">{{ number_format($estimatedFeatures['area_ha'] ?? 0, 2) }} <span style="font-size: 1rem; font-weight: 500; color: var(--muted-foreground);">ha</span></div>
                </div>
            </div>
            
            @if(isset($estimatedFeatures['weatherApiUsed']) && $estimatedFeatures['weatherApiUsed'])
            <div style="margin-top: 1.5rem; padding: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; border-left: 4px solid var(--emerald);">
                <p style="margin: 0; color: var(--emerald-dark); font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🌤️</span>
                    <span>Weather API data used for forecast</span>
                </p>
            </div>
            @endif
            
            <div style="margin-top: 1.5rem; padding: 1rem; background: var(--muted); border-radius: var(--radius);">
                <small style="color: var(--muted-foreground); font-size: 0.875rem; line-height: 1.6;">{{ $estimatedFeatures['dataSource'] ?? 'These values are based on historical averages.' }}</small>
            </div>
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
<script>
// Ensure native date pickers open when supported and make checkbox label clicks reliable
document.addEventListener('DOMContentLoaded', function () {
    try {
        // Attach showPicker() to date inputs when available
        document.querySelectorAll('input[type="date"]').forEach(function (el) {
            // Make extra-sure the input can receive pointer events
            el.style.pointerEvents = 'auto';
            el.style.position = el.style.position || 'relative';
            el.style.zIndex = el.style.zIndex || '20';
            if (typeof el.showPicker === 'function') {
                el.addEventListener('click', function (e) {
                    // Some browsers require showPicker to be invoked
                    try { el.showPicker(); } catch (err) { /* ignore */ }
                });
            }
        });

        // Make label click toggle checkbox in case label click is intercepted
        var cb = document.getElementById('use_weather_api_cb');
        if (cb) {
            var parentLabel = cb.closest('label');
            if (parentLabel) {
                parentLabel.addEventListener('click', function (e) {
                    // If click didn't land on the checkbox itself, toggle it
                    if (e.target !== cb) {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }
        }
    } catch (err) {
        // silent fallback
        console.error('Forecast input helper error', err);
    }
});
</script>
@endsection


