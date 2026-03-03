<?php

declare(strict_types=1);

namespace Endereco\Oxid7Client\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Theme;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Eshop\Core\ViewConfig;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;

/**
 * Builds Endereco frontend config for JSON API (no config in HTML source).
 */
class EnderecoConfigProvider
{
    /**
     * Returns the full integrator config as array (for JSON response).
     */
    public function getConfig(): array
    {
        $oConfig = Registry::getConfig();
        $sOxId = Registry::get(Request::class)->getRequestEscapedParameter('oxid') ?: $oConfig->getShopId();
        $languageId = (int)Registry::getLang()->getBaseLanguage();
        $lang = Registry::getLang();

        $moduleConfiguration = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleConfigurationDaoBridgeInterface::class)
            ->get('endereco-oxid7-client');

        $settings = [];
        foreach ($moduleConfiguration->getModuleSettings() as $moduleSetting) {
            $settings[$moduleSetting->getName()] = $moduleSetting->getValue();
        }
        if (isset($settings['sAllowedControllerClasses'])) {
            $settings['aAllowedControllerClasses'] = explode(',', $settings['sAllowedControllerClasses']);
        }

        $oTheme = oxNew(Theme::class);
        $oTheme->load($oTheme->getActiveThemeId());
        $settings['sThemeName'] = $oTheme->getId();

        /** @var ViewConfig $viewConfig */
        $viewConfig = oxNew(ViewConfig::class);
        $apiUrl = $viewConfig->getModuleUrl('endereco-oxid7-client', 'proxy/io.php');

        $viewNameGenerator = Registry::get(TableViewNameGenerator::class);
        $sCountryTable = $viewNameGenerator->getViewName('oxcountry', $languageId, $sOxId);
        $resultSet = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getAll(
            "SELECT `OXISOALPHA2`, `OXTITLE`, `OXID` FROM {$sCountryTable} WHERE `OXISOALPHA2` <> ''",
            []
        );
        $aCountries = [];
        $aCountryMapping = [];
        $aCountryMappingReverse = [];
        foreach ($resultSet as $result) {
            $aCountries[$result[0]] = $result[1];
            $aCountryMapping[strtoupper($result[0])] = $result[2];
            $aCountryMappingReverse[$result[2]] = strtoupper($result[0]);
        }

        $sStatesTable = $viewNameGenerator->getViewName('oxstates', $languageId, $sOxId);
        $resultSet = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getAll(
            "SELECT `MOJOISO31662`, `OXTITLE`, `OXID`, `OXISOALPHA2` FROM {$sStatesTable} WHERE `MOJOISO31662` <> '' AND `MOJOISO31662` IS NOT NULL",
            []
        );
        $aStates = [];
        $aStatesMapping = [];
        $aStatesMappingReverse = [];
        foreach ($resultSet as $result) {
            $aStates[$result[0]] = $result[1];
            $aStatesMapping[strtoupper($result[0])] = $result[2];
            $aStatesMappingReverse[$result[2]] = strtoupper($result[0]);
        }

        $texts = $this->getTexts($lang);

