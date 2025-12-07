<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rice Yield Forecasting</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
	<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<style>
		@import url('https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap');
		
		:root {
			--font-heading: 'Fraunces', serif;
			--font-body: 'DM Sans', sans-serif;
			
			/* Agriculture Theme Colors */
			--primary: hsl(152, 60%, 36%);
			--primary-dark: hsl(152, 60%, 30%);
			--primary-light: hsl(152, 60%, 45%);
			--primary-bg: hsl(152, 76%, 96%);
			
			--secondary: hsl(199, 89%, 48%);
			--secondary-dark: hsl(199, 89%, 40%);
			--secondary-light: hsl(199, 89%, 55%);
			--secondary-bg: hsl(199, 100%, 97%);
			
			--accent: hsl(38, 92%, 50%);
			--accent-dark: hsl(38, 92%, 45%);
			
			--neural: hsl(217, 91%, 60%);
			--neural-dark: hsl(217, 91%, 50%);
			--neural-bg: hsl(214, 100%, 97%);
			
			--tree: hsl(160, 84%, 39%);
			--tree-dark: hsl(160, 84%, 32%);
			--tree-bg: hsl(152, 76%, 96%);
			
			--linear: hsl(38, 92%, 50%);
			--linear-dark: hsl(38, 92%, 45%);
			--linear-bg: hsl(45, 93%, 96%);
			
			--background: hsl(140, 20%, 98%);
			--foreground: hsl(150, 30%, 10%);
			--card: hsl(0, 0%, 100%);
			--muted: hsl(150, 15%, 94%);
			--muted-foreground: hsl(150, 10%, 45%);
			--border: hsl(150, 15%, 88%);
			
			--radius: 0.75rem;
			--shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
			--shadow-card: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 4px 10px -5px rgba(0, 0, 0, 0.04);
			--shadow-glow: 0 0 40px rgba(34, 197, 94, 0.15);
			
			--gradient-hero: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
			--gradient-card: linear-gradient(180deg, var(--card) 0%, var(--background) 100%);
			--gradient-neural: linear-gradient(90deg, var(--neural) 0%, var(--neural-dark) 100%);
			--gradient-tree: linear-gradient(90deg, var(--tree) 0%, var(--tree-dark) 100%);
			--gradient-linear: linear-gradient(90deg, var(--linear) 0%, var(--linear-dark) 100%);
		}
		
		* {
			box-sizing: border-box;
		}
		
		html {
			font-family: var(--font-body);
		}
		
		body {
			background: var(--background) !important;
			color: var(--foreground);
			font-family: var(--font-body);
			line-height: 1.6;
			margin: 0;
			padding: 0;
			min-height: 100vh;
		}
		
		h1, h2, h3, h4, h5, h6 {
			font-family: var(--font-heading);
			font-weight: 700;
			color: var(--foreground);
		}
		
		.text-gradient-hero {
			background: var(--gradient-hero);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
		}
		
		.bg-gradient-hero {
			background: var(--gradient-hero);
		}
		
		.bg-gradient-card {
			background: var(--gradient-card);
		}
		
		.shadow-card {
			box-shadow: var(--shadow-card);
		}
		
		.shadow-glow {
			box-shadow: var(--shadow-glow);
		}
		
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		
		@keyframes slideUp {
			from { 
				opacity: 0;
				transform: translateY(20px);
			}
			to { 
				opacity: 1;
				transform: translateY(0);
			}
		}
		
		@keyframes scaleIn {
			from { 
				opacity: 0;
				transform: scale(0.95);
			}
			to { 
				opacity: 1;
				transform: scale(1);
			}
		}
		
		.animate-fade-in {
			animation: fadeIn 0.5s ease-out forwards;
		}
		
		.animate-slide-up {
			animation: slideUp 0.6s ease-out forwards;
		}
		
		.animate-scale-in {
			animation: scaleIn 0.4s ease-out forwards;
		}
		
		.stagger-1 { animation-delay: 0.1s; }
		.stagger-2 { animation-delay: 0.2s; }
		.stagger-3 { animation-delay: 0.3s; }
		.stagger-4 { animation-delay: 0.4s; }
		
		/* Override Pico CSS defaults */
		body {
			background-color: var(--background) !important;
		}
		
		main.container {
			background: transparent !important;
		}
		
		article {
			background: var(--card) !important;
			color: var(--foreground) !important;
		}
		
		/* Navigation */
		nav {
			background: var(--card) !important;
			border-radius: var(--radius);
			padding: 1rem 1.5rem;
			margin-bottom: 2rem;
			box-shadow: var(--shadow-soft);
			border: 1px solid var(--border);
		}
		
		nav ul {
			margin: 0;
		}
		
		nav a {
			color: var(--foreground) !important;
			text-decoration: none;
			transition: all 0.3s ease;
			padding: 0.5rem 1rem;
			border-radius: calc(var(--radius) / 2);
		}
		
		nav a:hover {
			background: var(--muted);
			color: var(--primary) !important;
			transform: translateY(-1px);
		}
		
		nav strong a {
			font-family: var(--font-heading);
			font-size: 1.25rem;
			background: var(--gradient-hero);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
		}
		
		/* Buttons */
		button, [role="button"], a[role="button"] {
			transition: all 0.3s ease;
			border-radius: var(--radius);
			font-weight: 600;
		}
		
		button:hover, [role="button"]:hover, a[role="button"]:hover {
			transform: translateY(-2px);
		}
		
		button:active, [role="button"]:active, a[role="button"]:active {
			transform: translateY(0);
		}
		
		.btn-brand { 
			background: var(--gradient-hero) !important;
			border: none !important;
			color: white !important;
			font-weight: 600;
			box-shadow: var(--shadow-soft);
		}
		
		.btn-brand:hover { 
			box-shadow: var(--shadow-card);
			transform: translateY(-2px);
		}
		
		button.secondary, [role="button"].secondary, a[role="button"].secondary {
			background: var(--muted) !important;
			color: var(--foreground) !important;
			border: 1px solid var(--border) !important;
		}
		
		button.secondary:hover, [role="button"].secondary:hover, a[role="button"].secondary:hover {
			background: var(--border) !important;
		}
		
		/* Cards */
		.card { 
			border: 1px solid var(--border); 
			border-radius: calc(var(--radius) * 2); 
			padding: 2rem; 
			background: var(--card);
			box-shadow: var(--shadow-soft);
			transition: all 0.3s ease;
		}
		
		.card:hover {
			box-shadow: var(--shadow-card);
			transform: translateY(-2px);
		}
		
		.prediction-card {
			background: var(--card);
			border-radius: calc(var(--radius) * 2);
			padding: 2rem;
			border: 2px solid transparent;
			transition: all 0.3s ease;
			box-shadow: var(--shadow-soft);
			cursor: pointer;
		}
		
		.prediction-card:hover {
			transform: translateY(-6px) scale(1.02);
			box-shadow: var(--shadow-card);
		}
		
		.prediction-card.neural {
			border-color: var(--neural);
			background: var(--gradient-card);
		}
		
		.prediction-card.neural:hover {
			border-color: var(--neural-dark);
			box-shadow: 0 0 30px rgba(59, 130, 246, 0.2);
		}
		
		.prediction-card.tree {
			border-color: var(--tree);
			background: var(--gradient-card);
		}
		
		.prediction-card.tree:hover {
			border-color: var(--tree-dark);
			box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
		}
		
		.prediction-card.linear {
			border-color: var(--linear);
			background: var(--gradient-card);
		}
		
		.prediction-card.linear:hover {
			border-color: var(--linear-dark);
			box-shadow: 0 0 30px rgba(245, 158, 11, 0.2);
		}
		
		/* Tables */
		table {
			background: var(--card) !important;
			border-radius: var(--radius);
			overflow: hidden;
			box-shadow: var(--shadow-soft);
		}
		
		table thead {
			background: var(--muted) !important;
		}
		
		table tbody tr {
			transition: all 0.2s ease;
		}
		
		table tbody tr:hover {
			background: var(--muted) !important;
			transform: scale(1.01);
		}
		
		/* Inputs */
		input, select, textarea {
			background: var(--card) !important;
			border: 2px solid var(--border) !important;
			border-radius: var(--radius) !important;
			color: var(--foreground) !important;
			transition: all 0.3s ease;
		}
		
		input:focus, select:focus, textarea:focus {
			border-color: var(--primary) !important;
			box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1) !important;
			outline: none;
		}
		
		/* Forms */
		fieldset {
			border: none;
			padding: 0;
		}
		
		label {
			color: var(--foreground);
		}
		
		/* Comparison bars */
		.comparison-bar {
			background: var(--muted);
			height: 48px;
			border-radius: var(--radius);
			position: relative;
			overflow: hidden;
			margin-bottom: 0.75rem;
			box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
		}
		
		.comparison-bar-fill {
			height: 100%;
			border-radius: var(--radius);
			display: flex;
			align-items: center;
			justify-content: flex-end;
			padding-right: 1.25rem;
			transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
			font-weight: 700;
			color: white;
			font-size: 0.95rem;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
		}
		
		/* Interactive elements */
		a, button, [role="button"] {
			cursor: pointer;
		}
		
		.table-actions a, .table-actions button { 
			margin-right: 0.5rem;
			padding: 0.5rem 1rem;
			border-radius: calc(var(--radius) / 2);
			transition: all 0.2s ease;
		}
		
		.table-actions a:hover, .table-actions button:hover {
			transform: translateY(-1px);
		}
		
		.fade-enter { 
			opacity: 0; 
			transform: translateY(8px); 
		}
		
		.fade-enter-active { 
			opacity: 1; 
			transform: translateY(0); 
			transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
		}
		
		main.container { 
			max-width: 1200px; 
			padding: 2rem 1rem;
		}
		
		/* Status messages */
		article[role="alert"], article {
			background: var(--card) !important;
			border-radius: var(--radius);
			padding: 1rem 1.5rem;
			border-left: 4px solid var(--primary);
			box-shadow: var(--shadow-soft);
		}
		
		/* Dialog */
		dialog article {
			background: var(--card) !important;
			border-radius: calc(var(--radius) * 2);
			box-shadow: var(--shadow-card);
		}
		
		@media (max-width: 768px) {
			fieldset[role="group"], .forecast-dates { 
				grid-template-columns: 1fr !important; 
			}
			main.container {
				padding: 1rem 0.5rem;
			}
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


