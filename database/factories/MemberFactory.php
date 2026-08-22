<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('################'),
            'nama' => $this->faker->name(),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $this->faker->date('Y-m-d', '-18 years'),
            'alamat' => $this->faker->address(),
            'province_id' => null,
            'regency_id' => null,
            'district_id' => null,
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'berlaku_hingga' => $this->faker->dateTimeBetween('now', '+5 years')->format('Y-m-d'),
            'tanggal_pembuatan' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'foto_path' => null,
            'status' => $this->faker->randomElement(['draft', 'ready', 'generated', 'active']),
        ];
    }

    public function withPhoto(): static
    {
        return $this->state(fn(array $attributes) => [
            'foto_path' => 'members/photos/' . $attributes['nik'] . '.jpg',
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'ready',
            'foto_path' => 'members/photos/' . ($attributes['nik'] ?? $this->faker->unique()->numerify('################')) . '.jpg',
        ]);
    }
}
