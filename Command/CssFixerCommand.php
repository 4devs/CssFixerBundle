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
            $executable = $this->getContainer()->getParameter('f_devs.css_fixer.executable');
            $options = [
                '-c ' . $this->getContainer()->getParameter('f_devs.css_fixer.config_path'),
                '-v'
            ];
            $fixerMode = 'fix';

            if (!$input->getOption('fix')) {
                $options[] = '-l';
                $fixerMode = 'lint';
            }

            $output->writeln(sprintf('Starting CssFixer in <info>%s</info> mode', $fixerMode));
            $process = new Process($executable . ' ' . implode(' ', $options) . ' ' . implode(' ', $paths));
            $process->run();
            $summary = $this->parseFixerOutput($process->getOutput());

            if ($input->getOption('verbose')) {
                $output->writeln(implode("\n", $summary['files']));
            }

            $output->writeln(sprintf('Processed files: %d', count($summary['files'])));
            $output->writeln(sprintf('Bad files: %d', $summary['bad']));
            $output->writeln(sprintf('Fixed files: %d', $summary['fixed']));

            if ($input->getOption('verbose')) {
                $output->writeln(sprintf('Time spent: %s', $summary['time']));
            }

            $exitCode = $process->getExitCode();
        } else {
            $output->writeln('Nothing to process');
        }

        $output->writeln('Finished');

        return $exitCode;
    }

    /**
     * @param string $fixerOutput
     * @return array
     */
    private function parseFixerOutput($fixerOutput)
    {
        $files = [];
        $bad = 0;
        $good = 0;
        $fixed = 0;
        $time = '0ms';
        $lines = preg_split('/\n/', $fixerOutput);

        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }

            if (preg_match('/\.css$/', $line)) {
                $files[] = $line;
                if (preg_match('/^!/', $line)) {
                    $bad += 1;
                } else {
                    $good += 1;
                }
            }
        }

        if (preg_match("/(\d+) files? fixed+/", $fixerOutput, $match)) {
            $fixed = $match[1];
        }

        if (preg_match("/spent (\d+\w+)/", $fixerOutput, $match)) {
            $time = $match[1];
        }

        return [
            'files' => $files,
            'fixed' => $fixed,
            'bad' => $fixed ?: $bad,
            'time' => $time,
        ];
    }
}
