<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $options = [
            'cost' => 12,
        ];        
        $user = new User();
        $user->setUsername('soum@');
        $user->setEmail('soum@yahoo.com');
        $user->setPhoneNumber('67988610');  
        $user->setPassword(password_hash('aaaaaaaa', PASSWORD_BCRYPT, $options));
        $user->setEnabled(true);

        $this->manager = $manager;
        $this->manager->persist($user);
        $this->manager->flush();
    }
}
