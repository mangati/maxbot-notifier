<?php

namespace Mangati\Notifier\Maxbot\Dto;

use JsonSerializable;

final class GetContactRequest implements JsonSerializable
{

	public function __construct(
        public string $phone,
    ) {}


	public function jsonSerialize() {
        return [
            "cmd" => "get_contact",
            "whatsapp" => $this->phone,
        ];
	}
}
