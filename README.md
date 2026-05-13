# Fast and extensible Markdown rendering with PHP

`tempest/markdown` is an incredibly fast Markdown parser written in PHP. It's designed to be extensible, and has a bunch of additional features built-in like code highlighting, table and div support, extended markup, and frontmatter support.

## Quickstart

```php
composer require tempest/markdown
```

Render Markdown like this:

```php
use Tempest\Markdown\Markdown;

$markdown = new Markdown();

$parsed = $markdown->render(file_get_contents('README.md'));

echo $parsed->frontMatter['title'];
echo $parsed->html;
```

You can read more in [the docs](#). 