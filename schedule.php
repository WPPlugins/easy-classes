<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check that the user has the required capability 
if (!current_user_can('manage_options'))
{
 wp_die( __('You do not have sufficient capability to access this page.') );
}

## Get posts by custom post type 'easyclass' ##
function eac_get_classes() {
	global $wpdb;
	$classes = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'easyclass' AND post_status = 'publish'" );
	return $classes;
}
## Display a list of 'easyclass' by title ##
function eac_get_classes_colors_list() {
	global $wpdb;
	$classes = eac_get_classes();
	if(isset($_POST['resetcolors'])&&($_POST['resetcolors']=='yes')) {
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'easyclass_color' ), array( '%s' ) );
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'easyclass_color' ), array( '%s' ) );
	}
	echo '<div id="caption-container">';
	echo '<div id="classlist" class="eac_list">';
	foreach($classes as $class) {
				// FINDING EVENTUAL DUPES
				$dupes = $wpdb->get_results($wpdb->prepare(
					"SELECT meta_id, post_id, meta_key 
					FROM $wpdb->postmeta
					WHERE post_id = %d
					AND meta_key = 'easyclass_color'",$class->ID
				));
				// Protecting first occurence
				$first = true;
				$first_id;
				// SUPPRESSING THEM
				foreach($dupes as $dupe) {
					if(!$first) {
						$wpdb->delete( $wpdb->postmeta, array( 'meta_id' => $dupe->meta_id ), array( '%d' ) );
					} else {
						$first_id = $dupe->meta_id;
					}
					$first = false;
				}
		echo '<span>';
		echo '<div id="color-'.$class->ID.'" class="color" onclick="class_color(\'color-'.$class->ID.'\',\''.$class->ID.'\',\'color-selected-'.$class->ID.'\');" style="background:';
		$the_id = 'color-selected-'.$class->ID;
		if(isset($_POST[$the_id])) {
			echo $_POST[$the_id];
				// UPDATING COLOR INFO
				if(!empty($first_id)) {
					$wpdb->replace(
						$wpdb->postmeta,
						array('meta_id' => $first_id, 'post_id' => $class->ID,'meta_key' => 'easyclass_color','meta_value' => $_POST[$the_id]),
						array('%d','%d','%s','%s')
					);
				} else {
					$wpdb->insert(
						$wpdb->postmeta,
						array('post_id' => $class->ID,'meta_key' => 'easyclass_color','meta_value' => $_POST[$the_id]),
						array('%d','%s','%s')
					);
				}
		} else {
			$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
			$color = $wpdb->get_var($wpdb->prepare($query,$class->ID,'easyclass_color'));
			if($color!=false) {
				echo $color;
			}
		}
		echo ';border-color:';
		if(isset($_POST[$the_id])) {
			echo $_POST[$the_id];
		} else {
			$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
			$color = $wpdb->get_var($wpdb->prepare($query,$class->ID,'easyclass_color'));
			if($color!=false) {
				echo $color;
			}
		}
		echo ';"></div>';
		echo '<input type="hidden" id="color-selected-'.$class->ID.'" name="color-selected-'.$class->ID.'" value="';
		if(isset($_POST[$the_id])) {
			echo $_POST[$the_id];
		} else {
			$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
			$color = $wpdb->get_var($wpdb->prepare($query,$class->ID,'easyclass_color'));
			if($color!=false) {
				echo $color;
			}
		}
		echo '">';
		echo '<label for="'.$class->ID.'">';
		echo $class->post_title;
		echo '</label>';
		echo '</span>';
		echo '';
	}
	echo '</div>';
	echo '</div>';
}

## USER SET DAYS FOR SCHEDULE SORT ##
function eac_set_days() {
	echo '<div id="orderdays">';
	echo '<h3>'; 
	_e('Order the days in the schedule','easyclasses');
	echo '</h3><br>';
	// Getting the registered days //
	$_days = get_terms("day",'hide_empty=0');
	// Arrays for sort function //
	$strings = array();
	$size = count($_days);
	// Displaying table //
	echo '<div><table class="days">';
	foreach($_days as $_day) {
		// Days are added one after another as the ref string //
		echo '<tr>';
		echo '<td style="font-weight:bold;font-size:14px;">';
		echo $_day->name;
		echo '</td><td>';
		for($k=0;$k<$size;$k++) {
			$n = $k+1;
			$name = $_day->name.'_order';
			// If the order has been set for the day register it. Add one ordered day to the count //
			if(isset($_POST[$name])) { $strings[$_day->name] = $_POST[$name];}
			if(isset($_POST[$name])&&($_POST[$name]==$k)) {
				echo '<input type="radio" name="'.$_day->name.'_order" value="'.$k.'" checked="checked">'.$n.' ';
			} else {
				echo '<input type="radio" name="'.$_day->name.'_order" value="'.$k.'">'.$n.' ';
			}
		}
		echo '</td></tr>';
	}
	echo '</table></div><br>';
	echo '<input type="hidden" id="resetdays" name="resetdays" value="no">';
	echo '<button class="button button-primary button-large" type="submit">';
	_e('Save','easyclasses');
	echo '</button>';
	echo '<button type="button" id="resetbutton" class="button button-primary button-large" onclick="reset_days();">';
	_e('Reset order','easyclasses');
	echo '</button><br><br>';
	echo '</div>';
	if(isset($_POST['resetdays'])&&($_POST['resetdays']=='yes')) {
		global $wpdb;
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'easyclass_search_days' ), array( '%s' ) );
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'easyclass_replace_days' ), array( '%s' ) );
		return false;
	}
	// If all the days have an order set save it //
	$strings_size = count($strings);
	if($strings_size == $size) {
		return $strings;
	} else {
		return false;
	}
}

