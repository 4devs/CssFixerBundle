<?php

namespace FDevs\CssFixerBundle\DependencyInjection;

use FDevs\CssFixerBundle\Exception\InvalidBundleException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FDevsCssFixerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $rules = [];
        foreach ($config['rules'] as $rule => $value) {
            $rules[preg_replace('/_/', '-', $rule)] = $value;
        }
        $this->createFixerConfigFile($rules);

        $allBundles = $this->getBundles(
            $container->getParameter('kernel.root_dir') . '/../src',
            array_keys($container->getParameter('kernel.bundles'))
        );

        $this->validateBundles($allBundles, $config['include']);
        $this->validateBundles($allBundles, $config['exclude']);

        $bundlesToCheck = $config['include'] ? $config['include'] : $allBundles;
        $bundlesToCheck = array_diff($bundlesToCheck, $config['exclude']);

        $container->setParameter('f_devs.css_fixer.bundles', $bundlesToCheck);
        $container->setParameter('f_devs.css_fixer.executable', __DIR__ . '/../node_modules/.bin/csscomb');
        $container->setParameter('f_devs.css_fixer.config_path', __DIR__ . '/../.csscomb.json');

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @param array $rules
     * @return string
     */
    private function createFixerConfigFile(array $rules)
    {
        $filesystem = new Filesystem();
        $fixerConfigPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.csscomb.json';
        $filesystem->remove($fixerConfigPath);
        $filesystem->dumpFile($fixerConfigPath, json_encode($rules));

        return $fixerConfigPath;
    }

    /**
     * @param array $allBundles
     * @param array $bundlesToCheck
     * @throws \FDevs\CssFixerBundle\Exception\InvalidBundleException
     */
    private function validateBundles(array $allBundles, array $bundlesToCheck)
    {
        if ($invalidBundles = array_diff($bundlesToCheck, $allBundles)) {
            throw new InvalidBundleException(reset($invalidBundles), $allBundles);
        }
    }

    /**
     * @param string $srcPath Path to folder with vendors
     * @param array $allBundles Registered in app bundles
     * @return array
     */
    private function getBundles($srcPath, array $allBundles)
    {
        $bundles = [];
        $finder = new Finder();
        $finder
            ->directories()
            ->in($srcPath)
            ->depth(0)
        ;

        foreach ($finder as $company) {
            /** @var SplFileInfo $company */
            $bundlesFinder = new Finder();
            $bundlesFinder
                ->directories()
                ->in($company->getPathname())
                ->depth(0)
            ;

            foreach ($bundlesFinder as $bundle) {
                /** @var SplFileInfo $bundle */
                $bundles[] = $company->getBasename() . $bundle->getBasename();
            }
        }

        return array_intersect($allBundles, $bundles);
    }
}
