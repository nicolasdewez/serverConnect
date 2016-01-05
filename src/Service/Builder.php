<?php

namespace App\Service;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Builder.
 */
class Builder
{
    const SKEL_SH = 'sh.sh';
    const SKEL_SH_FUNC = 'sh_function.sh';
    const SKEL_SH_CASE = 'sh_case.sh';
    const SKEL_SH_MAIN = 'sh_main.sh';

    /** @var string */
    private $pathBuild;

    /** @var string */
    private $pathSkeleton;

    /** @var Filesystem */
    private $fileSystem;

    public function __construct()
    {
        $this->pathBuild = __DIR__.'/../../build';
        $this->pathSkeleton = __DIR__.'/../../skeleton';

        $this->fileSystem = new Filesystem();
    }

    /**
     * @param string $name
     * @param array  $configuration
     */
    public function buildConfig($name, array $configuration)
    {
        $path = sprintf('%s/%s.sh', $this->pathBuild, $name);

        $shSkeleton = $this->getSkeleton(self::SKEL_SH);
        $functionSkeleton = $this->getSkeleton(self::SKEL_SH_FUNC);
        $caseSkeleton = $this->getSkeleton(self::SKEL_SH_CASE);
        $mainSkeleton = $this->getSkeleton(self::SKEL_SH_MAIN);

        $content = $shSkeleton;
        $contentMain = $mainSkeleton;
        $contentFunctions = '';
        $contentCases = '';

        foreach ($configuration['connections'] as $connectionName => $connection) {
            list($function, $case) = $this->buildConnection($connectionName, $connection, $functionSkeleton, $caseSkeleton);
            $contentFunctions .= $function;
            $contentCases .= $case;
        }

        $content = str_replace('__SH_FUNCTIONS__', $contentFunctions, $content);
        $contentMain = str_replace('__CASES__', $contentCases, $contentMain);
        $content = str_replace('__SH_MAIN__', $contentMain, $content);

        // Save file
        $this->fileSystem->dumpFile($path, $content);
        $this->fileSystem->chmod($path, 0775);
    }

    private function buildConnection($name, array $connection, $functionSkeleton, $caseSkeleton)
    {
        $function = str_replace(
            ['__FUNCTION_NAME__', '__HOSTNAME__', '__USERNAME__', '__PASSWORD__'],
            [$name, $connection['host'], $connection['username'], $connection['password']],
            $functionSkeleton
        );

        $case = str_replace(
            ['__CONNECTION_NAME__', '__FUNCTION_NAME__'],
            [$name, 'sh_'.$name],
            $caseSkeleton
        );

        return [$function, $case];
    }

    /**
     * @param string $skeleton
     *
     * @return string
     */
    private function getSkeleton($skeleton)
    {
        $path = sprintf('%s/%s', $this->pathSkeleton, $skeleton);
        if (!$this->fileSystem->exists($path)) {
            throw new RuntimeException(sprintf('Skeleton %s missing', $skeleton));
        }

        return file_get_contents($path);
    }
}
