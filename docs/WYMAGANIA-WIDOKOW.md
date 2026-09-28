# Wymagania widoków (Blade)

Lista plików HTML/Blade do przygotowania i tego, co ma się w nich znaleźć.
Zaznaczaj `[x]` przy gotowych widokach.

> **Gdzie zapisać pliki:** `resources/views/` — nazwa z kropkami odpowiada ścieżce,
> np. `shop.products.index` → `resources/views/shop/products/index.blade.php`.
>
> **Ważne:** każdy widok z listy jest wywoływany przez kontroler — brak pliku = błąd na stronie.

**Razem: 20 plików** — 2 layouty, 4 sklep, 2 logowanie, 2 zamówienia klienta, 10 panel admina.

---

## Wymagania z przedmiotu → gdzie są w projekcie

| Wymaganie | Realizacja |
|---|---|
| Rejestracja i logowanie oparte o bazę, szyfrowanie hasła | `RegisterController`, `LoginController`; hasło hashowane bcryptem (cast `'password' => 'hashed'` w `User`) |
| Dodawanie użytkowników (rejestracja lub administrator) | `/register` oraz panel admina → Użytkownicy → Dodaj |
| Min. dwa poziomy uprawnień | 3 role: `customer` (klient), `moderator`, `admin` — `App\Enums\UserRole`, middleware `role:...` |
| Różne strony po zalogowaniu zależnie od roli | `User::homeRoute()`: admin → pulpit admina, moderator → produkty w panelu, klient → sklep |
| CRUD użytkowników w panelu admina | `Admin\UserController` — lista, podgląd, dodawanie, edycja, usuwanie |
| Walidacja formularzy | Form Requesty: `RegisterRequest`, `LoginRequest`, `UserRequest`, `ProductRequest`, `CheckoutRequest` |
| Koszyk (sesja) | `CartService` — koszyk trzymany w sesji jako `[id_produktu => ilość]` |
| Dodawanie i usuwanie produktów (moderator, administrator) | `Admin\ProductController`, trasy z `role:admin,moderator` |
| Zamawianie produktów (klient) | `CheckoutController` + `OrderService` (zapis zamówienia, zmniejszenie stanów) |

**Konta demo** (po `php artisan migrate:fresh --seed`), hasło do wszystkich: `password`

| E-mail | Rola |
|---|---|
| `admin@example.com` | administrator |
| `moderator@example.com` | moderator |
| `customer@example.com` | klient |

---

## Wskazówki ogólne

| Potrzebujesz | Użyj |
|---|---|
| Wyświetlić kwotę | `@money($product->price)` → `1,299.99 PLN` |
| Nazwę roli / statusu | `$user->role->label()`, `$order->status->label()` |
| Sprawdzić rolę w widoku | `auth()->user()->isAdmin()`, `->isModerator()`, `->canManageProducts()` |
| Zdjęcie produktu | `$product->image_url` (może być `null` — pokaż wtedy zaślepkę) |
| Paginację | `{{ $products->links() }}` |
| Komunikaty po akcjach | `session('success')`, `session('error')` |
| Błędy walidacji | `@error('pole') ... @enderror`, `old('pole')` |

Każdy formularz `POST/PUT/PATCH/DELETE` musi mieć `@csrf`, a `PUT/PATCH/DELETE` dodatkowo `@method('...')`.

---

## 1. Layouty

### [ ] `layouts/app.blade.php` — layout sklepu

Zmienna dostępna automatycznie: `$cartCount` (liczba sztuk w koszyku).

- logo / nazwa sklepu → `route('products.index')`
- link do koszyka z licznikiem `$cartCount` → `route('cart.index')`
- **gość:** „Zaloguj się” (`login`), „Zarejestruj się” (`register`)
- **zalogowany:** imię użytkownika, „Moje zamówienia” (`account.orders.index`), „Wyloguj” (formularz `POST logout`)
- **moderator lub admin** (`canManageProducts()`): link „Panel” → `admin.products.index` (admin: `admin.dashboard`)
- komunikaty `success` / `error`
- treść strony (`@yield('content')`)

### [ ] `layouts/admin.blade.php` — layout panelu

