<?php

namespace Ermarian\XBBCode\Processor;

use Ermarian\XBBCode\Tree\TagElementInterface;

/**
 * A processor that uses a twig template.
 */
class TwigTagProcessor extends TagProcessorBase {

  /**
   * @var \Twig_TemplateWrapper
   */
  protected $template;

  public function __construct(\Twig_TemplateWrapper $template) {
    $this->template = $template;
  }

  /**
   * {@inheritdoc}
   */
  public function doProcess(TagElementInterface $tag) {
    return $this->template->render(['tag' => $tag]);
  }
}
