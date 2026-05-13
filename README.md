# Fast and extensible Markdown rendering with PHP

`tempest/markdown` is an incredibly fast Markdown parser written in PHP. It's designed to be extensible, and has a bunch of additional features built-in like code highlighting, table and div support, extended markup, and frontmatter support.

> [!IMPORTANT]
> This package is still a work in progress! Feel free to open issues.

## Quickstart

```sh
composer require tempest/markdown
```

Render Markdown like this:

```php
use Tempest\Markdown\Markdown;

$markdown = new Markdown();

$parsed = $markdown->parse(file_get_contents('README.md'));

echo $parsed->frontMatter['title'];
echo $parsed->html;
```

You can read more in [the docs](#TODO).

## Performance

This package began as a challenge to make a more performant Markdown parser in pure PHP. The primary performance gain is from not relying on regex but instead using a simple lexer to tokenize Markdown files and convert them to HTML.

Benchmarks are included in this repo and can be run with
`composer bench` after installing all dev dependencies. Here are the results on my machine for rendering the full Tempest docs:

| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 5.944mb  | 6.281ms       |
| league/commonmark      | 21.114mb | 56.993ms      |
| michelf/php-markdown   | 7.343mb  | 23.215ms      |
| erusev/parsedown-extra | 8.485mb  | 15.163ms      |