        return [
            'apiUrl' => $apiUrl,
            'apiKey' => $settings['sAPIKEY'] ?? '',
            'remoteApiUrl' => $settings['sSERVICEURL'] ?? '',
            'themeName' => $settings['sThemeName'] ?? '',
            'defaultCountry' => $settings['sPreselectableCountries'] ?? '',
            'defaultCountrySelect' => !empty($settings['bPreselectCountry']),
            'config' => [
                'showDebugInfo' => !empty($settings['bShowDebug']),
                'trigger' => [
                    'onblur' => !empty($settings['sAMSBLURTRIGGER']),
                    'onsubmit' => !empty($settings['sAMSSubmitTrigger']),
                ],
                'ux' => [
                    'resumeSubmit' => !empty($settings['sAMSResumeSubmit']),
                    'smartFill' => !empty($settings['sSMARTFILL']),
                    'checkExisting' => !empty($settings['sCHECKALL']),
                    'changeFieldsOrder' => !empty($settings['bChangeFieldsOrder']),
                    'showEmailStatus' => !empty($settings['bShowEmailserviceErrors']),
                    'useStandardCss' => !empty($settings['bUseCss']),
                    'allowCloseModal' => !empty($settings['bAllowCloseModal']),
                    'confirmWithCheckbox' => !empty($settings['bConfirmWithCheckbox']),
                    'correctTranspositionedNames' => !empty($settings['bCorrectTranspositionedNames']),
                ],
                'templates' => [
                    'primaryButtonClasses' => 'btn btn-primary',
                    'secondaryButtonClasses' => 'btn btn-secondary',
                ],
                'texts' => $texts,
            ],
            'activeServices' => [
                'ams' => !empty($settings['sUSEAMS']),
                'emailService' => !empty($settings['bUseEmailservice']),
                'personService' => !empty($settings['bUsePersonalService']),
            ],
            'countryCodeToNameMapping' => $aCountries,
            'countryMapping' => $aCountryMapping,
            'countryMappingReverse' => $aCountryMappingReverse,
            'subdivisionCodeToNameMapping' => $aStates,
            'subdivisionMapping' => $aStatesMapping,
            'subdivisionMappingReverse' => $aStatesMappingReverse,
        ];
    }

    private function getTexts(\OxidEsales\Eshop\Core\Language $lang): array
    {
        $t = static function (string $ident) use ($lang): string {
            return (string) $lang->translateString($ident);
        };
        return [
            'popUpHeadline' => $t('ENDERECOOXID6CLIENT_POPUP_HEADLINE'),
            'popUpSubline' => $t('ENDERECOOXID6CLIENT_POPUP_SUBLINE'),
            'mistakeNoPredictionSubline' => $t('ENDERECOOXID6CLIENT_mistakeNoPredictionSubline'),
            'notFoundSubline' => $t('ENDERECOOXID6CLIENT_notFoundSubline'),
            'confirmMyAddressCheckbox' => $t('ENDERECOOXID6CLIENT_confirmMyAddressCheckbox'),
            'yourInput' => $t('ENDERECOOXID6CLIENT_POPUP_INPUT'),
            'editYourInput' => $t('ENDERECOOXID6CLIENT_POPUP_EDITINPUT'),
            'ourSuggestions' => $t('ENDERECOOXID6CLIENT_POPUP_SUGGESTIONS'),
            'useSelected' => $t('ENDERECOOXID6CLIENT_POPUP_USE'),
            'confirmAddress' => $t('ENDERECOOXID6CLIENT_confirmAddress'),
            'editAddress' => $t('ENDERECOOXID6CLIENT_editAddress'),
            'warningText' => $t('ENDERECOOXID6CLIENT_warningText'),
            'popupHeadlines' => [
                'general_address' => $t('ENDERECOOXID6CLIENT_POPUP_HEADLINE_GENERAL_ADDRESS'),
                'billing_address' => $t('ENDERECOOXID6CLIENT_POPUP_HEADLINE_BILLING_ADDRESS'),
                'shipping_address' => $t('ENDERECOOXID6CLIENT_POPUP_HEADLINE_SHIPPING_ADDRESS'),
            ],
            'statuses' => [
                'email_not_correct' => $t('ENDERECOOXID6CLIENT_STATUS_EMAIL_NOT_CORRECT'),
                'email_cant_receive' => $t('ENDERECOOXID6CLIENT_STATUS_EMAIL_CANT_RECEIVE'),
                'email_syntax_error' => $t('ENDERECOOXID6CLIENT_STATUS_EMAIL_SYNTAX_ERROR'),
                'email_no_mx' => $t('ENDERECOOXID6CLIENT_STATUS_EMAIL_NO_MX'),
                'building_number_is_missing' => $t('ENDERECOOXID6CLIENT_STATUS_buildingNumberIsMissing'),
                'building_number_not_found' => $t('ENDERECOOXID6CLIENT_STATUS_buildingNumberNotFound'),
                'street_name_needs_correction' => $t('ENDERECOOXID6CLIENT_STATUS_streetNameNeedsCorrection'),
                'locality_needs_correction' => $t('ENDERECOOXID6CLIENT_STATUS_localityNeedsCorrection'),
                'postal_code_needs_correction' => $t('ENDERECOOXID6CLIENT_STATUS_postalCodeNeedsCorrection'),
                'country_code_needs_correction' => $t('ENDERECOOXID6CLIENT_STATUS_countryCodeNeedsCorrection'),
            ],
        ];
    }
}
