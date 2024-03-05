<?php

namespace Database\Seeders;

use App\Models\CertificateType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CertificateTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CertificateType::create(['name' => 'Digital']);
        CertificateType::create(['name' => 'Printed']);
    }
}
