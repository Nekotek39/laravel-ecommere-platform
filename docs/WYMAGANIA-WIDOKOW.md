# Wymagania widoków (Blade)

Lista wszystkich plików HTML/Blade, które trzeba przygotować, wraz z tym, co ma się w nich znaleźć.
Zaznaczaj `[x]` przy gotowych widokach.

> **Gdzie zapisać pliki:** `resources/views/` — nazwa z kropkami odpowiada ścieżce,
> np. `shop.home` → `resources/views/shop/home.blade.php`.
>
> **Ważne:** każdy widok z tej listy jest wywoływany przez kontroler — brak pliku = błąd na stronie.
> Maile nie wymagają widoków (korzystają z wbudowanego szablonu Laravela).

**Podsumowanie:** 35 plików

| Sekcja | Liczba |
|---|---|
| Layouty i elementy wspólne | 3 (w tym 1 opcjonalny) |
| Sklep | 7 |
| Logowanie i rejestracja | 4 |
| Konto klienta | 7 |
| Panel administracyjny | 14 |

---

## Wskazówki ogólne

| Potrzebujesz | Użyj |
|---|---|
| Wyświetlić kwotę | `@money($product->price)` → `1,299.99 PLN` |
| Wstawić kwotę do pola formularza (admin) | `\App\Support\Money::toDecimal($product->price)` → `1299.99` |
| Nazwę statusu / metody | `$order->status->label()`, `$order->payment_method->label()` |
| Adres zdjęcia | `$image->url`, `$product->mainImage?->url` |
| Paginację | `{{ $products->links() }}` |
| Komunikaty po akcjach | `session('success')`, `session('warning')`, `session('error')`, `session('status')` |
| Błędy walidacji | `@error('pole') ... @enderror`, `old('pole')` |

Każdy formularz `POST/PUT/PATCH/DELETE` musi mieć `@csrf`, a `PUT/PATCH/DELETE` dodatkowo `@method('...')`.
Dokładne nazwy tras i pól są też w `docs/VIEWS.md`.

---

## 0. Layouty i elementy wspólne

Zrób je jako pierwsze — pozostałe widoki będą je rozszerzać (`@extends` lub komponenty).

### [ ] `layouts/app.blade.php` — layout sklepu

Dostępne zmienne (dostarczane automatycznie): `$cartCount`, `$navigationCategories`

- **Nagłówek**
  - logo z linkiem do `route('home')`
  - wyszukiwarka: formularz `GET route('products.index')`, pole `search`
  - menu kategorii z `$navigationCategories` (każda ma `children`), link: `route('categories.show', $category)`
  - ikona koszyka z licznikiem `$cartCount` → `route('cart.index')`
  - dla gości: „Zaloguj się” (`login`), „Załóż konto” (`register`)
  - dla zalogowanych: „Moje konto” (`account.dashboard`), „Wyloguj” (formularz `POST logout`)
  - dla administratora dodatkowo: link „Panel admina” (`admin.dashboard`)
- **Komunikaty flash** nad treścią: `success`, `warning`, `error`
- **Treść strony** (`@yield('content')` lub `{{ $slot }}`)
- **Stopka**

### [ ] `layouts/admin.blade.php` — layout panelu

- **Menu boczne:** Dashboard, Kategorie, Produkty, Zamówienia, Kody rabatowe, Użytkownicy, Opinie
- link „Wróć do sklepu” i „Wyloguj”
- komunikaty flash: `success`, `warning`, `error`
- treść strony

### [ ] `partials/product-card.blade.php` — kafelek produktu *(opcjonalny, ale zalecany)*

Używany na stronie głównej, liście produktów, w kategorii, liście życzeń i produktach powiązanych.

- zdjęcie (`$product->mainImage?->url`) z linkiem do `route('products.show', $product)`
- nazwa produktu
- cena `@money($product->price)`
- jeśli `$product->isOnSale()`: cena przekreślona `@money($product->compare_at_price)` i znaczek `-{{ $product->discountPercent() }}%`
- ocena: `$product->rating_avg` (gwiazdki) i `$product->rating_count`
- jeśli `! $product->isInStock()`: napis „Brak w magazynie”
- przycisk „Dodaj do koszyka” (formularz `POST cart.items.store`, ukryte pole `product_id`)

---

## 1. Sklep

### [ ] `shop/home.blade.php` — strona główna

Zmienne: `$featuredProducts`, `$newProducts`, `$saleProducts`, `$categories`

