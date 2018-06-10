<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Form\LoginForm;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Security;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    /** @var EntityManager */
    private $em;
    /** @var Router */
    private $router;
    /** @var FormFactoryInterface */
    private $formFactory;
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    public function __construct(EntityManager $em, Router $router, FormFactoryInterface $formFactory, UserPasswordEncoder $passwordEncoder)
    {
        $this->router = $router;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getCredentials(Request $request)
    {
        $isFormSubmitted = $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');
        if(!$isFormSubmitted){
            return null;
        }

        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);
        $formFields = $form->getData();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $formFields['_username']
        );

        return $formFields;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        return  $this->em->getRepository(User::class)
                ->findOneBy(['email' => $username]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['_password'];

        if($this->passwordEncoder->isPasswordValid($user, $password)){
            return true;
        }
        return false;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // if the user hits a secure page and start() was called, this was
        // the URL they were on, and probably where you want to redirect to
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if (!$targetPath) {
            $targetPath = $this->router->generate('homepage');
        }

        return new RedirectResponse($targetPath);
    }

    private function getTargetPath(SessionInterface $session, $providerKey)
    {
        return $session->get('_security.'.$providerKey.'.target_path');
    }

}