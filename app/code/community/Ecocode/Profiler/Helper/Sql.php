<?php

/**
 * Class Ecocode_Profiler_Helper_Sql
 *
 */
class Ecocode_Profiler_Helper_Sql extends Mage_Core_Helper_Abstract
{
    protected $formattedQueriesCache = [];

    public function replaceQueryParameters($query, array $parameters)
    {
        $i = 0;

        $result = preg_replace_callback(
            '/\?|((?<!:):[a-z0-9_]+)/i',
            function ($matches) use ($parameters, &$i) {
                $key = substr($matches[0], 1);
                if (!is_numeric($key)) {
                    $key = ':' . $key;
                }
                if (!array_key_exists($i, $parameters) && (false === $key || !array_key_exists($key, $parameters))) {
                    return $matches[0];
                }

                $value  = array_key_exists($i, $parameters) ? $parameters[$i] : $parameters[$key];
                $result = Mage::getSingleton('core/resource')->getConnection('default_write')->quote($value);
                $i++;

                return $result;
            },
            $query
        );
        return $result;
    }

    public function dumpParameters(array $parameters)
    {
        if (!$parameters) {
            return '{}';
        }
        if (@!class_exists('\Symfony\Component\Yaml\Dumper')) {
            return '"\Symfony\Component\Yaml\Dumper" is not installed';
        }

        static $dumper;

        if (null === $dumper) {
            $dumper = new \Symfony\Component\Yaml\Dumper();
        }

        return $dumper->dump($parameters, 0, 0, 0);
    }

    public function formatQuery($sql, $highlightOnly = false)
    {
        if (!class_exists('SqlFormatter')) {
            return 'SqlFormatter is not installed';
        }
        $cacheKey = md5($sql . ($highlightOnly ? '1' : 0));
        if (isset($this->formattedQueriesCache[$cacheKey])) {
            return $this->formattedQueriesCache[$cacheKey];
        }
        \SqlFormatter::$cli                       = false;
        \SqlFormatter::$pre_attributes            = 'class="highlight highlight-sql"';
        \SqlFormatter::$quote_attributes          = 'class="string"';
        \SqlFormatter::$backtick_quote_attributes = 'class="string"';
        \SqlFormatter::$reserved_attributes       = 'class="keyword"';
        \SqlFormatter::$boundary_attributes       = 'class="symbol"';
        \SqlFormatter::$number_attributes         = 'class="number"';
        \SqlFormatter::$word_attributes           = 'class="word"';
        \SqlFormatter::$error_attributes          = 'class="error"';
        \SqlFormatter::$comment_attributes        = 'class="comment"';
        \SqlFormatter::$variable_attributes       = 'class="variable"';

        if ($highlightOnly) {
            $html = \SqlFormatter::highlight($sql);
            $html = preg_replace('/<pre class=".*">([^"]*+)<\/pre>/Us', '\1', $html);
        } else {
            $html = \SqlFormatter::format($sql);
            $html = preg_replace('/<pre class="(.*)">([^"]*+)<\/pre>/Us', '<div class="\1"><pre>\2</pre></div>', $html);
        }

        return $this->formattedQueriesCache[$cacheKey] = $html;
    }

}