## SORT BY DAY ##
function eac_sort_by_day($values){
	global $wpdb;
	$search_strings = array();
	$replace_string = array();
	// Getting user customized references //
	$strings = eac_set_days();
	if($strings==false) {
	
		$ser_search_strings = $wpdb->get_results($wpdb->prepare(
					"SELECT meta_value
					FROM $wpdb->postmeta
					WHERE post_id = %d
					AND meta_key = 'easyclass_search_days'",0
		));
		$ser_replace_string = $wpdb->get_results($wpdb->prepare(
					"SELECT meta_value
					FROM $wpdb->postmeta
					WHERE post_id = %d
					AND meta_key = 'easyclass_replace_days'",0
		));
		
		if(empty($ser_search_strings)||empty($ser_search_strings)) {
		
			// Default references for comparison //
			$search_strings = array(
					__('Monday','easyclasses'),__('monday','easyclasses'),__('Mon','easyclasses'),__('mon','easyclasses'),__('Mo','easyclasses'),__('mo','easyclasses'),
					__('Tuesday','easyclasses'),__('tuesday','easyclasses'),__('Tue','easyclasses'),__('tue','easyclasses'),__('Tu','easyclasses'),__('tu','easyclasses'),
					__('Wednesday','easyclasses'),__('wednesday','easyclasses'),__('Wed','easyclasses'),__('wed','easyclasses'),__('We','easyclasses'),__('we','easyclasses'),
					__('Thursday','easyclasses'),__('thursday','easyclasses'),__('Thu','easyclasses'),__('thu','easyclasses'),__('Th','easyclasses'),__('th','easyclasses'),
					__('Friday','easyclasses'),__('friday','easyclasses'),__('Fri','easyclasses'),__('fri','easyclasses'),__('Fr','easyclasses'),__('fr','easyclasses'),
					__('Saturday','easyclasses'),__('saturday','easyclasses'),__('Sat','easyclasses'),__('sat','easyclasses'),__('Sa','easyclasses'),__('sa','easyclasses'),
					__('Sunday','easyclasses'),__('sunday','easyclasses'),__('Sun','easyclasses'),__('sun','easyclasses'),__('Su','easyclasses'),__('su','easyclasses'),
				);
			$replace_string = array('0','0','0','0','0','0','1','1','1','1','1','1','2','2','2','2','2','2','3','3','3','3','3','3','4','4','4','4','4','4','5','5','5','5','5','5','6','6','6','6','6','6');
			
		} else {
			$search_strings = unserialize($ser_search_strings[0]->meta_value);
			$replace_string = unserialize($ser_replace_string[0]->meta_value);
		}
		
	} else {
		natsort($strings);
		foreach($strings as $day => $order) {
			array_push($search_strings,$day);
			array_push($replace_string,$order);
		}
		$forsave_search_strings = serialize($search_strings);
		$forsave_replace_string = serialize($replace_string);
		
			// FINDING EVENTUAL DUPES
			$dupes_1 = $wpdb->get_results($wpdb->prepare(
					"SELECT meta_id, post_id, meta_key 
					FROM $wpdb->postmeta
					WHERE post_id = %d
					AND meta_key = 'easyclass_search_days'",0
			));
			// FINDING EVENTUAL DUPES
			$dupes_2 = $wpdb->get_results($wpdb->prepare(
					"SELECT meta_id, post_id, meta_key 
					FROM $wpdb->postmeta
					WHERE post_id = %d
					AND meta_key = 'easyclass_replace_days'",0
			));
			// Protecting first occurence
			$first_1 = true;
			$first_1_id;
			$first_2 = true;
			$first_2_id;
			// SUPPRESSING THEM
			foreach($dupes_1 as $dupe) {
					if(!$first_1) {
						$wpdb->delete( $wpdb->postmeta, array( 'meta_id' => $dupe->meta_id ), array( '%d' ) );
					} else {
						$first_1_id = $dupe->meta_id;
					}
					$first_1 = false;
			}
			// SUPPRESSING THEM
			foreach($dupes_2 as $dupe) {
					if(!$first_2) {
						$wpdb->delete( $wpdb->postmeta, array( 'meta_id' => $dupe->meta_id ), array( '%d' ) );
					} else {
						$first_2_id = $dupe->meta_id;
					}
					$first_2 = false;
			}
			// UPDATING SORT INFOS
				if(!empty($first_1_id)) {
					$wpdb->replace(
						$wpdb->postmeta,
						array('meta_id' => $first_1_id, 'post_id' => 0,'meta_key' => 'easyclass_search_days','meta_value' => $forsave_search_strings),
						array('%d','%d','%s','%s')
					);
				} else {
					$wpdb->insert(
						$wpdb->postmeta,
						array('post_id' => 0,'meta_key' => 'easyclass_search_days','meta_value' => $forsave_search_strings),
						array('%d','%s','%s')
					);
				}
				if(!empty($first_2_id)) {
					$wpdb->replace(
						$wpdb->postmeta,
						array('meta_id' => $first_2_id, 'post_id' => 0,'meta_key' => 'easyclass_replace_days','meta_value' => $forsave_replace_string),
						array('%d','%d','%s','%s')
					);
				} else {
					$wpdb->insert(
						$wpdb->postmeta,
						array('post_id' => 0,'meta_key' => 'easyclass_replace_days','meta_value' => $forsave_replace_string),
						array('%d','%s','%s')
					);
				}
	}
    $sort_key = array_map('ucfirst', $values);
    $sort_key = str_replace($search_strings, $replace_string, $sort_key);
    array_multisort($sort_key, SORT_ASC, SORT_STRING, $values);
    return $values;
}

