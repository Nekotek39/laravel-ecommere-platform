<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | All monetary amounts are stored in the database in the currency's minor
    | unit (cents), as integers.
    |
    */

    'currency' => env('SHOP_CURRENCY', 'PLN'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'PLN'),

    /*
    |--------------------------------------------------------------------------
    | Shipping
    |--------------------------------------------------------------------------
    |
    | Cost of each shipping method (in cents) and the free shipping threshold,
    | calculated from the cart value after discount.
    |
    */

    'shipping' => [
        'methods' => [
            'courier' => (int) env('SHOP_SHIPPING_COURIER', 1999),
            'parcel_locker' => (int) env('SHOP_SHIPPING_PARCEL_LOCKER', 1299),
            'pickup' => 0,
        ],

        'free_shipping_threshold' => (int) env('SHOP_FREE_SHIPPING_THRESHOLD', 20000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalog
    |--------------------------------------------------------------------------
    */

    'per_page' => 12,

    'admin_per_page' => 20,

    'low_stock_threshold' => 5,

    'max_cart_item_quantity' => 99,

    /*
    |--------------------------------------------------------------------------
    | Guest carts
    |--------------------------------------------------------------------------
    |
    | After how many days of inactivity guest carts are removed by the
    | model:prune command.
    |
    */

    'guest_cart_lifetime_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Bank transfer details
    |--------------------------------------------------------------------------
    */

    'bank_account' => [
        'name' => env('SHOP_BANK_ACCOUNT_NAME', env('APP_NAME', 'Shop')),
        'number' => env('SHOP_BANK_ACCOUNT_NUMBER', '00 0000 0000 0000 0000 0000 0000'),
    ],

];
