<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';

require '../includes/DbOperations.php';

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

/*
endpoint: createuser
parameters: name, email, password
method: POST
 */
$app->post('/createuser', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('name', 'email', 'password'), $request,$response)) {
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $password = $request_data['password'];
        $name = $request_data['name'];
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $db = new DbOperations;
        $result = $db->createUser($name, $email, $hash_password);

        if ($result == USER_CREATED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User created successfully';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == USER_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        } else if ($result == USER_EXISTS) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Already Exists';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->get('/allusers', function (Request $request, Response $response) {
    $db = new DbOperations;
    $users = $db->getAllUsers();
    $response_data = array();
    $response_data['error'] = false;
    $response_data['users'] = $users;
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/userlogin', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('email', 'password'),$request, $response)) {
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $password = $request_data['password'];

        $db = new DbOperations;
        $result = $db->userLogin($email, $password);
        if ($result == USER_AUTHENTICATED) {

            $user = $db->getUserByEmail($email);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['user'] = $user;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});
/*UPDATING USERS*/
$app->put('/updateuser/{id}',function(Request $request, Response $response,array $args){ //app for 
    $id=$args['id'];
    if(!haveEmptyParameters(array('name','email'),$request,$response)){
    
    $request_data=$request->getParsedBody();
    $name=$request_data['name'];
    $email=$request_data['email'];
    //$id=$request_data['id'];
    
    $db=new DbOperations;
    if($db->updateUser($name,$email,$id)){
    
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
    
    /*CHange password*/

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
    
    //FOR DELETING A USER
    
    $app->delete('/deleteuser/{id}',function(Request $request, Response $response,array $args){
    $id=$args['id'];
    $db=new DbOperations;
    $response_data=array();
    if($db->deleteUser($id)){
     $response_data['error']=false;
     $response_data['message']='user has been deleted';
    }else{
        $response_data['error']=true;
        $response_data['message']='try later';
    }return $response
        ->withHeader('Content-type','application/json; charset=utf-8')
         ->withStatus(200);
    });
    
    //FOR CHECKING EMPTY PARAMETERS
function haveEmptyParameters($required_params,$request, $response)
{
    $error = false;
    $error_params = '';

    $request_params = $request->getParsedBody();// $request_params = $_REQUEST;
    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
            $error = true;
            $error_params .= $param . ', ';
        }
    }
    if ($error) {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}
$app->run();
