<?php

namespace Ermarian\XBBCode;

/**
 * An interface for parsers.
 */
interface ParserInterface {

  /**
   * Parse a text and build an element tree.
   *
   * @param string $text
   *   The source text.
   *
   * @return \Ermarian\XBBCode\ElementInterface
   *   The element representing the root of the tree.
   */
  public function parse($text);

}
