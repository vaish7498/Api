<?php
class DbOperations
{
    //the database connection variable
    private $con;

    //inside constructor
    //we are getting the connection link
    public function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect;
        $this->con = $db->connect();
    }

    /*  The Create Operation
         The function will insert a new user in our database
     */
    public function createUser($name, $email, $password)
    {
        if (!$this->isEmailExist($email)) {
            $stmt = $this->con->prepare("INSERT INTO student (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);
            if ($stmt->execute()) {
                return USER_CREATED;
            } else {
                return USER_FAILURE;
            }
        }
        return USER_EXISTS;
    }

     /* 
            The Read Operation 
            The function will check if we have the user in database
            and the password matches with the given or not 
            to authenticate the user accordingly    
        */
        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
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

        
        /*  
            The method is returning the password of a given user
            to verify the given password is correct or not
        */
        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM student WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

         /*
            The Read Operation
            Function is returning all the Students from database
        */
        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, name, email FROM student;");
            $stmt->execute(); 
            $stmt->bind_result($id, $name, $email);
            $students = array(); 
            while($stmt->fetch()){ 
                $student = array(); 
                $student['id'] = $id; 
                $student['email']=$email; 
                $student['name'] = $name; 
                array_push($students, $user);
            }             
            return $students; 
        }
 



        /*
            The Read Operation
            This function reads a specified user from database
        */
        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, name, email FROM student WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $name, $email);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['name'] = $name; 
            $user['email']=$email; 
            return $user; 
        }



    private function isEmailExist($email)
    {
        $stmt = $this->con->prepare("SELECT id FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}
