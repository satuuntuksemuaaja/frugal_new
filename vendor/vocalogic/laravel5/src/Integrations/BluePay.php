<?php namespace Vocalogic\Integrations;

use StdClass;
use Vocalogic\VocalogicException;

// BluePay 2.0 POST Method (bp20post)
// http://www.bluepay.com/sites/default/files/documentation/BluePay_bp20post/Bluepay20post.txt

class BluePay {

	protected $url = 'https://secure.bluepay.com/interfaces/bp20post';
	protected $secretKey = '';
	protected $defaultTpsDef = 'ACCOUNT_ID TRANS_TYPE AMOUNT MASTER_ID NAME1 PAYMENT_ACCOUNT';
	protected $response = '';
	protected $fields = [];

	/**
	 * Creates a new BluePay instance
	 */
	public function __construct()
	{
		$this->url = config('services.bluepay.url', $this->url);
		$this->secretKey = config('services.bluepay.secret');
		$this->presetFields();
	}

	/**
	 * Set bp20post interface URL
	 *
	 * @param  string  $url
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Get bp20post interface URL
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set BluePay Secret Key
	 *
	 * @param  string  $secretKey
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;
		return $this;
	}

	/**
	 * Get BluePay Secret Key
	 *
	 * @return string
	 */
	public function getSecretKey()
	{
		return $this->secretKey;
	}

	/**
	 * Set default list of fields for the TAMPER_PROOF_SEAL
	 *
	 * @param  string  $defaultTpsDef
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function setDefaultTpsDef($defaultTpsDef)
	{
		$this->defaultTpsDef = $defaultTpsDef;
		return $this;
	}

	/**
	 * Get default list of fields for the TAMPER_PROOF_SEAL
	 *
	 * @return string
	 */
	public function getDefaultTpsDef()
	{
		return $this->defaultTpsDef;
	}

	/**
	 * Formats the payload field key
	 *
	 * @param  string  $field
	 * @return string
	 */
	public function getFieldKey($field)
	{
		if (preg_match('/^[A-Z0-9_]+$/', $field))
		{
			$field = strtolower($field);
		}
		return strtoupper(snake_case($field));
	}

	/**
	 * Set payload field value
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function setField($field, $value)
	{
		$key = $this->getFieldKey($field);

		if ($key == 'AMOUNT')
		{
			$value = number_format($value, 2, '.', '');
		}

		$this->fields[$key] = $value;
		return $this;
	}

	/**
	 * Get payload field, optionally returning a default value
	 *
	 * @param  string  $field
	 * @param  string  $default
	 * @return string
	 */
	public function getField($field, $default = null)
	{
		$key = $this->getFieldKey($field);
		return isset($this->fields[$key]) ? $this->fields[$key] : $default;
	}

	/**
	 * Set payload fields values
	 *
	 * @param  array  $fields
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function setFields($fields, $reset = true)
	{
		if ($reset)
		{
			$this->resetFields();
		}
		foreach ($fields as $field => $value)
		{
			$this->setField($field, $value);
		}
		return $this;
	}

	/**
	 * Get payload fields
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Clear a payload field
	 *
	 * @param  string  $field
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function unsetField($field)
	{
		$key = $this->getFieldKey($field);
		unset($this->field[$key]);
		return $this;
	}

	/**
	 * Clear all payload fields
	 *
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function unsetFields()
	{
		$this->fields = [];
		return $this;
	}

	/**
	 * Set payload fields according to service configuration
	 *
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function presetFields()
	{
		$this->setField('ACCOUNT_ID', config('services.bluepay.account'));

		if (config('services.bluepay.user'))
		{
			$this->setField('USER_ID', config('services.bluepay.user'));
		}

		$extra = (array) config('services.bluepay');
		foreach (array_except($extra, ['secret', 'account', 'user']) as $field => $value)
		{
			$this->setField($field, $value);
		}

		return $this;
	}

	/**
	 * Clear payload fields and reload the presets
	 *
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function resetFields()
	{
		return $this->unsetFields()->presetFields();
	}

	/**
	 * Get field values for the TAMPER_PROOF_SEAL
	 * It checks TPS_DEF for custom defined fields
	 *
	 * @return array
	 */
	public function getTpsFields()
	{
		$tpsFields = [];
		$tpsDef = $this->getField('TPS_DEF', $this->getDefaultTpsDef());
		foreach (explode(' ', $tpsDef) as $field)
		{
			$tpsFields[] = $this->getField($field);
		}
		return $tpsFields;
	}

	/**
	 * Get the TAMPER_PROOF_SEAL value
	 *
	 * @return string
	 */
	public function getTamperProofSeal()
	{
		$tps = $this->getSecretKey() . implode('', $this->getTpsFields());
		return bin2hex(md5($tps, true));
	}

	/**
	 * Build final payload for BluePay post request
	 *
	 * @return array
	 */
	public function getPostFields()
	{
		$postFields = $this->getFields();

		if (!isset($postFields['TAMPER_PROOF_SEAL']))
		{
			$postFields['TAMPER_PROOF_SEAL'] = $this->getTamperProofSeal();
		}

		return $postFields;
	}

	/**
	 * Post the request to BluePay and get the response
	 * Optionally call a callback function
	 *
	 * @param  callable  $callback
	 * @return Vocalogic\Integrations\BluePay
	 */
	public function post($callback = null)
	{
		$curlOpts = [
			CURLOPT_URL            => $this->getUrl(),
			CURLOPT_POST           => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS     => http_build_query($this->getPostFields()),
		];

		$this->response = curl($curlOpts);

		if (!is_null($callback))
		{
			call_user_func($callback, $this->getResponse(), $this);
		}

		return $this;
	}

	/**
	 * Get the BluePay post response
	 *
	 * @param  boolean  $raw  If true, get the original string response
	 * @return array  Parsed response, as an array
	 */
	public function getResponse($raw = false)
	{
		if ($raw)
		{
			return $this->response;
		}

		parse_str($this->response, $array);
		return $array;
	}

	/**
	 * Get the BluePay post response as an object
	 * Includes 'amount' property from the payload
	 *
	 * @return object
	 */
	public function getResponseObject()
	{
		$object = new StdClass;
		$response = $this->getResponse();
		foreach ($response as $key => $value)
		{
			$property = camel_case(strtolower($key));
			$object->$property = $value;
		}
		if (empty($object->amount) && !is_null($this->getField('AMOUNT')))
		{
			$object->amount = $this->getField('AMOUNT');
		}
		return $object;
	}

	/**
	 * Posts a request to BluePay and returns the successful response as an object
	 * Optionally sets the payload fields before doing the request
	 * Throws exception if payment is not authorized
	 *
	 * @param  array  $fields
	 * @throws Vocalogic\VocalogicException
	 * @return object
	 */
	public function authorize($fields = null)
	{
		if (!is_null($fields))
		{
			$this->setFields($fields);
		}

		$response = $this->post()->getResponse();

		if (empty($response['STATUS']) || ($response['STATUS'] != '1'))
		{
			$message = isset($response['MESSAGE']) ? $response['MESSAGE'] : 'AUTHORIZATION FAILED';
			throw new VocalogicException($message);
		}

		return $this->getResponseObject();
	}

}
