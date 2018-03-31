<?php
namespace Model\Hashtag;
use \Db;
use \PDOException;
/**
 * Hashtag model
 *
 * This file contains every db action regarding the hashtags
 */

/**
 * Attach a hashtag to a post
 * @param pid the post id to which attach the hashtag
 * @param hashtag_name the name of the hashtag to attach
 * @return true or false (if something went wrong)
 */
function attach($pid, $hashtag_name) {
    try 
 {
   $db = \Db::dbc();
   $sql="SELECT IDHT FROM HASHTAG WHERE NAMEHT=?";
   $sth=$db->prepare($sql);
   $sth->execute(array($hashtag_name));
   $row=$sth->fetch();
   if ($row) {
   	$sql1="INSERT INTO INCLUDE (IDHT,IDT) 
      VALUES (?,?)";
   $sth1 = $db->prepare($sql1);
   $sth1->execute(array($row,$pid));
   $row1=$sth1->rowcount();
   }
   else
   {
   	$sql2="INSERT INTO HASHTAG (NAMEHT) 
      VALUES ('$hashtag_name')";
    $sth2 = $db->prepare($sql2);
   	$row2=$sth2->execute();
   	$last_id=$db->lastInsertId();
   	$sql1="INSERT INTO INCLUDE (IDHT,IDT) VALUES ('$last_id','$pid')";
   $sth1 = $db->prepare($sql1);
   $sth1->execute();
   $row1=$sth1->rowcount();
   }
   if($row1)
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
}


function addhashtag($hashtag_name){
	try{
  $db = \Db::dbc();
  $sql1="SELECT IDHT FROM HASHTAG WHERE NAMEHT= '$hashtag_name'";
  $sth1 = $db->prepare($sql1);
  $sth1->execute();
  $row1=$sth1->rowcount();
  if($row1==0){

  $sql="INSERT INTO HASHTAG (NAMEHT) VALUES ('$hashtag_name')";
   $sth = $db->prepare($sql);
   $sth->execute();
   $row=$sth->rowcount();	
   if($row){
   	return true;
   }
   else
   {
   	return false;
   }
}
}
catch (\PDOException $e)
   {
      echo $e->getMessage();
   }

}
/**
 * List hashtags
 * @return a list of hashtags names
 */
function list_hashtags() {
	try{
  $db = \Db::dbc();
  $sql="SELECT * FROM TWEET";
  $sth = $db->prepare($sql);
  $sth->execute();
  foreach ($sth->fetchAll()as$row)
    {
  $List=\Model\Post\extract_hashtags($row['NOMET']);
  array_map('\Model\Hashtag\addhashtag', $List);
	}
  $sql1="SELECT  NAMEHT FROM HASHTAG";
  $sth1 = $db->prepare($sql1);
  $sth1->execute();
  foreach ($sth1->fetchAll()as$row1)
   {
   	$List2[]=($row1['NAMEHT']);
  }
    return $List2;
}
catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
}

function gethashtag($pid){
  try{
  $db = \Db::dbc();
  $sql="SELECT * FROM INCLUDE INNER JOIN HASHTAG ON(INCLUDE.IDHT=HASHTAG.IDHT) WHERE IDT='$pid'";
  $sth = $db->prepare($sql);
  $sth->execute();
  foreach ($sth->fetchAll()as$row)
    {
  $List[]=(object)array(
    "hashtagid"=>$row['IDHT'],
    "hashtag"=>$row['NAMEHT']
  );
  return $List;
  }
  }
catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
}


 function getidhashtag($hashtag_name){
  try{
  $db = \Db::dbc();
  $sql="SELECT IDHT FROM HASHTAG WHERE NAMEHT='$hashtag_name'";
   $sth = $db->prepare($sql);
   $sth->execute();
   $row=$sth->fetch();
   if($row){
    return $row['IDHT'];
   }
   else
   {
    return false;
   }
}
catch (\PDOException $e)
   {
      echo $e->getMessage();
   }

 }

/**
 * List hashtags sorted per popularity (number of posts using each)
 * @param length number of hashtags to get at most
 * @return a list of hashtags
 */
function list_popular_hashtags($length) {
  try{
    $db = \Db::dbc();
    $sql="SELECT * FROM TWEET";
    $sth = $db->prepare($sql);
    $sth->execute();
       foreach($sth->fetchAll()as$row) {

  $List=\Model\Post\extract_hashtags($row['NOMET']);
  $idh=array_map('\Model\Hashtag\getidhashtag', $List);
  if($List){
    foreach ($idh as $h) {
    $sql2="INSERT INTO INCLUDE (IDHT,IDT) VALUES ('$h','$row[IDT]')";
    $sth2 = $db->prepare($sql2);
    $sth2->execute();
    }
  }
    }
    $sql3="SELECT NAMEHT FROM INCLUDE INNER JOIN HASHTAG ON(INCLUDE.IDHT=HASHTAG.IDHT) GROUP BY INCLUDE.IDHT ORDER BY COUNT(IDT) DESC LIMIT $length";
    $sth3=$db->prepare($sql3);
    $sth3->execute();
    foreach ($sth3->fetchAll()as$row3) {
        $List2[]=$row3['NAMEHT'];
      } 
      return $List2; 
  }
catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
}


/**
 * Get posts for a hashtag
 * @param hashtag the hashtag name
 * @return a list of posts objects or null if the hashtag doesn't exist
 */
function get_posts($hashtag_name) {
  try{

    $db = \Db::dbc();
    $idht=getidhashtag($hashtag_name);
    $sql="SELECT IDT FROM INCLUDE WHERE IDHT = '$idht'";
    $sth = $db->prepare($sql);
    $sth->execute();
    $i=$sth->rowcount();
    if($i){
    foreach ($sth->fetchAll()as$row) {
    $List[]=\Model\Post\get($row['IDT']);
    }
return $List;
    }
    else{
      return NULL;
    }

  }
  catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
   
}

/** Get related hashtags
 * @param hashtag_name the hashtag name
 * @param length the size of the returned list at most
 * @return an array of hashtags names
 */
function get_related_hashtags($hashtag_name, $length) {
  try{

    $db = \Db::dbc();
    $idht=getidhashtag($hashtag_name);
    $sql="SELECT DISTINCT NAMEHT FROM INCLUDE INNER JOIN HASHTAG ON(INCLUDE.IDHT=HASHTAG.IDHT) WHERE IDT IN(SELECT IDT FROM INCLUDE WHERE IDHT='$idht')AND INCLUDE.IDHT<>'$idht'
    LIMIT $length";
    $sth = $db->prepare($sql);
    $sth->execute();
    $i=$sth->rowcount();
    if($i){
    foreach ($sth->fetchAll()as$row) {
    $List[]=$row['NAMEHT'];
    }
return $List;
    }
    else{
      return NULL;
    }

  }
  catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
   
    //return ["Hello"];
}