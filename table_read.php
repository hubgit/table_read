<?php

/**
 * @param string $path     The path to the HTML file to be read
 * @param string $selector An XPath selector for the table
 *
 * @return Generator
 */
function table_read($path, $selector = '//table')
{
    $doc = new DOMDocument;

    // read the file
    libxml_use_internal_errors(true);
    $doc->loadHTMLFile($path);
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($doc);

    // select the table
    $table = $xpath->query($selector)->item(0);
    
    // pre-process each row
    foreach ($xpath->query('tr', $table) as $row) {
      // extend rowspans
      foreach ($xpath->query('*[@rowspan]', $row) as $cell) {
        $rowspan = $cell->getAttribute('rowspan');
        $cell->removeAttribute('rowspan');

        $nodes = $xpath->query(sprintf(
          'following-sibling::tr[position() < %d]/*[%d]',
          $rowspan,
          $xpath->evaluate('count(preceding-sibling::*)', $cell) + 1
        ), $row);

        foreach ($nodes as $node) {
          $clone = $cell->cloneNode(true);
          $node->parentNode->insertBefore($clone, $node);
        }
      }

      // extend colspans
      foreach ($xpath->query('*[@colspan]', $row) as $cell) {
        $colspan = $cell->getAttribute('colspan');
        $cell->removeAttribute('colspan');

        while (--$colspan) {
          $clone = $cell->cloneNode(true);
          $cell->parentNode->insertBefore($clone, $cell->nextSibling);
        }
      }
    }

    // read keys from the table header
    $keys = array_map(function ($node) {
        return trim($node->textContent);
    }, iterator_to_array($xpath->query('thead/tr/th', $table)));

    // count the expected number of columns
    $count = count($keys);

    // parse each row of the table body
    foreach ($xpath->query('tbody/tr', $table) as $row) {
        // read data from the table body
        $values = array_map(function ($node) {
            return trim($node->textContent);
        }, iterator_to_array($xpath->query('td', $row)));

        // ensure that each column has a value
        $values = array_pad($values, $count, null);

        // yield an associative array
        yield array_combine($keys, $values);
    }
}
