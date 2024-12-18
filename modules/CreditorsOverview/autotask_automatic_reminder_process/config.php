<?php
$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

$automatic_reminder_sending_time = $collecting_system_settings['automatic_reminder_sending_time'];
$v_auto_task_config = array(
	'runtime_y' => null,
	'runtime_m' => null,
	'runtime_d' => null,
	'runtime_h' => '07',
	'runtime_i' => '00',
	'repeat_minutes' => 1440, // 1 - one minute, 60 - one hour, 1440 - one day
	'parameters' => array(
		'time' => array(
			'input' => 1,
			'value' => $automatic_reminder_sending_time,
		)
	),
);