## GENERATE AN ARRAY OF TIMESLOTS ##
function eac_get_timeslots($arr1,$arr2) {
	// Get unique values //
	$arr1_uniques = array();
	$arr2_uniques = array();
	
	$arr1_uniques = array_diff($arr1,$arr2);
	$arr2_uniques = array_diff($arr2,$arr1);
	
	// Get all other values //
	$arr = array_intersect($arr1,$arr2);
	
	// Reinitialize keys through new array //
	$total = array();
	$key = 0;
	foreach($arr as $value) {
		$total[$key] = $value;
		$key++;
	}
	foreach($arr1_uniques as $a1) {
		$total[$key] = $a1;
		$key++;
	}
	foreach($arr2_uniques as $a2) {
		$total[$key] = $a2;
		$key++;
	}
	
	// Sort the array //
	natsort($total);
	$total = array_values($total);
	
	$new_array = array();
	for($key=0;$key<count($total);$key++) {
		if(!empty($total[$key+1])) {
			$timeslot = $total[$key].' / '.$total[$key+1];
			array_push($new_array,$timeslot);
		}
	}
	
	return $new_array;
}

## GENERATE BLANK SCHEDULE ##
function eac_generate_schedule() {

	global $wpdb;

	// Get registered days //
	$_days = get_terms("day",'hide_empty=0');
	// Make it an array of strings //
	$s_days = array();
	foreach($_days as $_day) {
		array_push($s_days,$_day->name);
	}
	// Order them properly //
	$days = eac_sort_by_day($s_days);
	// Count number of days //
	$number_of_days = count($days);
	
	// Get registered hours //
	$_beginning = get_terms("beginning",'hide_empty=0');
	$_ending = get_terms("ending",'hide_empty=0');
	// Make them arrays of strings //
	$beginning = array();
	foreach($_beginning as $_begin) {
		array_push($beginning,$_begin->name);
	}
	$ending = array();
	foreach($_ending as $_end) {
		array_push($ending,$_end->name);
	}
	// Order them properly //
	natsort($beginning);
	$beginning = array_values($beginning);
	natsort($ending);
	$ending = array_values($ending);
	// Generate timeslots //
	$hours = eac_get_timeslots($beginning,$ending);
	// Count total of hours entry
	$number_of_hours = count($hours);
	
	// Useful hours array in time format //
	$thours = $hours;
	$h = array("h","H", "-");
	foreach($thours as $hour) {
		$hour = str_replace ($h, ":", $hour);
		$hour = strtotime($hour);
	}
	
	
	// REGISTERING CLASSES IN ARRAYS //
	$classes = eac_get_classes();
	$classes_array = array();
	foreach($classes as $class) {
		
		$id = $class->ID;
		$name = $class->post_title;
		
		// Class day
		$_day = get_the_terms( $class->ID, 'day' );
		if(!empty($_day)) {
			foreach($_day as $__day) {
				$day = $__day->name;
			}
		} else { $day = ''; }
		// Class beginning time
		$_begin = get_the_terms( $class->ID, 'beginning' );
		if(!empty($_begin)) {
			foreach($_begin as $__begin) {
				$begin = $__begin->name;
			}
			$begin = str_replace ($h, ":", $begin);
		} else { $begin = ''; }
		// Class ending time
		$_end = get_the_terms( $class->ID, 'ending' );
		if(!empty($_end)) {
			foreach($_end as $__end) {
				$end = $__end->name;
			}
			$end = str_replace ($h, ":", $end);
		} else { $end = ''; }
		// Class teacher
		$_teacher = get_the_terms( $class->ID, 'teacher' );
		if(!empty($_teacher)) {
			foreach($_teacher as $__teacher) {
				$teacher = $__teacher->name;
			}
		} else { $teacher = ''; }
		// Class room
		$_room = get_the_terms( $class->ID, 'room' );
		if(!empty($_room)) {
			foreach($_room as $__room) {
				$room = $__room->name;
			}
		} else { $room = ''; }
		
		$class_array = array(
			'id' => $id,
			'name' => $name,
			'day' => $day,
			'begin' => $begin,
			'end' => $end,
			'teacher' => $teacher,
			'room' => $room,
		);
		
		array_push($classes_array,$class_array);
	}
	////////////////////////////////////
	
	$classmatrix = array();
	for($i=0;$i<$number_of_hours;$i++) {
		for($j=0;$j<$number_of_days;$j++) {
			foreach($classes_array as $class_array) {
				if($class_array['day']==$days[$j]) {
					$begin_time = strtotime($class_array['begin']);
					$end_time = strtotime($class_array['end']);
					$pos = strpos($thours[$i], ' ');
					$thour = substr($thours[$i],0,$pos);
					$thour = str_replace ($h, ":", $thour);
					$thour = strtotime($thour);
					if( ($begin_time <= $thour)&&($end_time > $thour) ) {
						if(!empty($classmatrix[$i][$j])) {
							$classmatrix[$i][$j][0] = $class_array;
						} else {
							$classmatrix[$i][$j] = $class_array;
						}
					}
				}
			}
		}
	}

	// TABLE HEADER : containing $days //
	echo '<div id="schedule-container">';
	echo '<div class="easy-class-schedule">
				<table>
					<tr>
						<th class="time" width="100px">';
	echo _e('Schedule','easyclasses');
	echo '</th>';

	foreach($days as $day) {		
		echo '<th>'.$day.'</th>';
	}
	echo '<th class="time" width="100px">';
	echo _e('Schedule','easyclasses');
	echo '</th>
				</tr>';
				
	$string = "";
				
	// TABLE LINES : containing $hours //
	for($line=0;$line<$number_of_hours;$line++) {
		// We create a line //
		echo '<tr>';
			// The first case is always a timeslot //
			echo '<td>';
			echo $hours[$line];
			echo '</td>';
			// We create blank case in the exact number of days //
			for($k=0;$k<$number_of_days;$k++) {
				// If there's only one entry the td will be our color container //
				if(!empty($classmatrix[$line][$k])&&empty($classmatrix[$line][$k][0])) {
					$the_id = 'color-selected-'.$classmatrix[$line][$k]['id'];
					echo '<td class="'.$classmatrix[$line][$k]['id'].'" style="background:';
					if(isset($_POST[$the_id])) {
						echo $_POST[$the_id];
					} else {
						$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
						$color = $wpdb->get_var($wpdb->prepare($query,$classmatrix[$line][$k]['id'],'easyclass_color'));
						if($color!=false) {
							echo $color;
						}
					}
					echo ';border-color:';
					if(isset($_POST[$the_id])) {
						echo $_POST[$the_id];
					} else {
						$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
						$color = $wpdb->get_var($wpdb->prepare($query,$classmatrix[$line][$k]['id'],'easyclass_color'));
						if($color!=false) {
							echo $color;
						}
					}
					echo ';">';
				} else {
					echo '<td>';
				}
				if(!empty($classmatrix[$line][$k])) {
					if(!empty($classmatrix[$line][$k][0])) {
						$the_id = 'color-selected-'.$classmatrix[$line][$k]['id'];
						echo '<table class="double"><tr><td class="'.$classmatrix[$line][$k]['id'].'" style="background:';
						if(isset($_POST[$the_id])) {
							echo $_POST[$the_id];
						} else {
							$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
							$color = $wpdb->get_var($wpdb->prepare($query,$classmatrix[$line][$k]['id'],'easyclass_color'));
							if($color!=false) {
								echo $color;
							}
						}
						echo ';border-color:';
						if(isset($_POST[$the_id])) {
							echo $_POST[$the_id];
						} else {
							$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
							$color = $wpdb->get_var($wpdb->prepare($query,$classmatrix[$line][$k]['id'],'easyclass_color'));
							if($color!=false) {
								echo $color;
							}
						}
						echo ';">';
					} else {
						echo '<div class="classblock">';
					}
					echo '<b>';
					echo $classmatrix[$line][$k]['name'];
					echo '</b><br>';
					echo $classmatrix[$line][$k]['teacher'];
					echo '<br><i>';
					echo $classmatrix[$line][$k]['room'];
					echo '</i>';
					if(!empty($classmatrix[$line][$k][0])) {
						echo '</td>';
					} else {
						echo '</div>';
					}
				}
				if(!empty($classmatrix[$line][$k][0])) {
					$the_id = 'color-selected-'.$classmatrix[$line][$k][0]['id'];
					echo '<td class="'.$classmatrix[$line][$k][0]['id'].'" style="background:';
					if(isset($_POST[$the_id])) {
						echo $_POST[$the_id];
					} else {
						$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
						$color = $wpdb->get_var($wpdb->prepare($query,$classmatrix[$line][$k][0]['id'],'easyclass_color'));
						if($color!=false) {
							echo $color;
						}
					}
					echo ';border-color:';
					if(isset($_POST[$the_id])) {
						echo $_POST[$the_id];
					} else {
						$query = 'SELECT meta_value FROM '.$wpdb->postmeta.' WHERE post_id = %d AND meta_key = %s';
						$color = $wpdb->get_var($wpdb->prepare($query,$classmatrix[$line][$k][0]['id'],'easyclass_color'));
						if($color!=false) {
							echo $color;
						}
					}
					echo ';">';
					echo '<b>';
					echo $classmatrix[$line][$k][0]['name'];
					echo '</b><br>';
					echo $classmatrix[$line][$k][0]['teacher'];
					echo '<br><i>';
					echo $classmatrix[$line][$k][0]['room'];
					echo '</i>';
					echo '</td></tr></table>';
				}
				echo'</td>';
			}
			// The last case is always a timeslot //
			echo '<td>';
			echo $hours[$line];
			echo '</td>';
		// We finish the line
		echo '</tr>';
	}
	
	echo '</table></div></div>';
	echo '<input type="hidden" id="nblines" name="nblines" value="'.$line.'">';
	echo '<input type="hidden" id="nbcol" name="nbcol" value="'.$k.'">';
}
?>

