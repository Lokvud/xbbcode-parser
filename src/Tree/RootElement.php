<?php

namespace Ermarian\XBBCode\Tree;

/**
 * The root element of the tag tree.
 */
class RootElement extends NodeElement {

  /**
   * {@inheritdoc}
   */
  public function render(): OutputElementInterface {
    return new OutputElement($this->getContent());
  }

}
