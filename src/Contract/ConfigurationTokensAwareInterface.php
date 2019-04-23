<?php

namespace PhpTaskman\Core\Contract;

/**
 * Interface ConfigurationTokensAwareInterface.
 */
interface ConfigurationTokensAwareInterface
{
    /**
     * Extract tokens and replace their values with current configuration.
     *
     * @param string $text
     *
     * @return array
     */
    public function extractProcessedTokens($text);
}
