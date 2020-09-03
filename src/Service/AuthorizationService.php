<?php


namespace Jokuf\User\Service;


use Firebase\JWT\JWT;
use Jokuf\Contract\Authorization\AuthorizationInterface;
use Jokuf\Contract\User\UserInterface;
use Jokuf\User\Core\Entity\AnonymousUser;
use Jokuf\User\Interactor\UserInteractor;


if (!defined('JWT_SECRET'))
    define('JWT_SECRET', hash('sha3-512' , 'pass'));

if (!defined('JWT_EXPIRE_TIME'))
    define('JWT_EXPIRE_TIME', 45 * 60);


class AuthorizationService implements AuthorizationInterface
{
    private $storage;
    /**
     * @var UserInteractor
     */
    private $userService;

    /**
     * AuthorizationService constructor.
     * @param UserInteractor $userService
     */
    public function __construct(UserInteractor $userService)
    {
        $this->userService = $userService;
        $this->storage = [];
    }

    public function getUserFromToken(?string $serializedToken=''): UserInterface
    {
        try {
            $payload = JWT::decode($serializedToken, JWT_SECRET, ['HS256']);

            if ($user = $this->userService->findByEmail($payload->identity) ) {
                return $user;
            }
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
                "exp" => time() + JWT_EXPIRE_TIME,
                'identity' => $user->getEmail()
            ],
            JWT_SECRET,
            'HS256');

        $this->storeToken($user, $serializedToken);

        return $serializedToken;
    }

    public function revokeToken(string $token): void
    {
        if (isset($this->storage[$token])){
            unset($this->storage[$token]);
        }
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