<?php

namespace Database\Seeders;

use App\Models\AssetType;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Brand::updateOrCreate([
            'code' => 'POONGSAN'
        ], [
            'name' => 'Poongsan Korea',
        ]);

        Brand::updateOrCreate([
            'code' => 'JUNGER'
        ], [
            'name' => 'Junger',
        ]);

        Brand::updateOrCreate([
            'code' => 'HAWONKOO'
        ], [
            'name' => 'Hawonkoo',
        ]);

        Brand::updateOrCreate([
            'code' => 'BOSSMS'
        ], [
            'name' => 'Bossmassage',
        ]);

        Brand::updateOrCreate([
            'code' => 'BOSSEL'
        ], [
            'name' => 'Boss Electronic',
        ]);

    }
}
