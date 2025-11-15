@extends('layouts.app')

@section('content')
<h2>Model Evaluation</h2>

@isset($message)
<article>{{ $message }}</article>
@else
<div class="card">
	<p><strong>Test size:</strong> {{ $testSize }}</p>
	<p><strong>MAE (t/ha):</strong> {{ number_format($mae, 3) }}</p>
	<p><strong>RMSE (t/ha):</strong> {{ number_format($rmse, 3) }}</p>
</div>
@endisset

@endsection


