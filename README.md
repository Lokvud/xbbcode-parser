# XBBCode

This is a parser for the BBCode markup language, supporting arbitrary rendering
plugins for every tag.

## Installation

Add the library to your composer dependencies as follows:

`composer require ermarian/xbbcode`

## Usage

The parser can be invoked as follows:

```php
<?php

use \Ermarian\XBBCode\Processor\CallbackTagProcessor;
use \Ermarian\XBBCode\Processor\TemplateTagProcessor;
use \Ermarian\XBBCode\Tree\TagElementInterface;
use \Ermarian\XBBCode\XBBCodeParser;

$parser = new XBBCodeParser([
  'b' => new TemplateTagProcessor('<strong>{content}</content>'),
  'url' => new CallbackTagProcessor(function(TagElementInterface $tag) {
    $url = htmlspecialchars($tag->getOption());
    return '<a href="' . $url . '>' . $tag->getContent() . '</a>';
  }),
]);

print $parser->parse('[b]Hello [url=http://example.com]world[/url]![/b]')->render();

?>
```

```html
<strong>Hello <a href="http://example.com">world</a>!</strong>
```

More powerful processor plugins can simply extend `TagProcessorBase` and
implement `doProcess` to perform their own rendering.

## Syntax

The syntax of BBCode used here is as follows:

    text = { VCHAR / LWSP / element1 /.../ elementN }

Where every `elementN` takes the following form (for a specific value of
`$name` that contains only lowercase alphanumeric characters and underscores)

    elementN = "[$name" argument "]" text "[/$name]"
    argument = option / { WSP attribute }
    option = "=" option-value
    attribute = name "=" attribute-value
    name = { ALPHA | DIGIT | "_" }

The `option-value` and `attribute-value` strings must be quoted or escape
terminating delimiters (spaces and `]`) with backslashes.

Tags must be correctly nested, and are otherwise skipped. For example, in
the input `[b][i][/b][/i]`, only the `[b]` tag will be parsed.
