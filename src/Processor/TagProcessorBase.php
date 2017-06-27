<?php

namespace Ermarian\XBBCode\Processor;

use Ermarian\XBBCode\Tree\OutputElement;
use Ermarian\XBBCode\Tree\OutputElementInterface;
use Ermarian\XBBCode\Tree\TagElementInterface;

/**
 * Base tag processor for wrapping the output.
 */
abstract class TagProcessorBase implements TagProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function process(TagElementInterface $tag): OutputElementInterface {
    $output = $this->doProcess($tag);
    if (!($output instanceof OutputElementInterface)) {
      $output = new OutputElement("$output");
    }
    return $output;
  }

  /**
   * Override this function to return any printable value.
   *
   * @param \Ermarian\XBBCode\Tree\TagElementInterface $tag
   *
   * @return mixed
   */
  abstract public function doProcess(TagElementInterface $tag);

}
