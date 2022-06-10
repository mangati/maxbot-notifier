<?php

namespace Mangati\Notifier\Maxbot;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Mangati\Notifier\Maxbot\Dto\Contact;

final class MaxbotOptions implements MessageOptionsInterface
{

    public function __construct(
        private Contact $contact,
    ) {}

	public function getContact(): Contact
	{
		return $this->contact;
	}

	function toArray(): array {
		return [
			'recipientId' => $this->getRecipientId(),
			'contact' => $this->contact->jsonSerialize(),
		];
	}
	
	/**
	 *
	 * @return null|string
	 */
	function getRecipientId(): null|string {
		return $this->contact->phone;
	}
}
