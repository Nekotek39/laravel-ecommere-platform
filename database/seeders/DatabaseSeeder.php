<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Demo accounts (password for all: "password"):
     *  - admin@example.com      (administrator)
     *  - moderator@example.com  (moderator)
     *  - customer@example.com   (customer)
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);

        User::factory()->moderator()->create([
            'name' => 'Moderator',
            'email' => 'moderator@example.com',
        ]);

        $customer = User::factory()->create([
            'name' => 'John Smith',
            'email' => 'customer@example.com',
        ]);

        Product::factory(20)->create();
        Product::factory(2)->outOfStock()->create();

        $customers = User::factory(5)->create()->push($customer);

        foreach ($customers as $user) {
            Order::factory()
                ->count(2)
                ->for($user)
                ->withItems(fake()->numberBetween(1, 3))
                ->state(fn () => ['status' => fake()->randomElement(OrderStatus::cases())])
                ->create();
        }
    }
}
