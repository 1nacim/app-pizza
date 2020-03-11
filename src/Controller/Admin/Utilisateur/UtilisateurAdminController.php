<?php

namespace App\Controller\Admin\Utilisateur;

use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UtilisateurAdminController extends EasyAdminController
{
    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    /**
     * UtilisateurAdminController constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param $user
     */
    public function persistUserEntity($user)
    {
        $this->updatePassword($user);
        parent::persistEntity($user);
    }

    /**
     * @param $user
     */
    public function updateUserEntity($user)
    {
        $this->updatePassword($user);
        parent::updateEntity($user);
    }

    /**
     * @param Utilisateur $user
     */
    public function updatePassword(Utilisateur $user)
    {
        if (!empty($user->getPlainPassword())) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        }
    }
}
