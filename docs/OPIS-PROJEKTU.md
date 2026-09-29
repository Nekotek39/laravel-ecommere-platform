# Opis projektu — sklep internetowy (Laravel)

Dokument do przygotowania się do obrony projektu: co aplikacja robi, jak jest zbudowana,
gdzie w kodzie znajduje się każda funkcja i jakie pytania mogą paść.

---

## 1. Informacje ogólne

| | |
|---|---|
| **Temat** | Sklep internetowy z panelem administracyjnym |
| **Framework** | Laravel 12 (PHP 8.2), wzorzec MVC |
| **Baza danych** | MySQL (XAMPP), baza `laravel_ecommerce` |
| **Widoki** | Blade (szablony Laravela) z Tailwind CSS |
| **Testy** | PHPUnit — 25 testów automatycznych (`php artisan test`) |

**Role użytkowników:**

| Rola | Co może |
|---|---|
| **Gość** (niezalogowany) | przeglądać produkty, dodawać do koszyka, zarejestrować się, zalogować |
| **Klient** (`customer`) | to co gość + składać zamówienia i przeglądać swoje zamówienia |
| **Moderator** (`moderator`) | to co klient + dodawać, edytować i usuwać produkty |
| **Administrator** (`admin`) | wszystko: pulpit ze statystykami, zarządzanie produktami, użytkownikami (CRUD) i zamówieniami |

---

## 2. Realizacja wymagań na ocenę dostateczną

| # | Wymaganie | Jak zostało zrealizowane | Gdzie w kodzie |
|---|---|---|---|
| 1 | Rejestracja i logowanie oparte o bazę danych, szyfrowanie hasła | Użytkownicy w tabeli `users`. Hasło hashowane algorytmem **bcrypt** (12 rund) automatycznie przy zapisie. Logowanie porównuje hash, nie tekst hasła. | `app/Http/Controllers/Auth/*`, `app/Models/User.php` (cast `'password' => 'hashed'`) |
| 2 | Dodawanie użytkowników (rejestracja lub administrator) | Formularz rejestracji (`/register`) oraz formularz „Dodaj użytkownika” w panelu admina. W obu przypadkach hasło jest hashowane. | `RegisterController`, `Admin/UserController@store` |
| 3 | Min. dwa poziomy uprawnień | Trzy role zapisane w kolumnie `users.role`: klient, moderator, administrator. Dostęp pilnowany przez middleware `role`. | `app/Enums/UserRole.php`, `app/Http/Middleware/EnsureUserHasRole.php`, `routes/web.php` |
| 4 | Różne strony po zalogowaniu zależnie od uprawnień | Admin → pulpit admina, moderator → zarządzanie produktami, klient → sklep. | `User::homeRoute()`, `LoginController@store` |
| 5 | CRUD użytkowników w panelu admina | Lista (z wyszukiwarką), podgląd jednego użytkownika, dodawanie, edycja, usuwanie. | `app/Http/Controllers/Admin/UserController.php` |
| 6 | Walidacja danych w formularzach | Każdy formularz sprawdzany po stronie serwera (wymagane pola, format e-mail, unikalność, długość, typy, pliki). Błędy wracają do formularza. | `app/Http/Requests/**` |
| 7 | Koszyk (sesja) | Koszyk przechowywany w sesji jako tablica `[id_produktu => ilość]`. | `app/Services/Cart/CartService.php` |
| 8 | Dodawanie i usuwanie produktów (moderator, administrator) | Panel produktów dostępny tylko dla ról `admin` i `moderator`. | `app/Http/Controllers/Admin/ProductController.php` |
| 9 | Zamawianie produktów (klient) | Zalogowany klient składa zamówienie z koszyka; zapis w bazie, zmniejszenie stanów magazynowych. | `CheckoutController`, `app/Services/Order/OrderService.php` |

---

## 3. Funkcjonalności szczegółowo

### 3.1. Rejestracja

- Adres: `GET/POST /register`
- Pola: imię, e-mail, hasło, powtórzenie hasła
- Walidacja (`RegisterRequest`): wszystkie pola wymagane, poprawny i **unikalny** e-mail, hasło min. 8 znaków i zgodne z powtórzeniem
- Nowe konto zawsze dostaje rolę **klient** — z formularza przyjmowane są tylko `name`, `email`, `password`, więc nie da się „podrzucić” roli administratora
- Po rejestracji użytkownik jest od razu zalogowany i trafia do sklepu

