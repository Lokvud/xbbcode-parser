<?php

namespace Ermarian\XBBCode\Tree;

/**
 * An element in the parser tree.
 */
interface ElementInterface {

  /**
   * Render this element to a string.
   *
   * @return string
   *   The rendered output.
   */
  public function render();

}
