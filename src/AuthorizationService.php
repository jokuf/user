<?php


namespace Jokuf\User;


use Firebase\JWT\JWT;
use Jokuf\User\Authorization\AuthorizationInterface;
use Jokuf\User\Infrastructure\Repository\UserRepository;
use Jokuf\User\User\UserInterface;
use Jokuf\User\User\UserRepositoryInterface;


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

    /**
     * AuthorizationService constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->storage = [];
    }

    public function getUserFromToken(?string $serializedToken=''): UserInterface
    {
        try {
            $payload = JWT::decode($serializedToken, JWT_SECRET, ['HS256']);

            return $this->userRepository->findByEmail($payload->identity);
        } catch (\Exception $e) {
        }

        return new AnonymousUser();
    }

    public function createTokenFor(UserInterface $user): string
    {
        $serializedToken = $this->getToken($user);

        if ($serializedToken) {
            try {
                JWT::decode($serializedToken, JWT_SECRET, ['HS256']);

                return $serializedToken;
            } catch (\Exception $e) {}
        }

        $serializedToken = JWT::encode(
            [
                "iss" => $_SERVER['HTTP_HOST'] ?? 'local',
                "aud" => $_SERVER['HTTP_HOST'] ?? 'local',
                "iat" => time(),
                'identity' => $user->getEmail()
            ],
            JWT_SECRET,
            'HS256');

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