### 3.2. Logowanie i wylogowanie

- Adres: `GET/POST /login`, wylogowanie `POST /logout`
- `Auth::attempt()` wyszukuje użytkownika po e-mailu i porównuje hasło z hashem w bazie (`Hash::check`)
- Opcja „zapamiętaj mnie” (`remember`)
- **Ograniczenie prób:** 5 nieudanych prób na minutę dla danego e-maila i IP (`LoginRequest`) — ochrona przed zgadywaniem haseł
- Po zalogowaniu **regeneracja sesji** (ochrona przed przejęciem sesji — *session fixation*)
- Przekierowanie zależne od roli (`User::homeRoute()`)
- Wylogowanie unieważnia sesję i token CSRF
- Zalogowany użytkownik, który wejdzie na `/login` lub `/register`, jest przekierowany na swoją stronę startową (middleware `guest`)

### 3.3. Uprawnienia (role)

- Role zdefiniowane jako enum PHP: `App\Enums\UserRole` (`customer`, `moderator`, `admin`)
- Middleware `EnsureUserHasRole` zarejestrowany jako alias `role` w `bootstrap/app.php`
- Użycie w trasach: `->middleware('role:admin')` lub `->middleware('role:admin,moderator')`
- Brak uprawnień → **błąd 403** (Forbidden); niezalogowany → przekierowanie na `/login`
- Metody pomocnicze w modelu `User`: `isAdmin()`, `isModerator()`, `canManageProducts()`

### 3.4. Katalog produktów (sklep)

- Strona główna `/` — lista produktów, 12 na stronę (paginacja), od najnowszych
- Wyszukiwarka po nazwie produktu (parametr `search`)
- Strona produktu `/products/{id}` — zdjęcie, nazwa, opis, cena, stan magazynowy
- Informacja „brak w magazynie”, gdy `stock = 0`

### 3.5. Koszyk (sesja)

- Koszyk jest zapisany w **sesji** pod kluczem `cart` jako tablica `[id_produktu => ilość]`, np. `[3 => 2, 7 => 1]`
- Sesje przechowywane są w tabeli `sessions` w bazie (`SESSION_DRIVER=database` w `.env`)
- Operacje (`CartController` + `CartService`):
  - dodanie produktu — `POST /cart/{product}` (dodanie tego samego produktu zwiększa ilość)
  - zmiana ilości — `PATCH /cart/{product}` (ilość 0 usuwa produkt)
  - usunięcie — `DELETE /cart/{product}`
  - podgląd — `GET /cart`: lista pozycji, wartości i suma
- **Kontrola stanu magazynowego:** nie da się dodać więcej sztuk niż jest na stanie
- Koszyk działa także dla gościa; do złożenia zamówienia trzeba się zalogować
- Aktualne ceny są zawsze pobierane z bazy — w sesji trzymane są tylko ID i ilości

### 3.6. Składanie zamówienia (klient)

- Adres: `GET/POST /checkout` — tylko dla zalogowanych (middleware `auth`)
- Formularz dostawy: imię i nazwisko, telefon, adres, miasto, kod pocztowy (wymagane), uwagi (opcjonalne)
- Logika w `OrderService::placeOrder()`, wykonywana w **transakcji bazodanowej**:
  1. blokada wierszy produktów (`lockForUpdate`) — dwóch klientów nie kupi jednocześnie ostatniej sztuki
  2. sprawdzenie, czy każdy produkt istnieje i jest dostępny w żądanej ilości
  3. zapis zamówienia (`orders`) i pozycji (`order_items`)
  4. zmniejszenie stanów magazynowych (`stock`)
  5. po zatwierdzeniu transakcji — opróżnienie koszyka
- Jeśli cokolwiek się nie uda, transakcja jest wycofywana (nic nie zostaje zapisane)
- W pozycji zamówienia zapisywana jest **kopia nazwy i ceny produktu** — zmiana ceny lub usunięcie produktu nie zmienia historycznych zamówień
- Po złożeniu zamówienia klient widzi jego szczegóły

### 3.7. Historia zamówień klienta

- `GET /my-orders` — lista zamówień zalogowanego klienta (numer, data, status, suma)
- `GET /my-orders/{id}` — szczegóły: dane dostawy, pozycje, suma, status
- **Polityka dostępu** `OrderPolicy`: klient widzi tylko **swoje** zamówienia (cudze → 403); administrator widzi wszystkie

### 3.8. Panel administracyjny

