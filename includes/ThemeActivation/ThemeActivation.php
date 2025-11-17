<?php

/**
 * VLT Theme Activation Handler
 *
 * @package VLT Helper
 */

namespace VLT\Helper\ThemeActivation;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Theme Activation class
 *
 * Handles license activation and updates for VLThemes products
 */
class ThemeActivation
{
	/**
	 * Encryption key (set via theme config)
	 *
	 * @var string
	 */
	public $key = "";

	/**
	 * Product ID
	 *
	 * @var string
	 */
	private $product_id;

	/**
	 * Product base slug
	 *
	 * @var string
	 */
	private $product_base;

	/**
	 * License server host
	 *
	 * @var string
	 */
	private $server_host = "https://docs.vlthemes.me/wp-json/license-api/";


	/**
	 * Plugin/Theme file path
	 *
	 * @var string
	 */
	private $pluginFile;

	/**
	 * Singleton instance
	 *
	 * @var self
	 */
	private static $selfobj = null;

	/**
	 * Current version
	 *
	 * @var string
	 */
	private $version = "";

	/**
	 * Email address
	 *
	 * @var string
	 */
	private $emailAddress = "";

	/**
	 * On delete license callbacks
	 *
	 * @var array
	 */
	private static $_onDeleteLicense = [];

	/**
	 * Constructor
	 *
	 * @param string $plugin_base_file Plugin/Theme file path.
	 * @param string $product_id Product ID.
	 * @param string $product_base Product slug.
	 */
	public function __construct($plugin_base_file = '', $product_id = '', $product_base = '')
	{
		$this->pluginFile = $plugin_base_file;
		$this->product_id = $product_id;
		$this->product_base = $product_base;

		// Always treat as theme activation (plugin updates not supported)

		$this->version = $this->getCurrentVersion();

		// Theme updates disabled - only license activation/validation
	}

	/**
	 * Set email address
	 *
	 * @param string $emailAddress Email address.
	 */
	public function setEmailAddress($emailAddress)
	{
		$this->emailAddress = $emailAddress;
	}


	/**
	 * Add on delete callback
	 *
	 * @param callable $func Callback function.
	 */
	public static function addOnDelete($func)
	{
		self::$_onDeleteLicense[] = $func;
	}

	/**
	 * Get current version
	 *
	 * @return string Version number.
	 */
	private function getCurrentVersion()
	{
		if (! function_exists('get_plugin_data')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		$data = get_plugin_data($this->pluginFile);
		if (isset($data['Version'])) {
			return $data['Version'];
		}
		return '0';
	}




	/**
	 * Get instance
	 *
	 * @param string $plugin_base_file Plugin/Theme file.
	 * @param string $product_id Product ID.
	 * @param string $product_base Product slug.
	 * @return self Instance.
	 */
	public static function &getInstance($plugin_base_file = null, $product_id = '', $product_base = '')
	{
		if (empty(self::$selfobj)) {
			if (! empty($plugin_base_file)) {
				self::$selfobj = new self($plugin_base_file, $product_id, $product_base);
			}
		}
		return self::$selfobj;
	}

	/**
	 * Get renew link
	 *
	 * @param object $responseObj Response object.
	 * @param string $type Type (s=support, l=license).
	 * @return string Renew link.
	 */
	public static function getRenewLink($responseObj, $type = "s")
	{
		if (empty($responseObj->renew_link)) {
			return "";
		}
		$isShowButton = false;
		if ($type == "s") {
			$support_str = strtolower(trim($responseObj->support_end));
			if (strtolower(trim($responseObj->support_end)) == "no support") {
				$isShowButton = true;
			} elseif (! in_array($support_str, ["unlimited"])) {
				if (strtotime('ADD 30 DAYS', strtotime($responseObj->support_end)) < time()) {
					$isShowButton = true;
				}
			}
			if ($isShowButton) {
				return $responseObj->renew_link . (strpos($responseObj->renew_link, "?") === false ? '?type=s&lic=' . rawurlencode($responseObj->license_key) : '&type=s&lic=' . rawurlencode($responseObj->license_key));
			}
			return '';
		} else {
			$isShowButton = false;
			$expire_str = strtolower(trim($responseObj->expire_date));
			if (! in_array($expire_str, ["unlimited", "no expiry"])) {
				if (strtotime('ADD 30 DAYS', strtotime($responseObj->expire_date)) < time()) {
					$isShowButton = true;
				}
			}
			if ($isShowButton) {
				return $responseObj->renew_link . (strpos($responseObj->renew_link, "?") === false ? '?type=l&lic=' . rawurlencode($responseObj->license_key) : '&type=l&lic=' . rawurlencode($responseObj->license_key));
			}
			return '';
		}
	}

	/**
	 * Encrypt data
	 *
	 * @param string $plainText Plain text.
	 * @param string $password Password.
	 * @return string Encrypted data.
	 */
	private function encrypt($plainText, $password = '')
	{
		if (empty($password)) {
			$password = $this->key;
		}
		$plainText = rand(10, 99) . $plainText . rand(10, 99);
		$method = 'aes-256-cbc';
		$key = substr(hash('sha256', $password, true), 0, 32);
		$iv = substr(strtoupper(md5($password)), 0, 16);
		return base64_encode(openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv));
	}

