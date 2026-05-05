<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Candid Photographer',
            'Cinematographer',
            'Traditional Photographer',
            'Traditional Videographer',
            'Drone',
            'Mixer (Live Setup)',
            'Corporate Shoot',
            'Album Song Shoot',
            'Ads Shoot',
            'Product Shoot',
            'Catalogue Shoot',
            'Short Film Shoot',
            'Manager (Wedding Project)',
            'Photo Editor',
            'Video Editor',
            'Album Creator',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
