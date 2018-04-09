<?php

/*
 * This file is part of the simplesamlphp-module-oidc.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\SimpleSAML\Modules\OpenIDConnect\Controller;

use PhpSpec\ObjectBehavior;
use SimpleSAML\Modules\OpenIDConnect\Controller\OpenIDConnectController;
use SimpleSAML\Modules\OpenIDConnect\Repositories\ClientRepository;
use SimpleSAML\Modules\OpenIDConnect\Services\ConfigurationService;
use SimpleSAML\Modules\OpenIDConnect\Services\Container;
use SimpleSAML\Modules\OpenIDConnect\Services\JsonWebKeySetService;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class OpenIDConnectControllerSpec extends ObjectBehavior
{
    public function let(
        Container $container,
        ClientRepository $clientRepository,
        JsonWebKeySetService $jsonWebKeySet,
        ConfigurationService $configurationService)
    {
        $this->beConstructedWith($container);

        $container->get(ClientRepository::class)->willReturn($clientRepository);
        $container->get(JsonWebKeySetService::class)->willReturn($jsonWebKeySet);
        $container->get(ConfigurationService::class)->willReturn($configurationService);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OpenIDConnectController::class);
    }

    public function it_returns_json_keys(
        ServerRequest $request,
        JsonWebKeySetService $jsonWebKeySet
    ) {
        $keys = [
            0 => [
                'kty' => 'RSA',
                'n' => 'n',
                'e' => 'e',
                'use' => 'sig',
                'alg' => 'RS256',
            ],
        ];

        $jsonWebKeySet->keys()->shouldBeCalled()->willReturn($keys);

        $this->jwks($request)->shouldHavePayload(['keys' => $keys]);
    }

    public function it_returns_openid_connect_configuration(
        ServerRequest $request,
        ConfigurationService $configurationService,
        \SimpleSAML_Configuration $oidcConfiguration
    ) {
        $configurationService->getOpenIDConnectConfiguration()->shouldBeCalled()->willReturn($oidcConfiguration);
        $oidcConfiguration->getArray('scopes')->shouldBeCalled()->willReturn(['openid' => 'openid']);
        $oidcConfiguration->getBoolean('pkce')->shouldBeCalled()->willReturn(true);

        $configurationService->getSimpleSAMLSelfURLHost()->shouldBeCalled()->willReturn('http://localhost');
        $configurationService->getOpenIdConnectModuleURL('authorize.php')->willReturn('http://localhost/authorize.php');
        $configurationService->getOpenIdConnectModuleURL('access_token.php')->willReturn('http://localhost/access_token.php');
        $configurationService->getOpenIdConnectModuleURL('userinfo.php')->willReturn('http://localhost/userinfo.php');
        $configurationService->getOpenIdConnectModuleURL('jwks.php')->willReturn('http://localhost/jwks.php');

        $this->configuration($request)->shouldHavePayload([
            'issuer' => 'http://localhost',
            'authorization_endpoint' => 'http://localhost/authorize.php',
            'token_endpoint' => 'http://localhost/access_token.php',
            'userinfo_endpoint' => 'http://localhost/userinfo.php',
            'jwks_uri' => 'http://localhost/jwks.php',
            'scopes_supported' => ['openid'],
            'response_types_supported' => ['code', 'token', 'id_token token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'code_challenge_methods_supported' => ['plain', 'S256'],
        ]);
    }

    public function getMatchers(): array
    {
        return [
            'havePayload' => function (JsonResponse $subject, $payload) {
                return $payload === $subject->getPayload();
            },
        ];
    }
}
