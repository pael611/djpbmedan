<?php

namespace App\Support;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use RuntimeException;

class RichTextSanitizer
{
    public static function sanitize(string $html): string
    {
        $html = preg_replace(
            '/&(?:nbsp|#0*160|#x0*a0);/i',
            ' ',
            str_replace("\u{00A0}", ' ', $html)
        ) ?? $html;
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>',
                LIBXML_NONET | LIBXML_HTML_NODEFDTD
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        if (! $loaded) {
            throw new RuntimeException('Rich text could not be parsed.');
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body instanceof DOMElement) {
            return '';
        }

        $allowedTags = array_fill_keys(['p', 'br', 'strong', 'b', 'em', 'i', 'ol', 'ul', 'li', 'a'], true);
        $blockedTags = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'noscript'];
        $sanitizeNodes = function (DOMNode $parent) use (&$sanitizeNodes, $allowedTags, $blockedTags, $document): void {
            foreach (iterator_to_array($parent->childNodes) as $child) {
                if ($child instanceof DOMComment) {
                    $parent->removeChild($child);

                    continue;
                }

                if ($child instanceof DOMText) {
                    $child->nodeValue = str_replace("\u{00A0}", ' ', $child->nodeValue ?? '');

                    continue;
                }

                if (! $child instanceof DOMElement) {
                    continue;
                }

                $tag = strtolower($child->tagName);
                if (in_array($tag, $blockedTags, true)) {
                    $parent->removeChild($child);

                    continue;
                }

                $sanitizeNodes($child);

                if (! isset($allowedTags[$tag])) {
                    $replacement = $document->createDocumentFragment();
                    while ($child->firstChild) {
                        $replacement->appendChild($child->firstChild);
                    }
                    $parent->replaceChild($replacement, $child);

                    continue;
                }

                if ($tag !== 'a') {
                    while ($child->attributes->length > 0) {
                        $child->removeAttributeNode($child->attributes->item(0));
                    }

                    continue;
                }

                $href = trim($child->getAttribute('href'));
                while ($child->attributes->length > 0) {
                    $child->removeAttributeNode($child->attributes->item(0));
                }

                if (! self::isSafeUrl($href)) {
                    $parent->replaceChild($document->createTextNode($child->textContent), $child);

                    continue;
                }

                $child->setAttribute('href', $href);
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener noreferrer nofollow');
            }
        };
        $sanitizeNodes($body);

        $sanitizedHtml = '';
        foreach ($body->childNodes as $child) {
            $sanitizedHtml .= $document->saveHTML($child);
        }

        return str_replace("\u{00A0}", ' ', $sanitizedHtml);
    }

    private static function isSafeUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true);
    }
}
