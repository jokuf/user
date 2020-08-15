<?php


namespace Jokuf\User;


use DateTime;
use Emarref\Jwt\Algorithm\Hs256;
use Emarref\Jwt\Encryption\Factory;
use Emarref\Jwt\Jwt;
use Emarref\Jwt\Token;
use Emarref\Jwt\Claim;
use Emarref\Jwt\Verification\Context;
use Jokuf\User\Authorization\AuthorizationInterface;
use Jokuf\User\Infrastructure\Repository\UserRepository;
use Jokuf\User\User\UserInterface;


if(!defined('JWT_SECRET'))
    define('JWT_SECRET', password_hash('verysecret', PASSWORD_BCRYPT, ['cost' => 12]));

if (!defined('JWT_EXPIRE_TIME'))
    define('JWT_EXPIRE_TIME', '45 minutes');


class AuthorizationService implements AuthorizationInterface
{
    private $storage;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->storage = [];
    }

    public function getUserFromToken(?string $serializedToken=''): UserInterface
    {
        if (null === $serializedToken || empty($serializedToken)) {
            return new AnonymousUser();
        }

        $jwt        = new Jwt();
        $algorithm  = new Hs256(JWT_SECRET);
        $encryption = Factory::create($algorithm);
        try {
            $context    = new Context($encryption);
            $token      = $jwt->deserialize($serializedToken);
            $jwt->verify($token, $context);

            $userIdClaim = $token->getPayload()->findClaimByName('userId');

            $identity = $userIdClaim->getValue();

            return $this->userRepository->findById($identity);
        } catch (\Exception $e) {
            return new AnonymousUser();
        }
    }

    public function createTokenFor(UserInterface $user): string
    {
        $jwt        = new Jwt();
        $algorithm  = new Hs256(JWT_SECRET);
        $encryption = Factory::create($algorithm);
        $serializedToken = $this->getToken($user);

        if ($serializedToken) {
            try {
                $context    = new Context($encryption);
                $token      = $jwt->deserialize($serializedToken);
                $jwt->verify($token, $context);

                return $serializedToken;
            } catch (\Exception $e) {}
        }

        $token = new Token();
        $token->addClaim(new Claim\Expiration(new DateTime(JWT_EXPIRE_TIME)));
        $token->addClaim(new Claim\IssuedAt(new DateTime('now')));
        $token->addClaim(new Claim\Issuer(__METHOD__));
        $token->addClaim(new Claim\PrivateClaim('identity', $user->getIdentity()));

        $serializedToken = $jwt->serialize($token, $encryption);

        $this->storeToken($user, $serializedToken);

        return $serializedToken;
    }

    public function revokeToken(string $token): void
    {
    }

    private function getToken(UserInterface $user): ?string
    {
        return current(array_keys($this->storage, $user, true));
    }

    private function storeToken(UserInterface $user, string $token)
    {
        $this->storage[$token] = $user;
    }

    public function authorize(UserInterface $user, string $url, string $method): bool
    {
        foreach ($user->getRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                foreach ($permission->getActivities() as $activity) {
                    $pattern = $activity->getRegex();
                    if ($method === $activity->getMethod() && 1 === preg_match("#$pattern#", $url)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}