Adresy pod prefiksem `/admin`.

**Pulpit** (`/admin`, tylko admin)
- liczba użytkowników, produktów, zamówień i zamówień oczekujących
- 5 ostatnich zamówień

**Produkty** (`/admin/products`, moderator i admin)
- lista produktów z paginacją
- dodawanie i edycja: nazwa, opis, cena, stan magazynowy, zdjęcie
- walidacja (`ProductRequest`): nazwa, cena i stan wymagane; cena > 0; stan ≥ 0; zdjęcie jpg/png/webp do 2 MB
- zdjęcia zapisywane na dysku `public` (`storage/app/public/products`), dostępne przez link `public/storage`
- przy zmianie zdjęcia stare jest usuwane; przy usunięciu produktu usuwane jest też jego zdjęcie

**Użytkownicy — CRUD** (`/admin/users`, tylko admin)
- **Read:** lista z wyszukiwarką (imię lub e-mail) i paginacją; podgląd pojedynczego użytkownika z jego zamówieniami
- **Create:** dodanie użytkownika z wybraną rolą i hasłem
- **Update:** edycja imienia, e-maila, roli; hasło opcjonalne (puste = bez zmian)
- **Delete:** usunięcie użytkownika (wraz z jego zamówieniami)
- walidacja (`UserRequest`): wymagane pola, unikalny e-mail, poprawna rola, hasło min. 8 znaków i potwierdzone
- **zabezpieczenia:** administrator nie może usunąć swojego konta ani odebrać sobie roli administratora

**Zamówienia** (`/admin/orders`, tylko admin)
- lista wszystkich zamówień
- szczegóły zamówienia z danymi klienta
- zmiana statusu: oczekujące → w realizacji → wysłane → zakończone / anulowane

---

## 4. Baza danych

### Tabele aplikacji

**`users`** — użytkownicy
| Kolumna | Typ | Opis |
|---|---|---|
| id | bigint | klucz główny |
| name | varchar | imię i nazwisko |
| email | varchar, unikalny | login |
| password | varchar | **hash bcrypt** hasła |
| role | varchar | `customer` / `moderator` / `admin` |
| remember_token, email_verified_at, timestamps | | pola standardowe Laravela |

**`products`** — produkty
| Kolumna | Typ | Opis |
|---|---|---|
| id | bigint | klucz główny |
| name | varchar | nazwa |
| description | text, null | opis |
| price | decimal(10,2) | cena |
| stock | int unsigned | stan magazynowy |
| image | varchar, null | ścieżka do zdjęcia |

**`orders`** — zamówienia
| Kolumna | Typ | Opis |
|---|---|---|
| id | bigint | numer zamówienia |
| user_id | FK → users | kto zamówił (usunięcie użytkownika usuwa jego zamówienia) |
| status | varchar | `pending` / `processing` / `shipped` / `completed` / `cancelled` |
| full_name, phone, address, city, postal_code | varchar | dane dostawy |
| notes | text, null | uwagi klienta |
| total | decimal(10,2) | suma zamówienia |

**`order_items`** — pozycje zamówienia
| Kolumna | Typ | Opis |
|---|---|---|
| id | bigint | klucz główny |
| order_id | FK → orders | zamówienie |
| product_id | FK → products, null | produkt (po usunięciu produktu ustawiane na NULL) |
| product_name | varchar | kopia nazwy z chwili zakupu |
| price | decimal(10,2) | kopia ceny z chwili zakupu |
| quantity | int | ilość |

### Relacje

```
users 1 ──── * orders 1 ──── * order_items * ──── 1 products
```

- `User::orders()` — `hasMany`
- `Order::user()` — `belongsTo`, `Order::items()` — `hasMany`
- `OrderItem::order()`, `OrderItem::product()` — `belongsTo`

### Tabele techniczne Laravela

`sessions` (sesje, w tym koszyk), `password_reset_tokens`, `cache`, `jobs`, `migrations`.

---

## 5. Architektura i struktura kodu

Aplikacja stosuje wzorzec **MVC** (Model – View – Controller) z dodatkową warstwą serwisów.

