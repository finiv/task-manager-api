<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class SecurityController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('This route is intercepted by the "login" firewall before reaching a controller. Check config/packages/security.yaml.');
    }
}
