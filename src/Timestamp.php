<?php
namespace ZeroReactExamples\Utilits;

class Timestamp {
	public function getTime() {
		$now = \DateTime::createFromFormat('U.u', microtime(true));
		return $now->format("H:i:s.u");
	}
}