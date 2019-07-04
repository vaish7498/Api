<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/DbOperations.php';

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

/*
 endpoint:createuser;
 parameters:name,username,email,password
 method:POST
*/
$app->post('/createuser', function(Request $request, Response $response){ //app for creating user
    if(!haveEmptyParameters(array('name','username','email','password'),$request,$response))
    {
$request_data=$request->getParsedBody();
$name=$request_data['name'];
$username=$request_data['username'];
$password=$request_data['password'];
$email=$request_data['email'];


$hash_password=password_hash($password,PASSWORD_DEFAULT);//hash form of the password

$db=new DbOperations;
$result=$db->createUser($name,$username,$email,$hash_password);

if($result==USER_CREATED){
 $message=array();
 $message['error']=false;
 $message['message']='User created successfully';
 $response->write(json_encode($message));
 return $response
         ->withHeader('Content-type','application/json; charset=utf-8')
         ->withStatus(200);
}
else if($result==USER_FAILURE){
    $message=array();
    $message['error']=true;
    $message['message']='Some error';
    $response->write(json_encode($message));
    return $response
            ->withHeader('Content-type','application/json; charset=utf-8')
            ->withStatus(422);
}

else if($result==USER_EXISTS){
    $message=array();
    $message['error']=true;
    $message['message']='User already exists';
    $response->write(json_encode($message));
    return $response
            ->withHeader('Content-type','application/json; charset=utf-8')
            ->withStatus(422);
}

    }
    return $response
            ->withHeader('Content-type','application/json')
            ->withStatus(422);
});



$app->post('/userlogin',function(Request $request, Response $response){  //app for user login
    if(!haveEmptyParameters(array('email','password'),$request,$response)){
        $request_data=$request->getParsedBody();
        $password=$request_data['password'];
        $email=$request_data['email'];

        $db= new DbOperations;
       // $hash_password=password_hash($password,PASSWORD_DEFAULT);//test!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $result=$db->userLogin($email,$password);//$password
        if($result==USER_AUTHENTICATED){
        $student=$db->getUserByEmail($email);
          $response_data=array();
            $response_data['error']=false;
           $response_data['message']='Login Successful';
        $response_data['student']=$student;

        $response->write(json_encode($response_data));
      return $response
        ->withHeader('Content-type','application/json; charset=utf-8')
        ->withStatus(200);
       }
        else if($result==USER_NOT_FOUND){
            $response_data=array();
            $response_data['error']=true;
            $response_data['message']='User does not exist';
            
            $response->write(json_encode($response_data));
            return $response
                    ->withHeader('Content-type','application/json; charset=utf-8')
                     ->withStatus(200);
            
        }
        else if($result==USER_PASSWORD_DO_NOT_MATCH){
            $response_data=array();
            $response_data['error']=true;
            $response_data['message']='Invalid Credentials';
            $response->write(json_encode($response_data));
            return $response
                    ->withHeader('Content-type','application/json; charset=utf-8')
                     ->withStatus(200);
            
        }
    }
    return $response
            ->withHeader('Content-type','application/json')
            ->withStatus(422);
});

$app->get('/allusers',function(Request $request, Response $response){       //app for retrieving all users
    $db=new DbOperations;
    $students=$db->getAllUsers();
    $response_data=array();
    $response_data['error']=false;
    $response_data['students']=$students;
    $response->write(json_encode($response_data));
    return $response
                    ->withHeader('Content-type','application/json; charset=utf-8')
                     ->withStatus(200);
            
});

$app->put('/updateuser/{username}',function(Request $request, Response $response,array $args){ //app for 
$username=$args['username'];
if(!haveEmptyParameters(array('name','email','username'),$request,$response)){

$request_data=$request->getParsedBody();
$name=$request_data['name'];
$username=$request_data['username'];
$email=$request_data['email'];

$db=new DbOperations;
if($db->updateUser($name,$email,$username)){

$response_data=array();
$response_data['error']=false;
$response_data['message']='User Update Successful';
$user=$db->getUserByEmail($email);
$response_data['user']=$user;

$response->write(json_encode($response_data));

return $response
            ->withHeader('Content-type','application/json; charset=utf-8')
            ->withStatus(200);
}else{
    $response_data=array();
$response_data['error']=true;
$response_data['message']='Please try again ';
$user=$db->getUserByEmail($email);
$response_data['user']=$user;

$response->write(json_encode($response_data));

return $response
            ->withHeader('Content-type','application/json; charset=utf-8')
            ->withStatus(422);
}
}
return $response
->withHeader('Content-type','application/json; charset=utf-8')
 ->withStatus(200);

});

$app->put('/updatepassword',function(Request $request, Response $response){
    
    if(!haveEmptyParameters(array('currentpassword','newpassword','email'),$request,$response)){
       
        $request_data=$request->getParsedBody();
        $currentpassword=$request_data['currentpassword'];
        $newpassword=$request_data['newpassword'];
        $email=$request_data['email'];
        $db=new DbOperations;
        $result=$db->updatePassword($currentpassword,$newpassword,$email);
        if($result==PASSWORD_CHANGED){
        $response_data=array();
        $response_data['error']=false;
        $response_data['message']='password changed';
        $response->write(json_encode($response_data));
        return $response
    ->withHeader('Content-type','application/json; charset=utf-8')
     ->withStatus(200);
     }else if($result==PASSWORD_DO_NOT_MATCH){
        $response_data=array();
        $response_data['error']=true;
        $response_data['message']='password does not match';
        $response->write(json_encode($response_data));
        return $response
    ->withHeader('Content-type','application/json; charset=utf-8')
     ->withStatus(422);
        }else if($result==PASSWORD_NOT_CHANGED){
            $response_data=array();
            $response_data['error']=true;
            $response_data['message']='some error ocurred';
            $response->write(json_encode($response_data));
            return $response
        ->withHeader('Content-type','application/json; charset=utf-8')
         ->withStatus(422);
        }
      }
    return $response
    ->withHeader('Content-type','application/json; charset=utf-8')
     ->withStatus(422);
    

});



$app->delete('/deleteuser/{username}',function(Request $request, Response $response,array $args){
$username=$args['username'];
$db=new DbOperations;
$response_data=array();
if($db->deleteUser($username)){
 $response_data['error']=false;
 $response_data['message']='user has been deleted';
}else{
    $response_data['error']=true;
    $response_data['message']='try later';
}return $response
    ->withHeader('Content-type','application/json; charset=utf-8')
     ->withStatus(200);
});
function haveEmptyParameters($required_params, $request, $response){
    $error=false;
    $error_params='';
    $request_params=$request->getParsedBody();

    foreach($required_params as $param){
        if(!isset($request_params[$param])|| strlen($request_params[$param])<=0){
            $error=true;
            $error_params .= $param . ', ';
        }
    }
    if($error){
        $error_detail=array();
        $error_detail['error']=true;
        $error_detail['message']='Required parameters'.substr($error_params,0,-2);
        $response->write(json_encode($error_detail));
    }
    return $error;
}
$app->run();