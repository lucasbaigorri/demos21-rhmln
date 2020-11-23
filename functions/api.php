<?php

function checkPermission($permission, $redirect = false)
{
    if (!isset($_SESSION)) {
        session_start();
    }
    //Verifica si la página requiere auntenticacion
    if (!isPermissionFree($permission)) {
        // Verifica si el usuario ya está logueado
        if (empty($_SESSION['userguid']) || empty($_SESSION['username']) || empty($_SESSION['udata'])) {
            $_SESSION['error'] = "Acceso Denegado1!";
            if ($redirect) {
                redirect("/login");
            }
        } else {
            $udata = @unserialize(base64_decode(secured_decrypt($_SESSION['udata'])));
            if($udata['username'] != $_SESSION['username']){
                $_SESSION['error'] = "Acceso Denegado2!";
                if($redirect){
                    redirect("/login");
                }
            }else{                
                $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
                //Check Permission
                $query = $db->query("SELECT count(*) as permission FROM permission_role pr INNER JOIN permission p ON p.idpermission = pr.idpermission WHERE pr.idrole=? AND p.url=? LIMIT 1",intval($udata['rol_id']),$permission)->fetchArray();    
                if($query['permission'] > 0){
                    return true;
                }elseif($redirect){
                    $_SESSION['error'] = "Acceso Denegado3! URL>".$url;
                    redirect("/login");
                }
            }
        }
    }
    return true;
}
function isPermissionFree($permission)
{    
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $isFree = false;
    $query = $db->query('SELECT count(*) as permission from permission where url=? and free=TRUE', $permission)->fetchArray();
    return $isFree = $query['permission'];
}
function redirect($url)
{
    if (headers_sent()) {
        echo "<script>document.location.href='" . $url . "';</script>\n";
    } else {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
    }
    die();
}
function getRealIP()
{
    return $_SERVER["REMOTE_ADDR"];
}
function secured_encrypt($data)
{
    $first_key = base64_decode(FIRSTKEY);
    $second_key = base64_decode(SECONDKEY);
    
    $method = "aes-256-cbc";
    $iv_length = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    $first_encrypted = openssl_encrypt($data, $method, $first_key, OPENSSL_RAW_DATA, $iv);
    $second_encrypted = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
    
    $output = base64_encode($iv . $second_encrypted . $first_encrypted);
    return $output;
}

function secured_decrypt($input)
{
    $first_key = base64_decode(FIRSTKEY);
    $second_key = base64_decode(SECONDKEY);
    $mix = base64_decode($input);
    
    $method = "aes-256-cbc";
    $iv_length = openssl_cipher_iv_length($method);
    
    $iv = substr($mix, 0, $iv_length);
    $second_encrypted = substr($mix, $iv_length, 64);
    $first_encrypted = substr($mix, $iv_length + 64);
    
    $data = openssl_decrypt($first_encrypted, $method, $first_key, OPENSSL_RAW_DATA, $iv);
    $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
    
    if (hash_equals($second_encrypted, $second_encrypted_new))
    return $data;
    
    return false;
}

