<?php

namespace AppBundle\Interactive;

use AppBundle\Entity\InteractiveInvitation;
use AppBundle\Mailer\Message\InteractiveMessage;
use AppBundle\Mailer\MailerService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class InteractiveProcessorHandler
{
    const SESSION_KEY = 'purchasing_power';

    private $builder;
    private $manager;
    private $mailer;
    private $stateMachine;

    public function __construct(
        InteractiveMessageBodyBuilder $builder,
        ObjectManager $manager,
        MailerService $mailer,
        StateMachine $stateMachine
    ) {
        $this->builder = $builder;
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->stateMachine = $stateMachine;
    }

    public function start(SessionInterface $session): InteractiveProcessor
    {
        return $session->get(self::SESSION_KEY, new InteractiveProcessor());
    }

    public function save(SessionInterface $session, InteractiveProcessor $processor): void
    {
        $session->set(self::SESSION_KEY, $processor);
    }

    public function terminate(SessionInterface $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    public function getCurrentTransition(InteractiveProcessor $processor): string
    {
        return current($this->stateMachine->getEnabledTransitions($processor))->getName();
    }

    /**
     * Returns whether the process is finished or not.
     */
    public function process(SessionInterface $session, InteractiveProcessor $processor): ?InteractiveInvitation
    {
        if ($this->stateMachine->can($processor, InteractiveProcessor::TRANSITION_SEND)) {
            // End process
            $processor->refreshChoices($this->manager); // merge objects from session before mapping them in the entity
            $purchasingPower = InteractiveInvitation::createFromProcessor($processor);

            $this->manager->persist($purchasingPower);
            $this->manager->flush();

            $this->mailer->sendMessage(InteractiveMessage::createFromInvitation($purchasingPower));
            $this->terminate($session);
            $this->stateMachine->apply($processor, InteractiveProcessor::TRANSITION_SEND);

            return $purchasingPower;
        }

        // Continue processing
        $this->stateMachine->apply($processor, $this->getCurrentTransition($processor));

        if ($this->stateMachine->can($processor, InteractiveProcessor::TRANSITION_SEND)) {
            $this->builder->buildMessageBody($processor);
        }

        $this->save($session, $processor);

        return null;
    }
}