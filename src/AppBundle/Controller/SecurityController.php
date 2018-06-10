<?php
/**
 * Created by PhpStorm.
 * User: Jakob
 * Date: 2018-06-09
 * Time: 12:44
 */

namespace AppBundle\Controller;


use AppBundle\Form\LoginForm;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
        /**
         * @var AuthenticationUtils $authenticationUtils
         */
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $loginForm = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername
        ]);

        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'form'     => $loginForm->createView(),
                'error'         => $error,
            )
        );
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction(Request $request){
        throw new \Exception('This should not be reached');
    }
}