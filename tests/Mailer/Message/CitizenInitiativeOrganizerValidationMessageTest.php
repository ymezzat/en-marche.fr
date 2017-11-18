<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CitizenInitiativeOrganizerValidationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class CitizenInitiativeOrganizerValidationMessageTest extends AbstractEventMessageTest
{
    public function testCreate()
    {
        $adherent[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');

        $message = CitizenInitiativeOrganizerValidationMessage::create(
            $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron'),
            $this->createCitizenInitiativeMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386', 'en-marche-lyon')
        );

        $this->assertInstanceOf(CitizenInitiativeOrganizerValidationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'IC_name' => 'En Marche Lyon',
                'IC_date' => 'mercredi 1 février 2017',
                'IC_hour' => '15h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
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
                'IC_name' => 'En Marche Lyon',
                'IC_date' => 'mercredi 1 février 2017',
                'IC_hour' => '15h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'prenom' => 'Émmanuel',
            ],
            $recipient->getVars()
        );
    }
}
