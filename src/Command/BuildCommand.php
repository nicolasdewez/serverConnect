<?php

namespace App\Command;

use App\Service\Builder;
use App\Service\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand.
 */
class BuildCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build scripts')
            ->addArgument('config', InputArgument::REQUIRED, 'Config file name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $nameConfig = $input->getArgument('config');

        $config = new Config();
        $configuration = $config->process($nameConfig);

        $builder = new Builder();
        $builder->build($nameConfig, $configuration);
    }
}
