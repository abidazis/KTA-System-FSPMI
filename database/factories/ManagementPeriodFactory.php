<?php

namespace Database\Factories;

use App\Models\ManagementPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManagementPeriodFactory extends Factory
{
    protected $model = ManagementPeriod::class;

    public function definition(): array
    {
        $startYear = $this->faker->numberBetween(2020, 2025);
        return [
            'nama_periode' => $startYear . ' - ' . ($startYear + 5),
            'tanggal_mulai' => $startYear . '-01-01',
            'tanggal_selesai' => ($startYear + 5) . '-12-31',
            'status' => 'inactive',
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }
}
