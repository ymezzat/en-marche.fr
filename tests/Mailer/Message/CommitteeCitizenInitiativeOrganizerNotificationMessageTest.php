<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CommitteeCitizenInitiativeOrganizerNotificationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class CommitteeCitizenInitiativeOrganizerNotificationMessageTest extends AbstractEventMessageTest
{
    const CONTACT_ADHERENT_URL = 'https://enmarche.dev/espace-adherent/contacter/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4?from=committee&id=464d4c23-cf4c-4d3a-8674-a43910da6419';

    public function testCreate()
    {
        $message = CommitteeCitizenInitiativeOrganizerNotificationMessage::create(
            $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron'),
            $this->createCommitteeFeedItemMock(
                $this->createAdherentMock('kl@exemple.com', 'Kévin', 'Lafont'),
                'Cette initiative est superbe !',
                $this->createCitizenInitiativeMock(
                    'Apprenez à sauver des vies',
                    '2017-02-01 15:30:00',
                    '15 allées Paul Bocuse',
                    '69006-69386',
                    'apprenez-a-sauver-des-vies',
                    $this->createAdherentMock('jb@exemple.com', 'Jean', 'Baptise')
                ),
                'Comité En Marche'
            ),
            self::CONTACT_ADHERENT_URL
        );

        $this->assertInstanceOf(CommitteeCitizenInitiativeOrganizerNotificationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Kévin',
                'animator_contact_link' => self::CONTACT_ADHERENT_URL,
                'committee_name' => 'Comité En Marche',
                'IC_name' => 'Apprenez à sauver des vies',
            ],
            $message->getVars()
        );

        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);

        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'animator_firstname' => 'Kévin',
                'animator_contact_link' => self::CONTACT_ADHERENT_URL,
                'committee_name' => 'Comité En Marche',
                'IC_name' => 'Apprenez à sauver des vies',
                'prenom' => 'Émmanuel',
            ],
            $recipient->getVars()
        );
    }
}
