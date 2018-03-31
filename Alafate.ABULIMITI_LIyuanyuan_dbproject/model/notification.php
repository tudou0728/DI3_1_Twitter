<?php
namespace Model\Notification;
use \Db;
use \PDOException;
/**
 * Notification model
 *
 * This file contains every db action regarding the notifications
 */

/**
 * Get a liked notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the post attribute is a post object
 * @warning the liked_by attribute is a user object
 * @warning the date attribute is a DateTime object
 * @warning the reading_date attribute is either a DateTime object or null (if it hasn't been read)
 */
function get_liked_notifications($uid) {
     try
    {   
    $db = \Db::dbc();
    $sql ="SELECT LOVE.IDU,LOVE.IDT,LDATE,LLOOKORNOT FROM LOVE INNER JOIN TWEET ON(LOVE.IDT=TWEET.IDT) WHERE TWEET.IDU='$uid'";
    $sth=$db->prepare($sql);
    $sth->execute();
    $l=$sth->rowcount();
   if($l)
        {
            foreach($sth->fetchAll() as $row)
            { $o=new \DateTime($row['LDATE']);
            $List[]=(object) array(
            "type" => "liked",
            "post" =>\Model\Post\get($row['IDT']),
            "liked_by" =>\Model\User\get($row['IDU']),
            "date" => $o,
            "reading_date" => $row['LLOOKORNOT']);
                  
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
 * Mark a like notification as read (with date of reading)
 * @param pid the post id that has been liked
 * @param uid the user id that has liked the post
 * @return true if everything went ok, false else
 */
function liked_notification_seen($pid, $uid) {
    try 
   {   
      $db = \Db::dbc();
      $date = new \DateTime();
      $ldate=$date->format('Y-m-d H:i:sP');
      $sql = "UPDATE LOVE SET LLOOKORNOT='$ldate' WHERE IDT='$pid' AND IDU='$uid'";
      
    $sth=$db->prepare($sql);
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
 * Get a mentioned notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the post attribute is a post object
 * @warning the mentioned_by attribute is a user object
 * @warning the reading_date object is either a DateTime object or null (if it hasn't been read)
 */
function get_mentioned_notifications($uid) {
    try
    {   
    $db = \Db::dbc();
    $sql ="SELECT TWEET.IDU,MENTION.IDT,MLOOKORNOT,TWEET.PDATE FROM MENTION INNER JOIN TWEET ON(MENTION.IDT=TWEET.IDT) WHERE MENTION.IDU='$uid'";
    $sth=$db->prepare($sql);
    $sth->execute();
    $l=$sth->rowcount();
   if($l)
        {
            foreach($sth->fetchAll() as $row){
              $o=new \DateTime($row['PDATE']);
            $List[]=(object) array(
            "type" => "mentioned",
            "post" =>\Model\Post\get($row['IDT']),
            "mentioned_by" =>\Model\User\get($row['IDU']),
            "date" => $o,
            "reading_date" => $row['MLOOKORNOT']);
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
 * Mark a mentioned notification as read (with date of reading)
 * @param uid the user that has been mentioned
 * @param pid the post where the user was mentioned
 * @return true if everything went ok, false else
 */
function mentioned_notification_seen($uid, $pid) {
    try 
   {   
      $db = \Db::dbc();
      $date = new \DateTime("NOW");
      $mdate=$date->format('Y-m-d H:i:sP');
      $sql = "UPDATE MENTION SET MLOOKORNOT='$mdate' WHERE IDT='$pid' AND IDU='$uid'";
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
 * Get a followed notification in db
 * @param uid the id of the user in db
 * @return a list of objects for each like notification
 * @warning the user attribute is a user object which corresponds to the user following.
 * @warning the reading_date object is either a DateTime object or null (if it hasn't been read)
 */
function get_followed_notifications($uid) {
    try
    {   
    $db = \Db::dbc();
    $sql ="SELECT IDU_USER2,FLOOKORNOT,FDATE FROM FOLLOW WHERE IDU_USER1='$uid'";
    $sth=$db->prepare($sql);
    $sth->execute();
    $l=$sth->rowcount();
   if($l)
        {
            foreach($sth->fetchAll() as $row){
              $o=new \DateTime($row['FDATE']);
            $List[]=(object) array(
            "type" => "followed",
            "user" =>\Model\User\get($row['IDU_USER2']),
            "date" => $o,
            "reading_date" => $row['FLOOKORNOT']);
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
 * Mark a followed notification as read (with date of reading)
 * @param followed_id the user id which has been followed
 * @param follower_id the user id that is following
 * @return true if everything went ok, false else
 */
function followed_notification_seen($followed_id, $follower_id) {
    try 
   {   
      $db = \Db::dbc();
      $date = new \DateTime("NOW");
      $fdate=$date->format('Y-m-d H:i:sP');
      $sql = "UPDATE FOLLOW SET FLOOKORNOT='$fdate' WHERE IDU_USER1='$followed_id' AND IDU_USER2='$follower_id'";
    $sth = $db->prepare($sql);
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
    catch (\PDOException $e)
    {
      echo $e->getMessage();
    }
}

/**
 * Get all the notifications sorted by time (descending order)
 * @param uid the user id
 * @return a sorted list of every notifications objects
 */
function list_all_notifications($uid) {
    $ary = array_merge(get_liked_notifications($uid), get_followed_notifications($uid), get_mentioned_notifications($uid));
    usort(
        $ary,
        function($a, $b) {
            return $b->date->format('U') - $a->date->format('U');
        }
    );
    return $ary;
}

/**
 * Mark a notification as read (with date of reading)
 * @param uid the user to whom modify the notifications
 * @param notification the notification object to mark as seen
 * @return true if everything went ok, false else
 */
function notification_seen($uid, $notification) {
    switch($notification->type) {
        case "liked":
            return liked_notification_seen($notification->post->id, $notification->liked_by->id);
        break;
        case "mentioned":
            return mentioned_notification_seen($uid, $notification->post->id);
        break;
        case "followed":
            return followed_notification_seen($uid, $notification->user->id);
        break;
    }
    return false;
}

