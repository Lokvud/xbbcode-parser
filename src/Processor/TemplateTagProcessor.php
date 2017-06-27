<?php

namespace Ermarian\XBBCode\Processor;

use Ermarian\XBBCode\Tree\TagElementInterface;

/**
 * A very simple template compiler.
 *
 * Placeholders have this syntax:
 *
 * "{" $name "}"
 *
 * The following placeholders are supported.
 *
 * - name: The name of the tag (eg. "size")
 * - argument: Un-parsed argument string (eg. "=small")
 * - option: A scalar option, if set (eg. "small")
 * - attribute.*: A named tag attribute.
 * - content: The rendered content of the tag.
 * - source: The un-rendered source content of the tag.
 *
 * @package Ermarian\XBBCode\Processor
 */
class TemplateTagProcessor extends TagProcessorBase {

  /**
   * @var string[]
   */
  protected $tokens;

  /**
   * TemplateTagProcessor constructor.
   *
   * @param string $template
   */
  public function __construct($template) {
    $this->tokens = static::compile($template);
  }

  /**
   * Shortcut for accessing tag attributes via their string properties.
   *
   * @param \Ermarian\XBBCode\Tree\TagElementInterface $tag
   * @param string $key
   *
   * @return string
   */
  public static function getTagProperty(TagElementInterface $tag,
                                        $key): string {
    switch ($key) {
      case 'name':
        return $tag->getName();
      case 'argument':
        return $tag->getArgument();
      case 'option':
        return $tag->getOption() ?: '';
      case 'content':
        return $tag->getContent();
      case 'source':
        return $tag->getSource();
    }

    return '';
  }

  /**
   * Compile a template string into a processor function.
   *
   * @param \Ermarian\XBBCode\Tree\TagElementInterface $tag
   *
   * @return string
   */
  public function doProcess(TagElementInterface $tag): string {
    $output = [];
    foreach ($this->tokens as $token) {
      $output[] = static::runExpression($tag, $token);
    }
    return implode('', $output);
  }

  /**
   * Process a single expression from the template.
   *
   * @param \Ermarian\XBBCode\Tree\TagElementInterface $tag
   * @param array $token
   *
   * @return mixed|string
   */
  protected static function runExpression(TagElementInterface $tag,
                                          array $token) {
    list($type, $name) = $token;
    switch ($type) {
      case 'property':
        return static::getTagProperty($tag, $name);
      case 'attribute':
        return $tag->getAttribute($name) ?: '';
      case 'literal':
        return $name;
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  protected static function compile($template): array {
    $tokens = preg_split('/{([.\w]+|\\\\[{}])}/',
      $template,
      -1,
      PREG_SPLIT_DELIM_CAPTURE);
    $compiled = [];
    foreach ($tokens as $i => $token) {
      if ($i % 2) {
        if (in_array($token,
          ['argument', 'content', 'name', 'option', 'source'],
          TRUE)) {
          $compiled[] = ['property', $token];
        }
        elseif (preg_match('/^attribute\.(\w+)$', $token, $match)) {
          $compiled[] = ['attribute', $match[1]];
        }
        elseif ($token) {
          $compiled[] = ['literal', stripslashes($token)];
        }
      }
      elseif ($token) {
        $compiled[] = ['literal', $token];
      }
    }
    return $compiled;
  }

}
