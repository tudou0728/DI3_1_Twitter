<?php
namespace Model\User;
use \Db;
use \PDOException;
/**
 * User model
 *
 * This file contains every db action regarding the users
 */

/**
 * Get a user in db
 * @param id the id of the user in db
 * @return an object containing the attributes of the user or null if error or the user doesn't exist
 */
function get($id)
{ 
	try 
    {	 
    $db = \Db::dbc();
    $sql = "SELECT * FROM USER WHERE IDU='$id'";
	  $sth = $db->prepare($sql);
	  $sth->execute();
    $row = $sth->fetch();
	  if($row==false)
	  {
		  return NULL;
	  }
	  else
	  {    return (object) array(
        "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
      }
    }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }
}

function getu($username)
{ 
  try 
    {  
    $db = \Db::dbc();
    $sql = "SELECT * FROM USER WHERE USERNAME='$username'";
    $sth = $db->prepare($sql);
    $sth->execute();
    $row = $sth->fetch();
    if($row==false)
    {
      return NULL;
    }
    else
    {    return (object) array(
        "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
      }
    }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }
}



/**
 * Create a user in db
 * @param username the user's username
 * @param name the user's name
 * @param password the user's password
 * @param email the user's email
 * @param avatar_path the temporary path to the user's avatar
 * @return the id which was assigned to the created user, null if an error occured
 * @warning this function doesn't check whether a user with a similar username exists
 * @warning this function hashes the password
 */