- baner / sekcja powitalna
- kafelki kategorii głównych (`$categories`, liczba produktów: `$category->products_count`)
- sekcja **Polecane** — `$featuredProducts`
- sekcja **Nowości** — `$newProducts`
- sekcja **Promocje** — `$saleProducts`

### [ ] `shop/products/index.blade.php` — lista produktów

Zmienne: `$products` (paginacja), `$filters`, `$sorts`, `$categories`

- **Panel filtrów** (formularz `GET`, wartości z `$filters`):
  - kategorie (`$categories` z `children`) — pole `category` (slug)
  - cena od / do — pola `min_price`, `max_price`
  - checkbox „tylko dostępne” — pole `in_stock`
- **Sortowanie** — pole `sort`: `newest`, `price_asc`, `price_desc`, `name`, `popular`
- liczba wyników (`$products->total()`)
- siatka kafelków produktów
- paginacja
- komunikat, gdy brak wyników

### [ ] `shop/categories/show.blade.php` — strona kategorii

Zmienne: `$category`, `$breadcrumbs`, `$products`, `$filters`, `$sorts`

- breadcrumbs (`$breadcrumbs` — lista kategorii od głównej do bieżącej)
- nazwa i opis kategorii
- linki do podkategorii (`$category->children`)
- filtry cenowe, „tylko dostępne” i sortowanie (bez wyboru kategorii)
- siatka produktów + paginacja

### [ ] `shop/products/show.blade.php` — karta produktu

Zmienne: `$product`, `$breadcrumbs`, `$reviews`, `$relatedProducts`, `$canReview`, `$inWishlist`

- breadcrumbs
- **galeria zdjęć** (`$product->images`)
- nazwa, SKU, średnia ocena (`$product->rating_avg`)
- cena, cena przekreślona i procent rabatu (jeśli promocja)
- stan magazynowy: „Dostępny (X szt.)” lub „Brak w magazynie”
- **formularz „Dodaj do koszyka”** — `POST cart.items.store`, pola `product_id`, `quantity`
- **„Dodaj do listy życzeń” / „Usuń z listy życzeń”** — tylko dla zalogowanych, zależnie od `$inWishlist`; `POST account.wishlist.toggle` (parametr: `$product->id`)
- krótki opis (`short_description`) i pełny opis (`description`)
- **opinie** — `$reviews` z paginacją (autor: `$review->user->name`, ocena, tytuł, treść, data)
- **formularz opinii** — tylko gdy `$canReview`; `POST products.reviews.store`, pola `rating` (1–5), `title`, `body`
- **produkty powiązane** — `$relatedProducts`

### [ ] `shop/cart/index.blade.php` — koszyk

Zmienne: `$summary`, `$warnings`

- ostrzeżenia z `$warnings` (np. „ilość zmniejszona do stanu magazynowego”)
- gdy `$summary->isEmpty()`: komunikat „Koszyk jest pusty” i link do sklepu
- **tabela pozycji** (`$summary->items`, każda ma `->product`):
  - zdjęcie, nazwa (link do produktu)
  - cena jednostkowa
  - pole ilości — `PATCH cart.items.update` (parametr: `$item->product_id`), pole `quantity` (0 = usuń)
  - wartość pozycji `@money($item->total())`
  - przycisk „Usuń” — `DELETE cart.items.destroy`
- przycisk „Opróżnij koszyk” — `DELETE cart.clear`
- **kod rabatowy:**
  - brak kuponu: pole `code`, `POST cart.coupon.apply`
  - jest kupon: jego kod (`$summary->coupon->code`) i przycisk „Usuń” — `DELETE cart.coupon.remove`
  - błąd kuponu: `$summary->couponError`
- **podsumowanie:** wartość produktów (`subtotal`), rabat (`discount`), razem (`total`)
- „Brakuje X do darmowej dostawy” — `$summary->missingForFreeShipping()` (gdy > 0)
- przycisk „Przejdź do kasy” → `route('checkout.create')`

### [ ] `shop/checkout/create.blade.php` — kasa (składanie zamówienia)

Zmienne: `$summary`, `$user`, `$addresses`, `$shippingMethods`, `$paymentMethods`, `$selectedShippingMethod`

Formularz `POST checkout.store`:

- **Dane kontaktowe:** `email`, `phone` (dla zalogowanego domyślnie z `$user`)
- **Adres dostawy:**
  - zalogowany z adresami: wybór zapisanego adresu — pole `address_id` (lista `$addresses`)
  - w przeciwnym razie formularz `shipping_address[...]`:
    `first_name`, `last_name`, `company`, `tax_id`, `street`, `city`, `postal_code`, `country` (2 litery, np. PL), `phone`
