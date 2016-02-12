<?php

namespace App\Service;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Builder.
 */
class Builder
{
    const OPERATION_SH = 'sh';
    const OPERATION_SCP = 'scp';

    const SKEL_SH = 'sh.sh';
    const SKEL_SH_FUNC = 'sh_function.sh';
    const SKEL_SH_CASE = 'sh_case.sh';
    const SKEL_SH_MAIN = 'sh_main.sh';

    const SKEL_SCP = 'scp.sh';
    const SKEL_SCP_FUNC = 'scp_function.sh';
    const SKEL_SCP_CASE = 'scp_case.sh';
    const SKEL_SCP_MAIN = 'scp_main.sh';

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
    public function build($name, array $configuration)
    {
        $this->buildConfig($name, self::OPERATION_SH, $configuration);
        $this->buildConfig($name, self::OPERATION_SCP, $configuration);
    }

    /**
     * @param string $name
     * @param string $operation
     * @param array  $configuration
     */
    private function buildConfig($name, $operation, array $configuration)
    {
        $upperOperation = strtoupper($operation);

        $path = sprintf('%s/%s.%s', $this->pathBuild, $name, $operation);

        list ($operationSkl, $functionSkl, $caseSkl, $mainSkl) = $this->getSkeletons($operation);

        $content = $operationSkl;
        $contentMain = $mainSkl;
        $contentFunctions = '';
        $contentCases = '';

        foreach ($configuration['connections'] as $connectionName => $connection) {
            list($function, $case) = $this->buildConnection($operation, $connectionName, $connection, $functionSkl, $caseSkl);
            $contentFunctions .= $function;
            $contentCases .= $case;
        }

        $content = str_replace(sprintf('__%s_FUNCTIONS__', $upperOperation), $contentFunctions, $content);
        $contentMain = str_replace('__CASES__', $contentCases, $contentMain);
        $content = str_replace(sprintf('__%s_MAIN__', $upperOperation), $contentMain, $content);

        $this->fileSystem->dumpFile($path, $content);
        $this->fileSystem->chmod($path, 0775);
    }

    /**
     * @param string $operation
     * @param string $name
     * @param array  $connection
     * @param string $functionSkeleton
     * @param string $caseSkeleton
     *
     * @return array
     */
    private function buildConnection($operation, $name, array $connection, $functionSkeleton, $caseSkeleton)
    {
        $function = str_replace(
            ['__FUNCTION_NAME__', '__HOSTNAME__', '__USERNAME__', '__PASSWORD__'],
            [$name, $connection['host'], $connection['username'], $connection['password']],
            $functionSkeleton
        );

        $case = str_replace(
            ['__CONNECTION_NAME__', '__FUNCTION_NAME__'],
            [$name, sprintf('%s_%s', $operation, $name)],
            $caseSkeleton
        );

        return [$function, $case];
    }

    /**
     * @param string $operation
     *
     * @return array
     */
    private function getSkeletons($operation)
    {
        switch ($operation) {
            case self::OPERATION_SH:
                return [
                    $this->getSkeleton(self::SKEL_SH),
                    $this->getSkeleton(self::SKEL_SH_FUNC),
                    $this->getSkeleton(self::SKEL_SH_CASE),
                    $this->getSkeleton(self::SKEL_SH_MAIN)
                ];

            case self::OPERATION_SCP:
                return [
                    $this->getSkeleton(self::SKEL_SCP),
                    $this->getSkeleton(self::SKEL_SCP_FUNC),
                    $this->getSkeleton(self::SKEL_SCP_CASE),
                    $this->getSkeleton(self::SKEL_SCP_MAIN)
                ];
        }

        return [];
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
