<?php 
    class DbOperations{
        private $con; 
        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
    }
        public function createUser($name, $username, $email, $password){
            if (!$this->isEmailExists($email)) {
                $stmt = $this->con->prepare("INSERT INTO student(name,username,email,password) VALUES (?,?,?,?)"); //$this->con
                $stmt->bind_param("ssss",$name,$username,$email,$password);
                if ($stmt->execute()) {
                    return USER_CREATED;
                } else {
                    return USER_FAILURE;
                }
            }
            return USER_EXISTS;
        }

        public function userLogin($email, $password){
            if($this->isEmailExists($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }


public function getUsersPasswordByEmail($email){ //function for validating passsord
    $stmt=$this->con->prepare("SELECT password FROM student where email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->bind_result($password);
    $stmt->fetch();
    //$student=array();
    return $password;
}

public function getAllUsers(){
    $stmt=$this->con->prepare("SELECT name,username,email,password FROM student");
    $stmt->execute();
    $stmt->bind_result($name,$username,$email,$password);
    $students=array();
    while($stmt->fetch()){
    $student=array();
    $student['name']=$name;
    $student['username']=$username;
    $student['email']=$email;
    $student['password']=$password;
    array_push($students,$student);
    }
    return $students;
}

public function getUserByEmail($email){ //function for retrieving user info for login
    $stmt=$this->con->prepare("SELECT name,username,email FROM student where email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->bind_result($name,$username,$email,$password);
    $stmt->fetch();
    $student=array();
    $student['name']=$name;
    $student['username']=$username;
    $student['email']=$email;
    //$student['password']=$password;
    return $student;

}
public function updateUser($name,$email,$username){
$stmt=$this->con->prepare("UPDATE student SET name=?,email=? where username=?");
$stmt->bind_param("sss",$name,$email,$username);
if($stmt->execute())
return true;
else return false;
}
public function updatePassword($currentpassword, $newpassword,$email){
    $hashed_password = $this->getUsersPasswordByEmail($email); 
    if(password_verify($currentpassword, $hashed_password)){
        $hash_password=password_hash($newpassword,PASSWORD_DEFAULT);
$stmt=$this->con->prepare("UPDATE student set password=? where email=?");
$stmt->bind_param("ss",$hash_password,$email);
if($stmt->execute())
return PASSWORD_CHANGED;
else return PASSWORD_DO_NOT_MATCH;
    }
    else{
        return PASSWORD_DO_NOT_MATCH;
    }

}
public function deleteUser($username){
    $stmt=$this->con->prepare("DELETE from student where username=?");
    $stmt->bind_param("s",$username);
    if($stmt->execute())
    return true;
    else return false;
}
   public function isEmailExists($email){
            $stmt = $this->con->prepare("SELECT username FROM student WHERE email = ?");

            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
}