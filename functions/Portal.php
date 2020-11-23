<?PHP

class Portal{

	private static $contents = [];

	public static function send($portalName, $content){
		if(!array_key_exists($portalName,self::$contents)){
			self::$contents[$portalName] = [];
		}
		array_push(self::$contents[$portalName],$content);
	}

	public static function receive($portalName){
		$outString = '';
			if(array_key_exists($portalName,self::$contents)){
				foreach(self::$contents[$portalName] as $content){
				$outString .= $content;
			}
		}
		return $outString;
	}

	public static function sendStart() {
		ob_start();
	}

	public static function sendEnd($portalName) {
		self::send($portalName,ob_get_clean());
	}

}
