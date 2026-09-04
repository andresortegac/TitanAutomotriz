<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ferreteria.com'],
            ['name' => 'Administrador', 'role' => 'admin', 'active' => true, 'password' => 'password']
        );

        User::updateOrCreate(
            ['email' => 'vendedor@ferreteria.com'],
            ['name' => 'Vendedor', 'role' => 'vendedor', 'active' => true, 'password' => 'password']
        );

        $herramientas = Category::firstOrCreate(['name' => 'Herramientas manuales'], ['active' => true]);
        $electricos = Category::firstOrCreate(['name' => 'Electricos'], ['active' => true]);
        $construccion = Category::firstOrCreate(['name' => 'Construccion'], ['active' => true]);

        $supplier = Supplier::firstOrCreate(
            ['name' => 'Proveedor General SAS'],
            ['nit' => '900123456-7', 'phone' => '3001234567', 'active' => true]
        );

        Customer::firstOrCreate(
            ['document' => '22222222'],
            ['name' => 'Cliente Mostrador', 'phone' => '3000000000']
        );

        $products = [
            ['category_id' => $herramientas->id, 'code' => 'HAM-16', 'name' => 'Martillo 16 oz', 'purchase_price' => 18000, 'sale_price' => 28000, 'stock' => 20, 'min_stock' => 5],
            ['category_id' => $herramientas->id, 'code' => 'DES-PH2', 'name' => 'Destornillador estrella PH2', 'purchase_price' => 6000, 'sale_price' => 12000, 'stock' => 35, 'min_stock' => 8],
            ['category_id' => $electricos->id, 'code' => 'CAB-12', 'name' => 'Cable electrico calibre 12 metro', 'purchase_price' => 2500, 'sale_price' => 4200, 'stock' => 120, 'min_stock' => 30],
            ['category_id' => $construccion->id, 'code' => 'CEM-50', 'name' => 'Cemento gris 50 kg', 'purchase_price' => 28000, 'sale_price' => 36000, 'stock' => 18, 'min_stock' => 6],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['code' => $product['code']],
                $product + ['supplier_id' => $supplier->id, 'active' => true]
            );
        }

        $admin->touch();
    }
}
