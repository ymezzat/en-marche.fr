<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends Message
{
    public static function create(
        ProcurationRequest $request,
        ProcurationProxy $proxy,
        ?Adherent $procurationManager
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            self::getTemplateVars(
                $request->getFirstNames(),
                $proxy->getFirstNames(),
                $proxy->getLastName()
            ),
            [],
            $procurationManager ? $procurationManager->getEmailAddress() : 'procurations@en-marche.fr'
        );

        $message->setSenderName('Procuration En Marche !');
        $message->addCC($proxy->getEmailAddress());

        if ($procurationManager) {
            $message->addCC($procurationManager->getEmailAddress());
        }

        return $message;
    }

    private static function getTemplateVars(
        string $targetFirstName,
        string $voterFirstName,
        string $voterLastName
    ): array {
        return [
            'target_firstname' => self::escape($targetFirstName),
            'voter_first_name' => $voterFirstName,
            'voter_last_name' => $voterLastName,
        ];
    }
}
