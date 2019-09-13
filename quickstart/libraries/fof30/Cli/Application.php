<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

// Do not put the JEXEC or die check on this file (it's called from a CLI entry point, defining _JEXEC)

/**
 * Joomla! CLI application helper. Include this file and extend your application class from FOFCliApplication.
 * Override the execute() method in your concrete class. At the end of your PHP file run the application with:
 *
 * FOFCliApplication::getInstance('YourClassName')->execute();
 *
 * You can set the following variables right before including this file:
 *
 * $minphp = '5.4.0'; // Minimum PHP version required for your script
 * $curdir = __DIR__; // Path to your script file
 */

// Abort immediately when this file is executed from a web SAPI
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	die;
}

// Define ourselves as a parent Joomla! entry point file
define('_JEXEC', 1);

// Work around some misconfigured servers which print out notices
if (function_exists('error_reporting'))
{
	$oldLevel = error_reporting(0);
}

// Minimum PHP version check
if (!isset($minphp))
{
	$minphp = '5.4.0';
}

if (version_compare(PHP_VERSION, $minphp, 'lt'))
{
	$currentPHPVersion   = PHP_VERSION;
	$phpExecutableFolder = PHP_BINDIR;

	echo <<< ENDWARNING
================================================================================
WARNING! Incompatible PHP version $currentPHPVersion (required: $minphp or later)
================================================================================

This script must be run using PHP version $minphp or later. Your server is
currently using a much older version which would cause this script to crash. As
a result we have aborted execution of the script. Please contact your host and
ask them for the correct path to the PHP CLI binary for PHP $minphp or later, then
edit your CRON job and replace your current path to PHP with the one your host
gave you.

For your information, the current PHP version information is as follows.

PATH:    $phpExecutableFolder
VERSION: $currentPHPVersion

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
IMPORTANT!
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
PHP version numbers are NOT decimals! Trailing zeros do matter. For example,
PHP 5.3.28 is twenty four versions newer (greater than) than PHP 5.3.4.
Please consult https://www.akeebabackup.com/how-do-version-numbers-work.html


Further clarifications:

1. There is no possible way that you are receiving this message in error. We
   are using the PHP_VERSION constant to detect the PHP version you are
   currently using. This is what PHP itself reports as its own version. It
   simply cannot lie.

2. Even though your *site* may be running in a higher PHP version that the one
   reported above, your CRON scripts will most likely not be running under it.
   This has to do with the fact that your site DOES NOT run under the command
   line and there are different executable files (binaries) for the web and
   command line versions of PHP.

3. Please note that we cannot provide support about this error as the solution
   depends only on your server setup. The only people who know how your server
   is set up are your host's technicians. Therefore we can only advise you to
   contact your host and request them the correct path to the PHP CLI binary.
   Let us stress out that only your host knows and can give this information
   to you.

4. The latest published versions of PHP can be found at http://www.php.net/
   Any older version is considered insecure and must not be used on a
   production site. If your server uses a much older version of PHP than those
   published in the URL above please notify your host that their servers are
   insecure and in need of an update.

This script will now terminate. Goodbye.

ENDWARNING;
	die();
}

// Required by the CMS
define('DS', DIRECTORY_SEPARATOR);

/**
 * Timezone fix
 *
 * This piece of code was originally put here because some PHP 5.3 servers forgot to declare a default timezone.
 * Unfortunately it's still required because some hosts STILL forget to provide a timezone in their php.ini files or,
 * worse, use invalid timezone names.
 */
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	$serverTimezone = @date_default_timezone_get();

	// Do I have no timezone set?
	if (empty($serverTimezone) || !is_string($serverTimezone))
	{
		$serverTimezone = 'UTC';
	}

	// Do I have an invalid timezone?
	try
	{
		$testTimeZone = new DateTimeZone($serverTimezone);
	}
	catch (\Exception $e)
	{
		$serverTimezone = 'UTC';
	}

	// Set the default timezone to a correct thing
	@date_default_timezone_set($serverTimezone);
}

// Load Joomla! system defines
if (!isset($curdir))
{
	// I assume I'm located in libraries/fof30/Cli
	$curdir   = __DIR__ . '/../../../cli';
	$realPath = @realpath($curdir);

	if ($realPath !== false)
	{
		$curdir = $realPath;
	}
}

// Restore the error reporting before importing Joomla core code
if (function_exists('error_reporting'))
{
	error_reporting($oldLevel);
}

// Include the Joomla constant override file for the CLI directory (if present)
if (file_exists($curdir . '/defines.php'))
{
	include_once $curdir . '/defines.php';
}

