<?php
namespace Model\Post;
use \Db;
use \PDOException;
/**
 * Post
 *
 * This file contains every db action regarding the posts
 */

/**
 * Get a post in db
 * @param id the id of the post in db
 * @return an object containing the attributes of the post or false if error
 * @warning the author attribute is a user object
 * @warning the date attribute is a DateTime object
 */
function get($id) {
    try{   
      $db = \Db::dbc();
      $sql = "SELECT * FROM TWEET WHERE IDT='$id'";
      $sth = $db->prepare($sql);
      $sth->execute();
      $row = $sth->fetch();
      if($row==false)
      {
          return NULL;
      }
      else
      {
        return (object) array(
        "id" => $row['IDT'],
        "text" => $row['NOMET'],
        "date" => (object) array ($row ['PDATE']),
        "author" => \Model\User\get($row['IDU']),
        );
      }
   }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }   
    /*return (object) array(
        "id" => 1337,
        "text" => "Text",
        "date" => new \DateTime('2011-01-01T15:03:01'),
        "author" => \Model\User\get(2)
    );*/
}

/**
 * Get a post with its likes, responses, the hashtags used and the post it was the response of
 * @param id the id of the post in db
 * @return an object containing the attributes of the post or false if error
 * @warning the author attribute is a user object
 * @warning the date attribute is a DateTime object
 * @warning the likes attribute is an array of users objects
 * @warning the hashtags attribute is an of hashtags objects
 * @warning the responds_to attribute is either null (if the post is not a response) or a post object
 */
function get_with_joins($id) {
    try 
    {     
      $db = \Db::dbc();
      $sql = "SELECT * FROM TWEET WHERE IDT='$id'";
      $sth = $db->prepare($sql);
      $sth->execute();
      if(empty($sth))
      {
          return NULL;
      }
      else
      {
        $row = $sth->fetch();
        return (object) array
         ("id" => $row['IDT'],
           "text" => $row['TEXT'],
           "date" => $row ['PDATE'],
           "likes" => get_likes($row['IDT']),
           "hashtags" => \Model\hashtag\gethashtag($id),
           "author" => \Model\User\get($row['IDU']),
           "responds_to" => get($row['IDT_TWEET2'])
           );
      }
    }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }   
}
 
/**
 * Create a post in db
 * @param author_id the author user's id
 * @param text the message
 * @param mentioned_authors the array of ids of users who are mentioned in the post
 * @param response_to the id of the post which the creating post responds to
 * @return the id which was assigned to the created post, null if anything got wrong
 * @warning this function computes the date
 * @warning this function adds the mentions (after checking the users' existence)
 * @warning this function adds the hashtags
 * @warning this function takes care to rollback if one of the queries comes to fail.
 */
