# Views to build

The backend is ready — controllers return the views listed below (`resources/views/...blade.php`).
All amounts are in **cents** (int). To display them use the `@money($cents)` directive
or `App\Support\Money::format($cents)`; in admin forms use `Money::toDecimal($cents)`.

## Shared data

Views matching `layouts.*` and `partials.*` automatically receive (`AppServiceProvider`):

| Variable | Description |
|---|---|
| `$cartCount` | number of items in the cart |
| `$navigationCategories` | active root categories with `children` (cached for 1h) |

Session flash messages: `success`, `warning`, `error`, `status` (password reset).

## Shop

| View | Route | Variables |
|---|---|---|
| `shop.home` | `home` | `$featuredProducts`, `$newProducts`, `$saleProducts`, `$categories` (with `products_count`) |
| `shop.products.index` | `products.index` | `$products` (paginator), `$filters`, `$sorts`, `$categories` |
| `shop.products.show` | `products.show` | `$product` (with `images`, `category`, `rating_avg`), `$breadcrumbs`, `$reviews` (paginator), `$relatedProducts`, `$canReview`, `$inWishlist` |
| `shop.categories.show` | `categories.show` | `$category` (with `children`), `$breadcrumbs`, `$products`, `$filters`, `$sorts` |
| `shop.cart.index` | `cart.index` | `$summary` (`CartSummary`), `$warnings` (list of messages) |
| `shop.checkout.create` | `checkout.create` | `$summary`, `$user`, `$addresses`, `$shippingMethods` (`[method, cost]`), `$paymentMethods`, `$selectedShippingMethod` |
| `shop.checkout.success` | `checkout.success` | `$order` (with `items`), `$bankAccount` |

Products on listings have `mainImage`, `category`, `rating_avg`, `rating_count` loaded.
Useful helpers: `$product->formatted_price`, `isOnSale()`, `discountPercent()`, `isInStock()`, `$image->url`.

**Catalog filters** (query string): `search`, `category` (slug), `min_price`, `max_price`
(major currency units), `in_stock`, `sort` = `newest|price_asc|price_desc|name|popular`.

**CartSummary**: `items` (CartItem with `product`), `subtotal`, `discount`, `shippingCost`, `total`,
`coupon`, `couponError`, `itemsCount()`, `isEmpty()`, `missingForFreeShipping()`.

## Forms (field names)

- **Add to cart** `POST cart.items.store`: `product_id`, `quantity`
- **Change quantity** `PATCH cart.items.update/{product.id}`: `quantity` (0 = remove)
- **Remove** `DELETE cart.items.destroy/{product.id}`, **empty cart** `DELETE cart.clear`
- **Coupon** `POST cart.coupon.apply`: `code`; remove with `DELETE cart.coupon.remove`
- **Checkout** `POST checkout.store`: `email`, `phone`, `address_id` *(optional, saved address)*
  or `shipping_address[first_name|last_name|company|tax_id|street|city|postal_code|country|phone]`,
  `billing_same_as_shipping` (defaults to 1), `billing_address[...]`, `shipping_method`
  (`courier|parcel_locker|pickup`), `payment_method` (`bank_transfer|cash_on_delivery`), `notes`, `terms`
- **Review** `POST products.reviews.store`: `rating` (1–5), `title`, `body`
- **Wishlist** `POST account.wishlist.toggle/{product.id}`

Cart and wishlist actions return JSON when the request has `Accept: application/json`
(handy for AJAX — the `cart` key contains the summary).

## Authentication

| View | Route | Fields / variables |
|---|---|---|
| `auth.login` | `login` | `email`, `password`, `remember` |
| `auth.register` | `register` | `name`, `email`, `phone`, `password`, `password_confirmation`, `terms` |
| `auth.forgot-password` | `password.request` → `password.email` | `email` |
| `auth.reset-password` | `password.reset` → `password.store` | `$token`, `$email`; fields `token`, `email`, `password`, `password_confirmation` |

Logout: `POST logout`.

## Customer account

| View | Route | Variables |
|---|---|---|
| `account.dashboard` | `account.dashboard` | `$user`, `$recentOrders`, `$ordersCount`, `$defaultAddress` |
| `account.profile` | `account.profile.edit` | `$user`; forms: `account.profile.update` (`name`, `email`, `phone`), `account.password.update` (`current_password`, `password`, `password_confirmation`), `account.profile.destroy` (`password`) |
| `account.addresses.index` | `account.addresses.index` | `$addresses` |
| `account.addresses.create` / `.edit` | `account.addresses.*` | `$address`; fields as in `AddressRequest` + `label`, `is_default` |
| `account.orders.index` | `account.orders.index` | `$orders` (paginator, `items_count`) |
| `account.orders.show` | `account.orders.show` | `$order` (with `items.product.mainImage`), `$bankAccount`; actions `account.orders.cancel`, `account.orders.reorder` |
| `account.wishlist` | `account.wishlist.index` | `$products` (paginator) |

## Admin panel (`/admin`)

| View | Variables |
|---|---|
| `admin.dashboard` | `$stats` (orders_today, revenue_today, revenue_month, pending_orders, customers, products, pending_reviews), `$latestOrders`, `$lowStockProducts`, `$bestSellers` (`sold_quantity`), `$salesChart` (30 days: `date`, `orders`, `revenue`) |
| `admin.categories.index` | `$categories` (with `parent`, `products_count`) |
| `admin.categories.create` / `.edit` | `$category`, `$parents` |
| `admin.products.index` | `$products`, `$categories`; filters `search`, `category_id`, `status` (`active|inactive|low_stock|out_of_stock|trashed`) |
| `admin.products.create` / `.edit` | `$product` (edit: with `images`), `$categories`; fields: `name`, `slug`, `sku`, `category_id`, `short_description`, `description`, `price`, `compare_at_price` (major units), `stock`, `is_active`, `is_featured`, `images[]` (form must be `multipart/form-data`) |
| `admin.orders.index` | `$orders`, `$filters`, `$statuses`, `$paymentStatuses` |
| `admin.orders.show` | `$order` (with `items.product`, `user`), `$allowedStatuses`, `$paymentStatuses`; `PATCH admin.orders.update`: `status`, `payment_status`, `tracking_number` |
| `admin.coupons.index` | `$coupons` |
| `admin.coupons.create` / `.edit` | `$coupon`, `$types`; fields `code`, `type`, `value` (% or major units), `min_order_amount` (major units), `max_uses`, `starts_at`, `expires_at`, `is_active` |
| `admin.users.index` | `$users` (with `orders_count`, `orders_total`), `$filters`, `$roles` |
| `admin.users.show` | `$user` (with `addresses`), `$orders`, `$roles`; `PATCH admin.users.update`: `role` |
| `admin.reviews.index` | `$reviews`, `$status` (`pending|approved|all`) |

The enums (`OrderStatus`, `PaymentStatus`, `ShippingMethod`, `PaymentMethod`, `CouponType`, `UserRole`)
have a `label()` method returning a human-readable English name.