// If no CLI overrides are present, try to load the regular constants from includes/defines.php
if (!defined('_JDEFINES'))
{
	if (!isset($altBasePath))
	{
		$altBasePath  = rtrim($curdir, DIRECTORY_SEPARATOR);
		$lastSlashPos = strrpos($altBasePath, DIRECTORY_SEPARATOR);
		$altBasePath  = substr($altBasePath, 0, $lastSlashPos);
	}

	define('JPATH_BASE', $altBasePath);
	require_once JPATH_BASE . '/includes/defines.php';
}

// Load the legacy Joomla! include files (this should go away in Joomla! 4?)
if (@file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}

// Load the CMS import file (newer Joomla! 3 versions)
$cmsImportPath = JPATH_LIBRARIES . '/cms.php';

if (@file_exists($cmsImportPath))
{
	@include_once $cmsImportPath;
}

// Load requirements for various versions of Joomla!. This should NOT be required since circa Joomla! 3.7.
JLoader::import('joomla.base.object');
JLoader::import('joomla.application.application');
JLoader::import('joomla.application.applicationexception');
JLoader::import('joomla.log.log');
JLoader::import('joomla.registry.registry');
JLoader::import('joomla.filter.input');
JLoader::import('joomla.filter.filterinput');
JLoader::import('joomla.factory');

// Load the Joomla! configuration file to grab database information
JFactory::getConfig(JPATH_CONFIGURATION . '/configuration.php');

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('Cannot load FOF', 500);
}

/**
 * Base class for a Joomla! command line application. Adapted from JCli / JApplicationCli
 */
abstract class FOFCliApplication
{
	/**
	 * The application input object.
	 *
	 * @var    JInput
	 */
	public $input;

	/**
	 * The application configuration object.
	 *
	 * @var    \Joomla\Registry\Registry
	 */
	protected $config;

	/**
	 * The application instance.
	 *
	 * @var    FOFCliApplication
	 */
	protected static $instance;

	/**
	 * POSIX-style CLI options. Access them with through the getOption method.
	 *
	 * @var   array
	 */
	protected static $cliOptions = array();

	/**
	 * Filter object to use.
	 *
	 * @var    JFilterInput
	 */
	protected $filter = null;

	/**
	 * Class constructor.
	 *
	 * @return  void
	 */
	protected function __construct()
	{
		// Some servers only provide a CGI executable. While not ideal for running CLI applications we can make do.
		$this->detectAndWorkAroundCGIMode();

		// Create the input object, used for retrieving options
		$this->input = new JInputCLI();

		// Create the registry with a default namespace of config
		$this->config = new JRegistry;

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->config->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->config->set('execution.timestamp', time());

		// Set the current directory.
		$this->config->set('cwd', getcwd());

		// Create a new JFilterInput
		$this->filter = JFilterInput::getInstance();

		// Parse the POSIX options
		$this->parseOptions();
	}

	/**
	 * Returns a reference to the global application object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked the first time as $cli = FOFCliApplication::getInstance('YourClassName');
	 *
	 * @param   string $name The name of the FOFCliApplication class to instantiate.
	 *
	 * @return  FOFCliApplication  A FOFCliApplication object
	 */
	public static function &getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			if (!class_exists($name) || (!is_subclass_of($name, 'FOFCliApplication')))
			{
				throw new InvalidArgumentException("Unknown FOF CLI application '$name'");
			}

