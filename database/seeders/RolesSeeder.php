<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use Uuid;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $roles = new Rol();
        $roles->uuid = Uuid::generate()->string;
        $roles->rol = 'Empleado';
        $roles->save();
    }
}
