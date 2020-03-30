<?php

namespace App\Service\Email;

use App\Entity\User;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 *
 */
class Mailer
{
    private $mailer;

    public function __construct( \Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationEmail(User $user): void
    {
        $message = (new \Swift_Message('Hello, Welcome in Météo !'));
        $message->setFrom('diakitesoumaila182@gmail.com')
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setSubject('Météo Account Activation !!!')
                ->setBody(
                    '<!doctype html>
                    <html>
                        <head>
                        <meta name="viewport" content="width=device-width" />
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                        <title>Inscription plateforme météo</title>
                        </head>
                        <body class="">'.
                        '<p>Bonjour M/Mme/Mlle '.$user->getFirstName().' '.$user->getLastName().', comment vous allez ? J\'espère bien !</p>
                        <p>Vous venez de vous inscrire sur la plateforme météo. Pour activer votre compte, cliquez sur le lien ci-dessous.</p>'.
                        '<a href="http://localhost:1025/v1/rest/users?token='.$user->getConfirmationToken().'" target="_blank">Activer votre compte</a>'.
                        '<p>Vous informez sur le climat est notre priorité majeure !</p>'.
                    '  </body>
                    </html>'
                    );
        $this->mailer->send($message);         
    }
    
}
