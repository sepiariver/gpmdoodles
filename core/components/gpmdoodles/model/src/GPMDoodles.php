<?php 
namespace SepiaRiver;
use \GuzzleHttp\Client;
use \modX;
use \xPDO;
use \PDO;

/**
 * The main MRAdmin service class.
 *
 * @package gpmdoodles
 */
class GPMDoodles {
    public $modx = null;
    public $namespace = 'gpmdoodles';
    public $options = [];
    public static $version = '0.0.1';
    public $logLevel = modX::LOG_LEVEL_DEBUG;
    public $client = null;

    public function __construct(modX &$modx, array $options = []) {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, 'gpmdoodles');

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/gpmdoodles/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/gpmdoodles/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/gpmdoodles/');

        /* loads some default paths for easier management */
        $this->options = array_merge(array(
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'vendorPath' => $corePath . 'model/vendor/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ), $options);

        // sets up autoload and pkg in modx
        require_once($this->options['vendorPath'] . 'autoload.php');
        $this->modx->addPackage('gpmdoodles', $this->getOption('modelPath'));
        $this->modx->lexicon->load('gpmdoodles:default');
        
        // class variables
        if ($this->getOption('debug')) $this->logLevel = modX::LOG_LEVEL_ERROR;
    }

    /**
     * getClient
     * Return or initialize Guzzle HTTP client
     * 
     * @return null|\GuzzleHttp\Client
     */
    protected function getClient($reInit = false)
    {   
        if ($this->client instanceof Client && !$reInit) {
            return $this->client;
        } 
        $this->client = new Client();
        if (!($this->client instanceof Client)) {
            $this->log('Could not instantiate http client!');
            return null;
        }
        return $this->client;
    }

    /**
     * fetchDoodles
     * Fetches doodles from the API
     * 
     * @return null|array
     */
    public function fetchDoodles() {
        $client = $this->getClient();
        if (!$client) {
            return null;
        }
        $url = $this->getOption('api_url');
        if (!$url) {
            return null;
        }
        $response = $client->get($url);
        return $this->modx->fromJSON((string) $response->getBody());
    }

    /**
     * log
     * Logs according to $this->logLevel and returns an array of log data
     * 
     * @param string $msg Error message to log
     * @param mixed  $data Data to log
     * 
     * @return array $toLog Array of log data 
     */
    public function log(string $msg, $data = '') 
    {
        $level = $this->logLevel;
        $toLog = [];
        // Format log data
        if (is_scalar($data)) {
            $toLog['data'] = (string) $data;
        } elseif (method_exists($data, 'toArray')) {
            $toLog['data'] = $data->toArray();
        } else {
            $toLog['data'] = (array) $data;
        }
        
        // Add stack trace to ERROR level logs
        if ($level === modX::LOG_LEVEL_ERROR) {
            $bt = debug_backtrace(0, 2);
            $toLog['caller'] = $bt[1];
        }

        // Output 
        $json = $this->modx->toJSON($toLog);
        $this->modx->log($level, $msg . PHP_EOL . $json);
        $toLog['message'] = $msg; // exclude from $json above
        return $toLog;
    }

    // UTILITY methods based on theboxer's work //

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
     public function getOption($key = '', $options = [], $default = null)
     {
         $option = $default;
         if (!empty($key) && is_string($key)) {
             if (is_array($options) && array_key_exists($key, $options)) {
                 // Simple array access
                 $option = $options[$key];
             } elseif (is_array($options) && array_key_exists("{$this->namespace}.{$key}", $options)) {
                 // Namespaced properties like formit->config
                 $option = $options["{$this->namespace}.{$key}"];
             } elseif (array_key_exists($key, $this->options)) {
                 // Instance config
                 $option = $this->options[$key];
             } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                 // Namespaced system settings
                 $option = $this->modx->getOption("{$this->namespace}.{$key}");
             }
         }
         return $option;
     }

    /**
     * Transforms a string to an array with removing duplicates and empty values
     *
     * @param $string
     * @param string $delimiter
     * @return array
     */
    public function explodeAndClean($string, $delimiter = ',')
    {
        $string = (string) $string;
        $array = explode($delimiter, $string);    // Explode fields to array
        $array = array_map('trim', $array);       // Trim array's values
        $array = array_keys(array_flip($array));  // Remove duplicate fields
        $array = array_filter($array);            // Remove empty values from array
        return $array;
    }

    /**
     * Processes a chunk or given string
     *
     * @param string $tpl
     * @param array $phs
     * @return string
     */
    public function getChunk($tpl = '', $phs = [])
    {
        if (empty($tpl)) return '';
        if (!is_array($phs)) $phs = [];
        if (strpos($tpl, '@INLINE ') !== false) {
            $content = str_replace('@INLINE', '', $tpl);
            /** @var \modChunk $chunk */
            $chunk = $this->modx->newObject('modChunk', array('name' => 'inline-' . uniqid()));
            $chunk->setCacheable(false);
            return $chunk->process($phs, $content);
        }
        // Not strictly necessary but helpful in common error scenario
        if ($this->modx->getCount('modChunk', ['name' => $tpl]) !== 1) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'MRAdmin: no Chunk with name ' . $tpl);
            return '';
        }
        return $this->modx->getChunk($tpl, $phs);
    }
}