<?php

declare(strict_types=1);

namespace JtlWooCommerceConnector\Integrations\Plugins\Wpml;

use JtlWooCommerceConnector\Integrations\Plugins\AbstractComponent;
use WPML\Auryn\InjectionException;

/**
 * Class WpmlStringTranslation
 *
 * @package JtlWooCommerceConnector\Integrations\Plugins\Wpml
 */
class WpmlStringTranslation extends AbstractComponent
{
    /**
     * @param string $sourceName
     * @param string $targetName
     * @param string $wawiIsoLanguage
     * @return void
     * @throws InjectionException
     * @throws \Exception
     */
    public function translate(string $sourceName, string $targetName, string $wawiIsoLanguage): void
    {
        $context = \WPML_ST_Taxonomy_Strings::LEGACY_STRING_DOMAIN;

        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin   = $this->getCurrentPlugin();
        $languageCode = $wpmlPlugin->convertLanguageToWpml($wawiIsoLanguage);

        $stringId = \icl_get_string_id($sourceName, $context);
        if ($stringId !== 0) {
            \icl_add_string_translation($stringId, $languageCode, \html_entity_decode($targetName), 10);
        }
    }

    /**
     * @param string $taxonomy
     * @param string $name
     * @param string $wawiIsoLanguage
     * @return void
     * @throws InjectionException
     * @throws \Exception
     */
    public function registerString(string $taxonomy, string $name, string $wawiIsoLanguage): void
    {
        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin   = $this->getCurrentPlugin();
        $languageCode = $wpmlPlugin->convertLanguageToWpml($wawiIsoLanguage);
        $context      = \WPML_ST_Taxonomy_Strings::LEGACY_STRING_DOMAIN;

        \icl_register_string(
            $context,
            \sprintf("URL %s tax slug", $taxonomy),
            $taxonomy,
            false,
            $languageCode
        );
        $nameSingular = $name;
        \icl_register_string(
            $context,
            \WPML_ST_Taxonomy_Strings::LEGACY_NAME_PREFIX_SINGULAR . $nameSingular,
            $nameSingular,
            false,
            $languageCode
        );
        $nameGeneral = 'Produkt ' . $name;
        \icl_register_string(
            $context,
            \WPML_ST_Taxonomy_Strings::LEGACY_NAME_PREFIX_GENERAL . $nameGeneral,
            $nameGeneral,
            false,
            $languageCode
        );
    }
}
