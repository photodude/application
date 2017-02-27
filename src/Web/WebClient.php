<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Web;

use UserAgentParser\Provider;
use UserAgentParser\Exception\NoResultFoundException;

/**
 * Class to model a Web Client.
 *
 * @property-read  integer  $platform        The detected platform on which the web client runs.
 * @property-read  boolean  $mobile          True if the web client is a mobile device.
 * @property-read  integer  $engine          The detected rendering engine used by the web client.
 * @property-read  integer  $browser         The detected browser used by the web client.
 * @property-read  string   $browserVersion  The detected browser version used by the web client.
 * @property-read  array    $languages       The priority order detected accepted languages for the client.
 * @property-read  array    $encodings       The priority order detected accepted encodings for the client.
 * @property-read  string   $userAgent       The web client's user agent string.
 * @property-read  string   $acceptEncoding  The web client's accepted encoding string.
 * @property-read  string   $acceptLanguage  The web client's accepted languages string.
 * @property-read  array    $detection       An array of flags determining whether or not a detection routine has been run.
 * @property-read  boolean  $robot           True if the web client is a robot
 * @property-read  array    $headers         An array of all headers sent by client
 *
 * @since  1.0
 */
class WebClient
{
	const WINDOWS = 1;
	const WINDOWS_PHONE = 2;
	const WINDOWS_CE = 3;
	const IPHONE = 4;
	const IPAD = 5;
	const IPOD = 6;
	const MAC = 7;
	const BLACKBERRY = 8;
	const ANDROID = 9;
	const LINUX = 10;
	const TRIDENT = 11;
	const WEBKIT = 12;
	const GECKO = 13;
	const PRESTO = 14;
	const KHTML = 15;
	const AMAYA = 16;
	const IE = 17;
	const FIREFOX = 18;
	const CHROME = 19;
	const SAFARI = 20;
	const OPERA = 21;
	const ANDROIDTABLET = 22;
	const EDGE = 23;
	const BLINK = 24;

	/**
	 * @var    UserAgentParser\Provider\Chain  The provider chain.
	 * @since  __DEPLOY_VERSION__
	 */
	protected $providerChain;

	/**
	 * @var    \UserAgentParser\Model\UserAgent The result from parsing the user agent.
	 * @since  __DEPLOY_VERSION__
	 */
	protected $result;

	/**
	 * @var    integer  The detected platform on which the web client runs.
	 * @since  1.0
	 */
	protected $platform;

	/**
	 * @var    string  The detected platform version used by the web client.
	 * @since  __DEPLOY_VERSION__
	 */
	protected $platformVersion;

	/**
	 * @var    boolean  True if the web client is a mobile device.
	 * @since  1.0
	 */
	protected $mobile = false;

	/**
	 * @var    integer  The detected rendering engine used by the web client.
	 * @since  1.0
	 */
	protected $engine;

	/**
	 * @var    string  The detected engine version used by the web client.
	 * @since  __DEPLOY_VERSION__
	 */
	protected $engineVersion;

	/**
	 * @var    integer  The detected browser used by the web client.
	 * @since  1.0
	 */
	protected $browser;

	/**
	 * @var    string  The detected browser version used by the web client.
	 * @since  1.0
	 */
	protected $browserVersion;

	/**
	 * @var    array  The priority order detected accepted languages for the client.
	 * @since  1.0
	 */
	protected $languages = array();

	/**
	 * @var    array  The priority order detected accepted encodings for the client.
	 * @since  1.0
	 */
	protected $encodings = array();

	/**
	 * @var    string  The web client's user agent string.
	 * @since  1.0
	 */
	protected $userAgent;

	/**
	 * @var    string  The web client's accepted encoding string.
	 * @since  1.0
	 */
	protected $acceptEncoding;

	/**
	 * @var    string  The web client's accepted languages string.
	 * @since  1.0
	 */
	protected $acceptLanguage;

	/**
	 * @var    boolean  True if the web client is a robot.
	 * @since  1.0
	 */
	protected $robot = false;

	/**
	 * @var    array  An array of flags determining whether or not a detection routine has been run.
	 * @since  1.0
	 */
	protected $detection = array();

	/**
	 * @var    array  An array of headers sent by client
	 * @since  1.3.0
	 */
	protected $headers;

