<?php


namespace Jokuf\User;


use Jokuf\User\Domain\Entity\Permission;
use Jokuf\User\Domain\Entity\User;

class AuthorizationService
{

    public function findOrCreate(?string $token=null): string
    {
        return "";
    }

    public function authorize(User $user, string $url, string $method): bool
    {
        foreach ($user->getPermissions() as $permission) {
            foreach ($permission->getActivities() as $activity) {
                $pattern = $activity->getRegex();
                if ($method === $activity->getMethod() && 1 === preg_match("#$pattern#", $url)) {
                    return true;
                }
            }
        }

        return false;
    }
}