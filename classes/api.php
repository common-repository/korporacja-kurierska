<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class API {
	const API = 'https://www.korporacjakurierska.pl/api';
	const API_TEST = 'https://www.korporacjakurierska.pl/api';

	const API_VALID_TOKEN = 1800; //60 * 30;

	const FIELD_SESSION = 'session';
	const FIELD_COURIER_ID = 'courierId';
	const FIELD_COD = 'cod';
	const FIELD_COD_BANK_ACCOUNT = 'codBankAccount';
	const FIELD_CONTENT = 'content';
	const FIELD_COMMENTS = 'comments';
	const FIELD_NO_COURIER_ORDER = 'noCourierOrder';
	const FIELD_PICKUP_DATE = 'pickupDate';
	const FIELD_COD_AMOUNT = 'codAmount';
	const FIELD_COD_TYPE = 'codType';
	const FIELD_DECLARED_VALUE = 'declaredValue';
	const FIELD_SMS_SENDING_NOTIFICATION = 'smsSendingNotification';
	const FIELD_PACKAGE_TYPE = 'packageType';
	const FIELD_PURPOSE = 'purpose';
	const FIELD_INFO_SERVICE_SMS = 'infoServiceSMS';
	const FIELD_INFO_SERVICE_EMAIL = 'infoServiceEmail';
	const FIELD_INSURANCE = 'insurance';
	const FIELD_SENDING_NOTIFICATION_EMAIL = 'sendingNotificationEmail';
	const FIELD_SENDING_NOTIFICATION_SMS = 'sendingNotificationSms';
	const FIELD_DELIVERY_NOTIFICATION_SMS = 'deliveryNotificationSms';
	const FIELD_CONFIRMATION_EMAIL = 'confirmationEmail';
	const FIELD_CONFIRMATION_SMS = 'confirmationSms';
	const FIELD_NOTIFICATION_EMAIL = 'notificationEmail';
	const FIELD_DELIVERY_CONFIRMATION = 'deliveryConfirmation';
	const FIELD_SENDER_MACHINE_NAME = 'senderMachineName';
	const FIELD_RECEIVER_EMAIL = 'receiverEmail';
	const FIELD_RECEIVER_PHONE = 'receiverPhone';
	const FIELD_RECEIVER_FLAT_NUMBER = 'receiverFlatNumber';
	const FIELD_RECEIVER_HOUSE_NUMBER = 'receiverHouseNumber';
	const FIELD_RECEIVER_STREET = 'receiverStreet';
	const FIELD_RECEIVER_COMPANY = 'receiverCompany';
	const FIELD_RECEIVER_LAST_NAME = 'receiverLastName';
	const FIELD_RECEIVER_NAME = 'receiverName';
	const FIELD_RECEIVER_MACHINE_NAME = 'receiverMachineName';
	const FIELD_SYSTEM_NAME = 'systemName';
	const FIELD_ROD = 'rod';
	const FIELD_DELIVERY_SATURDAY = 'deliverySaturday';
	const FIELD_PRIVATE_RECEIVER = 'privateReceiver';
	const FIELD_PAYMENT_TYPE = 'paymentType';
	const FIELD_PACKAGES = 'packages';
	const FIELD_PICKUP_TIME_FROM = 'pickupTimeFrom';
	const FIELD_PICKUP_TIME_TO = 'pickupTimeTo';
	const FIELD_SENDER_COUNTRY = 'senderCountry';
	const FIELD_SENDER_PHONE = 'senderPhone';
	const FIELD_SENDER_CITY = 'senderCity';
	const FIELD_SENDER_POST_CODE = 'senderPostCode';
	const FIELD_SENDER_FLAT_NUMBER = 'senderFlatNumber';
	const FIELD_SENDER_HOUSE_NUMBER = 'senderHouseNumber';
	const FIELD_SENDER_STREET = 'senderStreet';
	const FIELD_SENDER_COMPANY = 'senderCompany';
	const FIELD_SENDER_LAST_NAME = 'senderLastName';
	const FIELD_SENDER_NAME = 'senderName';
	const FIELD_RECEIVER_CITY = 'receiverCity';
	const FIELD_RECEIVER_COUNTRY = 'receiverCountry';
	const FIELD_RECEIVER_POST_CODE = 'receiverPostCode';
	const FIELD_PACKAGES_NUMBER = 'packagesNumber';
	const FIELD_SERVICE_TYPE = 'serviceType';
	const FIELD_DATE = 'date';
	const FIELD_WEIGHT = 'weight';
	const FIELD_TYPE = 'type';
	const FIELD_POST_CODE = 'postCode';

	const COURIERS = [
		self::COURIER_KEX              => 'KEX',
		self::COURIER_GLS              => 'GLS',
		self::COURIER_FEDEX            => 'FedEx',
		self::COURIER_PACZKOMATY       => 'InPost Paczkomaty',
		self::COURIER_DHL              => 'DHL',
		self::COURIER_FEDEX_LOT        => 'FedEx Lotniczy',
		self::COURIER_PACZKA_W_RUCHU   => 'Paczka w Ruchu',
		self::COURIER_INPOST_KURIER    => 'InPost Kurier',
		self::COURIER_DELTA_CITY       => 'Delta City',
		self::COURIER_PATRON_SERVICE   => 'Patron Service',
		self::COURIER_UPS_EXPRESS      => 'UPS Express Saver',
		self::COURIER_UPS_STANDARD     => 'UPS Standard',
		self::COURIER_UPS_ACCESS_POINT => 'UPS Access Point'
	];

	const COURIER_KEX = 2;
	const COURIER_GLS = 4;
	const COURIER_FEDEX = 5;
	const COURIER_PACZKOMATY = 6;
	const COURIER_DHL = 8;
	const COURIER_FEDEX_LOT = 10;
	const COURIER_PACZKA_W_RUCHU = 11;
	const COURIER_INPOST_KURIER = 12;
	const COURIER_DELTA_CITY = 13;
	const COURIER_PATRON_SERVICE = 15;
	const COURIER_UPS_EXPRESS = 30;
	const COURIER_UPS_STANDARD = 31;
	const COURIER_UPS_ACCESS_POINT = 32;

	const STD = [
		self::STD7  => 'zwrot w ciągu 7 dni roboczych',
		self::STD10 => 'zwrot w ciągu 10 dni roboczych',
		self::STD21 => 'zwrot w ciągu 21 dni roboczych'
	];

	const STD7 = 'STD7';
	const STD10 = 'STD10';
	const STD21 = 'STD21';

	private $test;

	/**
	 * KorporacjaKurierskaAPI constructor.
	 *
	 * @param bool $test
	 */
	public function __construct( $test = false ) {
		$this->setTest( $test );
	}

	/**
	 * @param $method
	 * @param $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function request( $method, $data = [] ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::API . '/' . $method . '.xml' );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

		if ( ! empty( $data ) ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
		}

		$result = curl_exec( $ch );

		if ( $result === false ) {
			throw new \Exception( curl_error( $ch ), curl_errno( $ch ) );
		} else {
			$response = @json_decode( json_encode( simplexml_load_string( $result ) ), true );
			$header   = curl_getinfo( $ch );

			curl_close( $ch );

			if ( $header['http_code'] === 200 ) {
				if ( $response['status'] == "OK" ) {
					return $response;
				} else {
					throw new ApiErrorException( @implode( ' ', $response['message'] ) );
				}
			} else {
				throw new ApiException( 'API Error' );
			}
		}
	}

	/**
	 * Autoryzacja użytkownika w systemie w oparciu o podane adres e-mail i hasło do API oraz utworzenie sesji. Zwracany
	 * identyfikator należy przekazywać jako parametr wszystkich pozostałych metod. Sesja jest aktualna przez 30 minut
	 * od ostatniego wywołania dowolnej z metod.
	 *
	 * @param $email
	 * @param $password
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function login( $email, $password ) {
		return $this->request( 'login', [ 'email' => $email, 'password' => $password ] );
	}

	/**
	 * Metoda zwraca dane użytkownika
	 *
	 * @param string $token
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function profile( $token ) {
		return $this->request( 'profile', [ 'session' => $token ] );
	}

	/**
	 * Aktualizacja profilu użytkownika.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function saveProfile( $token, $data ) {
		return $this->request( 'saveProfile', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Sprawdzenie cen wysyłki w oparciu o podane podstawowe parametry przesyłki (bez usług dodatkowych). Metoda zwraca
	 * ceny dla wszystkich dostępnych w systemie firm kurierskich.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function checkPrices( $token, $data ) {
		return $this->request( 'checkPrices', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Sprawdzenie poprawności danych i ostateczna wycena (z uwzględnieniem usług dodatkowych).
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function checkData( $token, $data ) {
		return $this->request( 'checkData', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Złożenie zamówienia.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function makeOrder( $token, $data ) {
		return $this->request( 'makeOrder', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Lista zamówień użytkownika.
	 *
	 * @param string $token
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function orders( $token ) {
		return $this->request( 'orders', [ 'session' => $token ] );
	}

	/**
	 * Szczegóły zamówienia
	 *
	 * @param string $token
	 * @param int $id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function order( $token, $id ) {
		return $this->request( implode( '/', [ 'order', $id ] ), [ 'session' => $token ] );
	}

	/**
	 * Pobieranie etykiety dla zamówienia określonego
	 *
	 * @param string $token
	 * @param int $id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function label( $token, $id ) {
		return $this->request( implode( '/', [ 'label', $id ] ), [ 'session' => $token ] );
	}

	/**
	 * Pobieranie wielu etykiet dla zamówień.
	 *
	 * @param string $token
	 * @param array $ids
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function labels( $token, $ids = [] ) {
		return $this->request( 'labels', [ 'session' => $token, 'orders' => $ids ] );
	}

	/**
	 * Pobieranie protokołu odbioru PDF dla zamówienia Paczki w Ruchu
	 *
	 * @param string $token
	 * @param int $id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function protocol( $token, $id ) {
		return $this->request( implode( '/', [ 'protocol', $id ] ), [ 'session' => $token ] );
	}

	/**
	 * Pobieranie etykiety zebra dla zamówienia określonego
	 *
	 * @param string $token
	 * @param int $id
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function labelZebra( $token, $id ) {
		return $this->request( implode( '/', [ 'labelZebra', $id ] ), [ 'session' => $token ] );
	}

	/**
	 * Pobieranie upoważnienia do obsługi celnej dla zamówienia FedEx lotniczego
	 *
	 * @param string $token
	 * @param int $id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function authorizationDocument( $token, $id ) {
		return $this->request( implode( '/', [ 'authorizationDocument', $id ] ), [ 'session' => $token ] );
	}

	/**
	 * Pobieranie faktury proforma wymaganej do obsługi celnej dla zamówienia FedEx lotniczego
	 *
	 * @param string $token
	 * @param int $id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function proforma( $token, $id ) {
		return $this->request( implode( '/', [ 'proforma', $id ] ), [ 'session' => $token ] );
	}

	/**
	 * Funkcja zwraca dostępne godziny nadania przesyłek DHL dla określonego kodu pocztowego, dnia i typu przesyłki.
	 * Metoda dostępna tylko dla polskich kodów pocztowych.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function dhlHours( $token, $data = [] ) {
		return $this->request( 'dhlHours', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Funkcja zwraca dostępne godziny nadania przesyłek FedEx Lotniczego dla określonego kraju, kodu pocztowego i dnia.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function fedexIntHours( $token, $data = [] ) {
		return $this->request( 'fedexIntHours', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Funkcja zwraca dostępne godziny nadania przesyłek UPS.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function upsHours( $token, $data = [] ) {
		return $this->request( 'upsHours', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Funkcja pozwala na zamówienie kuriera DHL, FedEx, Delta City, Inpost Kurier lub UPS dla jednego lub kilka
	 * wcześniej złożonych zamówień, dla którego nie było złożone zlecenie odbioru.
	 *
	 * @param string $token
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function bookCourier( $token, $data = [] ) {
		return $this->request( 'bookCourier', array_merge( [ 'session' => $token ], $data ) );
	}

	/**
	 * Funkcja zwraca dostępne paczkomaty InPost.
	 *
	 * @param string $token
	 * @param int $cod
	 * @param string $postCode
	 * @param string $city
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function inpostMachines( $token, $cod = 0, $postCode = '', $city = '' ) {
		return $this->request( 'inpostMachines', array_merge( [
			'session'  => $token,
			'cod'      => $cod,
			'postCode' => $postCode,
			'city'     => $city
		] ) );
	}

	/**
	 * Funkcja zwraca dostępne w systemie Paczka w Ruchu punkty nadania i odbioru paczek.
	 *
	 * @param string $token
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function pwrPoints( $token ) {
		return $this->request( 'pwrPoints', array_merge( [ 'session' => $token ] ) );
	}

	/**
	 * Funkcja zwraca najbliższe dla podanego miasta dostępne w systemie UPS Access Point punkty nadania i odbioru
	 * paczek.
	 *
	 * @param string $token
	 * @param string $city
	 * @param string $countryCode
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function upsAccessPoints( $token, $city, $countryCode = 'PL' ) {
		return $this->request( 'upsAccessPoints', array_merge( [
			'session'     => $token,
			'city'        => $city,
			'countryCode' => $countryCode
		] ) );
	}

	/**
	 * @return bool
	 */
	public function getTest() {
		return $this->test;
	}

	/**
	 * @param bool $test
	 *
	 * @return API
	 */
	public function setTest( $test ) {
		$this->test = $test;

		return $this;
	}
}

class ApiException extends \Exception {

}

class ApiErrorException extends \Exception {

}
