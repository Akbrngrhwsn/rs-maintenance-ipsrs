<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoomAndManagerSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar ruangan awal (sesuai contoh UI)
        $rooms = [
            'IGD',
            'Poli Mata',
            'Ruang Rawat Inap A',
            'Kantor Admin',
        ];

        foreach ($rooms as $name) {
            // Buat email manager dengan format manager{NamaRuanganTanpaSpasi}@rs.com
            $email = 'manager' . preg_replace('/\s+/', '', ucwords($name)) . '@rs.com';
            $user = User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => 'Manager ' . $name,
                'role' => 'manager',
                'password' => Hash::make('password'),
            ]);

            $room = Room::updateOrCreate([
                'name' => $name,
            ], [
                'manager_id' => $user->id,
            ]);

            // Map existing reports that have matching `ruangan` text to this room
            Report::whereRaw('LOWER(ruangan) = ?', [strtolower($name)])
                ->update(['room_id' => $room->id]);
        }

        // Also, for any other distinct ruangan values in reports, create rooms without manager
        $distinct = Report::selectRaw('DISTINCT LOWER(ruangan) as rname, ruangan')
            ->pluck('ruangan')
            ->filter()
            ->unique();

        foreach ($distinct as $rname) {
            if (!Room::where('name', $rname)->exists()) {
                $room = Room::create(['name' => $rname]);
                Report::whereRaw('LOWER(ruangan) = ?', [strtolower($rname)])
                    ->update(['room_id' => $room->id]);
            }
        }
    }
}
