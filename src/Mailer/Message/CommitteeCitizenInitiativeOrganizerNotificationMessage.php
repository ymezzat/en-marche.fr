<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

final class CommitteeCitizenInitiativeOrganizerNotificationMessage extends Message
{
    public static function create(Adherent $recipient, CommitteeFeedItem $feedItem, string $contactLink): self
    {
        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            self::getTemplateVars(
                $feedItem->getAuthor()->getFirstName(),
                $contactLink,
                $feedItem->getCommittee()->getName(),
                $feedItem->getEvent()->getName()
            ),
            self::getRecipientVars($recipient->getFirstName())
        );

        return $message;
    }

    private static function getTemplateVars(
        string $referentFirstName,
        string $contactLink,
        string $committeeName,
        string $initiativeName
    ): array {
        return [
            'animator_firstname' => self::escape($referentFirstName),
            'animator_contact_link' => $contactLink,
            'committee_name' => self::escape($committeeName),
            'IC_name' => $initiativeName,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
