<?php

namespace Database\Factories;

use App\Models\Record;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Record>
 */
class RecordFactory extends Factory
{
	protected $model = Record::class;

	public function definition(): array
	{
		$area = $this->faker->randomFloat(2, 0.5, 5.0);
		$rain = $this->faker->randomFloat(2, 50, 400);
		$temp = $this->faker->randomFloat(2, 20, 35);
		$ph = $this->faker->randomFloat(2, 4.5, 8.0);
		$fert = $this->faker->randomFloat(2, 50, 300);
		// Simple synthetic yield
		$yield = max(1.5, min(8.0, 0.01 * $rain + 0.2 * ($temp - 25) * -1 + 0.3 * ($ph - 6.5) * -1 + 0.005 * $fert + 2.5));

		return [
			'location' => $this->faker->city(),
			'season' => $this->faker->randomElement(['Wet', 'Dry']),
			'area_ha' => $area,
			'rainfall_mm' => $rain,
			'temperature_c' => $temp,
			'soil_ph' => $ph,
			'fertilizer_kg' => $fert,
			'yield_t_ha' => $yield,
		];
	}
}


