<?php
global $wpdb;
$table_name = $wpdb->prefix . "wp_bot_counter";
$bots = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
$now_timestamp = time();
?>

<?php if(!empty($_POST['submit'])) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options Saved.') ?></strong></p></div>
<?php
$_POST['marks'] .= ' ';
$dest_marks = array();

if (preg_match_all('/(\S+)\s/', $_POST['marks'], $outs, PREG_SET_ORDER)) {
	foreach ($outs as $out) {
		$dest_marks[$out[1]] = 1;
	}
}
foreach ($bots as $key=>$bot) {
	if (!isset($dest_marks[$bot['mark']])) {
		$wpdb->query("
			DELETE FROM $table_name WHERE mark = '$bot[mark]'");
		unset($bots[$key]);
	} else {
		unset($dest_marks[$bot['mark']]);
	}
}
foreach ($dest_marks as $mark => $nouse) {
	if ($mark != '') {
		$wpdb->insert($table_name, array('mark' => $mark));
		$bots[$mark]['mark'] = $mark;
		$bots[$mark]['counter'] = 0;
		$bots[$mark]['last_timestamp'] = 0;
	}
}
?>
<?php endif; ?>


<div class="wrap">
<p><h2><?php _e('Bot Counter Status'); ?></h2></p>
<div class="narrow">
<h3><label for="marks"><?php _e('User Agent'); ?></label></h3>
<form action="" method="post" id="bot-counter-conf" style="margin: auto; width: 500px; ">
<textarea name="marks" rows="5" cols="30" id="marks" class="large-text code">
<?php
foreach ($bots as $bot) {
	echo "$bot[mark]&#13";
}
?>
</textarea>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Update UA'); ?>" /></p>
</form>

<h3><label for="refresh"><?php _e('Bot Status'); ?></label></h3>
<form action="" method="post" id="refresh-counter" style="margin: auto; width: 500px; ">
<table style="width: 100%">
	<thead>
	<tr>
	<th><?php echo __('user agent');?></th>
	<th><?php echo __('count');?></th>
	<th><?php echo __('last come');?></th>
	</tr>
	</thead>
<tbody>
<?php
foreach ($bots as $bot) {
	if ($bot['counter'] != 0 && $now_timestamp - $bot['last_timestamp'] > 86400) {
		$color = '#888';
	} else {
		$color = '#4AB915';
	}
	echo "<tr>";
	echo "<td>$bot[mark]</td>";
	echo "<td>$bot[counter]</td>";
	echo "<td style=\"color: #fff; text-align: center; background-color: $color;\">{$bot[last_time]}</td>";
	echo "</tr>";
}
?>
</tbody>
</table>
<p class="submit"><input id="refresh" type="submit" name="refresh" value="<?php _e('Refresh status'); ?>" /></p>
</form>
</div>
</div>
