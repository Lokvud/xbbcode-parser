<?php

namespace Ermarian\XBBCode\Tree;

/**
 * Interface for node elements.
 */
interface NodeElementInterface extends ElementInterface {

  /**
   * Append an element to the children of this element.
   *
   * @param \Ermarian\XBBCode\Tree\ElementInterface $element
   *   The new element.
   */
  public function append(ElementInterface $element);

  /**
   * @return \Ermarian\XBBCode\Tree\ElementInterface[]
   */
  public function getChildren();

  /**
   * Retrieve the rendered content of the element.
   *
   * @return string
   *   The rendered content.
   */
  public function getContent();

  /**
   * @return \Ermarian\XBBCode\Tree\OutputElementInterface[]
   */
  public function getRenderedChildren();

  /**
   * Retrieve the descendants of the node.
   *
   * @return \Ermarian\XBBCode\Tree\ElementInterface[]
   *   Every descendant of the node.
   */
  public function getDescendants();

}