			self::$instance = new $name;
		}

		return self::$instance;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 */
	abstract public function execute();

	/**
	 * Exit the application.
	 *
	 * @param   integer $code Exit code.
	 *
	 * @return  void
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed $data Either an array or object to be loaded into the configuration object.
	 *
	 * @return  void
	 */
	public function loadConfiguration($data)
	{
		// Load the data into the configuration object.
		if (is_array($data))
		{
			$this->config->loadArray($data);

			return;
		}

		if (is_object($data))
		{
			$this->config->loadObject($data);

			return;
		}
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string  $text The text to display.
	 * @param   boolean $nl   True to append a new line at the end of the output string.
	 *
	 * @return  void
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $text . ($nl ? "\n" : null));
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 */
	public function in()
	{
		return rtrim(fread(STDIN, 8192), "\n");
	}

	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 */
	protected function fetchConfigurationData()
	{
		// Set the configuration file name.
		$file = JPATH_BASE . '/configuration.php';

		// Import the configuration file.
		if (!is_file($file))
		{
			return false;
		}

		include_once $file;

		// Instantiate the configuration object.
		if (!class_exists('JConfig'))
		{
			return false;
		}

		$config = new JConfig;

		return $config;
	}

	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param   int    $referencedate Timestamp of the reference date/time
	 * @param   int    $timepointer   Timestamp of the current date/time
	 * @param   string $measureby     Time unit. One of s, m, h, d, or y.
	 * @param   bool   $autotext      Add "ago" / "from now" suffix?
	 *
	 * @return  string
	 */
	protected function timeAgo($referencedate = 0, $timepointer = '', $measureby = '', $autotext = true)
	{
		if ($timepointer == '')
		{
			$timepointer = time();
		}

		// Raw time difference
		$Raw   = $timepointer - $referencedate;
		$Clean = abs($Raw);

		$calcNum = array(
			array('s', 60),
			array('m', 60 * 60),
			array('h', 60 * 60 * 60),
			array('d', 60 * 60 * 60 * 24),
			array('y', 60 * 60 * 60 * 24 * 365),
		);

		$calc = array(
			's' => array(1, 'second'),
			'm' => array(60, 'minute'),
			'h' => array(60 * 60, 'hour'),
			'd' => array(60 * 60 * 24, 'day'),
			'y' => array(60 * 60 * 24 * 365, 'year'),
		);

		if ($measureby == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($Clean <= $calcNum[$i][1])
				{
					$usemeasure = $calcNum[$i][0];
					$i          = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureby;
		}

		$datedifference = floor($Clean / $calc[$usemeasure][0]);

		if ($autotext == true && ($timepointer == time()))
		{
			if ($Raw < 0)
			{
				$prospect = ' from now';
			}
			else
			{
				$prospect = ' ago';
			}
		}
		else
		{
			$prospect = '';
		}

		if ($referencedate != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}

	/**
	 * Formats a number of bytes in human readable format
	 *
	 * @param   int $size The size in bytes to format, e.g. 8254862
	 *
	 * @return  string  The human-readable representation of the byte size, e.g. "7.87 Mb"
	 */
	protected function formatByteSize($size)
	{
		$unit = array('b', 'KB', 'MB', 'GB', 'TB', 'PB');

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
	}

	/**
	 * Returns the current memory usage, formatted
	 *
	 * @return  string
	 */
	protected function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size = memory_get_usage();

			return $this->formatByteSize($size);
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage, formatted
	 *
	 * @return  string
	 */
	protected function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size = memory_get_peak_usage();

			return $this->formatByteSize($size);
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Parses POSIX command line options and sets the self::$cliOptions associative array. Each array item contains
	 * a single dimensional array of values. Arguments without a dash are silently ignored.
	 *
	 * This works much better than JInputCli since it allows you to use all POSIX-valid ways of defining CLI parameters.
	 *
	 * @return  void
	 */
	protected function parseOptions()
	{
		global $argc, $argv;

		// Workaround for PHP-CGI
		if (!isset($argc) && !isset($argv))
		{
			$query = "";

			if (!empty($_GET))
			{
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";

					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			}

			$query = ltrim($query);
			$argv  = explode(' ', $query);
			$argc  = count($argv);
		}

		$currentName = "";
		$options     = array();

		for ($i = 1; $i < $argc; $i++)
		{
			$argument = $argv[$i];

			$value = $argument;

			if (strpos($argument, "-") === 0)
			{
				$argument = ltrim($argument, '-');

				$name  = $argument;
				$value = null;

				if (strstr($argument, '='))
				{
					list($name, $value) = explode('=', $argument, 2);
				}

				$currentName = $name;

				if (!isset($options[$currentName]) || ($options[$currentName] == null))
				{
					$options[$currentName] = array();
				}
			}

			if ((!is_null($value)) && (!is_null($currentName)))
			{
				$key = null;

				if (strstr($value, '='))
				{
					$parts = explode('=', $value, 2);
					$key   = $parts[0];
					$value = $parts[1];
				}

				$values = $options[$currentName];

				if (is_null($values))
				{
					$values = array();
				}

				if (is_null($key))
				{
					array_push($values, $value);
				}
				else
				{
					$values[$key] = $value;
				}

				$options[$currentName] = $values;
			}
		}

		self::$cliOptions = $options;
	}

	/**
	 * Returns the value of a command line option. This does NOT use JInputCLI. You MUST run parseOptions before.
	 *
	 * @param   string $key     The full name of the option, e.g. "foobar"
	 * @param   mixed  $default The default value to return
	 * @param   string $type    Joomla! filter type, e.g. cmd, int, bool and so on.
	 *
	 * @return  mixed  The value of the option
	 */
	protected function getOption($key, $default = null, $type = 'raw')
	{
		// If the key doesn't exist set it to the default value
		if (!array_key_exists($key, self::$cliOptions))
		{
			self::$cliOptions[$key] = is_array($default) ? $default : array($default);
		}

		$type = strtolower($type);

		if ($type == 'array')
		{
			return self::$cliOptions[$key];
		}

		$value = null;

		if (!empty(self::$cliOptions[$key]))
		{
			$value = self::$cliOptions[$key][0];
		}

		return $this->filterVariable($value, $type);
	}

	/**
	 * Filter a variable using JInputFilter
	 *
	 * @param   mixed  $var  The variable to filter
	 * @param   string $type The filter type, default 'cmd'
	 *
	 * @return  mixed  The filtered value
	 */
	protected function filterVariable($var, $type = 'cmd')
	{
		return $this->filter->clean($var, $type);
	}

	/**
	 * Detect if we are running under CGI mode. In this case it populates the global $argv and $argc parameters off the
	 * CGI input ($_GET superglobal).
	 */
	private function detectAndWorkAroundCGIMode()
	{
		$cgiMode = false;

		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$cgiMode = true;
		}

		// Create a JInput object
		if ($cgiMode)
		{
			$query = "";

			if (!empty($_GET))
			{
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";
					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			}

			$query = ltrim($query);

			global $argv, $argc;
			$argv = explode(' ', $query);
			$argc = count($argv);

			$_SERVER['argv'] = $argv;
		}
	}
}

