=== Korporacja Kurierska - Metoda wysyłki dla WooCommerce ===
Contributors: bgraczyk
Donate link: http://korporacjakurierska.pl
Tags: korporacja kurierska, woocommerce shipping, cart based shipping, order based shipping, kurier, wysyłka, metoda wysyłki
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 4.9
Stable tag: 2.1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Integracja Korporacji Kurierskiej dla wysyłki zamówień w WooCommerce.

Główne funkcjonalności integracji:

* automatyczne zamawianie kurierów,
* generowanie listu przewozowego,
* obsługa kurierów: **DHL, K-EX, UPS, GLS Zagranica, FedEx, Fedex Zagranica, InPost Paczkomaty, Inpost Kurier, RUCH, InPost Kurier, Delta City**,
* możliwość definiowania cen w oparciu o kryterium ilościowe i wagowe,
* obsługa klas wysyłki WooCommerce,
* możliwość zdefiniowania darmowej wysyłki,
* obsługa pobrań i ubezpieczenia oraz innych usług dodatkowych.

Polska Korporacja Wydawców i Dystrybutorów właściciel marki KORPORACJA KURIERSKA to jeden z największych i najdłużej istniejący pośrednik usług kurierskich w Polsce.

Od ponad 14 lat świadczy usługi kurierskie dla firm i instytucji wspierając swoim doświadczeniem i profesjonalizmem realizację najbardziej złożonych dostaw towarów do sieci handlowych, hurtowni, odbiorców firmowych i indywidualnych. Dla swoich klientów oferuje kompleksową usługę logistyczną magazynowania, kompletowania, etykietowania, doręczania towarów do odbiorów, raportowania poszczególnych etapów obsługi.

Korporacja Kurierska to tani kurier, który zatrudnia ponad 40 osób, specjalistów z branży logistycznej, finansowej i sprzedaży, którzy swoim doświadczeniem i zaangażowaniem skutecznie realizują zadania zlecane przez klientów.

== Installation ==

Opis w jaki sposób zainstalować wtyczkę:

1. Dodaj katalog `korporacja-kurierska` do katalogu `/wp-content/plugins/` swojego WordPress'a.
2. Aktywuj plugin w zakładce 'Wtyczki', którą znajdziesz w menu w panelu admina

[Instrukcja i dokumentacja](https://www.korporacjakurierska.pl/strona/wyswietl/186/integracja-z-woocommerce, "Instrukcja i dokumentacja").

Wtyczka wymaga serwera PHP z wersją co najmniej 5.6 lub nowszą.
Wtyczka wymaga do poprawnego działania WooCommerce z wersją 3.0 lub nowszą.

== Frequently Asked Questions ==

= W jaki sposób uzyskać dane dostępowe umożliwiające skorzystanie z metody wysyłki =

Aby skorzystać z wtyczki należy podać login z portalu Korporacja Kurierska oraz Hasło API. Należy zwrócić uwagę, że hasło API jest hasłem niezależnym od podstawowego hasła użytkownika. Konta użytkownika nie mają automatycznie zdefiniowanych haseł dla środowiska API, dlatego przed rozpoczęciem integracji należy zdefiniować hasło w profilu swojego konta. Brak zdefiniowanego hasła uniemożliwia skorzystanie z API.

= Powielona informacje o wyborze punktu dostawy =

Jeśli WooCommerce jest ustawiony w trybie debugowania to informacje o punkcie dostawy mogą się powielić w szczegółach dostawy. Aby informacja wyświetlała się prawidłowo należy wyłączyć tryb debugowania. Tryb debugowania jest dostępny w Ustawieniach WooCommerce -> Wysyłka -> Opcje wysyłki.

= Po aktualizacji integracji z wersji 1.x do 2.x kwota wysyłki jest obliczana niepoprawnie =

Związku z dodaniem obsługi klas wysyłki musiała się zmienić forma obliczania kwoty wysyłki. Należy sprawdzić metody wysyłki utworzone przez wtyczkę i dokonać aktualizacji ustawień.

= Mam pomysł na nową funkcjonalność integracji. Czy mogę zgłosić taki pomysł? =

Oczywiście. Wszystkie pomysły na rozwój naszej integracji zgłaszaj na it@korporacjakurierska.pl

== Changelog ==
= 2.1.2 - 2018-03-12 =
* Poprawka dot. błędów z nowego API

= 2.1.1 - 2017-12-12 =
* Poprawka wydajności integracji

= 2.1 - 2017-12-05 =
* Dodano opis dla metod wysyłki widoczny dla klienta

= 2.0.1 - 2017-11-30 =
* Dodano obsługę klas wysyłki WooCoomerce. **Należy dokonać aktualizacji i weryfikacji ustawień dla metod wysyłki zdefiniowanych przez wtyczkę.**
* Dodatno opcję włączenie / wyłączenia podatku dla wysyłki.
* Dodano informacje o braku dostępności punktu odbioru przesyłki dla InPost Paczkomaty oraz Paczka w Ruchu.
* Rozszerzono format przy wprowadzaniu ceny przesyłki. Możliwe jest teraz podanie ceny bez przedziału dziesiętnego jak i z większą dokładnością - do 10 miejsc. Separatorem dziesiętnym pozostaje kropka.
* Drobne poprawki.

= 1.4 - 2017-11-08 =
* Dodano kolumnę informacyjną dotyczącą statusu utworzenia przesyłki
* Dodano dodatkowy metabox dla zamówienia umożliwiający włączenie lub wyłączenie opcji nadania przesyłki przez Korporację Kurierską
* Dodano stronę informacyjną Wsparcia w ustawieniach wtyczki

= 1.3.1 - 2017-11-06 =
* Przywrócono zmianę formatu przy wprowadzaniu ceny przesyłki z xx,xx na xx.xx.

= 1.3 - 2017-10-30 =
* Dodano wyszukiwanie domyślnych punktów nadania w ustawieniach wtyczki

= 1.2.3 - 2017-09-26 =
* Poprawka dot. pobierania godzin podjazdu dla kuriera DHL

= 1.2.2 - 2017-09-18 =
* Dodanie komunikatu w przypadku braku godzin podjazdu kuriera dla wybranej daty

= 1.2.1 - 2017-09-18 =
* Poprawka przy wyświetlaniu godziny podjazdu kuriera

= 1.2 - 2017-09-14 =
* Wyświetlanie pełnej nazwy punktu nadania przy zamówieniu
* Wtyczka na stronie zamówienia przy wyborze punktu odbioru nie wybiera automatycznie pierwszego punktu.

= 1.1 - 2017-09-12 =
* Patron Service

= 1.0.5 - 2017-08-02 =
* Zmiana formatu przy wprowadzaniu ceny przesyłki z xx,xx na xx.xx. Zmiana powinna wyeliminować problem z podaniem cen netto dla wysyłki.
* Drobne poprawki

= 1.0.3 - 2017-07-13 =
* Poprawka wyświetlania punktów Paczka w Ruchu, Paczkomaty oraz UPS Access Point

= 1.0.2 - 2017-07-12 =
* Poprawka dot. zapisu danych przy tworzeniu metody wysyłki
* Usunięcie kontenera SEO przy tworzenie szablonu paczki

= 1.0.1 - 2017-07-11 =
* Dodanie komunikatu o wymagalności wersji PHP w przypadku posiadania serwera z wersją niższą niż 5.6

= 1.0 - 2017-07-05 =
* Production release!

= 1.0 RC2 - 2017-06-29 =
* Change settings page
* Add package templates
* Add settings for default COD type

= 1.0 RC1 - 2017-06-26 =
* First release!
