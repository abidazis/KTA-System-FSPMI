<?php

namespace Database\Factories;

use App\Models\ManagementOfficial;
use App\Models\ManagementPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManagementOfficialFactory extends Factory
{
    protected $model = ManagementOfficial::class;

    public function definition(): array
    {
        return [
            'management_period_id' => ManagementPeriod::factory(),
            'jabatan' => $this->faker->randomElement(['Ketua Umum', 'Sekretaris Umum', 'Bendahara Umum']),
            'nama' => $this->faker->name(),
            'signature_path' => null,
            'status' => 'active',
        ];
    }
}
