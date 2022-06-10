<?php

namespace Mangati\Notifier\Maxbot\Dto;

use JsonSerializable;

final class PutContactRequest implements JsonSerializable
{

	public function __construct(
        public Contact $contact,
    ) {}


	public function jsonSerialize() {
        return [
            "cmd" => "put_contact",
            "whatsapp" => $this->contact->phone,
            "name" => $this->contact->name,
            "surname" => $this->contact->surname,
        ];
	}
}
