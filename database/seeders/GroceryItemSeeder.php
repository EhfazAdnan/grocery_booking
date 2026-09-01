<?php

namespace Database\Seeders;

use App\Models\GroceryItem;
use Illuminate\Database\Seeder;

class GroceryItemSeeder extends Seeder
{
    /**
     * Seed the application's grocery items.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Fresh Red Apples', 'description' => 'Crisp and sweet red apples, perfect for snacking', 'price' => 3.99, 'stock' => 50],
            ['name' => 'Organic Bananas', 'description' => 'Ripe organic bananas, rich in potassium', 'price' => 2.49, 'stock' => 75],
            ['name' => 'Navel Oranges', 'description' => 'Juicy navel oranges, packed with vitamin C', 'price' => 4.99, 'stock' => 40],
            ['name' => 'Strawberries', 'description' => 'Fresh strawberries, sweet and tart', 'price' => 5.99, 'stock' => 30],
            ['name' => 'Blueberries', 'description' => 'Organic blueberries, antioxidant-rich', 'price' => 6.49, 'stock' => 25],
            ['name' => 'Avocados', 'description' => 'Ripe Hass avocados, perfect for guacamole', 'price' => 2.99, 'stock' => 35],
            ['name' => 'Baby Spinach', 'description' => 'Fresh baby spinach leaves, pre-washed', 'price' => 3.49, 'stock' => 45],
            ['name' => 'Roma Tomatoes', 'description' => 'Vine-ripened Roma tomatoes, ideal for sauces', 'price' => 2.99, 'stock' => 60],
            ['name' => 'Organic Carrots', 'description' => 'Crunchy organic carrots, great for snacking', 'price' => 1.99, 'stock' => 80],
            ['name' => 'Broccoli Crowns', 'description' => 'Fresh broccoli crowns, rich in vitamins', 'price' => 2.79, 'stock' => 40],
            ['name' => 'Seedless Grapes', 'description' => 'Sweet seedless red grapes', 'price' => 4.49, 'stock' => 35],
            ['name' => 'Fresh Lemons', 'description' => 'Tangy lemons, perfect for cooking and drinks', 'price' => 1.49, 'stock' => 65],
            ['name' => 'Yellow Onions', 'description' => 'Versatile yellow onions, a kitchen staple', 'price' => 1.29, 'stock' => 90],
            ['name' => 'Russet Potatoes', 'description' => 'Large russet potatoes, ideal for baking', 'price' => 3.99, 'stock' => 70],
            ['name' => 'Whole Milk', 'description' => 'Fresh whole milk, rich and creamy', 'price' => 4.29, 'stock' => 30],
        ];

        foreach ($products as $product) {
            GroceryItem::create(array_merge($product, ['is_active' => true]));
        }
    }
}
