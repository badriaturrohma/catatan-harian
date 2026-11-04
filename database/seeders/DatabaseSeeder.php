<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Campus;
use App\Models\GuardianSantri;
use App\Models\Location;
use App\Models\OfficerAssignment;
use App\Models\Program;
use App\Models\QrCredential;
use App\Models\Region;
use App\Models\Room;
use App\Models\RuleVersion;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $pengurus = User::updateOrCreate(
            ['email' => 'pengurus@example.com'],
            [
                'name' => 'Ust. Ahmad Fauzi (Pengurus)',
                'password' => Hash::make('password'),
                'role' => 'pengurus',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $petugas = User::updateOrCreate(
            ['email' => 'petugas@example.com'],
            [
                'name' => 'Rahmat Hidayat (Petugas Pos)',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        $wali = User::updateOrCreate(
            ['email' => 'wali@example.com'],
            [
                'name' => 'Bpk. Hendra Gunawan (Wali Santri)',
                'password' => Hash::make('password'),
                'role' => 'wali',
                'phone' => '081234567892',
                'is_active' => true,
            ]
        );

        // 2. Campuses
        $campusA = Campus::updateOrCreate(
            ['code' => 'KMP-A'],
            [
                'name' => 'Kampus Terpadu Utama',
                'address' => 'Jl. KH. Hasyim Asyari No. 10',
                'is_active' => true,
            ]
        );

        $campusB = Campus::updateOrCreate(
            ['code' => 'KMP-B'],
            [
                'name' => 'Kampus 2 Syariah & Sains',
                'address' => 'Jl. Pendidikan Mandiri No. 45',
                'is_active' => true,
            ]
        );

        // 3. Programs
        $progTI = Program::updateOrCreate(
            ['code' => 'TI-01', 'campus_id' => $campusA->id],
            ['name' => 'Teknik Informatika', 'is_active' => true]
        );

        $progSI = Program::updateOrCreate(
            ['code' => 'SI-01', 'campus_id' => $campusA->id],
            ['name' => 'Sistem Informasi', 'is_active' => true]
        );

        $progEks = Program::updateOrCreate(
            ['code' => 'ES-01', 'campus_id' => $campusB->id],
            ['name' => 'Ekonomi Syariah', 'is_active' => true]
        );

        // 4. Organization: Regions, Areas, Rooms
        $regionPutra = Region::updateOrCreate(['name' => 'Asrama Putra']);
        $areaGedungA = Area::updateOrCreate(['region_id' => $regionPutra->id, 'name' => 'Gedung Ali bin Abi Thalib']);
        $room101 = Room::updateOrCreate(['area_id' => $areaGedungA->id, 'name' => 'Kamar 101'], ['capacity' => 6]);
        $room102 = Room::updateOrCreate(['area_id' => $areaGedungA->id, 'name' => 'Kamar 102'], ['capacity' => 6]);

        // 5. Locations (Pos)
        $posPondok = Location::updateOrCreate(
            ['name' => 'Pos Gerbang Utama Pondok'],
            ['type' => 'pondok', 'campus_id' => null, 'is_active' => true]
        );

        $posKampusA = Location::updateOrCreate(
            ['name' => 'Pos Gerbang Kampus Utama'],
            ['type' => 'kampus', 'campus_id' => $campusA->id, 'is_active' => true]
        );

        $posKampusB = Location::updateOrCreate(
            ['name' => 'Pos Gerbang Kampus 2'],
            ['type' => 'kampus', 'campus_id' => $campusB->id, 'is_active' => true]
        );

        // 6. Officer Assignment
        OfficerAssignment::updateOrCreate(
            ['user_id' => $petugas->id, 'location_id' => $posPondok->id],
            [
                'valid_from' => now()->subDays(30),
                'valid_until' => now()->addDays(90),
                'is_active' => true,
                'notes' => 'Jadwal Reguler Pos Gerbang Pondok',
            ]
        );

        OfficerAssignment::updateOrCreate(
            ['user_id' => $pengurus->id, 'location_id' => $posPondok->id],
            [
                'valid_from' => now()->subDays(30),
                'valid_until' => now()->addDays(90),
                'is_active' => true,
                'notes' => 'Akses Monitoring Pengurus',
            ]
        );

        // 7. Santris & QR Credentials
        $santri1 = Santri::updateOrCreate(
            ['niup' => '2026001'],
            [
                'name' => 'Muhammad Zaki Al-Fatih',
                'gender' => 'L',
                'campus_id' => $campusA->id,
                'program_id' => $progTI->id,
                'region_id' => $regionPutra->id,
                'area_id' => $areaGedungA->id,
                'room_id' => $room101->id,
                'is_active' => true,
            ]
        );
        QrCredential::generateForSantri($santri1);

        $santri2 = Santri::updateOrCreate(
            ['niup' => '2026002'],
            [
                'name' => 'Fathur Rahman Hakim',
                'gender' => 'L',
                'campus_id' => $campusA->id,
                'program_id' => $progSI->id,
                'region_id' => $regionPutra->id,
                'area_id' => $areaGedungA->id,
                'room_id' => $room101->id,
                'is_active' => true,
            ]
        );
        QrCredential::generateForSantri($santri2);

        $santri3 = Santri::updateOrCreate(
            ['niup' => '2026003'],
            [
                'name' => 'Bilal Syarifuddin',
                'gender' => 'L',
                'campus_id' => $campusB->id,
                'program_id' => $progEks->id,
                'region_id' => $regionPutra->id,
                'area_id' => $areaGedungA->id,
                'room_id' => $room102->id,
                'is_active' => true,
            ]
        );
        QrCredential::generateForSantri($santri3);

        // 8. Guardian Relationship (Connect wali to santri1)
        GuardianSantri::updateOrCreate(
            ['user_id' => $wali->id, 'santri_id' => $santri1->id],
            [
                'relationship_type' => 'Ayah',
                'is_verified' => true,
                'verified_by' => $pengurus->id,
                'verified_at' => now(),
            ]
        );

        // 9. Initial Global Rule Version
        RuleVersion::updateOrCreate(
            ['scope' => 'global', 'scope_id' => null, 'is_active' => true],
            [
                'parameters' => [
                    'threshold_yellow_arrive_campus' => 15,
                    'threshold_red_arrive_campus' => 30,
                    'regular_departure_cutoff' => '16:00',
                    'regular_return_curfew' => '16:00',
                    'curfew_late_yellow' => 15,
                    'curfew_late_red' => 30,
                    'permit_late_yellow' => 15,
                    'permit_late_red' => 30,
                ],
                'effective_at' => now()->subDays(30),
                'created_by' => $pengurus->id,
                'change_reason' => 'Aturan inisial sistem monitoring santri',
            ]
        );
    }
}
