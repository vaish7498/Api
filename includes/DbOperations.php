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
                array_push($students, $student);
            }             
            return $students; 
        }
      //UPDATING USERS
            public function updateUser($name,$email,$id){
                $stmt=$this->con->prepare("UPDATE student SET name=?,email=? where id=?");
                $stmt->bind_param("ssi",$name,$email,$id);
                if($stmt->execute())
                return true;
                else return false;
                }

    //UPDATE PASSWORD
            public function updatePassword($currentpassword, $newpassword,$email){
                    $hashed_password = $this->getUsersPasswordByEmail($email); 
                    if(password_verify($currentpassword, $hashed_password)){
                        $hash_password=password_hash($newpassword,PASSWORD_DEFAULT);
                $stmt=$this->con->prepare("UPDATE student set password=? where email=?");
                $stmt->bind_param("ss",$hash_password,$email);
                if($stmt->execute())
                return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;
                    }
                    else{
                        return PASSWORD_DO_NOT_MATCH;
                    }
                
                }
                //DELETE USERS
                public function deleteUser($id){
                    $stmt=$this->con->prepare("DELETE from student where id=?");
                    $stmt->bind_param("i",$id);
                    if($stmt->execute())
                    return true;
                    else return false;
                }


        /*
            This function reads a specified user from database*/
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


/****************** TABLE PROFILE*****************************************************/ 
 //create profile details

 public function createProfile($name, $email, $gender,$age)
    {
        //if (!$this->isEmailExist($email)) {
            $stmt = $this->con->prepare("INSERT INTO profile (name, email, gender,age) VALUES (?, ?, ?,?)");
            $stmt->bind_param("sssi", $name, $email, $gender,$age);
            if ($stmt->execute()) {
                return PROFILE_CREATED;
            } else {
                return PROFILE_FAILURE;
            }
        }
       // return PROFILE_EXISTS;
    
       //profile pic
      /* public function profilePic($image){
        
        if(is_uploaded_file($_FILES['user_image']['tmp_name'])){

            $tmp_file=$_FILES['user_image']['tmp_name'];
            $img_name=$_FILES['user_image']['name'];
            $upload_dir='image/'.$img_name;
            $stmt = $this->con->prepare("INSERT INTO profilepic (image) VALUES (?)");
            $stmt->bind_param("b", $image);
            if(move_uploaded_file($tmp_file,$upload_dir)&& $stmt->execute()){
                return IMAGE_UPLOADED;
            }
         else{
             return FAILURE;
         }
        }
       }*/

public function showProfile($name){
    $stmt = $this->con->prepare("SELECT name,email,gender,age FROM profile where name=?;");
    $stmt->bind_param("s", $name);
            $stmt->execute(); 
            $stmt->bind_result($name,$email,$gender,$age);
            $students = array(); 
            while($stmt->fetch()){ 
                $student = array(); 
                $student['email']=$email; 
                $student['name'] = $name; 
                $student['gender'] = $gender; 
                $student['age'] = $age; 
                array_push($students, $student);
            }             
            return $students; 
}
public function updateProfile($email,$gender,$age,$name){
    $stmt=$this->con->prepare("UPDATE profile SET email=?,gender=?,age=? where name=?");
                $stmt->bind_param("ssis",$email,$gender,$age,$name);
                if($stmt->execute())
                return true;
                else return false;
}
public function getUserByEmail2($email){
    $stmt = $this->con->prepare("SELECT  name,email,gender,age FROM profile WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute(); 
    $stmt->bind_result($name, $email,$gender,$age);
    $stmt->fetch(); 
    $user = array(); 
    //$user['id'] = $id; 
    $user['name'] = $name; 
    $user['email']=$email; 
    $user['gender']=$gender;
    $user['age']=$age;
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