<style type="text/css">
<?php include 'schedule.css'; ?>
.color:hover {
border:2px solid silver;
}
</style>

<script>
/*
	Developed by Robert Nyman, http://www.robertnyman.com
	Code/licensing: http://code.google.com/p/getelementsbyclassname/
*/	
var getElementsByClassName = function (className, tag, elm){
	if (document.getElementsByClassName) {
		getElementsByClassName = function (className, tag, elm) {
			elm = elm || document;
			var elements = elm.getElementsByClassName(className),
				nodeName = (tag)? new RegExp("\\b" + tag + "\\b", "i") : null,
				returnElements = [],
				current;
			for(var i=0, il=elements.length; i<il; i+=1){
				current = elements[i];
				if(!nodeName || nodeName.test(current.nodeName)) {
					returnElements.push(current);
				}
			}
			return returnElements;
		};
	}
	else if (document.evaluate) {
		getElementsByClassName = function (className, tag, elm) {
			tag = tag || "*";
			elm = elm || document;
			var classes = className.split(" "),
				classesToCheck = "",
				xhtmlNamespace = "http://www.w3.org/1999/xhtml",
				namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace : null,
				returnElements = [],
				elements,
				node;
			for(var j=0, jl=classes.length; j<jl; j+=1){
				classesToCheck += "[contains(concat(' ', @class, ' '), ' " + classes[j] + " ')]";
			}
			try	{
				elements = document.evaluate(".//" + tag + classesToCheck, elm, namespaceResolver, 0, null);
			}
			catch (e) {
				elements = document.evaluate(".//" + tag + classesToCheck, elm, null, 0, null);
			}
			while ((node = elements.iterateNext())) {
				returnElements.push(node);
			}
			return returnElements;
		};
	}
	else {
		getElementsByClassName = function (className, tag, elm) {
			tag = tag || "*";
			elm = elm || document;
			var classes = className.split(" "),
				classesToCheck = [],
				elements = (tag === "*" && elm.all)? elm.all : elm.getElementsByTagName(tag),
				current,
				returnElements = [],
				match;
			for(var k=0, kl=classes.length; k<kl; k+=1){
				classesToCheck.push(new RegExp("(^|\\s)" + classes[k] + "(\\s|$)"));
			}
			for(var l=0, ll=elements.length; l<ll; l+=1){
				current = elements[l];
				match = false;
				for(var m=0, ml=classesToCheck.length; m<ml; m+=1){
					match = classesToCheck[m].test(current.className);
					if (!match) {
						break;
					}
				}
				if (match) {
					returnElements.push(current);
				}
			}
			return returnElements;
		};
	}
	return getElementsByClassName(className, tag, elm);
};

