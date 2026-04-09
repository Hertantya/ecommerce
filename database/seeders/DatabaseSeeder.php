<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name'     => 'Admin Shop',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // ── Demo customer ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'customer@shop.com'],
            [
                'name'     => 'John Customer',
                'password' => Hash::make('customer123'),
                'role'     => 'customer',
                'phone'    => '08123456789',
                'address'  => 'Jl. Contoh No. 1, Jakarta',
            ]
        );

        // ── Categories ────────────────────────────────────────
        $food      = Category::firstOrCreate(['name' => 'Food']);
        $beverages = Category::firstOrCreate(['name' => 'Beverages']);
        $snacks    = Category::firstOrCreate(['name' => 'Snacks']);

        // ── Products ──────────────────────────────────────────
        $products = [
            ['category_id' => $food->id,      'name' => 'Nasi Goreng',      'description' => 'Indonesian fried rice with egg',      'price' => 25000, 'stock' => 50],
            ['category_id' => $food->id,      'name' => 'Mie Goreng',       'description' => 'Indonesian fried noodles',             'price' => 22000, 'stock' => 50],
            ['category_id' => $food->id,      'name' => 'Ayam Bakar',       'description' => 'Grilled chicken with special sauce',   'price' => 35000, 'stock' => 30],
            ['category_id' => $beverages->id, 'name' => 'Es Teh Manis',     'description' => 'Sweet iced tea',                      'price' =>  5000, 'stock' => 100],
            ['category_id' => $beverages->id, 'name' => 'Jus Alpukat',      'description' => 'Fresh avocado juice',                  'price' => 15000, 'stock' => 40],
            ['category_id' => $beverages->id, 'name' => 'Kopi Susu',        'description' => 'Milk coffee',                         'price' => 18000, 'stock' => 60],
            ['category_id' => $snacks->id,    'name' => 'Keripik Singkong', 'description' => 'Crispy cassava chips',                 'price' =>  8000, 'stock' => 80],
            ['category_id' => $snacks->id,    'name' => 'Pisang Goreng',    'description' => 'Fried banana with chocolate',          'price' => 10000, 'stock' => 45],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['name' => $p['name']], $p);
        }

        $this->command->info('✅ Seeded: 1 admin, 1 customer, 3 categories, 8 products');
        $this->command->info('   Admin    → admin@shop.com    / admin123');
        $this->command->info('   Customer → customer@shop.com / customer123');
    }
}