- **Dane do faktury:**
  - checkbox `billing_same_as_shipping` (domyślnie zaznaczony)
  - po odznaczeniu: formularz `billing_address[...]` z tymi samymi polami
- **Metoda dostawy** — pole `shipping_method`, lista `$shippingMethods`
  (każda pozycja: `['method' => enum, 'cost' => grosze]`, nazwa: `$item['method']->label()`, wartość: `$item['method']->value`)
- **Metoda płatności** — pole `payment_method`, lista `$paymentMethods` (`->label()`, `->value`)
- **Uwagi do zamówienia** — pole `notes`
- **Akceptacja regulaminu** — checkbox `terms`
- **Podsumowanie** (np. w bocznej kolumnie): produkty, wartość, rabat, dostawa (`$summary->shippingCost`), razem
- przycisk „Zamawiam i płacę”
- błąd ogólny koszyka: `@error('cart')`, błąd kuponu: `@error('code')`

### [ ] `shop/checkout/success.blade.php` — podziękowanie za zamówienie

Zmienne: `$order`, `$bankAccount`

- „Dziękujemy za zamówienie!” i numer `$order->number`
- lista zamówionych produktów (`$order->items`: `product_name`, `quantity`, `total`)
- kwoty: `subtotal`, `discount`, `shipping_cost`, `total`
- metoda dostawy i płatności (`->label()`)
- jeśli płatność to przelew (`$order->payment_method->value === 'bank_transfer'`):
  dane do przelewu — `$bankAccount['name']`, `$bankAccount['number']`, tytuł: numer zamówienia
- dla zalogowanych: link do szczegółów zamówienia (`account.orders.show`)

---

## 2. Logowanie i rejestracja

### [ ] `auth/login.blade.php` — logowanie

Formularz `POST login`:
- `email`, `password`, checkbox `remember`
- link „Nie pamiętam hasła” (`password.request`) i „Załóż konto” (`register`)
- komunikat `session('status')` (np. po resecie hasła)

### [ ] `auth/register.blade.php` — rejestracja

Formularz `POST register`:
- `name`, `email`, `phone` (opcjonalny), `password`, `password_confirmation`
- checkbox `terms` (akceptacja regulaminu)
- link „Masz już konto? Zaloguj się”

### [ ] `auth/forgot-password.blade.php` — przypomnienie hasła

Formularz `POST password.email`:
- pole `email`
- komunikat `session('status')` po wysłaniu linku

### [ ] `auth/reset-password.blade.php` — ustawienie nowego hasła

Zmienne: `$token`, `$email`

Formularz `POST password.store`:
- ukryte pole `token` (wartość `$token`)
- `email` (domyślnie `$email`), `password`, `password_confirmation`

---

## 3. Konto klienta

Każda strona konta powinna mieć **menu konta**: Pulpit, Zamówienia, Adresy, Lista życzeń, Profil, Wyloguj.

### [ ] `account/dashboard.blade.php` — pulpit

Zmienne: `$user`, `$recentOrders`, `$ordersCount`, `$defaultAddress`

- powitanie z imieniem
- liczba zamówień
- 5 ostatnich zamówień (numer, data, status, kwota, link)
- domyślny adres (lub link „Dodaj adres”)

### [ ] `account/profile.blade.php` — profil

Zmienne: `$user`

Trzy osobne formularze:
1. **Dane konta** — `PUT account.profile.update`: `name`, `email`, `phone`
2. **Zmiana hasła** — `PUT account.password.update`: `current_password`, `password`, `password_confirmation`
3. **Usuń konto** — `DELETE account.profile.destroy`: `password` (potwierdzenie) + ostrzeżenie

### [ ] `account/addresses/index.blade.php` — lista adresów

Zmienne: `$addresses`

- karty adresów: etykieta, imię i nazwisko (`$address->full_name`), firma, ulica, kod i miasto, kraj, telefon
- oznaczenie adresu domyślnego (`is_default`)
- przyciski „Edytuj” (`account.addresses.edit`) i „Usuń” (`DELETE account.addresses.destroy`)
- przycisk „Dodaj adres” (`account.addresses.create`)

### [ ] `account/addresses/create.blade.php` i [ ] `account/addresses/edit.blade.php` — formularz adresu

Zmienne: `$address`

- dodawanie: `POST account.addresses.store`, edycja: `PUT account.addresses.update`
- pola: `label`, `first_name`, `last_name`, `company`, `tax_id`, `street`, `city`, `postal_code`, `country`, `phone`, checkbox `is_default`

> Wskazówka: oba widoki mogą korzystać z jednego wspólnego partiala z polami formularza.

