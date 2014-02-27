<?php

namespace Webfactory\TranslationBundle\Translator;

use Webfactory\TranslationBundle\Translator\Formatting\FormatterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Decorates a Symfony translator and adds support for message formatting.
 */
class FormatterDecorator implements TranslatorInterface
{

    /**
     * The inner translator.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator = null;

    /**
     * The formatter that is used to apply message transformations.
     *
     * @var \Webfactory\TranslationBundle\Translator\Formatting\IntlFormatter
     */
    protected $formatter = null;

    /**
     * Creates a decorator for the provided translator.
     *
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param \Webfactory\TranslationBundle\Translator\Formatting\FormatterInterface The formatter that is used.
     */
    public function __construct(TranslatorInterface $translator, FormatterInterface $formatter)
    {
        $this->translator = $translator;
        $this->formatter  = $formatter;
    }

    /**
     * Translates the given message.
     *
     * @param string $id         The message id
     * @param array $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     * @return string The translated string
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $message = $this->translator->trans($id, $parameters, $domain, $locale);
        return $this->handleFormatting($id, $message, $parameters, $locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string $id         The message id
     * @param integer $number     The number to use to find the indice of the message
     * @param array $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     * @return string The translated string
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $message = $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
        return $this->handleFormatting($id, $message, $parameters, $locale);
    }

    /**
     * Sets the current locale.
     *
     * @param string $locale The locale
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    /**
     * Returns the current locale.
     *
     * @return string The locale
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    /**
     * Formats the message if possible and throws a normalized exception in case of error.
     *
     * @param string $id The translation message ID.
     * @param string $message The message pattern that will be used for formatting.
     * @param array(mixed) $parameters
     * @param string|null $locale
     * @return string The formatted message.
     * @throws \Webfactory\TranslationBundle\Translator\FormattingException If formatting fails.
     */
    protected function handleFormatting($id, $message, array $parameters, $locale)
    {
        if (empty($message)) {
            // No formatting needed.
            return $message;
        }
        $locale = $this->toLocale($locale);
        try {
            return $this->format($message, $parameters, $locale);
        } catch (\Exception $e) {
            throw new FormattingException($locale, $id, $message, $parameters, $e);
        }
    }

    /**
     * Applies Intl formatting on the provided message.
     *
     * @param string $message
     * @param array(mixed) $parameters
     * @param string $locale
     * @return string
     */
    protected function format($message, array $parameters, $locale)
    {
        return $this->formatter->format($locale, $message, $parameters);
    }

    /**
     * Returns a valid locale.
     *
     * If a correct locale is provided that one is used.
     * Otherwise, the default locale is returned.
     *
     * @param string|null $locale
     * @return string
     */
    protected function toLocale($locale = null)
    {
        return ($locale === null) ? $this->getLocale() : $locale;
    }

}