	/**
	 * Class constructor.
	 *
	 * @param   string  $userAgent       The optional user-agent string to parse.
	 * @param   string  $acceptEncoding  The optional client accept encoding string to parse.
	 * @param   string  $acceptLanguage  The optional client accept language string to parse.
	 *
	 * @since   1.0
	 */
	public function __construct($userAgent = null, $acceptEncoding = null, $acceptLanguage = null)
	{
		// If no explicit user agent string was given attempt to use the implicit one from server environment.
		if (empty($userAgent) && isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}
		else
		{
			$this->userAgent = $userAgent;
		}

		// If no explicit acceptable encoding string was given attempt to use the implicit one from server environment.
		if (empty($acceptEncoding) && isset($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			$this->acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		}
		else
		{
			$this->acceptEncoding = $acceptEncoding;
		}

		// If no explicit acceptable languages string was given attempt to use the implicit one from server environment.
		if (empty($acceptLanguage) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$this->acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}
		else
		{
			$this->acceptLanguage = $acceptLanguage;
		}

		$this->getProvider();
		$this->getResult($this->userAgent);
	}

	/**
	 * Magic method to get an object property's value by name.
	 *
	 * @param   string  $name  Name of the property for which to return a value.
	 *
	 * @return  mixed  The requested value if it exists.
	 *
	 * @since   1.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'mobile':
			case 'platform':
				if (empty($this->detection['platform']))
				{
					$this->detectPlatform($this->userAgent);
				}
				break;

			case 'engine':
				if (empty($this->detection['engine']))
				{
					$this->detectEngine($this->userAgent);
				}
				break;

			case 'browser':
			case 'browserVersion':
				if (empty($this->detection['browser']))
				{
					$this->detectBrowser($this->userAgent);
				}
				break;

			case 'languages':
				if (empty($this->detection['acceptLanguage']))
				{
					$this->detectLanguage($this->acceptLanguage);
				}
				break;

			case 'encodings':
				if (empty($this->detection['acceptEncoding']))
				{
					$this->detectEncoding($this->acceptEncoding);
				}
				break;

			case 'robot':
				if (empty($this->detection['robot']))
				{
					$this->detectRobot($this->userAgent);
				}
				break;
			case 'headers':
				if (empty($this->detection['headers']))
				{
					$this->detectHeaders();
				}
				break;
		}

		// Return the property if it exists.
		if (isset($this->$name))
		{
			return $this->$name;
		}
	}

	/**
	 * Method to get the result object.
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getProvider()
	{
		$this->providerChain = new Provider\Chain(
							array(
								new Provider\PiwikDeviceDetector,
								new Provider\WhichBrowser,
							)
							);
	}

	/**
	 * Method to get the result object.
	 *
	 * @param   string                          $userAgent      The user-agent string to parse.
	 * @param   UserAgentParser\Provider\Chain  $providerChain  The provider chain object.
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getResult($userAgent)
	{
		$providerChain = new Provider\Chain(
							array(
								new Provider\PiwikDeviceDetector,
								new Provider\WhichBrowser,
							)
							);

		try
		{
			if (function_exists('getallheaders'))
			// If php is working under Apache, there is a special function
			{
				// Optional add all headers, to improve the result further (used currently only by WhichBrowser)
				$this->result = $providerChain->parse($userAgent, getallheaders());
			}
			else
			{
				$this->result = $providerChain->parse($userAgent);
			}
		}
		catch (NoResultFoundException $ex)
		{
			// Nothing found
		}
	}

	/**
	 * Detects the client browser and version in a user agent string.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function detectBrowser($userAgent)
	{
		// Attempt to detect the browser type.
		$this->result->getBrowser()->getName();
		$this->result->getBrowser()->getVersion()->getComplete();

		$resultArray = $result->toArray();
		$this->browser = $resultArray;
		$this->browserVersion = $resultArray;

		// Mark this detection routine as run.
		$this->detection['browser'] = true;
	}

	/**
	 * Method to detect the accepted response encoding by the client.
	 *
	 * @param   string  $acceptEncoding  The client accept encoding string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function detectEncoding($acceptEncoding)
	{
		// Parse the accepted encodings.
		$this->encodings = array_map('trim', (array) explode(',', $acceptEncoding));

		// Mark this detection routine as run.
		$this->detection['acceptEncoding'] = true;
	}

	/**
	 * Detects the client rendering engine in a user agent string.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function detectEngine($userAgent)
	{
		// Attempt to detect the client engine
		$this->result->getRenderingEngine()->getName();
		$this->result->getRenderingEngine()->getVersion()->getComplete();

		$resultArray = $result->toArray();
		$this->engine        = $resultArray;
		$this->engineVersion = $resultArray;

		// Mark this detection routine as run.
		$this->detection['engine'] = true;
	}

	/**
	 * Method to detect the accepted languages by the client.
	 *
	 * @param   mixed  $acceptLanguage  The client accept language string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function detectLanguage($acceptLanguage)
	{
		// Parse the accepted encodings.
		$this->languages = array_map('trim', (array) explode(',', $acceptLanguage));

		// Mark this detection routine as run.
		$this->detection['acceptLanguage'] = true;
	}

	/**
	 * Detects the client platform in a user agent string.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function detectPlatform($userAgent)
	{
		// Attempt to detect the client platform (OS).
		$this->result->getOperatingSystem();
		$this->result->getOperatingSystem()->getVersion()->getComplete();
		$this->mobile = $this->result->getDevice()->getIsMobile();

		$resultArray = $result->toArray();
		$this->platform        = $resultArray;
		$this->platformVersion = $resultArray;

		// Mark this detection routine as run.
		$this->detection['platform'] = true;
	}

	/**
	 * Determines if the browser is a robot or not.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function detectRobot($userAgent)
	{
		$this->robot = $this->result->isBot();

		$this->detection['robot'] = true;
	}

	/**
	 * Fills internal array of headers
	 *
	 * @return  void
	 *
	 * @since   1.3.0
	 */
	protected function detectHeaders()
	{
		if (function_exists('getallheaders'))
		// If php is working under Apache, there is a special function
		{
			$this->headers = getallheaders();
		}
		else
		// Else we fill headers from $_SERVER variable
		{
			$this->headers = array();

			foreach ($_SERVER as $name => $value)
			{
				if (substr($name, 0, 5) == 'HTTP_')
				{
					$this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
		}

		// Mark this detection routine as run.
		$this->detection['headers'] = true;
	}
}