	/**
	 * Decrypt data
	 *
	 * @param string $encrypted Encrypted data.
	 * @param string $password Password.
	 * @return string Decrypted data.
	 */
	private function decrypt($encrypted, $password = '')
	{
		if (empty($password)) {
			$password = $this->key;
		}

		// Debug logging
		if (WP_DEBUG) {
			error_log('VLT Activation - Decrypt key: ' . $password);
			error_log('VLT Activation - Encrypted length: ' . strlen($encrypted));
		}

		$method = 'aes-256-cbc';
		$key = substr(hash('sha256', $password, true), 0, 32);
		$iv = substr(strtoupper(md5($password)), 0, 16);
		$plaintext = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

		// Debug logging
		if (WP_DEBUG) {
			error_log('VLT Activation - Plaintext result: ' . ($plaintext === false ? 'FALSE' : substr($plaintext, 0, 100)));
			error_log('VLT Activation - Plaintext length: ' . strlen($plaintext));
		}

		if ($plaintext === false || strlen($plaintext) <= 4) {
			if (WP_DEBUG) {
				error_log('VLT Activation - Decryption failed or too short');
			}
			return '';
		}

		return substr($plaintext, 2, -2);
	}


	/**
	 * Decrypt object
	 *
	 * @param string $ciphertext Encrypted text.
	 * @return object Decrypted object.
	 */
	private function decryptObj($ciphertext)
	{
		$text = $this->decrypt($ciphertext);
		return unserialize($text);
	}

