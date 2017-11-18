<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\EventInvite;
use AppBundle\Mailer\Message\CitizenInitiativeInvitationMessage;
use AppBundle\Mailer\Message\Message;

class CitizenInitiativeInvitationMessageTest extends AbstractEventMessageTest
{
    const SHOW_CITIZEN_INITIATIVE_URL = 'https://enmarche.dev/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';

    public function testCreateFromInvite()
    {
        $guests[] = 'em@example.com';
        $guests[] = 'jb@example.com';
        $guests[] = 'ml@example.com';
        $guests[] = 'ez@example.com';

        $initiative = $this->createCitizenInitiativeMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386', 'en-marche-lyon');

        $event = $this->createMock(EventInvite::class);
        $event->expects(static::any())->method('getEmail')->willReturn('em@example.com');
        $event->expects(static::any())->method('getFullName')->willReturn('Émmanuel Macron');
        $event->expects(static::any())->method('getFirstName')->willReturn('Émmanuel');
        $event->expects(static::any())->method('getMessage')->willReturn('Rendez-vous à Lyon.');
        $event->expects(static::any())->method('getGuests')->willReturn($guests);

        $message = CitizenInitiativeInvitationMessage::createFromInvite(
            $event,
            $initiative,
            self::SHOW_CITIZEN_INITIATIVE_URL
        );

        $this->assertInstanceOf(CitizenInitiativeInvitationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'sender_firstname' => 'Émmanuel',
                'sender_message' => 'Rendez-vous à Lyon.',
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SHOW_CITIZEN_INITIATIVE_URL,
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getCC());

        $recipients = $message->getCC();

        foreach ($recipients as $key => $recipient) {
            $this->assertSame($guests[$key], $recipient);
        }
    }
}