/**
 * A default exception handler. Catches all unhandled exceptions, displays debug information about them and sets the
 * error level to 254.
 *
 * @param   Throwable $ex The Exception / Error being handled
 */
function FOFCliExceptionHandler($ex)
{
	echo "\n\n";
	echo "********** ERROR! **********\n\n";
	echo $ex->getMessage();
	echo "\n\nTechnical information:\n\n";
	echo "Code: " . $ex->getCode() . "\n";
	echo "File: " . $ex->getFile() . "\n";
	echo "Line: " . $ex->getLine() . "\n";
	echo "\nStack Trace:\n\n" . $ex->getTraceAsString();
	echo "\n\n";
	exit(254);
}

/**
 * Timeout handler
 *
 * This function is registered as a shutdown script. If a catchable timeout occurs it will detect it and print a helpful
 * error message instead of just dying cold. The error level is set to 253 in this case.
 *
 * @return  void
 */
function FOFCliTimeoutHandler()
{
	$connection_status = connection_status();

	if ($connection_status == 0)
	{
		// Normal script termination, do not report an error.
		return;
	}

	echo "\n\n";
	echo "********** ERROR! **********\n\n";

	if ($connection_status == 1)
	{
		echo <<< END
The process was aborted on user's request.

This usually means that you pressed CTRL-C to terminate the script (if you're
running it from a terminal / SSH session), or that your host's CRON daemon
aborted the execution of this script.

If you are running this script through a CRON job and saw this message, please
contact your host and request an increase in the timeout limit for CRON jobs.
Moreover you need to ask them to increase the max_execution_time in the
php.ini file or, even better, set it to 0.
END;
	}
	else
	{
		echo <<< END
This script has timed out. As a result, the process has FAILED to complete.

Your host applies a maximum execution time for CRON jobs which is too low for
this script to work properly. Please contact your host and request an increase
in the timeout limit for CRON jobs. Moreover you need to ask them to increase
the max_execution_time in the php.ini file or, even better, set it to 0.
END;


		if (!function_exists('php_ini_loaded_file'))
		{
			echo "\n\n";

			return;
		}

		$ini_location = php_ini_loaded_file();

		echo <<<END
The php.ini file your host will need to modify is located at:
$ini_location
Info for the host: the location above is reported by PHP's php_ini_loaded_file() method.

END;

		echo "\n\n";
		exit(253);
	}
}

/**
 * Error handler. It tries to catch fatal errors and report them in a meaningful way. Obviously it only works for
 * catchable fatal errors. It sets the error level to 252.
 *
 * IMPORTANT! Under PHP 7 the default exception handler will be called instead, including when there is a non-catchable
 *            fatal error.
 *
 * @param   int    $errno   Error number
 * @param   string $errstr  Error string, tells us what went wrong
 * @param   string $errfile Full path to file where the error occurred
 * @param   int    $errline Line number where the error occurred
 *
 * @return  void
 */
function FOFCliErrorHandler($errno, $errstr, $errfile, $errline)
{
	switch ($errno)
	{
		case E_ERROR:
		case E_USER_ERROR:
			echo "\n\n";
			echo "********** ERROR! **********\n\n";
			echo "PHP Fatal Error: $errstr";
			echo "\n\nTechnical information:\n\n";
			echo "File: " . $errfile . "\n";
			echo "Line: " . $errline . "\n";
			echo "\nStack Trace:\n\n" . debug_backtrace();
			echo "\n\n";

			exit(252);
			break;

		default:
			break;
	}
}

set_exception_handler('FOFCliExceptionHandler');
set_error_handler('FOFCliErrorHandler', E_ERROR | E_USER_ERROR);
register_shutdown_function('FOFCliTimeoutHandler');