```
app/
├── Enums/
│   ├── UserRole.php            role użytkowników
│   └── OrderStatus.php         statusy zamówień
├── Http/
│   ├── Controllers/
│   │   ├── Auth/               LoginController, RegisterController
│   │   ├── Shop/               ProductController, CartController, CheckoutController
│   │   ├── Account/            OrderController (zamówienia klienta)
│   │   └── Admin/              DashboardController, ProductController, UserController, OrderController
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php   sprawdzanie roli
│   └── Requests/               walidacja formularzy (Form Requests)
├── Models/                     User, Product, Order, OrderItem (Eloquent ORM)
├── Policies/
│   └── OrderPolicy.php         kto może oglądać zamówienie
└── Services/
    ├── Cart/CartService.php    logika koszyka w sesji
    └── Order/OrderService.php  logika składania zamówienia
database/
├── migrations/                 struktura tabel
├── factories/                  generatory danych testowych
└── seeders/DatabaseSeeder.php  dane demo
routes/web.php                  wszystkie adresy aplikacji
tests/Feature/                  testy automatyczne
```

**Przepływ żądania** (przykład: dodanie produktu do koszyka):

1. Formularz wysyła `POST /cart/5` z polem `quantity`
2. `routes/web.php` kieruje żądanie do `CartController@store`
3. Laravel automatycznie pobiera produkt o ID 5 z bazy (*route model binding*)
4. Kontroler waliduje `quantity` i wywołuje `CartService::add()`
5. Serwis sprawdza stan magazynowy i zapisuje koszyk w sesji
6. Użytkownik wraca na poprzednią stronę z komunikatem „dodano do koszyka”

---

## 6. Lista adresów (tras)

| Metoda | Adres | Dostęp | Opis |
|---|---|---|---|
| GET | `/` | wszyscy | lista produktów |
| GET | `/products/{product}` | wszyscy | szczegóły produktu |
| GET | `/cart` | wszyscy | koszyk |
| POST / PATCH / DELETE | `/cart/{product}` | wszyscy | dodaj / zmień ilość / usuń z koszyka |
| GET / POST | `/register` | goście | rejestracja |
| GET / POST | `/login` | goście | logowanie |
| POST | `/logout` | zalogowani | wylogowanie |
| GET / POST | `/checkout` | zalogowani | składanie zamówienia |
| GET | `/my-orders`, `/my-orders/{order}` | zalogowani | moje zamówienia |
| GET | `/admin` | admin | pulpit |
| * | `/admin/products...` | admin, moderator | CRUD produktów |
| * | `/admin/users...` | admin | CRUD użytkowników |
| GET / PATCH | `/admin/orders...` | admin | zamówienia i zmiana statusu |

Pełną listę pokazuje polecenie `php artisan route:list --except-vendor`.

---

## 7. Bezpieczeństwo

| Zagrożenie | Zabezpieczenie |
|---|---|
| Wyciek haseł | hasła przechowywane wyłącznie jako hash **bcrypt** |
| Zgadywanie haseł (brute force) | limit 5 prób logowania na minutę |
| CSRF (fałszywe żądania z innych stron) | token `@csrf` w każdym formularzu, weryfikowany automatycznie przez Laravel |
| SQL Injection | zapytania przez Eloquent / Query Builder z parametryzacją (np. wyszukiwarka) |
| XSS | Blade `{{ }}` automatycznie escapuje dane |
| Session fixation | regeneracja ID sesji po zalogowaniu, unieważnienie przy wylogowaniu |
| Nadanie sobie uprawnień | rejestracja przyjmuje tylko imię, e-mail i hasło; rolę może zmienić tylko admin |
| Dostęp do cudzych danych | middleware `role` (panel) i `OrderPolicy` (zamówienia) |
| Złośliwe pliki | upload tylko obrazów jpg/png/webp do 2 MB |
| Sprzedaż ponad stan | kontrola stanu w koszyku i ponownie przy zamówieniu, w transakcji z blokadą |

---

## 8. Testy automatyczne

Uruchomienie: `php artisan test` (testy używają osobnej bazy SQLite w pamięci — nie ruszają danych w MySQL).

| Plik | Co sprawdza |
|---|---|
| `AuthTest` | rejestracja i hashowanie hasła, wymagane pola, błędne hasło, **różne przekierowania dla każdej roli** |
| `AdminTest` | blokada panelu dla gościa i klienta, moderator zarządza produktami ale nie użytkownikami, dodanie produktu ze zdjęciem, walidacja formularzy, **pełny CRUD użytkowników**, admin nie usunie siebie, zmiana statusu zamówienia |
| `CartTest` | dodawanie do koszyka w sesji, limit stanu magazynowego, zmiana ilości i usuwanie |
| `CheckoutTest` | gość musi się zalogować, poprawne złożenie zamówienia (suma, zmniejszenie stanu, pusty koszyk), walidacja, pusty koszyk, brak dostępu do cudzego zamówienia |