function create($username, $name, $password, $email, $avatar_path) 
{ 
   try 
 {
   $db = \Db::dbc();
   $password=hash_password($password);
   $sql="INSERT INTO USER (USERNAME,NAMEU,PWD,EMAIL,AVATAR) 
      VALUES (?,?,?,?,?)";
   $sth = $db->prepare($sql);
   $sth->execute(array($username,$name,$password,$email,$avatar_path));
   $last_id=$db->lastInsertId();
   return $last_id; 
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param username the user's username
 * @param name the user's name
 * @param email the user's email
 * @return true if everything went fine, false else
 * @warning this function doesn't check whether a user with a similar username exists
 */
function modify($uid, $username, $name, $email) 
{
  try 
	{
	  $db = \Db::dbc();
    $sql="UPDATE USER SET USERNAME='$username',NAMEU='$name',EMAIL='$email'  WHERE IDU ='$uid'";
    $sth=$db->prepare($sql);
   $sth->execute();
   $row=$sth->rowcount();
    if($row) 
	   {
		  return true; 
	   } 
	   else 
	   {
		  return flase;
	   }
  }
  catch(\PDOException $e)
  {
    echo $e->getMessage();
  }
	/** return false;*/
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param new_password the new password
 * @return true if everything went fine, false else
 * @warning this function hashes the password
 */
function change_password($uid, $new_password) 
{
    try 
	{
	 $db = \Db::dbc();
   $new_password=hash_password($new_password);
    $sql="UPDATE USER SET PWD='$new_password' WHERE IDU ='$uid'";
    $sth=$db->prepare($sql);
    $sth->execute();
    $row=$sth->rowcount();
    if($row)
	  {
		return true; 
	  } 
	  else 
	  {
		return false;
	  }
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	/**return false;*/
}

/**
 * Modify a user in db
 * @param uid the user's id to modify
 * @param avatar_path the temporary path to the user's avatar
 * @return true if everything went fine, false else
 */
function change_avatar($uid, $avatar_path)  
{
    try 
	{
	  $db = \Db::dbc();
    $sql='UPDATE USER SET AVATAR=? WHERE IDU =?';
    $sth=$db->prepare($sql);
    $sth->execute(array($avatar_path,$uid));
    $row=$sth->rowcount();
      if($row)
	  {
		return true; 
	  } 
	  else 
	  {
		return false;
	  }
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	/**return false;*/
}

/**
 * Delete a user in db
 * @param id the id of the user to delete
 * @return true if the user has been correctly deleted, false else
 */
function destroy($id) 
{
    try 
	{
	 $db = \Db::dbc();
    $sql='DELETE FROM USER WHERE IDU=?';
    $sth=$db->prepare($sql);
    $sth->execute(array($id));
    $row=$sth->rowcount();
      if($row)
	  {
		return true; 
	  } 
	  else 
	  {
		return false;
	  }
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }

	/**return false;*/
}

/**
 * Hash a user password
 * @param password the clear password to hash
 * @return the hashed password
 */
function hash_password($password) 
{
    $pwd = md5($password);
	return $pwd;
	/**return $password;*/
}

/**
 * Search a user
 * @param string the string to search in the name or username
 * @return an array of find objects
 */
function search($string)  
{
    try 
	{
	  $db = \Db::dbc();
      $sql ="SELECT * FROM USER WHERE NAMEU LIKE '%$string%' OR USERNAME LIKE '%$string%'";
	  $sth = $db->prepare($sql);
      $sth->execute();
	  if($sth)
        {foreach ($sth ->fetchAll()as $row) {
        $List[]=(object) array(
        "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
        }
        return $List;
	  }
	  else
	  {
        return NULL;
    }

  }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	/**return [get(1)];*/
}

/**
 * List users
 * @return an array of the objects of every users
 */
function list_all() 
{
    try 
	{
	  $db = \Db::dbc();
      $sql ='SELECT * FROM USER';
      $sth = $db->prepare($sql);
      $sth->execute();
      foreach ($sth ->fetchAll()as $row)
	  {   $List[]= (object) array(
        "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);

    }
    return $List;
  }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	/**return [get(1)];*/
}

/**
 * Get a user from its username
 * @param username the searched user's username
 * @return the user object or null if the user doesn't exist
 */
function get_by_username($username) 
{
    try 
	{
	  $db = \Db::dbc();
      $sql ="SELECT * FROM USER WHERE USERNAME='$username'";
	  $sth=$db->prepare($sql);
	  $sth->execute();
    $row=$sth->fetch();
	  if($row)
	  {
      return (object) array
        (
        "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
	  }
	  else
        {
        return NULL;
        }
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	/**return get(1);*/
}

/**
 * Get a user's followers
 * @param uid the user's id
 * @return a list of users objects
 */
function get_followers($uid) 
{
    try 
	{
    $db = \Db::dbc();
    $sql ="SELECT * FROM FOLLOW INNER JOIN USER ON FOLLOW.IDU_USER2=USER.IDU WHERE FOLLOW.IDU_USER1='$uid'";
    $sth=$db->prepare($sql);
    $sth->execute();
    if($sth)
	  {
      foreach($sth->fetchAll() as $row) 
      {
      $List[]=(object) array(
       "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
      }
            return $List;
    }
	  else{
        return NULL;}
  }
  catch(\PDOException $e)
  {
  echo $e->getMessage();
  }
	/**return [get(2)];*/
}

/**
 * Get the users our user is following
 * @param uid the user's id
 * @return a list of users objects
 */
function get_followings($uid) 
{
    try 
  {
    $db = \Db::dbc();
    $sql ="SELECT * FROM FOLLOW INNER JOIN USER ON FOLLOW.IDU_USER1=USER.IDU WHERE FOLLOW.IDU_USER2='$uid'";
    $sth=$db->prepare($sql);
    $sth->execute();
    if($sth)
    {
      foreach($sth->fetchAll() as $row) 
      {
      $List[]=(object) array(
       "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
      }
      return $List;
    }
    else{
        return NULL;}
  }
  catch(\PDOException $e)
  {
  echo $e->getMessage();
  }//turn [get(2)];*/
}


/**
 * Get a user's stats
 * @param uid the user's id
 * @return an object which describes the stats
 */
function get_stats($uid) 
{
    try 
	{
	  $db = \Db::dbc();
      $sql1 ="SELECT * FROM TWEET WHERE IDU='$uid'";
      $res=$db->query($sql1);
	  $rowpost=$res->fetchAll();
	  
	  
	  $sql2 ="SELECT * FROM FOLLOW WHERE IDU_USER1='$uid'";
      $res2=$db->query($sql2);
	  $rowfollowers=$res2->fetchAll();
	  
	  
	  $sql3 ="SELECT * FROM FOLLOW WHERE IDU_USER2='$uid'";
      $res3=$db->query($sql3);
	  $rowfollowing=$res3->fetchAll();

    return (object) array(
        "nb_posts" => count($rowpost),
        "nb_followers" => count($rowfollowers),
        "nb_following" => count($rowfollowing));
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
   /**
	return (object) array(
        "nb_posts" => 10,
        "nb_followers" => 50,
        "nb_following" => 66
    );*/
}

/**
 * Verify the user authentification
 * @param username the user's username
 * @param password the user's password
 * @return the user object or null if authentification failed
 * @warning this function must perform the password hashing   
 */
function check_auth($username, $password) 
{
    try 
	{ $password=hash_password($password);
    $db = \Db::dbc();
    $sql ="SELECT * FROM USER WHERE USERNAME=? AND PWD=?";
    $sth=$db->prepare($sql);
    $sth->execute(array($username, $password));
    $row = $sth->fetch();
        if($row)
        {
        return (object) array(
        "id" => $row['IDU'],
        "username" => $row['USERNAME'],
        "name" => $row ['NAMEU'],
        "password" => $row['PWD'],
        "email" => $row['EMAIL'],
        "avatar" => $row['AVATAR']);
            //return NULL;
        }
        else
        {
        return NULL;
        }
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	//return null;
}

/**
 * Verify the user authentification based on id
 * @param id the user's id
 * @param password the user's password (already hashed)
 * @return the user object or null if authentification failed
 */
function check_auth_id($id, $password) 
{
    try 
	{
	$db = \Db::dbc();
    $sql ="SELECT * FROM USER WHERE IDU=? AND PWD=?";
	$sth=$db->prepare($sql);
    $sth->execute(array($id, $password));
    $row = $sth->fetch();
    if($row == false)
    {
        return NULL;
    }
    else
    {
    return (object) array
    (
    "id" => $row['IDU'],
    "username" => $row['USERNAME'],
    "name" => $row ['NAMEU'],
    "password" => $row['PWD'],
    "email" => $row['EMAIL'],
    "avatar" => $row['AVATAR']
    );
        }
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
	/**return null;*/
}

/**
 * Follow another user
 * @param id the current user's id
 * @param id_to_follow the user's id to follow
 * @return true if the user has been followed, false else
 */
function follow($id, $id_to_follow) 
{
  try 
	{
	  $db = \Db::dbc();
    $date = new \DateTime("NOW");
    $fdate=$date->format('Y-m-d H:i:sP');
    $sql="INSERT INTO FOLLOW (IDU_USER2 ,IDU_USER1,FDATE) VALUES ('$id','$id_to_follow','$fdate')";
    $sth=$db->prepare($sql);
    $sth->execute();
    $row=$sth->rowcount();
    if($row)
	  {
		return true; 
	  } 
	  else 
	  {
		return false;
	  }
  }
  catch(\PDOException $e)
  {
  echo $e->getMessage();
  }
   /**return false;*/
}

/**
 * Unfollow a user
 * @param id the current user's id
 * @param id_to_follow the user's id to unfollow
 * @return true if the user has been unfollowed, false else
 */
function unfollow($id, $id_to_unfollow) 
{
    try 
	{ $db = \Db::dbc();
    $sql="DELETE FROM FOLLOW  WHERE IDU_USER2='$id' AND IDU_USER1='$id_to_unfollow'";
    $sth=$db->prepare($sql);
    $sth->execute();
    $row=$sth-> rowcount();
    if($row)
	  {
		return true; 
	  } 
	  else 
	  {
		return false;
	  }
  }
  catch(\PDOException $e)
  {
    echo $e->getMessage();
  }
	/**return false;*/
}