function obtenerIP()
{
    $ip = "";
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function guidv4()
{
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function verificarCredenciales($usuario, $contrasenia){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    //Check Permission
    $query = $db->query("SELECT u.password FROM user u WHERE (u.username = ? OR u.email =?) and u.active = true LIMIT 1",$usuario, $usuario)->fetchArray();

    if(secured_decrypt($query["password"]) == $contrasenia){
        $_SESSION['error'] = "";
        return true;
    }elseif($redirect){
        $_SESSION['error'] = "Acceso Denegado!";
        redirect("503");
    }
    return false;
}
function obtenerUsuario($usuario){
       
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT u.userguid, u.id, umuni.nombre, umuni.apellido, u.username, CONCAT(umuni.nombre, ' ', umuni.apellido) as name, u.email, ur.idrole as rol_id, r.name as rol, u.created FROM user u INNER JOIN user_role ur ON ur.iduser = u.id INNER JOIN role r ON r.idrole = ur.idrole INNER JOIN Usuario umuni ON umuni.idUsuario = u.idUsuarioMuni WHERE(u.username= ? or u.email = ?) GROUP BY u.id LIMIT 1",$usuario,$usuario)->fetchArray();
	
	return (array)$query;
}
function login($usuario,$contrasenia){
    $return = array("status" => "error", "message" => NULL);
    if(!verificarCredenciales($usuario,$contrasenia)){
        $return['message'] = "Las credenciales ingresadas son inválidas!";
    }else{
        session_start();
        $user = obtenerUsuario($usuario);
        $_SESSION['userguid'] = $user['userguid'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['apellido'] = $user['apellido'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['rol_id'] = $user['rol_id'];
        $_SESSION['udata'] = secured_encrypt(base64_encode(serialize($user)));
        $return['message'] = "Bienvenido, en breve será redirigido!" ;
        $return['status'] = "success";
    }
    return $return;
}

function obtenerUsuarios($q){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query('SELECT u.idUsuario as id, CONCAT(u.Nombre, " ", u.Apellido) as text FROM Usuario u WHERE CONCAT(u.Nombre, " ", u.Apellido) like "%'.$q.'%"')->fetchAll();	
	return (array)$query;
}

function crearSecretaria($data){
    $return = array("status" => "error", "message" => NULL);
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query('INSERT INTO Secretaría (Secretaría,idResponsable) VALUES (?,?)',$data['nombre'],$data['responsable'])->lastInsertID();

    if($query > 0){
        $return['message'] = "Se ha creado correctamente!";
        $return['status'] = "success";
    }else{
        $return['message'] = "Ha ocurrido un error.";
    }
	return $return;
}

function obtenerSecretarias(){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT s.idSecretaría, s.Secretaría, CONCAT(u.Nombre, ' ',u.Apellido) as responsable, u.idUsuario, DATE_FORMAT(s.creado, '%d/%m/%Y') as creado, (SELECT COUNT(*) from Usuario where idSecretaria = s.idSecretaría) as colaboradores FROM Secretaría s INNER JOIN Usuario u ON s.idResponsable =u.idUsuario order by s.idSecretaría desc")->fetchAll();	
	return (array)$query;
}

function obtenerSecretariasJSON($q){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT s.idSecretaría as id, s.Secretaría as text FROM Secretaría s WHERE s.Secretaría like '%".$q."%'")->fetchAll();	
	return array("results" => $query);
}

function crearSubsecretaria($data){
    $return = array("status" => "error", "message" => NULL);
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query('INSERT INTO Subsecretaría (Subsecretaría,idSecretaría,idResponsable) VALUES (?,?,?)',$data['nombre'],$data['secretaria'],$data['responsable'])->lastInsertID();

    if($query > 0){
        $return['message'] = "Se ha creado correctamente!";
        $return['status'] = "success";
    }else{
        $return['message'] = "Ha ocurrido un error.";
    }
	return $return;
}

function obtenerSubsecretariasPorSecretaria($idSecretaria){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT s.idSubsecretaría as id, s.Subsecretaría as text FROM Subsecretaría s WHERE s.idSecretaría = ".$idSecretaria)->fetchAll();	
	return array("results" => $query);
}

function obtenerSubsecretarias(){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT s.idSubsecretaría, s.Subsecretaría, CONCAT(u.Nombre, ' ',u.Apellido) as responsable, u.idUsuario, DATE_FORMAT(s.creado, '%d/%m/%Y') as creado, (SELECT COUNT(*) from Usuario where idSubsecretaía = s.idSubsecretaría) as colaboradores, su.Secretaría FROM Subsecretaría s INNER JOIN Usuario u ON s.idResponsable = u.idUsuario INNER JOIN Secretaría su ON s.idSecretaría = su.idSecretaría order by s.idSubsecretaría desc")->fetchAll();	
	return (array)$query;
}

function obtenerSubsecretariasJSON($q){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT s.idSubsecretaría as id, s.Subsecretaría as text FROM Subsecretaría s WHERE s.Subsecretaría like '%".$q."%'")->fetchAll();	
	return array("results" => $query);
}

function crearDireccion($data){
    $return = array("status" => "error", "message" => NULL);
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query('INSERT INTO Dirección (Dirección, idSubsecretaría,idSecretaría,idResponsable) VALUES (?,?,?,?)',$data['nombre'],$data['subsecretaria'],$data['secretaria'],$data['responsable'])->lastInsertID();

    if($query > 0){
        $return['message'] = "Se ha creado correctamente!";
        $return['status'] = "success";
    }else{
        $return['message'] = "Ha ocurrido un error.";
    }
	return $return;
}
function obtenerDirecciones(){
    $db = new db(DB_HOST, DB_USUARIO, DB_CONTRASENA, DB_NOMBRE);
    $query = $db->query("SELECT d.idDirección, d.Dirección, s.Subsecretaría, su.Secretaría, CONCAT(u.Nombre, ' ',u.Apellido) as responsable, u.idUsuario, DATE_FORMAT(s.creado, '%d/%m/%Y') as creado, (SELECT COUNT(*) from Usuario where idDirección = d.idDirección) as colaboradores FROM Dirección d INNER JOIN Subsecretaría s ON d.idSubSecretaría = s.idSubsecretaría INNER JOIN Usuario u ON d.idResponsable = u.idUsuario INNER JOIN Secretaría su ON d.idSecretaría = su.idSecretaría order by d.idDirección desc")->fetchAll();	
	return (array)$query;
}