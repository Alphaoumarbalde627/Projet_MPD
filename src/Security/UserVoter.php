<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\Statut;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security; 

class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const DELETE = 'USER_DELETE';
    public const EDIT_SELF = 'USER_EDIT_SELF';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::DELETE, self::EDIT_SELF], true)) {
            return false;
        }

        if ($attribute === self::EDIT_SELF) {
            return $subject instanceof User;
        }

        return $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // Any admin can view user details
                return true;

            case self::DELETE:
                /** @var User $target */
                $target = $subject;

                // Cannot delete yourself
                if ($target->getId() === $user->getId()) {
                    return false;
                }

                // Admin can delete any client
                if ($target->getRole()?->value === Statut::CLIENT->value) {
                    return true;
                }

                // Admin can delete another admin only if they created them
                if ($target->getRole()?->value === Statut::ADMIN->value) {
                    return $target->getCreatedBy()?->getId() === $user->getId();
                }

                return false;

            case self::EDIT_SELF:
                /** @var User $target */
                $target = $subject;
                return $target->getId() === $user->getId();
        }

        return false;
    }
}
