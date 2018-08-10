<?php
namespace GarethMidwood\ApacheHousekeeper\Config;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Config
{
    const CONFIG_FILE = 'ahousekeeper.yml';

    /**
     * @var array
     */
    private $_config = array();

    /**
     * @param Filesystem $fs
     * @return null
     */
    public function __construct(ConsoleOutput $output)
    {
        $this->loadConfig($output);
    }

    private function loadConfig(ConsoleOutput $output)
    {
        $this->loadConfigFile(
            $output,
            self::CONFIG_FILE,
            'Config file ' . self::CONFIG_FILE . ' not found.'
        );
    }

    private function loadConfigFile(ConsoleOutput $output, $file, $error)
    {
        if (!file_exists($file)) {
            throw new \Exception(self::CONFIG_FILE . ' file is missing');
        }

        $config = Yaml::parse(file_get_contents($file));

        $this->_config = $config;
    }

    /**
     * Gets a value from the config
     * @param string $key 
     * @param mixed|bool $defaultValue 
     * @return mixed|bool
     */
    public function get($key, $defaultValue = false)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        return $defaultValue;        
    }
}
