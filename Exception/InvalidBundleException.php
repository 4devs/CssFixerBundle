<?php

namespace FDevs\CssFixerBundle\Exception;

class InvalidBundleException extends \LogicException
{
    /**
     * @param string $bundle
     * @param array $enabled
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($bundle, array $enabled, $code = 0, \Exception $previous = null)
    {
        $message = sprintf(
            'You used bundle `%s`, but need to use one from your own bundles: %s',
            $bundle,
            implode(', ', $enabled)
        );

        parent::__construct($message, $code, $previous);
    }
}