Wynik: **22 testy, wszystkie przechodzą.**

---

## 9. Uruchomienie projektu

```bash
composer install
cp .env.example .env          # ustawić DB_DATABASE=laravel_ecommerce, DB_USERNAME=root
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link      # dostęp do zdjęć produktów
php artisan serve             # http://127.0.0.1:8000
```

**Konta demo** (hasło: `password`):

| E-mail | Rola |
|---|---|
| admin@example.com | administrator |
| moderator@example.com | moderator |
| customer@example.com | klient |

---

## 10. Możliwe pytania na obronie

**Jak przechowywane są hasła?**
Jako hash bcrypt. W modelu `User` pole `password` ma cast `hashed`, więc każde przypisanie hasła automatycznie je hashuje (`Hash::make`). Przy logowaniu `Auth::attempt()` porównuje podane hasło z hashem (`Hash::check`). Hasła nie da się odczytać z bazy.

**Czym różni się hashowanie od szyfrowania?**
Szyfrowanie jest odwracalne (można odszyfrować kluczem), hashowanie jest jednokierunkowe. Bcrypt dodaje losową sól, więc to samo hasło daje za każdym razem inny hash.

**Jak działa podział uprawnień?**
Każdy użytkownik ma kolumnę `role`. Trasy panelu są objęte middleware `role:...`, które sprawdza rolę zalogowanego użytkownika i zwraca błąd 403, gdy nie pasuje. Dostęp do zamówień dodatkowo kontroluje `OrderPolicy`.

**Skąd aplikacja wie, gdzie przekierować po zalogowaniu?**
Metoda `User::homeRoute()` zwraca adres zależny od roli (`match` na enumie `UserRole`), a `LoginController` wykonuje `redirect()->intended(...)`.

**Dlaczego koszyk jest w sesji, a nie w bazie?**
Tak wymagało zadanie; poza tym koszyk jest tymczasowy i działa także dla niezalogowanych. W sesji trzymamy tylko ID produktów i ilości, a ceny pobieramy zawsze aktualne z bazy. Sesja jest zapisywana w tabeli `sessions`.

**Co się stanie, gdy dwóch klientów kupi ostatnią sztukę jednocześnie?**
Zamówienie jest składane w transakcji z `lockForUpdate()` — drugi klient poczeka, aż pierwsza transakcja się zakończy, a potem zobaczy, że produktu już nie ma.

**Po co kopiować nazwę i cenę produktu do `order_items`?**
Żeby historia zamówień się nie zmieniała, gdy administrator zmieni cenę albo usunie produkt.

**Gdzie jest walidacja?**
W klasach Form Request (`app/Http/Requests`). Laravel wykonuje ją przed kontrolerem; gdy dane są błędne, wraca do formularza z komunikatami błędów i starymi wartościami (`old()`).

**Czy zwykły użytkownik może nadać sobie rolę admina przy rejestracji?**
Nie. `RegisterController` zapisuje tylko `name`, `email` i `password` (`$request->safe()->only(...)`), a domyślna rola to `customer`.

**Co to jest middleware?**
Warstwa wykonywana przed kontrolerem. Używamy: `auth` (musi być zalogowany), `guest` (musi być niezalogowany) i własnego `role` (musi mieć odpowiednią rolę).

**Co to jest migracja i seeder?**
Migracja to kod PHP opisujący strukturę tabeli (wersjonowanie bazy). Seeder wypełnia bazę danymi startowymi — u nas konta demo, produkty i przykładowe zamówienia.

**Co to jest Eloquent?**
ORM Laravela — każda tabela ma swój model (np. `Product`), a relacje definiujemy metodami (`hasMany`, `belongsTo`). Dzięki temu piszemy `$user->orders` zamiast ręcznego SQL.

**Co to jest route model binding?**
Gdy w trasie jest `{product}`, a kontroler przyjmuje `Product $product`, Laravel sam pobiera produkt z bazy po ID albo zwraca 404, jeśli go nie ma.

**Dlaczego logika koszyka i zamówień jest w serwisach, a nie w kontrolerach?**
Kontroler obsługuje tylko żądanie i odpowiedź, a logika biznesowa jest w jednym miejscu (`CartService`, `OrderService`). Łatwiej ją testować i ponownie wykorzystać.
