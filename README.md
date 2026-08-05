# Fast and extensible Markdown in PHP

`tempest/markdown` is a Markdown parser for server-side Markdown parsing with PHP. It's designed to be fast and extensible, and has a bunch of extended Markdown features built-in like code highlighting, table and div support, responsive images, and frontmatter support.

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

echo $parsed->frontmatter['title'];
echo $parsed->html;
```

You can read more in [the docs](https://tempestphp.com/3.x/packages/markdown).

### Multi-Content

Need to keep multiple content parts together? Separate them with a
`<!-- next -->` marker. The simplest case doesn't even need frontmatter:

```md
First part.
<!-- next -->
Second part.
```

Frontmatter stays optional per part, and naming a part makes it directly
reachable, no need to search for it:

```md
---
title: Pancakes
---
Mix flour, eggs, and milk.

<!-- next: recipe-2 -->
---
title: Waffles
---
Mix flour, eggs, and butter.
```

```php
$chunks = $markdown->parseMany($content);

echo $chunks[0]->frontmatter['title'];          // Pancakes
echo $chunks['recipe-2']->frontmatter['title']; // Waffles
```

This works well for anything naturally made up of independent parts, no
matter where the content comes from: a tiny blog, the slides of a
presentation, page separators in a long document, or a small infrastructure
topology, for instance. Just like `parse()`, `parseMany()` only ever works on
the string you hand it; reading that content from a file, a database, or
anywhere else is entirely up to the caller. A larger example, modeling hosts,
services, and an application with dependencies between them, is available in
[`tests/Fixtures/infrastructure.md`](tests/Fixtures/infrastructure.md).

## Performance

This package began as a challenge to make a more performant Markdown parser in pure PHP. The primary performance gain is from not relying on regex but instead using a simple lexer to tokenize Markdown files and convert them to HTML.

Benchmarks are included in this repo and can be run with
`composer bench` after installing all dev dependencies. Here are the results on my machine for rendering the full Tempest docs:

| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 6.664mb  | 10.906ms      |
| league/commonmark      | 21.114mb | 56.993ms      |
| michelf/php-markdown   | 7.343mb  | 23.215ms      |
| erusev/parsedown-extra | 8.485mb  | 15.163ms      |
