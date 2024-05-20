<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccountVoter extends Voter
{
    public const EDIT = 'USER_EDIT';
    public const VIEW = 'USER_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                return ($user->getId() === $subject->getId()) || ($user->getRoles()[0] === 'ROLE_TEACHER') || ($user->getRoles()[0] === 'ROLE_ADMIN');
                break;

            case self::VIEW:
                // logic to determine if the user can VIEW
                return ($user->getId() === $subject->getId()) || ($user->getRoles()[0] === 'ROLE_TEACHER') || ($user->getRoles()[0] === 'ROLE_ADMIN');
                break;
        }

        return false;
    }
}
