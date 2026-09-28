<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Demo accounts:
     *  - admin@example.com / password (administrator)
     *  - customer@example.com / password (customer)
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);

        $customer = User::factory()->create([
            'name' => 'John Smith',
            'email' => 'customer@example.com',
        ]);

        Address::factory()->default()->for($customer)->create([
            'first_name' => 'John',
            'last_name' => 'Smith',
        ]);

        $this->call(CatalogSeeder::class);

        Coupon::factory()->create(['code' => 'WELCOME10', 'type' => CouponType::Percent, 'value' => 10]);
        Coupon::factory()->create(['code' => 'MINUS50', 'type' => CouponType::Fixed, 'value' => 5000, 'min_order_amount' => 30000]);

        $customers = User::factory(10)->create()->push($customer);

        foreach ($customers as $user) {
            Order::factory()
                ->count(fake()->numberBetween(0, 3))
                ->for($user)
                ->withItems(fake()->numberBetween(1, 4))
                ->state(fn () => [
                    'email' => $user->email,
                    'status' => fake()->randomElement(OrderStatus::cases()),
                    'created_at' => fake()->dateTimeBetween('-30 days'),
                ])
                ->create();
        }

        Product::query()->inRandomOrder()->limit(15)->get()->each(function (Product $product) use ($customers) {
            foreach ($customers->random(3) as $user) {
                Review::factory()->for($product)->for($user)->create([
                    'is_approved' => fake()->boolean(80),
                ]);
            }
        });
    }
}
