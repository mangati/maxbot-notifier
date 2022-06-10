<?php

namespace Mangati\Notifier\Maxbot\Dto;

use JsonSerializable;

final class Contact implements JsonSerializable
{

	public function __construct(
        public string $phone,
        public string $name,
        public string $surname,
    ) {}


	public function jsonSerialize(): array {
        return [
            "phone" => $this->phone,
            "name" => $this->name,
            "surname" => $this->surname,
        ];
	}
}
