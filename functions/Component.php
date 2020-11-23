<?PHP

class Component {

	public static function render ($name, $arguments = []) {
		ob_start();
		if(is_array($arguments)){
			extract($arguments);
		}
		// The name variable was prefixed because extract could override it
		include('themes/default/components/'.$name.'.php');
		return ob_get_clean();
	}

}
