<?php

namespace Mangati\Notifier\Maxbot\Dto;

use JsonSerializable;

final class SendTextRequest implements JsonSerializable
{

	public function __construct(
        public string $phone,
        public string $message,
    ) {}


	public function jsonSerialize() {
        return [
            "cmd" => "send_text",
            "ct_whatsapp" => $this->phone,
            "msg" => $this->message,
        ];
	}
}
