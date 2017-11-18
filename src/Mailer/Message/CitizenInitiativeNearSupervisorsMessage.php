<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeNearSupervisorsMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]        $recipients
     * @param Adherent          $organizer
     * @param CitizenInitiative $citizenInitiative
     * @param string            $citizenInitiativeLink
     *
     * @return CitizenInitiativeNearSupervisorsMessage
     */
    public static function create(
        array $recipients,
        Adherent $organizer,
        CitizenInitiative $citizenInitiative,
        string $citizenInitiativeLink
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $message = new static(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            self::getTemplateVars(
                $organizer->getFirstName(),
                $organizer->getLastName(),
                $citizenInitiative->getName(),
                self::formatDate($citizenInitiative->getBeginAt(), 'EEEE d MMMM y'),
                sprintf(
                    '%sh%s',
                    self::formatDate($citizenInitiative->getBeginAt(), 'HH'),
                    self::formatDate($citizenInitiative->getBeginAt(), 'mm')
                ),
                $citizenInitiative->getInlineFormattedAddress(),
                $citizenInitiativeLink
            ),
            self::getRecipientVars($recipient->getFirstName()),
            $organizer->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                self::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(
        string $organizerFirstName,
        string $organizerLastName,
        string $citizenInitiativeName,
        string $citizenInitiativeDate,
        string $citizenInitiativeHour,
        string $citizenInitiativeAddress,
        string $citizenInitiativeLink
    ): array {
        return [
            'IC_organizer_firstname' => self::escape($organizerFirstName),
            'IC_organizer_lastname' => self::escape($organizerLastName),
            'IC_name' => self::escape($citizenInitiativeName),
            'IC_date' => $citizenInitiativeDate,
            'IC_hour' => $citizenInitiativeHour,
            'IC_address' => self::escape($citizenInitiativeAddress),
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
