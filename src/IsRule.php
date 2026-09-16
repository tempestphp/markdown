<?php

namespace Tempest\Markdown;

/**
 * The default token support of a rule. Implementations declare the context
 * they are written for, and the tokens that deviate from it.
 */
trait IsRule
{
    /** @var \Tempest\Markdown\RuleContext[] */
    private(set) array $contexts = [RuleContext::BLOCK];

    /** @var array<class-string<\Tempest\Markdown\Token>, bool> */
    private(set) array $tokenSupport = [];

    public function supportsToken(Token $token): bool
    {
        return (
            $this->tokenSupport[$token::class] ?? in_array(
                RuleContext::INLINE,
                $this->contexts,
                strict: true,
            )
        );
    }

    /** @param class-string<\Tempest\Markdown\Token> $token */
    public function addTokenSupport(string $token): static
    {
        $this->tokenSupport[$token] = true;

        return $this;
    }

    /** @param class-string<\Tempest\Markdown\Token> $token */
    public function removeTokenSupport(string $token): static
    {
        $this->tokenSupport[$token] = false;

        return $this;
    }
}