- menu zależne od roli:
  - **admin:** Pulpit (`admin.dashboard`), Produkty (`admin.products.index`), Użytkownicy (`admin.users.index`), Zamówienia (`admin.orders.index`)
  - **moderator:** tylko Produkty
- link „Wróć do sklepu” i „Wyloguj”
- komunikaty `success` / `error`
- treść strony

---

## 2. Sklep

### [ ] `shop/products/index.blade.php` — lista produktów (strona główna)

Zmienne: `$products` (paginacja), `$search`

- wyszukiwarka: formularz `GET route('products.index')`, pole `search`
- siatka produktów — dla każdego:
  - zdjęcie (`image_url`), nazwa (link `route('products.show', $product)`), cena `@money(...)`
  - „Brak w magazynie”, gdy `! $product->isInStock()`
  - przycisk „Dodaj do koszyka” — formularz `POST route('cart.store', $product)`
- paginacja
- komunikat, gdy brak produktów

### [ ] `shop/products/show.blade.php` — szczegóły produktu

Zmienne: `$product`

- zdjęcie, nazwa, cena, opis
- stan magazynowy (np. „Dostępne: 7 szt.” / „Brak w magazynie”)
- formularz „Dodaj do koszyka”: `POST route('cart.store', $product)`, pole `quantity`
- błąd `@error('quantity')` (np. „Only 2 of X available.”)

### [ ] `shop/cart/index.blade.php` — koszyk

Zmienne: `$items`, `$total`

Każdy element `$items` to tablica: `$item['product']`, `$item['quantity']`, `$item['total']`.

- gdy `$items` jest puste: „Koszyk jest pusty” + link do sklepu
- tabela: nazwa produktu, cena, ilość, wartość
  - zmiana ilości: formularz `PATCH route('cart.update', $item['product'])`, pole `quantity` (0 = usuń)
  - usunięcie: formularz `DELETE route('cart.destroy', $item['product'])`
- suma `@money($total)`
- przycisk „Złóż zamówienie” → `route('checkout.create')` (gość zostanie przekierowany do logowania)
- błąd `@error('quantity')`

### [ ] `shop/checkout/create.blade.php` — składanie zamówienia (tylko zalogowani)

Zmienne: `$items`, `$total`, `$user`

- podsumowanie koszyka: produkty, ilości, wartości, suma
- formularz `POST route('checkout.store')` z polami:
  - `full_name` (domyślnie `$user->name`), `phone`, `address`, `city`, `postal_code` — **wymagane**
  - `notes` — opcjonalne uwagi
- błędy przy polach (`@error`) i ogólny błąd `@error('cart')` (np. brak towaru)
- przycisk „Zamawiam”

Po złożeniu zamówienia klient trafia na `account/orders/show` z komunikatem `success`.

---

## 3. Logowanie i rejestracja

### [ ] `auth/login.blade.php`

Formularz `POST route('login')`:
- `email`, `password`, checkbox `remember`
- błąd `@error('email')` (złe dane lub za dużo prób)
- link do rejestracji

### [ ] `auth/register.blade.php`

Formularz `POST route('register')`:
- `name`, `email`, `password`, `password_confirmation` (hasło min. 8 znaków)
- błędy przy polach
- link do logowania

---

## 4. Zamówienia klienta

### [ ] `account/orders/index.blade.php` — moje zamówienia

Zmienne: `$orders` (paginacja)

- tabela: numer (`#{{ $order->id }}`), data, status (`->label()`), suma, link „Szczegóły” (`account.orders.show`)
- komunikat, gdy brak zamówień

### [ ] `account/orders/show.blade.php` — szczegóły zamówienia

Zmienne: `$order` (z `items`)

- numer, data, status
- dane dostawy: `full_name`, `phone`, `address`, `postal_code`, `city`, `notes`
- pozycje: `$item->product_name`, `$item->price`, `$item->quantity`, `$item->total()`
- suma `@money($order->total)`

---

## 5. Panel administracyjny

### [ ] `admin/dashboard.blade.php` — pulpit (admin)

