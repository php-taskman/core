<?php

namespace PhpTaskman\Core\Traits;

/**
 * Class ConfigurationTokensTrait.
 */
trait ConfigurationTokensTrait
{
    /**
     * Extract tokens and replace their values with current configuration.
     *
     * @param string $text
     *
     * @return array
     */
    public function extractProcessedTokens($text)
    {
        /** @var \Robo\Config\Config $config */
        $config = $this->getConfig();

        return \array_map(
            static function ($key) use ($config) {
                return $config->get($key);
            },
            $this->extractRawTokens($text)
        );
    }

    /**
     * Extract token in given text.
     *
     * @param string $text
     *
     * @return array
     */
    private function extractRawTokens($text)
    {
        \preg_match_all('/\$\{(([A-Za-z_\-]+\.?)+)\}/', $text, $matches);

        if (isset($matches[0]) && !empty($matches[0]) && \is_array($matches[0])) {
            if (false !== $return = \array_combine($matches[0], $matches[1])) {
                return $return;
            }
        }

        return [];
    }
}