function create($author_id, $text, $response_to=null) {
    try {
    $db = \Db::dbc();
    $date = new \DateTime();
    $pdate=$date->format('Y-m-d H:i:sP');
    $sql="INSERT INTO TWEET (IDU,IDT_TWEET2,NOMET,PDATE) 
      VALUES (?,?,?,?)";
   $sth =$db->prepare($sql);
   $sth->execute(array($author_id,$response_to,$text,$pdate));
   $last_id=$db->lastInsertId();
   return $last_id; 
    }
    
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Get the list of used hashtags in message
 * @param text the message
 * @return an array of hashtags
 */
function extract_hashtags($text) {
    return array_map(
        function($el) { return substr($el, 1); },
        array_filter(
            explode(" ", $text),
            function($c) {
                return $c !== "" && $c[0] == "#";
            }
        )
    );
}

/**
 * Get the list of mentioned users in message
 * @param text the message
 * @return an array of usernames
 */
function extract_mentions($text) {
    return array_map(
        function($el) { return substr($el, 1); },
        array_filter(
            explode(" ", $text),
            function($c) {
                return $c !== "" && $c[0] == "@";
            }
        )
    );
}

/**
 * Mention a user in a post
 * @param pid the post id
 * @param uid the user id to mention
 * @return true if everything went ok, false else
 */
function mention_user($pid, $uid) {
    try 
   {   
      $db = \Db::dbc();
      $sql = "INSERT INTO MENTION(IDT,IDU) VALUES('$pid','$uid')";
          $sth = $db->prepare($sql);
    $sth->execute();
    
    $row=$sth->rowcount();
    if(empty($row))
    {
      return false;
    }
    else
    {
    return true;
      }
   }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Get mentioned user in post
 * @param pid the post id
 * @return the array of user objects mentioned
 */
function get_mentioned($pid) {
  try{
  $db = \Db::dbc();
  $sql="SELECT NOMET FROM TWEET WHERE IDT='$pid'";
  $sth = $db->prepare($sql);
  $sth->execute();
  $row=$sth->fetch();
  $Text=extract_mentions($row['NOMET']);
  return $List=array_map('\Model\User\getu', $Text);
}
catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
}


/**
 * Delete a post in db
 * @param id the id of the post to delete
 * @return true if the post has been correctly deleted, false else
 */
function destroy($id) {
    try 
  {
   $db = \Db::dbc();
   $sql="DELETE FROM TWEET WHERE IDT='$id'";
   $sth = $db->prepare($sql);
   $sth->execute();
   $row=$sth->rowcount();
   //$sql1="DELETE FROM MENTION WHERE IDT='$id'";
   //$sth1 = $db->prepare($sql1);
   //$sth1->execute();
   if(empty($row))
   {
     return false;
   }
   else
   {
     return true;
   }
  }
   catch (\PDOException $e)
   {
      echo $e->getMessage();
   }
}

/**
 * Search for posts
 * @param string the string to search in the text
 * @return an array of find objects
 */
function search($string) {
    try 
  {
    $db = \Db::dbc();
      $sql ="SELECT * FROM TWEET WHERE NOMET LIKE '%$string%'";
    $sth = $db->prepare($sql);
      $sth->execute();
    if($sth)
        {
          foreach ($sth ->fetchAll()as $row) {
        $List[]=get($row['IDT']);
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
}

/**
 * List posts
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return an array of the objects of each post
 * @warning this function does not return the passwords
 */
function list_all($date_sorted=false) {
    try 
  {
    $db = \Db::dbc();
    if($date_sorted){
      if($date_sorted=='DESC'){
      $sql ="SELECT * FROM TWEET ORDER BY PDATE DESC";
    }
    else{
      $sql ="SELECT * FROM TWEET ORDER BY PDATE ASC";
    }
    }
    else
    {
      $sql ="SELECT * FROM TWEET";
    }
      $sth = $db->prepare($sql);
      $sth->execute();
      foreach($sth ->fetchAll()as $row)
    {   
      $List[]= get($row['IDT']);
    }
    return $List;
  }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Get a user's posts
 * @param id the user's id
 * @param date_sorted the type of sorting on date (false if no sorting asked), "DESC" or "ASC" otherwise
 * @return the list of posts objects
 */
function list_user_posts($id, $date_sorted="DESC") {
    try 
  {
    $db = \Db::dbc();
    if($date_sorted){
      if($date_sorted=='DESC'){
      $sql ="SELECT * FROM TWEET WHERE IDU='$id' ORDER BY PDATE DESC";
    }
    else{
      $sql ="SELECT * FROM TWEET WHERE IDU='$id' ORDER BY PDATE ASC";
    }
    }
    else
    {
      $sql ="SELECT * FROM TWEET WHERE IDU='$id";
    }
      $sth = $db->prepare($sql);
      $sth->execute();
      foreach($sth ->fetchAll()as $row)
    {   
      $List[]= get($row['IDT']);
    }
    return $List;
  }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Get a post's likes
 * @param pid the post's id
 * @return the users objects who liked the post
 */
function get_likes($pid) {
    try 
   {   
      $db = \Db::dbc();
      $sql = "SELECT * FROM LOVE  WHERE IDT='$pid'";
    $sth = $db->prepare($sql);
    $sth->execute();
    if($sth)
    {foreach ($sth ->fetchAll()as$row) {
     $List[]= \Model\User\get($row['IDU']);
    
      }
      return $List;
    }
     else
    { 
      return NULL;
    } 
    }  
   catch (\PDOException $e)
   {
     echo $e->getMessage();
   }
}

/**
 * Get a post's responses
 * @param pid the post's id
 * @return the posts objects which are a response to the actual post
 */
function get_responses($pid) {
   try 
   {   
      $db = \Db::dbc();
      $sql = "SELECT * FROM TWEET  WHERE IDT_TWEET2=?";
    $sth = $db->prepare($sql);
    $sth->execute(array($pid));
    if($sth)
    {foreach ($sth ->fetchAll()as$row) {
     $List[]= get($row['IDT']);
    
      }
      return $List;
    }
     else
    { 
      return NULL;
    } 
    }  
   catch (\PDOException $e)
   {
     echo $e->getMessage();
   }
}
/**
 * Get stats from a post (number of responses and number of likes
 */
function get_stats($pid) {
try 
  {
    $db = \Db::dbc();
      $sql1 ="SELECT * FROM LOVE WHERE IDT='$pid'";
      $res=$db->query($sql1);
    $rowlove=$res->fetchAll();
    
    
    $sql2 ="SELECT * FROM TWEET WHERE IDT_TWEET2='$pid'";
      $res2=$db->query($sql2);
    $rowresponse=$res2->fetchAll();

    return (object) array(
        "nb_likes" => count($rowlove),
        "nb_responses" => count($rowresponse));
    }
    catch(\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Like a post
 * @param uid the user's id to like the post
 * @param pid the post's id to be liked
 * @return true if the post has been liked, false else
 */
function like($uid, $pid) {
    try 
   {   
      $db = \Db::dbc();
      $date = new \DateTime("NOW");
      $ldate=$date->format('Y-m-d H:i:sP');
      $sql = "INSERT INTO LOVE(IDU,IDT,LDATE) VALUES('$uid','$pid','$ldate')";
    $sth = $db->prepare($sql);
    $sth->execute();
    $row=$sth->rowcount();
    if(empty($row))
    {
      return false;
    }
    else
    {
    return true;
      }
   }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Unlike a post
 * @param uid the user's id to unlike the post
 * @param pid the post's id to be unliked
 * @return true if the post has been unliked, false else
 */
function unlike($uid, $pid) {
     try 
   {   
      $db = \Db::dbc();
      $sql = "DELETE FROM LOVE WHERE IDU='$uid'AND IDT='$pid'";
    $sth = $db->prepare($sql);
    $sth->execute();
    $row=$sth->rowcount();
    if(empty($row))
    {
      return false;
    }
    else
    {
    return true;
      }
   }
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }
}

