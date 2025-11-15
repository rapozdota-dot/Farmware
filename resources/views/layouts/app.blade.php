<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rice Yield Forecasting</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
	<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<style>
		:root { --brand:#0ea5e9; --brand-600:#0284c7; }
		.btn-brand { background:var(--brand); border-color:var(--brand); }
		.btn-brand:hover { background:var(--brand-600); border-color:var(--brand-600); }
		.card { border:1px solid #e5e7eb; border-radius:8px; padding:1rem; background:#fff; }
		.table-actions a, .table-actions button { margin-right:.25rem; }
		.fade-enter { opacity:0; transform:translateY(4px); }
		.fade-enter-active { opacity:1; transform:translateY(0); transition:all .18s ease; }
	</style>
</head>
<body>
	<main class="container">
		<nav>
			<ul>
				<li><strong>Rice Forecast</strong></li>
			</ul>
			<ul>
				<li><a href="{{ route('records.index') }}">Records</a></li>
				<li><a href="{{ route('records.forecast.form') }}">Forecast</a></li>
			</ul>
		</nav>
		@if(session('status'))
			<article>{{ session('status') }}</article>
		@endif
		@yield('content')
	</main>
</body>
</html>


