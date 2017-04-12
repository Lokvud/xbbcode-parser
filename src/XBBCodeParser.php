<?php

namespace Ermarian\XBBCode;

/**
 * The standard XBBCode parser.
 */
class XBBCodeParser implements ParserInterface {
  const RE_TAG = '/\[(?<closing>\/)(?<name1>[a-z0-9_]+)\]|\[(?<name2>[a-z0-9_]+)(?<arg>(?<attr>(?:\s+(?<key>\w+)=(?:\'(?<val1>(?:[^\\\\\']|\\\\[\\\\\'])*)\'|\"(?<val2>(?:[^\\\\\"]|\\\\[\\\\\"])*)\"|(?=[^\'"\s])(?<val3>(?:[^\\\\\'\"\s\]]|\\\\[\\\\\'\"\s\]])*)))*)|=(?<option>(?:[^\\\\\]]|\\\\[\\\\\]])*))\]/';

  /**
   * The plugins for rendering.
   *
   * @var \Ermarian\XBBCode\TagProcessorInterface[]
   */
  protected $processors;

  /**
   * XBBCodeParser constructor.
   *
   * @param \Ermarian\XBBCode\TagProcessorInterface[]|\Drupal\xbbcode\PluginCollectionInterface $processors
   *   The plugins for rendering.
   */
  public function __construct($processors) {
    $this->processors = $processors;
  }

  /**
   * {@inheritdoc}
   */
  public function parse($text, $prepared = FALSE) {
    $tokens = static::tokenize($text, $this->processors);
    $tokens = static::validateTokens($tokens);
    if ($prepared) {
      $tokens = static::unpackArguments($tokens);
    }
    $tree = static::buildTree($text, $tokens);
    static::decorateTree($tree, $this->processors, $prepared);
    return $tree;
  }

  /**
   * Find the opening and closing tags in a text.
   *
   * @param string $text
   *   The source text.
   * @param array|\ArrayAccess|null $allowed
   *   An array keyed by tag name, with non-empty values for allowed tags.
   *   Omit this argument to allow all tag names.
   *
   * @return array[]
   *   The tokens.
   */
  public static function tokenize($text, $allowed = NULL) {
    // Find all opening and closing tags in the text.
    $matches = [];
    preg_match_all(self::RE_TAG,
                   $text,
                   $matches,
                   PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $tokens = [];

    foreach ($matches as $i => $match) {
      $name = !empty($match['name1'][0]) ? $match['name1'][0] :
        $match['name2'][0];
      if ($allowed && empty($allowed[$name])) {
        continue;
      }

      $start = $match[0][1];
      $tokens[] = [
        'name'     => $name,
        'start'    => $start,
        'end'      => $start + strlen($match[0][0]),
        'arg'      => !empty($match['arg'][0]) ? $match['arg'][0] : NULL,
        'closing'  => !empty($match['closing'][0]),
        'prepared' => FALSE,
      ];
    }

    return $tokens;
  }

  /**
   * Validate the nesting, and remove tokens that are not nested.
   *
   * @param array[] $tokens
   *   The tokens.
   *
   * @return array[]
   *   A well-formed list of tokens.
   */
  public static function validateTokens(array $tokens) {
    // Initialize the counter for each tag name.
    $counter = [];
    foreach ($tokens as $token) {
      $counter[$token['name']] = 0;
    }

    $stack = [];

    foreach ($tokens as $i => $token) {
      if ($token['closing']) {
        if ($counter[$token['name']] > 0) {
          // Pop the stack until a matching token is reached.
          do {
            $last = array_pop($stack);
            $counter[$last['name']]--;
          } while ($last['name'] !== $token['name']);

          $tokens[$last['id']] += [
            'length' => $token['start'] - $last['end'],
            'verified' => TRUE,
          ];

          $tokens[$i]['verified'] = TRUE;
        }
      }
      else {
        // Stack this token together with its position.
        $stack[] = $token + ['id' => $i];
        $counter[$token['name']]++;
      }
    }

    // Filter the tokens.
    return array_filter($tokens, function ($token) {
      return !empty($token['verified']);
    });
  }

  /**
   * Decode the base64-encoded argument of each token.
   *
   * @param array[] $tokens
   *   The tokens.
   *
   * @return array[]
   *   The processed tokens.
   */
  public static function unpackArguments(array $tokens) {
    return array_map(function ($token) {
      $token['arg'] = base64_decode(substr($token['arg'], 1));
      return $token;
    }, $tokens);
  }

  /**
   * Convert a well-formed list of tokens into a tree.
   *
   * @param string $text
   *   The source text.
   * @param array[] $tokens
   *   The tokens.
   *
   * @return \Ermarian\XBBCode\NodeElement
   *   The element representing the tree.
   */
  public static function buildTree($text, array $tokens) {
    /** @var \Ermarian\XBBCode\NodeElement[] $stack */
    $stack = [new RootElement()];

    // Tracks the current position in the text.
    $index = 0;

    foreach ($tokens as $token) {
      // Append any text before the token to the parent.
      $leading = substr($text, $index, $token['start'] - $index);
      if ($leading) {
        end($stack)->append(new TextElement($leading));
      }
      // Advance to the end of the token.
      $index = $token['end'];

      if (!$token['closing']) {
        // Push the element on the stack.
        $stack[] = new TagElement(
          $token['name'],
          $token['arg'],
          substr($text, $token['end'], $token['length'])
        );
      }
      else {
        // Pop the closed element.
        $element = array_pop($stack);
        end($stack)->append($element);
      }
    }

    $final = substr($text, $index);
    if ($final) {
      end($stack)->append(new TextElement($final));
    }

    return array_pop($stack);
  }

  /**
   * Assign processors to the tag elements of a tree.
   *
   * @param \Ermarian\XBBCode\NodeElementInterface $tree
   *   The tree to decorate.
   * @param \Ermarian\XBBCode\TagProcessorInterface[] $processors
   *   The processors, keyed by name.
   * @param bool $prepared
   *   TRUE if the text was already prepared once.
   */
  public static function decorateTree(NodeElementInterface $tree,
                                      array $processors,
                                      $prepared = FALSE) {
    foreach ($tree->getDescendants() as $element) {
      if ($element instanceof TagElementInterface) {
        $element->setProcessor($processors[$element->getName()]);
        $element->setPrepared($prepared);
      }
    }
  }

}
