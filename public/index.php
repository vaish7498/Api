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
//CREATE USER
$app->post('/createuser', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('name', 'collegecode', 'password'), $request,$response)) {
        $request_data = $request->getParsedBody();
        $collegecode = $request_data['collegecode'];
        $password = $request_data['password'];
        $name = $request_data['name'];
        //$hash_password = password_hash($password, PASSWORD_DEFAULT);
        $db = new DbOperations;
        $result = $db->createUser($name, $collegecode, $password);

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
//DISPLAY ALL USERS
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
//USER LOGIN
$app->post('/userlogin', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('collegecode', 'password'),$request, $response)) {
        $request_data = $request->getParsedBody();
        $collegecode = $request_data['collegecode'];
        $password = $request_data['password'];

        $db = new DbOperations;
        $result = $db->userLogin($collegecode, $password);
        if ($result == USER_AUTHENTICATED) {

            $user = $db->getUserByCode($collegecode);
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
//UPDATING USERS
/*$app->put('/updateuser/{collegecode}',function(Request $request, Response $response,array $args){ //app for 
    $collegecode=$args['collegecode'];
    if(!haveEmptyParameters(array('name','collegecode'),$request,$response)){
    
    $request_data=$request->getParsedBody();
    $name=$request_data['name'];
    $email=$request_data['email'];
    $id=$request_data['id'];
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

/*$app->put('/updatepassword',function(Request $request, Response $response){
    
        if(!haveEmptyParameters(array('currentpassword','newpassword','collegecode'),$request,$response)){
           
            $request_data=$request->getParsedBody();
            $currentpassword=$request_data['currentpassword'];
            $newpassword=$request_data['newpassword'];
            $collegecode=$request_data['collegecode'];
            $db=new DbOperations;
            $result=$db->updatePassword($currentpassword,$newpassword,$collegecode);
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
                $response_data['message']='password does\'t match';
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













/********************************************************TABLE PROFILE */
  





//create profile
$app->post('/createprofile', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('name','phone', 'email', 'gender','age'), $request,$response)) {
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $gender = $request_data['gender'];
        $age = $request_data['age'];
        $name = $request_data['name'];
        $phone = $request_data['phone'];
        $db = new DbOperations;
        $result = $db->createProfile($name,$phone, $email, $gender,$age);

        if ($result == PROFILE_CREATED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User PRofile created successfully';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == PROFILE_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';
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

//display profile details
$app->get('/showprofile', function (Request $request, Response $response) {

    
    if(!haveEmptyParameters(array('name'),$request,$response)){
    
        $request_data=$request->getParsedBody();
        $name=$request_data['name'];
        //$email=$request_data['email'];
        //$id=$request_data['id'];
        
        $db=new DbOperations;
        if($db->showProfile($name)){
        $students=$db->showProfile($name);
        $response_data=array();
        $response_data['error']=false;
        $response_data['user']=$students;
        /*$user=$db->getUserByEmail($email);
        $response_data['user']=$user;*/
        
        $response->write(json_encode($response_data));
        
        return $response
                    ->withHeader('Content-type','application/json; charset=utf-8')
                    ->withStatus(200);
        }
        else{
            $response_data=array();
            $response_data['error']=true;
            $response_data['user']='failure';
            /*$user=$db->getUserByEmail($email);
            $response_data['user']=$user;*/
            
            $response->write(json_encode($response_data));
            
            return $response
                        ->withHeader('Content-type','application/json; charset=utf-8')
                        ->withStatus(200);
        }
    } return $response
    ->withHeader('Content-type','application/json; charset=utf-8')
    ->withStatus(200);
});


// UPDATE PROFILE

$app->put('/updateprofile/{name}',function(Request $request, Response $response,array $args){ //app for 
    $name=$args['name'];
    if(!haveEmptyParameters(array('email','gender','age','name'),$request,$response)){
    $request_data=$request->getParsedBody();
    $name=$request_data['name'];
    $email=$request_data['email'];
    $gender=$request_data['gender'];
    $age=$request_data['age'];
    //$id=$request_data['id'];
    
    $db=new DbOperations;
    if($db->updateProfile($email,$gender,$age,$name)){
    
    $response_data=array();
    $response_data['error']=false;
    $response_data['message']='User Update Successful';
    $user=$db->getUserByEmail2($email);
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



///////////// TABLE COLLEGE


$app->post('/createcollege', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('name','coursetype','coursename','fee','location'), $request,$response)) {
        $request_data = $request->getParsedBody();
        $coursetype = $request_data['coursetype'];
        $coursename = $request_data['coursename'];
        $name = $request_data['name'];
        $fee = $request_data['fee'];
        $location = $request_data['location'];
        //$hash_password = password_hash($password, PASSWORD_DEFAULT);
        $db = new DbOperations;
        $result = $db->createCollege($name, $coursetype, $coursename,$fee,$location);

        if ($result == COLLEGE_CREATED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'College created successfully';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';
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

/////TABLE INSTITUTION
$app->get('/showinstitution', function (Request $request, Response $response) {

    
    if(!haveEmptyParameters(array('Name'),$request,$response)){
    
        $request_data=$request->getParsedBody();
        $Name=$request_data['Name'];
        //$email=$request_data['email'];
        //$id=$request_data['id'];
        
        $db=new DbOperations;
        if($db->showInstitution($Name)){
        $students=$db->showInstitution($Name);
        $response_data=array();
        $response_data['error']=false;
        $response_data['user']=$students;
        /*$user=$db->getUserByEmail($email);
        $response_data['user']=$user;*/
        
        $response->write(json_encode($response_data));
        
        return $response
                    ->withHeader('Content-type','application/json; charset=utf-8')
                    ->withStatus(200);
        }
        else{
            $response_data=array();
            $response_data['error']=true;
            $response_data['user']='failure';
            /*$user=$db->getUserByEmail($email);
            $response_data['user']=$user;*/
            
            $response->write(json_encode($response_data));
            
            return $response
                        ->withHeader('Content-type','application/json; charset=utf-8')
                        ->withStatus(200);
        }
    } return $response
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
