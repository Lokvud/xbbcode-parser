<?php

namespace Ermarian\XBBCode\Tree;

/**
 * An output element must be convertible to a string.
 */
interface OutputElementInterface {

  /**
   * Convert to string.
   *
   * @return string
   */
  public function __toString();

}
