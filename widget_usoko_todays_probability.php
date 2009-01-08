<?php
/*
Plugin Name: Usokomaker Today's Probability Widget
Plugin URI: http://www.vjcatkick.com/?p=4769
Description: Display Today's Probability from Usokomaker.
Version: 0.0.4
Author: V.J.Catkick
Author URI: http://www.vjcatkick.com/
*/

/*  Copyright V.J.Catkick - http://www.vjcatkick.com/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/* Changelog
* Thu Nov 20 2008 - v0.0.1
- Initial release
* Fri Dec 26 2008 - v0.0.2
- 
* Tue Dec 30 2008 - v0.0.3
- compatibility bug fix
* Jan 06 2009 - v0.0.4
- renew reading engin, add cache, almost re-create
*/


function widget_usoko_todays_probability_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_usoko_todays_probability( $args ) {
		extract($args);

		$options = get_option('widget_usoko_todays_probability');
		$uname = $options['usoko_username'];
		$title = $options['usoko_pmk_title'];
		$usoko_cache = $options['usoko_cache'];
		$usoko_cache_time = $options['usoko_cache_time'];
		$usoko_cache_time_interval = 60 * 60;

		$output = '<div id="usoko_todays_probability"><ul>';

		// section to get tumblr photos from here 

		$filedata = false;

		if( $usoko_cache_time + $usoko_cache_time_interval < time() ) {
			$workstr = "http://usokomaker.net/kakuritu/?a=Maker&oo=" . $uname;
			$filedata = @file_get_contents( $workstr );
//			$filedata = @simplexml_load_file( $workstr );
		} /* if */

		if( $filedata ) {
			$filedata = mb_convert_encoding( $filedata, "UTF-8", "EUC-JP" );

			$retv = '<div id="usoko_titleline" style="width:100%;font-size:1.0em;text-align:center;color:#888; ">';
			$retv .= '- ' . $uname . '：本日の確率 -';
			$retv .= '</div>';

			$spos = strpos( $filedata, '<h1 id="maga_title">' );
			$filedata = substr( $filedata, $spos );
			$spos = strpos( $filedata, '</h1>' );
			$usoko_date = strip_tags( substr( $filedata, 0, $spos ) );
			$retv .= '<div id="usoko_date" style="width:100%; font-size:0.8em;text-align:center;color:#888; border-bottom: 1px dotted #DDD; padding-bottom:2px;">';
			$retv .= $usoko_date;
			$retv .= '</div>';
			$filedata = substr( $filedata, $spos );

			$spos = strpos( $filedata, '<div id="li">' );
			$filedata = substr( $filedata, $spos );
			$spos = strpos( $filedata, '<input' );
			$filedata = substr( $filedata, strlen( '<div id="li">' ), $spos );

			$soucepattern_1 = "今日<strong>" . $uname . "</strong>.";
			$repstr1 = "";
			mb_regex_encoding( "UTF-8" );
			$filedata = mb_ereg_replace( $soucepattern_1, $repstr1, $filedata );

			$soucepattern_2 = "</span><br />";
			$repstr2 = "</li><li>";
			$filedata = "<li>" . mb_ereg_replace( $soucepattern_2, $repstr2, $filedata );

			$filedata = substr( $filedata, 0, strpos( $filedata, '<li></div>' ) );
			$filedata = strip_tags( $filedata, '<li>' );

			$retv .= $filedata;
			$retv .= '<div style="margin-top: 4px;font-size: 0.8em;text-align:right;" ><span style="color:#888;">Powered by </span><a href="http://maker.usoko.net/" target="_blank" >うそこメーカー</a></div>';

			$output .= $retv;

			$options['usoko_cache'] = $retv . '<!-- cached -->';
			$options['usoko_cache_time'] = time();
			update_option('widget_usoko_todays_probability', $options);
		}else{
			$output .= $usoko_cache;
		} /* if else */

		$output .= '</ul></div>';

		// These lines generate the output
		echo $before_widget . $before_title . $title . $after_title;
		echo $output;
		echo $after_widget;
	} /* widget_usoko_todays_probability() */

	function widget_usoko_todays_probability_control() {
		$options = $newoptions = get_option('widget_usoko_todays_probability');
		if ( $_POST["usoko_submit"] ) {
			$newoptions['usoko_pmk_title'] = strip_tags(stripslashes($_POST["usoko_pmk_title"]));
			$newoptions['usoko_username'] = strip_tags(stripslashes($_POST["usoko_username"]));

			$newoptions['usoko_cache'] = "";
			$newoptions['usoko_cache_time'] = 0;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_usoko_todays_probability', $options);
		}

		// those are default value
		if ( !$options['usoko_username'] ) $options['usoko_username'] = 'site owner';

		$usoko_username = $options['usoko_username'];
		$title = htmlspecialchars($options['usoko_pmk_title'], ENT_QUOTES);
		$uname = htmlspecialchars($options['usoko_username'], ENT_QUOTES);
?>

	    <?php _e('Title:'); ?> <input style="width: 170px;" id="usoko_pmk_title" name="usoko_pmk_title" type="text" value="<?php echo $title; ?>" /><br />
	    <?php _e('Name:'); ?> <input style="width: 170px;" id="usoko_username" name="usoko_username" type="text" value="<?php echo $uname; ?>" /><br />
  	    <input type="hidden" id="usoko_submit" name="usoko_submit" value="1" />

<?php
	} /* widget_usoko_todays_probability_control() */

	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('Usokomaker Todays Probability', 'widget_usoko_todays_probability');
	register_widget_control('Usokomaker Todays Probability', 'widget_usoko_todays_probability_control' );
} /* widget_usoko_todays_probability_init() */

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_usoko_todays_probability_init');

?>