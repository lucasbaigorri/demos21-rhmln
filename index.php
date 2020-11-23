<?PHP

// Include app 
define('_VALID', TRUE);
include("functions/Configuration.php");
include("functions/Database.php");
include('functions/Route.php');
include('functions/Page.php');
include('functions/Portal.php');
include('functions/Component.php');

session_start();

// Define base route
Route::add('/',function(){
  redirect('/home');
});
Route::add('/home',function(){
  Page::render('home','none');
});
// Register a login route
Route::add('/login',function(){
  Page::render('login','none');
},['get', 'post']);

Route::add('/logout',function(){
  redirect('/api/v1/logout');
},['get', 'post']);

Route::add('/estructuraorganizacional/puestos',function(){
  Page::render('puestos','none');
},['get', 'post']);

Route::add('/estructuraorganizacional/direcciones',function(){
  Page::render('direcciones','none');
},['get', 'post']);

Route::add('/estructuraorganizacional/subsecretarias',function(){
  Page::render('subsecretarias','none');
},['get', 'post']);

Route::add('/estructuraorganizacional/secretarias',function(){
  Page::render('secretarias','none');
},['get', 'post']);


Route::add('/testGET', function() {
  print_r($_GET);
}, 'get');
Route::add('/testPOST', function() {
  print_r($_POST);
}, 'get');
//Login
Route::add('/api/v1/login', function() {
  $return = array("status" => "error", "message" => NULL);
  
  $usuario = filter_input(INPUT_GET, 'username');
  $contrasenia = filter_input(INPUT_GET, 'password');
  if(empty($_GET)){
    $return['message'] = "Acción invalida!";
  }else{
    if(empty($usuario)){
      $return['message'] = "El usuario no puede estar vacío";
    }elseif(empty($contrasenia)){
      $return['message'] = "La contraseña no puede estar vacía";
    }
    else{
      $return = login($usuario,$contrasenia);
    }
  }  
  echo(json_encode($return));
}, ['get', 'post']);
//Logout
Route::add('/api/v1/logout', function() {
  $return = array("status" => "error", "message" => NULL);
  session_start();
  
  // Destruir todas las variables de sesión.
  $_SESSION = array();
  
  // Si se desea destruir la sesión completamente, borre también la cookie de sesión.
  // Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]);  
  }
  
  // Finalmente, destruir la sesión.
  session_destroy();
  redirect('/');   
}, ['get', 'post']);


Route::add('/api/v1/(.*)/(.*)', function($object,$action) {
  $return = array("status" => "error", "message" => NULL);
  //Usuario
  if($object == 'usuario'){
    if($action=='list'){
      $search = filter_input(INPUT_GET, 'q');
      $return = array("results" => obtenerUsuarios($search));     
    }
  }
  //Secretaría
  else if($object == 'secretaria'){
    if($action=='crear'){
      $data['nombre'] = filter_input(INPUT_POST, 'nombre');
      $data['responsable'] = filter_input(INPUT_POST, 'responsable');
      if(empty($data['nombre'])){
        $return['message'] = "El nombre no puede estar vacío";
      }elseif(empty($data['responsable'])){
        $return['message'] = "El responsable no puede estar vacío";
      }else{
        $return = crearSecretaria($data);
      }      
    }else if($action=='list'){
      $search = filter_input(INPUT_GET, 'q');
      $return = obtenerSecretariasJSON($search);     
    }else if(is_numeric($action)){
      $return = obtenerSubsecretariasPorSecretaria($action);   
    }
  }

  //Subsecretaría
  else if($object == 'subsecretaria'){
    if($action=='crear'){
      $data['nombre'] = filter_input(INPUT_POST, 'nombre');
      $data['responsable'] = filter_input(INPUT_POST, 'responsable');      
      $data['secretaria'] = filter_input(INPUT_POST, 'secretaria');
      if(empty($data['nombre'])){
        $return['message'] = "El nombre no puede estar vacío";
      }elseif(empty($data['responsable'])){
        $return['message'] = "El responsable no puede estar vacío";
      }elseif(empty($data['secretaria'])){
        $return['message'] = "El campo de secretaria no puede estar vacío";
      }else{
        $return = crearSubsecretaria($data);
      }      
    }    
  }

  //direccion
  else if($object == 'direccion'){
    if($action=='crear'){
      $data['nombre'] = filter_input(INPUT_POST, 'nombre');
      $data['responsable'] = filter_input(INPUT_POST, 'responsable');      
      $data['secretaria'] = filter_input(INPUT_POST, 'secretaria');                
      $data['subsecretaria'] = filter_input(INPUT_POST, 'subsecretaria');
      if(empty($data['nombre'])){
        $return['message'] = "El nombre no puede estar vacío";
      }elseif(empty($data['responsable'])){
        $return['message'] = "El responsable no puede estar vacío";
      }elseif(empty($data['secretaria'])){
        $return['message'] = "El campo de secretaria no puede estar vacío";
      }else{
        $return = crearDireccion($data);
      }      
    }    
  }
  //null
  else{
    $return['message'] = "Objeto inválido!";
  }
  echo(json_encode($return));
}, ['get', 'post']);

// 
Route::add('/test',function(){
  Page::render('test', 'none');
});

// Error pages
Route::pathNotFound(function($path){
  Page::render('404', 'login', ['path' => $path]);
});

Route::methodNotAllowed(function($path, $method){
  Page::render('405', 'default', ['path' => $path, 'method' => $method]);
});

// Run the router
Route::run('/');

?>