function display_colors() {
	var colors = document.getElementById('allcolors');
	var button = document.getElementById('colors_button');
	colors.style.display=(colors.style.display=='block')?'none':'block';
	button.innerHTML=(button.innerHTML=='<?php _e("Show colors","easyclasses") ?>')?'<?php _e("Hide colors","easyclasses") ?>':'<?php _e("Show colors","easyclasses") ?>';
}

function display_orderdays() {
	var order = document.getElementById('orderdays');
	var button = document.getElementById('order_button');
	order.style.display=(order.style.display=='block')?'none':'block';
	button.innerHTML=(button.innerHTML=='<?php _e("Change the order","easyclasses") ?>')?'<?php _e("Hide order options","easyclasses") ?>':'<?php _e("Change the order","easyclasses") ?>';
}

var k = 0;
var htmlcolors = new Array();
htmlcolors[k] = "White";k++;
htmlcolors[k] = "Snow";k++;
htmlcolors[k] = "Ivory";k++;
htmlcolors[k] = "SeaShell";k++;
htmlcolors[k] = "WhiteSmoke";k++;
htmlcolors[k] = "FloralWhite";k++;
htmlcolors[k] = "BlanchedAlmond";k++;
htmlcolors[k] = "Cornsilk";k++;
htmlcolors[k] = "Beige";k++;
htmlcolors[k] = "OldLace";k++;
htmlcolors[k] = "Bisque";k++;
htmlcolors[k] = "Azure";k++;
htmlcolors[k] = "AliceBlue";k++;
htmlcolors[k] = "LightCyan";k++;
htmlcolors[k] = "MintCream";k++;
htmlcolors[k] = "Lavender";k++;
htmlcolors[k] = "Linen";k++;
htmlcolors[k] = "HoneyDew";k++;
htmlcolors[k] = "LavenderBlush";k++;
htmlcolors[k] = "PapayaWhip";k++;
htmlcolors[k] = "LightYellow";k++;
htmlcolors[k] = "LemonChiffon";k++;
htmlcolors[k] = "LightGoldenrodYellow";k++;
htmlcolors[k] = "Mocassin";k++;
htmlcolors[k] = "Wheat";k++;
htmlcolors[k] = "PeachPuff";k++;
htmlcolors[k] = "NavajoWhite";k++;
htmlcolors[k] = "Gainsboro";k++;
htmlcolors[k] = "GhostWhite";k++;
htmlcolors[k] = "LightGray";k++;
htmlcolors[k] = "Silver";k++;
htmlcolors[k] = "Gray";k++;
htmlcolors[k] = "LDarkGray";k++;
htmlcolors[k] = "DimGray";k++;
htmlcolors[k] = "LightSlateGray";k++;
htmlcolors[k] = "Slategray";k++;
htmlcolors[k] = "DarkSlateGray";k++;
htmlcolors[k] = "MistyRose";k++;
htmlcolors[k] = "Salmon";k++;
htmlcolors[k] = "LightSalmon";k++;
htmlcolors[k] = "LightPink";k++;
htmlcolors[k] = "Pink";k++;
htmlcolors[k] = "HotPink";k++;
htmlcolors[k] = "DeepPink";k++;
htmlcolors[k] = "Fuchsia";k++;
htmlcolors[k] = "Magenta";k++;
htmlcolors[k] = "MediumVioletRed";k++;
htmlcolors[k] = "PaleVioletRed";k++;
htmlcolors[k] = "Orchid";k++;
htmlcolors[k] = "MediumOrchid";k++;
htmlcolors[k] = "DarkOrchid";k++;
htmlcolors[k] = "Plum";k++;
htmlcolors[k] = "Thistle";k++;
htmlcolors[k] = "Violet";k++;
htmlcolors[k] = "Purple";k++;
htmlcolors[k] = "DarkMagenta";k++;
htmlcolors[k] = "DarkViolet";k++;
htmlcolors[k] = "Indigo";k++;
htmlcolors[k] = "MediumPurple";k++;
htmlcolors[k] = "BlueViolet";k++;
htmlcolors[k] = "SlateBlue";k++;
htmlcolors[k] = "MediumSlateBlue";k++;
htmlcolors[k] = "DarkSlateBlue";k++;
htmlcolors[k] = "MidnightBlue";k++;
htmlcolors[k] = "Navy";k++;
htmlcolors[k] = "MediumBlue";k++;
htmlcolors[k] = "Blue";k++;
htmlcolors[k] = "MediumBlue";k++;
htmlcolors[k] = "DarkBlue";k++;
htmlcolors[k] = "RoyalBlue";k++;
htmlcolors[k] = "SteelBlue";k++;
htmlcolors[k] = "LightSteelBlue";k
htmlcolors[k] = "PowderBlue";k++;
htmlcolors[k] = "DodgerBlue";k++;
htmlcolors[k] = "DeepSkyeBlue";k++;
htmlcolors[k] = "SkyBlue";k++;
htmlcolors[k] = "LightSkyBlue";k++;
htmlcolors[k] = "LightBlue";k++;
htmlcolors[k] = "CornflowerBlue";k++;
htmlcolors[k] = "CadetBlue";k++;
htmlcolors[k] = "MediumAquaMarine";k++;
htmlcolors[k] = "Teal";k++;
htmlcolors[k] = "DarkTurquoise";k++;
htmlcolors[k] = "MediumTurquoise";k++;
htmlcolors[k] = "Turquoise";k++;
htmlcolors[k] = "PaleTurquoise";k++;
htmlcolors[k] = "Cyan";k++;
htmlcolors[k] = "DarkCyan";k++;
htmlcolors[k] = "Aqua";k++;
htmlcolors[k] = "AquaMarine";k++;
htmlcolors[k] = "LightSeaGreen";k++;
htmlcolors[k] = "SeaGreen";k++;
htmlcolors[k] = "MediumSeaGreen";k++;
htmlcolors[k] = "DarkSeaGreen";k++;
htmlcolors[k] = "ForestGreen";k++;
htmlcolors[k] = "DarkGreen";k++;
htmlcolors[k] = "Green";k++;
htmlcolors[k] = "DarkOliveGreen";k++;
htmlcolors[k] = "OliveDrab";k++;
htmlcolors[k] = "LightGreen";k++;
htmlcolors[k] = "SpringGreen";k++;
htmlcolors[k] = "MediumSpringGreen";k++;
htmlcolors[k] = "PaleGreen";k++;
htmlcolors[k] = "LawnGreen";k++;
htmlcolors[k] = "LimeGreen";k++;
htmlcolors[k] = "Lime";k++;
htmlcolors[k] = "Chartreuse";k++;
htmlcolors[k] = "YellowGreen";k++;
htmlcolors[k] = "GreenYellow";k++;
htmlcolors[k] = "Olive";k++;
htmlcolors[k] = "Yellow";k++;
htmlcolors[k] = "Gold";k++;
htmlcolors[k] = "Khaki";k++;
htmlcolors[k] = "GoldenRod";k++;
htmlcolors[k] = "DarkGoldenRod";k++;
htmlcolors[k] = "SandyBrown";k++;
htmlcolors[k] = "Orange";k++;
htmlcolors[k] = "DarkOrange";k++;
htmlcolors[k] = "OrangeRed";k++;
htmlcolors[k] = "DarkSalmon";k++;
htmlcolors[k] = "LightCoral";k++;
htmlcolors[k] = "Coral";k++;
htmlcolors[k] = "Tomato";k++;
htmlcolors[k] = "Red";k++;
htmlcolors[k] = "FireBrick";k++;
htmlcolors[k] = "Crimson";k++;
htmlcolors[k] = "IndianRed";k++;
htmlcolors[k] = "DarkRed";k++;
htmlcolors[k] = "Maroon";k++;
htmlcolors[k] = "Brown";k++;
htmlcolors[k] = "RosyBrown";k++;
htmlcolors[k] = "SaddleBrown";k++;
htmlcolors[k] = "Sienna";k++;
htmlcolors[k] = "Chocolate";k++;
htmlcolors[k] = "Peru";k++;
htmlcolors[k] = "BurlyWood";k++;
htmlcolors[k] = "DarkKhaki";k++;
htmlcolors[k] = "Tan";k++;
htmlcolors[k] = "PaleGoldenRod";

