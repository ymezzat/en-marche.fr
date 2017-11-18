<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentAccountConfirmationMessage;
use AppBundle\Security\AuthenticationUtils;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $mailer;
    private $authenticator;

    public function __construct(
        AdherentManager $adherentManager,
        MailerService $mailer,
        AuthenticationUtils $authenticator
    ) {
        $this->adherentManager = $adherentManager;
        $this->mailer = $mailer;
        $this->authenticator = $authenticator;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token)
    {
        $this->adherentManager->activateAccount($adherent, $token);
        $this->authenticator->authenticateAdherent($adherent);

        $this->mailer->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent(
            $adherent,
            $this->adherentManager->countActiveAdherents()
        ));
    }
}
