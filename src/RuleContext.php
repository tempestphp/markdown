<?php

namespace Tempest\Markdown;

/**
 * The content a rule is written for. It decides which tokens a rule supports
 * by default; {@see \Tempest\Markdown\Rule::addTokenSupport()} and
 * {@see \Tempest\Markdown\Rule::removeTokenSupport()} override that per token.
 */
enum RuleContext
{
    /** Structure: the rule only runs on the document, and on the containers that opt in. */
    case BLOCK;

    /** A run of text: the rule runs inside every token that holds inline content. */
    case INLINE;
}
