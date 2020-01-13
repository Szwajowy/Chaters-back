<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\User;
use App\Repository\UserRepository;

class SecurityController extends ApiController
{
    private $encoder;
    private $userRepository;

    public function __construct(UserPasswordEncoderInterface $encoder, UserRepository $userRepository)
    {
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/login", methods={"GET,POST"}, name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $user = $this->getUser();

        return $this->respond([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/register", methods={"POST"}, name="register")
     */
     public function register(Request $request, EntityManagerInterface $em) {
        $requestBody = $this->transformJsonBody($request);

        if(!$requestBody) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if((!$requestBody->get('login')) || (!$requestBody->get('password')) || (!$requestBody->get('email'))) {
            return $this->respondValidationError('Please provide a proper login, password and email!');
        }

        $user = new User;
        $user->setUsername($request->get('login'));
        $user->setPassword(
            $this->encoder->encodePassword(
                $user,
                $request->get('password')
            )
        );
        $user->setEmail($request->get('email'));
        if($request->get('forename'))
            $user->setForename($request->get('forename'));
        if($request->get('surname'))
            $user->setSurname($request->get('surname'));

        if($this->userRepository->isTaken($user))
            return $this->respondValidationError('Login or email is already taken!');

        $em->persist($user);
        $em->flush();

        return $this->respondCreated($this->userRepository->transform($user));
    }
}
