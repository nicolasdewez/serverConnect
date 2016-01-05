<?php

namespace App\Service;

use App\Configuration\ConnectionConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

/**
 * Class Config.
 */
class Config
{
    /** @var string */
    private $pathConfig;

    public function __construct()
    {
        $this->pathConfig = __DIR__.'/../../config';
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function process($name)
    {
        $config = $this->read($name);

        $processor = new Processor();
        $configuration = new ConnectionConfiguration();

        return $processor->processConfiguration(
            $configuration,
            $config
        );
    }

    /**
     * @param string $name
     *
     * @return array
     */
    private function read($name)
    {
        $fs = new Filesystem();
        $path = sprintf('%s/%s.yml', $this->pathConfig, $name);

        if (!$fs->exists($path)) {
            throw new InvalidArgumentException(sprintf('Config file %s not found', $name));
        }

        $parser = new Parser();

        return $parser->parse(file_get_contents($path));
    }
}
