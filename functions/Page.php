<?PHP

class Page{

	public static function render($name, $layout='default', $arguments = []) {
		// Catch the page results and send them to the main portal
		checkPermission($name, true);
		
		Portal::sendStart();
		if(is_array($arguments)){
			extract($arguments);
		}
		if($layout != 'none')		
		{
			include('pages/'.$name.'.php');
			Portal::sendEnd('main');			
			// Include the layout
			include('themes/default/layouts/'.$layout.'.php');
		}else{
			if(file_exists('pages/'.$name.'.html'))
				include('pages/'.$name.'.html');
			else
				include('pages/'.$name.'.php');
		}
		
	}

}
