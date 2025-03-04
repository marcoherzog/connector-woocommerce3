<?php

declare(strict_types=1);

namespace JtlWooCommerceConnector\Integrations\Plugins\Wpml;

use Exception;
use Jtl\Connector\Core\Model\Specific;
use Jtl\Connector\Core\Model\SpecificI18n as SpecificI18nModel;
use JtlWooCommerceConnector\Integrations\Plugins\AbstractComponent;
use Psr\Log\InvalidArgumentException;
use WPML\Auryn\InjectionException;

/**
 * Class WpmlSpecific
 *
 * @package JtlWooCommerceConnector\Integrations\Plugins\Wpml
 */
class WpmlSpecific extends AbstractComponent
{
    /**
     * @return int
     * @throws InvalidArgumentException
     */
    public function getStats(): int
    {
        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin = $this->getCurrentPlugin();

        $wpdb = $wpmlPlugin->getWpDb();
        $wat  = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
        $jcls = $wpdb->prefix . 'jtl_connector_link_specific';

        $sql = \sprintf("
            SELECT COUNT(at.attribute_id)
            FROM {$wat} at
            LEFT JOIN {$jcls} l ON at.attribute_id = l.endpoint_id
            WHERE l.host_id IS NULL;");

        return (int)$this->getCurrentPlugin()->getPluginsManager()->getDatabase()->queryOne($sql);
    }


    /**
     * @param Specific $specific
     * @param string   $name
     * @return void
     * @throws Exception
     */
    public function getTranslations(Specific $specific, string $name): void
    {
        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin = $this->getCurrentPlugin();
        $languages  = $wpmlPlugin->getActiveLanguages();

        foreach ($languages as $languageCode => $language) {
            $translatedName = \apply_filters('wpml_translate_single_string', $name, 'WordPress', $name, $languageCode);
            if ($translatedName !== $name) {
                $specific->addI18n(
                    (new SpecificI18nModel())
                        ->setLanguageISO($wpmlPlugin->convertLanguageToWawi((string)$languageCode))
                        ->setName($translatedName)
                );
            }
        }
    }

    /**
     * @param Specific          $specific
     * @param SpecificI18nModel $defaultTranslation
     * @return void
     * @throws InjectionException
     * @throws Exception
     */
    public function setTranslations(Specific $specific, SpecificI18nModel $defaultTranslation): void
    {
        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin = $this->getCurrentPlugin();

        foreach ($specific->getI18ns() as $specificI18n) {
            $languageCode = $wpmlPlugin->convertLanguageToWpml($specificI18n->getLanguageISO());
            if ($wpmlPlugin->getDefaultLanguage() === $languageCode) {
                continue;
            }

            $translatedName = \apply_filters(
                'wpml_translate_single_string',
                $defaultTranslation->getName(),
                'WordPress',
                $specificI18n->getName(),
                $languageCode
            );

            if ($translatedName !== $specificI18n->getName()) {
                \icl_register_string(
                    'WordPress',
                    \sprintf('taxonomy singular name: %s', $defaultTranslation->getName()),
                    $specificI18n->getName(),
                    false,
                    $languageCode
                );
            }
        }
    }

    /**
     * @param string $specificName
     * @return array<int, array<string, int|string>>|null
     * @throws InvalidArgumentException
     */
    public function getValues(string $specificName): ?array
    {
        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin   = $this->getCurrentPlugin();
        $wpdb         = $wpmlPlugin->getWpDb();
        $jclsv        = $wpdb->prefix . 'jtl_connector_link_specific_value';
        $iclt         = $wpdb->prefix . 'icl_translations';
        $languageCode = $wpmlPlugin->getDefaultLanguage();
        $elementType  = 'tax_' . \esc_sql($specificName);

        /** @var array<int, array<string, int|string>>|null $values */
        $values = $this->getPluginsManager()->getDatabase()->query(
            "SELECT t.term_id, t.name, tt.term_taxonomy_id, tt.taxonomy, t.slug, tt.description
                FROM {$wpdb->terms} t
                  LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                  LEFT JOIN {$jclsv} lsv ON t.term_id = lsv.endpoint_id
                  LEFT JOIN {$iclt} wpmlt ON t.term_id = wpmlt.element_id
                WHERE lsv.host_id IS NULL
                AND tt.taxonomy LIKE '{$specificName}'
                AND wpmlt.element_type = '{$elementType}'
                AND wpmlt.source_language_code IS NULL
                AND wpmlt.language_code = '{$languageCode}'
                ORDER BY tt.parent ASC;"
        );

        return $values;
    }

    /**
     * @param string $specificName
     * @return bool
     */
    public function isTranslatable(string $specificName): bool
    {
        /** @var Wpml $wpmlPlugin */
        $wpmlPlugin = $this->getCurrentPlugin();
        $attributes = $wpmlPlugin->getWcml()->get_setting('attributes_settings');
        return isset($attributes[$specificName]) && (int)$attributes[$specificName] === 1;
    }
}
