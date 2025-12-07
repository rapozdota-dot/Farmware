@extends('layouts.app')

@section('content')
<div class="fade-enter fade-enter-active animate-slide-up">
	<div style="margin-bottom: 2rem;">
		<h1 class="text-gradient-hero" style="font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 800;">➕ New Record</h1>
		<p style="color: var(--muted-foreground); font-size: 1.1rem;">Add a new rice yield record to the database</p>
	</div>
	
	<div class="card animate-fade-in stagger-1" style="max-width: 800px; margin: 0 auto; background: var(--gradient-card);">
		<form action="{{ route('records.store') }}" method="POST">
			@csrf
			@include('records.form')
			<div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
				<a href="{{ route('records.index') }}" role="button" class="secondary" style="padding: 1rem 2rem; border-radius: var(--radius); font-weight: 600; text-decoration: none;">
					Cancel
				</a>
				<button type="submit" class="btn-brand" style="padding: 1rem 2rem; border-radius: var(--radius); font-weight: 700; font-size: 1.05rem;">
					💾 Save Record
				</button>
			</div>
		</form>
	</div>
</div>
@endsection