	/**
	 * Get domain
	 *
	 * @return string Domain URL.
	 */
	private function getDomain()
	{
		// Попробуем использовать site_url() если функция доступна
		if (function_exists('site_url')) {
			return site_url();
		}

		// Попробуем использовать bloginfo() если определён WP
		if (defined('WPINC') && function_exists('get_bloginfo')) {
			return get_bloginfo('url');
		}

		// Фолбек на $_SERVER
		$scheme = 'http';
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$scheme = 'https';
		} elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
			$scheme = $_SERVER['REQUEST_SCHEME'];
		}

		$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
		$script = !empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';

		$base_url = $scheme . '://' . $host;
		$base_url .= str_replace(basename($script), '', $script);

		return rtrim($base_url, '/');
	}
	/**
	 * Get email
	 *
	 * @return string Email address.
	 */
	private function getEmail()
	{
		return $this->emailAddress;
	}

	/**
	 * Process response
	 *
	 * @param string $response Response data.
	 * @return object Processed response.
	 */
	private function processs_response($response)
	{
		$resbk = "";
		if (! empty($response)) {
			// Debug logging
			if (WP_DEBUG) {
				error_log('VLT Activation - Raw response: ' . substr($response, 0, 200));
			}

			if (! empty($this->key)) {
				$resbk = $response;
				$response = $this->decrypt($response);

				// Debug logging
				if (WP_DEBUG) {
					error_log('VLT Activation - Decrypted response: ' . substr($response, 0, 200));
				}
			}
			$response = json_decode($response);

			if (is_object($response)) {
				return $response;
			} else {
				// Debug logging
				if (WP_DEBUG) {
					error_log('VLT Activation - JSON decode failed');
					error_log('VLT Activation - Trying to decode backup: ' . substr($resbk, 0, 200));
				}

				$response = new \stdClass();
				$response->status = false;
				$response->msg    = "Response Error, contact with the author or update the plugin or theme";

				// Try to get error message from non-encrypted response
				$bkjson = @json_decode($resbk);
				if (! empty($bkjson->msg)) {
					$response->msg = $bkjson->msg;
				}

				$response->data = null;
				return $response;
			}
		}
		$response = new \stdClass();
		$response->msg    = "unknown response";
		$response->status = false;
		$response->data = null;

		return $response;
	}

	/**
	 * Make request to server
	 *
	 * @param string $relative_url Relative URL.
	 * @param object $data Request data.
	 * @param string $error Error message reference.
	 * @return object Response object.
	 */
	private function _request($relative_url, $data, &$error = '')
	{
		$response         = new \stdClass();
		$response->status = false;
		$response->msg    = "Empty Response";
		$response->is_request_error = false;
		$finalData        = json_encode($data);
		if (! empty($this->key)) {
			$finalData = $this->encrypt($finalData);
		}
		$url = rtrim($this->server_host, '/') . "/" . ltrim($relative_url, '/');
		if (function_exists('wp_remote_post')) {
			$rq_params = [
				'method' => 'POST',
				'sslverify' => true,
				'timeout' => 120,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => [],
				'body' => $finalData,
				'cookies' => []
			];
			$serverResponse = wp_remote_post($url, $rq_params);

			if (is_wp_error($serverResponse)) {
				$rq_params['sslverify'] = false;
				$serverResponse = wp_remote_post($url, $rq_params);
				if (is_wp_error($serverResponse)) {
					$response->msg    = $serverResponse->get_error_message();;
					$response->status = false;
					$response->data = null;
					$response->is_request_error = true;
					return $response;
				} else {
					if (! empty($serverResponse['body']) && (is_array($serverResponse) && 200 === (int) wp_remote_retrieve_response_code($serverResponse)) && $serverResponse['body'] != "GET404") {
						return $this->processs_response($serverResponse['body']);
					}
				}
			} else {
				if (! empty($serverResponse['body']) && (is_array($serverResponse) && 200 === (int) wp_remote_retrieve_response_code($serverResponse)) && $serverResponse['body'] != "GET404") {
					return $this->processs_response($serverResponse['body']);
				}
			}
		}
		if (! extension_loaded('curl')) {
			$response->msg    = "Curl extension is missing";
			$response->status = false;
			$response->data = null;
			$response->is_request_error = true;
			return $response;
		}
		//curl when fall back
		$curlParams = [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 120,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => $finalData,
			CURLOPT_HTTPHEADER     => array(
				"Content-Type: text/plain",
				"cache-control: no-cache"
			)
		];
		$curl             = curl_init();
		curl_setopt_array($curl, $curlParams);
		$serverResponse = curl_exec($curl);
		$curlErrorNo = curl_errno($curl);
		$error = curl_error($curl);
		curl_close($curl);
		if (! $curlErrorNo) {
			if (! empty($serverResponse)) {
				return $this->processs_response($serverResponse);
			}
		} else {
			$curl  = curl_init();
			$curlParams[CURLOPT_SSL_VERIFYPEER] = false;
			$curlParams[CURLOPT_SSL_VERIFYHOST] = false;
			curl_setopt_array($curl, $curlParams);
			$serverResponse = curl_exec($curl);
			$curlErrorNo = curl_errno($curl);
			$error = curl_error($curl);
			curl_close($curl);
			if (! $curlErrorNo) {
				if (! empty($serverResponse)) {
					return $this->processs_response($serverResponse);
				}
			} else {
				$response->msg    = $error;
				$response->status = false;
				$response->data = null;
				$response->is_request_error = true;
				return $response;
			}
		}
		$response->msg    = "unknown response";
		$response->status = false;
		$response->data = null;
		$response->is_request_error = true;
		return $response;
	}

	/**
	 * Get request parameters
	 *
	 * @param string $purchase_key Purchase key.
	 * @param string $app_version App version.
	 * @param string $admin_email Admin email.
	 * @return object Request parameters.
	 */
	private function getParam($purchase_key, $app_version, $admin_email = '')
	{
		$req               = new \stdClass();
		$req->license_key  = $purchase_key;
		$req->email        = ! empty($admin_email) ? $admin_email : $this->getEmail();
		$req->domain       = $this->getDomain();
		$req->app_version  = $app_version;
		$req->product_id   = $this->product_id;
		$req->product_base = $this->product_base;

		return $req;
	}

	/**
	 * Get key name for storing license
	 *
	 * @return string Key name.
	 */
	private function getKeyName()
	{
		return hash('crc32b', $this->getDomain() . $this->pluginFile . $this->product_id . $this->product_base . $this->key . "LIC");
	}

	/**
	 * Save WordPress response
	 *
	 * @param object $response Response to save.
	 */
	private function SaveWPResponse($response)
	{
		$key  = $this->getKeyName();
		$data = $this->encrypt(serialize($response), $this->getDomain());
		update_option($key, $data) or add_option($key, $data);
	}

	/**
	 * Get old WordPress response
	 *
	 * @return object|null Saved response.
	 */
	private function getOldWPResponse()
	{
		$key  = $this->getKeyName();
		$response = get_option($key, null);
		if (empty($response)) {
			return null;
		}

		return unserialize($this->decrypt($response, $this->getDomain()));
	}

	/**
	 * Remove old WordPress response
	 *
	 * @return bool Success status.
	 */
	private function removeOldWPResponse()
	{
		$key  = $this->getKeyName();
		$isDeleted = delete_option($key);
		foreach (self::$_onDeleteLicense as $func) {
			if (is_callable($func)) {
				call_user_func($func);
			}
		}

		return $isDeleted;
	}

	/**
	 * Remove license key
	 *
	 * @param string $plugin_base_file Plugin/Theme file.
	 * @param string $message Message reference.
	 * @param string $product_id Product ID.
	 * @param string $product_base Product slug.
	 * @return bool Success status.
	 */
	public static function RemoveLicenseKey($plugin_base_file, &$message = "", $product_id = '', $product_base = '')
	{
		$obj = self::getInstance($plugin_base_file, $product_id, $product_base);
		// No need to clean update info since we don't handle theme updates
		return $obj->_removeWPPluginLicense($message);
	}

	/**
	 * Check WordPress plugin/theme license
	 *
	 * @param string $purchase_key Purchase key.
	 * @param string $email Email address.
	 * @param string $error Error reference.
	 * @param object $responseObj Response object reference.
	 * @param string $plugin_base_file Plugin/Theme file.
	 * @param string $product_id Product ID.
	 * @param string $product_base Product slug.
	 * @return bool Success status.
	 */
	public static function CheckWPPlugin($purchase_key, $email, &$error = "", &$responseObj = null, $plugin_base_file = "", $product_id = '', $product_base = '')
	{
		$obj = self::getInstance($plugin_base_file, $product_id, $product_base);
		$obj->setEmailAddress($email);
		return $obj->_CheckWPPlugin($purchase_key, $error, $responseObj);
	}

	/**
	 * Remove WordPress plugin/theme license
	 *
	 * @param string $message Message reference.
	 * @return bool Success status.
	 */
	final public function _removeWPPluginLicense(&$message = '')
	{
		$oldRespons = $this->getOldWPResponse();
		if (! empty($oldRespons->is_valid)) {
			if (! empty($oldRespons->license_key)) {
				$param    = $this->getParam($oldRespons->license_key, $this->version);
				$response = $this->_request('product/deactive/' . $this->product_id, $param, $message);
				if (empty($response->code)) {
					if (! empty($response->status)) {
						$message = $response->msg;
						$this->removeOldWPResponse();
						return true;
					} else {
						$message = $response->msg;
					}
				} else {
					$message = $response->message;
				}
			}
		} else {
			$this->removeOldWPResponse();
			return true;
		}
		return false;
	}

	/**
	 * Get register info
	 *
	 * @return object|null Register info.
	 */
	public static function GetRegisterInfo()
	{
		if (! empty(self::$selfobj)) {
			return self::$selfobj->getOldWPResponse();
		}
		return null;
	}

	/**
	 * Check WordPress plugin/theme
	 *
	 * @param string $purchase_key Purchase key.
	 * @param string $error Error reference.
	 * @param object $responseObj Response object reference.
	 * @return bool Success status.
	 */
	final public function _CheckWPPlugin($purchase_key, &$error = "", &$responseObj = null)
	{
		if (empty($purchase_key)) {
			$this->removeOldWPResponse();
			$error = "";
			return false;
		}
		$oldRespons = $this->getOldWPResponse();
		$isForce = false;
		if (! empty($oldRespons)) {
			if (! empty($oldRespons->expire_date) && strtolower($oldRespons->expire_date) != "no expiry" && strtotime($oldRespons->expire_date) < time()) {
				$isForce = true;
			}
			if (! $isForce && ! empty($oldRespons->is_valid) && $oldRespons->next_request > time() && (! empty($oldRespons->license_key) && $purchase_key == $oldRespons->license_key)) {
				$responseObj = clone $oldRespons;
				unset($responseObj->next_request);

				return true;
			}
		}

		$param    = $this->getParam($purchase_key, $this->version);

		// Debug logging
		if (WP_DEBUG) {
			error_log('VLT Activation - Request URL: ' . $this->server_host . 'product/active/' . $this->product_id);
			error_log('VLT Activation - Product ID: ' . $this->product_id);
			error_log('VLT Activation - Product Base: ' . $this->product_base);
			error_log('VLT Activation - Domain: ' . $param->domain);
		}

		$response = $this->_request('product/active/' . $this->product_id, $param, $error);
		if (empty($response->is_request_error)) {
			if (empty($response->code)) {
				if (! empty($response->status)) {
					if (! empty($response->data)) {
						$serialObj = $this->decrypt($response->data, $param->domain);

						$licenseObj = unserialize($serialObj);
						if ($licenseObj->is_valid) {
							$responseObj           = new \stdClass();
							$responseObj->is_valid = $licenseObj->is_valid;
							if ($licenseObj->request_duration > 0) {
								$responseObj->next_request = strtotime("+ {$licenseObj->request_duration} hour");
							} else {
								$responseObj->next_request = time();
							}
							$responseObj->expire_date   = $licenseObj->expire_date;
							$responseObj->support_end   = $licenseObj->support_end;
							$responseObj->license_title = $licenseObj->license_title;
							$responseObj->license_key   = $purchase_key;
							$responseObj->msg           = $response->msg;
							$responseObj->renew_link           = ! empty($licenseObj->renew_link) ? $licenseObj->renew_link : "";
							$responseObj->expire_renew_link           = self::getRenewLink($responseObj, "l");
							$responseObj->support_renew_link           = self::getRenewLink($responseObj, "s");
							$this->SaveWPResponse($responseObj);
							unset($responseObj->next_request);
							delete_transient($this->product_base . "_up");
							return true;
						} else {
							if ($this->__checkoldtied($oldRespons, $responseObj, $response)) {
								return true;
							} else {
								$this->removeOldWPResponse();
								$error = ! empty($response->msg) ? $response->msg : "";
							}
						}
					} else {
						$error = "Invalid data";
					}
				} else {
					$error = $response->msg;
				}
			} else {
				$error = $response->message;
			}
		} else {
			if ($this->__checkoldtied($oldRespons, $responseObj, $response)) {
				return true;
			} else {
				$this->removeOldWPResponse();
				$error = ! empty($response->msg) ? $response->msg : "";
			}
		}
		return $this->__checkoldtied($oldRespons, $responseObj);
	}

	/**
	 * Check old tied license
	 *
	 * @param object $oldRespons Old response reference.
	 * @param object $responseObj Response object reference.
	 * @return bool Success status.
	 */
	private function __checkoldtied(&$oldRespons, &$responseObj)
	{
		if (! empty($oldRespons) && (empty($oldRespons->tried) || $oldRespons->tried <= 2)) {
			$oldRespons->next_request = strtotime("+ 1 hour");
			$oldRespons->tried = empty($oldRespons->tried) ? 1 : ($oldRespons->tried + 1);
			$responseObj = clone $oldRespons;
			unset($responseObj->next_request);
			if (isset($responseObj->tried)) {
				unset($responseObj->tried);
			}
			$this->SaveWPResponse($oldRespons);
			return true;
		}
		return false;
	}
}
