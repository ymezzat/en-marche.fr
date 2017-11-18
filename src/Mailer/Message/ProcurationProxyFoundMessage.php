<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyFoundMessage extends Message
{
    public static function create(
        Adherent $procurationManager,
        ProcurationRequest $request,
        ProcurationProxy $proxy,
        string $infosUrl
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            self::getTemplateVars(
                $request->getFirstNames(),
                $infosUrl,
                implode(', ', $request->getElections()),
                $proxy->getFirstNames(),
                $proxy->getLastName(),
                PhoneNumberFormatter::format($proxy->getPhone()),
                $request->getFirstNames(),
                $request->getLastName(),
                PhoneNumberFormatter::format($request->getPhone())
            ),
            [],
            $proxy->getEmailAddress()
        );

        $message->setSenderName('Procuration En Marche !');
        $message->addCC($procurationManager->getEmailAddress());
        $message->addCC($proxy->getEmailAddress());

        return $message;
    }

    private static function getTemplateVars(
        string $targetFirstName,
        string $infoLink,
        string $elections,
        string $voterFirstName,
        string $voterLastName,
        string $voterPhone,
        string $mandantFirstName,
        string $mandantLastName,
        string $mandantPhone
    ): array {
        return [
            'target_firstname' => self::escape($targetFirstName),
            'info_link' => $infoLink,
            'elections' => $elections,
            'voter_first_name' => self::escape($voterFirstName),
            'voter_last_name' => self::escape($voterLastName),
            'voter_phone' => $voterPhone,
            'mandant_first_name' => self::escape($mandantFirstName),
            'mandant_last_name' => self::escape($mandantLastName),
            'mandant_phone' => $mandantPhone,
        ];
    }
}
