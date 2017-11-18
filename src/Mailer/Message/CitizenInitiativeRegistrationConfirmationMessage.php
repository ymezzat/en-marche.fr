<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeRegistrationConfirmationMessage extends Message
{
    public static function createFromRegistration(EventRegistration $registration, string $citizenInitiativeLink): self
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();
        $organizer = $event->getOrganizer();

        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $firstName,
            self::getTemplateVars(
                $event->getName(),
                $organizer->getFirstName(),
                $organizer->getLastName(),
                $citizenInitiativeLink
            ),
            self::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(
        string $initiativeName,
        string $referentFirstName,
        string $referentLastName,
        string $citizenInitiativeLink
    ): array {
        return [
            'IC_name' => self::escape($initiativeName),
            'IC_organizer_firstname' => self::escape($referentFirstName),
            'IC_organizer_lastname' => self::escape($referentLastName),
            'IC_link' => $citizenInitiativeLink,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