### [ ] `account/orders/index.blade.php` — historia zamówień

Zmienne: `$orders` (paginacja)

- tabela: numer, data, liczba pozycji (`items_count`), status, płatność, kwota, link „Szczegóły”
- paginacja
- komunikat, gdy brak zamówień

### [ ] `account/orders/show.blade.php` — szczegóły zamówienia

Zmienne: `$order`, `$bankAccount`

- numer, data, status, status płatności
- numer przesyłki (`tracking_number`), jeśli jest
- adres dostawy i adres do faktury (`$order->shipping_address['city']` itd.)
- metoda dostawy i płatności
- pozycje: zdjęcie (`$item->product?->mainImage?->url`), nazwa, cena, ilość, wartość
- kwoty: produkty, rabat (+ kod `coupon_code`), dostawa, razem
- dane do przelewu, jeśli płatność przelewem i nieopłacone
- przycisk **„Anuluj zamówienie”** — tylko gdy `$order->canBeCancelledByCustomer()`; `POST account.orders.cancel`
- przycisk **„Zamów ponownie”** — `POST account.orders.reorder`

### [ ] `account/wishlist.blade.php` — lista życzeń

Zmienne: `$products` (paginacja)

- siatka kafelków produktów
- przy każdym: „Usuń z listy” (`POST account.wishlist.toggle`, parametr `$product->id`) i „Dodaj do koszyka”
- komunikat, gdy lista jest pusta

---

## 4. Panel administracyjny

Wszystkie widoki korzystają z `layouts/admin`.

### [ ] `admin/dashboard.blade.php` — pulpit admina

Zmienne: `$stats`, `$latestOrders`, `$lowStockProducts`, `$bestSellers`, `$salesChart`

- **kafelki statystyk** (`$stats`):
  `orders_today`, `revenue_today`, `revenue_month`, `pending_orders`, `customers`, `products`, `pending_reviews`
- **wykres sprzedaży z 30 dni** — `$salesChart` (każdy dzień: `date`, `orders`, `revenue` w groszach)
- tabela **ostatnich zamówień**
- tabela **niskich stanów magazynowych** (nazwa, SKU, stan, link do edycji)
- **bestsellery** (nazwa, `sold_quantity`)

### [ ] `admin/categories/index.blade.php` — lista kategorii

Zmienne: `$categories` (paginacja)

- wyszukiwarka (`search`)
- tabela: nazwa, kategoria nadrzędna (`parent?->name`), liczba produktów (`products_count`), aktywna, pozycja
- akcje: edytuj, usuń (`DELETE admin.categories.destroy`)
- przycisk „Dodaj kategorię”

### [ ] `admin/categories/create.blade.php` i [ ] `admin/categories/edit.blade.php` — formularz kategorii

Zmienne: `$category`, `$parents`

- dodawanie: `POST admin.categories.store`, edycja: `PUT admin.categories.update`
- pola: `name`, `slug` (opcjonalny — wygeneruje się sam), `parent_id` (select z `$parents`, opcja „brak”), `description`, `position`, checkbox `is_active`

### [ ] `admin/products/index.blade.php` — lista produktów

Zmienne: `$products` (paginacja), `$categories`

- filtry: `search`, `category_id`, `status` (`active`, `inactive`, `low_stock`, `out_of_stock`, `trashed`)
- tabela: miniatura, nazwa, SKU, kategoria, cena, stan, aktywny
- akcje: edytuj, usuń (`DELETE admin.products.destroy`), dla produktów w koszu — przywróć (`POST admin.products.restore`)
- przycisk „Dodaj produkt”

### [ ] `admin/products/create.blade.php` i [ ] `admin/products/edit.blade.php` — formularz produktu

Zmienne: `$product`, `$categories`

- dodawanie: `POST admin.products.store`, edycja: `PUT admin.products.update`
- **formularz musi mieć `enctype="multipart/form-data"`**
- pola: `name`, `slug`, `sku`, `category_id`, `short_description`, `description`,
  `price` i `compare_at_price` (w złotówkach, np. `199.99`), `stock`, checkboxy `is_active`, `is_featured`
- upload zdjęć: `images[]` (wiele plików, jpg/png/webp, max 4 MB)
- **tylko w edycji:** lista obecnych zdjęć (`$product->images`) z przyciskiem usuń
  (`DELETE admin.products.images.destroy`) i zmianą kolejności (`PATCH admin.products.images.reorder`, pole `images[]` = ID w nowej kolejności)

### [ ] `admin/orders/index.blade.php` — lista zamówień

