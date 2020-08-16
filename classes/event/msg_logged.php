<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class msg_logged extends \core\event\base {

	protected function init() {
		$this->data ['crud'] = 'c';
		$this->data ['edulevel'] = self::LEVEL_OTHER;
	}

	public static function get_name() {
		return get_string ( 'msg2log', 'local_ws_rashim' );
	}

	public function get_description() {
		return $this->other ['message'];
	}
}

?>