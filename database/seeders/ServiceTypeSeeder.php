<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'REGISTRO SANITARIO NUEVO',
            'RENOVACION DE REGISTRO SANITARIO',
            'MODIFICACION LEGAL',
            'MODIFICACION TÉCNICA',
            'REQUERIMIENTO INVIMA (AUTOS)',
            'CERTIFICADO DE VENTA LIBRE',
            // Aquí se pueden agregar más tipos según el archivo CSV real
        ];

        foreach ($types as $name) {
            ServiceType::firstOrCreate(['name' => $name]);
        }
    }
}
