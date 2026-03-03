<?php

declare(strict_types=1);

namespace Endereco\Oxid7Client\Controller;

use Endereco\Oxid7Client\Service\EnderecoConfigProvider;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

/**
 * Returns Endereco frontend config as JSON (no config in HTML source).
 * Protected by session/CSRF token (stoken); only valid session requests get config.
 */
class ConfigApiController extends FrontendController
{
    /**
     * Output config as JSON and stop execution.
     * Sends 403 if session or CSRF token is invalid.
     */
    public function getConfig(): void
    {
        $session = Registry::get(Session::class);
        if (!$session->checkSessionChallenge()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            echo json_encode(['error' => 'Invalid or missing session token'], JSON_THROW_ON_ERROR);
            exit;
        }

        $provider = new EnderecoConfigProvider();
        $config = $provider->getConfig();

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
