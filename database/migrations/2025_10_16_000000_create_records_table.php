<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('records', function (Blueprint $table) {
			$table->id();
			$table->string('location')->nullable();
			$table->string('season')->nullable();
			$table->decimal('area_ha', 8, 2)->nullable();
			$table->decimal('rainfall_mm', 8, 2)->nullable();
			$table->decimal('temperature_c', 5, 2)->nullable();
			$table->decimal('soil_ph', 4, 2)->nullable();
			$table->decimal('fertilizer_kg', 10, 2)->nullable();
			$table->decimal('yield_t_ha', 6, 3)->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('records');
	}
};


