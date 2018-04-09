<?php

/*
 * This file is part of the simplesamlphp-module-oidc.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleSAML\Modules\OpenIDConnect\Entity;

use League\OAuth2\Server\Entities\UserEntityInterface;
use SimpleSAML\Modules\OpenIDConnect\Utils\TimestampGenerator;

class UserEntity implements UserEntityInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $claims;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    private $updatedAt;

    private function __construct()
    {
    }

    public static function fromId(string $identifier): self
    {
        $user = new self();

        $user->identifier = $identifier;
        $user->createdAt = TimestampGenerator::utc();
        $user->updatedAt = $user->createdAt;
        $user->claims = [];

        return $user;
    }

    public static function fromState(array $state): self
    {
        $user = new self();

        $user->identifier = $state['id'];
        $user->claims = json_decode($state['claims'], true);
        $user->updatedAt = TimestampGenerator::utc($state['updated_at']);
        $user->createdAt = TimestampGenerator::utc($state['created_at']);

        return $user;
    }

    public function getState(): array
    {
        return [
            'id' => $this->identifier,
            'claims' => json_encode($this->claims),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function setClaims(array $claims): self
    {
        $this->claims = $claims;
        $this->updatedAt = TimestampGenerator::utc();

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