//// RESET COLOR CHOICE FOR CLASSES
function class_color_reset(color_id,input_id,form_id) {
	// The div displaying visually the chosen color //
	var color_div = document.getElementById(color_id);
	// The hidden input sending the chosen color //
	var classlist = document.getElementsByClassName(input_id);
	var form_div = document.getElementById(form_id);
	// Changing the displayed color //
	color_div.style.background = "";
	form_div.value = "";
	for(index in classlist) {
		// Changing each td color //
		classlist[index].style.background = "";
		classlist[index].style.borderColor = "";
	}
}

//// HANDLE COLOR CHOICE FOR CLASSES
function class_color(color_id,input_id,form_id) {

	// The div displaying visually the chosen color //
	var color_div = document.getElementById(color_id);
	// The hidden input sending the chosen color //
	var classlist = document.getElementsByClassName(input_id);
	var form_div = document.getElementById(form_id);
	
	var color = form_div.value;
	var chosen_color = form_div.value;
	var chosen = false;
	
	while(!chosen) {
		// Here we need to use another function to display a modal window to choose a color //
		chosen_color = window.prompt("<?php _e('Enter a color name to assign it to the class :','easyclasses') ?>",color);
		if(chosen_color==''||chosen_color==null) { chosen_color = form_div.value; }
		for(var n=0;n<htmlcolors.length;n++) {
			if(chosen_color==htmlcolors[n]) {
				color = chosen_color;
				chosen = true;
				break;
			}
		}
		if((!chosen)&&(chosen_color!=="")) {
			chosen = window.confirm("<?php _e('Are you sure the colour you entered is a valid HTML colour ?','easyclasses') ?>");
			if(chosen) {
				color = chosen_color; break; 
			} else {
				chosen = true;
			}
		}
	}
	// Changing the displayed color //
	color_div.style.background = color;
	form_div.value = color;
	for(index in classlist) {
		// Changing each td color //
		classlist[index].style.background = color;
		classlist[index].style.borderColor = color;
	}
}

function generate_code() {
	// Get all elements needed //
	schedule_div = document.getElementById('schedule-container');
	url_div = document.getElementById('url');
	// Textarea that will receive the code generated //
	codarea = document.getElementById('codarea');
	
	codarea.innerHTML = '<LINK rel="stylesheet" href="';
	codarea.innerHTML += url_div.value;
	codarea.innerHTML += '" type="text/css">';
	
	// Finalizing //
	codarea.innerHTML += schedule_div.innerHTML;
}

