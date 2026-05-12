<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Alessio Attanasio',
            'email' => 'alessio.attanasio@reloadlab.it',
            'password' => Hash::make('C4rt3s10'),
        ]);

        $this->call([
            OrganizationTypeSeeder::class,
            OrganizationRoleSeeder::class,
            QualificationSeeder::class,
            DepartmentSeeder::class,
            ContactTypeSeeder::class,
            ContactUsageSeeder::class,
            AddressTypeSeeder::class,
        ]);
    }
}
