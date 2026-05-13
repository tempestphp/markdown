---
title: Introduction
description: "Tempest is a framework for PHP development, designed to get out of your way. Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework."
---

Tempest's goal is to make you more productive when building web and console apps in PHP. It handles all the boilerplate parts of such projects for you, so that you can focus on what matters the most: writing application code.

People using Tempest say it's the sweet spot between the robustness of Symfony and the eloquence of Laravel. It feels lightweight and close to vanilla PHP; and yet powerful and feature-rich. On this page, you'll read what sets Tempest apart as a framework for modern PHP development. If you're already convinced, you can head over to the [installation page](../0-getting-started/02-installation.md) and get started with Tempest.

## Vision

Tempest's vision can be summarized like this: **it's a community-driven, modern PHP framework that gets out of your way and dares to think outside the box**. Let's dissect that vision in depth.

### Community driven

Tempest started out as an educational project, without the intention for it to be something real. People picked up on it, though, and it was only after a strong community had formed that we considered making it something real.

Currently, there are three core members dedicating a lot of their time to Tempest, as well as over [50 additional contributors](https://github.com/tempestphp/tempest-framework). We have an active [Discord server](/discord) with close to 400 members.

Tempest isn't a solo project and never has been. It is a new framework and has a way to go compared to Symfony or Laravel, but there already is significant momentum and will only keep growing.

### Embracing modern PHP

The benefit of starting from scratch like Tempest did is having a clean slate. Tempest embraced modern PHP features from the start, and its goal is to keep doing this in the future by shipping built-in upgraders whenever breaking changes happen (think of it as Laravel Shift, but built into the framework).

Just to name a couple of examples, Tempest uses property hooks:

```php
interface MigratesUp
{
    public string $name {
        get;
    }

    public function up(): QueryStatement;
}
```

Attributes:

```php
final class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }
}
```

Proxy objects:

```php
use Tempest\Container\Proxy;

final readonly class BookController
{
    public function __construct(
        #[Proxy] private SlowDependency $slowDependency,
    ) { /* … */ }
}
```

And a lot more.

### Getting out of your way

A core part of Tempest's philosophy is that it wants to "get out of your way" as best as possible. For starters, Tempest is designed to structure your project code however you want, without making any assumptions or forcing conventions on you. You can prefer a classic MVC application, DDD or hexagonal design, microservices, or something else; Tempest works with any project structure out of the box without any configuration.

Behind Tempest's flexibility is one of its most powerful features: [discovery](../internals/discovery). Discovery gives Tempest a great number of insights into your codebase, without any handholding. Discovery handles routing, console commands, view components, event listeners, command handlers, middleware, schedules, migrations, and more.

```php
final class ConsoleCommandDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ConsoleConfig $consoleConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            if ($consoleCommand = $method->getAttribute(ConsoleCommand::class)) {
                $this->discoveryItems->add($location, [$method, $consoleCommand]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $consoleCommand]) {
            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }
}
```

Discovery makes Tempest truly understand your codebase so that you don't have to explain the framework how to use it. Of course, discovery is heavily optimized for local development and entirely cached in production, so there's no performance overhead. Even better: discovery isn't just a core framework feature, you're encouraged to write your own project-specific discovery classes wherever they make sense. That's the Tempest way.

:::info
Read the [getting started with discovery](/blog/discovery-explained) guide if you are new to Tempest.
:::

Besides Discovery, Tempest is designed to be extensible. You'll find that any part of the framework can be replaced and hooked into by implementing an interface and plugging it into the container. No fighting the framework, Tempest gets out of your way.

```php
use Tempest\View\ViewRenderer;

$container->singleton(ViewRenderer::class, $myCustomViewRenderer);
```

### Thinking outside the box

Finally, since Tempest originated as an educational project, many Tempest features dare to rethink the things we've gotten used to. For example, [console commands](../1-essentials/04-console-commands), which in Tempest are designed to be very similar to controller actions:

```php
final readonly class BooksCommand
{
    use HasConsole;
    
    public function __construct(
        private BookRepository $repository,
    ) {}
    
    #[ConsoleCommand]
    public function find(?string $initial = null): void
    {
        $book = $this->search(
            'Find your book',
            $this->repository->find(...),
        );
    }

    #[ConsoleCommand(middleware: [CautionMiddleware::class])]
    public function delete(string $title, bool $verbose = false): void 
    { /* … */ }
}
```

Or what about [Tempest's ORM](../1-essentials/03-database), which aims to have truly decoupled models:

```php
use Tempest\Validation\Rules\HasLength;
use App\Author;

final class Book
{
    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

```php
final class BookRepository
{
    public function findById(int $id): Book
    {
        return query(Book::class)
            ->select()
            ->with('chapters', 'author')
            ->where('id = ?', $id)
            ->first();
    }
}
```

Then there's our view engine, which embraces the most original template engine of all time: HTML;

```html
<x-base :title="$this->seo->title">
    <ul>
        <li :foreach="$this->books as $book">
            {{ $book->title }}

            <span :if="$this->showDate($book)">
                <x-tag>
                    {{ $book->publishedAt }}
                </x-tag>
            </span>
        </li>
    </ul>
</x-base>
```

## Getting started

Are you intrigued? Want to give Tempest a try? Head over to [the next chapter](../0-getting-started/02-installation.md) to learn about how to get started with Tempest.

If you want to become part of our community, you're more than welcome to [join our Discord server](/discord), and to check out [Tempest on GitHub](https://github.com/tempestphp/tempest-framework).

Enjoy!


---
title: Installation
description: Tempest can be installed as a standalone PHP project, as well as a package within existing projects. The framework modules can also be installed individually, including in projects built on other frameworks.
---

## Prerequisites

Tempest requires PHP [8.5+](https://www.php.net/downloads.php) and [Composer](https://getcomposer.org/) to be installed. Optionally, you may install either [Bun](https://bun.sh) or [Node](https://nodejs.org) if you chose to bundle front-end assets.

For a better experience, it is recommended to have a complete development environment, such as [ServBay](https://www.servbay.com), [Herd](https://herd.laravel.com/docs), or [Valet](https://laravel.com/docs/valet). However, Tempest can serve applications using PHP's built-in server just fine.

Once the prerequisites are installed, you can chose your installation method. Tempest can be a [standalone application](#creating-a-tempest-application), or be added [in an existing project](#tempest-as-a-package)—even one built on top of another framework.

## Creating a Tempest application

To get started with a new Tempest project, you may use {`tempest/app`} as the starting point. The `composer create-project` command will scaffold it for you:

```sh
{:hl-keyword:composer:} create-project tempest/app {:hl-type:my-app:}
{:hl-keyword:cd:} {:hl-type:my-app:}
```

If you have a dedicated development environment, you may then access your application by opening `{txt}https://my-app.test` in your browser. Otherwise, you may use PHP's built-in server:

```sh
{:hl-keyword:php:} tempest serve
{:hl-comment:PHP 8.5.1 Development Server (http://localhost:8000) started:}
```

### Scaffolding front-end assets

Optionally, you may install a basic front-end scaffolding that includes [Vite](https://vite.dev/) and [Tailwind CSS](https://tailwindcss.com/). To do so, run the Vite installer and follow through the wizard:

```sh
{:hl-keyword:php:} tempest install vite --tailwind
```

The assets created by this wizard, `main.entrypoint.ts` and `main.entrypoint.css`, are automatically discovered by Tempest. You can serve them using the [`<x-vite-tags />`](../1-essentials/03-views#x-vite-tags) component in your templates.

You may then [run the front-end development server](../2-features/02-asset-bundling.md#running-the-development-server), which will serve your assets on-the-fly:

```bash
{:hl-keyword:npm:} run dev
```

## Tempest as a package

If you already have a project, you can opt to install {`tempest/framework`} as a standalone package. You could do this in any project; it could already contain code, or it could be an empty project.

```sh
{:hl-keyword:composer:} require tempest/framework
```

Installing Tempest this way will give you access to the Tempest console, `./vendor/bin/tempest`. Optionally, you can choose to install Tempest's entry points in your project. To do so, you may run the framework installer:

```txt
{:hl-keyword:./vendor/bin/tempest:} install framework
```

This installer will prompt you to install the following files into your project:

- `public/index.php` — the web application entry point
- `tempest` – the console application entry point
- `.env.example` – a clean example of a `.env` file
- `.env` – the real environment file for your local installation

You can choose which files you want to install, and you can always rerun the `install` command at a later point in time.

## Project structure

Tempest won't impose any file structure on you: one of its core features is that it will scan all project and package code for you, and will automatically discover any files the framework needs to know about.

For instance, Tempest is able to differentiate between a controller method and a console command by looking at the code, instead of relying on naming conventions or configuration files.

:::info
This concept is called [discovery](../1-essentials/05-discovery), and is one of Tempest's most powerful features.
:::

The following project structures work the same way in Tempest, without requiring any specific configuration:

```txt
.                                    .
└── src                              └── src
    ├── Authors                          ├── Controllers
    │   ├── Author.php                   │   ├── AuthorController.php
    │   ├── AuthorController.php         │   └── BookController.php
    │   └── authors.view.php             ├── Models
    ├── Books                            │   ├── Author.php
    │   ├── Book.php                     │   ├── Book.php
    │   ├── BookController.php           │   └── Chapter.php
    │   ├── Chapter.php                  ├── Services
    │   └── books.view.php               │   └── PublisherGateway.php
    ├── Publishers                       └── Views
    │   └── PublisherGateway.php             ├── authors.view.php
    └── Support                              ├── books.view.php
        └── x-base.view.php                  └── x-base.view.php
```

## About discovery

Discovery works by scanning your project code and looking at each file and method individually to determine what that code does. In production environments, [Tempest caches the discovery process](../1-essentials/05-discovery#discovery-in-production), avoiding any performance overhead.

As an example, Tempest is able to determine which methods are controller methods based on their [route attributes](../1-essentials/01-routing.md), or to detect console commands based on methods annotated with {b`#[Tempest\Console\ConsoleCommand]`}:

:::code-group

```php app/BlogPostController.php
use Tempest\Router\Get;
use Tempest\Http\Response;
use Tempest\View\View;

final readonly class BlogPostController
{
    #[Get('/blog')]
    public function index(): View
    { /* … */ }

    #[Get('/blog/{post}')]
    public function show(Post $post): Response
    { /* … */ }
}
```

```php app/RssSyncCommand.php
use Tempest\Console\HasConsole;
use Tempest\Console\ConsoleCommand;

final readonly class RssSyncCommand
{
    #[ConsoleCommand('rss:sync')]
    public function __invoke(bool $force = false): void
    {
        // …
    }
}
```

:::

:::tip{tabler:link}
Learn more about discovery in the [dedicated documentation](../1-essentials/05-discovery.md).
:::


---
title: "Routing"
description: "Learn how to route requests to controllers. In Tempest, this is done using attributes, which are automatically discovered by the framework."
---

## Overview

In Tempest, routes can be associated with any class method. This is typically done in dedicated controller classes, but any class can be used.

Tempest provides attributes, named after HTTP verbs, to attach URIs to controller actions. These attributes implement the {b`Tempest\Router\Route`} interface, allowing custom route attributes to be created.

```php app/HomeController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('./home.view.php');
    }
}
```

Out of the box, an attribute for every HTTP verb is available: {b`Tempest\Router\Get`}, {b`Tempest\Router\Post`}, {b`Tempest\Router\Delete`}, {b`Tempest\Router\Put`}, {b`Tempest\Router\Patch`}, {b`Tempest\Router\Options`}, {b`Tempest\Router\Connect`}, {b`Tempest\Router\Trace`} and {b`Tempest\Router\Head`}.

## Route parameters

Dynamic segments can be defined in route URIs by wrapping them in curly braces. The segment name inside the braces is passed as a parameter to the controller method.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{id}')]
    public function show(int $id): View
    {
        // Fetch the aircraft by ID
        $aircraft = $this->aircraftRepository->getAircraftById($id);

        // Pass the aircraft to the view
        return view('./aircraft.view.php', aircraft: $aircraft);
    }
}
```

### Optional parameters

A route can match both with and without a parameter. For instance, `/aircraft` can show all aircraft, while `/aircraft/123` shows a specific aircraft. This is achieved by marking route parameters as optional.

To mark a parameter as optional, prefix it with a question mark `?` inside the curly braces. The corresponding method parameter must either be nullable or have a default value.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{?id}')]
    public function index(?string $id): View
    {
        if ($id === null) {
            $aircraft = $this->aircraftRepository->all();
        } else {
            $aircraft = $this->aircraftRepository->find($id);
        }

        return view('aircraft.view.php', aircraft: $aircraft);
    }
}
```

In this example, both `/aircraft` and `/aircraft/123` match the same route. When the parameter is not provided, the method parameter receives `null`.

Alternatively, a default value can be provided instead of using a nullable type:

```php app/AircraftController.php
#[Get(uri: '/aircraft/{?type}')]
public function filter(string $type = 'all'): View
{
    // $type defaults to 'all' when not provided
    // $type is set to the provided value otherwise
}
```

Required and optional parameters can be combined. Optional parameters must come after required ones:

```php app/FlightController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class FlightController
{
    #[Get(uri: '/flights/{flightNumber}/{?segment}')]
    public function show(string $flightNumber, ?string $segment): View
    {
        // Matches both /flights/JFA123 and /flights/JFA123/departure
    }
}
```

Multiple optional parameters are also supported:

```php app/AircraftController.php
#[Get(uri: '/aircraft/{?manufacturer}/{?model}')]
public function search(?string $manufacturer, ?string $model): View
{
    // Matches /aircraft, /aircraft/pilatus, and /aircraft/pilatus/pc24
}
```

Optional parameters work with [regular expression constraints](#regular-expression-constraints). Add the regular expression after the parameter name:

```php app/AircraftController.php
#[Get(uri: '/aircraft/{?id:\d+}')]
public function show(?int $id): View
{
    // Matches /aircraft and /aircraft/123 (numeric only)
}
```

### Regular expression constraints

The format of a route parameter can be constrained by specifying a regular expression after its name.

For instance, to accept only numeric identifiers for an `id` parameter, use the following dynamic segment: `{regex}{id:[0-9]+}`. In practice, a route looks like this:

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{id:[0-9]+}')]
    public function showAircraft(int $id): View
    {
        // …
    }
}
```

### Route binding

Controller actions can receive objects instead of scalar values such as identifiers. This is particularly useful for [models](./03-database.md#models) to avoid writing fetching logic in each controller.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\Http\Response;
use App\Aircraft;

final class AircraftController
{
    #[Get('/aircraft/{aircraft}')]
    public function show(Aircraft $aircraft): Response { /* … */ }
}
```

Route binding can be enabled for any class that implements the {b`Tempest\Router\Bindable`} interface, which requires a static `resolve()` method responsible for returning the correct instance.

```php
use Tempest\Router\Bindable;
use Tempest\Database\IsDatabaseModel;

final class Aircraft implements Bindable
{
    public static function resolve(string $input): ?static
    {
        return query(self::class)->resolve($input);
    }
}
```

By default, {b`Tempest\Router\Bindable`} objects are cast to strings when passed into the {b`Tempest\Router\uri()`} function as a route parameter. This means that these objects should implement `Stringable`.

This default behaviour can be overridden by annotating a public property on the object with the {b`\Tempest\Router\IsBindingValue`} attribute:

:::code-group

```php app/Aircraft.php
use Tempest\Router\Bindable;
use Tempest\Router\IsBindingValue;

final class Aircraft implements Bindable
{
    #[IsBindingValue]
    public string $registrationNumber;

    public static function resolve(string $input): ?static
    {
        return query(self::class)
            ->where('registrationNumber', $input)
            ->first();
    }
}
```

```php "URI generation"
uri(ShowAircraftController::class, aircraft: $aircraft);
// → /aircraft/lxjfa
```

:::

### Backed enum binding

String-backed enumerations can be injected into controller actions. Tempest maps the corresponding parameter from the URI to an instance of that enum using the [`tryFrom`](https://www.php.net/manual/en/backedenum.tryfrom.php) enum method.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\Http\Response;
use App\AircraftType;

final readonly class AircraftController
{
    #[Get('/aircraft/{type}')]
    public function show(AircraftType $type): Response { /* … */ }
}
```

In the example above, an `AircraftType` enumeration is injected. If the request's `type` parameter has a value specified in that enumeration, it is passed to the controller action. Otherwise, an HTTP 404 response is returned without entering the controller method.

```php app/AircraftType.php
enum AircraftType: string
{
    case PC12 = 'pc12';
    case PC24 = 'pc24';
    case SF50 = 'sf50';
}
```

## Generating URIs

Tempest provides a {b`\Tempest\Router\uri()`} function to generate URIs to controller methods. This function accepts the fully-qualified class name of the controller or a callable to a method as its first argument, and named parameters as [the rest of its arguments](https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list).

```php
use function Tempest\Router\uri;

// Invokable classes can be referenced directly:
uri(HomeController::class);
// → /home

// Classes with named methods are referenced using an array
uri([AircraftController::class, 'store']);
// → /aircraft

// Additional URI parameters are passed in as named arguments:
uri([AircraftController::class, 'show'], id: $aircraft->id);
// → /aircraft/1
```

:::info
URI-related methods are also available by injecting the {b`Tempest\Router\UriGenerator`} class into your controller.
:::

### Signed URIs

A signed URI ensures that the URI was not modified after it was created. This is useful for implementing login or unsubscribe links, or other endpoints that need protection against tampering.

To create a signed URI, use the {b`\Tempest\Router\signed_uri()`} function. This function accepts the same arguments as {b`\Tempest\Router\uri()`} and returns the URI with a `signature` parameter:

```php
use function Tempest\Router\signed_uri;

signed_uri(
    action: [MailingListController::class, 'unsubscribe'],
    email: $email
);
```

Alternatively, {b`\Tempest\Router\temporary_signed_uri()`} can be used to provide a duration after which the signed URI expires, providing an extra layer of security.

```php
use function Tempest\Router\temporary_signed_uri;

temporary_signed_uri(
    action: PasswordlessAuthenticationController::class,
    duration: Duration::minutes(10),
    userId: $userId
);
```

To ensure the validity of a signed URL, call the `hasValidSignature` method on the {b`Tempest\Router\UriGenerator`} class.

```php
final class PasswordlessAuthenticationController
{
    public function __construct(
        private readonly UriGenerator $uri,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (! $this->uri->hasValidSignature($request)) {
            throw new HttpRequestFailed(Status::UNPROCESSABLE_CONTENT);
        }

        // …
    }
}
```

### Matching the current URI

To determine whether the current request matches a specific controller action, Tempest provides the {b`\Tempest\Router\is_current_uri()`} function. This function accepts the same arguments as `uri`, and returns a boolean.

```php "GET /aircraft/1"
use function Tempest\Router\is_current_uri;

// Providing no argument to the right controller action will match
is_current_uri(AircraftController::class); // true

// Providing the correct arguments to the right controller action will match
is_current_uri(AircraftController::class, id: 1); // true

// Providing invalid arguments to the right controller action will not match
is_current_uri(AircraftController::class, id: 2); // false
```

## Accessing request data

Web applications need to process user input—whether it is form submissions, search queries, API payloads, or filter parameters.

Tempest handles this by injecting {b`Tempest\Http\Request`} objects into controller actions, giving access to the request's body, query parameters, method, and headers through dedicated class properties.

### Using request classes

In most situations, the data expected from a request is structured. Clients are expected to send specific values and follow specific rules.

The idiomatic approach is to use request classes. These are classes with public properties that correspond to the data to retrieve from the request. Tempest automatically validates these properties using PHP's type system, in addition to optional [validation attributes](../2-features/03-validation) when needed.

A request class must implement {b`Tempest\Http\Request`} and use the {b`Tempest\Http\IsRequest`} trait, which provides the default implementation.

:::code-group

```php app/RegisterAirportRequest.php
use Tempest\Http\Request;
use Tempest\Http\IsRequest;
use Tempest\Validation\Rules\HasLength;

final class RegisterAirportRequest implements Request
{
    use IsRequest;

    #[HasLength(min: 10, max: 120)]
    public string $name;

    #[HasLength(min: 2)]
    public string $servedCity;

    #[HasLength(min: 4, max: 4)]
    public string $icaoCode;

    public ?DateTime $registeredAt = null;
}
```

```php app/AirportController.php
use Tempest\Router\Post;
use Tempest\Http\Responses\Redirect;

use function Tempest\Mapper\map;
use function Tempest\Router\uri;

final readonly class AirportController
{
    #[Post(uri: '/airports/register')]
    public function store(RegisterAirportRequest $request): Redirect
    {
        $airport = map($request)
            ->to(Airport::class)
            ->save();

        return new Redirect(uri([self::class, 'show'], id: $airport->id));
    }
}
```

```php app/Airport.php
#[Table('airports')]
final class Airport
{
    public string $name;
    public string $servedCity;
    public string $icaoCode;
    public ?DateTime $registeredAt = null;
}
```

:::

Once a request class is created, it can be injected into a controller action. Tempest fills its properties and validates them, providing a properly-typed object.

:::info A note on data mapping
The `map()` function allows mapping any data from any source into objects of your choice. You may read more about them in [their documentation](../2-features/01-mapper.md).
:::

### Sensitive fields

When a validation error occurs, Tempest filters out sensitive fields from the original values stored in the session. This prevents sensitive data from being re-populated in forms after a redirect.

Request properties can be marked as sensitive using the {b`#[Tempest\Http\SensitiveField]`} attribute:

```php app/ResetPasswordRequest.php
use Tempest\Http\Request;
use Tempest\Http\IsRequest;
use Tempest\Http\SensitiveField;
use Tempest\Validation\Rules\HasLength;

final class ResetPasswordRequest implements Request
{
    use IsRequest;

    public string $email;

    #[SensitiveField]
    #[HasLength(min: 8)]
    public string $password;
}
```

### Retrieving data directly

For simpler use cases, a value can be retrieved from the body or the query parameter using the {b`Tempest\Http\Request`}'s `get` method. Other methods, such as `hasBody` or `hasQuery`, are also available.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\Http\Request;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft')]
    public function me(Request $request): View
    {
        $icao = $request->get('icao');
        // …
    }
}
```

## Form validation

When users submit forms—like updating profile settings, or posting comments—the data needs validation before processing. Tempest automatically validates request objects using type hints and validation attributes, then provides errors back to users when something is wrong.

On validation failure, Tempest either redirects back to the form (for web pages) or returns a 422 response (for stateless requests). Validation errors are available in two places:

- As a JSON encoded string in the `{txt}X-Validation` header
- Through the {b`Tempest\Http\Session\FormSession`} class

For web pages, Tempest also provides built-in view components to display errors when they occur.

```html
<x-form :action="uri(StorePostController::class)">
  <x-input name="name" />
  <x-input type="email" name="email" />
  <x-submit />
</x-form>
```

`{html}<x-form>` is a view component that defaults to sending `POST` requests. `{html}<x-input>` is a view component that renders a label, input field, and validation errors all at once.

:::info
These built-in view components can be customized. Run `./tempest install view-components` and select the components to pull into the project. [Read more about installing view components here](../1-essentials/02-views.md#built-in-components).
:::

## Route middleware

Middleware can be applied to handle tasks between receiving a request and sending a response. To specify middleware for a route, add it to the `middleware` argument of a route attribute.

```php app/ReceiveInteractionController.php
use Tempest\Router\Get;
use Tempest\Http\Response;

final readonly class ReceiveInteractionController
{
    #[Post('/slack/interaction', middleware: [ValidateWebhook::class])]
    public function __invoke(): Response
    {
        // …
    }
}
```

The middleware class must be an invokable class that implements the {b`Tempest\Router\HttpMiddleware`} interface. This interface has an `{:hl-property:__invoke:}()` method that accepts the current request as its first parameter and {b`Tempest\Router\HttpMiddlewareCallable`} as its second parameter.

{b`Tempest\Router\HttpMiddlewareCallable`} is an invokable class that forwards the `$request` to its next step in the pipeline.

```php app/ValidateWebhook.php
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Support\Priority;

#[SkipDiscovery]
#[Priority(Priority::LOW)]
final readonly class ValidateWebhook implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $signature = $request->headers->get('X-Slack-Signature');
        $timestamp = $request->headers->get('X-Slack-Request-Timestamp');

        // …

        return $next($request);
    }
}
```

### Middleware priority

All middleware classes are sorted based on their priority. By default, each middleware has the "normal" priority, which can be overridden using the {b`#[Tempest\Support\Priority]`} attribute:

```php
use Tempest\Support\Priority;

#[Priority(Priority::HIGH)]
final readonly class ValidateWebhook implements HttpMiddleware
{ /* … */ }
```

Priority is defined using an integer. However, for consistency reasons, it is recommended to use of the built-in {b`Tempest\Support\Priority`} constants.

### Middleware discovery

Global middleware classes are discovered and sorted based on their priority. A middleware class can be made non-global by annotating it with the {b`#[Tempest\Discovery\SkipDiscovery]`} attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class ValidateWebhook implements HttpMiddleware
{ /* … */ }
```

### Cross-site request forgery protection

Tempest provides [cross-site request forgery](https://en.wikipedia.org/wiki/Cross-site_request_forgery) protection based on the presence and values of the [`{txt}Sec-Fetch-Site`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site) and [`{txt}Sec-Fetch-Mode`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode) headers through the {b`Tempest\Router\PreventCrossSiteRequestsMiddleware`} middleware, included by default in all requests.

Unlike traditional CSRF tokens, this approach uses browser-generated headers that cannot be forged by external websites:

- [`{txt}Sec-Fetch-Site`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site) indicates whether the request came from the same domain, subdomain, a different site or if it was user-initiated, such as typing the URL directly,
- [`{txt}Sec-Fetch-Mode`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode) allows distinguishing between requests originating from a user navigating between HTML pages, and requests to load images and other resources.

:::info
This middleware requires browsers that support `{txt}Sec-Fetch-*` headers, which is the case for all modern browsers. You may [exclude this middleware](#excluding-route-middleware) and implement traditional CSRF protection using tokens if you need to support older browsers.
:::

### Excluding route middleware

Some routes do not require specific global middleware to be applied. For instance, a publicly accessible health check endpoint could bypass rate limiting that's applied to other routes. Specific middleware can be skipped by using the `without` argument of the route attribute.

```php app/HealthCheckController.php
use Tempest\Router\Get;
use Tempest\Http\Response;

final readonly class HealthCheckController
{
    #[Get('/health', without: [RateLimitMiddleware::class])]
    public function __invoke(): Response
    {
        return new Ok(['status' => 'healthy']);
    }
}
```

## Route decorators

When building an API or an administration panel, routes often share common configuration—like a URL prefix (`/api`), authentication middleware, or stateless behavior. Route decorators are attributes that can be annotated to controller classes or methods to apply common configuration.

```php app/Books/ApiController.php
use Tempest\Router\Prefix;
use Tempest\Router\Get;

#[Prefix('/api')]
final readonly class ApiController
{
    #[Get('/books')]
    public function books(): Response { /* … */ }
    
    #[Get('/authors')]
    public function authors(): Response { /* … */ }
}
```

### Built-in route decorators

Tempest includes several route decorators to handle common scenarios—like providing routes without session overhead, organizing routes under a common prefix, or applying authentication across an entire controller.

These decorators save you from creating custom implementations for frequently-needed patterns.

#### `#[Stateless]`

For API endpoints, RSS feeds, or any other kind of page that does not require cookie or session data, use the {b`#[Tempest\Router\Stateless]`} attribute to remove all state-related logic:

```php
use Tempest\Router\Stateless;
use Tempest\Router\Get;

final readonly class BlogPostController
{
    #[Stateless]
    #[Get('/rss')]
    public function rss(): Response { /* … */ }
}
```

#### `#[Prefix]`

Adds a prefix to the URI for all associated routes.

```php
use Tempest\Router\Prefix;
use Tempest\Router\Get;

#[Prefix('/api')]
final readonly class ApiController
{
    #[Get('/books')]
    public function books(): Response { /* … */ }
    
    #[Get('/authors')]
    public function authors(): Response { /* … */ }
}
```

#### `#[WithMiddleware]`

Adds middleware to all associated routes.

```php
use Tempest\Router\WithMiddleware;
use Tempest\Router\Get;

#[WithMiddleware(AuthMiddleware::class, AdminMiddleware::class)]
final readonly class AdminController { /* … */ }
```

#### `#[WithoutMiddleware]`

Explicitly removes middleware to all associated routes.

```php
use Tempest\Router\WithoutMiddleware;
use Tempest\Router\Get;
use Tempest\Router\PreventCrossSiteRequestsMiddleware;

#[WithoutMiddleware(PreventCrossSiteRequestsMiddleware::class)]
final readonly class StatelessController { /* … */ }
```

### Custom route decorators

Custom route decorators are built by implementing the {b`\Tempest\Router\RouteDecorator`} interface and marking the decorator as an attribute. The `decorate()` method receives the current {b`Tempest\Router\Route`} as a parameter, and must return the modified route.

```php
use Attribute;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Auth implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = AuthMiddleware::class;

        return $route;
    }
}
```

## Idempotent routes

For operations like payment processing or order creation, retrying the same request should not produce duplicate side effects. Tempest provides the {b`Tempest\Idempotency\Attributes\Idempotent`} route decorator to handle this. Clients send an `Idempotency-Key` header; the first request executes normally and caches the response, while subsequent requests with the same key replay the cached response.

```php app/OrderController.php
use Tempest\Router\Post;
use Tempest\Http\Response;
use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\Idempotency\Attributes\Idempotent;

final readonly class OrderController
{
    #[Post('/orders')]
    #[Idempotent]
    public function create(CreateOrderRequest $request): Response
    {
        $order = $this->orderService->create($request);

        return new GenericResponse(
            status: Status::CREATED,
            body: ['id' => $order->id],
        );
    }
}
```

Idempotency is only supported for `POST` and `PATCH` routes. The attribute can be applied at the class level to make all routes in a controller idempotent, and accepts optional TTL parameters. Route-specific settings like key requirement and header name can be configured with the `#[IdempotentRoute]` attribute.

:::info
Read the full [idempotency documentation](../2-features/19-idempotency.md) for details on scope resolvers, configuration, response behavior, and command bus idempotency.
:::

## Responses

All requests to a controller action expect a response to be returned to the client. This is done by returning a {b`Tempest\View\View`} or a {b`Tempest\Http\Response`} object.

For simpler use cases or debugging purposes, scalar values and arrays can also be returned directly. Tempest automatically converts these values into proper responses.

### View responses

Returning a view is a shorthand for returning a successful response with that view. The {b`Tempest\view()`} function can be used directly to construct a view.

```php app/Aircraft/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{aircraft}')]
    public function show(Aircraft $aircraft, User $user): View
    {
        return view('./show.view.php',
            aircraft: $aircraft,
            user: $user,
        );
    }
}
```

Tempest has a templating system inspired by modern front-end frameworks like [Vue](https://vuejs.org). Read more about views in the [dedicated chapter](./02-views.md).

### Using built-in response classes

Tempest provides several response classes for common use cases, all implementing the {b`Tempest\Http\Response`} interface, mostly named after HTTP statuses.

- {b`Tempest\Http\Responses\Ok`} — the 200 response. Accepts an optional body.
- {b`Tempest\Http\Responses\Created`} — the 201 response. Accepts an optional body.
- {b`Tempest\Http\Responses\Redirect`} — redirects to the specified URI.
- {b`Tempest\Http\Responses\Back`} — redirects to previous page, accepts a fallback.
- {b`Tempest\Http\Responses\Download`} — downloads a file from the browser.
- {b`Tempest\Http\Responses\File`} — shows a file in the browser.
- {b`Tempest\Http\Responses\NotFound`} — the 404 response. Accepts an optional body.
- {b`Tempest\Http\Responses\ServerError`} — a 500 server error response.

The following example conditionally returns a {b`Tempest\Http\Responses\Redirect`}, otherwise letting the user download a file by sending a {b`Tempest\Http\Responses\Download`} response:

```php app/FlightPlanController.php
use Tempest\Router\Get;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Response;

final readonly class FlightPlanController
{
    #[Get('/{flight}/flight-plan/download')]
    public function download(Flight $flight): Response
    {
        if (! $this->accessControl->isGranted('view', $flight)) {
            return new Redirect('/');
        }

        return new Download($flight->flight_plan_path);
    }
}
```

### Sending generic responses

When the response's status code needs to be dynamically computed without using a condition to send the corresponding response object, return an instance of {b`Tempest\Http\GenericResponse`} and specify the status code and an optional body.

```php app/CreateFlightController.php
use Tempest\Router\Get;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\GenericResponse;
use Tempest\Http\Response;

final readonly class CreateFlightController
{
    #[Post('/{flight}')]
    public function __invoke(Flight $flight): Response
    {
        $status = /* … */
        $body = /* … */

        return new GenericResponse(
            status: $status,
            body: $body,
        );
    }
}
```

### Using custom response classes

There are situations where the same kind of response is sent in multiple places, or where a proper API is needed for sending a structured response.

Custom response classes can be created by implementing {b`Tempest\Http\Response`}, which default implementation is provided by the {b`Tempest\Http\IsResponse`} trait:

```php app/AircraftRegistered.php
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class AircraftRegistered implements Response
{
    use IsResponse;

    public function __construct(Aircraft $aircraft)
    {
        $this->status = Status::CREATED;
        $this->flash(
            key: 'success',
            value: "Aircraft {$aircraft->icao_code} was successfully registered."
        );
    }
}
```

### Specifying content types

Tempest automatically infers the response's content type, typically from the request's `{txt}Accept` header.

However, the content type can be overridden manually by using the `setContentType` method on {b`Tempest\Http\Response`} classes. This method accepts a case of {b`Tempest\Http\ContentType`}.

```php app/JsonController.php
use Tempest\Router\Get;
use Tempest\Http\ContentType;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;

final readonly class JsonController
{
    #[Get('/json')]
    public function json(string $path): Response
    {
        $data = [ /* … */ ];

        return new Ok($data)->setContentType(ContentType::JSON);
    }
}
```

### Post-processing responses

There are situations where actions need to be taken on a response right before it is sent to the client. For instance, custom error pages can be displayed when an exception occurred, or a redirect can be performed instead of displaying the [built-in HTTP 404](/hello-from-the-void){:ssg-ignore="true"} page.

This can be done using a response processor. Similar to [view processors](./02-views.md#pre-processing-views), these are classes that implement the {b`Tempest\Router\ResponseProcessor`} interface. In the `process()` method, the response object can be mutated and returned:

```php app/ErrorResponseProcessor.php
use function Tempest\View\view;

final readonly class ErrorResponseProcessor implements ResponseProcessor
{
    public function process(Response $response): Response
    {
        if (! $response->status->isSuccessful()) {
            return $response->setBody(view('./error.view.php', status: $response->status));
        }

        return $response;
    }
}
```

## Session management

Sessions in Tempest are managed by the {b`Tempest\Http\Session\Session`} class. It can be injected anywhere needed. As soon as the {b`Tempest\Http\Session\Session`} is injected, it is started behind the scenes.

```php
use Tempest\Http\Session\Session;

final readonly class TodoController
{
    public function __construct(
        private Session $session,
    ) {}

    #[Post('/select/{todo}')]
    public function select(Todo $todo): View
    {
        if ($this->session->get('selected_todo') === $todo->id) {
            $this->session->remove('selected_todo');
        } else {
            $this->session->set('selected_todo', $todo->id);
        }

        return $this->list();
    }
}
```

### Flashing values

After saving data or performing an action, it is often needed to show users a success message, error notification, or status update that appears once and then disappears after they refresh the page.

Use the `flash()` method on the {b`Tempest\Http\Session\Session`} to store a value that lasts for the next request only:

```php
public function store(Todo $todo): Redirect
{
    $this->session->flash('message', value: 'Save was successful');
    
    return new Redirect('/');
}
```

### Session configuration

Tempest supports file, Redis and database-based sessions, the former being the default option.

Sessions can be configured by creating a `session.config.php` file [anywhere](../1-essentials/06-configuration.md#configuration-files), in which the expiration time and the [clean up strategy](#session-cleaning) can be configured.

#### File sessions

When using file-based sessions, which is the default, session data is stored in files within the specified directory, relative to `.tempest`. The path and expiration duration can be configured as follows:

```php app/session.config.php
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\DateTime\Duration;

return new FileSessionConfig(
   expiration: Duration::days(30),
   path: 'sessions',
);
```

#### Database sessions

Tempest provides a database-based session driver, particularly useful for applications that run on multiple servers, as session data can be shared across all instances.

Before using database sessions, a dedicated table is needed. Tempest provides a dedicated sessions installer that can publish file, database, or Redis session configuration:

```sh
./tempest install sessions
```

When choosing the database strategy, the installer can also publish a migration and the configuration file that sets up database sessions, with a default expiration of 30 days:

```php app/Sessions/session.config.php
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\DateTime\Duration;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
);
```

### Session cleaning

Sessions expire based on the last activity time. This means that as long as a user is actively using the application, their session remains valid.

By default, Tempest removes expired session randomly at the end of a request, in a deferred task. This can be configured by specifying a {b`Tempest\Http\Session\CleanupStrategy`} in the [session configuration](#session-configuration).

```php app/Sessions/session.config.php
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\DateTime\Duration;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
    cleanupStrategy: CleanupStrategy::EVERY_REQUEST,
);
```

The default behavior is great for most applications. However, at a certain scale, the performance of random cleanup can decrease as the number of sessions grows. In that case, it is recommended to disable request-based session cleaning and switch to a scheduled cleanup strategy:

:::code-group

```php app/Sessions/session.config.php
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\DateTime\Duration;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
    cleanupStrategy: CleanupStrategy::DISABLED,
);
```

```php app/Sessions/CleanupSessionsCommand.php
namespace App\Sessions;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\Http\Session\SessionManager;

final readonly class CleanupSessionsCommand
{
    public function __construct(
        private SessionManager $sessionManager,
    ) {
    }

    #[ConsoleCommand(name: 'session:clean')]
    #[Schedule(Every::MINUTE)]
    public function __invoke(): void
    {
        $this->sessionManager->deleteExpiredSessions();
    }
}
```

:::

:::info
This cleanup command can be installed in your codebase by running the `./tempest install sessions` command.
:::

## Deferring tasks

During requests, tasks that take a few seconds to complete are sometimes needed. This could be sending an email or keeping track of a page visit.

Tempest provides a way to perform that task after the response has been sent, so the client does not have to wait until its completion. This is done by passing a callback to the `defer` function:

```php app/TrackVisitMiddleware.php
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Http\Request;
use Tempest\Http\Response;

use function Tempest\defer;
use function Tempest\event;

final readonly class TrackVisitMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        defer(fn () => event(new PageVisited($request->getUri())));

        return $next($request);
    }
}
```

The `defer` callback can accept any parameter that the container can inject.

:::warning
Task deferring only works if [`fastcgi_finish_request()`](https://www.php.net/manual/en/function.fastcgi-finish-request.php) is available within your PHP installation. If it's not available, deferred tasks will still be run, but the client response will only complete after all tasks have been finished.
:::

## Testing

Tempest provides a router testing utility accessible through the `http` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. Learn more about testing in the [dedicated chapter](./07-testing.md).

The router testing utility provides methods for all HTTP verbs. These methods return an instance of [`TestResponseHelper`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/Http/TestResponseHelper.php), giving access to multiple assertion methods.

```php tests/ProfileControllerTest.php
final class ProfileControllerTest extends IntegrationTestCase
{
    public function test_can_render_profile(): void
    {
        $response = $this->http
            ->get('/account/profile')
            ->assertOk()
            ->assertSee('My Profile');
    }
}
```


---
title: Views
description: "Tempest provides a modern templating engine with syntax inspired by the best front-end frameworks. However, Blade, Twig or any other engine can be used if you prefer so."
keywords: "Experimental"
---

## Overview

Views in Tempest are parsed by Tempest View, our own templating engine. Tempest View uses a syntax that can be thought of as a superset of HTML. If you prefer using a templating engine with more widespread support, [you may also use Blade, Twig, or any other](#using-other-engines) — as long as you provide a way to initialize it.

If you'd like to Tempest View as a standalone component in your project, you can read the documentation on how to do so [here](../5-extra-topics/02-standalone-components.md#tempest-view).

### Syntax overview

The following is an example of a view that inherits the `x-base` component, passing a `title` property.

Inside, a `x-post` [component](#view-components) is rendered multiple times thanks to a [foreach loop](#foreach-and-forelse) on `$this->posts`. That component has a default [slot](#using-slots), in which the post details are rendered. The [control flow](#control-flow-directives) is implemented using HTML attributes that start with colons `:`.

```html
<x-base title="Home">
    <x-post :foreach="$this->posts as $post">
        {{-- a comment which won't be rendered to HTML --}}
        
        {!! $post->title !!}

        <span :if="$this->showDate($post)">
            {{ $post->date }}
        </span>
        <span :else>
            -
        </span>
    </x-post>
    <div :forelse>
        <p>It's quite empty here…</p>
    </div>

    <x-footer />
</x-base>
```

## Rendering views

As specified in the documentation about [sending responses](./01-routing.md#view-responses), views may be returned from controller actions using the `{php}view` function. This function is a shorthand for instantiating a {`Tempest\View\View`} object.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{aircraft}')]
    public function show(Aircraft $aircraft): View
    {
        return view('aircraft.view.php', aircraft: $aircraft);
    }
}
```

### View paths

The `view` function accepts the path to a view as its first parameter. This path may be relative or absolute, depending on your preference.

The following three examples are equivalent:

```php
return view(__DIR__ . '/views/home.view.php');
return view('./views/home.view.php');
return view('views/home.view.php');
```

### Using dedicated view objects

A view object is a dedicated class that represent a specific view.

Using view objects will improve static insights in your controllers and view files, and may offer more flexibility regarding how the data may be constructed before being passed on to a view file.

```php
final class AircraftController
{
    #[Get('/aircraft/{type}/{aircraft}')]
    public function show(AircraftType $type, Aircraft $aircraft): AircraftView
    {
        return new AircraftView($aircraft, $type);
    }
}
```

To create a view object, implement the {`Tempest\View\View`} interface, and add the {`Tempest\View\IsView`} trait, which provides the default implementation.

```php app/AircraftView.php
use Tempest\View\View;
use Tempest\View\IsView;

use function Tempest\root_path;

final class AircraftView implements View
{
    use IsView;

    public function __construct(
        public Aircraft $aircraft,
        public AircraftType $type,
    ) {
        $this->path = root_path('src/Aircraft/aircraft.view.php');
    }
}
```

In a view file rendered by a view object, you may add a type annotation for `$this`. This allows IDEs like [PhpStorm](https://www.jetbrains.com/phpstorm/) to infer variables and methods.

```html app/Aircraft/aircraft.view.php
<?php /** @var \App\Modules\Home\HomeView $this */ ?>

<p :if="$this->type === AircraftType::PC24">
	The {{ $this->aircraft->icao_code }} is a light business jet
	produced by Pilatus Aircraft of Switzerland.
</p>
```

View objects are an excellent way of encapsulating view-related logic and complexity, moving it away from controllers, while simultaneously improving static insights.

## Templating syntax

### Text interpolation

Text interpolation is done using the "mustache" syntax. This will escape the given variable or PHP expression before rendering it.

```html
<span>Welcome, {{ $username }}</span>
```

To avoid escaping the data, you may use the following syntax. This should only be used on trusted, sanitized data, as this can open the door to an [XSS vulnerability](https://en.wikipedia.org/wiki/Cross-site_scripting):

```html
<div>
	{!! $content !!}
</div>
```

### Expression attributes

Expression attributes are HTML attributes that are evaluated as PHP code. Their syntax is the same as HTML attributes, except they are identified by a colon `:`:

```html
<html :lang="$this->user->language"></h1>
<!-- <html lang="en"></h1> -->
```

As with text interpolation, only variables and PHP expressions that return a value are allowed. Mustache and PHP opening tags cannot be used inside them:

```html
<!-- This is invalid -->
<h1 :title="<?= $this->post->title ?>"></h1>
```

When using expression attributes on normal HTML elements, only [scalar](https://www.php.net/manual/en/language.types.type-system.php#language.types.type-system.atomic.scalar) and `Stringable` values can be returned. However, any object can be passed down to a [component](#view-components).

### Boolean attributes

The HTML specification describes a special kind of attributes called [boolean attributes](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attribute). These attributes don't have a value, but indicate `true` whenever they are present.

Using an expression attribute that return a boolean variable will follow the HTML specification, effectively not rendering the attribute if the value is `false`.

```html
<option :value="$value" :selected="$selected">{{ $label }}</option>
```

Depending on whether `$selected` evaluates to `true` or `false`, the above example may or may not render the `selected` attribute.

Apart from HTMLs boolean attributes, the same syntax can be used with any expression attribute as well:

```html
<div :data-active="{$isActive}"></div>

<!-- <div></div> when $isActive is falsy -->
<!-- <div data-active></div> when $isActive is truthy -->
```

### Control flow directives

#### `:if`, `:elseif`, and `:else`

The `:if` directive can conditionally render the element it is attached to, depending on the result of its expression. Similarly, the `:elseif` and `:else` directives can be used on direct siblings for additional control.

```html
<span :if="$this->pendingUploads->isEmpty()">Import files</span>
<span :else>Import {{ $this->pendingUploads->count() }} file(s)</span>
```

#### `:isset`

The `:isset` directive can be used to conditionally render the element it is attached to, depending on the existence of a variable.

```html
<h1 :isset="$title">{{ $title }}</h1>
```

The `:isset` directive will also detect when you have multiple cases, and will wrap each variable with `isset()` for you. Consider this example:

```html
<h1 :isset="$foo || $bar">Welcome!</h1>
```

If either `isset($foo)` or `isset($bar)` returns `true`, then the condition is met, and the element will be conditionally rendered.

You can also use `!isset($foo)` for inverse if needed.

Note: Ensuring that the expression returns `true` or `false` and thus applies the condition correctly is left down to you, the directive will simply wrap each `$var` with `isset()` preserving operators, without performing any logic checks itself. If you make an incompatible string, it will throw an Exception which you'll be able to view in the Debug log or interface, when enabled.

Since `:isset` is a shorthand for `:if="isset()"`, it can be combined with `:elseif` and `:else`:

```html
<h1 :isset="$title">{{ $title }}</h1>
<h1 :else>Title</h1>
```

#### `:foreach` and `:{:hl-keyword:forelse:}`

The `:foreach` directive may be used to render the associated element multiple times based on the result of its expression. Combined with `:{:hl-keyword:forelse:}`, an empty state can be displayed when the data is empty.

```html
<li :foreach="$this->reports as $report">
  {{ $report->title }}
</li>
<li :forelse>
	There is no report.
</li>
```

### Templates

The built-in `{html}<x-template>` element may be used as a placeholder when you want to use a directive without rendering an actual element in the DOM.

```html
<x-template :foreach="$posts as $post">
    <div>{{ $post->title }}</div>
</x-template>
```

The example above will only render the child `div` elements:

```html
<div>Post A</div>
<div>Post B</div>
<div>Post C</div>
```

### Tag override with the `as` prop

The `as` attribute allows you to transform the rendered tag of one element into another. This takes place on an instance of `GenericElement`, so for example this code:
```html
<a as="button">My Link</a>
```
Would render
```html
<button>My Link</button>
```
The power behind this is when you use an `Expression` to determine the element.

Say for example, you wish to have a `<x-link>` component which renders as an `<a>` when the `$href` attribute is provided. In your view, use the component like so:
```html
<x-link href="https://tempestphp.com">Click to go to an awesome website</x-link>

<x-link>This is just a button</x-link>
```
In your `<x-link>` component, define:
```html
<a :as="$href ?? 'button'" :href="$href ?? ''"><x-slot /></a>
```
Your page will render two links, as follows
```html
<a href="https://tempestphp.com">Click to go to an awesome website</a>

<button>This is just a button</button>
```

#### Where this can and cannot be used

You can't use the `as` Attribute on things like `<x-template>`, `<x-slot>`, etc, as these do not themselves render any HTML. They are placeholders in the page. Nor will placing it on a view component itself inherently do anything. The `as` attribute CAN be passed to a ViewComponent as shown in the example above, but by itself it will actually do nothing, unless you specifically provide logic to place it where you want it.

## View components

Components allow for splitting the user interface into independent and reusable pieces.

Tempest doesn't have a concept of extending other views. Instead, a component may include another component using the same syntax as other HTML elements.

### Registering view components

To create a view component, create a `.view.php` file that starts with `x-`. These files are referred to as anonymous view components and are automatically discovered by Tempest.

```html app/x-base.view.php
<html lang="en">
	<head>
		<title :if="$title ?? null">{{ $title }} — AirAcme</title>
		<title :else>AirAcme</title>
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

### Using view components

All views may include a view component. In order to do so, you may simply use a component's name as a tag, including the `x-` prefix:

```html app/home.view.php
<x-base :title="$this->post->title">
	<article>
		{{ $this->post->body }}
	</article>
</x-base>
```

The example above demonstrates how to pass data to a component using an [expression attribute](#expression-attributes), as well as how to pass elements as children if that component where the `<x-slot />` tag is used.

### Attributes in components

Attributes and [expression attributes](#expression-attributes) may be passed into view components. They work the same way as normal elements, and their values will be available in variables of the same name:

```html home.view.php
<x-base :title="$this->post->title">
	// ...
</x-base>
```

```html x-base.view.php
// ...
<title :if="$title ?? null">{{ $title }}</title>
```

Note that the casing of attributes will affect the associated variable name:

- `{txt}camelCase` and `{txt}PascalCase` attributes will be converted to `$lowercase` variables
- `{txt}kebab-case` and `{txt}snake_case` attributes will be converted to `$camelCase` variables.

:::info
The idiomatic way of using attributes is to always use `{txt}kebab-case`.
:::

### Fallthrough attributes

When `{html}class` and `{html}style` attributes, or `{html}id` is provided on a view component, Tempest will attempt to automatically apply these to the root node within the view component.

:::info
In previous releases (3.8.0 and prior), Tempest would attempt to *merge* these values, however there was no way to prevent this, or customise the behaviour. There was also a bug in applying the attributes, which meant that in many cases it didn't apply at all, resulting in inconsistent behaviour. This has been resolved, but has a new default behaviour, as explained below.
:::

Assume you have a `button`, like so, with a default set of classes present:
```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm">
	<x-slot />
</button>
```
Now, in your page, you may utilise the element:
```html index.view.php
<x-button id="myBtn" style="color: red;" />
```
As these attributes automatically apply, your button will be converted to this:
```html
<button id="myBtn" style="color: red;" class="rounded-md px-2.5 py-1.5 text-sm" />
```

#### Disabling automatic fallthrough

Tempest will attempt to apply `{html}class`, `{html}style`, and `{html}id` automatically, when they are passed to a view component. For example:
```html index.view.php
<x-button id="myBtn" style="color: red;" />
```
With the above, Tempest will attempt to apply `{html}style`, and `{html}id` automatically. As `{html}class` isn't configured, it isn't applied.

In the view component itself, you can configure `{html}class`, `{html}style`, and `{html}id` to anything you want, and Tempest will not overwrite them. You can of course, also then use these classes however you want to use them:
```html x-button.view.php
<button :id="uniqid(($id ?? 'mybtn') . '_')" :class="$class ?? 'rounded-md px-2.5 py-1.5 text-sm'">
	<x-slot />
</button>
```
When you use this version of `<x-button />`:
- `{html}id` will now default to `mybtn_(sequence generated by uniqid)`,
- `{html}style` will not appear automatically, as it was not supplied,
- `{html}class` will have a default, you can of course instead concatenate these strings, or use a CVA utility for smart class merging, or anything you want.

For example, pass one or more classes:
```html
<x-button id="myBtn" class="override" />
```
And you'll get
```html
<button class="override" id="myBtn_69cad27787c20"></button>
```

### Controlling fallthrough attributes with the Apply attribute

You can also leverage the `ApplyAttribute` to completely control the behaviour, and add further fallthrough attributes, if you wish. When `:apply` is detected on a view component, Tempest will disable all automatic fallthrough attributes, for that instance of the view component. If you are familiar with JS frontend frameworks, this is not dissimilar to a one-way `v-bind` in Vue, or a spread props operator in other languages.

By default, `$attributes` is an `ImmutableArray` and so we can manipulate it with the methods available on that class.

:::info
You cannot mix `ApplyAttribute` with automatic fallthrough attributes. Opting to use the `ApplyAttribute` hands you full control of which attributes are applied, which means you then need to declare these.
:::

#### Excluding specific fallthrough attributes

To exclude specific attributes from falling through, configure your `button` view component like this:
```html x-button.view.php
<button :apply="$attributes->removeKeys(['id', 'style'])">
	<x-slot />
</button>
```
:::info
The `removeKeys` method returns all key=>value pairs, except for those specified. You can also use the `filter` method if you need to use a closure to filter.
:::
Now, when utilising it in your page:
```html index.view.php
<x-button id="myBtn" style="color: red;" class="rounded-md px-2.5 py-1.5 text-sm" />
```
Will result in:
```html
<button class="rounded-md px-2.5 py-1.5 text-sm" />
```

#### Including only specific fallthrough attributes

To include only specific attributes, configure your `button` view component like this:
```html x-button.view.php
<button :apply="$attributes->removeKeysExcept(['class', 'width', 'height'])">
	<x-slot />
</button>
```
:::info
The `removeKeysExcept` method returns only the specified key=>value pairs. You can also use the `filter` method if you need to use a closure to filter.
:::
Now, when utilising it in your page:
```html index.view.php
<x-button id="myBtn" style="color: red;" class="rounded-md px-2.5 py-1.5 text-sm"  width="1em" height="1em" />
```
Tempest will apply only the specified attributes:
```html
<button class="rounded-md px-2.5 py-1.5 text-sm" />
```

#### Advanced usage of the Apply attribute

As the `ApplyAttribute` simply stringifies string and boolean values from the provided array, you can build the array however you like.

Consider this example `button`:
```php x-button.view.php
<?php
$apply = [
    'class' => $class ?? null,
    'href' => $href ?? '',
    'target' => (isset($href) && str_contains($href, 'http')) ? '_blank' : null,
];
?>
<button :as="$apply['href'] !== '' ? 'a' : 'button'" :apply="$apply">{{ $label ?? '' }}</button>
```
Now, when utilising it in your page:
```html index.view.php
<x-button href="https://tempestphp.com" label="Tempest, the framework that gets out of your way" />
```
Tempest will spread the supplied attributes, and as we also used the `AsAttribute` to convert it to a `{html}a` when `$href` is populated, you will get a hyperlink:
```html
<a href="https://www.tempestphp.com" target="_blank">Tempest, the framework that gets out of your way</a>
```

### Dynamic attributes

An `$attributes` variable is accessible within view components. This variable is an array that contains all attributes passed to the component, except expression attributes.

Note that attribute names use `{txt}kebab-case`.

```html x-badge.view.php
<span class="px-2 py-1 rounded-md text-sm bg-gray-100 text-gray-900">
	{{ $attributes['value'] }}
</span>
```

### Using slots

The content of components is often dynamic, depending on external context to be rendered. View components may define zero or more slot outlets, which may be used to render the given HTML fragments.

```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm text-gray-100 bg-gray-900">
	<x-slot />
</button>
```

The example above defines a button component with default classes, and a slot inside. This component may be used like a normal HTML element, providing the content that will be rendered in the slot outlet:

```html index.view.php
<x-button>
	<!-- This will be injected into the <x-slot /> outlet -->
	<x-icon name="tabler:x" />
	<span>Delete</span>
</x-button>
```

### Default slot content

A view component's slot can define a default value, which will be used when a view using that component doesn't pass any value to it:

```html x-my-component.view.php
<div>
    <x-slot>Fallback value</x-slot>
    <x-slot name="a">Fallback value for named slot</x-slot>
</div>
```

```html
<x-my-component />

<!-- Will render "Fallback value" and "Fallback value for named slot" -->
```

### Named slots

When a single slot is not enough, names can be attached to them. When using a component with a named slot, you may use the `<x-slot>` tag with a `name` attribute to render content in a named outlet:

```html x-base.view.php
<html lang="en">
	<head>
		<!-- … -->
		<x-slot name="styles" />
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

The above example uses a slot named `styles` in its `<head>` element. The `<body>` element has a default, unnamed slot. A view component may use `<x-base>` and optionally refer to the `styles` slot using the syntax mentioned above, or simply provide content that will be injected in the default slot:

```html index.view.php
<x-base title="Hello World">
	<!-- This part will be injected into the "styles" slot -->
	<x-slot name="styles">
		<style>
			body {
				/* … */
			}
		</style>
	</x-slot>

	<!-- Everything not living in a slot will be injected into the default slot -->
	<p>
		Hello World
	</p>
</x-base>
```

### Dynamic slots

Within a view component, a `$slots` variable will always be provided, allowing you to dynamically access the named slots within the component.

This variable is an instance of {`Tempest\View\Slot`}, with has a handful of properties:

- `{php}$slot->name`: the slot's name
- `{php}$slot->content`: the compiled content of the slot
- `{php}$slot->attributes`: all the attributes defined on the slot
- `{php}$slot->{attribute}`: dynamically access an attribute defined on the slot

For instance, the snippet below implements a tab component that accepts any number of tabs.

```html x-tabs.view.php
<div :foreach="$slots as $slot">
	<h1 :title="$slot->title">{{ $slot->name }}</h1>
	<p>{!! $slot->content !!}</p>
</div>
```

```html
<x-tabs>
	<x-slot name="php" title="PHP">This is the PHP tab</x-slot>
	<x-slot name="js" title="JavaScript">This is the JavaScript tab</x-slot>
	<x-slot name="html" title="HTML">This is the HTML tab</x-slot>
</x-tabs>
```

### Dynamic view components

On some occasions, you might want to dynamically render view components, for example, render a view component whose name is determined at runtime. You can use the `{html}<x-component :is="">` element to do so:

```html
<!-- $name = 'x-post' -->

<x-component :is="$name" :title="$title" />
```

### View component scope

View components act almost exactly the same as PHP's closures: they only have access to the variables you explicitly provide them, and any variable defined within a view component won't leak into the out scope.

The only difference with normal closures is that view components also have access to view-defined variables as local variables.

```html
<?php 
$title = 'foo';
?>

<!-- $title will need to be passed in explicitly, 
     otherwise `x-post` wouldn't know about it: -->

<x-post :title="$title"></x-post>
```

```php
/* View-defined data will be available within the component directly */
final class HomeController
{
    #[Get('/')]
    public function __invoke(): View
    {
        return view('<x-base />', siteTitle: 'Tempest');
    }
}
```

```html x-base.view.php
<h1>{{ $siteTitle }}</h1>
```

## Built-in components

Besides components that you may create yourself, Tempest provides a default set of useful built-in components to improve your developer experience. Any vendor-provided component can be published in your own project by running the `tempest install` command:

```console
./tempest install view-components

 <dim>│</dim> <em>Select which view components you want to install</em>
 <dim>│</dim> / <dim>Filter...</dim>
 <dim>│</dim> → ⋅ x-csrf-token
 <dim>│</dim>   ⋅ x-markdown
 <dim>│</dim>   ⋅ x-input
 <dim>│</dim>   ⋅ x-icon
 
<comment>…</comment>
```

Any component with the same name that lives in your local project will get precedence over vendor-defined components.

### `x-base`

A base template you can install into your own project as a starting point. This one includes the Tailwind CDN for quick prototyping.

```html
<x-base :title="Blog">
    <h1>Welcome!</h1>
</x-base>
```

### `x-form`

This component provides a form element that will post by default and includes the csrf token out of the box:

```html
<?php
use function \Tempest\Router\uri;
?>

<x-form :action="uri(StorePostController::class)">
    <!-- … -->
</x-form>
```

### `x-input`

A versatile input component that will render labels and validation errors automatically.

```html
<x-input name="title" />
<x-input name="content" type="textarea" label="Write your content" />
<x-input name="email" type="email" id="other_email" />
```

### `x-submit`

A submit button component that prefills with a "Submit" label:

```html
<x-submit />
<x-submit label="Send" />
```

### `x-csrf-token`

Includes the CSRF token in a form

```html
<form action="…">
    <x-csrf-token />
</form>
```

### `x-icon`

This component provides the ability to inject any icon from the [Iconify](https://iconify.design/) project in your templates.

```html
<x-icon name="material-symbols:php" class="size-4 text-indigo-400" />
```
will render
```html
<svg class="size-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

This component includes some optional props you can use to control width and height. As a fallback, if you specify no class, no style, no width & height, the component will render a default width and height, but you can override this behaviour in any of the following ways.

```html
<x-icon name="material-symbols:php" />
```
will render
```html
<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

Firstly, you can set width and height using Tailwind or custom classes. As long as you pass the `class` prop, the component will assume you are providing suitable dimensions, and will not check, or assert, any default dimensions.

```html
<x-icon name="material-symbols:php" class="w-[24px] h-[24px] text-indigo-400" />
```
will render
```html
<svg class="w-[24px] h-[24px] text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

Secondly, if you aren't using Tailwind, or wish to set for a single icon without making a class, you can instead pass dimensions via the `style` prop. Again, as long as you pass `style`, the component will assume you are providing suitable dimensions, and will not check, or assert, any default dimensions.

```html
<x-icon name="material-symbols:php" style="width: 24px; height: 24px;" />
```
will render
```html
<svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

Finally, you may provide the width and height properties directly with the props `width` and `height`. The component requires both to be set, or will render the fallback dimensions.

```html
<x-icon name="material-symbols:php" width="24px" height="24px" />
```
will render
```html
<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

The first time a specific icon is being rendered, Tempest will query the [Iconify API](https://iconify.design/docs/api/queries.html) to fetch the corresponding SVG tag. The result of this query will be cached indefinitely, so it can be reused at no further cost.

:::info
Iconify has a large collection of icon sets, which you may browse using the [Icônes](https://icones.js.org/) directory.
:::

### `x-vite-tags`

Tempest has built-in support for [Vite](https://vite.dev/), the most popular front-end development server and build tool. You may read more about [asset bundling](../2-features/05-asset-bundling.md) in the dedicated documentation.

This component simply injects registered entrypoints where it is called.

```html x-base.view.php
<html lang="en">
	<head>
		<x-vite-tags />
	</head>
	<!-- ... -->
</html>
```

Optionally, it accepts an `entrypoint` attribute. If it is passed, the component will not inject other entrypoints discovered by Tempest.

```html x-base.view.php
<x-vite-tags entrypoint="src/main.ts" />
```

### `x-markdown`

The `{html}x-markdown` component can be used to render markdown content, either directly from your view files, or by passing a content variables into it:

```html
<x-markdown># hi</x-markdown>
<x-markdown :content="$text" />
```

## Pre-processing views

In most applications, some views will need access to common data. To avoid having to manually provide this data to views through controller methods, it is possible to use view processors to manipulate views before they are rendered.

To create a view processor, create a class that implements the {`Tempest\View\ViewProcessor`} interface. It requires a `process()` method in which you may mutate and return the view that will be rendered.

```php
use Tempest\View\View;
use Tempest\View\ViewProcessor;

final class StarCountViewProcessor implements ViewProcessor
{
    public function __construct(
        private readonly GitHub $github,
    ) {}

    public function process(View $view): View
    {
        if (! $view instanceof WithStargazersCount) {
            return $view;
        }

        return $view->data(stargazers: $this->github->getStarCount());
    }
}
```

The example above provides the `$stargazers` variable to all view classes that implement the `WithStargazersCount` interface.

## View caching

Tempest views are always compiled to plain PHP code before being rendered. During development, this is done on-the-fly, every time. In production, these compiled views should be cached to avoid the performance overhead. This is done by setting the `{txt}{:hl-property:VIEW_CACHE:}` environment variable:

```env .env
{:hl-property:VIEW_CACHE:}={:hl-keyword:true:}
```

During deployments, that cache must be cleared in order to not serve outdated views to users. You may do that by running `tempest view:clear` on every deploy.

## Tempest View as a standalone engine

Tempest View is also designed to be used as a standalone engine in whatever PHP project you want. Start by requiring `tempest/view`:

```sh
composer require tempest/view
```

As a bare minimum setup, you can create an instance of the renderer by calling `TempestViewRenderer::make()`:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use function Tempest\View\view;

$renderer = TempestViewRenderer::make();

$html = $renderer->render(view('home.view.php', name: 'Brent'));
```

If, however, you want view component support, you will need to provide a `ViewConfig` object as well:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewConfig;

$config = new ViewConfig()->addViewComponents(
    __DIR__ . '/components/x-base.view.php',
    __DIR__ . '/components/x-footer.view.php',
    __DIR__ . '/components/x-header.view.php',
);

$renderer = TempestViewRenderer::make($config);
```

If you want to rely on Tempest's discovery to find view components, you can boot a minimal version of Tempest, and resolve the view renderer from the container:

```php
use Tempest\Core\Tempest;
use Tempest\View\ViewRenderer;

$container = Tempest::boot(__DIR__);

$html = $container->get(ViewRenderer::class)->render(
    view('home.view.php', name: 'Brent')
);
```

You can choose whichever way you prefer. Chances are that, if you use the minimal setup without booting Tempest, you'll want to add a custom view component loader. That's up to you to implement then.

### A note on caching

When you're using the minimal setup, view caching can be enabled by passing in a `$viewCache` parameter into `TempestViewRenderer::make()`:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewCache;

$renderer = TempestViewRenderer::make(
    viewCache: ViewCache::create(),
);
```

It's recommended to turn view caching on in production environments. To clear the view cache, you can call the `clear()` method on the `ViewCache` object:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewCache;

$viewCache = ViewCache::create();
$viewCache->clear();

$renderer = TempestViewRenderer::make(
    viewCache: $viewCache,
);
```

## Separate view directories

View files can live in any directory that is discoverable by Tempest. That means: a directory with a PSR-4 namespace associated with it. If you want your view files to live outside of `src` or `app`, you can add a namespace for it in composer.json:

```json composer.json
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "Views\\": "views/"
    },
}
```

Don't forget to run `composer up` after making changes to your composer.json file.

Note that view files themselves don't need a namespace; this namespace is only here to tell Tempest that `views/` is a directory it should scan. If you want to add a class in the `Views` namespace (like, for example, a [custom view object](/2.x/essentials/views#using-dedicated-view-objects)), then that is possible as well.

## Using other engines

While Tempest View is simple to use, it currently lacks tooling support from editors and IDEs. You may also simply prefer other templating engines. For these reasons, you may use any other engine of your choice.

Out-of-the-box, Tempest has support for Twig and Blade. Note that the view loaders for other engines are not based on Tempest's discovery, so the syntax to refer to a specific view might differ.

### Using Twig

You will first need to install the Twig engine. It is provided by the `twig/twig` package:

```sh
composer require twig/twig
```

The next step is to provide the configuration needed for Twig to find your view files.

```php app/twig.config.php
return new TwigConfig(
    viewPaths: [
        __DIR__ . '/views/',
    ],
);
```

Finally, update the view configuration to use the Twig renderer:

```php view.config.php
return new ViewConfig(
    rendererClass: \Tempest\View\Renderers\TwigViewRenderer::class,
);
```

### Using Blade

You will first need to install the Blade engine. Tempest provides a bridge distributed as `tempest/blade`:

```
composer require tempest/blade
```

The next step is to provide the configuration needed for Blade to find your view files.

```php blade.config.php
return new BladeConfig(
    viewPaths: [
        __DIR__ . '/views/',
    ],
);
```

Finally, update the view configuration to use the Blade renderer:

```php view.config.php
return new ViewConfig(
    rendererClass: \Tempest\View\Renderers\BladeViewRenderer::class,
);
```

### Using something else

Tempest refers to the view configuration to determine which view renderer should be used. By default, it uses Tempest View's renderer, {`Tempest\View\Renderers\TempestViewRenderer`}. When using Blade or Twig, we provided {`Tempest\View\Renderers\BladeViewRenderer`} or {`Tempest\View\Renderers\TwigViewRenderer`}, respectively.

#### Implementing your own renderer

If you prefer using another templating engine, you will need to write your own renderer by implementing the {`Tempest\View\ViewRenderer`} interface.

This interface only requires a `render` method. It will be responsible for taking a {`Tempest\View\View`} instance and rendering it to a PHP file.

As an example, the Blade renderer is as simple as the following:

```php
use Tempest\Blade\Blade;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final readonly class BladeViewRenderer implements ViewRenderer
{
    public function __construct(
        private Blade $blade,
    ) {
    }

    public function render(View|string|null $view): string
    {
        return $this->blade->render($view->path, $view->data);
    }
}
```

Once your renderer is implemented, you will need to configure Tempest to use it. This is done by creating or updating a `ViewConfig`:

```php view.config.php
return new ViewConfig(
    rendererClass: YourOwnViewRenderer::class,
);
```

#### Initializing your engine

The renderer will be called every time a view is rendered. If your engine has an initialization step, it may be a good idea to use a singleton [initializer](../1-essentials/05-container.md#dependency-initializers) to construct it.

As an example, here is a simplified version of the initializer that creates the `Blade` object, used by the Blade renderer:

```php
use Tempest\Blade\Blade;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final readonly class BladeInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        if (! class_exists(Blade::class)) {
            return false;
        }

        return $class->getName() === Blade::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        $bladeConfig = $container->get(BladeConfig::class);

        return new Blade(
            viewPaths: $bladeConfig->viewPaths,
            cachePath: Tempest\internal_storage_path($bladeConfig->cachePath ?? 'cache/blade'),
        );
    }
}
```


---
title: Database
description: "Tempest's database component provides data persistence to SQLite, MySQL, and PostgreSQL databases through a query builder and decoupled model architecture."
keywords: ["experimental", "orm", "database", "sqlite", "postgresql", "pgsql", "mysql", "query", "sql", "connection", "models"]
---

:::warning
Tempest's database component is currently experimental and is not covered by our backwards compatibility promise.
:::

## Connecting to a database

By default, Tempest connects to a local SQLite database located in its internal storage, `.tempest/database.sqlite`. The default database connection can be overridden by creating a [configuration file](../1-essentials/06-configuration.md#configuration-files):

```php app/Config/database.config.php
use Tempest\Database\Config\SQLiteConfig;
use function Tempest\root_path;

return new SQLiteConfig(
    path: root_path('database.sqlite'),
);
```

Alternatively, connect to another database by returning a different configuration object from the file. Available configuration classes include {b`Tempest\Database\Config\SQLiteConfig`}, {b`Tempest\Database\Config\MysqlConfig`}, and {b`Tempest\Database\Config\PostgresConfig`}:

```php app/Config/database.config.php
use Tempest\Database\Config\PostgresConfig;
use function Tempest\env;

return new PostgresConfig(
    host: env('DATABASE_HOST', default: '127.0.0.1'),
    port: env('DATABASE_PORT', default: '5432'),
    username: env('DATABASE_USERNAME', default: 'postgres'),
    password: env('DATABASE_PASSWORD', default: 'postgres'),
    database: env('DATABASE_DATABASE', default: 'postgres'),
);
```

## Querying the database

Multiple approaches exist for querying the database, all of which execute a {b`Tempest\Database\Query`} on the {b`Tempest\Database\Database`} class. The most straightforward approach is to inject {b`Tempest\Database\Database`}:

```php
use Tempest\Database\Database;
use Tempest\Database\Query;

final class BookRepository
{
    public function __construct(
        private readonly Database $database,
    ) {}

    public function findById(int $id): array
    {
        return $this->database->fetchFirst(new Query(
            sql: 'SELECT id, title FROM books WHERE id = ?',
            bindings: [$id],
        ));
    }
}
```

Manually building and executing queries provides maximum flexibility. Tempest's query builder offers a more convenient approach with fluent methods that abstract database-specific syntax differences.

```php
use function Tempest\Database\query;

final class BookRepository
{
    public function findById(int $id): array
    {
        return query('books')
            ->select('id', 'title')
            ->where('id', $id)
            ->first();
    }
}
```

Both methods can be combined by using the query builder to construct a query that is then executed on a database:

```php
use Tempest\Database\Database;
use function Tempest\Database\query;

final class BookRepository
{
    public function __construct(
        private readonly Database $database,
    ) {}

    public function findById(int $id): array
    {
        return $this->database->fetchFirst(
            query('books')
                ->select('id', 'title')
                ->where('id = ?', $id),
        );
    }
}
```

## Models

A common use case in many applications is to represent persisted data as objects within the codebase. Model classes fulfill this purpose. Tempest decouples models from the database as much as possible, allowing any object with public typed properties to represent a table.

These objects do not require implementing any interface—they can be plain PHP objects:

```php app/Book.php
use Tempest\Validation\Rules\HasLength;
use App\Author;

final class Book
{
    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

Because model objects are not tied specifically to the database, Tempest's [mapper](../2-features/01-mapper.md) can map data from many different sources to them. For instance, models can be persisted as JSON:

```php
use function Tempest\Mapper\map;

$books = map($json)->collection()->to(Book::class); // from JSON source to Book collection
$json = map($books)->toJson(); // from Book collection to JSON
```

### Models and query builders

The query builder provides a straightforward approach to persisting models to a database. It can work with tables and arrays as well as map data to and from model objects. Specify the class to query, and Tempest handles the mapping.

The following example selects all fields from the table related to the `Book` model, loads the related `chapters` and `author`, filters by the book ID, and returns the first result:

```php
use App\Models\Book;
use function Tempest\Database\query;

final class BookRepository
{
    public function findById(int $id): Book
    {
        return query(Book::class)
            ->select()
            ->with('chapters', 'author')
            ->where('id', $id)
            ->first();
    }
}
```

Tempest infers all relation-type information from the model class by analyzing property types. For example, a property with the `Author` type is assumed to be a "belongs to" relation, while a property with the `/** @var \App\Books\Chapter[] */` docblock is assumed to be a "has many" relation on the `Chapter` model.

Beyond selecting models, any query builder can be used with model objects:

```php
use App\Models\Book;
use Tempest\Database\PrimaryKey;

;use function Tempest\Database\query;

final class BookRepository
{
    public function create(Book $book): PrimaryKey
    {
        return query(Book::class)
            ->insert($book)
            ->execute();
    }
}
```

### Model relations

Tempest infers relations based on type information from the model class. A public property with a reference to another class is assumed to be a {b`Tempest\Database\BelongsTo`} relation, while a property with a docblock that defines an array type is assumed to be a {b`Tempest\Database\HasMany`} relation.

```php
use App\Author;

final class Book
{
    public ?Author $author = null;
    //      ^ BelongsTo relation

    /** @var \App\Books\Chapter[] */
    public array $chapters = [];
    //     ^ HasMany relation
}
```

:::warning
Due to a restriction with reflection, relation types in docblocks must always be fully qualified. Short class names are not supported.
:::

### Relation attributes

Tempest infers all information needed to build queries. When property names and type information do not map one-to-one to the database schema, dedicated attributes can be used to define relations.

Available attributes are {b`#[Tempest\Database\HasMany]`}, {b`#[Tempest\Database\HasOne]`}, and {b`#[Tempest\Database\BelongsTo]`}. They accept two arguments:

- `ownerJoin`, which is used to build the owner's side of join query,
- `relationJoin`, which is used to build the relation's side of the join query.

```php
use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;

final class Book
{
    #[BelongsTo(ownerJoin: 'books.author_uuid', relationJoin: 'authors.uuid')]
    public ?Author $author = null;

    /** @var \App\Chapter[] */
    #[HasMany(ownerJoin: 'chapters.book_uuid', relationJoin: 'books.uuid')]
    public array $chapters = [];

    #[HasOne(ownerJoin: 'isbns.book_uuid', relationJoin: 'books.uuid')]
    public ?Isbn $isbn = null;
}
```

The _owner_ part of the relation represents the table that _owns_ the relation—the table with a column referencing another table. The _relation_ part represents the table that is _being referenced by another table_.

The {b`Tempest\Database\BelongsTo`} relation starts with _the owner join_, while both {b`Tempest\Database\HasMany`} and {b`Tempest\Database\HasOne`} start with _the relation join_.

The full owner or relation join does not need to include both the table and field names. Field names can be specified without the table name, in which case the table name is inferred from the related model:

```php
use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;

final class Book
{
    #[BelongsTo(ownerJoin: 'author_uuid', relationJoin: 'uuid')]
    public ?Author $author = null;

    /** @var \App\Chapter[] */
    #[HasMany(ownerJoin: 'chapter_uuid', relationJoin: 'uuid')]
    public array $chapters = [];

    #[HasOne(ownerJoin: 'book_uuid', relationJoin: 'uuid')]
    public ?Isbn $isbn = null;
}
```

### Has one through

The {b`#[Tempest\Database\HasOneThrough]`} attribute defines a one-to-one relationship that traverses an intermediate model. This lets you access a distant relation directly, resolved in a single SQL query with two JOINs.

```php
use Tempest\Database\HasOne;
use Tempest\Database\HasOneThrough;

final class Author
{
    #[HasOne]
    public ?Profile $profile = null;

    #[HasOneThrough(Profile::class)]
    public ?Address $address = null;
}
```

The `through` parameter specifies the intermediate model class. The target model is inferred from the property type. This generates SQL like:

```sql
LEFT JOIN profiles ON profiles.author_id = authors.id
LEFT JOIN addresses ON addresses.profile_id = profiles.id
```

When conventions don't match, optional parameters can override the join fields:

```php
#[HasOneThrough(
    through: Profile::class,
    ownerJoin: 'custom_author_fk',
    relationJoin: 'uuid',
    throughOwnerJoin: 'custom_profile_fk',
    throughRelationJoin: 'uuid',
)]
public ?Address $address = null;
```

- `ownerJoin`: FK on the intermediate table pointing to the owner
- `relationJoin`: PK on the owner table
- `throughOwnerJoin`: FK on the target table pointing to the intermediate
- `throughRelationJoin`: PK on the intermediate table

### Has many through

The {b`#[Tempest\Database\HasManyThrough]`} attribute defines a one-to-many relationship that traverses an intermediate model. This lets you access a collection of distant relations directly, resolved in a single SQL query with two JOINs.

```php
use Tempest\Database\HasManyThrough;

final class Author
{
    /** @var \App\Payment\Payment[] */
    #[HasManyThrough(Contract::class)]
    public array $payments = [];
}
```

The `through` parameter specifies the intermediate model class. The target model is inferred from the docblock's array type. This generates SQL like:

```sql
LEFT JOIN contracts ON contracts.author_id = authors.id
LEFT JOIN payments ON payments.contract_id = contracts.id
```

The same optional parameters as `HasOneThrough` are available for custom join fields: `ownerJoin`, `relationJoin`, `throughOwnerJoin`, and `throughRelationJoin`.

### Belongs to many

The {b`#[Tempest\Database\BelongsToMany]`} attribute defines a many-to-many relationship using a pivot table. Both sides of the relationship can declare the attribute.

```php
use Tempest\Database\BelongsToMany;

final class Author
{
    /** @var \App\Tag\Tag[] */
    #[BelongsToMany]
    public array $tags = [];
}

final class Tag
{
    /** @var \App\Author\Author[] */
    #[BelongsToMany]
    public array $authors = [];
}
```

The pivot table name is inferred alphabetically from both model table names (e.g., `authors` + `tags` = `authors_tags`). This generates SQL like:

```sql
LEFT JOIN authors_tags ON authors_tags.author_id = authors.id
LEFT JOIN tags ON tags.id = authors_tags.tag_id
```

A custom pivot table name and join fields can be specified:

```php
#[BelongsToMany(
    pivot: 'custom_pivot_table',
    ownerJoin: 'custom_author_fk',
    relationJoin: 'uuid',
    relatedOwnerJoin: 'custom_tag_fk',
    relatedRelationJoin: 'uuid',
)]
public array $tags = [];
```

- `pivot`: Custom pivot table name
- `ownerJoin`: FK on pivot pointing to the owner model
- `relationJoin`: PK on the owner model
- `relatedOwnerJoin`: FK on pivot pointing to the related model
- `relatedRelationJoin`: PK on the related model

### Using UUIDs as primary keys

By default, Tempest uses auto-incrementing integers as primary keys. UUIDs can be used as primary keys instead by annotating the {b`Tempest\Database\PrimaryKey`} property with the {b`#[Tempest\Database\Uuid]`} attribute. Tempest automatically generates a UUID v7 when a new model is created:

```php app/Books/Book.php
use Tempest\Database\PrimaryKey;
use Tempest\Database\Uuid;

final class Book
{
    #[Uuid]
    public PrimaryKey $uuid;

    public function __construct(
        public string $title,
        public string $author_name,
    ) {}
}
```

Within migrations, specify `uuid: true` to the `primary()` method, or use `uuid()` directly:

```php app/Books/CreateBooksTable.php
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateBooksTable implements MigratesUp
{
    public string $name = '2024-08-12_create_books_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('books')
            ->primary('uuid', uuid: true)
            ->text('title')
            ->text('author_name');
    }
}
```

:::warning
Currently, the [`IsDatabaseModel`](#the-is-database-model-trait) trait already provides a primary `$id` property. It is therefore not possible to use UUIDs alongside `IsDatabaseModel`.
:::

### Table names

Tempest infers the table name for a model class based on the model's classname. By default, the table name is the pluralized, `snake_cased` version of the base class name. This can be overridden using the {b`Tempest\Database\Table`} attribute:

```php
use Tempest\Database\Table;

#[Table('table_books')]
final class Book
{
    // …
}
```

It is possible to define your own convention for naming tables without specifying the {b`Tempest\Database\Table`} attribute on all your models. To do so, set the `namingStrategy` parameter of your database configuration to a {b`Tempest\Database\Tables\NamingStrategy`} instance.

By default, Tempest provides a {b`Tempest\Database\Tables\PascalCaseStrategy`} and {b`Tempest\Database\Tables\PluralizedSnakeCaseStrategy`} strategy, the latter being the default. Of course, custom strategies can be implemented as needed:

:::code-group

```php app/Database/PrefixedPascalCaseStrategy.php
use Tempest\Database\Tables\NamingStrategy;
use function Tempest\Support\str;

final class PrefixedPascalCaseStrategy implements NamingStrategy
{
    public function getName(string $model): string
    {
        return 'table_' . str($model)
            ->classBasename()
            ->pascal()
            ->toString();
    }
}
```

```php app/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    namingStrategy: new PrefixedPascalCaseStrategy(),
);
```

:::

### Migration prefixes

When generating a migration file via `make:migration`, Tempest prefixes the file name with a sortable identifier so that migrations run in the correct order. By default, a date-based prefix is used (e.g. `2025-06-15_create_books_table`).

The prefix format is determined by the `migrationNamingStrategy` property of your database configuration, which accepts any {b`Tempest\Database\Migrations\MigrationNamingStrategy`} instance.

Tempest ships with two built-in strategies:

- {b`Tempest\Database\Migrations\DatePrefixStrategy`} — generates a `Y-m-d` date prefix (default). Pass `useTime: true` to include hours, minutes and seconds (`Y-m-d_His`).
- {b`Tempest\Database\Migrations\Uuidv7PrefixStrategy`} — generates a UUIDv7 prefix, which is both unique and time-ordered.

You can also implement your own strategy:

:::code-group

```php app/Database/IncrementingPrefixStrategy.php
use Tempest\Database\Migrations\MigrationNamingStrategy;

final class IncrementingPrefixStrategy implements MigrationNamingStrategy
{
    public function generatePrefix(): string
    {
        return sprintf('%06d', /* resolve the next sequence number */);
    }
}
```

```php app/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    migrationNaming: new IncrementingPrefixStrategy(),
);
```

:::

### Data transfer object properties

Arbitrary objects can be stored in a `json` column when they are not part of the relational schema. Annotate the class with {b`#[Tempest\Mapper\SerializeAs]`} and provide a unique identifier to represent the object. The identifier must map to a single, distinct class.

:::code-group

```php app/User.php
use Tempest\Mapper\SerializeAs;

final class User implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed, SensitiveParameter]
        public ?string $password,
        public Settings $settings,
    ) {}
}
```

```php app/Settings.php
#[SerializeAs('user_settings')]
final class Settings
{
    public function __construct(
        public readonly Theme $theme,
        public readonly bool $hide_sidebar_by_default,
    ) {}
}
```

```php app/Theme.php
enum Theme: string
{
    case DARK = 'dark';
    case LIGHT = 'light';
    case AUTO = 'auto';
}
```

:::

### Hashed properties

The {b`#[Tempest\Database\Hashed]`} attribute hashes the model's property during serialization. If the property is already hashed, Tempest detects this and avoids re-hashing. Common use cases include passwords, tokens, and other sensitive values.

```php app/User.php
final class User
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed, SensitiveParameter]
        public ?string $password,
    ) {}
}
```

:::info
Hashing requires the `SIGNING_KEY` environment variable to be set, as it is used as the hashing key.
:::

### Encrypted properties

The {b`#[Tempest\Database\Encrypted]`} attribute encrypts the model's property during serialization and decrypts it during deserialization. If the property is already encrypted, Tempest detects this and avoids re-encrypting.

```php app/User.php
final class User
{
    // ...

    #[Encrypted]
    public ?string $accessToken;
}
```

:::info
Encryption uses the `SIGNING_KEY` environment variable as the encryption key.
:::

### Virtual properties

By default, all public properties are considered part of the model's query fields. To exclude a field from the database mapper, use the {b`#[Tempest\Database\Virtual]`} attribute.

```php
use Tempest\Database\Virtual;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;

final class Book
{
    // …

    public DateTime $publishedAt;

    #[Virtual]
    public DateTime $saleExpiresAt {
        get => $this->publishedAt->add(Duration::days(5));
    }
}
```

### Hidden properties

Sensitive properties can be marked with the {b`#[Tempest\Mapper\Hidden]`} attribute to exclude them from SELECT queries. This is useful for properties like passwords, API keys, or other sensitive data that should not be fetched or exposed by default.

```php
use Tempest\Database\IsDatabaseModel;
use Tempest\Mapper\Hidden;

final class User
{
    use IsDatabaseModel;

    public string $email;

    #[Hidden]
    public string $password;

    #[Hidden]
    public ?string $apiKey = null;
}
```

Hidden properties are still included in INSERT and UPDATE queries, allowing them to be persisted to the database.

To explicitly include hidden fields in a query, use the `include()` method on the query builder:

```php
// Password is not included in the query
$user = User::select()->where('email', $email)->first();

// Password is explicitly included
$user = User::select()
    ->include('password')
    ->where('email', $email)
    ->first();

// Multiple hidden fields can be included
$user = User::select()
    ->include('password', 'apiKey')
    ->where('email', $email)
    ->first();
```

:::info
Unlike {b`#[Virtual]`} which marks computed properties that don't exist in the database, {b`#[Hidden]`} is for real database columns that should be protected from accidental exposure.
:::

:::info
The {b`#[Hidden]`} attribute also excludes properties from serialization. See the [mapper documentation](../2-features/01-mapper.md#hiding-properties-from-serialization) for more information.
:::

### The `IsDatabaseModel` trait

The {b`Tempest\Database\IsDatabaseModel`} trait provides an active record pattern. This trait enables database interaction via static methods on the model class itself.

:::code-group

```php app/Book.php
use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\HasLength;
use App\Author;

final class Book
{
    use IsDatabaseModel;

    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

```php "Query examples"
$book = Book::create(
    title: 'Timeline Taxi',
    author: $author,
    chapters: [
        new Chapter(index: 1, contents: '…'),
        new Chapter(index: 2, contents: '…'),
        new Chapter(index: 3, contents: '…'),
    ],
);

$books = Book::select()
    ->whereAfter('publishedAt', DateTime::now())
    ->orderBy('title', Direction::DESC)
    ->limit(10)
    ->with('author')
    ->all();

$books[0]->chapters[2]->delete();
```

:::

### Filtering by relations

Use `whereHas` and `whereDoesntHave` to filter models based on whether related records exist:

```php
// Authors who have at least one book
$authors = Author::select()
    ->whereHas(relation: 'books')
    ->all();

// Authors who have no books
$authors = Author::select()
    ->whereDoesntHave(relation: 'books')
    ->all();
```

Add a callback to constrain the related records:

```php
// Authors who have a published book
$authors = Author::select()
    ->whereHas(relation: 'books', callback: function (SelectQueryBuilder $query): void {
        $query->whereField(field: 'published', value: true);
    })
    ->all();
```

Use `operator` and `count` for count-based filtering:

```php
// Authors with 3 or more books
$authors = Author::select()
    ->whereHas(relation: 'books', operator: WhereOperator::GREATER_THAN_OR_EQUAL, count: 3)
    ->all();
```

Dot notation supports nested relations:

```php
// Authors who have books with chapters
$authors = Author::select()
    ->whereHas(relation: 'books.chapters')
    ->all();
```

These methods work on all query builders:

```php
// Count authors with books
$count = Author::count()->whereHas(relation: 'books')->execute();

// Delete authors without books
query(model: Author::class)->delete()->whereDoesntHave(relation: 'books')->execute();

// Update authors who have books
query(model: Author::class)->update(verified: true)->whereHas(relation: 'books')->execute();
```

### Querying relation properties

While the global `query(Model::class)` function creates a query builder for any model, models using the `IsDatabaseModel` trait also have a `query()` method that returns a query builder scoped to a specific relation. The returned `QueryBuilder` is pre-filtered to only include records belonging to that model:

```php
// Select with constraints
$books = $author->query('books')->select()->whereField(field: 'title', value: 'Timeline Taxi')->all();
$books = $author->query('books')->select()->limit(limit: 5)->all();

// Count related records
$count = $author->query('books')->count()->execute();

// Update scoped to relation
$author->query('books')->update(title: 'Updated')->execute();

// Delete scoped to relation
$author->query('books')->delete()->execute();
```

The `query()` method works with all relation types:

```php
// HasMany / HasOne — simple FK on related table
$author->query('books')->select()->all();
$book->query('isbn')->select()->first();

// BelongsTo — subquery through owner's FK
$book->query('author')->select()->first();

// HasManyThrough / HasOneThrough — subquery through intermediate table
$tag->query('reviewers')->select()->all();
$tag->query('topReviewer')->select()->first();

// BelongsToMany — subquery through pivot table
$tag->query('books')->select()->all();
```

## Migrations

When persisting objects to the database, a table is required to store the data. A migration is a file that instructs the framework how to manage the database schema.

Tempest uses migrations to create and update databases across different environments in a consistent way.

### Writing migrations

Classes implementing the {b`Tempest\Database\MigratesUp`} or {b`Tempest\Database\MigratesDown`} interface and `.sql` files are automatically [discovered](../1-essentials/05-discovery) and registered as migrations. These files can be stored anywhere in the application.

:::code-group

```php app/CreateBooksTable.php
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateBooksTable implements MigratesUp
{
    public string $name = '2024-08-12_create_books_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('books')
            ->primary()
            ->text('title')
            ->datetime('created_at')
            ->datetime('published_at', nullable: true)
            ->belongsTo('books.author_id', 'authors.id');
    }
}
```

```sql app/2025-01-01_create_publisher_table.sql
CREATE TABLE Publisher
(
    `id`   INTEGER,
    `name` TEXT NOT NULL
);
```

:::

:::info
The file name of `{txt}.sql` migrations and the `{txt}{:hl-type:$name:}` property of `DatabaseMigration` classes determine the order in which migrations are applied. Using the creation date as a prefix ensures chronological ordering.
:::

When using migration classes, Tempest handles the SQL dialect automatically with support for MySQL, PostgreSQL, and SQLite. When using raw SQL files, a hard-coded SQL dialect must be chosen based on database requirements.

### Up and down migrations

Up-migrations move the database schema forward. Down-migrations roll back the database schema to a previous state.

Down migrations are complex to test and manage, especially in production environments. For this reason, they require explicitly implementing the {`Tempest\Database\MigratesDown`} interface.

```php
use Tempest\Database\MigratesDown;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateBookTable implements MigratesDown
{
    public string $name = '2024-08-12_drop_book_table';

    public function down(): QueryStatement
    {
        return new DropTableStatement('books');
    }
}
```

### Applying migrations

Several [console commands](../3-console/02-building-console-commands) are provided to work with migrations. These commands apply, roll back, or erase and re-apply migrations.

When deploying the application to production, use `php tempest migrate:up` to apply the latest migrations.

```sh
{:hl-comment:# Apply migrations not yet run in the current environment:}
./tempest migrate:up

{:hl-comment:# Drop all tables and rerun migrate:up:}
./tempest migrate:fresh

{:hl-comment:# Validate the integrity of migration files:}
./tempest migrate:validate
```

### Validating migrations

By default, an integrity check is performed before applying database migrations with the `migrate:up` and `migrate:fresh` commands. This validation compares the current migration hash with the one stored in the `migrations` table, if it was already applied in the environment.

If a migration file has been tampered with, the command reports it as a validation failure. This behavior can be disabled using the `--no-validate` argument.

The `migrate:validate` command can be used to validate the integrity of migrations at any point in any environment:

```sh
./tempest migrate:validate
```

:::info
Only the actual SQL query of a migration, minified and stripped of comments, is hashed during validation. Code-style changes, such as indentation, formatting, and comments do not impact the validation process.
:::

### Rehashing migrations

The `migrate:rehash` command can be used to bypass migration integrity checks and update the hashes of migrations in the database.

```sh
./tempest migrate:rehash
```

:::warning
Bypassing migration integrity checks may result in a broken database state. Use this command only when migration files are confirmed to be correct and consistent across environments.
:::

## Database seeders

Database seeders populate the database with data. These classes can fill the database with any required data. To create a seeder, implement the {b`\Tempest\Database\DatabaseSeeder`} interface.

```php
use Tempest\Database\DatabaseSeeder;
use UnitEnum;

final class BookSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        query(Book::class)
            ->insert(title: 'Timeline Taxi')
            ->onDatabase($database)
            ->execute();
    }
}
```

The `$database` property is passed into the `run()` method. If a database has been specified for the seeder, this property reflects that choice.

Database seeders can be run in two ways: via the `database:seed` command or via the `migrate:fresh` command. Note that `database:seed` always _appends_ the seeded data to the existing database.

```console
./tempest database:seed
./tempest migrate:fresh --seed
```

### Multiple seeders

Multiple seeder classes can be created. Each seeder class can bring the database into a specific state or seed specific parts of the database.

When multiple seeder classes exist, Tempest prompts for selection:

```console
./tempest database:seed

 │ <em>Which seeders do you want to run?</em>
 │ / <dim>Filter...</dim>
 │ → ⋅ Tests\Tempest\Fixtures\MailingSeeder
 │   ⋅ Tests\Tempest\Fixtures\InvoiceSeeder
```

Both the `database:seed` and `migrate:fresh` commands also allow to pick one specific seeder or run all seeders automatically.

```console
./tempest database:seed --all
./tempest database:seed --seeder="Tests\Tempest\Fixtures\MailingSeeder"

./tempest migrate:fresh --seed --all
./tempest migrate:fresh --seeder="Tests\Tempest\Fixtures\MailingSeeder"
```

### Seeding on multiple databases

Seeders support multiple databases via the `--database` option. See the [Multiple databases](#multiple-databases) section for more information.

```console
./tempest database:seed --database="backup"
./tempest migrate:fresh --database="main"
```

## Multiple databases

Tempest supports connecting to multiple databases simultaneously. This is useful for transferring data between databases or building multi-tenant systems.

### Connecting to multiple databases

To connect to multiple databases, create multiple database config files and attach a tag to each database config object:

:::code-group

```php app/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
    tag: 'main',
);
```

```php app/database-backup.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database-backup.sqlite',
    tag: 'backup',
);
```

:::

Enums provide better refactorability when used as tags:

```php app/database-backup.config.php
use Tempest\Database\Config\SQLiteConfig;
use App\Database\DatabaseType;

return new SQLiteConfig(
    path: __DIR__ . '/../database-backup.sqlite',
    tag: DatabaseType::BACKUP,
);
```

:::info
The default connection is the connection without a tag.
:::

### Querying multiple databases

With multiple databases configured, several approaches exist for using them when working with queries or models. The first approach is to inject separate database instances using their tags:

```php
use Tempest\Database\Database;
use Tempest\Container\Tag;
use App\Database\DatabaseType;
use function Tempest\Database\query;

final class DatabaseBackupCommand
{
    public function __construct(
        private Database $main,
        #[Tag(DatabaseType::BACKUP)] private Database $backup,
    ) {}

    public function __invoke(): void
    {
        $books = $this->main->fetch(
            query(Book::class)
                ->select()
                ->where('published_at < ?', '2025-01-01')
        );

        $this->backup->execute(
            query(Book::class)->insert(...$books)
        );
    }
}
```

A shorthand approach is available that does not require injecting multiple database instances:

```php
use App\Database\DatabaseType;
use function Tempest\Database\query;

final class DatabaseBackupCommand
{
    public function __invoke(): void
    {
        $books = query(Book::class)
            ->select()
            ->where('published_at < ?', '2025-01-01')
            ->onDatabase(DatabaseType::MAIN)
            ->all();

        query(Book::class)
            ->insert(...$books)
            ->onDatabase(DatabaseType::BACKUP)
            ->execute();
    }
}
```

The same approach works with active-record style models:

```php
use App\Database\DatabaseType;

final class DatabaseBackupCommand
{
    public function __invoke(): void
    {
        $books = Book::select()
            ->where('published_at < ?', '2025-01-01')
            ->onDatabase(DatabaseType::MAIN)
            ->all();

        Book::insert(...$books)
            ->onDatabase(DatabaseType::BACKUP)
            ->execute();
    }
}
```

### Migrating multiple databases

To run migrations on a specific database, you must specify the `database` flag to the migration command:

```sh
./tempest migrate:up --database=main
./tempest migrate:down --database=backup
./tempest migrate:fresh --database=main
./tempest migrate:validate --database=backup
```

:::info
When no database is specified, the default database is used. The default database is the one without a tag.
:::

### Database-specific migrations

Some migrations may need to run only on specific databases. Any database migration class can implement {b`Tempest\Database\ShouldMigrate`}, which adds a `shouldMigrate()` method to determine whether a migration should run based on the database:

```php
use Tempest\Database\Database;
use Tempest\Database\MigratesUp;
use Tempest\Database\ShouldMigrate;

final class MigrationForBackup implements MigratesUp, ShouldMigrate
{
    public string $name = '…';

    public function shouldMigrate(Database $database): bool
    {
        return $database->tag === DatabaseType::BACKUP;
    }

    public function up(): QueryStatement
    { /* … */ }
}
```

### Dynamic databases

In systems with dynamic databases, such as multi-tenant systems, a hard-coded tag may not always be available to configure and resolve the correct database. In these cases, dynamic databases can be added as needed:

```php
final class ConnectTenant
{
    public function __invoke(string $tenantId): void
    {
        $this->container->config(new SQLiteConfig(
            path: __DIR__ . "/tenant-{$tenantId}.sqlite",
            tag: $tenantId,
        ));
    }
}
```

Migrations can be run programmatically on dynamically defined databases using the {b`Tempest\Database\Migrations\MigrationManager`}:

```php
use Tempest\Database\Migrations\MigrationManager;

final class OnboardTenant
{
    public function __construct(
        private MigrationManager $migrationManager,
    ) {}

    public function __invoke(string $tenantId): void
    {
        $setupMigrations = [
            new CreateMigrationsTable(),
            // Additional migrations
        ];

        foreach ($setupMigrations as $migration) {
            $this->migrationManager->onDatabase($tenantId)->executeUp($migration);
        }
    }
}
```

Dynamic database connections should be registered within the application's entry points. This can be accomplished with [middleware](/main/essentials/routing#route-middleware) or with a [kernel event hook](/main/extra-topics/package-development#provider-classes):

```php
use Tempest\Container\Container;
use Tempest\Router\HttpMiddleware;
use Tempest\Support\Priority;

#[Priority(Priority::HIGHEST)]
final class ConnectTenantMiddleware implements HttpMiddleware
{
    public function __construct(
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $tenantId = // Tenant ID resolution from request

        (new ConnectTenant)($tenantId);

        return $next($request);
    }
}
```


---
title: Console commands
description: "Learn how to write console commands with a modern, minimal syntax. In Tempest, this is done using attributes, which are automatically discovered by the framework."
---

## Overview

Tempest leverages [discovery](./05-discovery.md) to find class methods tagged with the {b`#[Tempest\Console\ConsoleCommand]`} attribute. Such methods will automatically be available as console commands through the `./tempest` executable.

Additionally, Tempest supports [console middleware](#middleware), which makes it easier to build some console features.

## Creating console commands

A console command is defined by adding the {b`#[Tempest\Console\ConsoleCommand]`} attribute to any class method. Usually, this is done in a dedicated command class, but it can be any method in any class.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(name: 'aircraft:track')]
    public function __invoke(): void
    {
        // …
    }
}
```

The command will be named after the class name and the method name. If you prefer, you may add a `name` argument to the {b`#[Tempest\Console\ConsoleCommand]`} attribute to give a dedicated name to the command.

You may learn more about [configuring commands](#configuring-commands) in the dedicated section.

### Writing to the output

You may use the {`Tempest\Console\Console`} interface to write to the output. You can do this by injecting it into your command class, or by using the {`Tempest\Console\HasConsole`} trait, which provides a `$console` property.

The console methods are documented, but you might use the following ones most often:

```php
// Writes a line to the output.
$this->console->writeln('Hello from Tempest!');

 // Writes an informational, error, or warning message.
$this->console->info('This is an informational message.');
$this->console->error('This is an error message.');
$this->console->warning('This is a warning.');

// Prompts for user input. Supports validation and multiple choices.
$this->console->ask('What should be the email?', validation: [new Email()]);

// Executes and reports the progress of a closure.
$this->console->task('Syncing...', $this->synchronize(...));
```

### Specifying an exit code

Optionally, console may return an exit code. By default, Tempest will infer the correct exit code, depending on whether the command was successful or not.

If you want more control over which exit code is returned, you may return an integer between 0 and 255. For convenience, Tempest comes with an {`Tempest\Console\ExitCode`} enumeration that has a handful of predefined exit codes, which are generally accepted to be standard.

```php
use Tempest\Console\ExitCode;

public function __invoke(): ExitCode
{
    if (! $this->hasBeenSetup()) {
        return ExitCode::ERROR;
    }

    // …

    return ExitCode::SUCCESS;
}
```

## Command arguments

The command definition is inferred by the method's parameters. This way, there is no need to remember a framework-specific syntax—this is simple, modern PHP.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand('aircraft:track')]
    public function __invoke(AircraftType $type, ?int $radius = null): void
    {
        // …
    }
}
```

All built-in types are supported, including enums. When a parameter is nullable, it is also optional when invoking the console command.

### Negating boolean arguments

You may negate boolean flags by prefixing them with `--no`.

For instance, if the command has a `$validate` parameter with a default value of `true`, using the `--no-validate` flag would set the value of `$validate` to `false`.

### Adding a description or an alias

You may provide the {b`#[Tempest\Console\ConsoleArgument]`} to any argument of the method definition. This may be used to describe the argument, change its name or specify an alias.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(
        name: 'aircraft:track',
        description: 'Updates operating aircraft in the database'
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Specifies the type of aircraft to track')]
        AircraftType $type,
        #[ConsoleArgument(
            description: 'Specifies the maximum radius around HQ to track aircraft in',
            aliases: ['r']
        )]
        ?int $radius = null
    ): void
    {
        // …
    }
}
```

Argument description are visible when using the `--help` flag during command invocation.

```console
<dim>./</dim>tempest aircraft:track --help

<h1>// AIRCRAFT:TRACK</h1>
Updates operating aircraft in the database

<h1>// USAGE</h1>
aircraft:track <type {<em>pc12</em>|<em>pc24</em>}> [<em>radius</em>=null]

<u>type</u>
Specifies the type of aircraft to track

<u>radius (r)</u>
Specifies the maximum radius around HQ to track aircraft in
```

## Configuring commands

The {b`#[Tempest\Console\ConsoleCommand]`} attribute accepts a few arguments that may provide more context to the user or affect its functionality.

For instance, the `middleware` argument accepts a list of [middleware classes](#middleware) for this command.

### Adding a description

You may use the `description` argument on the {b`#[Tempest\Console\ConsoleCommand]`} attribute to provide context to users regarding the functionality of the command.

This description is shown when listing console commands or when calling it with the `--help` argument.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(description: 'Updates operating aircraft in the database')]
    public function __invoke(): void
    {
        // …
    }
}
```

### Hiding the command

A command may be completely hidden from the command list by setting the `hidden` argument to `true`. The command will remain invokable, but will not be visible to the user when listing commands.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(hidden: true)]
    public function __invoke(): void
    {
        // …
    }
}
```

### Specifying a name

The `name` argument of the {b`#[Tempest\Console\ConsoleCommand]`} attribute allows for configuring the command name. This is the name used for the command invokation, and the name that is displayed when listing all commands.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand('aircraft:track')]
    public function __invoke(): void
    {
        // …
    }
}
```

### Specifying aliases

When a command is used a lot, you may add aliases instead of shortening its name. To do this, use the `aliases` argument of the {b`#[Tempest\Console\ConsoleCommand]`} attribute.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand('aircraft:track', aliases: ['track'])]
    public function __invoke(AircraftType $type): void
    {
        // …
    }
}
```

You may then call the command by using this alias.

### Preventing usage in production

Some commands are dangerous to use in a non-local environment. You may add the {b`Tempest\Console\Middleware\CautionMiddleware`} to a command to prevent it from being invoked in production. When this happens, the user will be alerted and provided with the choice to continue or abort the command execution.

```php
final readonly class SynchronizeAircraft
{
    #[ConsoleCommand('aircraft:sync', middleware: [CautionMiddleware::class])]
    public function __invoke(): void
    {
        // …
    }
}
```

## Interactive components

Tempest console comes with a range of interactive components that can be used to interact with the user while running a console command:

- `$console->ask()` will prompt the user for input and validate it.
- `$console->confirm()` will prompt the user for a yes/no answer.
- `$console->password()` will prompt the user for a password, the input will be masked.
- `$console->progressBar()` will render a progress bar.
- `$console->search()` will prompt the user with a search bar and update a result list in real-time.
- `$console->task()` will run a task and show a progress bar while it's running.

:::warning
Interactive components are only supported on Mac and Linux. On Windows, Tempest will fall back to non-interactive versions of these components.
:::

## Shell completion

Tempest provides shell completion for Zsh, Bash and Fish on Linux and macOS. This allows you to press `Tab` to autocomplete command names and options. On Windows, use WSL.

Completion relies on two things: a **completion script** sourced by your shell, and a **helper executable** (`vendor/bin/tempest-complete`) that performs the actual matching.

### Installing completions

Run the install command and follow the prompts:

```console
<dim>./</dim>tempest completion:install
```

This will:

1. Detect your shell (or use `--shell=zsh` / `--shell=bash` / `--shell=fish` to specify it manually).
2. Generate completion metadata (`commands.json`) for all registered commands.
3. Install the completion script to the appropriate location.

After installation, add the following line to your shell configuration file and restart your terminal:

```bash
# Zsh: add to ~/.zshrc
source ~/.tempest/completion/tempest.zsh

# Bash: add to ~/.bashrc
source ~/.tempest/completion/tempest.bash

# Fish: add to ~/.config/fish/config.fish
source ~/.tempest/completion/tempest.fish
```

### Keeping completions up to date

After adding or removing commands, regenerate the metadata:

```console
<dim>./</dim>tempest completion:generate
```

### Available commands

| Command                | Description                                                              |
| ---------------------- | ------------------------------------------------------------------------ |
| `completion:install`   | Install the completion script and generate metadata.                     |
| `completion:generate`  | Regenerate the completion metadata JSON.                                 |
| `completion:show`      | Output the completion script to stdout (useful for custom installation). |
| `completion:uninstall` | Remove the installed completion script.                                  |

## Middleware

Console middleware can be applied globally or on a per-command basis. Global console middleware will be discovered and applied automatically, by priority order.

### Building your own middleware

You may implement the {`Tempest\Console\ConsoleMiddleware`} interface to build a console middleware.

```php app/InspireMiddleware.php
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;

final readonly class InspireMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private InspirationService $inspiration,
        private Console $console,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        if ($invocation->argumentBag->get('inspire')) {
            $this->console->writeln($this->inspiration->random());
        }

        return $next($invocation);
    }
}
```

Middleware classes will be autowired by the container, so you can use the constructor to inject any dependency you'd like. The {b`Tempest\Console\Initializers\Invocation`} object contains everything you need about the context for the current console command invocation:

- `{php}$invocation->argumentBag` contains the argument bag with all the input provided by the user.
- `{php}$invocation->consoleCommand` an instance of the {b`#[Tempest\Console\ConsoleCommand]`} attribute for the matched console command. This property will be `null` if you're not using {b`Tempest\Console\Middleware\ResolveOrRescueMiddleware`} or if your middleware runs before it.

#### Middleware priority

All console middleware classes get sorted based on their priority. By default, each middleware gets the normal priority, but you can override it using the {b`#[Tempest\Support\Priority]`} attribute:

```php app/InspireMiddleware.php
use Tempest\Support\Priority;

#[Priority(Priority::HIGH)]
final readonly class InspireMiddleware implements ConsoleMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. However, the {b`Tempest\Support\Priority`} class provides a few constants with predefined priorities: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

#### Middleware discovery

Global console middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by using the {b`#[Tempest\Discovery\SkipDiscovery]`} attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class InspireMiddleware implements ConsoleMiddleware
{ /* … */ }
```

### Built-in middleware

Tempest provides a few built-in middleware that you may use on your console commands. Some of these middleware are used internally on some commands, and some of them are used on all commands.

- The {b`Tempest\Console\Middleware\ForceMiddleware`} adds the `--force` flag for skipping `{php}$console->confirm()` calls.
- The {b`Tempest\Console\Middleware\CautionMiddleware`} middleware [prevents usage of commands in production](#preventing-usage-in-production).
- The {b`Tempest\Console\Middleware\OverviewMiddleware`} is responsible from listing all commands when none is provided.
- The {b`Tempest\Console\Middleware\ResolveOrRescueMiddleware`} middleware provides a list of similar commands when an unknown command is invoked.
- The {b`Tempest\Console\Middleware\HelpMiddleware`} middleware provides help when the `--help` flag is used.
- The {b`Tempest\Console\Middleware\ConsoleExceptionMiddleware`} middleware catches and properly render console exceptions.

## Scheduling

Console commands—or any public class method—may be scheduled by using the {b`#[Tempest\Console\Schedule]`} attribute, which accepts an {b`Tempest\Console\Scheduler\Interval`} or {b`Tempest\Console\Scheduler\Every`} value. Methods with this attributes are automatically [discovered](./05-discovery.md), so there is nothing more to add.

You may read more on the [dedicated chapter](../2-features/11-scheduling.md).

## Testing

Tempest provides a console command testing utility accessible through the `console` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. You may learn more about testing in the [dedicated chapter](./07-testing.md).

```php tests/ExportUsersCommandTest.php
$this->console
    ->call(ExportUsersCommand::class)
    ->assertSuccess()
    ->assertSee('12 users exported');

$this->console
    ->call(WipeDatabaseCommand::class)
    ->assertSee('caution')
    ->submit()
    ->assertSuccess();
```


---
title: Container
description: "Learn how Tempest's container works, how to inject and resolve dependencies, and how to implement initialization logic for your service classes when they need it."
---

## Overview

A dependency container is a system that manages the creation and resolution of objects within an application. Instead of manually instantiating dependencies, classes declare what they need, and the container provides them automatically.

Tempest has a dependency container capable of resolving dependencies without any configuration. Most features are built upon this concept, from controllers to console commands, through event handlers and the command bus.

## Injecting dependencies

The constructors of classes resolved by the container may be any class or interface associated with a [dependency initializer](#dependency-initializers). Similarly, invoked methods such as [event handlers](../2-features/08-events.md), [console commands](../3-console/02-building-console-commands) and invokable classes may also be called directly from the container.

```php app/Aircraft/AircraftService.php
use App\Aircraft\ExternalAircraftProvider;
use App\Aircraft\AircraftRepository;
use Tempest\Console\ConsoleCommand;

final readonly class AircraftService
{
    public function __construct(
        private ExternalAircraftProvider $externalAircraftProvider,
        private AircraftRepository $repository,
    ) {}

    #[ConsoleCommand]
    public function synchronize(): void
    {
        // …
    }
}
```

### Invoking a method or function

If you have access to the container instance, you may call its `{php}invoke()` method to call another method, function or invokable class, resolving its dependencies along the way.

Using named arguments, it is also possible to manually specify parameters on the invoked method:

```php
$this->container->invoke(TrackOperatingAircraft::class, type: AircraftType::PC12);
```

The `{php}\Tempest\invoke()` function serves the same purpose when the container is not directly accessible.

### Locating a dependency

There are situations where it may not be possible to inject a dependency on a constructor. To work around this, Tempest provides the `{php}\Tempest\Container\get()` function, which can resolve an object from the container.

```php
use function Tempest\Container\get;

$config = get(AppConfig::class);
```

:::warning
Resolving services this way should only be used as a last resort. If you are interested in knowing why, you may read more about service location in this [blog post](https://stitcher.io/blog/service-locator-anti-pattern).
:::

## Dependency initializers

When you need fine-grained control over how a dependency is constructed instead of relying on Tempest's autowiring capabilities, you can use initializer classes.

Initializers are classes that know how to construct a specific class or interface. Whenever that class or interface is requested from the container, Tempest will use its corresponding initializer to construct it.

### Implementing an initializer

Initializers are classes that implement the {`Tempest\Container\Initializer`} interface. The `initialize()` method receives the container as its only parameter, and returns an instantiated object.

**Most importantly**, Tempest knows which object this initializer is tied to thanks to the return type of the `initialize()` method, which needs to be typed.

```php app/MarkdownInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class MarkdownInitializer implements Initializer
{
    public function initialize(Container $container): MarkdownConverter
    {
        $environment = new Environment();
        $highlighter = new Highlighter(new CssTheme());

        $highlighter
            ->addLanguage(new TempestViewLanguage())
            ->addLanguage(new TempestConsoleWebLanguage())
            ->addLanguage(new ExtendedJsonLanguage());

        $environment
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new FrontMatterExtension())
            ->addRenderer(FencedCode::class, new CodeBlockRenderer($highlighter))
            ->addRenderer(Code::class, new InlineCodeBlockRenderer($highlighter));

        return new MarkdownConverter($environment);
    }
}
```

The above example is an initializer for a `MarkdownConverter` class. It will set up a markdown converter, configure its extensions, and finally return the object. Whenever `MarkdownConverter` is requested via the container, this initializer class will be used to construct it.

### Matching multiple classes or interfaces

The container may match several classes to a single initializer if it has a union return type.

```php app/MarkdownInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class MarkdownInitializer implements Initializer
{
    public function initialize(Container $container): MarkdownConverter|Markdown
    {
        // …
    }
}
```

### Dynamically matching classes or interfaces

While initializers are capable of resolving almost all situations, there are times where the return type of `initialize` is not enough and more flexibility is needed.

Let's take use the concept of route model binding as an example. A controller might accept an instance of a model as its parameters:

```php app/BookController.php
use Tempest\Router\Get;
use Tempest\Http\Response;

final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }
}
```

Since `$book` isn't a scalar value, Tempest will try to resolve `{php}Book` from the container whenever this controller action is invoked. This means we need an initializer that's able to match the `Book` model:

```php app/BookInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class BookInitializer implements Initializer
{
    public function initialize(Container $container): Book
    {
        // …
    }
}
```

While this approach works, it would be very inconvenient to create an initializer for every model class. Furthermore, we want route binding to be provided by the framework, so we need a more generic approach.

The {`Tempest\Container\DynamicInitializer`} interface provides a `canInitialize` method, in which the logic for matching a class may be implemented:

```php app/RouteBindingInitializer.php
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class RouteBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Model::class);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        // …
    }
}
```

## Autowired dependencies

When you need to assign a default implementation to an interface without any specific instantiation steps, creating an initializer class for a single line of code might feel excessive.

```php app/AircraftServiceInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class AircraftServiceInitializer implements Initializer
{
    public function initialize(Container $container): AircraftServiceInterface
    {
        return new AircraftService();
    }
}
```

For simple one-to-one mappings, you can skip the initializer class, instead using the `#[Autowire]` attribute on the default implementation. Tempest will discover this, and link that class to the interface it implements:

```php app/AircraftService.php
use Tempest\Container\Autowire;

#[Autowire]
final readonly class AircraftService implements AircraftServiceInterface
{
    // …
}
```

## Singletons

If you need to register a class as a singleton in the container, you can use the `#[Singleton]` attribute. Any class can have this attribute:

```php app/Services/AircraftService/Client.php
use Tempest\Container\Singleton;
use Tempest\HttpClient\HttpClient;

#[Singleton]
final readonly class Client
{
    public function __construct(
        private HttpClient $http,
    ) {}

    public function fetch(Icao $icao): Aircraft
    {
        // …
    }
}
```

Furthermore, an initializer method can be annotated as a `#[Singleton]`, meaning its return object will only ever be resolved once:

```php app/MarkdownInitializer.php
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class MarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): MarkdownConverter|Markdown
    {
        // …
    }
}
```

### Tagged singletons

In some cases, you want more control over singleton definitions.

Let's say you want an instance of `{php}\Tempest\Highlight\Highlighter` that would be configured for web highlighting, and one that would be configured CLI highlighting. In this situation, you can differentiate them using the `tag` parameter of the `#[Singleton]` attribute:

```php app/WebHighlighterInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class WebHighlighterInitializer implements Initializer
{
    #[Singleton(tag: 'web')]
    public function initialize(Container $container): Highlighter
    {
        return new Highlighter(new CssTheme());
    }
}
```

Retrieving this specific instance from the container may be done by using the `{php}#[Tag]` attribute during autowiring:

```php app/HttpExceptionHandler.php
use Tempest\Container\Tag;

class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        #[Tag('web')]
        private Highlighter $highlighter,
    ) {}
}
```

If you have a container instance, you may also get it directly using the `tag` argument:

```php
$container->get(Highlighter::class, tag: 'cli');
```

:::info
[This blog post](https://stitcher.io/blog/tagged-singletons), by {gh:brendt}, provides in-depth explanations about tagged singletons.
:::

### Dynamic tags

Some components implement the {`Tempest\Container\HasTag`} interface, which requires a `tag` property. Singletons using this interface are tagged by the `tag` property, essentially providing the ability to have dynamic tags.

This is specifically useful to get multiple instances of the same configuration. This is how [multiple database connections support](../1-essentials/03-database.md#using-multiple-connections) is implemented.

## Built-in types dependencies

Besides being able to depend on objects, sometimes you'd want to depend on built-in types like `string`, `int` or more often `array`. It is possible to depend on these built-in types, but these cannot be autowired and must be initialized through a [tagged singleton](#tagged-singletons).

For example if we want to group a specific set of validators together as a tagged collection, you can initialize them in a tagged singleton initializer like so:

```php
// app/BookValidatorsInitializer.php

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class BookValidatorsInitializer implements Initializer
{
    #[Singleton(tag: 'book-validators')]
    public function initialize(Container $container): array
    {
        return [
            $container->get(HeaderValidator::class),
            $container->get(BodyValidator::class),
            $container->get(FooterValidator::class),
        ];
    }
}
```

Now you can use this group of validators as a normal tagged value in your container:

```php
// app/BookController.php

use Tempest\Container\Tag;

final readonly class BookController
{
    public function __construct(
        #[Tag('book-validators')] private readonly array $contentValidators,
    ) { /* … */ }
}
```

## Injected properties

While constructor injection is almost always the preferred way to go, Tempest also offers the ability to inject values straight into properties, without them being requested by the constructor.

You may mark any property—public, protected, or private—with the `#[Inject]` attribute. Whenever a class instance is resolved via the container, its properties marked for injection will be provided the right value. Tagged singletons may also be injected using the optional `tag` parameter of the `#[Inject]` attribute.

```php Tempest/Console/src/HasConsole.php
use Tempest\Container\Inject;

trait HasConsole
{
    #[Inject]
    private Console $console;

    // …
}
```

Keep in mind that injected properties are a form of service location. While it's recommended to rely on constructor injection by default, injected properties may offer flexibility when using traits without having to claim the constructor within that trait.

For example, without injected properties, the above example would have to define a constructor within the trait to inject the `Console` dependency:

```php
trait HasConsole
{
    public function __construct(
        private readonly Console $console,
    ) {}

    // …
}
```

On its own, that isn't a problem, but it causes some usability issues when using this trait in classes that require other dependencies as well:

```php
use Tempest\Console\HasConsole;

class MyCommand
{
    use HasConsole;

    public function __construct(
        private BlogPostRepository $repository,

        // The `HasConsole` trait breaks if you didn't remember to explicitly inject it here
        private Console $console,
    ) {}

    // …
}
```

For these edge cases, it's nicer to make the trait self-contained without having to rely on constructor injection. That's why injected properties are supported.

## Decorators

The container supports the [decorator pattern](https://refactoring.guru/design-patterns/decorator), which allows you to wrap objects and add new behavior to them at runtime without changing their structure. This is particularly useful for adding cross-cutting concerns like logging, caching, validation, or authentication.

To create a decorator, you need to:

1. Use the {b`#[Tempest\Container\Decorates]`} attribute on your decorator class
2. Implement the same interface as the class you're decorating
3. Accept the decorated object as a constructor parameter

```php app/Cache/CacheRepository.php
use Tempest\Container\Decorates;

#[Decorates(Repository::class)]
final readonly class CacheRepository implements Repository
{
    public function __construct(
        private Repository $repository,
        private Cache $cache,
    ) {}

    public function findById(int $id): ?Book
    {
        return $this->cache->resolve(
            key: "book.{$id}",
            callback: fn () => $this->repository->find($id)
        );
    }

    public function save(Book $book): Book
    {
        $this->cache->delete("book.{$book->id}");

        return $this->repository->save($book);
    }
}
```

When you request the `Repository` from the container, Tempest will automatically wrap the original implementation with your decorator. The decorated object (the original `Repository`) is injected into the decorator's constructor.

:::info
Decorators are discovered automatically through Tempest's [discovery](./05-discovery.md), so you don't need to manually register them.
:::

## Proxy loading

The container supports lazy loading of dependencies using the `#[Proxy]` attribute. Using this attribute on a property (that has `#[Inject]`) or a constructor parameter
will allow the container to instead inject a lazy proxy.
Since lazy proxies are transparent to the consumer you do not need to change anything else in your code.
The primary use case for this are heavy dependencies that may or may not be used.

```php app/BookController.php
use Tempest\Container\Proxy;

final readonly class BookController
{
    public function __construct(
        #[Proxy]
        private VerySlowClass $verySlowClass
    ) { /* … */ }
}
```


---
title: Discovery
description: "Tempest automatically locates controller actions, event handlers, console commands, and other components of your application, without needing any configuration from you."
---

## Overview

Tempest introduces a unique approach to bootstrapping applications. Instead of requiring manual registration of project code and packages, Tempest automatically scans the codebase and detects the components that should be loaded. This process is called **discovery**.

Discovery is powered by composer metadata. Every package that depends on Tempest, along with your application's own code, are included in the discovery process.

Tempest applies [various rules](#built-in-discovery-classes) to determine the purpose of different pieces of code—it can analyze file names, attributes, interfaces, return types, and more. For instance, web routes are discovered when methods are annotated with route attributes:

```php app/HomeController.php
final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('home.view.php');
    }
}
```

:::tip
Read the [getting started with discovery](/blog/discovery-explained) guide if you want to know more about the philosophy of discovery and how it works.
:::

## Discovery in production

Discovery comes with performance considerations. In production, it is always cached to avoid scanning files on every request.

To ensure that the discovery cache is up-to-date, add the `discovery:generate` command before any other Tempest command in your deployment pipeline.

```console ">_ ./tempest discovery:generate --no-interaction"
Clearing discovery cache <dim>.....................................</dim> <strong>2025-12-30 15:51:46</strong>
Clearing discovery cache <dim>.....................................</dim> <strong>DONE</strong>
Generating discovery cache using the `full` strategy <dim>.........</dim> <strong>2025-12-30 15:51:46</strong>
Generating discovery cache using the `full` strategy <dim>.........</dim> <strong>DONE</strong>
```

## Discovery for local development

During development, discovery is only enabled for application code. This implies that the cache should be regenerated whenever a package is installed or updated.

It is recommended to add the `discovery:generate` command to the `post-package-update` script in `composer.json`:

```json composer.json
{
	"scripts": {
		"post-package-update": [
			"@php tempest discovery:generate"
		]
	}
}
```

### Disabling discovery cache

In some situations, you may want to enable discovery even for vendor code. For instance, if you are working on a third-party package that is being developed alongside your application, you may want to have discovery enabled all the time.

To achieve this, set the `DISCOVERY_CACHE` environment variable to `false`:

```env .env
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:false:}
```

### Troubleshooting

The `discovery:clear` command clears the discovery cache, which will be rebuilt the next time the framework boots. `discovery:generate` can be used to manually regenerate the cache.

If the discovery cache gets corrupted and even `discovery:clear` is not enough, the `.tempest/cache/discovery` may be manually deleted from your project.

## Implementing your own discovery

While Tempest provides a variety of [built-in discovery classes](#built-in-discovery-classes), you may want to implement your own to extend the framework's capabilities in your application or in a package you are building.

### Discovering code in classes

Tempest discovers classes that implement {b`Tempest\Discovery\Discovery`}, which requires implementing the `discover()` and `apply()` methods. The {b`Tempest\Discovery\IsDiscovery`} trait provides the rest of the implementation.

The `discover()` method accepts a {b`Tempest\Discovery\DiscoveryLocation`} and a {b`Tempest\Reflection\ClassReflector`} parameter. The reflector can be used to loop through a class' attributes, methods, parameters or anything else. If the class matches your expectations, you may register it using `$this->discoveryItems->add()`.

As an example, the following is a simplified version of the event bus discovery:

```php EventBusDiscovery.php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\IsDiscovery;

final class EventBusDiscovery implements Discovery
{
    // This provides the default implementation for `Discovery`'s internals
    use IsDiscovery;

    public function __construct(
        // Discovery classes are autowired,
        // so you can inject all dependencies you need
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $eventHandler = $method->getAttribute(EventHandler::class);

            // Extra checks to determine whether
            // we can actually use the current method as an event handler

            // …

            // Finally, we add all discovery-related data into `$this->discoveryItems`:
            $this->discoveryItems->add($location, [$eventName, $eventHandler, $method]);
        }
    }

    // Next, the `apply` method is called whenever discovery is ready to be
    // applied into the framework. In this case, we want to loop over all
    // registered discovery items, and add them to the event bus config.
    public function apply(): void
    {
        foreach ($this->discoveryItems as [$eventName, $eventHandler, $method]) {
            $this->eventBusConfig->addClassMethodHandler(
                event: $eventName,
                handler: $eventHandler,
                reflectionMethod: $method,
            );
        }
    }
}
```

### Discovering files

It is possible to discover files instead of classes. For instance, view files, front-end entrypoints or SQL migrations are not PHP classes, but still need to be discovered.

In this case, you may implement the additional {b`\Tempest\Discovery\DiscoversPath`} interface. It requires a `discoverPath()` method that accepts a {b`Tempest\Discovery\DiscoveryLocation`} and a string path.

The example below shows a simplified version of the Vite entrypoint discovery:

```php ViteDiscovery.php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\IsDiscovery;
use Tempest\Support\Str;

final class ViteDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly ViteConfig $viteConfig,
    ) {}

    // We are not discovering any class, so we return immediately.
    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        return;
    }

    // This method is called for every file in registered discovery locations.
    // We can use the `$path` to determine whether we are interested in it.
    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        // We are interested in `.ts`, `.css` and `.js` files only.
        if (! Str\ends_with($path, ['.ts', '.css', '.js'])) {
            return;
        }

        // These files need to be specifically marked as `.entrypoint`.
        if (! str($path)->beforeLast('.')->endsWith('.entrypoint')) {
            return;
        }

        $this->discoveryItems->add($location, [$path]);
    }

    // When discovery is cached, `discover` and `discoverPath` are not called.
    // Instead, `discoveryItems` is already fed with serialized data, which
    // we can use. In this case, we add the paths to the Vite config.
    public function apply(): void
    {
        foreach ($this->discoveryItems as [$path]) {
            $this->viteConfig->addEntrypoint($path);
        }
    }
}
```

## Excluding files and classes from discovery

Files and classes may be excluded from discovery by providing a {b`Tempest\Core\DiscoveryConfig`} [configuration](./06-configuration.md) file.

```php src/discovery.config.php
use Tempest\Core\DiscoveryConfig;

return new DiscoveryConfig()
    ->skipClasses(GlobalHiddenDiscovery::class)
    ->skipPaths(__DIR__ . '/../../Fixtures/GlobalHiddenPathDiscovery.php');
```

## Built-in discovery classes

Most of Tempest's features are built on top of discovery. The following is a non-exhaustive list that describes which discovery class is associated to which feature.

- {b`Tempest\Core\DiscoveryDiscovery`} discovers other discovery classes. This class is run manually by the framework when booted.
- {b`Tempest\CommandBus\CommandBusDiscovery`} discovers methods with the {b`#[Tempest\CommandBus\CommandHandler]`} attribute and registers them into the [command bus](../2-features/10-command-bus.md).
- {b`Tempest\Console\Discovery\ConsoleCommandDiscovery`} discovers methods with the {b`#[Tempest\Console\ConsoleCommand]`} attribute and registers them as [console commands](../1-essentials/04-console-commands.md).
- {b`Tempest\Console\Discovery\ScheduleDiscovery`} discovers methods with the {b`#[Tempest\Console\Schedule]`} attribute and registers them as [scheduled tasks](../2-features/11-scheduling.md).
- {b`Tempest\Container\InitializerDiscovery`} discovers classes that implement {b`\Tempest\Container\Initializer`} or {b`\Tempest\Container\DynamicInitializer`} and registers them as [dependency initializers](./05-container.md#dependency-initializers).
- {b`Tempest\Database\MigrationDiscovery`} discovers classes that implement {b`Tempest\Database\MigratesUp`} or {b`Tempest\Database\MigratesDown`} and registers them as [migrations](./03-database.md#migrations).
- {b`Tempest\EventBus\EventBusDiscovery`} discovers methods with the {b`#[Tempest\EventBus\EventHandler]`} attribute and registers them in the [event bus](../2-features/08-events.md).
- {b`Tempest\Router\RouteDiscovery`} discovers route attributes on methods and registers them as [controller actions](./01-routing.md) in the router.
- {b`Tempest\Mapper\MapperDiscovery`} discovers classes that implement {b`Tempest\Mapper\Mapper`} and registers them for [mapping](../2-features/01-mapper.md#mapper-discovery).
- {b`Tempest\Mapper\CasterDiscovery`} discovers classes that implement {b`Tempest\Mapper\DynamicCaster`} and registers them as [casters](../2-features/01-mapper.md#casters-and-serializers).
- {b`Tempest\Mapper\SerializerDiscovery`} discovers classes that implement {b`Tempest\Mapper\DynamicSerializer`} and registers them as [serializers](../2-features/01-mapper.md#casters-and-serializers).
- {b`Tempest\View\ViewComponentDiscovery`} discovers `x-*.view.php` files and registers them as [view components](../1-essentials/02-views.md#view-components).
- {b`Tempest\Vite\ViteDiscovery`} discovers `*.entrypoint.{ts,js,css}` files and register them as [entrypoints](../2-features/02-asset-bundling.md#entrypoints).
- {b`Tempest\Auth\AccessControl\PolicyDiscovery`} discovers methods annotated with the {b`#[Tempest\Auth\AccessControl\Policy]`} attribute and registers them as [access control policies](../2-features/04-authentication.md#access-control).
- {b`Tempest\Core\InsightsProviderDiscovery`} discovers classes that implement {b`Tempest\Core\InsightsProvider`} and registers them as insights providers, which power the `tempest about` command.

## Discovery as a standalone package

Discovery can be used as a standalone package in any application that uses a [PSR-11](https://www.php-fig.org/psr/psr-11/) compliant container, which includes Laravel and Symfony applications.

First, you may require `tempest/discovery`:

```console
composer require tempest/discovery
```

Next, you may boot discovery by calling {b`Tempest\Discovery\BootDiscovery`}:

```php
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryConfig;

new BootDiscovery(
    container: $container,
    config: DiscoveryConfig::autoload($rootPath),
)();
```

The `$container` in this example is a PSR-11 implementation that must already be available in your application. For instance, in a Laravel application, you can access it in a service provider:

```php
final class DiscoveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        new BootDiscovery(
            container: $this->app,
            config: DiscoveryConfig::autoload(base_path()),
        )();
    }
}
```

### Specifying discovery locations

`DiscoveryConfig::autoload()` scans the given root path, finds a `composer.json` in it, and registers all PSR-4 locations defined in it for discovery, in addition to vendor locations.

If you prefer to have more control over which locations are registered for discovery, you can create a {b`Tempest\Discovery\DiscoveryConfig`} instance manually and pass in the desired locations:

```php
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;

$config = new DiscoveryConfig(locations: [
    new DiscoveryLocation('App\\', 'src/'),
    // …
]);
```

### Skipping classes and paths

The {b`Tempest\Discovery\DiscoveryConfig`} instance also allows you to skip specific classes and paths from discovery. This is useful for excluding code that you don't want to be discovered, or that is causing issues during discovery, such as Pest test files.

```php
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryConfig;

new BootDiscovery(
    container: $container,
    config: DiscoveryConfig::autoload(__DIR__)
        ->skipClasses(
            \App\Foo::class,
            \Tempest\Container\AutowireDiscovery::class
        )
        ->skipPaths(
            __DIR__ . '/../vendor/tempest/support'
        )
        ->skipUsing(static function (string $input) {
            if (str_ends_with($input, needle: 'Test.php')) {
                return true;
            }

            if (str_ends_with($input, needle: 'Pest.php')) {
                return true;
            }

            return false;
        }),
)();
```

You can also mark classes themselves to be skipped entirely by discovery:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class CautionMiddleware implements ConsoleMiddleware
{
    // …
}
```

Furthermore, you can skip discovery entirely for a specific class, expect for a specific set of discovery classes:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery(except: [MigrationDiscovery::class])]
final class HiddenMigratableDatabaseMigration implements MigratesUp
{
    // …
}
```

Finally, you can pass in a callable to this `$except` parameter as well, which allows access to the container, and gives you even more flexibility on when a class should be skipped or not:

```php
use Tempest\Discovery\SkipDiscovery;
use Tempest\Container\Container;

#[SkipDiscovery(static function (Container $container): bool {
    return ! $container->get(Application::class) instanceof ConsoleApplication;
})]
final class BlogPostEventHandlers {
    // …
}
```

### Caching discovery

By default, discovery is not cached, meaning all configured discovery locations are scanned on every request. This is fine for development, but in production, it's recommended to cache discovery to remove any performance overhead.

You may call the {b`Tempest\Discovery\GenerateDiscoveryCache`} action to generate the discovery cache. This action accepts a {b`Tempest\Discovery\DiscoveryCache`} instance, which allows you to specify the caching strategy, which usually depend on the environment:

```php
use Tempest\Discovery\GenerateDiscoveryCache;
use Tempest\Discovery\DiscoveryConfig;

(new GenerateDiscoveryCache())(
    container: $this->container,
    config: $config,
    cache: new DiscoveryCache(
        strategy: $this->isProduction
            ? DiscoveryCacheStrategy::FULL
            : DiscoveryCacheStrategy::NONE,
        pool: new PhpFilesAdapter(
            directory: base_path('.discovery'),
        ),
    ),
);
```

:::warning
The discovery cache only works if the strategy used during the cache generation is the same as the strategy defined in subsequent requests.
:::

It's advised to always run cache generation code from within a script that doesn't have discovery cache enabled. For example:

:::code-group

```sh "bin/console"
{:hl-property:DISCOVERY_CACHE:}=false {:hl-keyword:php:} bin/console discovery:generate
```

```sh "artisan"
{:hl-property:DISCOVERY_CACHE:}=false {:hl-keyword:php:} artisan discovery:generate
```

:::

### Clearing the discovery cache

You may call the {b`Tempest\Discovery\ClearDiscoveryCache`} action to clear the discovery cache. The {b`Tempest\Discovery\DiscoveryCache`} instance must have the same pool and strategy as the one used during cache generation:

```php
use Tempest\Discovery\ClearDiscoveryCache;

(new ClearDiscoveryCache())(new DiscoveryCache(
    strategy: $this->isProduction
        ? DiscoveryCacheStrategy::FULL
        : DiscoveryCacheStrategy::NONE,
    pool: new PhpFilesAdapter(
        directory: base_path('.discovery'),
    ),
));
```


---
title: Configuration
description: "Tempest takes a unique approach at configuration, providing an excellent developer experience due to its inherent support from code editors."
---

## Overview

Within Tempest, configuration is represented by objects. This allows code editors to provide static insights and autocompletion during edition, resulting in an unmatched developer experience.

Even though the framework is designed to use as little configuration as possible, many configuration classes are available. When fine-grained control over a specific part of the framework is needed, the default configuration can be overwritten.

## Configuration files

Files ending with `*.config.php` are recognized by Tempest's [discovery](../1-essentials/05-discovery) as configuration objects, and will be registered as [singletons](./01-container#singletons) in the container.

```php app/postgres.config.php
use Tempest\Database\Config\PostgresConfig;
use function Tempest\env;

return new PostgresConfig(
    host: env('DB_HOST'),
    port: env('DB_PORT'),
    username: env('DB_USERNAME'),
    password: env('DB_PASSWORD'),
    database: env('DB_DATABASE'),
);
```

The configuration object above instructs Tempest to use PostgreSQL as its database, replacing the framework's default database, SQLite.

### Accessing configuration objects

To access a configuration object, you may inject it from the container like any other dependency.

```php
use Tempest\Core\AppConfig;

final readonly class AboutController
{
    public function __construct(
        private AppConfig $config,
    ) {}

    #[Get('/')]
    public function __invoke(): View
    {
        return view('about.view.php', name: $this->config->name);
    }
}
```

### Updating configuration objects

To update a property in a configuration object, you may simply assign a new value. Due to the object being a singleton, the modification will be persisted through the rest of the application's lifecycle.

```php
use Tempest\Support\Random;
use Tempest\Vite\ViteConfig;

$this->viteConfig->nonce = Random\secure_string(length: 40);
```

Alternatively, you may completely override the configuration instance by calling the `config()` method of the container, registering the new object as a singleton.

```php
$this->container->config(new SQLiteConfig(
    path: root_path('database.sqlite'),
));
```

## Creating your own configuration

As your application grows, you may need to create your own configuration objects. Such a use case could be an integration with Slack, where an API token and an application ID would be required.

You may first create a class representing the configuration needed for your feature. It can have default values for its properties, and even methods if needed.

```php app/Slack/SlackConfig.php
final class SlackConfig
{
    public function __construct(
        public string $token,
        public string $baseUrl,
        public string $applicationId,
        public ?string $userAgent = null,
    ) {}
}
```

The next step is to register this configuration object in the container. This can be done by creating a `slack.config.php` file, which will be discovered by Tempest and registered as a [singleton](./01-container#singletons).

```php app/Slack/slack.config.php
use function Tempest\env;

return new SlackConfig(
    token: env('SLACK_API_TOKEN'),
    baseUrl: env('SLACK_BASE_URL', default: 'https://slack.com/api'),
    applicationId: env('SLACK_APP_ID'),
    userAgent: env('USER_AGENT'),
);
```

You may now inject the `SlackConfig` class into a service, a controller, an action, or anything that can be resolved by the container.

```php app/Slack/SlackConnector.php
final class SlackConnector extends HttpConnector
{
    public function __construct(
        private readonly SlackConfig $slackConfig,
    ) {
    }

    public function resolveBaseUrl(): string
    {
        return $this->slackConfig->baseUrl;
    }

    // ...
}
```

## Per-environment configuration

Whenever possible, you should have a single configuration file per feature. You may use the {b`Tempest\env()`} function inside that file to reference credentials and environment-specific values.

However, it's sometimes needed to have completely different configurations in development and in production. For instance, you may use S3 for your [storage](../2-features/05-file-storage.md) in production, but use the local filesystem during development.

When this happens, you may create environment-specific configuration files by using the `.<env>.config.php` suffix. For instance, a production-only configuration file could be `storage.prod.config.php`:

```php app/storage.prod.config.php
return new S3StorageConfig(
    bucket: env('S3_BUCKET'),
    region: env('S3_REGION'),
    accessKeyId: env('S3_ACCESS_KEY_ID'),
    secretAccessKey: env('S3_SECRET_ACCESS_KEY'),
);
```

The following suffixes are supported:

- `.prd.config.php`, `.prod.config.php`, and `.production.config.php` for the production environment.
- `.stg.config.php` and `.staging.config.php` for the staging environment.
- `.dev.config.php` and `.local.config.php` for the development environment.
- `.test.config.php` and `.testing.config.php` for the testing environment.

## Disabling the configuration cache

During development, Tempest will discover configuration files every time the framework is booted. In a production environment, configuration files are automatically cached.

You may override this behavior by setting the `{txt}{:hl-property:CONFIG_CACHE:}` environment variable to `true`.

```env .env
{:hl-property:CONFIG_CACHE:}={:hl-keyword:true:}
```


---
title: Testing
description: "Tempest is built with testing in mind. It ships with convenient utilities that make it easy to test application code without boilerplate."
keywords: ["phpunit", "pest"]
---

## Overview

Tempest uses [PHPUnit](https://phpunit.de) for testing and provides an integration through the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. This class boots the framework with configuration suitable for testing, and provides access to multiple utilities.

Testing utilities specific to components are documented in their respective chapters. For instance, testing the router is described in the [routing documentation](./01-routing.md#testing).

## Running tests

Any test class that needs to interact with Tempest must extend [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php).

By default, Tempest ships with a `phpunit.xml` file that configures PHPUnit to find test files in the `tests` directory. You may run tests using the following command:

```sh
./vendor/bin/phpunit
```

## Using the database

By default, tests don't interact with the database. You may manually set up the database for testing in test files by using the `setup()` method on the `database` testing utility.

```php tests/ShowAircraftControllerTest.php
final class ShowAircraftControllerTest extends IntegrationTest
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->database->setup();
    }
}
```

:::info
The [`PreCondition`](https://docs.phpunit.de/en/12.5/attributes.html#precondition) attribute instructs PHPUnit to run the associated method after the `setUp()` method. We recommend using it instead of overriding `setUp()` directly.
:::

### Running migrations

By default, all migrations are run when setting up the database. However, you may choose to run only specific migrations by using the `migrate()` method instead of `setup()`.

```php tests/ShowAircraftControllerTest.php
final class ShowAircraftControllerTest extends IntegrationTest
{
    #[Test]
    public function shows_aircraft(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateAircraftTable::class,
        );
        
        // …
    }
}
```

### Using a dedicated testing database

To ensure your tests run in isolation and do not affect your main database, you may configure a dedicated test database connection.

To do so, create a `database.testing.config.php` file anywhere—Tempest will [use it](./06-configuration.md#per-environment-configuration) to override the default database settings.

```php tests/database.testing.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/testing.sqlite'
);
```

## Spoofing the environment

By default, Tempest provides a `phpunit.xml` that sets the `ENVIRONMENT` variable to `testing`. This is needed so that Tempest can adapt its boot process and load the proper configuration files for the testing environment.

During tests, you may want to test different paths of your application depending on the environment. For instance, you may want to test that certain features are only available in production. To do this, you may override the {b`Tempest\Core\Environment`} singleton:

```php
use Tempest\Core\Environment;

$this->container->singleton(Environment::class, Environment::PRODUCTION);
```

## Changing the location of tests

The `phpunit.xml` file contains a `{html}<testsuite>` element that configures the directory in which PHPUnit looks for test files. This may be changed to follow any rule of your convenience.

For instance, you may colocate test files and their corresponding class by changing the `{html}suffix` attribute in `phpunit.xml` to the following:

```diff phpunit.xml
<testsuites>
	<testsuite name="Tests">
-		<directory suffix="Test.php">./tests</directory>
+		<directory suffix="Test.php">./app</directory>
	</testsuite>
</testsuites>
```

## Discovering test-specific fixtures

Non-test files created in the `tests` directory are automatically discovered by Tempest when running the test suite.

You can override this behavior by providing your own implementation of `discoverTestLocations()`:

```php tests/Aircraft/ShowAircraftControllerTest.php
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;

final class ShowAircraftControllerTest extends IntegrationTest
{
    protected function discoverTestLocations(): array
    {
        return [
            new DiscoveryLocation('Tests\\Aircraft', __DIR__ . '/Aircraft'),
        ];
    }
}
```

## Using Pest as a test runner

[Pest](https://pestphp.com/) is a test runner built on top of PHPUnit. It provides a functional way of writing tests similar to JavaScript testing frameworks like [Vitest](https://vitest.dev/), and features an elegant console reporter.

Pest is framework-agnostic, so you may use it in place of PHPUnit if that is your preference. The [installation process](https://pestphp.com/docs/installation) consists of removing the dependency on `phpunit/phpunit` in favor of `pestphp/pest`.

```sh
{:hl-type:composer:} remove {:hl-keyword:phpunit/phpunit:}
{:hl-type:composer:} require {:hl-keyword:pestphp/pest:} --dev --with-all-dependencies
```

The next step is to create a `tests/Pest.php` file, which will instruct Pest how to run tests. You may read more about this file in the [dedicated documentation](https://pestphp.com/docs/configuring-tests).

```php tests/Pest.php
pest()
    ->extend(Tests\IntegrationTest::class)
    ->in(__DIR__);
```

You may now run `./vendor/bin/pest` to run your test suite. You might also want to replace the `phpunit` script in `composer.json` by one that uses Pest.


---
title: Primitive utilities
description: "Working with strings and arrays in PHP is notoriously hard due to the lack of a standard library. Tempest comes with a bunch of utilities to improve the experience in this area."
---

## Overview

Tempest provides a set of utilities that make working with primitive values easier. It provides an object-oriented API for handling strings and arrays, along with many namespaced functions to work with arithmetic operations, regular expressions, random values, pluralization, filesystem paths and more.

## Namespaced functions

Most utilities provided by Tempest have a function-based implementation under the [`Tempest\Support`](https://github.com/tempestphp/tempest-framework/tree/main/packages/support/src) namespace. You may look at what is available on GitHub:

- [Regular expressions](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Regex/functions.php)
- [Arithmetic operations](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Math/functions.php)
- [Filesystem operations](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Filesystem/functions.php)
- [Filesystem paths](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Path/functions.php)
- [Json manipulation](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Json/functions.php)
- [Random values](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Random/functions.php)
- [Pluralization](https://github.com/tempestphp/tempest-intl)
- [PHP namespaces](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Namespace/functions.php)

Tempest also provides the {`Tempest\Support\IsEnumHelper`} trait to work with enumerations, since a functional API is not useful in this case.

## String utilities

Tempest provides string utilities through [namespaced functions](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Str/functions.php) or a fluent, object-oriented API, which comes in an immutable and a mutable flavor.

Providing a string value, you may create an instance of {`\Tempest\Support\Str\ImmutableString`} or {`\Tempest\Support\Str\MutableString`}:

```php
use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;

// Functional API
$title = Str\to_sentence_case($title);

// Object-oriented API
$slug = new ImmutableString('/blog/01-chasing-bugs-down-the-rabbit-hole/')
    ->stripEnd('/')
    ->afterLast('/')
    ->replaceRegex('/\d+-/', '')
    ->slug()
    ->toString();
```

Note that you may use the `str()` function as a shorthand to create an {b`\Tempest\Support\Str\ImmutableString`} instance.

## Array utilities

Tempest provides array utilities through [namespaced functions](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Arr/functions.php) or a fluent, object-oriented API, which comes in an immutable and a mutable flavor.

Providing an iterable value, you may create an instance of {`\Tempest\Support\Arr\ImmutableArray`} or {`\Tempest\Support\Arr\MutableArray`}:

```php
use Tempest\Support\Arr;
use Tempest\Support\Arr\ImmutableArray;

// Functional API
$first = Arr\first($collection);

// Object-oriented API
$items = new ImmutableArray(glob(__DIR__ . '/content/*.md'))
    ->reverse()
    ->map(function (string $path) {
        // …
    })
    ->mapTo(BlogPost::class);
```

Note that you may use the `arr()` function as a shorthand to create an {b`\Tempest\Support\Arr\ImmutableArray`} instance.

## Recommendations

We recommend working with primitive utilities when possible instead of using PHP's built-in methods. For instance, you may read a file by using `Filesystem\read_file`:

```php
use Tempest\Support\Filesystem;

$contents = Filesystem\read_file(__DIR__ . '/content.md');
```

Using this function covers more edge cases and throws clear exceptions that are easier to catch. Similarly, it may not be useful to always reach for the object-oriented array and string helpers. Sometimes, you may simply use a single function:

```php
use Tempest\Support\Str;
use function Tempest\Support\str;

{- $title = str('My title')->title()->toString(); -}
{+ $title = Str\to_title_case('My title'); +}
```


---
title: Mapper
description: "The mapper component is capable of mapping data to objects and the other way around. It is one of Tempest's most powerful tools."
---

## Overview

Tempest provides a mapper component for mapping data to objects and back. The component maps request data to request classes, SQL query results to model classes, and other data transformations.

This component is used internally for persistence between models and the database, it maps PSR objects to internal requests, request data to objects, and more.

## Mapping data

To map data from a source to a target, use the {b`\Tempest\Mapper\map()`} function. This function accepts the source data as its sole parameter and returns a mapper instance.

Calling the `to()` method on this instance returns a new instance of the target class, populated with the mapped data:

```php
use function Tempest\Mapper\map;

$book = map($rawBookAsJson)->to(Book::class);
```

### Mapping to collections

When the source data is an array, calling the `collection()` method instructs the mapper to map each item to an instance of the target class.

```php
use function Tempest\Mapper\map;

$books = map($rawBooksAsJson)
    ->collection()
    ->to(Book::class);
```

### Choosing specific mappers

By default, Tempest determines which mapper to use based on the source and target types. To specify which mapper to use explicitly, call the `with()` method on the mapper instance. This method accepts one or multiple mapper class names to use for the mapping.

```php
$psrRequest = map($request)
    ->with(RequestToPsrRequestMapper::class)
    ->do();
```

Alternatively, provide closures to the `with()` method. These closures expect the mapper as their first parameter and the source data as the second. Using closures provides access to the `$from` parameter for more advanced mapping operations:

```php
$result = map($rawBooksAsJson)
    ->with(fn (ArrayToBooksMapper $mapper, array $books) => $mapper->map($books, Book::class))
    ->do();
```

Of course, `with()` can also be combined with `collection()` and `to()`.

```php
use function Tempest\Mapper\map;

$books = map($rawBooksAsJson)
    ->collection()
    ->with(ArrayToBooksMapper::class)
    ->to(Book::class);
```

### Serializing to arrays or JSON

To serialize the mapped data to an array or JSON string, call `toArray()` or `toJson()` on the mapper instance, respectively.

```php
$array = map($book)->toArray();
$json = map($book)->toJson();
```

### Hiding properties from serialization

Properties marked with the {b`#[Tempest\Mapper\Hidden]`} attribute are excluded from serialization. This is useful for sensitive data like passwords or API keys that should never be exposed in arrays or JSON responses.

```php
use Tempest\Mapper\Hidden;

final class User
{
    public string $email;

    #[Hidden]
    public string $password;
}
```

When serializing, hidden properties are automatically excluded:

```php
$user = new User();
$user->email = 'user@example.com';
$user->password = 'secret';

$array = map($user)->toArray();
// ['email' => 'user@example.com']

$json = map($user)->toJson();
// {"email":"user@example.com"}
```

:::info
The {b`#[Hidden]`} attribute also excludes properties from database SELECT queries. See the [database documentation](../1-essentials/03-database.md#hidden-properties) for more information.
:::

### Overriding field names

When mapping from an array to an object, Tempest uses the property names of the target class to map the data. If a property name doesn't match a key in the source array, use the {b`#[Tempest\Mapper\MapFrom]`} attribute to specify the source key to map to the property.

```php
use Tempest\Mapper\MapFrom;

final class Book
{
    #[MapFrom('book_title')]
    public string $title;
}
```

In the following example, the `book_title` key from the source array will be mapped to the `title` property of the `Book` class.

```php
$book = map(['book_title' => 'Timeline Taxi'])->to(Book::class);
```

Similarly, use the {b`#[Tempest\Mapper\MapTo]`} attribute to specify the key used when serializing the object to an array or a JSON string.

```php
use Tempest\Mapper\MapTo;

final class Book
{
    #[MapTo('book_title')]
    public string $title;
}
```

### Strict mapping

By default, the mapper allows building objects with missing data. For instance, if a class has two properties and data is provided for only one, the mapper still creates an instance of the class.

This behavior supports building objects incrementally. Protected and private properties are ignored and not populated.

```php
final class Book
{
    public string $title;
    public string $contents;
}

// Allowed
$book = map(['title' => 'Timeline Taxi'])->to(Book::class);
```

Accessing missing properties after the object has been constructed results in an uninitialized property error. To have the mapper throw an exception when properties are missing, mark the class or a specific property with the {b`#[Tempest\Mapper\Strict]`} attribute.

```php
use Tempest\Mapper\Strict;

use function Tempest\Mapper\map;

#[Strict]
final class Book
{
    public string $title;
    public string $contents;
}

// MappingValuesWereMissing is thrown
$book = map(['title' => 'Timeline Taxi'])->to(Book::class);
```

## Custom mappers

To create custom mappers, implement the {`\Tempest\Mapper\Mapper`} interface. This interface requires a `canMap()` and a `map()` method.

```php
final readonly class PsrRequestToRequestMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! $from instanceof PsrRequest) {
            return false;
        }

        return is_a($to, Request::class, allow_string: true);
    }

    public function map(mixed $from, mixed $to): object
    { /* … */ }
}
```

### Mapper discovery

Tempest automatically discovers and registers all classes that implement the {b`\Tempest\Mapper\Mapper`} interface.

Mapper discovery relies on the result of the `canMap()` method. When a mapper is dedicated to mapping a source to a specific class, the `$to` parameter is not necessarily used.

## Casters and serializers

Casters map serialized data to a complex type. Serializers convert complex types to a serialized representation.

To create custom casters and serializers, implement the {`\Tempest\Mapper\Caster`} and {`\Tempest\Mapper\Serializer`} interfaces, respectively.

:::code-group

```php app/AddressCaster.php
use Tempest\Mapper\Caster;

final readonly class AddressCaster implements Caster
{
    public function cast(mixed $input): Address
    {
        return new Address(
            street: $input['street'],
            city: $input['city'],
            postalCode: $input['postal_code'],
        );
    }
}
```

```php app/AddressSerializer.php
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final readonly class AddressSerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! $input instanceof Address) {
            throw new ValueCouldNotBeSerialized(Address::class);
        }

        return $input->toArray();
    }
}
```

:::

Of course, Tempest provides casters and serializers for the most common data types, including arrays, booleans, dates, enumerations, integers and value objects.

### Registering casters and serializers globally

To register casters and serializers globally without specifying them for every property, implement the {b`\Tempest\Mapper\DynamicCaster`} or {b`\Tempest\Mapper\DynamicSerializer`} interface, which require an `accepts` method:

```php app/AddressSerializer.php
use Tempest\Mapper\Serializer;
use Tempest\Mapper\DynamicSerializer;

final readonly class AddressSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(Address::class);
    }

    public function serialize(mixed $input): array|string
    {
        if (! $input instanceof Address) {
            throw new ValueCouldNotBeSerialized(Address::class);
        }

        return $input->toArray();
    }
}
```

:::info
Dynamic serializers and casters will automatically be discovered by Tempest.
:::

### Specifying casters or serializers for properties

To use a specific caster or serializer for a property, apply the {b`#[Tempest\Mapper\CastWith]`} or {b`#[Tempest\Mapper\SerializeWith]`} attribute, respectively. Of course, both attributes can be used together on the same property.

```php
use Tempest\Mapper\CastWith;

final class User
{
    #[CastWith(AddressCaster::class)]
    #[SerializeWith(AddressSerializer::class)]
    public Address $address;
}
```

## Mapping contexts

Contexts enable using different casters, serializers, and mappers depending on the situation. For example, dates can be serialized differently for an API response versus database storage, or different validation rules can be applied for different contexts.

### Specifying a context

To specify a context when mapping, use the `in()` method on the mapper instance. Contexts can be provided as a string, an enum, or a {b`\Tempest\Mapper\Context`} object.

```php
use App\SerializationContext;
use function Tempest\Mapper\map;

$json = map($book)
    ->in(SerializationContext::API)
    ->toJson();
```

To create a caster or serializer that only applies in a specific context, use the {b`#[Tempest\Mapper\Attributes\Context]`} attribute on your class and provide it with a context name:

```php app/ApiDateSerializer.php
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Serializer;
use Tempest\Mapper\DynamicSerializer;

#[Context(SerializationContext::API)]
final readonly class ApiDateSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(DateTime::class);
    }

    public function serialize(mixed $input): string
    {
        return $input->format(FormatPattern::ISO8601);
    }
}
```

This serializer is only used when mapping with `->in(SerializationContext::API)`. Without a context specified, or in other contexts, the default serializers are used.

### Injecting context into casters and serializers

To adapt behavior dynamically, inject the current context into the caster or serializer constructor by naming its property `$context`. Other dependencies from the container can also be injected.

```php
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Serializer;

#[Context(DatabaseContext::class)]
final class BooleanSerializer implements Serializer, DynamicSerializer
{
    public function __construct(
        private DatabaseContext $context,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $type): bool
    {
        $type = $type instanceof PropertyReflector
            ? $type->getType()
            : $type;

        return $type->getName() === 'bool' || $type->getName() === 'boolean';
    }

    public function serialize(mixed $input): string
    {
        return match ($this->context->dialect) {
            DatabaseDialect::POSTGRESQL => $input ? 'true' : 'false',
            default => $input ? '1' : '0',
        };
    }
}
```

## Configurable casters and serializers

Casters or serializers sometimes need configuration based on the property they're applied to. For example, an enum caster needs to know which enum class to use, and an object caster needs to know the target type.

To create casters or serializers that are configured per property, implement the {b`\Tempest\Mapper\ConfigurableCaster`} or {b`\Tempest\Mapper\ConfigurableSerializer`} interface:

```php
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;

final readonly class EnumCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        private string $enum,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(UnitEnum::class);
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        // Create a new instance configured for this property
        return new self(enum: $property->getType()->getName());
    }

    public function cast(mixed $input): ?object
    {
        if ($input === null) {
            return null;
        }

        // Use the configured enum class
        return $this->enum::from($input);
    }
}
```

The `configure()` method receives the property being mapped and the current context, enabling the creation of a caster instance tailored to that specific property.

Note that `ConfigurableSerializer::configure()` can receive either a `PropertyReflector`, `TypeReflector`, or `string`, depending on whether it's used for property mapping or value serialization.

Configurable casters and serializers are appropriate when:

- The caster or serializer behavior depends on the specific property type (e.g., enum class, object class),
- Access to property attributes or metadata is required,
- Different properties of the same base type require different handling,
- Creating many similar caster or serializer classes needs to be avoided.

For static behavior that doesn't depend on property information, regular casters and serializers are sufficient.


---
title: Asset bundling
description: "Web applications usually need to serve assets to users. Tempest provide a seamless integration with Vite, the most popular front-end development server and build tool"
keywords: ["vite", "frontend", "js", "css", "ts", "typescript", "javascript", "sri", "manifest", "assets"]
---

## Overview

[Vite](https://vite.dev) is the de-facto standard build tool for front-end development. It provides a very fast development server and bundles your assets for production without barely any configuration needed.

Tempest provides an integration with Vite that consists of a [Vite plugin](https://github.com/tempestphp/tempest-framework/tree/main/packages/vite-plugin-tempest) and a [server-side package](https://github.com/tempestphp/tempest-framework/tree/main/src/Tempest/Vite).

## Quick start

To install Vite, you may run the corresponding installer command. The wizard will guide you through the installation, including adding the Vite plugin, the `vite.config.ts` configuration file, the TypeScript entrypoint, and, if you chose so, Tailwind CSS.

```sh
php tempest install vite
```

The next step is to add the [`{html}<x-vite-tags />`](../1-essentials/02-views.md#x-vite-tags) component to your base template. This is how the script and style tags including your entrypoints are provided to the browser.

```html x-base.view.php
<html lang="en">
	<head>
		<!-- ... -->
		<x-vite-tags />
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

## Running the development server

During development, the purpose of Vite is to transpile asset files on-the-fly to a format that the browser understands. This is the concept that makes Vite really fast—it doesn't need to bundle the whole application everytime some code is updated.

For Vite to be able to transpile assets, its server needs to be started. This is done by running its command-line interface, `vite`.

```sh
npm run dev
```

The command above looks for the `dev` script in `package.json`, which in turns runs the `vite` CLI. This is the equivalent of running the `{sh}npx vite` command.

## Entrypoints

An entrypoint is a primary script or stylesheet that serves as the starting point in an application. Any asset file ending with `.entrypoint.{ts,css,js}` will automatically be discovered by Tempest, meaning you don't have to configure anything.

```js app/main.entrypoint.ts
console.log('Hello, world! 🌊')
```

### Manually including an entrypoint

It might happen that you only need a specific script or stylesheet in a particular view. In this situation, you may use the [`{html}<x-vite-tags />`](./03-views#x-vite-tags) component with an `entrypoint` attribute that points to the file you want to include:

```html app/Profile/show.view.php
<x-base>
	<slot name="head">
		<x-vite-tags entrypoint="src/Profile/profile.css" />
	</slot>
	<!-- ... -->
</x-base>
```

:::warning
For Vite to bundle this file in production, it still needs to be [configured as an entrypoint](#manually-configuring-entrypoints). Otherwise, it will not be included in the production manifest, and Tempest won't be able to generate a link to it.
:::

### Manually configuring entrypoints

If you prefer, you may opt-out of the `*.entrypoint.{ts,cs,js}` naming convention and manually configure entrypoints in the Vite configuration.

To do so, create a `vite.config.php` file that returns a {`Tempest\Vite\ViteConfig`} instance. You should configure the `entrypoints` parameter:

```php app/vite.config.php
return new ViteConfig(
    entrypoints: [
        'app/main.css',
        'app/main.ts',
    ],
);
```

Note that the paths to the entrypoint files must be relative to the root of the project.

If you opted in for manual entrypoint configuration, your base template should also [specify which entrypoint to include by default](#manually-including-an-entrypoint). Otherwise, all configured entrypoints will be used.

## Building for production

Running the `build` script from the `package.json` will bundle your application's assets, versioning them and creating a `manifest.json` file.

```sh
npm run build
```

By default, assets are compiled in the `public/build` directory. This directory should be added to `.gitignore`, to avoid adding compiled assets to version control.

:::info
This directory is already in your `.gitignore` if you used the `{sh}php tempest install vite` command.
:::

## Using a `nonce` attribute

If your application uses a [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP), you may need to include `nonce` attributes to the tags generated by `{html}<x-vite-tags />`.

The value of a `nonce` attribute should be used only once per request, as it is mainly used to prevent [replay attacks](https://en.wikipedia.org/wiki/Replay_attack). To generate and configure that value for each request, you may use a [route middleware](../1-essentials/02-views.md#route-middleware):

```php
use Tempest\Support\Random;
use Tempest\Vite\ViteConfig;

final class ConfigureViteNonce implements HttpMiddleware
{
    public function __construct(
        private readonly ViteConfig $viteConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->viteConfig->nonce = Random\secure_string(length: 40);

        return $next($request);
    }
}
```

Note that middleware are not automatically registered, as their order generally matters. You may manually include this middleware to routes that need it, or apply it automatically by registering it globally:

```php
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\Router;
use Tempest\Support\Random;
use Tempest\Vite\ViteConfig;

final class ConfigureViteNonce implements HttpMiddleware
{
    public function __construct(
        private readonly Router $router,
        private readonly ViteConfig $viteConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->viteConfig->nonce = Random\secure_string(length: 40);

        return $next($request);
    }

    #[EventHandler(KernelEvent::BOOTED)]
    public function register(): void
    {
        $this->router->addMiddleware(self::class);
    }
}
```

The `register` method above is an [event handler](../2-features/08-events.md) that is called when Tempest boots. It registers the middleware on the injected {`Tempest\Router\Router`} instance, effectively registering it for every route.

Alternatively, you may also set the `nonce` directly in the event handler. However, keep in mind that this would be called every time the framework boots, even when only using console commands.

## Subresource integrity

Tempest will detect [subresource integrity](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity) hashes in your `manifest.json` file and will automatically add them to the generated script and style tags.

Integrity hashes are not included in Vite manifests by default, but the `vite-plugin-manifest-sri` plugin provides this functionality. You may install it through `bun` or `npm` and register it like any other Vite plugin in your configuration file:

```js vite.config.ts
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'
import sri from 'vite-plugin-manifest-sri'
import tempest from 'vite-plugin-tempest'

export default defineConfig({
	plugins: [
		tailwindcss(),
		tempest(),
		sri(),
	],
})
```

## Testing

By default, Tempest is instructed to not generate any tag during tests. This behavior is in place to prevent triggering `ManifestNotFoundException` exceptions in your test suite.

If, for any reason, you wish to restore tag resolution in a test, you may call the `{php}allowTagResolution()` method on the `ViteTester` instance:

```php tests/SomeTest.php
public function setUp(): void
{
    parent::setUp();

    $this->vite->allowTagResolution();
}
```


---
title: Validation
description: "Tempest's validation is based on built-in PHP types, but provides many attribute-based rules to cover a wide variety of situations."
---

## Overview

Tempest provides a {`\Tempest\Validation\Validator`} object capable of validating an array of values against the public properties of a class or an array of validation rules.

While validation and [data mapping](./01-mapper) often work together, the two are separate components and can also be used separately.

## Validating against objects

When you have raw data and an associated model or data transfer object, you may use the `validateValuesForClass()` method on the {b`\Tempest\Validation\Validator`}. Note that the validator needs to be [resolved from the container](../1-essentials/05-container.md#injecting-dependencies).

```php
$failingRules = $this->validator->validateValuesForClass(Book::class,  [
    'title' => 'Timeline Taxi',
    'description' => 'My sci-fi novel',
    'publishedAt' => '2024-10-01',
]);
```

This method accepts a fully-qualified class name as the first argument, and an array of data as the second. The values of the data array will be validated against the public properties of the class.

In this case, validation works by inferring validation rules from the built-in PHP types. In the example above, the `Book` class has the following public properties:

```php
use Tempest\DateTime\DateTime;

final class Book
{
    public string $title;
    public string $description;
    public ?DateTime $publishedAt = null;
}
```

If validation fails, `validateValuesForClass()` returns a list of fields and their respective failed rules.

### Adding more rules

Most of the time, the built-in PHP types will not be enough to fully validate your data. You may then add validation attributes to the model or data transfer object.

```php
use Tempest\Validation\Rules;

final class Book
{
    #[Rules\HasLength(min: 5, max: 50)]
    public string $title;

    #[Rules\IsNotEmptyString]
    public string $description;

    #[Rules\HasDateTimeFormat('Y-m-d')]
    public ?DateTime $publishedAt = null;
}
```

:::info
A list of all available validation rules can be found on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/validation/src/Rules).
:::

### Skipping validation

You may have situations where you don't want specific properties on a model to be validated. In this case, you may use the {b`#[Tempest\Validation\SkipValidation]`} attribute to prevent them from being validated.

```php
use Tempest\Validation\SkipValidation;

final class Book
{
    #[SkipValidation]
    public string $title;
}
```

## Validating against specific rules

If you don't have a model or data transfer object to validate data against, you may alternatively use the `validateValues()` and provide an array of rules.

```php
$this->validator->validateValues([
    'name' => 'Jon Doe',
    'email' => 'jon@doe.co',
    'age' => 25,
], [
    'name' => [new IsString(), new IsNotNull()],
    'email' => [new IsEmail()],
    'age' => [new IsInteger(), new IsNotNull()],
]);
```

If validation fails, `validateValues()` returns a list of fields and their respective failing rules.

:::info
A list of all available validation rules can be found on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/validation/src/Rules).
:::

## Validating a single value

You may validate a single value against a set of rules using the `validateValue()` method.

```php
$this->validator->validateValue('jon@doe.co', [new IsEmail()]);
```

Alternatively, you may provide a closure for validation. The closure should return `true` if validation passes, or `false` otherwise. You may also return a string to specify the validation failure message.

```php
$this->validator->validateValue('jon@doe.co', function (mixed $value) {
    return str_contains($value, '@');
});
```

## Accessing error messages

When validation fails, a list of fields and their respective failing rules is returned. You may call the `getErrorMessage` method on the validator to get a [localized](./11-localization.md) validation message.

```php
use Tempest\Support\Arr;
use Tempest\Validation\Rules\IsEmail;

// Validate some value
$failures = $this->validator->validateValue('jon@doe.co', new IsEmail());

// Map failures to their message
$errors = Arr\map_iterable($failures, fn (FailingRule $failure) => $this->validator->getErrorMessage($failure));
```

You may also specify the field name of the validation failure to get a localized message for that field.

```php
$this->validator->getErrorMessage($failure, 'email');
// => 'Email must be a valid email address'
```

## Overriding translation messages

You may override the default validation messages by adding a [translation file](../2-features/11-localization.md#defining-translation-messages) anywhere in your codebase. Note that Tempest uses the [MessageFormat 2.0](https://messageformat.unicode.org/) format for localization.

```php app/Localization/validation.en.yml
validation_error:
  is_email: |
    .input {$field :string}
    {$field} must be a valid email address.
```

Sometimes though, you may want to have a specific error message for a rule, without overriding the default translation message for that rule.

This can be done by using the {b`#[Tempest\Validation\TranslationKey]`} attribute on the property being validated. For instance, you may have the following object:

```php
final class Book {
    #[Rules\HasLength(min: 5, max: 50)]
    #[TranslationKey('book_management.book_title')]
    public string $title;
}
```

When this rule fails, the `getErrorMessage()` method from the validator will use `validation_error.has_length.book_management.book_title` as the translation key, instead of `validation_error.has_length`.


---
title: Authentication
description: "Learn how to authenticate models, implement access control, and secure your application with Tempest's flexible authentication system."
keywords: "Experimental"
---

## Overview

Tempest provides an authentication implementation designed to be flexible, not assuming an authenticatable model is a user. This means you can use it for API keys, service accounts, or any other system that requires authentication.

Additionally, Tempest provides a [policy-based access control](#access-control) implementation that allows you to define fine-grained permissions for your resources.

## Quick start

Tempest does not assume that all applications have users, but it is the most common case. For this reason, we provide the ability to publish a basic user model and its migration.

```sh sh
./tempest install auth
```

After publishing, you may run `./tempest migrate`. You now have the building blocks for your authentication.

## Authentication

Tempest's authentication is flexible enough not to assume that an authenticatable model is a user. If your application uses a different system for authentication, such as an API key or a service account, you have the ability to create such a model while preserving the correct nomenclature.

To register an authenticatable model, you may create a class that implements the {b`Tempest\Auth\Authentication\Authenticatable`} interface. This interface is automatically discovered by Tempest.

```php app/Authentication/User.php
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Hashed;

final class User implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed]
        #[\SensitiveParameter]
        public ?string $password,
    ) {}
}
```

Note that if you use the default [database authenticatable resolver](#custom-authenticatable-resolver), the model must have at least a {b`Tempest\Database\PrimaryKey`} property—it will be used to uniquely identify the model in the database.

### Authenticating a model

Authenticating a model—in most cases, a user—is usually done in a controller. Tempest provides an {b`Tempest\Auth\Authentication\Authenticator`} that may authenticate, deauthenticate, and access the currently authenticated model.

Because there are a lot of different ways to authenticate users or systems, Tempest doesn't provide the logic to verify authentication credentials. In the case of a user, you may use the {b`Tempest\Cryptography\Password\PasswordHasher`} for this purpose.

```php app/Authentication/AuthenticationController.php
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cryptography\Password\PasswordHasher;

final readonly class AuthenticationController
{
    public function __construct(
        private Authenticator $authenticator,
        private PasswordHasher $passwordHasher,
    ) {}

    #[Post('/login')]
    public function login(LoginRequest $request): Redirect
    {
        $user = query(User::class)
            ->select()
            ->where('email', $request->email)
            ->first();

        if (! $user || ! $this->passwordHasher->verify($request->password, $user->password)) {
            return new Redirect('/login')->flash('error', 'Invalid credentials');
        }

        $this->authenticator->authenticate($user);

        return new Redirect('/');
    }
    
    #[Post('/logout')]
    public function logout(): Redirect
    {
        $this->authenticator->deauthenticate();
        
        return new Redirect('/login');
    }
}
```

### Accessing the authenticated model

You may access the currently authenticated model by injecting the {b`Tempest\Auth\Authentication\Authenticator`}. The authenticator provides a `current()` method that returns the currently authenticated model, or `null` if no model is authenticated.

```php app/ProfileController.php
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class ProfileController
{
    public function __construct(
        private Authenticator $authenticator,
    ) {}

    #[Get('/profile', middleware: [MustBeAuthenticated::class])]
    public function show(): View
    {
        return view('profile.view.php', user: $this->authenticator->current());
    }
}
```

Alternatively, you may also inject the model directly. For instance, if you have a `User` model implementing `Authenticatable`, it can be injected as a dependency:

```php app/ProfileController.php
final readonly class ProfileController
{
    public function __construct(
        private User $user,
    ) {}

    #[Get('/profile', middleware: [MustBeAuthenticated::class])]
    public function show(): View
    {
        return view('profile.view.php', user: $this->user);
    }
}
```

:::warning
In situations where the model might not be authenticated—for instance, in a route that is not protected by a middleware, you will need to make the property nullable.
:::

### Custom authenticatable resolver

The authenticatable resolver is used internally by the authenticator to resolve an unique identifier from a model and the other way around. Typically, applications use a database to store users, but you can implement custom resolvers to fetch users from other sources, such as LDAP or external APIs.

Tempest provides a {b`Tempest\Auth\Authentication\DatabaseAuthenticatableResolver`}, which is used by default. However, you may implement your own resolver by implementing the {b`Tempest\Auth\Authentication\AuthenticatableResolver`} interface.

```php app/Authentication/LdapAuthenticatableResolver.php
use Tempest\Auth\Authentication\AuthenticatableResolver;
use Tempest\Auth\Authentication\Authenticatable;
use App\Authentication\User;

final readonly class LdapAuthenticatableResolver implements AuthenticatableResolver
{
    public function __construct(
        private LdapClient $ldap,
    ) {}

    public function resolve(int|string $id, string $class): ?Authenticatable
    {
        $attributes = $this->ldap->findUserByIdentifier($id);

        if ($attributes === null) {
            return null;
        }

        return new User(
            username: $attributes['uid'] ?? null,
            email: $attributes['mail'] ?? null,
            displayName: $attributes['cn'] ?? null
        );
    }

    public function resolveId(Authenticatable $authenticatable): int|string
    {
        return $authenticatable->email;
    }
}
```

To instruct Tempest that you want to use your own resolver, you will need to create a dedicated [initializer](../1-essentials/05-container.md#implementing-an-initializer).

```php app/Authentication/LdapAuthenticatableResolverInitializer.php
use Tempest\Auth\Authentication\AuthenticatableResolver;

final class LdapAuthenticatableResolverInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AuthenticatableResolver
    {
        return new LdapAuthenticatableResolver(
            ldap: $container->get(LdapClient::class),
        );
    }
}
```

### Custom authenticator

By default, Tempest uses the provided {b`Tempest\Auth\Authentication\SessionAuthenticator`} to remember the authenticated model across requests using browser sessions.

However, you may provide your own authenticator by implementing the {b`Tempest\Auth\Authentication\Authenticator`} interface. For instance, may want the model to be authenticated for the duration of the request only.

```php app/Authentication/RequestAuthenticator.php
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\Authenticatable;

#[Autowire]
final class RequestAuthenticator implements Authenticator
{
    private ?Authenticatable $current = null;

    public function authenticate(Authenticatable $authenticatable): void
    {
        $this->current = $authenticatable;
    }

    public function deauthenticate(): void
    {
        $this->current = null;
    }

    public function current(): ?Authenticatable
    {
        return $this->current;
    }
}
```

## Access control

In most applications, it is necessary to restrict access to certain resources depending on many factors. For instance, you may want to allow only the author of a post to edit it, or allow only administrators to delete other users.

To solve this problem, Tempest provides the ability to write policies. A policy defines the authorization rules for a specific resource, allowing you to implement complex business logic around who can access that resource.

This paradigm is known as [policy-based access control](https://en.wikipedia.org/wiki/Attribute-based_access_control). Policies build on the concept of actions, resources and subjects:

- An action is a specific operation that can be performed on a resource, such as `view`, `edit`, or `delete`.
- A resource may be anything represented by a class.
- A subject is the entity that is trying to perform the action, typically the authenticated user.

### Defining policies

To create a policy, you may define a method in any class and annotate it with the {b`#[Tempest\Auth\AccessControl\Policy]`} attribute. Typically, this is done in a dedicated policy class.

The attribute expects the class name of the resource as its first parameter, and the action name as the second parameter. If the resource is not specified, it will be inferred by the method's first parameter. Similarly, if the action name is not provided, the kebab-cased method name is used instead.

```php app/PostPolicy.php
use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\AccessControl\AccessDecision;

final class PostPolicy
{
    #[Policy(Post::class)]
    public function create(): bool
    {
        return true;
    }

    #[Policy]
    public function view(Post $post): bool
    {
        if (! $post->published) {
            return false;
        }

        return true;
    }

    #[Policy(action: ['edit', 'update'])]
    public function edit(Post $post, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $post->authorId === $user->id->value;
    }
}
```

The policy method will be given the resource instance as the first parameter and the subject as the second one. Both of these may be `null`, depending on the context in which the policy is evaluated.

The policy method is expected to return a boolean value or an {b`Tempest\Auth\AccessControl\AccessDecision`} instance. The latter can be used to provide more context about the decision:

```php
return AccessDecision::denied('You must be authenticated to perform this action.');
```

### Checking for permissions

You may inject the {b`Tempest\Auth\AccessControl\AccessControl`} interface to check if a specific action is granted for a resource and subject. Typically, the `ensureGranted()` method is called in a controller.

```php app/Controllers/PostController.php
use Tempest\Auth\AccessControl\AccessControl;

final readonly class PostController
{
    public function __construct(
        private AccessControl $accessControl,
    ) {}

    #[Delete('/posts/{post}')]
    public function delete(Post $post): Redirect
    {
        $this->accessControl->ensureGranted('delete', $post);

        // Proceed with deletion...
        
        return new Redirect('/posts');
    }
}
```

Alternatively, you may use the `isGranted()` method. It will return an {b`Tempest\Auth\AccessControl\AccessDecision`} instance. Check the `granted` property to determine access for the resource and subject.

:::info
Note that the subject is optional in both methods—if omitted, the [authenticated model](#authentication) is automatically provided.
:::

### Resources without instances

When evaluating the ability to perform an action on a resource without an instance, you may pass the class name of the resource as a string. Typically, this is used when checking if a subject has the permissions to create a new resource.

```php
$accessControl->isGranted('create', resource: Post::class, subject: $user);
```


---
title: File storage
description: "Tempest's storage provides a way to access many different types of filesystems, such as the local filesystem, Amazon S3, Cloudflare R2 or even an FTP server."
---

## Overview

Tempest provides the ability to interact with the local filesystem and many cloud storage solutions, such as Cloudflare R2 or Amazon S3, using the same interface.

This implementation is built on top of [Flysystem](https://github.com/thephpleague/flysystem)—a reliable, battle-tested abstraction layer for file systems.

## Getting started

To get started with file storage, you will first need to create a configuration file for your desired filesystem.

Tempest provides a different configuration object for each provider. For instance, if you wish to interact with an Amazon S3 bucket, you may create a `s3.config.php` file returning an instance of {b`Tempest\Storage\Config\S3StorageConfig`}:

```php app/s3.config.php
return new S3StorageConfig(
    bucket: env('S3_BUCKET'),
    region: env('S3_REGION'),
    accessKeyId: env('S3_ACCESS_KEY_ID'),
    secretAccessKey: env('S3_SECRET_ACCESS_KEY'),
);
```

In this example, the S3 credentials are specified in the `.env`, so a different bucket and credentials can be configured depending on the environment.

Once your storage is configured, you may interact with it by using the {`Tempest\Storage\Storage`} interface. This is usually done through [dependency injection](../1-essentials/05-container.md#injecting-dependencies):

```php app/UserService.php
final readonly class UserService
{
    public function __construct(
        private Storage $storage,
    ) {}

    public function getProfilePictureUrl(User $user): string
    {
        return $this->storage->publicUrl($user->profile_picture_path);
    }

    // …
}
```

## The storage interface

Once you have access to the the {b`Tempest\Storage\Storage`} interface, you gain access to a few useful methods for working with files, directory and streams. All methods are documented, so you are free to explore the source to get an understanding of what you can do with it.

Below are a few useful methods that you may need more often than the others:

```php
/**
 * Gets a public URL to the file at the specified `$location`.
 */
$storage->publicUrl($location);

/**
 * Writes the given `$contents` to the specified `$location`.
 */
$storage->write($location, $contents);

/**
 * Reads the contents of the file at the specified `$location`.
 */
$storage->read($location);

/**
 * Deletes the file or directory at the specified `$location`.
 */
$storage->delete($location);

/**
 * Determines whether a file or a directory exists at the specified `$location`.
 */
$storage->fileOrDirectoryExists($location);
```

## Configuration

Tempest provides a different configuration object for each storage provider. Below are the ones that are currently supported:

- {`Tempest\Storage\Config\LocalStorageConfig`}
- {`Tempest\Storage\Config\R2StorageConfig`}
- {`Tempest\Storage\Config\S3StorageConfig`}
- {`Tempest\Storage\Config\AzureStorageConfig`}
- {`Tempest\Storage\Config\FTPStorageConfig`}
- {`Tempest\Storage\Config\GoogleCloudStorageConfig`}
- {`Tempest\Storage\Config\InMemoryStorageConfig`}
- {`Tempest\Storage\Config\SFTPStorageConfig`}
- {`Tempest\Storage\Config\StorageConfig`}
- {`Tempest\Storage\Config\ZipArchiveStorageConfig`}
- {`Tempest\Storage\Config\CustomStorageConfig`}

### Multiple storages

If you need to work with multiple storage locations, you may create multiple storage configurations using tags. These tags may then be used to resolve the {b`Tempest\Storage\Storage`} interface, which will use the corresponding configuration.

It's a good practice to use an enum for the tag:

```php app/userdata.storage.config.php
return new S3StorageConfig(
    tag: StorageLocation::USER_DATA,
    bucket: env('USERDATA_S3_BUCKET'),
    region: env('USERDATA_S3_REGION'),
    accessKeyId: env('USERDATA_S3_ACCESS_KEY_ID'),
    secretAccessKey: env('USERDATA_S3_SECRET_ACCESS_KEY'),
);
```

```php app/backup.storage.config.php
return new R2StorageConfig(
    tag: StorageLocation::BACKUPS,
    bucket: env('BACKUPS_R2_BUCKET'),
    endpoint: env('BACKUPS_R2_ENDPOINT'),
    accessKeyId: env('BACKUPS_R2_ACCESS_KEY_ID'),
    secretAccessKey: env('BACKUPS_R2_SECRET_ACCESS_KEY'),
);
```

Once you have configured your storages and your tags, you may inject the {b`Tempest\Storage\Storage`} interface using the corresponding tag:

```php app/BackupService.php
final readonly class BackupService
{
    public function __construct(
        #[Tag(StorageLocation::BACKUPS)]
        private Storage $storage,
    ) {}

    // …
}
```

### Read-only storage

A storage may be restricted to only allow read operations. Attempting to write to such a storage will result in a `League\Flysystem\UnableToWriteFile` exception being thrown.

First, the `league/flysystem-read-only` adapter needs to be installed:

```sh
composer require league/flysystem-read-only
```

Once this is done, you may pass the `readonly` parameter to the adapter configuration and set it to `true`.

```php app/data-snapshots.storage.config.php
return new S3StorageConfig(
    tag: StorageLocation::DATA_SNAPSHOTS,
    readonly: true,
    bucket: env('DATA_SNAPSHOTS_S3_BUCKET'),
    region: env('DATA_SNAPSHOTS_S3_REGION'),
    accessKeyId: env('DATA_SNAPSHOTS_S3_ACCESS_KEY_ID'),
    secretAccessKey: env('DATA_SNAPSHOTS_S3_SECRET_ACCESS_KEY'),
);
```

### Custom storage

If you need to implement your own adapter for an unsupported provider, you may do so by implementing the `League\Flysystem\FilesystemAdapter` interface.

Tempest provides a {b`Tempest\Storage\Config\CustomStorageConfig`} configuration object which accepts any `FilesystemAdapter`, which will be resolved through the container.

```php app/custom-storage.config.php
return new CustomStorageConfig(
    adapter: App\MyCustomFilesystemAdapter::class,
);
```

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the storage testing utilities through the `storage` property.

These utilities include a way to replace the storage with a testing implementation, as well as a few assertion methods related to files and directories.

### Faking a storage

You may generate a fake, testing-only storage by calling the `fake()` method on the `storage` property. This will replace the storage implementation in the container, and provide useful assertion methods.

```php
// Replace the storage with a fake implementation
$storage = $this->storage->fake();

// Replace the specified storage with a fake implementation
$storage = $this->storage->fake(StorageLocation::DATA_SNAPSHOTS);

// Asserts that the specified file exists
$storage->assertFileExists('file.txt');
```

These fake storages are located in `.tempest/tests/storage`. They get erased every time the `fake()` method is called. To prevent this, you may set the `persist` argument to `true`.

### Preventing storage access during tests

It may be useful to prevent code from using any of the registered storages during tests. This could happen when forgetting to fake a storage for a specific test, for instance, and could result in unexpected costs when relying on a cloud storage provider.

This may be achieved by calling the `preventUsageWithoutFake()` method on the `storage` property.

```php tests/MyServiceTest.php
$this->storage->preventUsageWithoutFake();
```


---
title: Cache
description: "The cache component is based on Symfony's Cache, providing access to many different adapters through a convenient, simple interface."
---

## Getting started

By default, Tempest uses a filesystem-based caching strategy. You may use a different cache back-end by creating a configuration file for the desired cache adapter.

<!-- For instance, you may use Redis as your cache back-end by creating a `cache.config.php` file returning an instance of {b`Tempest\Cache\Config\RedisCacheConfig`}:

```php app/cache.config.php
return new RedisCacheConfig(
    host: env('REDIS_HOST', default: '127.0.0.1'),
    port: env('REDIS_PORT', default: 6379),
    username: env('REDIS_USERNAME'),
    password: env('REDIS_PASSWORD'),
);
```

In this example, the Redis credentials are specified in the `.env`, so a different bucket and credentials can be configured depending on the environment. Of course, you may use different, more specific environment variables if needed. -->

Once your cache is configured, you may interact with it by using the {`Tempest\Cache\Cache`} interface. This is usually done through [dependency injection](../1-essentials/05-container.md#injecting-dependencies):

```php app/OrderService.php
use Tempest\Cache\Cache;
use Tempest\DateTime\Duration;

final readonly class OrderService
{
    public function __construct(
        private Cache $cache,
    ) {}

    public function getOrdersCount(): int
    {
        return $this->cache->resolve(
            key: 'orders_count',
            callback: fn () => $this->fetchOrdersCountFromDatabase(),
            expiration: Duration::hours(12)
        );
    }

    // …
}
```

## The cache interface

Once you have access to the the {b`Tempest\Cache\Cache`} interface, you gain access to a few useful methods for working with cache items. All methods are documented, so you are free to explore the source to get an understanding of what you can do with it.

Below are a few useful methods that you may need more often than the others:

```php
/**
 * Gets a value from the cache by the given key.
 */
$cache->get($key);

/**
 * Sets a value in the cache for the given key.
 */
$cache->put($key, $value);

/**
 * Gets a value from the cache by the given key, or resolve it using the given callback.
 */
$cache->resolve($key, function () {
    return $this->expensiveOperation();
});
```

## Clearing the cache

The cache may programmatically by cleared by calling the `clear()` method on a cache instance. However, it is sometimes useful to manually clear it. To do so, you may call the `cache:clear` command:

```sh
./tempest cache:clear
```

By default, this would clear the main cache. If there are multiple configured caches, you will be prompted to choose which one to clear.

## Disabling caches

During development, all internal caches except the icon one are disabled. This is to ensure that you always get the latest changes when working on your application.

In production, all caches are automatically enabled without you needing to tweak any configuration. In all environments, you may forcefully enable or disable caches by adding a dedicated environment variable to your `.env`.

### Disabling project caches

You may set the `CACHE_ENABLED` environment variable to `false` to forcefully disable your project cache. When disabled, the cache will not save any value and will return default values for getter methods.

```ini .env
# Force-disables user cache
CACHE_ENABLED=false

# Force-disables a tagged cache named `custom`
CACHE_CUSTOM_ENABLED=false
```

### Disabling internal caches

Tempest has a few internal caches for views, discovery, configuration and icons. You may forcefully disable these caches, individually or all at once, by setting the following environment variables in your `.env` file:

```ini .env
# Force-disables all internal caches
INTERNAL_CACHES=false

# Force-disables the view cache
VIEW_CACHE=false

# Force-disables the icon cache
ICON_CACHE=false

# Force-disables the discovery cache
DISCOVERY_CACHE=false

# Force-disables the config cache
CONFIG_CACHE=false
```

## Locks

You may create a lock by calling the `lock()` method on a cache instance. After being created, the lock needs to be acquired by calling the `acquire()`, and released by calling the `release()` method.

Alternatively, the `execute()` method may be used to acquire a lock, execute a callback, and release the lock automatically when the callback is done.

```php
// Create the lock
$lock = $cache->lock('processing', Duration::seconds(30));

// Acquire the lock, do something and release it.
if ($lock->acquire()) {
    $this->process();

    $lock->release();
}

// Or using a callback, with an optional wait
// time if the lock is not yet available.
$lock->execute($this->process(...), wait: Duration::seconds(30));
```

### Lock ownership

Normally, a lock cannot be acquired if it is already held by another process. However, if you know the owner token, you may still access a lock by specifying the `owner` parameter.

This may be useful to release a lock in an async command, for instance.

```php
$cache->lock("processing:{$processId}", owner: $processId)
    ->release();
```

## Configuration

Tempest provides a different configuration object for each cache provider. Below are the ones that are currently supported:

- {`Tempest\Cache\Config\FilesystemCacheConfig`}
- {`Tempest\Cache\Config\InMemoryCacheConfig`}
- {`Tempest\Cache\Config\PhpCacheConfig`}

<!-- - {`Tempest\Cache\Config\RedisCacheConfig`}
- {`Tempest\Cache\Config\PredisCacheConfig`}
- {`Tempest\Cache\Config\ValkeyCacheConfig`} -->

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the cache testing utilities through the `cache` property.

These utilities include a way to replace the cache with a testing implementation, as well as a few assertion methods related to cache items and locks.

### Faking the cache

You may generate a fake, testing-only cache by calling the `fake()` method on the `cache` property. This will replace the cache implementation in the container, and provide useful assertion methods.

```php
// Replace the cache with a fake implementation
$cache = $this->cache->fake();

// Asserts that the specified cache key exists
$cache->assertCached('users_count');

// Asserts that the cache is empty
$cache->assertEmpty();
```

### Testing locks

Calling the `lock()` method on the cache testing utility will return a testing lock, which provides a few more testing utilities.

```php
$cache = $this->cache->fake();

// Call some application code
// …

$cache->assertNotLocked('processing');
```


---
title: Mail
description: "Tempest provides a convenient layer built on top of Symfony's excellent mailer component so that you can send emails with ease."
---

## Getting started

Sending emails starts with picking an email transport. Tempest comes with built-in support for SMTP, Amazon SES, and Postmark; but it's trivial to add any other transport you'd like. We'll start with plain SMTP, and explain how to switch to other transports later.

By default, Tempest is configured to use SMTP mailing. You'll need to add these environment variables and the mailer will be ready for use:

```dotenv
MAIL_SMTP_HOST=mail.my_provider.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USERNAME=my_username@my_provider.com
MAIL_SMTP_PASSWORD=my_password_123
MAIL_SENDER_NAME=Brent
MAIL_SENDER_EMAIL=brendt@stitcher.io
```

Sending an email is done via the {b`\Tempest\Mail\Mailer`}, you can inject it anywhere you'd like:

```php
use Tempest\Mail\Mailer;
use Tempest\Mail\GenericEmail;
 
final class UserEventHandlers
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    #[EventHandler]
    public function onCreated(UserCreated $userCreated): void
    {
        $this->mailer->send(new GenericEmail(
            subject: 'Welcome!',
            to: $userCreated->email,
            html: view(
                __DIR__ . '/mails/welcome.view.php', 
                user: $userCreated->user,
            ),
        ));
    }
}
```

Note that {b`\Tempest\Mail\GenericEmail`} is a default email implementation that can be used on the fly, but a more scalable approach would be to make individual classes for every email:

```php
use Tempest\Mail\Mailer;
use Tempest\Mail\GenericEmail;
 
final class UserEventHandlers
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    #[EventHandler]
    public function onCreated(UserCreated $userCreated): void
    {
        $this->mailer->send(new WelcomeEmail($userCreated->user));
    }
}
```

Here's what that `WelcomeEmail` would look like:

```php
use Tempest\Mail\Email;
use Tempest\Mail\Envelope;
use Tempest\View\View;
use function Tempest\View\view;

final class WelcomeEmail implements Email
{
    public function __construct(
        private readonly User $user,
    ) {}

    public Envelope $envelope {
        get => new Envelope(
            subject: 'Welcome',
            to: $this->user->email,
        );
    }

    public string|View $html {
        get => view('welcome.view.php', user: $this->user);
    }
}
```

Note how {b`\Tempest\Mail\Envelope`} contains all meta information about an email. Here you can specify the subject and receiver, but also headers, bcc, cc, and more.

## Email content

In the previous examples, we assumed there to be a [view](/docs/essentials/views) attached to an email. Views are flexible since they can contain variable data like the user object, for example. In simple cases though, you might only want to send HTML without it being a view. In that case, you can pass in the HTML like so:

```php
use Tempest\Mail\Email;

final class WelcomeEmail implements Email
{
    // …
    
    public string|View $html {
        get => <<<HTML
        <h1>Thanks for joining!</h1>
        HTML;
    }
}
```

Whenever an email is sent, Tempest will automatically provide a text-only version of that email as well, which will be used by text-only email clients. The text is generated based on your HTML template (by stripping all the HTML tags). However, you also have the option to manually specify the text-only contents of an email, by implementing {b`Tempest\Mail\HasTextContent`}:

```php
use Tempest\Mail\Email;
use Tempest\View\View;
use Tempest\Mail\HasTextContent;

final class WelcomeEmail implements Email, HasTextContent
{
    // …
    
    public string|View|null $text = <<<TXT
    This is the text-only version of this email.
    TXT;
}
```

Note that you can _also_ use a view to render your text-only content. This is especially useful when you have lots of dynamic parts in your text content. Keep in mind that these kinds of views should not contain any HTML:

```php
use Tempest\Mail\Email;
use Tempest\View\View;
use Tempest\Mail\HasTextContent;

final class WelcomeEmail implements Email, HasTextContent
{
    // …
    
    public string|View|null $text {
        get => view('welcome-text.view.php', user: $this->user);
    }
}
```

```html welcome-text.view.php
Hello {{ $user->name }}

Please visit this link to activate your account: {{ $user->activationLink }}.

See you soon!

Tempest
```

## Attachments

If you want your email to have attachments, you can implement the {b`\Tempest\Mail\HasAttachments`} interface:

```php
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\HasAttachments;

final class WelcomeEmail implements Email, HasAttachments
{
    // …

    public array $attachments {
        get => [
            Attachment::fromFilesystem(__DIR__ . '/welcome.pdf')
        ];
    }
}
```

Creating attachments can be done in multiple ways:

- By referencing a file directly on the filesystem (as shown in the previous example);
- By using a [storage drive](/docs/features/file-storage): `Attachment::fromStorage($s3Storage, '/welcome.pdf')`;
- Or by manually passing a closure to a new attachment instance:

```php
use Tempest\Mail\Attachment;

$attachment = new Attachment(function () {
    return Pdf::createFromTemplate('user-pdf.pdf', user: $this->user);
});
```

## Other transports

As mentioned, Tempest has built-in support for SMTP, Amazon SES, and Postmark. It is however trivial to use a range of other transports as well. First let's talk about switching to one of the built-in transports.

The first step in using any transport is to install the transport-specific driver. You can find a list of all supported transports on [Symfony's documentation](https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport). If we take Postmark as an example, you should install these two dependencies:

```
composer require symfony/postmark-mailer
composer require symfony/http-client
```

Next, create a new mail config file and return an instance of {b`Tempest\Mail\Transports\Postmark\PostmarkConfig`}:

```php app/mail.config.php
use Tempest\Mail\Transports\Postmark\PostmarkConfig;
use function Tempest\env;

return new PostmarkConfig(
    key: env('MAIL_POSTMARK_TOKEN'),
);
```

Note that the Postmark token is the token associated with your Postmark account. A good practice is to also provide a default sender:

```php app/mail.config.php
use Tempest\Mail\EmailAddress;
use Tempest\Mail\Transports\Postmark\PostmarkConfig;
use function Tempest\env;

$defaultSender = null;

if (env('MAIL_SENDER_NAME') && env('MAIL_SENDER_EMAIL')) {
    $defaultSender = new EmailAddress(
        email: env('MAIL_SENDER_EMAIL'),
        name: env('MAIL_SENDER_NAME'),
    );
}

return new PostmarkConfig(
    key: env('MAIL_POSTMARK_TOKEN'),
    defaultSender: $defaultSender,
);
```

Finally, make sure that all environment variables are correctly set, and you're done! Tempest's mailer will now route your emails via Postmark.

## Creating your own transports

While SMTP, Amazon SES, and Postmark are built in, there are a lot of [other transports available](https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport) as well. In order to use one of those, you must create a new config class, specifically for that transport. Here's an example of using Mailgun. First you require the Symfony driver:

```
composer require symfony/mailgun-mailer
```

Then you create a new config class, specifically for that transport:

```php
final class MailgunConfig implements MailerConfig, ProvidesDefaultSender
{
    public string $transport = MailgunApiTransport::class;

    public function __construct(
        public readonly EmailAddress $defaultSender,
        #[SensitiveParameter]
        private readonly string $key,
        #[SensitiveParameter]
        private readonly string $domain,
    ) {}

    public function createTransport(): TransportInterface
    {
        return new MailgunTransportFactory()
            ->create(Dsn::fromString("mailgun+api://{$this->key}:{$this->domain}@default"));
    }
}
```

And finally, use it like so:

```php app/mail.config.php
return new MailgunConfig(
    defaultSender: $defaultSender,
    key: env('MAIL_MAILGUN_KEY'),
    domain: env('MAIL_MAILGUN_DOMAIN'),
);
```

## Events

Tempest dispatches events during the email sending lifecycle, allowing you to hook into both successful and failed sends.

### Email was sent

The {b`\Tempest\Mail\EmailWasSent`} event is dispatched after an email has been successfully sent:

```php
use Tempest\EventBus\EventHandler;
use Tempest\Mail\EmailWasSent;

final class MailEventHandlers
{
    #[EventHandler]
    public function onEmailSent(EmailWasSent $event): void
    {
        // $event->email contains the sent email
    }
}
```

### Email sending failed

The {b`\Tempest\Mail\EmailSendingFailed`} event is dispatched when the transport fails to send an email. The original exception is still re-thrown after the event is dispatched:

```php
use Tempest\EventBus\EventHandler;
use Tempest\Mail\EmailSendingFailed;

final class MailEventHandlers
{
    #[EventHandler]
    public function onEmailFailed(EmailSendingFailed $event): void
    {
        // $event->email contains the email that failed
        // $event->exception contains the thrown exception
    }
}
```

## Testing

Any test class extending from {b`\Tempest\Framework\Testing\IntegrationTest`} will have the {b`\Tempest\Mail\Testing\MailTester`} available:

```php
public function test_welcome_mail()
{
    $this->mailer
        ->send(new WelcomeEmail($this->user))
        ->assertSentTo($this->user->email)
        ->assertAttached('welcome.pdf');
}
```

You can also simulate transport failures using `shouldFail()`:

```php
#[Test]
public function email_failure(): void
{
    $this->mailer->shouldFail();

    try {
        $this->mailer->send(new WelcomeEmail($this->user));
    } catch (TransportException $exception) {
        // handle the exception
    }

    $this->mailer->assertFailed(WelcomeEmail::class);
}
```

By default, `shouldFail()` throws a Symfony `TransportException`. You can pass a custom exception if needed:

```php
$this->mailer->shouldFail(exception: new RuntimeException(message: 'Connection refused'));
```

Note that mails sent within tests using the {b`\Tempest\Mail\Testing\MailTester`} will never be actually sent. Read more about testing [here](/docs/essentials/testing).


---
title: Event bus
description: "Learn how to use Tempest's built-in event bus to dispatch events and decouple different components in your application."
---

## Overview

An event bus is a synchronous communication system that allows different parts of an application to interact while being decoupled from each other.

In Tempest, events can be anything from a scalar value to a simple data class. An event handler can be a closure or a class method, the former needing manual registration and the latter being automatically discovered by the framework.

## Defining events

Most events are typically simple data classes that store information relevant to the event. As a best practice, they should not include any logic.

```php app/AircraftRegistered.php
final readonly class AircraftRegistered
{
    public function __construct(
        public string $registration,
    ) {}
}
```

When event classes are too much, you may also use scalar values—such as strings or enumerations—to define events. The latter is highly recommended for a better experience.

```php app/AircraftLifecycle.php
enum AircraftLifecycle
{
    case REGISTERED;
    case RETIRED;
}
```

## Dispatching events

The {`Tempest\EventBus\EventBus`} interface implements a `dispatch()` method, which you may use to dispatch any event. The event bus may be [injected as a dependency](../1-essentials/01-container) like any other service:

```php app/AircraftService.php
use Tempest\EventBus\EventBus;

final readonly class AircraftService
{
    public function __construct(
        public EventBus $eventBus,
    ) {}

    public function register(Aircraft $aircraft): void
    {
        // …

        $this->eventBus->dispatch(new AircraftRegistered(
            registration: $aircraft->icao_code,
        ));
    }
}
```

Alternatively, Tempest also provides the `\Tempest\event()` function. It accepts the same arguments as the {`Tempest\EventBus\EventBus`}'s `dispatch()` method, but uses [service location](../1-essentials/01-container#injected-properties) under the hood to access the event bus.

## Handling events

Events are only useful if they are listened for. In Tempest, this is done by calling the `listen()` method on the {b`Tempest\EventBus\EventBus`} instance, or by using the {b`#[Tempest\EventBus\EventHandler]`} attribute.

### Global handlers

Attribute-based event handling is most useful when events should be listened to application-wide. In other words, this is the option you should adopt when the associated event must be acted on every time it is dispatched.

```php app/AircraftObserver.php
final readonly class AircraftObserver
{
    #[EventHandler]
    public function onAircraftRegistered(AircraftRegistered $event): void
    {
        // …
    }
}
```

### Local handlers

When an event is only meant to be listened for in a specific situation, it is better to register it only when relevant. Such a situation could be, for instance, a [console command](../3-console/01-introduction) that needs logging when an event is dispatched.

```php app/SyncUsersCommand.php
final readonly class SyncUsersCommand
{
    public function __construct(
      private readonly Console $console,
      private readonly UserService $userService,
      private readonly EventBus $eventBus,
    ) {}

    #[ConsoleCommand('users:sync')]
    public function __invoke(AircraftRegistered $event): void
    {
        $this->console->header('Synchronizing users');

        // Listen for the UserSynced to write to the console when it happens
        $this->eventBus->listen(function (UserSynced $event) {
            $this->console->keyValue($event->fullName, 'SYNCED');
        });

        // Call external code that dispatches the UserSynced event
        $this->userService->synchronize();
    }
}
```

## Event middleware

When an event is dispatched, it is sent to the event bus, which then forwards it to all registered handlers. Similar to web requests and console commands, the event bus supports middleware.

Event bus middleware can be used for various purposes, such as logging specific events, adding metadata, or performing other pre—or post-processing tasks. These middleware are defined as classes that implement the {`Tempest\EventBus\EventBusMiddleware`} interface.

```php app/EventLoggerMiddleware.php
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final readonly class EventLoggerMiddleware implements EventBusMiddleware
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void
    {
        $next($event);

        if ($event instanceof ShouldBeLogged) {
            $this->logger->info($event->getLogMessage());
        }
    }
}
```

### Middleware priority

All event bus middleware classes get sorted based on their priority. By default, each middleware gets the "normal" priority, but you can override it using the `#[Priority]` attribute:

```php
use Tempest\Support\Priority;

#[Priority(Priority::HIGH)]
final readonly class EventLoggerMiddleware implements EventBusMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. You can however use one of the built-in `Priority` constants: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

### Middleware discovery

Global event bus middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by adding the `#[SkipDiscovery]` attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class EventLoggerMiddleware implements EventBusMiddleware
{ /* … */ }
```

## Stopping event propagation

In rare cases you might want an event only to be handled by a single handler. You can use the {b`Tempest\EventBus\StopsPropagation`} attribute on both events and event handlers to achieve this:

```php
use Tempest\EventBus\StopsPropagation;

#[StopsPropagation]
final class MyEvent {}
```

```php
use Tempest\EventBus\StopsPropagation;
use Tempest\EventBus\EventHandler;

final class MyHandler 
{   
    #[EventHandler]
    #[StopsPropagation]
    public function handle(OtherEvent $event): void
    {
        // …
    }
}
```

## Built-in framework events

Tempest includes a few built-in events that are primarily used internally. While most applications won’t need them, you are free to listen to them if desired.

Most notably, the {`\Tempest\Core\KernelEvent`} enumeration defines the `BOOTED` and `SHUTDOWN` events, which are dispatched when the framework has [finished bootstrapping](../4-internals/01-bootstrap) and right before the process is exited, respectively.

Other events include migration-related ones, such as {b`Tempest\Database\Migrations\MigrationMigrated`}, {b`Tempest\Database\Migrations\MigrationRolledBack`}, {b`Tempest\Database\Migrations\MigrationFailed`} and {b`Tempest\Database\Migrations\MigrationValidationFailed`}.

## Testing

By extending {b`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the event bus testing utilities through the `eventBus` property.

These utilities include a way to replace the event bus with a testing implementation, as well as a few assertion methods to ensure that events have been dispatched or are being listened to.

```php
// Record dispatched events for assertion
$this->eventBus->recordEventDispatches();

// Prevents events from being handled
$this->eventBus->preventEventHandling();

// Assert that an event has been dispatched
$this->eventBus->assertDispatched(AircraftRegistered::class);

// Assert that an event has been dispatched multiple times
$this->eventBus->assertDispatched(AircraftRegistered::class, count: 2);

// Assert that an event has been dispatched,
// and make custom assertions on the event object
$this->eventBus->assertDispatched(
    event: AircraftRegistered::class,
    callback: fn (AircraftRegistered $event) => $event->registration === 'LX-JFA'
);

// Assert that an event has not been dispatched
$this->eventBus->assertNotDispatched(AircraftRegistered::class);

// Assert that an event has an attached handler
$this->eventBus->assertListeningTo(AircraftRegistered::class);
```

### Recording event dispatches

When testing code that dispatches events, you may want to prevent Tempest from handling them. This can be useful when the event’s handlers are tested separately, or when the side-effects of these handlers are not desired for this test case.

To disable event handling, the event bus instance must be replaced with a testing implementation in the container. This is achieved by calling the `preventEventHandling()` method on the `eventBus` property.

```php
$this->eventBus->preventEventHandling();
```

If you want to be able to make assertions while still allowing events to be dispatched, you may instead call the `recordEventDispatches()` method.

```php
$this->eventBus->recordEventDispatches();
```

### Testing a method-based handler

When handlers are registered as methods, instead of dispatching the corresponding event to test the handler logic, you may simply call the method to test it in isolation.

As an example, the following class contains an handler for the `AircraftRegistered` event:

```php app/AircraftObserver.php
final readonly class AircraftObserver
{
    #[EventHandler]
    public function onAircraftRegistered(AircraftRegistered $event): void
    {
        // …
    }
}
```

This handler may be tested by resolving the service class from the container, and calling the method with an instance of the event created for this purpose.

```php app/AircraftObserverTest.php
// Prevent events from being handled while allowing assertions
$this->eventBus->preventEventHandling();

// Resolve the service class
$observer = $this->container->get(AircraftObserver::class);

// Call the event handler
$observer->onAircraftRegistered(new AircraftRegistered(
    registration: 'LX-JFA',
));

// Assert that a mail has been sent, that the database contains something…
```


---
title: Logging
description: "Learn how to use Tempest's logging features to monitor and debug your application."
---

## Overview

Tempest provides a logging implementation built on top of [Monolog](https://github.com/Seldaek/monolog) that follows PSR-3 and the [RFC 5424 specification](https://datatracker.ietf.org/doc/html/rfc5424). This gives you access to eight standard log levels and the ability to send log messages to multiple destinations simultaneously.

The system supports file logging, Slack integration, system logs, and custom channels. You can configure different loggers for different parts of your application using Tempest's [tagged singletons](../1-essentials/05-container.md#tagged-singletons) feature.

## Writing logs

To start logging messages, you may inject the {b`Tempest\Log\Logger`} interface in any class. By default, log messages will be written to a daily rotating log file stored in `.tempest/logs`. This may be customized by providing a different [logging configuration](#configuration).

```php app/Services/UserService.php
use Tempest\Log\Logger;

final readonly class UserService
{
    public function __construct(
        private Logger $logger,
    ) {}
}
```

Tempest supports all eight levels described in the [RFC 5424](https://tools.ietf.org/html/rfc5424) specification. It is possible to configure channels to only log messages at or above a certain level.

```php
$logger->emergency('System is unusable');
$logger->alert('Action required immediately');
$logger->critical('Important, unexpected error');
$logger->error('Runtime error that should be monitored');
$logger->warning('Exceptional occurrence that is not an error');
$logger->notice('Uncommon event');
$logger->info('Miscellaneous event');
$logger->debug('Detailed debug information');
```

### Providing context

All log methods accept an optional context array for additional information. This data is formatted as JSON and included with your log message:

```php
$logger->error('Order processing failed', context: [
    'user_id' => $order->userId,
    'order_id' => $order->id,
    'total_amount' => $order->total,
    'payment_method' => $order->paymentMethod,
    'error_code' => $exception->getCode(),
    'error_message' => $exception->getMessage(),
]);
```

## Configuration

By default, Tempest uses a daily rotating log configuration that creates a new log file each day and retains up to 31 files:

```php config/logging.config.php
use Tempest\Log\Config\DailyLogConfig;
use Tempest;

return new DailyLogConfig(
    path: Tempest\internal_storage_path('logs', 'tempest.log'),
    maxFiles: Tempest\env('LOG_MAX_FILES', default: 31)
);
```

To configure a different logging channel, you may create a `logging.config.php` file anywhere and return one of the [available configuration classes](#available-configurations-and-channels).

### Specifying a minimum log level

Every configuration class and log channel accept a `minimumLogLevel` property, which defines the lowest severity level that will be logged. Messages below this level will be ignored.

```php config/logging.config.php
use Tempest\Log\Config\MultipleChannelsLogConfig;
use Tempest\Log\Channels\DailyLogChannel;
use Tempest\Log\Channels\SlackLogChannel;
use Tempest;

return new MultipleChannelsLogConfig(
    channels: [
        new DailyLogChannel(
            path: Tempest\internal_storage_path('logs', 'tempest.log'),
            maxFiles: Tempest\env('LOG_MAX_FILES', default: 31),
            minimumLogLevel: LogLevel::DEBUG,
        ),
        new SlackLogChannel(
            webhookUrl: Tempest\env('SLACK_LOGGING_WEBHOOK_URL'),
            channelId: '#alerts',
            minimumLogLevel: LogLevel::CRITICAL,
        ),
    ],
    prefix: null,
);
```

### Using multiple loggers

In situations where you would like to log different types of information to different places, you may create multiple tagged configurations to create separate loggers for different purposes.

For instance, you could have a logger dedicated to critical alerts, while each of your application's module have its own logger:

```php src/Monitoring/logging.config.php
use Tempest\Log\Config\SlackLogConfig;
use Modules\Monitoring\Logging;
use Tempest;

return new SlackLogConfig(
    webhookUrl: Tempest\env('SLACK_LOGGING_WEBHOOK_URL'),
    channelId: '#alerts',
    minimumLogLevel: LogLevel::CRITICAL,
    tag: Logging::SLACK,
);
```

```php src/Orders/logging.config.php
use Tempest\Log\Config\DailyLogConfig;
use Modules\Monitoring\Logging;
use Tempest;

return new DailyLogConfig(
    path: Tempest\internal_storage_path('logs', 'orders.log'),
    tag: Logging::ORDERS,
);
```

Using this approach, you can inject the appropriate logger using [tagged singletons](../1-essentials/05-container.md#tagged-singletons). This gives you the flexibility to customize logging behavior in different parts of your application.

```php src/Orders/ProcessOrder.php
use Tempest\Log\Logger;

final readonly class ProcessOrder
{
    public function __construct(
        #[Tag(Logging::ORDERS)]
        private Logger $logger,
    ) {}

    public function __invoke(Order $order): void
    {
        $this->logger->info('Processing new order', ['order' => $order]);
        
        // ...
    }
}
```

### Available configurations and channels

Tempest provides a few log channels that correspond to common logging needs:

- {b`Tempest\Log\Channels\AppendLogChannel`} — append all messages to a single file without rotation,
- {b`Tempest\Log\Channels\DailyLogChannel`} — create a new file each day and remove old files automatically,
- {b`Tempest\Log\Channels\WeeklyLogChannel`} — create a new file each week and remove old files automatically,
- {b`Tempest\Log\Channels\SlackLogChannel`} — send messages to a Slack channel via webhook,
- {b`Tempest\Log\Channels\SysLogChannel`} — write messages to the system log.

As a convenient abstraction, a configuration class for each channel is provided:

- {b`Tempest\Log\Config\SimpleLogConfig`}
- {b`Tempest\Log\Config\DailyLogConfig`}
- {b`Tempest\Log\Config\WeeklyLogConfig`}
- {b`Tempest\Log\Config\SlackLogConfig`}
- {b`Tempest\Log\Config\SysLogConfig`}

These configuration classes also accept a `channels` property, which allows for providing multiple channels for a single logger. Alternatively, you may use the {b`Tempest\Log\Config\MultipleChannelsLogConfig`} configuration class to achieve the same result more explicitly.

## Debugging

Tempest includes several global functions for debugging. Typically, these functions are for quick debugging and should not be committed to production.

- `ll()` — writes values to the debug log without displaying them,
- `lw()` (also `dump()`) — logs values and displays them,
- `ld()` (also `dd()`) — logs values, displays them, and stops execution,
- `le()` — logs values and emits an {b`Tempest\Debug\ItemsDebugged`} event.

### Tailing debug logs

Debug logs are written with console formatting, so they can be tailed with syntax highlighting. You may use `./tempest tail:debug` to monitor the debug log in real time.

:::warning
By default, debug logs are cleared every time the `tail:debug` command is run. If you want to keep previous log entries, you may pass the `--no-clear` flag.
:::

### Configuring the debug log

By default, the debug log is written to `.tempest/debug.log`. This is configurable by creating a `debug.config.php` file that returns a {b`Tempest\Debug\DebugConfig`} with a different `path`:

```php config/debug.config.php
use Tempest\Debug\DebugConfig;
use Tempest;

return new DebugConfig(
    logPath: Tempest\internal_storage_path('logs', 'debug.log')
);
```


---
title: Command bus
keywords: "Experimental"
---

Tempest comes with a built-in command bus, which can be used to dispatch a command to its handler (synchronous or asynchronous). A command bus offers multiple advantages over a more direct approach to modelling processes: commands and their handlers can easily be tested in isolation, they are simple to serialize, and similar to the eventbus, the command bus also supports middleware.

## Commands and handlers

Commands themselves are simple data classes. They don't have to implement anything:

```php
// app/CreateUser.php

final readonly class CreateUser
{
    public function __construct(
        public string $name,
        public string $email,
        public string $passwordHash,
    ) {}
}
```

Just like controller actions and console commands, command handlers are discovered automatically: every method tagged with `#[CommandHandler]` will be registered as one. Tempest knows which command a method handles by looking at the type of the first parameter:

```php
// app/UserHandlers.php

use Tempest\CommandBus\CommandHandler;

final class UserHandlers
{
    #[CommandHandler]
    public function handleCreateUser(CreateUser $createUser): void
    {
        User::create(
            name: $createUser->name,
            email: $createUser->email,
            password: $createUser->passwordHash,
        );

        // Send mail…
    }
}
```

Note that handler method names can be anything: invokable methods, `handleCreateUser()`, `handle()`, `whateverYouWant()`, …

Dispatching a command can be done with the `command()` function:

```php
use function Tempest\command;

command(new CreateUser($name));
```

Alternatively to using the `command()` function, you can inject the `CommandBus`, and dispatch commands like so:

```php
// app/UserController.php

use Tempest\CommandBus\CommandBus;

final readonly class UserController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    public function create(): Response
    {
        // …

        $this->commandBus->dispatch(new CreateUser($name));
    }
}
```

## Async commands

:::warning
The asynchronous commands implementation of Tempest is currently experimental. Although you can use it, please note that it is not covered by our backwards compatibility promise.
:::

A common use case for Tempest's command bus is to dispatch asynchronous commands: commands that are executed by their handler in the background, outside the main PHP process. Making a command asynchronous is done by adding the `#[Async]` to your command object:

```php
// app/SendMail.php

use Tempest\CommandBus\Async;

#[Async]
final readonly class SendMail
{
    public function __construct(
        public string $to,
        public string $body,
    ) {}
}
```

Besides adding the `#[Async]` attribute, the flow remains exactly the same as if you were dispatching synchronous commands:

```php
use function Tempest\command;

command(new SendMail(
    to: 'brendt@stitcher.io',
    body: 'Hello!'
));
```

In order to _run_ an asynchronous command, you'll have to run the `tempest command:monitor` console command. This is a long-running process, and you will need to set it up as a daemon on your production server. As long as `command:monitor` is running, async commands will be handled in the background.

Note that async command handling is still an early feature, and will receive many improvements over time.

## Idempotent commands

Commands that should not be processed more than once—such as payment processing or invoice imports—can be marked with {b`Tempest\Idempotency\Attributes\Idempotent`}. The attribute can be placed on the command class or on the handler method. Duplicate dispatches with the same payload are silently skipped.

```php
// app/ImportInvoicesCommand.php

use Tempest\Idempotency\Attributes\Idempotent;

#[Idempotent]
final readonly class ImportInvoicesCommand
{
    public function __construct(
        public string $vendorId,
        public string $month,
    ) {}
}
```

Alternatively, the attribute can be placed on the handler method instead:

```php
// app/ImportInvoicesHandler.php

use Tempest\CommandBus\CommandHandler;
use Tempest\Idempotency\Attributes\Idempotent;

final class ImportInvoicesHandler
{
    #[Idempotent]
    #[CommandHandler]
    public function handle(ImportInvoicesCommand $command): void { /* … */ }
}
```

By default, the deduplication key is derived from the command's properties. Two commands with identical property values are considered duplicates. For explicit control over the key, implement the {b`Tempest\Idempotency\HasIdempotencyKey`} interface:

```php
// app/ProcessPaymentCommand.php

use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\HasIdempotencyKey;

#[Idempotent]
final readonly class ProcessPaymentCommand implements HasIdempotencyKey
{
    public function __construct(
        public string $paymentId,
        public int $amount,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->paymentId;
    }
}
```

When using explicit keys, the payload fingerprint is still verified. Dispatching the same key with a different payload throws {b`Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed`}.

:::info
Read the full [idempotency documentation](./19-idempotency.md) for details on configuration, TTL overrides, custom stores, and HTTP route idempotency.
:::

## Command bus middleware

Whenever commands are dispatched, they are passed to the command bus, which will pass the command along to each of its handlers. Similar to web requests and console commands, this command bus supports middleware. Command bus middleware can be used to, for example, do logging for specific commands, add metadata to commands, or anything else. Command bus middleware are classes that implement the `CommandBusMiddleware` interface, and look like this:

```php
// app/MyCommandBusMiddleware.php

use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\CommandBus\CommandBusMiddlewareCallable;

class MyCommandBusMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $next($command);

        if ($command instanceof ShouldBeLogged) {
            $this->logger->info($command->getLogMessage());
        }
    }
}
```

### Middleware priority

All command bus middleware classes get sorted based on their priority. By default, each middleware gets the "normal" priority, but you can override it using the `#[Priority]` attribute:

```php
use Tempest\Support\Priority;

#[Priority(Priority::HIGH)]
final readonly class MyCommandBusMiddleware implements CommandBusMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. You can however use one of the built-in `Priority` constants: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

### Middleware discovery

Global command bus middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by adding the `#[SkipDiscovery]` attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class MyCommandBusMiddleware implements CommandBusMiddleware
{ /* … */ }
```


---
title: Localization
description: "Tempest provides convenient utilities for localizing applications, including a translator built on the MessageFormat 2.0 specification."
---

## Overview

Tempest provides a simple {b`Tempest\Intl\Translator`} interface for localizing applications. It allows you to translate messages into different languages and formats them according to the current or specified locale.

The translator implements the [MessageFormat 2.0](https://messageformat.unicode.org/) specification, which provides a flexible syntax for defining translation messages. This specification is [maintained by the Unicode project](https://github.com/unicode-org/message-format-wg) and is widely used in internationalization libraries.

## Translating messages

To translate messages, you may [inject](../1-essentials/05-container.md) the {`Tempest\Intl\Translator`} interface and use its `translate()` method. If the translation message accepts variables, you may pass them as named parameters.

```php
$translator->translate('cart.expire_at', expire_at: $expiration);
// Your cart is valid until 1:30 PM
```

To translate a message in a specific locale, you may use the `translateForLocale()` instead and provide the {b`Tempest\Intl\Locale`} as the first parameter.

```php
$translator->translateForLocale(Locale::FRENCH, 'cart.expire_at', expire_at: $expiration);
// Votre panier expire à 12h30
```

Alternatively, you may use the `translate` or the `translate_for_locale` function in the `Tempest\Intl` namespace.

### Configuring the locale

The current locale is stored in the `currentLocale` property of the {`Tempest\Intl\IntlConfig`} [configuration object](../1-essentials/06-configuration.md). You may configure another default locale by creating a dedicated configuration file:

```php intl.config.php
return new IntlConfig(
    currentLocale: Locale::FRENCH,
    fallbackLocale: Locale::ENGLISH,
);
```

By default, Tempest uses the [`intl.default_locale`](https://www.php.net/manual/en/locale.getdefault.php) ini value for the current locale.

### Changing the locale

You may update the current locale at any time by mutating the {b`Tempest\Intl\IntlConfig`} configuration object. For instance, this could be done in a [middleware](../1-essentials/01-routing.md#route-middleware):

```php
final readonly class SetLocaleMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
        private IntlConfig $intlConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->intlConfig->currentLocale = $this->authenticator
            ->currentUser()
            ->preferredLocale;

        return $next($request);
    }
}
```

## Defining translation messages

Translation messages are usually stored in translation files. Tempest automatically [discovers](../1-essentials/05-discovery.md) YAML and JSON translation files that use the `<name>.<locale>.{yaml,json}` naming format, where `<name>` may be any string, and `<locale>` must be an [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes) language code.

For instance, you may store translation files in a `lang` directory:

```
src/
└── lang/
    ├── messages.fr.yaml
    └── messages.en.yaml
```

Alternatively, you may call the `add()` method on a {`Tempest\Intl\Catalog\Catalog`} instance to add a translation message at runtime.

```php
$catalog->add(Locale::FRENCH, 'order.continue_shopping', 'Continuer vos achats');
```

### Message syntax

Tempest implements the [MessageFormat 2.0](https://messageformat.unicode.org/) specification, which provides a flexible syntax for defining translation messages. The syntax allows for variables, [pluralization](#pluralization), and [custom formatting functions](#custom-formatting-functions).

Since most translation messages are multiline, YAML is the recommended format for defining them. Here is an example of a translation message that uses a [variable](https://messageformat.unicode.org/docs/reference/variables/), a [function](https://messageformat.unicode.org/docs/reference/functions/) and a function [parameter](https://messageformat.unicode.org/docs/reference/functions/#options):

```yaml messages.en.yaml
today:
  Today is {$today :datetime pattern=|yyyy/MM/dd|}
```

:::info
You may learn more about this syntax in the [MessageFormat documentation](https://messageformat.unicode.org/docs/translators/).
:::

### Pluralization

Pluralizing messages may be done using [matchers](https://messageformat.unicode.org/docs/reference/matchers/) and the `number` function. This syntax supports languages that have more than two plural categories. For instance, you may translate this sentence in Polish:

```php messages.pl.yaml
cart:
  items_count:
    .input {$count :number}
    .match $count
      one   {{Masz {$count} przedmiot.}}
      few   {{Masz {$count} przedmioty.}}
      many  {{Masz {$count} przedmiotów.}}
      other {{Masz {$count} przedmiotów.}}
```

For more complex translation messages, you may also use multiple variables in a matcher. In this example, we use a `type` and a `count` variable in the same matcher.

```php messages.pl.yaml
cart:
  items_by_type_count:
    .input {$type :string}
    .input {$count :number}
    .match $type $count
      product one   {{Masz {$count} produkt w koszyku.}}
      product few   {{Masz {$count} produkty w koszyku.}}
      product many  {{Masz {$count} produktów w koszyku.}}
      product *     {{Masz {$count} produktów w koszyku.}}
      service one   {{Masz {$count} usługę w koszyku.}}
      service few   {{Masz {$count} usługi w koszyku.}}
      service many  {{Masz {$count} usług w koszyku.}}
      service *     {{Masz {$count} usług w koszyku.}}
      *       one   {{Masz {$count} element w koszyku.}}
      *       few   {{Masz {$count} elementy w koszyku.}}
      *       many  {{Masz {$count} elementów w koszyku.}}
      *       *     {{Masz {$count} elementów w koszyku.}}
```

### Using markup

Markup may be added to translation messages using a [dedicated syntax](https://messageformat.unicode.org/docs/reference/markup/) defined in the MessageFormat specification. Tempest provides a markup implementation that renders HTML tags and Iconify icons.

```yaml
bold_text: "This is {#strong}bold{/strong}."
ui:
  open_menu: "{#icon-tabler-menu/} Open menu"
```

It is possible to implement your own markup by implementing the {b`Tempest\Intl\MessageFormat\MarkupFormatter`} or {b`Tempest\Intl\MessageFormat\StandaloneMarkupFormatter`} interfaces. Classes implementing these interfaces are automatically discovered by Tempest.

### Custom formatting functions

The [MessageFormat 2.0](https://messageformat.unicode.org/) specification allows for defining custom formatting functions that can be used in translation messages. By default, Tempest provides formatting functions for strings, numbers and dates.

You may define a custom formatting function by implementing the {b`Tempest\Intl\MessageFormat\FormattingFunction`} interface. For instance, the function for formatting dates is implemented as follows:

```php
final class DateTimeFunction implements FormattingFunction
{
    public string $name = 'datetime';

    public function format(mixed $value, array $parameters): FormattedValue
    {
        $datetime = DateTime::parse($value);
        $formatted = $datetime->format(Arr\get_by_key($parameters, 'pattern'));

        return new FormattedValue($value, $formatted);
    }
}
```


---
title: Scheduling
description: 'Tempest provides a modern and convenient way of scheduling tasks, which can be any class method, even existing console commands.'
---

## Overview

Dealing with repeating, scheduled tasks is as simple as adding the {`#[Tempest\Console\Schedule]`} attribute to any class method. As with console commands, [discovery](../1-essentials/05-discovery.md) takes care of finding these methods and registering them.

## Using the scheduler

To run tasks on your server, a single cron task is required. This task should call the `schedule:run` command, which will evaluate which scheduled task should be run at the current time.

```
0 * * * * user /path/to/{*tempest schedule:run*}
```

## Defining schedules

Any method using the `{php}#[Schedule]` attribute will be run by the scheduler. As with everything Tempest, these methods are discovered automatically.

```php app/ScheduledTasks.php
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final readonly class ScheduledTasks
{
    #[Schedule(Every::HOUR)]
    public function updateSlackChannels(): void
    {
        // …
    }
}
```

For most common scheduling use-cases, the {b`Tempest\Console\Scheduler\Every`} enumeration can be used. In case you need more fine-grained control, you can pass in an {b`Tempest\Console\Scheduler\Interval`} object instead:

```php
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Interval;

#[Schedule(new Interval(hours: 2, minutes: 30))]
public function updateSlackChannels(): void
{
    // …
}
```

Note that scheduled task don't have to be console commands, but they can be both. This is handy when you need a task to be run on a schedule, but also want to be able to run it manually.

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;

#[Schedule(Every::HOUR)]
#[ConsoleCommand('slack:update-channels')]
public function updateSlackChannels(): void
{
    // …
}
```


---
title: HTTP client
description: ""
hidden: true
---


---
title: Static pages
description: "When rendering pages with no dynamic component, booting the whole framework is not necessary. Tempest provides a way to generate static pages that can be rendered directly from your web server."
---

## Overview

When a controller action is tagged with {b`#[Tempest\Router\StaticPage]`}, it can be compiled by Tempest as a static HTML page. These pages can then directly be served directly through your web server.

```php app/Marketing/FrontPageController.php
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class FrontPageController
{
    #[StaticPage]
    #[Get('/')]
    public function frontpage(): View
    {
        return view('./front-page');
    }
}
```

Compiling and cleaning up static pages is done using the `{txt}static:generate` and `{txt}static:clean` commands, respectively. Note that the latter removes all HTML files and empty directories in your `/public` directory.

```sh
{:hl-comment:./tempest:} static:generate
{:hl-comment:./tempest:} static:clean
```

## Data providers

Since most pages require some form of dynamic data, static pages can be assigned a data provider, which will generate multiple pages for one controller action.

Let's take a look at the controller action for this very website:

```php app/Documentation/ChapterController.php
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

final readonly class ChapterController
{
    #[StaticPage(ChapterDataProvider::class)]
    #[Get('/{category}/{slug}')]
    public function show(string $category, string $slug, ChapterRepository $chapters): View
    {
        return new ChapterView(
            repository: $chapters,
            current: $chapters->find($category, $slug),
        );
    }
}
```

In this case, the {b`#[Tempest\Router\StaticPage]`} attribute gets a reference to the `ChapterDataProvider`, which implements the {`\Tempest\Router\DataProvider`} interface:

```php app/Documentation/ChapterDataProvider.php
use Tempest\Router\DataProvider;

final readonly class ChapterDataProvider implements DataProvider
{
    public function provide(): Generator
    {
        // …
    }
}
```

A data provider's goal is to generate multiple pages for one controller action. It does so by yielding an array of controller action parameters for every page that needs to be generated. In case of the documentation chapter controller, the action needs a `$category` and `$slug`, as well as the chapter repository.

That repository is injected by the container, so we don't need to worry about it here. What we do need to provide is a category and slug for each page we want to generate.

In other words: we want to generate a page for every documentation chapter. We can use the `ChapterRepository` to get a list of all available chapters. Eventually, our data provider looks like this:

```php app/Documentation/ChapterDataProvider.php
use Tempest\Router\DataProvider;

final readonly class ChapterDataProvider implements DataProvider
{
    public function __construct(
        private ChapterRepository $chapters
    ) {}

    public function provide(): Generator
    {
        foreach ($this->chapters->all() as $chapter) {
            // Yield an array of parameters that should be passed to the controller action,
            yield [
                'category' => $chapter->category,
                'slug' => $chapter->slug,
            ];
        }
    }
}
```

The only thing left to do is to generate the static pages:

```console
<dim>./tempest static:generate</dim>

/framework/01-getting-started <dim>.............</dim> <em>/public/framework/01-getting-started/index.html</em>
/framework/02-the-container <dim>.................</dim> <em>/public/framework/02-the-container/index.html</em>
/framework/03-controllers <dim>.....................</dim> <em>/public/framework/03-controllers/index.html</em>
/framework/04-views <dim>.................................</dim> <em>/public/framework/04-views/index.html</em>
/framework/05-models <dim>...............................</dim> <em>/public/framework/05-models/index.html</em>
<comment>…</comment>
```

## Crawling for dead links

Optionally, you can instruct the static generate to crawl your pages to scan for dead links. This is done by passing the `--crawl` option to the `static:generate` command:

```console
<dim>./tempest static:generate --crawl</dim>
```

By default, the crawler will only check for internal dead links. If you want to check for external links as well, you can pass the `--external` option:

```console
<dim>./tempest static:generate --crawl --external</dim>
```

## Production

Static pages are generated in the `/public` directory, as `index.html` files. Most web servers will automatically serve these static pages for you without any additional setup.

Note that static pages are meant to be generated as part of your deployment script. That means the `{txt}./tempest static:generate` command should be in your deployment pipeline.


---
title: Exception handling
description: "Learn how exception handling works, how to manually report exceptions, and how to customize exception rendering for HTTP responses."
---

## Overview

Tempest comes with an exception handler that provides a simple way to report exceptions and render error responses.

Custom [exception reporters](#writing-exception-reporters) can be created by implementing the {b`Tempest\Core\Exceptions\ExceptionReporter`} interface, and custom [exception renderers](#customizing-exception-rendering) can be created by implementing {b`Tempest\Router\Exceptions\ExceptionRenderer`}. These classes are automatically [discovered](../1-essentials/05-discovery.md) and do not require manual registration.

## Processing exceptions

Exceptions can be reported without throwing them using the `process()` method of the {b`Tempest\Core\Exceptions\ExceptionProcessor`} interface. This allows putting exceptions through the reporting process without stopping the application's execution.

```php app/CreateUser.php
use Tempest\Core\Exceptions\ExceptionProcessor;

final readonly class CreateUser
{
    public function __construct(
        private ExceptionProcessor $exceptions
    ) {}

    public function __invoke(): void
    {
        try {
            // Some code that may throw an exception
        } catch (SomethingFailed $somethingFailed) {
            $this->exceptions->process($somethingFailed);
        }
    }
}
```

## Disabling exception logging

The default logging reporter, {b`Tempest\Core\Exceptions\LoggingExceptionReporter`}, is automatically added to the list of reporters. To disable it, create a {b`Tempest\Core\Exceptions\ExceptionsConfig`} [configuration file](../1-essentials/06-configuration.md#configuration-files) and set `logging` to `false`:

```php app/exceptions.config.php
use Tempest\Core\Exceptions\ExceptionsConfig;

return new ExceptionsConfig(
    logging: false,
);
```

## Adding context to exceptions

Exceptions can provide additional information for logging by implementing the {`Tempest\Core\ProvidesContext`} interface. The context data becomes available to exception processors.

```php
use Tempest\Core\ProvidesContext;

final readonly class UserWasNotFound extends Exception implements ProvidesContext
{
    public function __construct(private string $userId)
    {
        parent::__construct("User {$userId} not found.");
    }

    public function context(): array
    {
        return [
            'user_id' => $this->userId,
        ];
    }
}
```

## Writing exception reporters

Exception reporters allow defining custom reporting logic for exceptions, such as sending them to external error tracking services like Sentry or logging them to specific destinations.

To create a custom reporter, implement the {b`Tempest\Core\Exceptions\ExceptionReporter`} interface and define a `report()` method:

```php app/SentryExceptionReporter.php
use Tempest\Core\Exceptions\ExceptionReporter;
use Throwable;

final class SentryExceptionReporter implements ExceptionReporter
{
    public function __construct(
        private SentryClient $sentry,
    ) {}

    public function report(Throwable $throwable): void
    {
        $this->sentry->captureException($throwable);
    }
}
```

Exception reporters are automatically [discovered](../4-internals/02-discovery.md) and registered. All registered reporters are invoked whenever an exception is processed, allowing multiple reporters to handle the same exception.

For example, the default logging reporter logs to a file, while the reporter above sends the error to Sentry.

If an exception reporter throws an exception during execution, it is silently caught to prevent infinite loops. This ensures that a failing reporter doesn't prevent other reporters from running.

### Accessing exception context

Exceptions can implement the {b`Tempest\Core\ProvidesContext`} interface, which reporters can leverage to provide additional context data during reporting:

```php app/SentryExceptionReporter.php
use Tempest\Core\Exceptions\ExceptionReporter;
use Tempest\Core\ProvidesContext;
use Sentry\State\HubInterface as Sentry;
use Sentry\State\Scope;

final class SentryExceptionReporter implements ExceptionReporter
{
    public function __construct(
        private readonly Sentry $sentry,
    ) {}

    public function report(Throwable $throwable): void
    {
        $this->sentry->withScope(function (Scope $scope) use ($throwable) {
            if ($throwable instanceof ProvidesContext) {
                $scope->withContext($throwable->context());
            }

            $scope->captureException($throwable);
        });
    }
}
```

### Conditional reporting

Reporters can implement conditional logic to only report specific exception types or under certain conditions. There is no built-in filtering mechanism; reporters are responsible for determining when to report an exception.

```php app/CriticalErrorReporter.php
use Tempest\Core\Exceptions\ExceptionReporter;
use Throwable;

final class CriticalErrorReporter implements ExceptionReporter
{
    public function __construct(
        private AlertService $alerts,
    ) {}

    public function report(Throwable $throwable): void
    {
        if (! $throwable instanceof CriticalException) {
            return;
        }

        $this->alerts->sendCriticalAlert(
            message: $throwable->getMessage(),
        );
    }
}
```

## Customizing exception rendering

Exception renderers provide control over how exceptions are rendered in HTTP responses. Custom renderers can be used to display specialized error pages for specific exception types, format errors differently based on content type (JSON, HTML, XML), or provide user-friendly error messages for common scenarios like 404 or validation failures.

To create a custom renderer, implement the {b`Tempest\Router\Exceptions\ExceptionRenderer`} interface. It requires a `canRender()` method to determine if the renderer can handle the given exception and request, and a `render()` method to produce the response:

```php app/NotFoundExceptionRenderer.php
use Tempest\Http\ContentType;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\Router\Exceptions\ExceptionRenderer;
use Throwable;

use function Tempest\View\view;

final class NotFoundExceptionRenderer implements ExceptionRenderer
{
    public function canRender(Throwable $throwable, Request $request): bool
    {
        if (! $request->accepts(ContentType::HTML)) {
            return false;
        }

        if (! $throwable instanceof HttpRequestFailed) {
            return false;
        }

        return $throwable->status === Status::NOT_FOUND;
    }

    public function render(Throwable $throwable): Response
    {
        return new NotFound(
            body: view('./404.view.php'),
        );
    }
}
```

:::info
Exception renderers are automatically [discovered](../4-internals/02-discovery.md) and checked in {b`#[Tempest\Support\Priority]`} order.
:::

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from a test case, exception testing utilities may be accessed for making assertions about processed exceptions.

```php
// Allows exceptions to be processed during tests
$this->exceptions->allowProcessing();

// Assert that the exception was processed
$this->exceptions->assertProcessed(UserNotFound::class);

// Assert that the exception was not processed
$this->exceptions->assertNotProcessed(UserNotFound::class);

// Assert that no exceptions were processed
$this->exceptions->assertNothingProcessed();
```

By default, Tempest disables exception processing during tests. It is recommended to unit-test your own {b`Tempest\Core\Exceptions\ExceptionReporter`} implementations.


---
title: 'Date and time'
description: "Tempest provides a complete alternative to the DateTime implementation, with a higher-level API, deeply integrated into the framework."
keywords: ["timezone", "date", "time", "time zone", "carbon"]
---

## Overview

PHP provides multiple date and time implementations. There is [`DateTime`](https://www.php.net/manual/en/class.datetime.php) and [`DateTimeImmutable`](https://www.php.net/manual/en/class.datetimeimmutable.php), based on [`DateTimeInterface`](https://www.php.net/manual/en/class.datetimeinterface.php), as well as [`IntlCalendar`](https://www.php.net/manual/en/class.intlcalendar.php). Unfortunately, those implementation have rough, low-level, awkward APIs, which are not pleasant to work with.

Tempest provides an alternative to [`DateTimeInterface`](https://www.php.net/manual/en/class.datetimeinterface.php), partially based on [`IntlCalendar`](https://www.php.net/manual/en/class.intlcalendar.php). This implementation provides a better API with a more consistent interface. It was initially created by {x:azjezz} for the [PSL](https://github.com/azjezz/psl), and was ported to Tempest so it could be deeply integrated.

:::info
You're not required to use Tempest's DateTime implementation, and may as well use PHP's native datetime, Carbon, or any other. If you rely on third-party libraries like Carbon, you should read about [global casters and serializers](/2.x/features/mapper#registering-casters-and-serializers-globally) as well to ensure model support.  
:::

## Creating date instances

The {`Tempest\DateTime\DateTime`} class provides a `DateTime::parse()` method to create a date from a string, a timestamp, or another datetime instance. This is the most flexible way to create a date instance.

```php
DateTime::parse('2025-09-19 02:00:00');
```

Alternatively, if you know the format of the date string you are working with, you may use the `DateTime::fromPattern()`. It accepts a standard [ICU format](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax).

Finally, for more specific use cases, the `DateTime::fromString()` method may be used to create a date from a localized date and time string.

### For the current date and time

The recommended approach for getting the current time is by calling the `now()` method on the {`Tempest\Clock\Clock`} interface, [which may be injected as a dependency](#clock-interface) in any class.

However, for convenience, you may also create a {b`Tempest\DateTime\DateTime`} instance for the current time using the `DateTime::now()` method or the `Tempest\now()` function.

```php
$now = DateTime::now();
```

### From a known format pattern

If you know the format of the date string you are working with, you should prefer using the `DateTime::fromPattern()` method. It accepts a standard [ICU format](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax).

```php
DateTime::fromPattern('2025-09-19 02:00', pattern: 'yyyy-MM-dd HH:mm');
```

## Manipulating dates

The {b`Tempest\DateTime\DateTime`} class provides multiple methods to manipulate dates. You may add or subtract time from a date using the `plus()` and `minus()` methods, which accept a {b`Tempest\DateTime\Duration`} instance.

For convenience, more specific manipulation methods are also provided.

```php
// Adding a set duration
$date->plus(Duration::seconds(30));

// Using convenience methods
$date->plusHour();
$date->plusMinutes(30);
$date->minusDay();
$date->endOfDay();
```

### Converting timezones

All datetime creation methods accept a `timezone` parameter. This parameter accepts an instance of the {b`Tempest\DateTime\Timezone`} enumeration. When not provided, the default timezone, provided by [`date.timezone`](https://www.php.net/manual/en/datetime.configuration.php#ini.date.timezone), will be used.

You may convert the timezone of an existing instance by calling the `convertToTimezone()` method:

```php
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timezone;

$date = DateTime::now();
$date->convertToTimezone(Timezone::EUROPE_PARIS);
```

### Computing a duration

By calling the `between()` method on a date instance, you may compute the duration between this date and a second one. This method returns a {b`Tempest\DateTime\Duration`} instance.

```php
use Tempest\DateTime\DateTime;

$date1 = DateTime::now();
$date2 = DateTime::parse('2025-09-19 02:00:00');
$duration = $date1->between($date2);
```

### Comparing dates

The {b`Tempest\DateTime\DateTime`} instance provides multiple methods to compare dates against each other, or against the current time. For instance, you may check if a date is before or after another date using the `isBefore()` and `isAfter()` methods, respectively.

```php
// Check if a date is before another date (exclusive - does not include the comparison date)
$date->isBefore($other);

// Check if a date is before or at another date (inclusive - includes the comparison date)
$date->isBeforeOrAt($other);

// Check if a date between two other dates, inclusively
$date->betweenTimeInclusive($otherDate1, $otherDate2);

// Check if a date is in the future
$date->isFuture();
```

## Formatting dates

You may format a {b`Tempest\DateTime\DateTime`} instance in a specific format using the `format()` method. This method accepts an optional format string, which is a standard [ICU format](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax), and an optional locale.

```php
use Tempest\DateTime\FormatPattern;
use Tempest\Intl\Locale;

$date->format(); // Jan 7, 2026, 10:30:05 PM
$date->format(pattern: FormatPattern::COOKIE); // Wednesday, 07-Jan-2026 22:30:46 UTC
$date->format(locale: Locale::FRENCH); // 7 janv. 2026, 22:32:12
```

## Clock interface

Tempest provides a {`Tempest\Clock\Clock`} interface which may be [injected as a dependency](../1-essentials/05-container.md#injecting-dependencies) in any class. This is the recommended way of working with time.

```php
final readonly class HomeController
{
    public function __construct(
        private readonly Clock $clock,
    ) {}

    public function __invoke(): View
    {
        return view('./home.view.php', currentTime: $this->clock->now());
    }
}
```

Note that because Tempest has its own {b`Tempest\DateTime\DateTime`} implementation, the {b`Tempest\Clock\Clock`} interface is not compatible with PSR-20. However, you may get a PSR-20 implementation by calling the `toPsrClock()` method.

```php
$psrClock = $clock->toPsrClock();
```

## Testing time

Tempest provides a time-related testing utilities accessible through the `clock` method of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case.

Calling this method replaces the {b`Tempest\Clock\Clock`} instance in the container with a testing one, on which a specific date and time can be defined. {b`Tempest\DateTime\DateTime`} instances created with the `DateTime::now()` method or `Tempest\now()` function will use the date and time specified by the testing clock.

```php
// Create a testing clock
$clock = $this->clock();

// Set a specific date and time
$clock->setNow('2025-09-19 02:00:00');

// Advance time by the specified duration
$clock->sleep(milliseconds: 250);
```


---
title: Processes
description: "Learn how to run synchronous and asynchronous processes, capture their output, and test them."
---

## Overview

Tempest provides a testable wrapper around the [Symfony Process component](https://symfony.com/doc/current/components/process.html), inspired by [Laravel's own wrapper](https://laravel.com/docs/12.x/processes). It allows you to run one or multiple processes synchronously or asynchronously, while being testable and convenient to use.

## Synchronous processes

The {`Tempest\Process\ProcessExecutor`} interface is the entrypoint for invoking processes. It provides a `run()` method to run a process synchronously, and a `start()` method to run it asynchronously. You may access the interface by [injecting it as a dependency](../1-essentials/05-container.md) in your classes.

```php app/Composer.php
use Tempest\Process\ProcessExecutor;

final readonly class Composer
{
    public function __construct(
        private ProcessExecutor $executor
    ) {}

    public function update(): void
    {
        $this->executor->run('composer update');
    }
}
```

The `run()` method returns an instance of {b`Tempest\Process\ProcessResult`}, which contains the output of the process, its exit code, and whether it was successful. You can access these properties to handle the result of the process.

```php app/Composer.php
$result = $this->executor->run('composer update');

$result->successful();
$result->failed();
$result->exitCode;
$result->output;
$result->errorOutput;
```

## Asynchronous processes

To run a process asynchronously, you may use the `start()` method instead. This will return an instance of {b`Tempest\Process\InvokedProcess`}, which you can use to monitor the process.

You may send a signal to a running process using the `signal()` method, or stop it using `stop()`. It is also possible to wait for the process using `wait()`, which accepts a callback to capture the live output of the process.

```php app/Composer.php
$this->executor
    ->start('composer update')
    ->wait(function (OutputChannel $channel, string $output) {
        echo $output;
    });
```

## Process pools

It is possible to execute multiple tasks simultaneously using a process pool. To do so, you may call the `pool()` method on the {`Tempest\Process\ProcessExecutor`}. This returns a {b`Tempest\Process\Pool`} instance, which provides convenient methods for managing the processes.

```php
$pool = $this->executor->pool([
    'composer update',
    'bun install',
]);

$pool->start();
$pool->count();
$pool->forEach(fn (InvokedProcess $process) => /** ... */);
$pool->forEachRunning(fn (InvokedProcess $process) => /** ... */);
$pool->signal(SIGINT);
$pool->stop();
```

Alternatively, if you are only interested in the process outputs, you may use the `concurrently()` method and destructure its results:

```php
[$composer, $bun] = $this->executor->concurrently([
    'composer update',
    'bun install',
]);

echo $composer;
echo $bun;
```

## Testing

Tempest provides a process testing utility accessible through the `process` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. You may learn more about testing in the [dedicated chapter](../1-essentials/07-testing.md).

### Mocking processes

Testing process invokation results involves calling `mockProcessResult()` with the command you want to mock and an optional result. This will simulate the command being run and return the result you specified.

```php
// Mocks `composer up` calls
$this->process->mockProcessResult('composer up');

// Call application code...
// ...

// Assert against executed processes
$this->process->assertCommandRan('composer up');
$this->process->assertRan(function (PendingProcess $process, ProcessResult $result) {
    // ...
});
```

### Describing asynchronous processes

When dealing with asynchronous processes, you may use the `describe()` method to define the expectations of the process. This allows you to specify the command, the expected output and error output, the exit code, and the amount of times the `running` property should return `true`.

```php
$this->process->mockProcessResults([
    'composer up' => $this->process
        ->describe()
        ->iterations(1)
        ->output('Nothing to install, update or remove'),
    'bun install' => $this->process
        ->describe()
        ->iterations(4)
        ->output('Checked 225 installs across 274 packages (no changes) [144.00ms]'),
]);

$this->process->assertCommandRan('composer up', function (ProcessResult $result) {
    $this->assertSame("Nothing to install, update or remove\n", $result->output);
});
```

In the example above, `composer up` and `bun install` are mocked to return the specified output. They both return `0` as their exit code by default. The `running` property of the process that runs `composer up` will return `true` only once, while the one that runs `bun install` will return `true` four times.

### Allowing process execution

By default, to prevent unintended side effects, Tempest does not actually execute processes during tests. Instead, trying to execute non-mocked processes will throw an exception.

If you prefer to allow process execution, you may change this behavior by calling `allowRunningActualProcesses()` in your test case. This will allow all processes to be executed, and you may still perform assertions on them.

```php
$this->process->allowRunningActualProcesses();

// Call application code...

$this->process->assertCommandRan('composer up');
```


---
title: OAuth
description: "Learn how to implement OAuth to authenticate users with many different providers, such as GitHub, Google, Discord, and many others."
keywords: "Experimental"
---

## Overview

Tempest provides the ability to authenticate users with many OAuth providers, such as GitHub, Google, Discord, and many others, using the same interface.

This implementation is built on top of the PHP league's [OAuth client](https://github.com/thephpleague/oauth2-client)—a reliable, battle-tested OAuth 2.0 client library.

## Getting started

Tempest provides an installer to quickly set up OAuth in your project. You can run the installer using the following command:

```sh
./tempest install auth --oauth
```

The installer will:
- Prompt you to select one or more OAuth providers from the available options
- Publish the necessary configuration files and controller stubs
- Optionally add the OAuth credentials to your `.env` and `.env.example` files
- Optionally install the required Composer dependencies for the selected providers

This is the quickest way to get started with OAuth in your Tempest application.

Alternatively, you can manually create a configuration file for your desired OAuth provider.

Tempest provides a [different configuration object for each provider](#available-providers). For instance, if you wish to authenticate users with GitHub, you may create a `github.config.php` file returning an instance of {b`Tempest\Auth\OAuth\Config\GitHubOAuthConfig`}:

```php app/Auth/github.config.php
return new GitHubOAuthConfig(
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [GitHubOAuthController::class, 'callback'],
    scopes: ['user:email'],
);
```

In this example, the GitHub OAuth credentials are specified in the `.env`, so different credentials can be configured depending on the environment.

Once your OAuth provider is configured, you may interact with it by using the {`Tempest\Auth\OAuth\OAuthClient`} interface. This is usually done through [dependency injection](../1-essentials/05-container.md#injecting-dependencies).

## Implementing the OAuth flow

To implement a complete OAuth flow for your application, you will need two routes.

- The first one will redirect the user to the OAuth provider's authorization page,
- The second one, which will be redirected to once the user authorizes your application, will fetch the user's information thanks to the code provided by the OAuth provider.

The {b`Tempest\Auth\OAuth\OAuthClient`} interface has the necessary methods to handle both parts of the flow. The following is an example of a complete OAuth flow, including CSRF protection, creating or updating the user, and authenticating them against the application:

```php app/Auth/DiscordOAuthController.php
use Tempest\Auth\OAuth\OAuthClient;

final readonly class DiscordOAuthController
{
    public function __construct(
        private OAuthClient $oauth,
        private Session $session,
        private Authenticator $authenticator,
    ) {}

    #[Get('/auth/discord')]
    public function redirect(): Redirect
    {
        return $this->oauth->createRedirect(scopes: ['identify']);
    }

    #[Get('/auth/discord/callback')]
    public function callback(Request $request): Redirect
    {
        $user = $this->oauth->authenticate(
            request: $request,
            map: fn (OAuthUser $user): User => query(User::class)->updateOrCreate([
                'discord_id' => $user->id,
            ], [
                'discord_id' => $user->id,
                'username' => $user->nickname,
                'email' => $user->email,
            ])
        );

        return new Redirect('/');
    }
}
```

Of course, this example assumes that the database and an [authenticatable model](../2-features/04-authentication.md#authentication) are configured.

### Working with the OAuth user

When an OAuth flow is completed and you call `fetchUser`, you will receive an {b`Tempest\Auth\OAuth\OAuthUser`} object containing the user's information from the OAuth provider:

```php
$user = $this->oauth->fetchUser($code);

$user->id;         // The unique identifier for the user from the OAuth provider
$user->email;      // The user's email address
$user->name;       // The user's name
$user->nickname;   // The user's nickname/username
$user->avatar;     // The user's avatar URL
$user->provider;   // The OAuth provider name
$user->raw;        // Raw user data from the OAuth provider
```

As seen in the example above, you can use this information to create or update a user in your database, or to authenticate them directly.

## Configuring a provider

Most providers require only a `clientId`, `clientSecret` and `redirectTo`, but some might need other parameters. A typical configuration looks like the following:

```php app/Auth/github.config.php
return new GitHubOAuthConfig(
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [GitHubOAuthController::class, 'callback'],
    scopes: ['user:email'],
);
```

Note that the `redirectTo` accepts a tuple of a controller class and a method name, which will be resolved to the full URL of the route handled by that method. You may also provide an URI path if you prefer.

### Supporting multiple providers

If you need to work with multiple OAuth providers, you may create multiple OAuth configurations using tags. These tags may then be used to resolve the {b`Tempest\Auth\OAuth\OAuthClient`} interface, which will use the corresponding configuration.

It's a good practice to use an enum for the tag:

```php app/Auth/Provider.php
enum Provider
{
    case GITHUB;
    case GOOGLE;
    case DISCORD;
}
```

```php app/Auth/github.config.php
return new GitHubOAuthConfig(
    tag: Provider::GITHUB,
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [OAuthController::class, 'handleGitHubCallback'],
    scopes: ['user:email'],
);
```

```php app/Auth/google.config.php
return new GoogleOAuthConfig(
    tag: Provider::GOOGLE,
    clientId: env('GOOGLE_CLIENT_ID'),
    clientSecret: env('GOOGLE_CLIENT_SECRET'),
    redirectTo: [GoogleOAuthController::class, 'handleGoogleCallback'],
);
```

Once you have configured your OAuth providers and your tags, you may inject the {b`Tempest\Auth\OAuth\OAuthClient`} interface using the corresponding tag:

```php app/AuthController.php
use Tempest\Container\Tag;

final readonly class AuthController
{
    public function __construct(
        #[Tag(OAuthProvider::GITHUB)]
        private OAuthClient $githubClient,
        #[Tag(OAuthProvider::GOOGLE)]
        private OAuthClient $googleClient,
    ) {}

    #[Get('/auth/github')]
    public function redirectToGitHub(): Redirect
    {
        // ...

        return new Redirect($this->githubClient->getAuthorizationUrl());
    }

    #[Get('/auth/github/callback')]
    public function handleGitHubCallback(Request $request): Redirect
    {
        $githubUser = $this->githubClient->handleCallback($request->get('code'));

        // ...
    }

    // Do the same for Google
}
```

### Using a generic provider

If you need to implement OAuth with a provider that Tempest doesn't have a specific configuration for, you may use the {b`Tempest\Auth\OAuth\Config\GenericOAuthConfig`}:

```php app/Auth/custom.config.php
return new GenericOAuthConfig(
    clientId: env('CUSTOM_CLIENT_ID'),
    clientSecret: env('CUSTOM_CLIENT_SECRET'),
    redirectTo: [OAuthController::class, 'handleCallback'],
    urlAuthorize: 'https://provider.com/oauth/authorize',
    urlAccessToken: 'https://provider.com/oauth/token',
    urlResourceOwnerDetails: 'https://provider.com/api/user',
    scopes: ['read:user'],
    scopeSeparator: ' ', // Optional: If omitted the default `,` will be used. OIDC uses a space as separator.
);
```

### Available providers

Tempest provides a different configuration object for each OAuth provider. Below are the ones that are currently supported:

- **GitHub** authentication using {b`Tempest\Auth\OAuth\Config\GitHubOAuthConfig`},
- **Google** authentication using {b`Tempest\Auth\OAuth\Config\GoogleOAuthConfig`},
- **Facebook** authentication using {b`Tempest\Auth\OAuth\Config\FacebookOAuthConfig`},
- **Discord** authentication using {b`Tempest\Auth\OAuth\Config\DiscordOAuthConfig`},
- **Instagram** authentication using {b`Tempest\Auth\OAuth\Config\InstagramOAuthConfig`},
- **LinkedIn** authentication using {b`Tempest\Auth\OAuth\Config\LinkedInOAuthConfig`},
- **Microsoft** authentication using {b`Tempest\Auth\OAuth\Config\MicrosoftOAuthConfig`},
- **Slack** authentication using {b`Tempest\Auth\OAuth\Config\SlackOAuthConfig`},
- **Apple** authentication using {b`Tempest\Auth\OAuth\Config\AppleOAuthConfig`},
- **Twitch** authentication using {b`Tempest\Auth\OAuth\Config\TwitchOAuthConfig`},
- Any other OAuth platform using {b`Tempest\Auth\OAuth\Config\GenericOAuthConfig`}.

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the OAuth testing utilities through the `oauth` property.

These utilities include a way to replace the OAuth client with a testing implementation, as well as a few assertion methods related to OAuth flows.

### Faking an OAuth client

You may generate a fake, testing-only OAuth client by calling the `fake()` method on the `oauth` property. This will replace the OAuth client implementation in the container, and provide useful assertion methods.

```php tests/AuthControllerTest.php
$oauth = $this->oauth->fake(new OAuthUser(
    id: 'jon',
    email: 'jondoe@example.test',
    nickname: 'jondoe',
));
```

Below is an example of a complete testing flow for an OAuth authentication:

```php tests/AuthControllerTest.php
final class OAuthControllerTest extends IntegrationTestCase
{
    #[Test]
    public function oauth(): void
    {
        // We create a fake OAuth client that will return
        // the specified user when the OAuth flow is completed
        $oauth = $this->oauth->fake(new OAuthUser(
            id: 'jon',
            email: 'jondoe@example.test',
            nickname: 'jondoe',
        ));

        // We first simulate a call to the endpoint
        // that redirects to the provider
        $this->http
            ->get('/oauth/discord')
            ->assertRedirect($oauth->lastAuthorizationUrl);

        // We check that the authorization URL was generated,
        // optionally specifying scopes and options
        $oauth->assertAuthorizationUrlGenerated();

        // We then simulate the callback from the provider
        // with a fake code and the expected state
        $this->http
            ->get("/oauth/discord/callback", query: ['code' => 'some-fake-code', 'state' => $oauth->getState()])
            ->assertRedirect('/');

        // We assert that an access token was retrieved
        // with the same fake code we provided before
        $oauth->assertUserFetched(code: 'some-fake-code');

        // Finally, we ensure a user was created with the
        // credentials we specified in the fake OAuth user
        $user = query(User::class)
            ->find(discord_id: 'jon')
            ->first();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('jondoe@example.test', $user->email);
        $this->assertSame('jondoe', $user->username);
    }
}
```


---
title: TypeScript
description: "Tempest provides the ability to generate TypeScript interfaces from PHP classes to ease integration with TypeScript-based front-ends."
keywords: ["Experimental", "Generation"]
experimental: true
---

## Overview

When building applications with TypeScript-based front-ends like [Inertia](https://inertiajs.com), keeping your client-side types synchronized with your PHP backend can be tedious and error-prone.

Tempest solves this by automatically generating TypeScript definitions from your PHP value objects, data transfer objects, and enums.

You can choose to output a single `.d.ts` declaration file or a directory tree of individual `.ts` modules, depending on your project's needs.

## Generating types

Mark any PHP class with the {b`#[Tempest\Generation\TypeScript\AsType]`} attribute to instruct Tempest that a matching TypeScript interface must be generated based on its public properties.

By default, all application enums are also included automatically without needing an attribute. Generate your TypeScript definitions by running `generate:typescript-types`:

```sh ">_ generate:typescript-types"
✓ // Generated 14 type definitions across 2 namespaces.
```

This command scans your marked classes, generates the corresponding TypeScript definitions, and writes them to your configured output location.

## Customizing type resolution

Tempest provides several built-in type resolvers for common types: strings, numbers, dates, enums and class references.

You can add your own resolver by implementing {b`Tempest\Generation\TypeScript\TypeResolver`}. This interface requires a `canResolve()` method to determine if the resolver can handle a given type, and a `resolve()` method that returns a type node.

The following is the actual implementation of the built-in resolver that handles enum cases:

```php EnumCaseTypeResolver.php
#[Priority(Priority::LOW)]
final class EnumCaseTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->isEnumCase();
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        $case = $type->asEnumCase()->getValue();
        $value = $case instanceof BackedEnum
            ? $case->value
            : $case->name;

        return new LiteralTypeNode($value);
    }
}
```

Resolvers may return any supported semantic node depending on your use case, such as {b`Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode`}, {b`Tempest\Generation\TypeScript\TypeNodes\LiteralTypeNode`}, {b`Tempest\Generation\TypeScript\TypeNodes\SymbolTypeNode`}, {b`Tempest\Generation\TypeScript\TypeNodes\ArrayTypeNode`}, {b`Tempest\Generation\TypeScript\TypeNodes\UnionTypeNode`}, {b`Tempest\Generation\TypeScript\TypeNodes\IntersectionTypeNode`}, {b`Tempest\Generation\TypeScript\TypeNodes\ObjectTypeNode`} or {b`Tempest\Generation\TypeScript\TypeNodes\RawTypeNode`}.

:::info
Type resolvers are automatically [discovered](../1-essentials/05-discovery.md) and do not need to be registered manually.
:::

## Configuring output location

By default, Tempest generates a `types.d.ts` definition file at the root of the project, in which the generated types are organized by namespace.

This may be configured by creating a `typescript.config.php` [configuration file](../1-essentials/06-configuration.md#configuration-files) and returning one of the available configuration objects.

### Single file output

To keep all of the TypeScript definitions in a single `.d.ts` declaration file, which is the default, return a {b`Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig`} object and specify the desired output filename.

```php
use Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig;

return new NamespacedTypeScriptGenerationConfig(
    filename: 'types.d.ts',
);
```

The declaration file should be automatically picked up by TypeScript—if not, ensure that it's included in the `include` property of your `tsconfig.json`:

```json
{
	"include": ["types.d.ts"]
}
```

You may then reference the generated types globally by using their namespaces:

```ts
defineProps<{
	entry: Module.Changelog.ChangelogEntry
}>()
```

### Directory structure output

If you prefer to mirror your PHP namespace structure in separate files, you may return a {b`Tempest\Generation\TypeScript\Writers\DirectoryTypeScriptGenerationConfig`} configuration object:

```php
use Tempest\Generation\TypeScript\Writers\DirectoryTypeScriptGenerationConfig;

return new DirectoryTypeScriptGenerationConfig(
    directory: 'src/Web/types',
);
```

This creates a directory tree of individual `.ts` files, making it easier to navigate your types. Each namespace gets its own file, and imports between files are handled automatically.


---
title: Idempotency
description: "Prevent duplicate side effects for HTTP routes and command bus commands by storing and replaying the result of the first execution."
---

## Overview

Payment processing, order creation, resource provisioning - any operation where retrying the same request should not produce duplicate side effects. Timeouts, client retries, and accidental double clicks all cause the same problem: the server cannot distinguish a retry from a new request.

The `tempest/idempotency` package solves this by storing the result of the first execution and replaying it for subsequent requests with the same idempotency key. It supports both [HTTP routes](#idempotent-routes) and [command bus commands](#idempotent-commands).

## Idempotent routes

Add the {b`Tempest\Idempotency\Attributes\Idempotent`} attribute to a controller method. Clients send an `{txt}{:hlvalueproperty:Idempotency-Key:}` header with a unique value (typically a UUID). The first request executes normally and caches the response. Subsequent requests with the same key replay the cached response without re-executing the handler.

```php app/OrderController.php
use Tempest\Router\Post;
use Tempest\Http\Response;
use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\Idempotency\Attributes\Idempotent;

final readonly class OrderController
{
    #[Post('/orders')]
    #[Idempotent]
    public function create(CreateOrderRequest $request): Response
    {
        $order = $this->orderService->create($request);

        return new GenericResponse(
            status: Status::CREATED,
            body: ['id' => $order->id],
        );
    }
}
```

The client must include the idempotency key as a header:

```
POST /orders HTTP/1.1
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json

{"product": "widget", "quantity": 3}
```

When a cached response is replayed, the response includes an `{:hl-property:idempotency-replayed:}: true` header so the client can distinguish replays from original executions.

### Supported methods

Idempotency is only supported for `POST` and `PATCH` routes. Applying `#[Idempotent]` to a `GET`, `PUT`, `DELETE`, or other method will throw an {b`Tempest\Idempotency\Exceptions\IdempotencyMethodWasNotSupported`} exception. `GET` is inherently idempotent, `PUT` and `DELETE` are idempotent by definition in HTTP semantics, and only `POST` and `PATCH` produce non-idempotent side effects.

### Scope resolver

Idempotency keys must be scoped per user or client to prevent key collisions across different actors. This is done by implementing the {b`Tempest\Idempotency\IdempotencyScopeResolver`} interface and registering it in the container.

The `resolve()` method receives the current request and must return a string that uniquely identifies the caller - such as a user ID, session ID, or API key:

```php app/UserIdempotencyScopeResolver.php
use Tempest\Http\Request;
use Tempest\Idempotency\IdempotencyScopeResolver;

final readonly class UserIdempotencyScopeResolver implements IdempotencyScopeResolver
{
    public function __construct(
        private AuthManager $auth,
    ) {}

    public function resolve(Request $request): string
    {
        return (string) $this->auth->currentUser()->id;
    }
}
```

:::warning
A scope resolver is required. If no implementation of {b`Tempest\Idempotency\IdempotencyScopeResolver`} is registered in the container, the middleware will fail at construction time.
:::

### Per-route overrides

The `#[Idempotent]` attribute accepts optional TTL parameters to override the global configuration on a per-route basis. For route-specific settings like key requirement and header name, use the {b`Tempest\Idempotency\Attributes\IdempotentRoute`} attribute alongside `#[Idempotent]`:

```php app/PaymentController.php
use Tempest\Router\Post;
use Tempest\Http\Response;
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Attributes\IdempotentRoute;

final readonly class PaymentController
{
    #[Post('/payments')]
    #[Idempotent(ttlInSeconds: 172_800)]
    #[IdempotentRoute(requireKey: true)]
    public function charge(ChargeRequest $request): Response
    {
        // Cached response persists for 48 hours instead of the default 24
    }
}
```

#### `#[Idempotent]` parameters

| Parameter                             | Type               | Description                                                                                              |
|---------------------------------------|--------------------|----------------------------------------------------------------------------------------------------------|
| `{:hl-property:ttlInSeconds:}`        | `{:hl-type:?int:}` | How long a completed response is cached. Defaults to the config value (86400 / 24 hours).                |
| `{:hl-property:pendingTtlInSeconds:}` | `{:hl-type:?int:}` | How long a pending (in-progress) record is considered active. Defaults to the config value (60 seconds). |

#### `#[IdempotentRoute]` parameters

| Parameter                    | Type                  | Description                                                                                                     |
|------------------------------|-----------------------|-----------------------------------------------------------------------------------------------------------------|
| `{:hl-property:requireKey:}` | `{:hl-type:?bool:}`   | Whether requests without the idempotency key header should be rejected with a 400 response. Defaults to `true`. |
| `{:hl-property:header:}`     | `{:hl-type:?string:}` | The header name to read the idempotency key from. Defaults to `{txt}{:hl-value:Idempotency-Key:}`.              |

When `{:hl-property:requireKey:}` is set to `false`, requests without the header bypass idempotency protection entirely and execute normally.

### Class-level application

The `#[Idempotent]` attribute can be applied at the class level to make all routes in a controller idempotent:

```php app/ApiOrderController.php
use Tempest\Router\Post;
use Tempest\Router\Patch;
use Tempest\Http\Response;
use Tempest\Idempotency\Attributes\Idempotent;

#[Idempotent]
final readonly class ApiOrderController
{
    #[Post('/api/orders')]
    public function create(CreateOrderRequest $request): Response { /* … */ }

    #[Patch('/api/orders/{id}')]
    public function update(int $id, UpdateOrderRequest $request): Response { /* … */ }
}
```

### Response behavior

The middleware produces different responses depending on the state of the idempotency key:

| Scenario                            | Status          | Description                                                                                                            |
|-------------------------------------|-----------------|------------------------------------------------------------------------------------------------------------------------|
| No existing record                  | -               | The request executes normally and the response is cached.                                                              |
| Completed record, same payload      | Original status | The cached response is replayed with an `{:hl-property:idempotency-replayed:}: true` header.                           |
| Completed record, different payload | `422`           | The key was already used with a different request body.                                                                |
| Pending record (in progress)        | `409`           | Another request with the same key is currently being processed. A `{:hl-property:retry-after:}: 1` header is included. |
| Missing key (when required)         | `400`           | The `{txt}{:hl-value:Idempotency-Key:}` header was not provided.                                                       |

### How it works

The `#[Idempotent]` attribute is a [route decorator](../1-essentials/01-routing.md#route-decorators) that adds {b`Tempest\Idempotency\Middleware\IdempotencyMiddleware`} to the route's middleware stack. The middleware:

1. Reads the idempotency key from the request header.
2. Computes a fingerprint of the request (method, URI, body, and query parameters).
3. Acquires a cache lock to prevent concurrent processing of the same key.
4. Checks for an existing record in the idempotency store.
5. If no record exists, saves a pending record, executes the handler, and stores the completed response.
6. If the handler throws an exception, the pending record is deleted so the request can be retried.

A heartbeat mechanism keeps pending records alive during long-running requests, preventing other processes from incorrectly taking over an operation that is still in progress.

## Idempotent commands

Add the {b`Tempest\Idempotency\Attributes\Idempotent`} attribute to prevent duplicate dispatches. When the same command is dispatched more than once, the duplicate is silently skipped. The attribute can be placed on the command class or on the handler method.

On the command class:

```php app/ImportInvoicesCommand.php
use Tempest\Idempotency\Attributes\Idempotent;

#[Idempotent]
final readonly class ImportInvoicesCommand
{
    public function __construct(
        public string $vendorId,
        public string $month,
    ) {}
}
```

Or on the handler method:

```php app/ImportInvoicesHandler.php
use Tempest\CommandBus\CommandHandler;
use Tempest\Idempotency\Attributes\Idempotent;

final class ImportInvoicesHandler
{
    #[Idempotent]
    #[CommandHandler]
    public function handleImportInvoices(ImportInvoicesCommand $command): void
    {
        // Only executes once per unique command payload.
        // Duplicate dispatches are silently skipped.
    }
}
```

When placed on both the command class and the handler, the command class takes precedence.

By default, the idempotency key is derived from a fingerprint of the command's properties. Two commands with identical property values produce the same fingerprint and are considered duplicates.

### Explicit idempotency keys

Commands can provide an explicit key by implementing the {b`Tempest\Idempotency\HasIdempotencyKey`} interface. This is useful when the deduplication key should be a specific business identifier rather than the full payload:

```php app/ProcessPaymentCommand.php
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\HasIdempotencyKey;

#[Idempotent]
final readonly class ProcessPaymentCommand implements HasIdempotencyKey
{
    public function __construct(
        public string $paymentId,
        public int $amount,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->paymentId;
    }
}
```

When using explicit keys, the fingerprint of the command payload is still verified. If the same key is dispatched with a different payload, an {b`Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed`} exception is thrown.

### Per-command TTL overrides

The `#[Idempotent]` attribute accepts the same optional TTL parameters for commands as it does for routes:

```php
#[Idempotent(ttlInSeconds: 3600, pendingTtlInSeconds: 30)]
final readonly class ProcessPaymentCommand { /* … */ }
```

| Parameter                             | Type               | Description                                                                                |
|---------------------------------------|--------------------|--------------------------------------------------------------------------------------------|
| `{:hl-property:ttlInSeconds:}`        | `{:hl-type:?int:}` | How long the completed record is cached. Defaults to the config value (86400 / 24 hours).  |
| `{:hl-property:pendingTtlInSeconds:}` | `{:hl-type:?int:}` | How long a pending record is considered active. Defaults to the config value (60 seconds). |

## Configuration

The idempotency package is configured by creating an `idempotency.config.php` file. All settings have sensible defaults:

```php app/idempotency.config.php
use Tempest\Idempotency\Config\IdempotencyConfig;

return new IdempotencyConfig(
    header: 'Idempotency-Key',
    requireKey: true,
    ttlInSeconds: 86_400,
    pendingTtlInSeconds: 60,
    cachePrefix: 'idempotency',
);
```

| Parameter                             | Default                             | Description                                                                 |
|---------------------------------------|-------------------------------------|-----------------------------------------------------------------------------|
| `{:hl-property:header:}`              | `{txt}{:hl-value:Idempotency-Key:}` | The HTTP header name to read the idempotency key from.                      |
| `{:hl-property:requireKey:}`          | `{:hl-type:true:}`                  | Whether to reject requests that do not include the idempotency key header.  |
| `{:hl-property:ttlInSeconds:}`        | `86400` (24h)                       | How long a completed response is cached.                                    |
| `{:hl-property:pendingTtlInSeconds:}` | `60`                                | How long a pending record is considered active before it can be taken over. |
| `{:hl-property:cachePrefix:}`         | `{:hl-value:idempotency:}`          | Prefix for cache keys in the idempotency store.                             |
| `{:hl-property:storeClass:}`          | `{:hl-type:CacheIdempotencyStore:}` | The {b`Tempest\Idempotency\Store\IdempotencyStore`} implementation to use.  |

### Custom stores

The default store uses Tempest's [cache](./06-cache.md) component. A custom store can be created by implementing the {b`Tempest\Idempotency\Store\IdempotencyStore`} interface and setting the `storeClass` in the configuration:

```php app/idempotency.config.php
use Tempest\Idempotency\Config\IdempotencyConfig;
use App\RedisIdempotencyStore;

return new IdempotencyConfig(
    storeClass: RedisIdempotencyStore::class,
);
```

## Limitations
- **Windows is not supported.** The heartbeat mechanism relies on `pcntl_alarm()` and
  `pcntl_signal()`, which are not available on Windows. Attempting to use idempotency on Windows will throw an {b`Tempest\Idempotency\Exceptions\IdempotencyPlatformWasNotSupported`} exception.
- **Stored responses must be serializable.** Response bodies are stored using PHP serialization or JSON encoding. Non-serializable bodies (such as generators or views) are stored as type name strings and will not reproduce the original output on replay.


---
title: Highlight
description: "Tempest's highlighter is a package for server-side, high-performance, and flexible code highlighting."
---

## Quickstart

Require `tempest/highlight` with composer:

```
composer require tempest/highlight
```

And highlight code like this:

```php
$highlighter = new \Tempest\Highlight\Highlighter();

$code = $highlighter->parse($code, 'php');
```

## Supported languages

All supported languages can be found in the [GitHub repository](https://github.com/tempestphp/highlight/tree/main/src/Languages).

## Themes

There are a [bunch of themes](https://github.com/tempestphp/highlight/tree/main/src/Themes/Css) included in this package. You can load them either by importing the correct CSS file into your project's CSS file, or you can manually copy a stylesheet.

```css
@import "../../../../../vendor/tempest/highlight/src/Themes/Css/highlight-light-lite.css";
```

You can build your own CSS theme with just a couple of classes, copy over [the base stylesheet](https://github.com/tempestphp/highlight/tree/main/src/Themes/Css/highlight-light-lite.css), and make adjustments however you like. Note that `pre` tag styling isn't included in this package.

### Inline themes

If you don't want to or can't load a CSS file, you can opt to use the `InlineTheme` class. This theme takes the path to a CSS file, and will parse it into inline styles:

```php
$highlighter = new Highlighter(new InlineTheme(__DIR__ . '/../src/Themes/Css/solarized-dark.css'));
```

### Terminal themes

Terminal themes are simpler because of their limited styling options. Right now there's one terminal theme provided: `LightTerminalTheme`. More terminal themes are planned to be added in the future.

```php
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\LightTerminalTheme;

$highlighter = new Highlighter(new LightTerminalTheme());

echo $highlighter->parse($code, 'php');
```

![](/img/terminal.png)

## Gutter

This package can render an optional gutter if needed.

```php
$highlighter = new Highlighter()->withGutter(startAt: 10);
```

The gutter will show additions and deletions, and can start at any given line number:

```php{10}
  public function before(TokenType $tokenType): string
  {
      $style = match ($tokenType) {
{-          TokenType::KEYWORD => TerminalStyle::FG_DARK_BLUE,
          TokenType::PROPERTY => TerminalStyle::FG_DARK_GREEN,
          TokenType::TYPE => TerminalStyle::FG_DARK_RED,-}
          TokenType::GENERIC => {+TerminalStyle::FG_DARK_CYAN+},
          TokenType::VALUE => TerminalStyle::FG_BLACK,
          TokenType::COMMENT => TerminalStyle::FG_GRAY,
          TokenType::ATTRIBUTE => TerminalStyle::RESET,
      };

      return TerminalStyle::ESC->value . $style->value;
  }
```

Finally, you can enable gutter rendering on the fly if you're using [commonmark code blocks](#common-mark-integration) by appending <code>{startAt}</code> to the language definition:

<pre>
&#96;&#96;&#96;php{10}
echo 'hi'!
&#96;&#96;&#96;
</pre>

```php{10}
echo 'hi'!
```

## Special highlighting tags

This package offers a collection of special tags that you can use within your code snippets. These tags won't be shown in the final output, but rather adjust the highlighter's default styling. All these tags work multi-line, and will still properly render its wrapped content.

Note that highlight tags are not supported in terminal themes.

### Emphasize, strong, and blur

You can add these tags within your code to emphasize or blur parts:

- <code>{_ content _}</code> adds the <code>.hl-em</code> class
- <code>{* content *}</code> adds the <code>.hl-strong</code> class
- <code>{~ content ~}</code> adds the <code>.hl-blur</code> class

<pre>
{_Emphasized text_}
{*Strong text*}
{~Blurred text~}
</pre>

This is the end result:

```txt
{_Emphasized text_}
{*Strong text*}
{~Blurred text~}
```

### Additions and deletions

You can use these two tags to mark lines as additions and deletions:

- <code>{+ content +}</code> adds the `.hl-addition` class
- <code>{- content -}</code> adds the `.hl-deletion` class

<pre>
{-public class Foo {}-}
{+public class Bar {}+}
</pre>

```php
{-public class Foo {}-}
{+public class Bar {}+}
```

As a reminder: all these tags work multi-line as well:

```php{1}
  public function before(TokenType $tokenType): string
  {
      $style = match ($tokenType) {
{-          TokenType::KEYWORD => TerminalStyle::FG_DARK_BLUE,
          TokenType::PROPERTY => TerminalStyle::FG_DARK_GREEN,
          TokenType::TYPE => TerminalStyle::FG_DARK_RED,
          TokenType::GENERIC => TerminalStyle::FG_DARK_CYAN,
          TokenType::VALUE => TerminalStyle::FG_BLACK,
          TokenType::COMMENT => TerminalStyle::FG_GRAY,
          TokenType::ATTRIBUTE => TerminalStyle::RESET,-}
      };

      return TerminalStyle::ESC->value . $style->value;
  }
```

### Custom classes

You can add any class you'd like by using the <code>{:classname: content :}</code> tag:

<pre>
&lt;style&gt;
.hl-a {
    background-color: #FFFF0077;
}

.hl-b {
    background-color: #FF00FF33;
}
&lt;/style&gt;

&#96;&#96;&#96;php
{:hl-a:public class Foo {}:}
{:hl-b:public class Bar {}:}
&#96;&#96;&#96;
</pre>

```php
{:hl-a:public class Foo {}:}
{:hl-b:public class Bar {}:}
```

### Inline languages

Within inline Markdown code tags, you can specify the language by prepending it between curly brackets:

<pre>
&#96;{php}public function before(TokenType $tokenType): string&#96;
</pre>

You'll need to set up [commonmark](#common-mark-integration) properly to get this to work.

## CommonMark integration

If you're using `league/commonmark`, you can highlight codeblocks and inline code like so:

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\CommonMark\HighlightExtension;

$environment = new Environment();

$environment
    ->addExtension(new CommonMarkCoreExtension())
    ->addExtension(new HighlightExtension());

$markdown = new MarkdownConverter($environment);
```

Keep in mind that you need to manually install `league/commonmark`:

```
composer require league/commonmark;
```

## Implementing a custom language

Let's explain how `tempest/highlight` works by implementing a new language — [Blade](https://laravel.com/docs/11.x/blade) is a good candidate. It looks something like this:

```blade
@if(! empty($items))
    <div class="container">
        Items: {{ count($items) }}.
    </div>
@endslot
```

In order to build such a new language, you need to understand _three_ concepts of how code is highlighted: _patterns_, _injections_, and _languages_.

### Patterns

A _pattern_ represents part of code that should be highlighted. A _pattern_ can target a single keyword like `return` or `class`, or it could be any part of code, like for example a comment: `/* this is a comment */` or an attribute: `#[Get(uri: '/')]`.

Each _pattern_ is represented by a simple class that provides a regex pattern, and a `TokenType`. The regex pattern is used to match relevant content to this specific _pattern_, while the `TokenType` is an enum value that will determine how that specific _pattern_ is colored.

Here's an example of a simple _pattern_ to match the namespace of a PHP file:

```php
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;

final readonly class NamespacePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return 'namespace (?<match>[\w\\\\]+)';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::TYPE;
    }
}
```

Note that each pattern must include a regex capture group that's named `match`. The content that matched within this group will be highlighted.

For example, this regex `namespace (?<match>[\w\\\\]+)` says that every line starting with `namespace` should be taken into account, but only the part within the named group `(?<match>…)` will actually be colored. In practice that means that the namespace name matching `[\w\\\\]+`, will be colored.

Yes, you'll need some basic knowledge of regex. Head over to [https://regexr.com/](https://regexr.com/) if you need help, or take a look at the existing patterns in this repository.

**In summary:**

- Pattern classes provide a regex pattern that matches parts of code.
- Those regexes should contain a group named `match`, which is written like so `(?<match>…)`, this group represents the code that will actually be highlighted.
- Finally, a pattern provides a `{php}TokenType`, which is used to determine the highlight style for the specific match.

### Injections

Once you've understood patterns, the next step is to understand _injections_. _Injections_ are used to highlight different languages within one code block. For example: HTML could contain CSS, which should be styled properly as well.

An _injection_ will tell the highlighter that it should treat a block of code as a different language. For example:

```html
<div>
    <x-slot name="styles">
        <style>
            body {
                background-color: red;
            }
        </style>
    </x-slot>
</div>
```

Everything within `{html}<style></style>` tags should be treated as CSS. That's done by injection classes:

```php
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\IsInjection;
use Tempest\Highlight\ParsedInjection;

final readonly class CssInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '<style>(?<match>(.|\n)*)<\/style>';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'css')
        );
    }
}
```

Just like patterns, an _injection_ must provide a pattern. This pattern, for example, will match anything between style tags: `{html}<style>(?<match>(.|\n)*)<\/style>`.

The second step in providing an _injection_ is to parse the matched content into another language. That's what the `{php}parseContent()` method is for. In this case, we'll get all code between the style tags that was matched with the named `(?<match>…)` group, and parse that content as CSS instead of whatever language we're currently dealing with.

**In summary:**

- Injections provide a regex that matches a blob of code of language A, while in language B.
- Just like patterns, injection regexes should contain a group named `match`, which is written like so: `(?<match>…)`.
- Finally, an injection will use the highlighter to parse its matched content into another language.

### Languages

The last concept to understand: _languages_ are classes that bring _patterns_ and _injections_ together. Take a look at the `{php}HtmlLanguage`, for example:

```php
class HtmlLanguage extends BaseLanguage
{
    public function getName(): string
    {
        return 'html';
    }

    public function getAliases(): array
    {
        return ['htm', 'xhtml'];
    }

    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
            new PhpInjection(),
            new PhpShortEchoInjection(),
            new CssInjection(),
            new CssAttributeInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new OpenTagPattern(),
            new CloseTagPattern(),
            new TagAttributePattern(),
            new HtmlCommentPattern(),
        ];
    }
}
```

This `{php}HtmlLanguage` class specifies the following things:

- PHP can be injected within HTML, both with the short echo tag `<?=` and longer `<?php` tags
- CSS can be injected as well, JavaScript support is still work in progress
- There are a bunch of patterns to highlight HTML tags properly

On top of that, it extends from `{php}BaseLanguage`. This is a language class that adds a bunch of cross-language injections, such as blurs and highlights. Your language doesn't _need_ to extend from `{php}BaseLanguage` and could implement `{php}Language` directly if you want to.

With these three concepts in place, let's bring everything together to explain how you can add your own languages.

### Adding custom languages

So we're adding [Blade](https://laravel.com/docs/11.x/blade) support. We could create a new language class and start from scratch, but it'd probably be easier to extend an existing language, `{php}HtmlLanguage` is probably the best. Let create a new `{php}BladeLanguage` class that extends from `{php}HtmlLanguage`:

```php
class BladeLanguage extends HtmlLanguage
{
    public function getName(): string
    {
        return 'blade';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
        ];
    }
}
```

With this class in place, we can start adding our own patterns and injections. Let's start with adding a pattern that matches all Blade keywords, which are always prepended with the `@` sign. Let's add it:

```php
final readonly class BladeKeywordPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\@[\w]+)\b';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::KEYWORD;
    }
}
```

And register it in our `{php}BladeLanguage` class:

```php
public function getPatterns(): array
{
    return [
        ...parent::getPatterns(),
        new BladeKeywordPattern(),
    ];
}
```

Next, there are a couple of places within Blade where you can write PHP code: within the `{blade}@php` keyword, as well as within keyword brackets: `{blade}@if (count(…))`. Let's write two injections for that:

```php
final readonly class BladePhpInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '\@php(?<match>(.|\n)*?)\@endphp';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'php')
        );
    }
}
```

```php
final readonly class BladeKeywordInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '(\@[\w]+)\s?\((?<match>.*)\)';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'php')
        );
    }
}
```

Let's add these to our `{php}BladeLanguage` class as well:

```php
public function getInjections(): array
{
    return [
        ...parent::getInjections(),
        new BladePhpInjection(),
        new BladeKeywordInjection(),
    ];
}
```

Next, you can write `{{ … }}` and `{!! … !!}` to echo output. Whatever is between these brackets is also considered PHP, so, one more injection:

```php
final readonly class BladeEchoInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '({{|{!!)(?<match>.*)(}}|!!})';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'php')
        );
    }
}
```

And, finally, you can write Blade comments like so: `{{-- --}}`, this can be a simple pattern:

```php
final readonly class BladeCommentPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\{\{\-\-(.|\n)*?\-\-\}\})';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::COMMENT;
    }
}
```

With all of that in place, the only thing left to do is to add our language to the highlighter:

```php
$highlighter->addLanguage(new BladeLanguage());
```

And we're done! Blade support with just a handful of patterns and injections!

## Adding tokens

<style>
.hl-null {
    color: red;
}
</style>

Some people or projects might want more fine-grained control over how specific words are coloured. A common example are `null`, `true`, and `false` in json files. By default, `tempest/highlight` will treat those value as normal text, and won't apply any special highlighting to them:

```json
{
	"null-property": null,
	"value-property": "value"
}
```

However, it's super trivial to add your own, extended styling on these kinds of tokens. Start by adding a custom language, let's call it `ExtendedJsonLanguage`:

```php
use Tempest\Highlight\Languages\Json\JsonLanguage;

class ExtendedJsonLanguage extends JsonLanguage
{
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
        ];
    }
}
```

Next, let's add a pattern that matches `null`:

```php
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\DynamicTokenType;
use Tempest\Highlight\Tokens\TokenType;

final readonly class JsonNullPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '\: (?<match>null)';
    }

    public function getTokenType(): TokenType
    {
        return new DynamicTokenType('hl-null');
    }
}
```

Note how we return a `{php}DynamicTokenType` from the `{php}getTokenType()` method. The value passed into this object will be used as the classname for this token.

Next, let's add this pattern in our newly created `{php}ExtendedJsonLanguage`:

```php
class ExtendedJsonLanguage extends JsonLanguage
{
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            {*new JsonNullPattern(),*}
        ];
    }
}
```

Finally, register `{php}ExtendedJsonLanguage` into the highlighter:

```php
$highlighter->addLanguage(new ExtendedJsonLanguage());
```

Note that, because we extended `{php}JsonLanguage`, this language will target all code blocks tagged as `json`. You could provide a different name, if you want to make a distinction between the default implementation and yours (this is what's happening on this page):

```php
class ExtendedJsonLanguage extends JsonLanguage
{
    public function getName(): string
    {
        return 'json_extended';
    }

    // …
}
```

There we have it!

```json_extended
{
    "null-property": null,
    "value-property": "value"
}
```

You can add as many patterns as you like, you can even make your own `{php}TokenType` implementation if you don't want to rely on `{php}DynamicTokenType`:

```php
enum ExtendedTokenType: string implements TokenType
{
    case VALUE_NULL = 'null';
    case VALUE_TRUE = 'true';
    case VALUE_FALSE = 'false';

    public function getValue(): string
    {
        return $this->value;
    }

    public function canContain(TokenType $other): bool
    {
        return false;
    }
}
```

## Opt-in features

`tempest/highlight` has a couple of opt-in features, if you need them.

### Markdown support

```
composer require league/commonmark;
```

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\CommonMark\HighlightExtension;

$environment = new Environment();

$environment
    ->addExtension(new CommonMarkCoreExtension())
    ->addExtension(new HighlightExtension(/* You can manually pass in configured highlighter as well */));

$markdown = new MarkdownConverter($environment);
```

### Word complexity

Ellison is a simple library that helps identify complex sentences and poor word choices. It uses similar heuristics to Hemingway, but it doesn't include any calls to third-party APIs or LLMs. Just a bit of PHP:

```ellison
The app highlights lengthy, complex sentences and common errors; if you see a yellow sentence, shorten or split it. If you see a red highlight, your sentence is so dense and complicated that your readers will get lost trying to follow its meandering, splitting logic — try editing this sentence to remove the red.

You can utilize a shorter word in place of a purple one. Click on highlights to fix them.

Adverbs and weakening phrases are helpfully shown in blue. Get rid of them and pick words with force, perhaps.

Phrases in green have been marked to show passive voice.
```

You can enable Ellison support by installing [`assertchris/ellison`](https://github.com/assertchris/ellison-php):

```
composer require assertchris/ellison
```

You'll have to add some additional CSS classes to your stylesheet as well:

```css
.hl-moderate-sentence {
	background-color: #fef9c3;
}

.hl-complex-sentence {
	background-color: #fee2e2;
}

.hl-adverb-phrase {
	background-color: #e0f2fe;
}

.hl-passive-phrase {
	background-color: #dcfce7;
}

.hl-complex-phrase {
	background-color: #f3e8ff;
}

.hl-qualified-phrase {
	background-color: #f1f5f9;
}

pre[data-lang="ellison"] {
	text-wrap: wrap;
}
```

The `ellison` language is now available:

<pre>
```ellison
Hello world!
```
</pre>

You can play around with it [here](/ellison).


---
title: Console
description: "The console component can be used as a standalone package to build console applications."
---

## Installation and usage

Tempest's console component can be used standalone. You simply need to require the `tempest/console` package:

```sh
composer require tempest/console
```

Once installed, you may boot a console application as follows.

```php ./my-cli
{:hl-comment:#!/usr/bin/env php:}
<?php

use Tempest\Console\ConsoleApplication;

require_once __DIR__ . '/vendor/autoload.php';

ConsoleApplication::boot()->run();
```

## Registering commands

`tempest/console` relies on [discovery](../1-essentials/05-discovery.md) to find and register console commands. That means you don't have to register any commands manually, and any method within your codebase using the `{php}#[ConsoleCommand]` attribute will automatically be discovered by your console application.

You may read more about building commands in the [dedicated documentation](../1-essentials/04-console-commands.md).

## Configuring discovery

Tempest will automatically discover all console commands from multiple sources:

1. **Core Tempest packages** — Built-in commands from Tempest itself
2. **Vendor packages** — Packages that require any `tempest/*` package,or opt in via `extra.tempest.can-discover`
3. **App namespaces** — All namespaces configured as PSR-4 autoload paths in your `composer.json`

```json
{
	"autoload": {
		"psr-4": {
			"App\\": "app/"
		}
	}
}
```

In case you need more fine-grained control over which directories to discover, you may provide additional discovery locations to the `{php}ConsoleApplication::boot()` method:

```php
use Tempest\Console\ConsoleApplication;
use Tempest\Discovery\DiscoveryLocation;

ConsoleApplication::boot(
    discoveryLocations: [
        new DiscoveryLocation(
            namespace: 'MyApp\\',
            path: __DIR__ . '/src',
        ),
    ],
)->run();
```

The `{php}boot()` method accepts the following parameters:

- `{php}$name` — The application name (default: `'Tempest'`)
- `{php}$root` — The root directory (default: current working directory)
- `{php}$discoveryLocations` — Additional discovery locations to append to the auto-discovered ones


---
title: Framework lifecycle
description: "Learn the steps involved in booting, running and shutting down the framework."
---

## Booting

Tempest's entry point is usually `public/index.php` or `./tempest`. The former uses {b`Tempest\Router\HttpApplication`}, the latter {b`Tempest\Console\ConsoleApplication`}.

When created, the application boots by creating the {b`\Tempest\Core\FrameworkKernel`}:

- it loads the environment, the exception handler, and configures the container,
- it then starts discovery through the {b`\Tempest\Core\Kernel\LoadDiscoveryLocations`} and {b`\Tempest\Core\Kernel\LoadDiscoveryClasses`} classes,
- and finally registers configuration files through the {b`\Tempest\Core\Kernel\LoadConfig`} class.

When bootstrapping is completed, the `Tempest\Core\KernelEvent::BOOTED` event is fired.

## Shutdown

The shutdown process is managed by the kernel's `shutdown()` method, which is called at the end of both HTTP and console lifecycles, as well as in exception handlers. This method:

- runs deferred tasks,
- dispatches the `KernelEvent::SHUTDOWN` event,
- performs any necessary cleanup,
- and terminates the application process.


---
title: View specifications
description: Read the technical specifications for Tempest View, our templating language.
---

Tempest View is a server-side templating engine powered by PHP. Most of its syntax is inspired by [Vue.js](https://vuejs.org/). Tempest View aims to stay as close as possible to HTML, using PHP where needed. All syntax builds on top of HTML and PHP so that developers don't need to learn any new syntax.

## Basic Syntax

### Expression attributes

Whenever an attribute starts with `:`, it's considered to be an expression attribute and its contents will be interpreted as PHP code. Common examples are control structures or data-passing.

```html
<div :if="$condition"></div>

<x-component :title="$content->title"></x-component>
```

### Escaped expression attributes

Some frontend frameworks also provide a `{html}:{:hl-property:attribute:}` syntax, these attributes can be escaped by using a double `::`:

```html
<div ::if="frontend-code"></div>
```

### Control structures

Control structures like conditionals and loops are modelled with expression attributes. These control structure attributes are available: `{html}:{:hl-property:if:}`, `{html}:{:hl-property:elseif:}`, `{html}:{:hl-property:else:}`, {:hl-property:isset:}`, `{html}:{:hl-property:foreach:}`, `{html}:{:hl-property:forelse:}`. Code within these control structures is compiled to valid PHP expressions.

The following conditional:

```html
<div :if="$condition">A</div>
<div :elseif="$otherCondition">B</div>
<div :else>C</div>
```

Will compile to:

```html
<?php if($condition) { ?>
    <div>A</div>
<?php } elseif ($otherCondition) { ?>
    <div>B</div>
<?php } else { ?>
    <div>C</div>
<?php } ?>
```

The following loop:

```html
<div :foreach="$items as $key => $item">
    A
</div>
<div :forelse>
    Nothing here
</div>
```

Will be compiled to:

```html
<?php if (iterator_count($items)) { ?>
    <?php foreach ($items as $key => $item) { ?>
        <div>A</div>
    <?php } ?>
<?php } else { ?>
    Nothing here
<?php } ?>
```

### Combined control structures

Control structures can be combined and will be parsed in order:

```html
<div :foreach="$items as $key => $item" :if="$key !== 0">
    <!-- Never print the first item -->
</div>
```

### Echoing data

The `{{ $var }}` and `{!! $raw !!}` expressions can be used to write out escaped and raw data respectively. Anything within these expressions is interpreted as PHP:

```html
{{ strtoupper($var) }}
{!! $markdown->render($content) !!}
{{ uri([PostController::class, 'show'], post: $post->id) }}
```

### Comments

The `{html}{{-- --}}` expression is used to mark a block of code as comments. These comments will be stripped out server-side and not passed to the frontend. Normal HTML `{html}<!-- -->` comments can be used as client-side comments.

### Imports

Tempest will merge all imports at the top of the compiled view, meaning that each view can import any reference it needs:

```html
<?php
use App\PostController;
use function Tempest\Router\uri;
?>

{{ uri([PostController::class, 'show'], post: $post->id) }}
```

### View file resolution

Tempest views can be returned from a controller with data passed into them via named arguments:

```php
return view(__DIR__ . '/views/home.view.php', title: 'foo', description: 'bar');
return view('./views/home.view.php', title: 'foo', description: 'bar');
return view('views/home.view.php', title: 'foo', description: 'bar');
```

Tempest will search for view files according to the following rules:

- View files always end with `.view.php`
- First we check whether the view path as-is exists (absolute paths, eg. when using `__DIR__`)
- If not, we'll check whether the view file can be found relative to the controller's location
- If not, we'll search all discovery locations for the given path

### View objects

instead of using a `.view.php` file directly, developers can opt to create custom view objects. These objects implement the {b`\Tempest\View\View`} interface and expose their public properties and methods to their associated view:

```php
use Tempest\View\View;
use Tempest\View\IsView;

final class BookView implements View
{
    use IsView;

    public function __construct(
        public string $title,
        public Book $book,
    ) {
        $this->path = __DIR__  . '/books.view.php';
    }
    
    public function summarize(Book $book): string 
    {
        return // …
    }
}
```

```html
<h1>{{ $title }}</h1>

<div :foreach="$book->relatedBooks as $relatedBook">
    {{ $this->summarize($relatedBook) }}
</div>
```

### Templates

The built-in `{html}<x-template>` element may be used as a placeholder when you want to use a directive without rendering an actual element in the DOM.

```html
<x-template :foreach="$posts as $post">
    <div>{{ $post->title }}</div>
</x-template>
```

The example above will only render the child `div` elements:

```html
<div>Post A</div>
<div>Post B</div>
<div>Post C</div>
```

### Boolean attributes

The HTML specification describes a special kind of attributes called [boolean attributes](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attribute). These attributes don't have a value, but indicate `true` whenever they are present.

Using an expression attribute that return a boolean variable will follow the HTML specification, effectively not rendering the attribute if the value is `false`.

```html
<option :value="$value" :selected="$selected">{{ $label }}</option>
```

Depending on whether `$selected` evaluates to `true` or `false`, the above example may or may not render the `selected` attribute.

Apart from HTMLs boolean attributes, the same syntax can be used with any expression attribute as well:

```html
<div :data-active="{$isActive}"></div>

<!-- <div></div> when $isActive is falsy -->
<!-- <div data-active></div> when $isActive is truthy -->
```

## View components

Both template inclusion and inheritance with tempest/view is handled with html components. Any view file starting with `x-` will be considered to be a view component. View components are written as normal HTML elements, but can pass server-side variables between them in the form of normal and expression attributes.

### Registering view components

To create a view component, create a `.view.php` file that starts with `x-`. These files are referred to as anonymous view components and are automatically discovered by Tempest.

```html app/x-base.view.php
<html lang="en">
	<head>
		<title :if="$title">{{ $title }} — AirAcme</title>
		<title :else>AirAcme</title>
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

### Using view components

All views may include a views components. In order to do so, you may simply use a component's name as a tag, including the `x-` prefix:

```html app/home.view.php
<x-base :title="$this->post->title">
	<article>
		{{ $this->post->body }}
	</article>
</x-base>
```

The example above demonstrates how to pass data to a component using an [expression attribute](#expression-attributes), as well as how pass elements as children if that component where the `<x-slot />` tag is used.

### Attributes in components

Attributes and [expression attributes](#expression-attributes) may be passed into view components. They work the same way as normal elements, and their values will be available in variables of the same name:

```html home.view.php
<x-base :title="$this->post->title">
	// ...
</x-base>
```

```html x-base.view.php
// ...
<title :if="$title">{{ $title }}</title>
```

Note that the casing of attributes will affect the associated variable name:

- `{txt}camelCase` and `{txt}PascalCase` attributes will be converted to `$lowercase` variables
- `{txt}kebab-case` and `{txt}snake_case` attributes will be converted to `$camelCase` variables.

:::info
The idiomatic way of using attributes is to always use `{txt}kebab-case`.
:::

### Fallthrough attributes

When `{html}class` and `{html}style` attributes are used on a view component, they will automatically be added to the root node, or merged with the existing attribute if it already exists.

```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm">
	<!-- ... -->
</button>
```

The example above defines a button component with a default set of classes. Using this component and providing another set of classes will merge them together:

```html index.view.php
<x-button class="text-gray-100 bg-gray-900" />
```

Similarly, the `id` attribute will always replace an existing `id` attribute on the root node of a view component.

### Dynamic attributes

An `$attributes` variable is accessible within view components. This variable is an array that contains all attributes passed to the component, except expression attributes.

Note that attributes names use `{txt}kebab-case`.

```html x-badge.view.php
<span class="px-2 py-1 rounded-md text-sm bg-gray-100 text-gray-900">
	{{ $attributes['value'] }}
</span>
```

### Using slots

The content of components is often dynamic, depending on external context to be rendered. View components may define zero or more slot outlets, which may be used to render the given HTML fragments.

```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm text-gray-100 bg-gray-900">
	<x-slot />
</button>
```

The example above defines a button component with default classes, and a slot inside. This component may be used like a normal HTML element, providing the content that will be rendered in the slot outlet:

```html index.view.php
<x-button>
	<!-- This will be injected into the <x-slot /> outlet -->
	<x-icon name="tabler:x" />
	<span>Delete</span>
</x-button>
```

### Default slot content

A view component's slot can define a default value, which will be used when a view using that component doesn't pass any value to it:

```html x-component.view.php
<div>
    <x-slot>Fallback value</x-slot>
    <x-slot name="a">Fallback value for named slot</x-slot>
</div>
```

```html
<x-component />

<!-- Will render "Fallback value" and "Fallback value for named slot" -->
```

### Named slots

When a single slot is not enough, names can be attached to them. When using a component with named slot, you may use the `<x-slot>` tag with a `name` attribute to render content in a named outlet:

```html x-base.view.php
<html lang="en">
	<head>
		<!-- … -->
		<x-slot name="styles" />
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

The above example uses a slot named `styles` in its `<head>` element. The `<body>` element has a default, unnamed slot. A view component may use `<x-base>` and optionally refer to the `styles` slot using the syntax mentioned above, or simply provide content that will be injected in the default slot:

```html index.view.php
<x-base title="Hello World">
	<!-- This part will be injected into the "styles" slot -->
	<x-slot name="styles">
		<style>
			body {
				/* … */
			}
		</style>
	</x-slot>

	<!-- Everything not living in a slot will be injected into the default slot -->
	<p>
		Hello World
	</p>
</x-base>
```

### Dynamic slots

Within a view component, a `$slots` variable will always be provided, allowing you to dynamically access the named slots within the component.

This variable is an instance of {`Tempest\View\Slot`}, with has a handful of properties:

- `{php}$slot->name`: the slot's name
- `{php}$slot->content`: the compiled content of the slot
- `{php}$slot->attributes`: all the attributes defined on the slot
- `{php}$slot->{attribute}`: dynamically access an attribute defined on the slot

For instance, the snippet below implements a tab component that accepts any number of tabs.

```html x-tabs.view.php
<div :foreach="$slots as $slot">
	<h1 :title="$slot->title">{{ $slot->name }}</h1>
	<p>{!! $slot->content !!}</p>
</div>
```

```html
<x-tabs>
	<x-slot name="php" title="PHP">This is the PHP tab</x-slot>
	<x-slot name="js" title="JavaScript">This is the JavaScript tab</x-slot>
	<x-slot name="html" title="HTML">This is the HTML tab</x-slot>
</x-tabs>
```

### Dynamic view components

On some occasions, you might want to dynamically render view components, ie. render a view component whose name is determined at runtime. You can use the `{html}<x-component :is="">` element to do so:

```html
<!-- $name = 'x-post' -->

<x-component :is="$name" :title="$title" />
```

### View component scope

View components act almost exactly the same as PHP's closures: they only have access to the variables you explicitly provide them, and any variable defined within a view component won't leak into the out scope.

The only difference with normal closures is that view components also have access to view-defined variables as local variables.

```html
<?php 
$title = 'foo';
?>

<!-- $title will need to be passed in explicitly, 
     otherwise `x-post` wouldn't know about it: -->

<x-post :title="$title"></x-post>
```

```php
/* View-defined data will be available within the component directly */
final class HomeController
{
    #[Get('/')]
    public function __invoke(): View
    {
        return view('<x-base />', siteTitle: 'Tempest');
    }
}
```

```html x-base.view.php
<h1>{{ $siteTitle }}</h1>
```

## Built-in components

Besides components that you may create yourself, Tempest provides a default set of useful built-in components to improve your developer experience.

All meta-data about discovered view components can be retrieved via the hidden `meta:view-component` command.

```console
./tempest meta:view-component [view-component]
```

```json
{
	"file": "/…/tempest-framework/packages/view/src/Components/x-markdown.view.php",
	"name": "x-markdown",
	"slots": [],
	"variables": [
		{
			"type": "string|null",
			"name": "$content",
			"attributeName": "content",
			"description": "The markdown content from a variable"
		}
	]
}
```

### `x-base`

A base template you can install into your own project as a starting point. This one includes the Tailwind CDN for quick prototyping.

```html
<x-base :title="Blog">
    <h1>Welcome!</h1>
</x-base>
```

### `x-form`

This component provides a form element that will post by default and includes the csrf token out of the box:

```html
<?php
use function \Tempest\Router\uri;
?>

<x-form :action="uri(StorePostController::class)">
    <!-- … -->
</x-form>
```

### `x-input`

A versatile input component that will render labels and validation errors automatically.

```html
<x-input name="title" />
<x-input name="content" type="textarea" label="Write your content" />
<x-input name="email" type="email" id="other_email" />
```

### `x-submit`

A submit button component that prefills with a "Submit" label:

```html
<x-submit />
<x-submit label="Send" />
```

### `x-csrf-token`

Includes the CSRF token in a form

```html
<form action="…">
    <x-csrf-token />
</form>
```

### `x-icon`

This component provides the ability to inject any icon from the [Iconify](https://iconify.design/) project in your templates.

```html
<x-icon name="material-symbols:php" class="size-4 text-indigo-400" />
```

The first time a specific icon is being rendered, Tempest will query the [Iconify API](https://iconify.design/docs/api/queries.html) to fetch the corresponding SVG tag. The result of this query will be cached indefinitely, so it can be reused at no further cost.

:::info
Iconify has a large collection of icon sets, which you may browse using the [Icônes](https://icones.js.org/) directory.
:::

### `x-vite-tags`

Tempest has built-in support for [Vite](https://vite.dev/), the most popular front-end development server and build tool. You may read more about [asset bundling](../2-features/05-asset-bundling.md) in the dedicated documentation.

This component simply inject registered entrypoints where it is called.

```html x-base.view.php
<html lang="en">
	<head>
		<x-vite-tags />
	</head>
	<!-- ... -->
</html>
```

Optionally, it accepts an `entrypoint` attribute. If it is passed, the component will not inject other entrypoints discovered by Tempest.

```html x-base.view.php
<x-vite-tags entrypoint="src/main.ts" />
```

### `x-template`

See [Templates](#templates).

### `x-slot`

See [Using slots](#using-slots).

### `x-markdown`

A component that will render markdown contents:

```html
<x-markdown># hi</x-markdown>
<x-markdown :content="$text" />
```

### `x-component`

A reserved component to render dynamic view components:

```html
<x-component is="x-post" :title="$title">
    Content
</x-component>
```

The attributes and content of dynamic components are passed to the underlying component.

## Possible IDE integrations

This section lists a bunch of ideas for IDE features that would be useful for IDE integrations.

### Click-through view files

Clicking a view file path leads to the view:

```php
return view(__DIR__ . '/views/home.view.php');
return view('views/home.view.php');
```

### View data autocompletion:

```php
return view(__DIR__ . '/views/home.view.php', foo: 'Foo', bar: 'Bar');
```

`$foo` and `$bar` are available as variables within `__DIR__ . '/views/home.view.php'`.

```php
return view(__DIR__ . '/views/home.view.php', book: new Book(/* … */));
```

`$book` is available in the view, and its type known for autocompletion.

### Auto-import symbols

Referencing a symbol within a view will automatically import it at the top of the file.

```html
<?php
use App\PostController;
use function Tempest\Router\uri;
?>

{{ uri([PostController::class, 'show'], post: $post->id) }}
```

### Loop variable autocompletion

```html
<div :foreach="$items as $key => $item">
    {{ $item }} {{-- Autocomplete here --}}
</div>
```

### View component autocompletion

```html
<x-book :title="$book->title"></x-book>

{{-- `$title` is available in the `x-book` component  --}}
```

### Click-through view components

cmd/ctrl+click on a view component's tag will open the associated view component file.

### Auto-comment selected text

```html
{{-- this text was selected then commented out via a keyboard shortcut --}}
```

### Cycle between comment types

Pressing the same keyboard short twice will toggle between server-side and client-side comments

```html
{{-- this text was selected then commented out via a keyboard shortcut --}} — First press
<!-- this text was selected then commented out via a keyboard shortcut --> — Second press
this text was selected then commented out via a keyboard shortcut — Third press, reverts back to normal
```


---
title: Roadmap
---

Tempest's first stable version is now released! You're more than welcome to [contribute to Tempest](https://github.com/tempestphp/tempest-framework), and can even work on features in future milestones if anything is of particular interest to you. The best way to get in touch about Tempest development is to [join our Discord server](https://discord.gg/pPhpTGUMPQ).

## Experimental features

Given the size of the project, we decided to mark a couple of features as experimental. These features may still change without having to tag a new major release. Our goal is to rid all experimental components before Tempest 2.0. Here's the list of experimental features:

- [tempest/view](/main/essentials/views): you can use both [Twig](/main/essentials/views#using-twig) or [Blade](/main/essentials/views#using-blade) as alternatives.
- [The command bus](/main/essentials/console-commands): you can plug in any other command bus if you'd like.
- [Authentication and authorization](/main/features/authentication): the current implementation is very lightweight, and we welcome people to experiment with more complex implementations as third-party packages before committing to a framework-provided solution.
- [ORM](/main/essentials/database): you can use existing ORMs like [Doctrine](https://www.doctrine-project.org/) as an alternative.
- [The DateTime component](https://github.com/tempestphp/tempest-framework/tree/main/packages/datetime): you can use [Carbon](https://carbon.nesbot.com/docs/) or [Psl](https://github.com/azjezz/psl) as alternatives.
- [The mail component](/docs/features/mail): this is a newly added component in Tempest 1.4, and is kept experimental for a couple of feature releases to make sure we can fix all edge cases before calling it "stable".
- The cryptography component: this is also kept experimental for a couple of feature releases to be able to iterate on the API.

Please note that we're committed to making all of these components stable as soon as possible. To do so, we will need real-life feedback from the community. By marking these components as experimental, we acknowledge that we probably won't get it right from the get-go, and we want to be clear about that up front.

## Upcoming features

Apart from experimental features, we're also aware that Tempest isn't feature-complete yet. Below is a list of items in our priority list. Feel free to contact us via [GitHub](https://github.com/tempestphp/tempest-framework) or [Discord](https://tempestphp.com/discord) if you'd like to suggest other features, or want to help out with one of these:

- Dedicated support for API development
- HTMX support combined with tempest/view
- Form builder
- Event bus and command bus improvements (transport support, async messaging, event sourcing, …)
- Queuing and messaging components


---
title: Package development
description: "Tempest comes with a handful of tools to help third-party package developers."
---

## Overview

Creating a package for Tempest consists of creating a typical PHP package, except it should depend on the relevant Tempest dependency. When you install a dependency that depends on any `tempest/*` package, [discovery](../1-essentials/05-discovery.md) will find it through Composer metadata and register discoverable classes.

Unlike Symfony or Laravel, Tempest doesn't have a dedicated "service provider" concept. Instead, you're encouraged to rely on [discovery](../1-essentials/05-discovery.md) and [initializers](../1-essentials/05-container#dependency-initializers).

## Optional Tempest support

If your package is a normal package but has optional support for Tempest, you can opt-in for discovery by providing metadata in `composer.json`:

```json composer.json
{
	"extra": {
		"tempest": {
			"can-discover": true
		}
	}
}
```

The `extra.tempest.can-discover` property marks your package as discoverable even without a Tempest dependency.

## Preventing discovery

You may create classes which would normally be discovered by Tempest. You may prevent this behavior by marking them with the {`Tempest\Discovery\SkipDiscovery`} attribute.

You may still use that class internally, or allow you package to publish it using an [installer](#installers).

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class UserMigration implements Migration
{
    // …
}
```

Alternatively, you may use composer metadata to completely exclude any path from discovery. This is mostly useful when the package has optional dependencies, since discovery use Reflection and will throw errors when encountering unknown classes or interfaces.

```json composer.json
{
	"extra": {
		"tempest": {
			"ignore": [
				"src/OptionalDependency.php"
			]
		}
	}
}
```

## Installers

An installer is a command that publishes files to the user's project. For instance, this can be used to export migration files that shouldn't be discovered unless the user have published them.

You may create an installed by implementing the {`Tempest\Core\Installer`} interface. Usually, the {`Tempest\Core\PublishesFiles`} trait is used to help with this task. This trait provides a convenient way to publish files and adjust their imports automatically.

### Publishing files

The `publish()` method from the {b`Tempest\Core\PublishesFiles`} trait allows for copying a file to the user's project. It will automatically adjust the file's imports, so that they point to the correct namespace.

The user will have a chance to specify the destination of the file, and whether or not to overwrite it.

```php
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\ClassManipulator;
use function Tempest\src_namespace;
use function Tempest\src_path;

final readonly class AuthInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'auth';
    
    public function install(): void
    {
        $publishFiles = [
            __DIR__ . '/User.php' => src_path('User.php'),
            __DIR__ . '/UserMigration.php' => src_path('UserMigration.php'),
            __DIR__ . '/Permission.php' => src_path('Permission.php'),
            __DIR__ . '/PermissionMigration.php' => src_path('PermissionMigration.php'),
            __DIR__ . '/UserPermission.php' => src_path('UserPermission.php'),
            __DIR__ . '/UserPermissionMigration.php' => src_path('UserPermissionMigration.php'),
        ];

        foreach ($publishFiles as $source => $destination) {
            $this->publish(
                source: $source,
                destination: $destination,
            );
        }

        $this->publishImports();
    }
}
```

### Customizing the publishing process

You may provide a callback to the `publish()` method to customize the publishing process. This callback will be called after the file has been copied, but before the imports have been adjusted.

```php
public function install(): void
{
    // …

    $this->publish(
        source: $source,
        destination: $destination,
        callback: function (string $source, string $destination): void {
            // …
        },
    );

    $this->publishImports();
}
```

### Ensuring correct imports

When publishing files using the `publish()` method, namespaces are not updated automatically.

This needs to be done by calling the `publishImports()` method. This method will loop over all published files, and adjust any import that references published files.

## Provider classes

Unlike Symfony or Laravel, Tempest doesn't have a dedicated "service provider" concept. Instead, you're encouraged to rely on discovery and initializers. However, there might be situations where you need to set up things for your package.

In order to do that, you may register a listener for the `KernelEvent::BOOTED` event. This event is triggered when Tempest's kernel has booted, but before any application code is run. It's the perfect place to hook into Tempest's internals if you need to set up stuff specifically for your package.

```php
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;

final readonly class MyPackageProvider
{
    public function __construct(
        // You can inject any dependency you like
        private Container $container,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function initialize(): void
    {
        // Do whatever needs to be done
        $this->container->…
    }
}
```

## Testing helpers

Tempest provides a {`\Tempest\Framework\Testing\IntegrationTest`} class, which your PHPUnit tests may extend from. By doing so, your tests will automatically boot the framework, and have a range of helper methods available.

For more information regarding testing, you may read the [dedicated documentation](../1-essentials/07-testing.md).


---
title: Standalone components
---

## Overview

Many Tempest components can be installed as standalone packages in existing or new projects: `tempest/console`, `tempest/http`, `tempest/event-bus`, `tempest/debug`, `tempest/command-bus`, etc.

Note that Tempest is in its early stages—some components still depend on `tempest/core`, while they ideally should not. This may change in the future.

## `tempest/console`

```
composer require tempest/console
```

`tempest/console` ships with a built-in binary:

```console
./vendor/bin/tempest

<h1>Tempest</h1>

<comment>…</comment>
```

Or you can manually boot the console application like so:

```php
<?php

use \Tempest\Console\ConsoleApplication;

require_once __DIR__ . '/vendor/autoload.php';

ConsoleApplication::boot()->run();
```

## `tempest/http`

`tempest/http` contains all code to run a web application: router and view renderer, controllers, HTTP exception handling, view components, etc.

```
composer require tempest/http
```

Note that `tempest/console` is shipped with `tempest/http` as well so that you can manage discovery cache, static pages, debug routes, use the local dev server, etc.

You can install the necessary files with the built-in tempest console:

```console
./vendor/bin/tempest install framework
```

Or you can manually create an `index.php` file in your project's public folder:

```php
<?php
use \Tempest\Router\HttpApplication;

require_once __DIR__ . '/vendor/autoload.php';

HttpApplication::boot(
    root: __DIR__ . '/../',
)->run();
```

Note that the `root` path passed in `HttpApplication::boot` should point to your project's root folder.

## `tempest/container`

`tempest/container` is Tempest's standalone container implementation. Note that this package doesn't provide discovery, so initializers will need to be added manually.

```
composer require tempest/container
```

```php
$container = new Tempest\Container\GenericContainer();

$container->addInitializer(FooInitializer::class);

$foo = $container->get(Foo::class);
```

## `tempest/debug`

`tempest/debug` provides the `lw`, `ld` and `ll` functions. This package is truly standalone, but when installed in a Tempest project, it will also automatically write to configured log files.

```
composer require tempest/debug
```

```php
ld($variable);
```

## `tempest/view`

Tempest View can be used as a standalone package. You can read about how to use it [here](/2.x/essentials/views#tempest-view-as-a-standalone-engine).

## `tempest/event-bus`

Tempest's event bus can be used as a standalone package, in order for event handlers to be discovered, you'll have to boot Tempest's kernel and resolve the event bus from the container:

```
composer require tempest/event-bus
```

```php
$container = Tempest::boot();

// You can manually resolve the event bus from the container
$eventBus = $container->get(\Tempest\EventBus\EventBus::class);
$eventBus->dispatch(new MyEvent());

// Or use the `event` function, which is shipped with the package
\Tempest\event(new MyEvent());
```

## `tempest/command-bus`

Tempest's command bus can be used as a standalone package, in order for command handlers to be discovered, you'll have to boot Tempest's kernel and resolve the command bus from the container:

```
composer require tempest/command-bus
```

```php
$container = Tempest::boot();

// You can manually resolve the command bus from the container
$commandBus = $container->get(\Tempest\CommandBus\CommandBus::class);
$commandBus->dispatch(new MyCommand());

// Or use the `command` function, which is shipped with the package
\Tempest\command(new MyCommand());
```

## `tempest/mapper`

`tempest/mapper` maps data between many types of sources, from arrays to objects, objects to JSON, …

```
composer require tempest/mapper
```

```php
Tempest::boot();

$foo = map(['name' => 'Hi'])->to(Foo::class);
```


---
title: Deployments
description: "How to deploy Tempest to production."
---

There are many ways to deploy a PHP application. This page will list the most basic way of setting up a Tempest production server.

## Prerequisites

Your server will need PHP [8.4+](https://www.php.net/downloads.php) and [Composer](https://getcomposer.org/) at the minimum. You should also have either [Bun](https://bun.sh) or [Node](https://nodejs.org) available if you chose to bundle front-end assets. While shared servers will probably work given enough configuration, it is recommended to use a dedicated server for production. The rest of this page will assume you have a server with SSH access available.

## Deployment scripts

Currently, Tempest doesn't have a dedicated deployment script that comes with the framework. There will be a `tempest ship` command in the future, but that's still [work in progress](https://github.com/tempestphp/tempest-framework/issues/352). However, creating a deployment script is very simple, given that you have SSH access.

This website, for example, has a very simple [`deploy` command](https://github.com/tempestphp/tempest-docs/blob/main/src/Console/DeployCommand.php) that does two things:

- Login via SSH and pull in the latest changes from the repository.
- Run the [deploy.sh](https://github.com/tempestphp/tempest-docs/blob/main/deploy.sh) script that's included in the repository.

This `deploy.sh` script could look something like this:

```sh
#!/bin/bash

{:hl-comment:# Sourcing bashrc because we're connecting via SSH':}
{:hl-keyword:.:} /home/user/.bashrc

{:hl-comment:# Dependencies:}
{:hl-keyword:composer:} install --no-dev
{:hl-keyword:bun:} install

{:hl-comment:# Tempest:}
{:hl-keyword:tempest:} cache:clear --force --internal --all
{:hl-keyword:tempest:} discovery:generate
{:hl-keyword:tempest:} migrate:up --force
{:hl-keyword:tempest:} static:clean --force
{:hl-keyword:bun:} run build
{:hl-keyword:tempest:} static:generate --allow-dead-links --verbose=true
```

As you can see, there are a number of steps involved to deploying a Tempest project:

- Installing composer and frontend dependencies
- Clearing all caches and regenerating the discovery cache
- Running migrations
- Clean up static assets if you're using static pages
- Compiling frontend assets
- Finally, regenerating static pages if you're using them

## Initial installation

While a deploy script handles day-by-day deployments, initial server setup requires a number of one-time steps.

First, make sure there's a `.env` file created in your project's root directory. Don't forget to run `tempest key:generate` once to create a signing key.

```dotenv
# Generated by `tempest key:generate`
SIGNING_KEY=…

# Set to production
ENVIRONMENT=production

# Set the base URI to your production domain
BASE_URI=https://tempestphp.com

# Enable all caches
INTERNAL_CACHES=true

# Use full discovery cache in production
DISCOVERY_CACHE=true

# Set the PHP executable path if you're using Tempest's front-end scaffolding
# See: https://tempestphp.com/2.x/getting-started/installation#scaffolding-front-end-assets
PHP_EXECUTABLE_PATH=/usr/bin/php8.4

# Any project-specific environment variables you may need.
# …
```

Next, make sure that the `.tempest` directory is writable by the web server, this is the cache directory used by Tempest. Finally, [enable the scheduler](/2.x/features/scheduling#using-the-scheduler).

## In closing

If you find that there is anything missing from this page, please let us know by opening an issue [on GitHub](https://github.com/tempestphp/tempest-framework).

---
title: Contributing
keywords: "How do I"
---

Welcome aboard! We're excited that you are interested in contributing to the Tempest framework. We value all contributions to the project and have assembled the following resources to help you get started. Thanks for being a contributor!

## Report an error or bug

To report an error or a bug, please:

- Head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) to open an issue.
- Provide as much context about the problem you are running into and the environment you are running Tempest in.
- Provide the version and, if relevant, the component you are running into issues with.
- For a shot at getting our "Perfect Storm" label, submit a PR with a failing test!

Once the issue has been opened, the Tempest team will:

<!-- TODO: Update this section with some links -->

- Label the issue appropriately.
- Assign the issue to the appropriate team member.
- Try and get a response to you as quickly as possible.

In the event that an issue is opened, but we get no response within 30 days, the issue will be closed.

## Request a feature

Tempest is a work in progress. We recognize that some features you might benefit from or expect may be missing. If you do have a feature request, please:

- Head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) to open an issue.
- Provide as much detail about the feature you are looking for and how it might benefit you and others.

Once the feature request has been opened, the Tempest team will:

<!-- TODO: Update this section with some links -->

- Label the issue appropriately.
- Ask any clarifying question to help better understand the use case.
- If the feature requested is accepted, the Tempest team will assign the `{txt}Uncharted waters` label. A Tempest team member or a member of the community can contribute the code for this.

:::tip
We welcome all contributions and greatly value your time and effort. To ensure your work aligns with Tempest's vision and avoids unnecessary effort, we aim to provide clear guidance and feedback throughout the process.
:::

## Contribute documentation

Documentation is how users learn about the framework, and developers begin to understand how Tempest works under the hood. It's critical to everything we do! Thank you in advance for your assistance in ensuring Tempest documentation is extensive, user-friendly, and up-to-date.

:::tip
We welcome contributions of any size! Feel free to submit a pull request, even if it's just fixing a typo or adding a sentence. We especially value additions coming from new users' perspectives, which help make Tempest more accessible.
:::

To contribute to Tempest's documentation, please:

- [Set up Tempest locally](#setting-up-tempest-locally) and head to `/docs`.
- Add or edit any relevant documentation in a manner consistent with the rest of the documentation.
- Re-read what you wrote and run it through a spell checker.
- Optionally,
    - Head over to the [Tempest docs repository](https://github.com/tempestphp/tempest-docs) to fork the project.
    - Run `tempest docs:symlink` to link the `/docs` of your local Tempest clone to the documentation website.
    - Preview your changes by running `bun run dev`.
- Open a pull request with your changes.

Once a pull request has been opened, the Tempest team will:

- Use GitHub reviews to review your pull request.
- If necessary, ask for revisions.
- If we decide to pass on your pull request, we will thank you for your contribution and explain our decision. We appreciate all the time contributors put into Tempest!
- If your pull request is accepted, we will mark it as such and merge it into the project. It will be released in the next tagged version! 🎉

## Contribute code

So, you want to dive into the code. To make the most of your time, please ensure that any contributions pertain to an approved feature request or a confirmed bug. This helps us focus on the vision for Tempest and ensuring the best developer experience.

To contribute to Tempest's code, you will need to first [setup Tempest locally](#setting-up-tempest-locally). Then,

- Make the relevant code changes.
- Write tests that verify that your contribution works as expected.
- Run `composer qa` to ensure you are adhering to our style guidelines.
- Create a [pull request](https://github.com/tempestphp/tempest-framework/pulls) with your changes.
- If your pull request is connected to an open issue, add a line in your description that says `{txt}Fixes #xxx`, where `{txt}#xxx` is the number of the issue you're fixing.

:::tip Pull request titles
We use [conventional commits](#commit-and-merge-conventions) to automatically generate readable changelogs. You may help with this by providing a clear pull request title, which will appear in the changelog and needs to be understandable without the pull request's content as a context. Read more about this in the [pull requests](#pull-requests) section.
:::

Once a pull request has been opened, the Tempest team will:

- Use GitHub reviews to review your pull request.
- Ensure all CI pipelines are passing.
- If necessary, ask for revisions.
- If we decide to pass on your pull request, we will thank you for your contribution and explain our decision. We appreciate all the time contributors put into Tempest!
- If your pull request is accepted, we will mark it as such and merge it into the project. It will be released in the next tagged version! 🎉

### Setting up Tempest locally

- Install PHP.
- Install Composer.
- Install [Bun](https://bun.sh) or Node.
- [Fork and clone](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo) the Tempest repository.

In your terminal, run:

```sh
cd /path/to/your/clone
composer update
bun install
bun dev
```

You're ready to get started!

#### Linking your local Tempest to another local Tempest application

If you have another Tempest application with which you want to use your local version of the framework, you may do so with [composer symlinking](https://getcomposer.org/doc/05-repositories.md#path).

Add the following in your `composer.json`, replacing `{txt}/path/to/your/clone` with the absolute path to your local version of the framework:

```json
{
	// ...
	"repositories": [
		{
			"type": "path",
			"url": "/path/to/your/clone"
		}
	],
	"minimum-stability": "dev",
	"prefer-stable": true
	// ...
}
```

You may then run `{sh}composer require "tempest/framework:*"`.

If you are also working on one of the JavaScript packages, you may also symlink them to your local Tempest application by running `{sh}bun install /path/to/your/clone/package`. Note that the path must be to the actual JavaScript package, and not the root of the framework.

For instance, assuming you cloned the framework in `/Users/you/Code/forks/tempest`, the command to symlink `vite-plugin-tempest` should look like that:

```sh
bun install /Users/you/Code/forks/tempest/packages/vite-plugin-tempest
```

Do not forget to run `bun dev` in the root of your local version of the framework, so your changes can be reflected on your local application without needing to run `bun build` each time.

### Code style and conventions

Tempest uses a modified version of PSR-12. We automate the entire styling process because we know everyone is used to different standards and workflows. To see some of the rules we enforce, check out our [Mago](https://github.com/tempestphp/tempest-framework/blob/main/mago.toml) and [Rector](https://github.com/tempestphp/tempest-framework/blob/main/rector.php) configurations.

The following outlines some other guidelines we have established for Tempest.

#### `final` and `readonly` as a default

Whenever possible, classes should be `final` and `readonly`. This practice promotes immutability and prevents inadvertent changes to logic.

:::tip{tabler:bulb}
You may watch this [video](https://www.youtube.com/watch?v=HiD6CwWq5Ds&ab_channel=PHPAnnotated) to understand {x:brendt_gd}'s thoughts about using `final`.
:::

---

#### Acronym casing

Tempest uses a modified version of the [.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043(v=vs.100)?redirectedfrom=MSDN) for acronym casing. Please see below for our guidelines:

**Do capitalize all characters of two to three character acronyms, except the first word of a camel-cased identifier.**
A class named `IPAddress` is an example of a short acronym (IP) used as the first word of a Pascal-cased identifier. A parameter named `ipAddress` is an example of a short acronym (ip) used as the first word of a camel-cased identifier.

**Do capitalize only the first character of acronyms with four or more characters, except the first word of a camel-cased identifier.**
A class named `UuidGenerator` is an example of a long acronym (Uuid) used as the first word of a Pascal-cased identifier. A parameter named `uuidGenerator` is an example of a long acronym (uuid) used as the first word of a camel-cased identifier.

**Do not capitalize any of the characters of any acronyms, whatever their length, at the beginning of a camel-cased identifier.**
A class named `Uuid` is an example of a long acronym (Uuid) used as the first word of a camel-cased identifier. A parameter named `dbUsername` is an example of a short acronym (db) used as the first word of a camel-cased identifier.

---

#### Validation classes

When writing error messages for validation rules, **refrain from including ending punctuation** such as periods, exclamation marks, or question marks. This helps in maintaining a uniform style and prevents inconsistency in error message presentation.

```diff
- Value should be a valid email address!
+ Value should be a valid email address
```

---

#### Exception classes

Exception classes can be thought of as events and should be named accordingly. Use a subject-verb structure in the past tense to describe what happened (e.g., `DatabaseOperationFailed`, `StorageUsageWasForbidden`, `AuthenticatedUserWasMissing`).

- Do not suffix class names with `Exception`; the context of `throw` or `catch` language constructs makes their purpose clear.
- All exception classes must extend PHP's built-in `\Exception`.
- When appropriate, define marker interfaces such as `CacheException`, `DatabaseException`, or `FilesystemException` to group related exceptions. These interfaces must be suffixed with `Exception`.
- Set the exception message within the exception class itself—not where it is thrown.
- Override the constructor to only accept relevant context-specific input.

For instance, the following exception accepts a the relevant cache key as a constructor argument, and keeps it accessible through a public property:

```php LockAcquisitionTimedOut.php
final class LockAcquisitionTimedOut extends Exception implements CacheException
{
    public function __construct(
        public readonly string $key,
    ) {
        parent::__construct("Lock with key `{$key}` could not be acquired on time.");
    }
}
```

## AI contributions

Behind Tempest is a small group of humans who are passionate about code and this project. We welcome anyone who's passionate about PHP and programming to join Tempest, regardless of the tools they are using. At the same time, we expect a level of respect between each other.

As an example, PRs that were AI-generated without any self-review from the contributor's side don't show mutual respect, as it puts the burden of reviewing LLM-generated code on the maintainer's side. We expect each contributor to take a level of ownership and responsibility over their contributions (before they are merged), to make sure the submitted code is clean and understandable, well-tested and adhering to our code style. In turn, from our side, we will be happy to guide anyone who's eager to learn, as long as it goes both ways. We also expect each contributor to ensure that code they are contributing is compatible with the Tempest License.

In other words, you may use whatever tools you want to write code (editors, IDEs, AI chat, agents, …), but you must take ownership and responsibility over your PRs. The least we ask is that you yourself understand the code you've written or generated and are able to explain it in full.

## Release workflow

Tempest uses sub-splits to allow components to be installed as individual packages. The following outlines how this process works.

### Workflow steps

1. **Trigger event**
    - When a pull request is merged, or a new tag is created, the `.github/workflows/subsplit-packages.yml` action is run.

2. **Package information retrieval**
    - When the `subsplit-packages.yml` is run, it calls `bin/get-packages`.
    - This PHP script uses a combination of Composer and the filesystem to return (in JSON) some information about every package. It returns the:
        - **Directory**
        - **Name**
        - **Package**
        - **Organization**
        - **Repository**

3. **Action matrix creation**
    - The result of the `get-packages` command is then used to create an action matrix.
    - This ensures that the next steps are performed for _every_ package discovered.

4. **Monorepo split action**
    - The `symplify/monorepo-split-github-action@v2.3.0` GitHub action is called for every package and provided the necessary information (destination repo, directory, etc.).
    - This action takes any changes and pushes them to the sub-split repository determined by combining the "Organization" and "Repository" values returned in step 2.
    - Depending on whether a tag is found or not, a tag is also supplied so the repository is tagged appropriately.

## Commit and merge conventions

Commits must all respect the [conventional commit specification](https://www.conventionalcommits.org/en/), so the changelog and release notes are generated using the commit history.

### Commit descriptions

Commit descriptions **should not** start with an uppercase letter and should use [imperative mood](https://git.kernel.org/pub/scm/git/git.git/tree/Documentation/SubmittingPatches?h=v2.36.1#n181):

```diff
- feat(support): Adds some cool feature
+ feat(support): add some cool feature
```

### Commit scopes

Scopes are not mandatory, but are highly recommended for consistency and easy of read. The following scopes are the most commonly used:

- `feat` — for a new feature
- `fix` — for a bug fix
- `refactor` — for changes in code that are neither bug fixes or new features
- `docs` — for any change related to the documentation
- `perf` — for code refactoring that improves performance
- `test` — for code related to automatic testing
- `style` — for refactoring related to the code style (not for CSS)
- `ci` — for changes related to our continuous integration pipeline
- `chore` — for anything else

Here are some commit examples:

```
{:hl-property:feat:}({:hl-keyword:support:}): add `StringHelper` class
{:hl-property:feat:}({:hl-keyword:support/string:}): add `uuid` method
{:hl-property:perf:}({:hl-keyword:discovery:}): improve cache efficiency
{:hl-property:refactor:}({:hl-keyword:highlight:}): improve code readability
{:hl-property:docs:}: mention new `highlight` package
{:hl-property:chore:}: update dependencies
{:hl-property:style:}: apply php-cs-fixer
```

### Pull requests

Pull request titles and descriptions should be as explicit as possible to ease the review process.

Contributors are not required to respect conventional commits within pull requests, but doing so will ease the review process by removing some overhead for core contributors.

All pull requests will be renamed to the conventional commit convention if necessary before being squash-merged to keep the commit history and changelog clean.

## Release cycles

Tempest current does not follow a fixed release cycle. In general, bug fixes and minor features can be released as soon as possible. For breaking changes, though, we aim to bundle as many as possible in a single major release.

### Milestones

Even though bug fixes and minor features can be released whenever available, we do some level of long-term planning to ensure Tempest stays on track. There should always be two active milestones, and one for future versions.

- The **current minor milestone** includes all issues that should be addressed as patch or minor versions within the current major version. Anything in this milestone should be considered "ready to work on" and can be done at any point in time before the next major release.
- The **next major milestone** includes all issues that are planned for the next major release, many will be breaking changes. Oftentimes, we'll work on both current minor and next major milestones at the same time.
- The **next minor milestone** includes all issues that should be addressed as patch or minor versions after the next major release has been tagged.
- All other issues that don't get assigned a milestone are considered to be "unplanned". They might at one point be added to a milestone, but there's no guarantee on timing.

As an example:

- The current Tempest version is `2.14`, that means that the current minor milestone is `2.x`
- The next major release is planned for `3.0`, so the next major milestone is `3.0`
- The next minor milestone includes features that are planned after 3.0 is released, and thus go in the `3.x` milestone

For clarity, each milestone will get its corresponding name, with the target branch or tag at the end. For the previous example, the milestones are called:

- `current minor (2.x)`
- `next major (3.0)`
- `next minor (3.x)`

Finally, as we close in on tagging `next major`, features that would usually go in `current minor` can be targeted to `next major` instead, in order to avoid too many merge conflicts between the two milestones.

### Milestone deadlines

Even though we release on a non-fixed schedule, we do assign deadlines to the `next major` version. This gives all contributors a clear goal to work towards, and helps us stay on track. The dealine for `next major` also determines the end date of `current minor`

## Brand Guidelines

### Colors

<span class="swatch" style="--color: #1b1429">#1b1429</span>
<span class="swatch" style="--color: #29abe2">#29abe2</span>
<span class="swatch" style="--color: #00e7ff">#00e7ff</span>
<span class="swatch" style="--color: #0071bc">#0071bc</span>

---
title: Governance
keywords: "governance"
---

## Overview

Tempest, being an open-source project, recognizes the need for a clear governance model to:

- Provide clarity surrounding how project decisions will be made.
- Ensure a safe, fun, and encouraging community.
- Ensure the longevity of the project.

This document defines the governance process for the Tempest project and community.

## Roles & responsibilities

### Benevolent Dictator for Life

The Benevolent Dictator for Life (from here on, BDFL) is ultimately responsible and has final say for project decisions. The BDFL’s responsibilities include:

- Setting the project scope.
- Setting the project timeline.
- Approving releases.
- Approving new council members, core contributors, or moderators.
- Suggesting changes in governance to the council members.

The current BDFL is [Brent Roose](http://github.com/brendt).

### Council members

Council members are appointed delegates who are responsible for helping in governance decisions and ensuring fair decisions are made regarding project governance rules and the BDFL role. The council members' responsibilities include:

- Suggesting new council members, core contributors, or moderators.
- Ratifying governance laws.
- Appointing a new BDFL.
- Three standing council members must always exist, including the BDFL. If the number of council members drops below this minimum count, the BDFL is responsible for appointing new members.

The current council members are [Aidan Casey](https://github.com/aidan-casey), [Enzo Innocenzi](https://github.com/innocenzi), and [Brent Roose](http://github.com/brendt).

### First officer

The first officer is a council member, appointed by the BDFL, as successor in the case of their prolonged or permanent absence.

The current first officer is [Enzo Innocenzi](https://github.com/innocenzi).

### Core contributors

Core contributors are Tempest community members who have shown wisdom, discretion, and provide consistent and prolonged contributions to the project. Core contributors are appointed by the BDFL and must be active members of the project. Core contributors' responsibilities include:

- Promoting Tempest's core values.
- Collective care for the Tempest project.
- Feedback and collaboration on issues.
- Review, approving, and merging of pull requests.

The current core contributors are [Aidan Casey](https://github.com/aidan-casey), [Enzo Innocenzi](https://github.com/innocenzi), [Márk Magyar](https://github.com/xHeaven), and [Brent Roose](http://github.com/brendt).

### Moderators

Moderators hold a special role amongst the Tempest community, ensuring its tone is consistent with the Tempest core values. Moderators are appointed by the BDFL and must be active members of the community Discord. Moderators’ responsibilities include:

- Promoting Tempest Core Values.
- Collective care for the Tempest community through editing and removal of inappropriate messages.
- Collective care for the Tempest community through removal of spam, promotions, and banning of such members.

The current moderators are [iamDadmin](https://github.com/iamdadmin), [Aidan Casey](https://github.com/aidan-casey), [Enzo Innocenzi](https://github.com/innocenzi), and [Brent Roose](http://github.com/brendt).

## Governance

### Appointing the BDFL

In principle, the BDFL stays on the project forever. A new BDFL can be appointed, though, in a limited number of cases:

- When the BDFL decides to step down or retire.
- When the BDFL fails to perform their duties or ceases to perform their duties in a manner of benevolence.
- When the BDFL has been inactive for a prolonged period of 90 days, the BDFL will be considered inactive and replaced by the current First Officer. After an additional 90 days of inactivity, the inactive BDFL will be declared retired and replaced by the acting BDFL. At this point the new BDFL will assign a new First Officer and ensure a minimum of three council members.

"Activity" is measured by:

- Code contributions.
- Community interactions (Discord, socials, etc.).
- Issue and pull request activity.

The BDFL is expected to have reasonable contributions in all areas, although "inactivity" only means being inactive on all fronts. Council members must take reasonable attempts to inform the inactive BDFL of his inactive status. The new BDFL will always be the current First Officer.

### Appointing council members

New council members may be put forward by an existing council member or the BDFL. Like the BDFL, council members must demonstrate benevolence. New council members require a 2/3 majority vote by the standing council to be accepted. If the number of council members drops below the minimum threshold of three members (the BDFL included), the BDFL may appoint new members at their discretion.

Council members are bound to the same activity standards as the BDFL. They will become inactive after 90 days of inactivity and become retired after another 90 days of inactivity.

### Removal of a council member

Council members can be removed by a 2/3 majority vote by the standing council.

### Appointment of a core contributor

New core contributors may be put forward by an existing core contributor, council member, or the BDFL. core contributors are approved by the BDFL.

Core contributors are also expected to be active, although their activity is only measured by:

- Code contributions.
- Issue and PR activity.

When a core contributor has been inactive for 90 days, they may become inactive, per the BDFL's decision. A core contributor can be made active again per decision by the BDFL.

### Changes to this document

This document can be changed via pull requests on GitHub, but it must always be approved by a 2/3 majority of the standing council. They must give their approval via a pull request review. The BDFL can deny a change but not force a change to be made.

## Clarifications

"Tempest's core values" have been mentioned throughout this document, so we want to list them here:

1. We always act in a manner of respect towards each other
2. Everyone is welcome to contribute, regardless of their background or experience
3. Contributors are expected to follow [our contributing guidelines](../extra-topics/contributing)
4. Tempest originated as a project that wanted to think outside the box, and we continue to encourage this mindset

Furthermore, this document has mentioned **a manner of benevolence** when it comes to the BDFL and council members. With this, we mean that we'll put the welfare of the project and wellbeing of its community first, above our own ambitions, as well as the expectation to follow and enforce previously mentioned core values.


