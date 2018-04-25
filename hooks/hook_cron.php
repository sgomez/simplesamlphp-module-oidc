<?php

/*
 * This file is part of the simplesamlphp-module-oidc.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleSAML\Modules\OpenIDConnect\Repositories\AccessTokenRepository;
use SimpleSAML\Modules\OpenIDConnect\Repositories\AuthCodeRepository;
use SimpleSAML\Modules\OpenIDConnect\Repositories\RefreshTokenRepository;

function oidc_hook_cron(&$croninfo)
{
    assert('is_array($croninfo)');
    assert('array_key_exists("summary", $croninfo)');
    assert('array_key_exists("tag", $croninfo)');

    $oidcConfig = SimpleSAML_Configuration::getOptionalConfig('module_oidc.php');

    if (null === $oidcConfig->getValue('cron_tag', 'hourly')) {
        return;
    }
    if ($oidcConfig->getValue('cron_tag', null) !== $croninfo['tag']) {
        return;
    }

    try {
        $accessTokenRepository = new AccessTokenRepository();
        $accessTokenRepository->removeExpired();

        $authTokenRepository = new AuthCodeRepository();
        $authTokenRepository->removeExpired();

        $refreshTokenRepository = new RefreshTokenRepository();
        $refreshTokenRepository->removeExpired();

        $croninfo['summary'][] = 'OpenID Connect clean up. Removed expired entries from OpenID Connect storage.';
    } catch (Exception $e) {
        $message = 'OpenID Connect clean up cron script failed: '.$e->getMessage();
        \SimpleSAML_Logger::warning($message);
        $croninfo['summary'][] = $message;
    }
}
