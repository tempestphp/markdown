<?php

namespace Tempest\Markdown;

interface Rule
{
    public function shouldParse(Parser $parser): bool;

    public function parse(Parser $parser): ?Token;

    /** Whether this rule runs on the content of $token. */
    public function supportsToken(Token $token): bool;

    /** @param class-string<\Tempest\Markdown\Token> $token */
    public function addTokenSupport(string $token): static;

    /** @param class-string<\Tempest\Markdown\Token> $token */
    public function removeTokenSupport(string $token): static;
}
