<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\ManagementOfficial;
use App\Models\ManagementPeriod;
use App\Models\Member;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            RegionSeeder::class,
            ManagementPeriodSeeder::class,
            MemberSeeder::class,
        ]);
    }
}