Zmienne: `$orders` (paginacja), `$filters`, `$statuses`, `$paymentStatuses`

- filtry: `search` (numer lub e-mail), `status`, `payment_status`, `from`, `to` (daty)
- tabela: numer, data, klient (e-mail), liczba pozycji, kwota, status, płatność, link „Szczegóły”

### [ ] `admin/orders/show.blade.php` — szczegóły zamówienia

Zmienne: `$order`, `$allowedStatuses`, `$paymentStatuses`

- dane klienta (e-mail, telefon, link do konta, jeśli jest `$order->user`)
- adres dostawy i do faktury, uwagi klienta (`notes`)
- pozycje zamówienia i kwoty
- formularz `PATCH admin.orders.update`:
  - `status` — **tylko statusy z `$allowedStatuses`** (dozwolone przejścia)
  - `payment_status` — z `$paymentStatuses`
  - `tracking_number`
- błąd `@error('status')` (np. niedozwolona zmiana)

### [ ] `admin/coupons/index.blade.php` — lista kodów rabatowych

Zmienne: `$coupons` (paginacja)

- wyszukiwarka (`search`)
- tabela: kod, typ (`->label()`), wartość (`formatted_value`), użycia (`used_count` / `max_uses`), ważność (od–do), aktywny
- akcje: edytuj, usuń
- przycisk „Dodaj kod”

### [ ] `admin/coupons/create.blade.php` i [ ] `admin/coupons/edit.blade.php` — formularz kodu

Zmienne: `$coupon`, `$types`

- dodawanie: `POST admin.coupons.store`, edycja: `PUT admin.coupons.update`
- pola: `code`, `type` (procentowy / kwotowy — z `$types`), `value` (procent 1–100 albo kwota w zł),
  `min_order_amount` (zł), `max_uses`, `starts_at`, `expires_at`, checkbox `is_active`

### [ ] `admin/users/index.blade.php` — lista użytkowników

Zmienne: `$users` (paginacja), `$filters`, `$roles`

- filtry: `search`, `role`
- tabela: imię, e-mail, rola (`->label()`), liczba zamówień (`orders_count`), suma zamówień (`orders_total`), data rejestracji, link „Szczegóły”

### [ ] `admin/users/show.blade.php` — szczegóły użytkownika

Zmienne: `$user`, `$orders`, `$roles`

- dane: imię, e-mail, telefon, data rejestracji
- adresy (`$user->addresses`)
- zamówienia (`$orders`, paginacja)
- formularz zmiany roli — `PATCH admin.users.update`, pole `role`

### [ ] `admin/reviews/index.blade.php` — moderacja opinii

Zmienne: `$reviews` (paginacja), `$status`

- zakładki: Oczekujące (`?status=pending`), Zatwierdzone (`?status=approved`), Wszystkie (`?status=all`)
- tabela: produkt, autor, ocena, tytuł, treść, data
- akcje: zatwierdź (`PATCH admin.reviews.approve`), ukryj (`PATCH admin.reviews.reject`), usuń (`DELETE admin.reviews.destroy`)

---

## Checklista — szybki podgląd

**Wspólne**
- [ ] `layouts/app`
- [ ] `layouts/admin`
- [ ] `partials/product-card` *(opcjonalny)*

**Sklep**
- [ ] `shop/home`
- [ ] `shop/products/index`
- [ ] `shop/products/show`
- [ ] `shop/categories/show`
- [ ] `shop/cart/index`
- [ ] `shop/checkout/create`
- [ ] `shop/checkout/success`

**Logowanie**
- [ ] `auth/login`
- [ ] `auth/register`
- [ ] `auth/forgot-password`
- [ ] `auth/reset-password`

**Konto klienta**
- [ ] `account/dashboard`
- [ ] `account/profile`
- [ ] `account/addresses/index`
- [ ] `account/addresses/create`
- [ ] `account/addresses/edit`
- [ ] `account/orders/index`
- [ ] `account/orders/show`
- [ ] `account/wishlist`

**Panel admina**
- [ ] `admin/dashboard`
- [ ] `admin/categories/index`
- [ ] `admin/categories/create`
- [ ] `admin/categories/edit`
- [ ] `admin/products/index`
- [ ] `admin/products/create`
- [ ] `admin/products/edit`
- [ ] `admin/orders/index`
- [ ] `admin/orders/show`
- [ ] `admin/coupons/index`
- [ ] `admin/coupons/create`
- [ ] `admin/coupons/edit`
- [ ] `admin/users/index`
- [ ] `admin/users/show`
- [ ] `admin/reviews/index`
