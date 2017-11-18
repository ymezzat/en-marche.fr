<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\CitizenInitiativeRegistrationConfirmationMessage;
use AppBundle\Mailer\Message\Message;

class CitizenInitiativeRegistrationConfirmationMessageTest extends AbstractEventMessageTest
{
    const CITIZEN_INITIATIVE_LINK = 'http://en-marche.fr/initiative-citoyenne/2017-12-27-initiative-citoyenne-a-lyon';

    public function testCreateMessageFromEventRegistration()
    {
        $organizer = $this->createAdherentMock('michelle.doe@example.com', 'Michelle', 'Doe');

        $event = $this->createEventMock('Grand Meeting de Paris', '2017-02-01 15:30:00', 'Palais des Congrés, Porte Maillot', '75001-75101', 'EM Paris');
        $event->expects($this->any())->method('getOrganizer')->willReturn($organizer);

        $registration = $this->createMock(EventRegistration::class);
        $registration->expects($this->any())->method('getEvent')->willReturn($event);
        $registration->expects($this->any())->method('getFirstName')->willReturn('John');
        $registration->expects($this->any())->method('getEmailAddress')->willReturn('john@bar.com');

        $message = CitizenInitiativeRegistrationConfirmationMessage::createFromRegistration($registration, self::CITIZEN_INITIATIVE_LINK);

        $this->assertInstanceOf(CitizenInitiativeRegistrationConfirmationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'IC_name' => 'Grand Meeting de Paris',
                'IC_organizer_firstname' => 'Michelle',
                'IC_organizer_lastname' => 'Doe',
                'IC_link' => self::CITIZEN_INITIATIVE_LINK,
            ],
            $message->getVars()
        );
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);

        $this->assertSame('john@bar.com', $recipient->getEmailAddress());
        $this->assertSame('John', $recipient->getFullName());
        $this->assertSame(
            [
                'IC_name' => 'Grand Meeting de Paris',
                'IC_organizer_firstname' => 'Michelle',
                'IC_organizer_lastname' => 'Doe',
                'IC_link' => self::CITIZEN_INITIATIVE_LINK,
                'prenom' => 'John',
            ],
            $recipient->getVars()
        );
    }
}