Zmienne: `$usersCount`, `$productsCount`, `$ordersCount`, `$pendingOrdersCount`, `$latestOrders`

- 4 kafelki ze statystykami
- tabela 5 ostatnich zamówień (numer, klient `->user->name`, suma, status, link do `admin.orders.show`)

### [ ] `admin/products/index.blade.php` — lista produktów (moderator, admin)

Zmienne: `$products` (paginacja)

- przycisk „Dodaj produkt” → `admin.products.create`
- tabela: miniatura, nazwa, cena, stan
- akcje: „Edytuj” (`admin.products.edit`), „Usuń” — formularz `DELETE route('admin.products.destroy', $product)` (najlepiej z `onclick="return confirm('...')"`)

### [ ] `admin/products/create.blade.php` i [ ] `admin/products/edit.blade.php` — formularz produktu

Zmienne: `$product`

- dodawanie: `POST route('admin.products.store')`, edycja: `PUT route('admin.products.update', $product)`
- **formularz musi mieć `enctype="multipart/form-data"`** (upload zdjęcia)
- pola: `name` *(wymagane)*, `description`, `price` *(wymagane, np. 199.99)*, `stock` *(wymagane)*, `image` (jpg/png/webp, max 2 MB)
- przy edycji: podgląd obecnego zdjęcia
- błędy przy polach

> Wskazówka: pola można wydzielić do wspólnego pliku, np. `admin/products/_form.blade.php`.

### [ ] `admin/users/index.blade.php` — lista użytkowników (admin)

Zmienne: `$users` (paginacja), `$search`

- wyszukiwarka (`search` — imię lub e-mail)
- przycisk „Dodaj użytkownika” → `admin.users.create`
- tabela: imię, e-mail, rola (`->label()`), data rejestracji
- akcje: „Pokaż” (`admin.users.show`), „Edytuj” (`admin.users.edit`), „Usuń” (`DELETE admin.users.destroy`)

### [ ] `admin/users/show.blade.php` — podgląd użytkownika (admin)

Zmienne: `$user`, `$orders`

- imię, e-mail, rola, data rejestracji
- lista zamówień użytkownika (numer, data, status, suma)
- przyciski „Edytuj” i „Usuń”

### [ ] `admin/users/create.blade.php` i [ ] `admin/users/edit.blade.php` — formularz użytkownika (admin)

Zmienne: `$user`, `$roles`

- dodawanie: `POST route('admin.users.store')`, edycja: `PUT route('admin.users.update', $user)`
- pola: `name`, `email`, `role` (select z `$roles`: wartość `->value`, etykieta `->label()`), `password`, `password_confirmation`
- przy edycji: podpis „zostaw puste, aby nie zmieniać hasła”
- błędy przy polach

### [ ] `admin/orders/index.blade.php` — lista zamówień (admin)

Zmienne: `$orders` (paginacja)

- tabela: numer, data, klient (`->user->name`), suma, status, link „Szczegóły” (`admin.orders.show`)

### [ ] `admin/orders/show.blade.php` — szczegóły zamówienia (admin)

Zmienne: `$order` (z `items`, `user`), `$statuses`

- dane klienta i dostawy, uwagi
- pozycje zamówienia i suma
- formularz zmiany statusu: `PATCH route('admin.orders.update', $order)`, select `status` z `$statuses`

---

## Checklista

**Layouty**
- [ ] `layouts/app`
- [ ] `layouts/admin`

**Sklep**
- [ ] `shop/products/index`
- [ ] `shop/products/show`
- [ ] `shop/cart/index`
- [ ] `shop/checkout/create`

**Logowanie**
- [ ] `auth/login`
- [ ] `auth/register`

**Zamówienia klienta**
- [ ] `account/orders/index`
- [ ] `account/orders/show`

**Panel admina**
- [ ] `admin/dashboard`
- [ ] `admin/products/index`
- [ ] `admin/products/create`
- [ ] `admin/products/edit`
- [ ] `admin/users/index`
- [ ] `admin/users/show`
- [ ] `admin/users/create`
- [ ] `admin/users/edit`
- [ ] `admin/orders/index`
- [ ] `admin/orders/show`
