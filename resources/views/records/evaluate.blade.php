@extends('layouts.app')

@section('content')
<h2>AI Model Evaluation & Comparison</h2>

@isset($message)
<article>{{ $message }}</article>
@else
<div class="card">
	<p><strong>Test Dataset Size:</strong> {{ $testSize }} records</p>
	<p><strong>Training Dataset Size:</strong> {{ $testSize * 4 }} records (80/20 split)</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-top: 1rem;">
	<!-- Linear Regression (Simplest) -->
	@isset($linearRegression)
	<div class="card" style="border-left: 4px solid #f59e0b;">
		<h3 style="margin-top: 0; color: #92400e;">📊 Linear Regression</h3>
		<p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0;"><strong>Complexity:</strong> Simple</p>
		<p><strong>Type:</strong> Ridge Regression (L2 regularization)</p>
		<p><strong>Method:</strong> Ordinary Least Squares</p>
		<hr>
		<p><strong>Mean Absolute Error (MAE):</strong> <span style="font-size: 1.2em; color: #92400e;">{{ number_format($linearRegression['mae'], 3) }}</span> t/ha</p>
		<p><strong>Root Mean Squared Error (RMSE):</strong> <span style="font-size: 1.2em; color: #92400e;">{{ number_format($linearRegression['rmse'], 3) }}</span> t/ha</p>
		<small style="color: #64748b;">Fastest, simplest model. Good baseline.</small>
	</div>
	@endisset

	<!-- Decision Tree (Medium) -->
	@isset($decisionTree)
	<div class="card" style="border-left: 4px solid #10b981;">
		<h3 style="margin-top: 0; color: #065f46;">🌳 Decision Tree</h3>
		<p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0;"><strong>Complexity:</strong> Medium</p>
		<p><strong>Algorithm:</strong> CART (Classification and Regression Trees)</p>
		<p><strong>Max Depth:</strong> 5 levels</p>
		<hr>
		<p><strong>Mean Absolute Error (MAE):</strong> <span style="font-size: 1.2em; color: #065f46;">{{ number_format($decisionTree['mae'], 3) }}</span> t/ha</p>
		<p><strong>Root Mean Squared Error (RMSE):</strong> <span style="font-size: 1.2em; color: #065f46;">{{ number_format($decisionTree['rmse'], 3) }}</span> t/ha</p>
		<small style="color: #64748b;">Interpretable, handles non-linear relationships.</small>
	</div>
	@endisset

	<!-- Neural Network (Most Advanced) -->
	@isset($neuralNetwork)
	<div class="card" style="border-left: 4px solid #3b82f6;">
		<h3 style="margin-top: 0; color: #1e40af;">🤖 Neural Network</h3>
		<p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0;"><strong>Complexity:</strong> Advanced</p>
		<p><strong>Architecture:</strong> Multi-Layer Perceptron (5 → 10 → 8 → 1)</p>
		<p><strong>Training:</strong> Backpropagation with 500 epochs</p>
		<hr>
		<p><strong>Mean Absolute Error (MAE):</strong> <span style="font-size: 1.2em; color: #1e40af;">{{ number_format($neuralNetwork['mae'], 3) }}</span> t/ha</p>
		<p><strong>Root Mean Squared Error (RMSE):</strong> <span style="font-size: 1.2em; color: #1e40af;">{{ number_format($neuralNetwork['rmse'], 3) }}</span> t/ha</p>
		<small style="color: #64748b;">Most accurate, learns complex non-linear patterns.</small>
	</div>
	@endisset
</div>

