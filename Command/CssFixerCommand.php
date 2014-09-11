<?php

namespace FDevs\CssFixerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class CssFixerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fdevs:cs:css-fixer')
            ->setDescription('Coding style formatter for CSS')
            ->setAliases(['code-style:css-fixer'])
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'If set, then files will be fixed, not just validated')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()->getKernel();
        $bundleNames = $this->getContainer()->getParameter('f_devs.css_fixer.bundles');
        $paths = [];

        foreach ($bundleNames as $name) {
            $bundle = $kernel->getBundle($name);
            $paths[] = $bundle->getPath() . '/Resources/public/css';
        }

        foreach ($paths as $id => $path) {
            if (!is_dir($path)) {
                unset($paths[$id]);
            }
        }

        $exitCode = 0;
        if ($paths) {
            $output->writeln('Starting CssFixer');
            $executable = $this->getContainer()->getParameter('f_devs.css_fixer.executable');
            $options = ['-c ' . $this->getContainer()->getParameter('f_devs.css_fixer.config_path')];

            if ($input->getOption('verbose')) {
                $options[] = '-v';
            }

            if (!$input->getOption('fix')) {
                $options[] = '-l';
            }

            $process = new Process($executable . ' ' . implode(' ', $options) . ' ' . implode(' ', $paths));
            $process->run();

            if ($input->getOption('verbose')) {
                $output->writeln($process->getOutput());
            }
            $exitCode = $process->getExitCode();
        } else {
            $output->writeln('Nothing to process');
        }

        $output->writeln('Finished');

        return $exitCode;
    }
}