function reset_days() {
	var input = document.getElementById('resetdays');
	input.value = (input.value == 'no') ? 'yes' : 'no';
	var resetbutton = document.getElementById('resetbutton');
	resetbutton.innerHTML = (resetbutton.innerHTML == 'Reset order') ? 'Cancel reset' : 'Reset order';
}
</script>

<div class="wrap">
	<h2><?php _e('Schedule','easyclasses') ?></h2>
	<?php _e('Configure a week calendar to display classes schedules.','easyclasses') ?>
	<div class="content">
	<form name="scheform" action="admin.php?page=easy-classes/schedule.php" method="post">
		<h3><?php _e('Days') ?> :</h3>
		<?php _e("You can change the order in which the days appear in the schedule.",'easyclasses') ?><br>
		<a href="#" id="order_button" onclick="display_orderdays();"><?php _e("Change the order",'easyclasses') ?></a><br><br>
		<h3><?php _e('Colors') ?> :</h3>
		<?php _e("Click on the square in front of a class to enter the selected color.",'easyclasses') ?><br>
		<a href="#" id="colors_button" onclick="display_colors();"><?php _e("Show colors",'easyclasses') ?></a><br><br>
		<div id="allcolors" style="display:none;">
			<button type="submit" class="button button-primary button-large"><?php _e('Save','easyclasses'); ?></button>
			<br><br>
			<table>
			<?php
			$k = 0; $htmlcolors = array();
			$htmlcolors[$k] = "White";$k++;
			$htmlcolors[$k] = "Snow";$k++;
			$htmlcolors[$k] = "Ivory";$k++;
			$htmlcolors[$k] = "SeaShell";$k++;
			$htmlcolors[$k] = "WhiteSmoke";$k++;
			$htmlcolors[$k] = "FloralWhite";$k++;
			$htmlcolors[$k] = "BlanchedAlmond";$k++;
			$htmlcolors[$k] = "Cornsilk";$k++;
			$htmlcolors[$k] = "Beige";$k++;
			$htmlcolors[$k] = "OldLace";$k++;
			$htmlcolors[$k] = "Bisque";$$k++;
			$htmlcolors[$k] = "Azure";$k++;
			$htmlcolors[$k] = "AliceBlue";$k++;
			$htmlcolors[$k] = "LightCyan";$k++;
			$htmlcolors[$k] = "MintCream";$k++;
			$htmlcolors[$k] = "Lavender";$k++;
			$htmlcolors[$k] = "Linen";$k++;
			$htmlcolors[$k] = "HoneyDew";$k++;
			$htmlcolors[$k] = "LavenderBlush";$k++;
			$htmlcolors[$k] = "PapayaWhip";$k++;
			$htmlcolors[$k] = "LightYellow";$k++;
			$htmlcolors[$k] = "LemonChiffon";$k++;
			$htmlcolors[$k] = "LightGoldenrodYellow";$k++;
			$htmlcolors[$k] = "Mocassin";$k++;
			$htmlcolors[$k] = "Wheat";$k++;
			$htmlcolors[$k] = "PeachPuff";$k++;
			$htmlcolors[$k] = "NavajoWhite";$k++;
			$htmlcolors[$k] = "Gainsboro";$k++;
			$htmlcolors[$k] = "GhostWhite";$k++;
			$htmlcolors[$k] = "LightGray";$k++;
			$htmlcolors[$k] = "Silver";$k++;
			$htmlcolors[$k] = "Gray";$k++;
			$htmlcolors[$k] = "DarkGray";$k++;
			$htmlcolors[$k] = "DimGray";$k++;
			$htmlcolors[$k] = "LightSlateGray";$k++;
			$htmlcolors[$k] = "Slategray";$k++;
			$htmlcolors[$k] = "DarkSlateGray";$k++;
			$htmlcolors[$k] = "MistyRose";$k++;
			$htmlcolors[$k] = "Salmon";$k++;
			$htmlcolors[$k] = "LightSalmon";$k++;
			$htmlcolors[$k] = "LightPink";$k++;
			$htmlcolors[$k] = "Pink";$k++;
			$htmlcolors[$k] = "HotPink";$k++;
			$htmlcolors[$k] = "DeepPink";$k++;
			$htmlcolors[$k] = "Fuchsia";$k++;
			$htmlcolors[$k] = "Magenta";$k++;
			$htmlcolors[$k] = "MediumVioletRed";$k++;
			$htmlcolors[$k] = "PaleVioletRed";$k++;
			$htmlcolors[$k] = "Orchid";$k++;
			$htmlcolors[$k] = "MediumOrchid";$k++;
			$htmlcolors[$k] = "DarkOrchid";$k++;
			$htmlcolors[$k] = "Plum";$k++;
			$htmlcolors[$k] = "Thistle";$k++;
			$htmlcolors[$k] = "Violet";$k++;
			$htmlcolors[$k] = "Purple";$k++;
			$htmlcolors[$k] = "DarkMagenta";$k++;
			$htmlcolors[$k] = "DarkViolet";$k++;
			$htmlcolors[$k] = "Indigo";$k++;
			$htmlcolors[$k] = "MediumPurple";$k++;
			$htmlcolors[$k] = "BlueViolet";$k++;
			$htmlcolors[$k] = "SlateBlue";$k++;
			$htmlcolors[$k] = "MediumSlateBlue";$k++;
			$htmlcolors[$k] = "DarkSlateBlue";$k++;
			$htmlcolors[$k] = "MidnightBlue";$k++;
			$htmlcolors[$k] = "Navy";$k++;
			$htmlcolors[$k] = "MediumBlue";$k++;
			$htmlcolors[$k] = "Blue";$k++;
			$htmlcolors[$k] = "MediumBlue";$k++;
			$htmlcolors[$k] = "DarkBlue";$k++;
			$htmlcolors[$k] = "RoyalBlue";$k++;
			$htmlcolors[$k] = "SteelBlue";$k++;
			$htmlcolors[$k] = "LightSteelBlue";$k++;
			$htmlcolors[$k] = "PowderBlue";$k++;
			$htmlcolors[$k] = "DodgerBlue";$k++;
			$htmlcolors[$k] = "DeepSkyeBlue";$k++;
			$htmlcolors[$k] = "SkyBlue";$k++;
			$htmlcolors[$k] = "LightSkyBlue";$k++;
			$htmlcolors[$k] = "LightBlue";$k++;
			$htmlcolors[$k] = "CornflowerBlue";$k++;
			$htmlcolors[$k] = "CadetBlue";$k++;
			$htmlcolors[$k] = "MediumAquaMarine";$k++;
			$htmlcolors[$k] = "Teal";$k++;
			$htmlcolors[$k] = "DarkTurquoise";$k++;
			$htmlcolors[$k] = "MediumTurquoise";$k++;
			$htmlcolors[$k] = "Turquoise";$k++;
			$htmlcolors[$k] = "PaleTurquoise";$$k++;
			$htmlcolors[$k] = "Cyan";$k++;
			$htmlcolors[$k] = "DarkCyan";$k++;
			$htmlcolors[$k] = "Aqua";$k++;
			$htmlcolors[$k] = "AquaMarine";$k++;
			$htmlcolors[$k] = "LightSeaGreen";$k++;
			$htmlcolors[$k] = "SeaGreen";$k++;
			$htmlcolors[$k] = "MediumSeaGreen";$k++;
			$htmlcolors[$k] = "DarkSeaGreen";$k++;
			$htmlcolors[$k] = "ForestGreen";$k++;
			$htmlcolors[$k] = "DarkGreen";$k++;
			$htmlcolors[$k] = "Green";$k++;
			$htmlcolors[$k] = "DarkOliveGreen";$k++;
			$htmlcolors[$k] = "OliveDrab";$k++;
			$htmlcolors[$k] = "LightGreen";$k++;
			$htmlcolors[$k] = "SpringGreen";$k++;
			$htmlcolors[$k] = "MediumSpringGreen";$k++;
			$htmlcolors[$k] = "PaleGreen";$k++;
			$htmlcolors[$k] = "LawnGreen";$k++;
			$htmlcolors[$k] = "LimeGreen";$k++;
			$htmlcolors[$k] = "Lime";$k++;
			$htmlcolors[$k] = "Chartreuse";$k++;
			$htmlcolors[$k] = "YellowGreen";$k++;
			$htmlcolors[$k] = "GreenYellow";$k++;
			$htmlcolors[$k] = "Olive";$k++;
			$htmlcolors[$k] = "Yellow";$k++;
			$htmlcolors[$k] = "Gold";$k++;
			$htmlcolors[$k] = "Khaki";$k++;
			$htmlcolors[$k] = "GoldenRod";$k++;
			$htmlcolors[$k] = "DarkGoldenRod";$k++;
			$htmlcolors[$k] = "SandyBrown";$k++;
			$htmlcolors[$k] = "Orange";$k++;
			$htmlcolors[$k] = "DarkOrange";$k++;
			$htmlcolors[$k] = "OrangeRed";$k++;
			$htmlcolors[$k] = "DarkSalmon";$k++;
			$htmlcolors[$k] = "LightCoral";$k++;
			$htmlcolors[$k] = "Coral";$k++;
			$htmlcolors[$k] = "Tomato";$k++;
			$htmlcolors[$k] = "Red";$k++;
			$htmlcolors[$k] = "FireBrick";$k++;
			$htmlcolors[$k] = "Crimson";$k++;
			$htmlcolors[$k] = "IndianRed";$k++;
			$htmlcolors[$k] = "DarkRed";$k++;
			$htmlcolors[$k] = "Maroon";$k++;
			$htmlcolors[$k] = "Brown";$k++;
			$htmlcolors[$k] = "RosyBrown";$k++;
			$htmlcolors[$k] = "SaddleBrown";$k++;
			$htmlcolors[$k] = "Sienna";$k++;
			$htmlcolors[$k] = "Chocolate";$k++;
			$htmlcolors[$k] = "Peru";$k++;
			$htmlcolors[$k] = "BurlyWood";$k++;
			$htmlcolors[$k] = "DarkKhaki";$k++;
			$htmlcolors[$k] = "Tan";$k++;
			$htmlcolors[$k] = "PaleGoldenRod";
			echo '<tr>';
			for($n=0;$n<count($htmlcolors);$n++) {
				echo '<td class="onecolor">'.$htmlcolors[$n].' <div class="colordisplay" style="background:'.$htmlcolors[$n].';"></div> </td>';
				if(($n%10)==0) { echo '</tr><tr>'; }
			}
			?>
				</tr>
			</table>
		</div><br>
		<?php eac_get_classes_colors_list(); ?>
		<?php eac_generate_schedule(); ?><br>
		<button type="submit" class="button button-primary button-large"><?php _e('Save','easyclasses'); ?></button> 
		<button type="button" onclick="generate_code()" class="button button-primary button-large"><?php _e('Generate code','easyclasses'); ?></button><br>
		<h3><?php _e('Code for displaying the schedule on a page','easyclasses') ?> :</h3><br>
		<textarea id="codarea"></textarea>
		<input type="hidden" id="url" value="<?php echo plugin_dir_url(__FILE__);?>schedule.css">
	</form>
	</div>
</div>