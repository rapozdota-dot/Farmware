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
		.card { border:1px solid #e5e7eb; border-radius:12px; padding:1.5rem; background:#fff; }
		.table-actions a, .table-actions button { margin-right:.25rem; }
		.fade-enter { opacity:0; transform:translateY(4px); }
		.fade-enter-active { opacity:1; transform:translateY(0); transition:all .18s ease; }
		nav { margin-bottom: 2rem; }
		nav ul { margin: 0; }
		main.container { max-width: 1200px; }
		@media (max-width: 768px) {
			fieldset[role="group"], .forecast-dates { grid-template-columns: 1fr !important; }
		}
	</style>
</head>
<body>
	<main class="container">
		<nav>
			<ul>
				<li><strong><a href="{{ route('home') }}" style="text-decoration: none;">Rice Forecast</a></strong></li>
			</ul>
			<ul>
				<li><a href="{{ route('home') }}">Forecast</a></li>
				<li><a href="{{ route('records.index') }}">Records</a></li>
				<li><a href="{{ route('records.data') }}">Data</a></li>
			</ul>
		</nav>
		@if(session('status'))
			<article>{{ session('status') }}</article>
		@endif
		@yield('content')
	</main>
</body>
</html>