@if(isset($linearRegression) && isset($decisionTree) && isset($neuralNetwork))
<div class="card" style="margin-top: 1rem; background: #f3f4f6;">
	<h3 style="margin-top: 0;">📈 Model Comparison (All 3 AI Models)</h3>
	
	@php
		// Find best model for each metric
		$models = [
			'Linear Regression' => ['mae' => $linearRegression['mae'], 'rmse' => $linearRegression['rmse']],
			'Decision Tree' => ['mae' => $decisionTree['mae'], 'rmse' => $decisionTree['rmse']],
			'Neural Network' => ['mae' => $neuralNetwork['mae'], 'rmse' => $neuralNetwork['rmse']],
		];
		
		$bestMae = min(array_column($models, 'mae'));
		$bestRmse = min(array_column($models, 'rmse'));
		
		$bestMaeModel = '';
		$bestRmseModel = '';
		
		foreach ($models as $name => $metrics) {
			if ($metrics['mae'] == $bestMae) {
				$bestMaeModel = $name;
			}
			if ($metrics['rmse'] == $bestRmse) {
				$bestRmseModel = $name;
			}
		}
	@endphp
	
	<p><strong>Best Model for MAE:</strong> <span style="color: #059669; font-weight: bold;">{{ $bestMaeModel }}</span> ({{ number_format($bestMae, 3) }} t/ha)</p>
	<p><strong>Best Model for RMSE:</strong> <span style="color: #059669; font-weight: bold;">{{ $bestRmseModel }}</span> ({{ number_format($bestRmse, 3) }} t/ha)</p>
	
	<hr style="margin: 1rem 0;">
	
	<h4 style="margin-top: 0;">Performance Ranking:</h4>
	<ol style="padding-left: 1.5rem;">
		@php
			$sortedByMae = collect($models)->sortBy('mae')->keys();
		@endphp
		@foreach($sortedByMae as $modelName)
		<li><strong>{{ $modelName }}</strong>: MAE = {{ number_format($models[$modelName]['mae'], 3) }}, RMSE = {{ number_format($models[$modelName]['rmse'], 3) }}</li>
		@endforeach
	</ol>
	
	<p style="margin-top: 1rem; color: #64748b; font-size: 0.875rem;">
		<strong>Note:</strong> Lower values are better. The model with the lowest MAE and RMSE is most accurate.
	</p>
</div>
@elseif(isset($neuralNetwork) && isset($linearRegression))
<div class="card" style="margin-top: 1rem; background: #f3f4f6;">
	<h3 style="margin-top: 0;">📈 Model Comparison</h3>
	@php
		$nnBetterMae = $neuralNetwork['mae'] < $linearRegression['mae'];
		$nnBetterRmse = $neuralNetwork['rmse'] < $linearRegression['rmse'];
		$maeImprovement = abs($neuralNetwork['mae'] - $linearRegression['mae']);
		$rmseImprovement = abs($neuralNetwork['rmse'] - $linearRegression['rmse']);
	@endphp
	
	@if($nnBetterMae && $nnBetterRmse)
		<p style="color: #059669; font-weight: bold;">✅ Neural Network performs better on both metrics!</p>
	@elseif(!$nnBetterMae && !$nnBetterRmse)
		<p style="color: #f59e0b; font-weight: bold;">📊 Linear Regression performs better on both metrics.</p>
	@else
		<p style="color: #6366f1; font-weight: bold;">⚖️ Mixed results - models perform differently on different metrics.</p>
	@endif
	
	<p><strong>MAE Difference:</strong> {{ number_format($maeImprovement, 3) }} t/ha 
		@if($nnBetterMae)
			<span style="color: #059669;">(Neural Network is better by {{ number_format(($maeImprovement / $linearRegression['mae']) * 100, 1) }}%)</span>
		@else
			<span style="color: #f59e0b;">(Linear Regression is better by {{ number_format(($maeImprovement / $neuralNetwork['mae']) * 100, 1) }}%)</span>
		@endif
	</p>
	
	<p><strong>RMSE Difference:</strong> {{ number_format($rmseImprovement, 3) }} t/ha
		@if($nnBetterRmse)
			<span style="color: #059669;">(Neural Network is better by {{ number_format(($rmseImprovement / $linearRegression['rmse']) * 100, 1) }}%)</span>
		@else
			<span style="color: #f59e0b;">(Linear Regression is better by {{ number_format(($rmseImprovement / $neuralNetwork['rmse']) * 100, 1) }}%)</span>
		@endif
	</p>
</div>
@endif

@endisset

@endsection


