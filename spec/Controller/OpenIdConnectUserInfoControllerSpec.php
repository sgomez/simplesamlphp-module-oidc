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

use League\OAuth2\Server\ResourceServer;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\Modules\OpenIDConnect\Controller\OpenIdConnectUserInfoController;
use SimpleSAML\Modules\OpenIDConnect\Entity\AccessTokenEntity;
use SimpleSAML\Modules\OpenIDConnect\Entity\UserEntity;
use SimpleSAML\Modules\OpenIDConnect\Repositories\AccessTokenRepository;
use SimpleSAML\Modules\OpenIDConnect\Repositories\UserRepository;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class OpenIdConnectUserInfoControllerSpec extends ObjectBehavior
{
    public function let(
        ResourceServer $resourceServer,
        AccessTokenRepository $accessTokenRepository,
        UserRepository $userRepository
    ) {
        $this->beConstructedWith($resourceServer, $accessTokenRepository, $userRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OpenIdConnectUserInfoController::class);
    }

    public function it_returns_user_claims(
        ServerRequest $request,
        ServerRequestInterface $authorization,
        ResourceServer $resourceServer,
        AccessTokenRepository $accessTokenRepository,
        AccessTokenEntity $accessTokenEntity,
        UserRepository $userRepository,
        UserEntity $userEntity
    ) {
        $resourceServer->validateAuthenticatedRequest($request)->shouldBeCalled()->willReturn($authorization);
        $authorization->getAttribute('oauth_access_token_id')->shouldBeCalled()->willReturn('tokenid');
        $authorization->getAttribute('oauth_scopes')->shouldBeCalled()->willReturn(['openid', 'email']);

        $accessTokenRepository->findById('tokenid')->shouldBeCalled()->willReturn($accessTokenEntity);
        $accessTokenEntity->getUserIdentifier()->shouldBeCalled()->willReturn('userid');
        $userRepository->getUserEntityByIdentifier('userid')->shouldBeCalled()->willReturn($userEntity);
        $userEntity->getClaims()->shouldBeCalled()->willReturn(['mail' => ['userid@localhost.localdomain']]);

        $this->__invoke($request)->shouldHavePayload(['email' => 'userid@localhost.localdomain']);
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
