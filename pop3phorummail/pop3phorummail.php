<?php

////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2008  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////
/*
 * This script is derived from 
 * 1) http://www.phorum.org/phorum5/read.php?16,121015
 *    pop3-collector from woleium
 * 
 * 2) http://www.phorum.org/phorum5/read.php?16,14081
 *    Phorummail by Brian Moon
 * 
 * Updated, reworked, turned into a module for current 5.2 (tested with 5.2.9)
 * and added attachment import feature
 * by Thomas Seifert ( thomas@phorum.org )
 * 
 * 
 * You usually only need to change the defines below and setup a cronjob
 * to have this script run in regular intervals.
 * 
 * It does *not* import any attachments despite the setting for it below.
 * 
 */

define ( "PMAIL_USE_EMAIL_FOR_LOGIN", true );
define ( "PMAIL_SAVE_ATTACHMENTS", true );
define ( "PMAIL_USE_BBCODE", true );

function phorum_pop3phorummail_scheduled() {
	global $PHORUM;
	
	if(isset($PHORUM['mod_pop3phorummail']['forum_pop3servers']) 
	   && is_array($PHORUM['mod_pop3phorummail']['forum_pop3servers']) ) {
	   	
	           
       include_once("./include/email_functions.php");
       include_once("./include/thread_info.php");
       include_once("./include/profile_functions.php");
	   include_once('./include/api/file_storage.php');

	   foreach($PHORUM['mod_pop3phorummail']['forum_pop3servers'] as $forum_id => $forum_pop3server) {
	   	
	   	    // is the import enabled?
	   	    if(empty($forum_pop3server['enabled'])) {
	   	    	continue;
	   	    }
	
			$_REQUEST ["forum_id"] = $forum_id;
		    $GLOBALS['PHORUM']["forum_id"] = $forum_id;
		
			$pop3 = new POP3 ();
			// Connect to mail server
			$do = $pop3->connect ( $forum_pop3server['hostname'] );
			if ($do == false) {
				die ( $pop3->error );
			}
		
			// Login to your inbox
			$do = $pop3->login ( $forum_pop3server['username'], $forum_pop3server['password'] );
			if ($do == false) {
				die ( $pop3->error );
			}
			//fwrite(STDOUT,"Logged in\n");
			// Get office status
			$status = $pop3->get_office_status ();
			if ($status == false) {
				die ( $pop3->error );
			}
			$count = $status ['count_mails'];
		
			for($i = 1; $i <= $count; $i ++) {
				$email = $pop3->get_mail ( $i );
				if ($email == false) {
					continue;
				}
		
				// Split header and message
				$header = array ();
				$message = array ();
		
				$is_header = true;
				foreach ( $email as $line ) {
					if ($line == '<HEADER> ' . "\r\n")
					continue;
					if ($line == '<MESSAGE> ' . "\r\n")
					continue;
					if ($line == '</MESSAGE> ' . "\r\n")
					continue;
					if ($line == '</HEADER> ' . "\r\n") {
						$is_header = false;
						continue;
					}
		
					if ($is_header == true) {
						$header [] = $line;
					} else {
						$message [] = $line;
					}
				}
		
				$message = implode ( '', $header ) . "\n" . implode ( '', $message );
		
				phorummail ( $message, $forum_id );
		
				// Remove from mail server
				$do = $pop3->delete_mail ( $i );
				if ($do == false) {
					//echo $pop3->error;
				}
			}
		
			$pop3->close ();
			
			unset($pop3);
	
	   }
	
	
	}

}

function phorummail($message, $forum_id) {
    GLOBAL $PHORUM;
    
    $_REQUEST ["forum_id"] = $forum_id;
    
    // this will require the PEAR module be installed
    require_once 'Mail/mimeDecode.php';
    
    $args ['include_bodies'] = true;
    $args ['decode_bodies'] = true;
    $args ['decode_headers'] = true;
    $args ['input'] = $message;
    $sections = Mail_mimeDecode::decode ( $args );
    
    $headers = $sections->headers;
    
    if (isset($headers ["x-mailer"]) && $headers ["x-mailer"] == "Phorum5") {
        return;
    }
    
    $new_message = array ("forum_id" => $forum_id, "datestamp" => 0, "thread" => 0, "parent_id" => 0, "author" => "", "subject" => "", "email" => "", "ip" => "", "user_id" => 0, "moderator_post" => 0, "status" => PHORUM_STATUS_APPROVED, "sort" => PHORUM_SORT_DEFAULT, "msgid" => "", "body" => "", "closed" => 0 );
    

    if (preg_match ( '!"(.+?)"\s*<(.+?)>!', $headers ["from"], $matches )) {
        $new_message ["author"] = trim ( $matches [1] );
        $new_message ["email"] = trim ( $matches [2] );
    } elseif (preg_match ( '!([^<]+?)\s*<(.+?)>!', $headers ["from"], $matches )) {
        $new_message ["author"] = trim ( $matches [1] );
        $new_message ["email"] = trim ( $matches [2] );
    } elseif (preg_match ( '!<(.+?)>!', $headers ["from"], $matches )) {
        $new_message ["author"] = trim ( $matches [1] );
        $new_message ["email"] = trim ( $matches [1] );
    } else {
        $new_message ["author"] = trim ( $matches [1] );
        $new_message ["email"] = trim ( $matches [1] );
    }

    if (PMAIL_USE_EMAIL_FOR_LOGIN) {
        $new_message["user_id"] = phorum_api_user_search ( "email", $new_message ["email"] );
        if(!empty($new_message["user_id"])) {
            $user = phorum_api_user_get ( $new_message ["user_id"], false );
            $new_message ["author"] = $user ["username"];
            $new_message ["email"] = "";
            $GLOBALS ["PHORUM"] ["user"] ["user_id"] = $new_message ["user_id"];
        }
    }
    
    // init
    $new_post = true;
    
    $headers ["subject"] = str_replace ( "[" . $PHORUM ["name"] . "] ", "", $headers ["subject"] );
    //echo $headers ["subject"] . "---" . $PHORUM ["name"];
    // check for reply-to header and use it if we can get a match
    if (! empty ( $headers ["in-reply-to"] )) {
        $msgid = $headers["in-reply-to"];
		if($msgid[0] == "<" && $msgid[strlen($msgid)-1] == ">") {
			$msgid = substr($msgid, 1, -1);
		}
        if (strpos ( $msgid, "@" ) !== false) {
            $msgid = substr ( $msgid, 0, strpos ( $msgid, "@" ) - 1 );
        }
        $message = phorum_db_get_message ( $msgid, "msgid" );
        if ($message) {
            $new_post = false;
            $new_message ["parent_id"] = $message ["message_id"];
            $new_message ["thread"] = $message ["thread"];
        }
    }
    
    // if we did not get a match on reply-to, try references header
    if ($new_post && ! empty ( $headers ["references"] )) {
        if (preg_match_all ( '!<(.+?)>!', $headers ["references"], $matches )) {
            $ids = array_reverse ( $matches [1] );
            foreach ( $ids as $id ) {
                $message = phorum_db_get_message ( $id, "msgid" );
                if ($message) {
                    $new_post = false;
                    $new_message ["parent_id"] = $message ["message_id"];
                    $new_message ["thread"] = $message ["thread"];
                    break;
                }
            }
        }
    }
    
    // lastly, if the subject has re: or aw: in it, try a match there.
    if ($new_post && strtolower ( substr ( $headers ["subject"], 0, 3 ) ) == "re:" || strtolower ( substr ( $headers ["subject"], 0, 3 ) ) == "aw:") {
        $trim_sub = trim ( substr ( $headers ["subject"], 3 ) );
        $message = phorum_db_get_message ( $trim_sub, "subject" );
        if ($message) {
            $new_post = false;
            $new_message ["parent_id"] = $message ["message_id"];
            $new_message ["thread"] = $message ["thread"];
        }
    }
    
    if ($PHORUM ["moderation"] == PHORUM_MODERATE_ON && ! phorum_api_user_access_allowed ( PHORUM_USER_ALLOW_MODERATE_MESSAGES )) {
        $message ["status"] = PHORUM_STATUS_HOLD;
    }
    
    $new_message ["datestamp"] = strtotime ( $headers ["date"] );
    $new_message ["subject"] = $headers ["subject"];

    if (empty($new_message["subject"])) {
		$new_message["subject"] = '(no subject)';
	}

    if ($sections->body) {
        
        $new_message ["body"] = $sections->body;
        
        if ($sections->ctype_secondary == "html") {
            $new_message ["body"] = strip_tags ( $new_message ["body"] );
        }
        
        if ($headers ["content-transfer-encoding"] == "quoted-printable") {
            $new_message ["body"] = quoted_printable_decode ( $new_message ["body"] );
        }
    
    } elseif ($sections->parts) {
        
            
		$new_message["body"] = searchpart($sections);

		// if we found no text/plain part, use the first part
		// This should be rare, but you can never tell.
		if(empty($new_message["body"])){

			$new_message["body"]=$sections->parts[0]->body;

			// give it shot to see if it is HTML
			if($sections->parts[0]->ctype_secondary=="html"){
				$new_message["body"]=strip_tags($new_message["body"]);
			}

		 }

         $new_message['attachments']=array();

         if(PMAIL_SAVE_ATTACHMENTS) {

             // now going over it again for attachments
             foreach ( $sections->parts as $part ) {

                // only save if this part is an attachment (one manner they can be stored in the message)
                if (isset($part->ctype_parameters['name'])) {

                  $new_message['attachments'][]=array(
                                                'name'=>$part->ctype_parameters['name'],
                                                'file_data'=>$part->body,
                                                );
                }

                // only save if this part is an attachment (disposition method)
                if ((isset($part->disposition)) && ($part->disposition=='attachment')) {
                  // open file
                  /*$fp = fopen($part->ctype_parameters['filename'], 'w');
                  // write body
                  fwrite($fp, $part->body);
                  // close file
                  fclose($fp);
                  $attachment[] = $part->ctype_parameters['name'];*/
                  // TODO???
                  $new_message['attachments'][]=array(
                                                'name'=>$part->ctype_parameters['name'],
                                                'file_data'=>$part->body,
                                                );

                }
            
            }
        }
        
        // if we found no text/plain part, use the first part
        // This should be rare, but you can never tell.
        if (empty ( $new_message ["body"] )) {
            
            $new_message ["body"] = $sections->parts[0]->body;
            
            // give it shot to see if it is HTML
            if ($sections->parts [0]->ctype_secondary == "html") {
                $new_message ["body"] = strip_tags ( $new_message ["body"] );
            }
        
        }
    
    }
    
    $new_message ["body"] = trim ( $new_message ["body"] );
    
    $new_message ["msgid"] = substr ( $headers ["message-id"], 1, - 1 );
    
    $new_message ["ip"] = "via email";

    $error = "";
    
    if (! phorum_check_ban_lists ( $new_message ["author"], PHORUM_BAD_NAMES )) {
        $error = $PHORUM ["DATA"] ["LANG"] ["ErrBannedName"];
    } elseif (! phorum_check_ban_lists ( $new_message ["email"], PHORUM_BAD_EMAILS )) {
        $error = $PHORUM ["DATA"] ["LANG"] ["ErrBannedEmail"];
    } elseif (strlen ( $new_message ['body'] ) > 64000) {
        $error = $PHORUM ['DATA'] ['LANG'] ['ErrBodyTooLarge'];
    }
    
    if (empty($error)) {
        // get our attachments into a separate array
        if(isset($new_message['attachments'])) {
            $attachments = $new_message['attachments'];
            unset($new_message['attachments']);
        } else {
            $attachments = array();
        }

        $success = phorum_db_post_message ( $new_message );

        if ($success) {

            if(PMAIL_SAVE_ATTACHMENTS && is_array($attachments) && count($attachments)) {

                $inserted_files=array();

                foreach($attachments as $attachment) {

                    $filearray = array(
                                "filename"  => $attachment['name'],
                                "file_data" => $attachment['file_data'],
                                "filesize"  => strlen($attachment['file_data']),
                                "link" => PHORUM_LINK_MESSAGE,
                                "user_id" => $new_message['user_id'],
                                "message_id" => $new_message['message_id']
                    );

                    $stored_file = phorum_api_file_store($filearray);
                        
                    $inserted_files[]=array(
                                        "file_id" => $stored_file['file_id'], 
                                        "name"    => $attachment['name'], 
                                        "size"    => $filearray['filesize']
                                      );
            
                }
                // update the meta-data
                if(count($inserted_files)) {
                    $save_message = array();
                    $save_message['meta']['attachments']=$inserted_files;
                    phorum_db_update_message($new_message["message_id"], $save_message);
                }

            }

            phorum_update_thread_info ( $new_message ["thread"] );
            
            if (! empty ( $new_message ["user_id"] )) {
                // Increase the user's post count.
                phorum_api_user_increment_posts ();
            }
            // Actions for messages which are approved.
            if ($new_message ["status"] > 0) {
                // Update forum statistics.
                phorum_db_update_forum_stats ( false, 1, $new_message ["datestamp"] );
                
                // Mail subscribed users.
                phorum_email_notice ( $new_message );
            
            }
            
            if ($PHORUM ["email_moderators"] == PHORUM_EMAIL_MODERATOR_ON) {
                // mailing moderators
                phorum_email_moderators ( $new_message );
            }
        }
    } else {
        
        mail ( $PHORUM ["system_email_from_address"], "Phorummail error", "There was an error delivering the following email via Phorummail\n\n$error\n\n$message", "From: $PHORUM[system_email_from_address]" );
    
    }

}

//////////////////////////////////////////////////////////pop3 class/////////////////////////////////////////////////////////////////////
/*
  Class pop3.class.inc
  Author: Jointy <bestmischmaker@web.de>
  create: 07. May 2003
  last cahnge: 25. July 2006

  Version: 1.16 (final)

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

  ChangeLog:

  // 08 May 2003
  - Version 0.52 (beta) public coming out !!!
  - add logging

  // 09 May 2003
  - Version 0.61 out !!!
  - add get_top() function (public)

  -------------------------
  ! POP3 Class get Public !
  -------------------------

  // 10 May 2003
  - add reset() function (public)
  - add _checkstate($string) function (private)
  - add _stats() function (private)
  - add uidl($msg_number) function (public)

  // 11 May 2003
  - add save2mysql function (public) (beta)
  - fixed some errors !!!

  // 14 May 2003
  - fixed a heavy bug with APOP Server (private func _parse_banner($server_text))
  (so sometimes the APOP Authorization goes failed, although the password was correct !!)

  // 15 May 2003
  - changed error handling in get_office_status(), get_top(), get_mail()
  - add APOP Autodetection !!! more in Readme.txt

  -----------------------------------
  POP3 Class get Version 1.00 (final)
  -----------------------------------

  // 16 May 2003
  - finished save2mysql() function ( public )

  // 17 May 2003
  - remove some bugs ( save2mysql() )
  ///////////////////////////////////
  finished pop3.class.inc  Version: 1.10
  ///////////////////////////////////

  // 20 May 2003
  - fixed a bug in get_top()

  // 12. July 2003
  - fix a bug in get_top()

  // 26. July 2003
  // fixed an error with noob ! Thanks to "Martin Eisenfuehrer.de" <martin@eisenfuehrer.de>" !
  !!! it doesn't named "noob", it is named "noop" !!!

  so now the func named "noop()" and it will send the right command "NOOP"

  // version 1.14 (final) out

  // 22. M&auml;rz 2004
  - fix a bug while checking sock_timeout
  - fix a bug in _cleanup() while check $this->socket if resource , now it use is_resource function
  - fix a bug in _cleanup() and _getnextstring() with $this->socket_status
    ( php always reported a notice: undefined property (thats fixed) dont do an unset() on a class vars :) )

  // 24. M&auml;rz 2004 version 1.15 (-fix update-)


  // 25 July 2006 version 1.16 (-fix update-)
  - Qmail does send a point (.) instead an (+OK) by getting an email by the get_mail() function.
    By the 3 parameter $qmailer you can say that we communicate with an qmail server
    
    BEST THANKS TO Daniel Sepeur that he post this cool bug fix on phpclasses.org

  
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
*/
class POP3 {
    // Socket Vars
    var $socket = FALSE;
    var $socket_status = FALSE;
    var $socket_timeout = "10,500";
    
    var $error = "No Errors";
    var $state = "DISCONNECTED";
    var $apop_banner = "";
    var $apop_detect;
    
    var $log;
    var $log_file;
    var $log_fp;
    
    var $file_fp;
    var $mysql_socket;
    
    // Constructor
    function POP3($log = FALSE, $log_file = "", $apop_detect = FALSE) {
        $this->log = $log;
        $this->log_file = $log_file;
        $this->apop_detect = $apop_detect;
    }
    /*
      Function _cleanup()
      Access: Private
    */
    function _cleanup() {
        $this->state = "DISCONNECTED";
        
        if (is_array ( $this->socket_status ))
            $this->socket_status = FALSE;
        
        if (is_resource ( $this->socket )) {
            socket_set_blocking ( $this->socket, false );
            @fclose ( $this->socket );
            $this->socket = FALSE;
        }
        
        $close_log = "Connection Closed. \r\n";
        $close_log .= "/------------------------------------------------------------------- \r\n";
        $close_log .= "/--- Log File: " . $this->log_file . " \r\n";
        $close_log .= "/--- Log Close: " . date ( 'l, d M Y @ H:i:s' ) . " \r\n";
        $close_log .= "/------------------------------------------------------------------- \r\n";
        
        if ($this->log)
            $this->_logging ( $close_log );
        
        if (is_resource ( $this->mysql_socket )) {
            @mysql_close ( $this->mysql_socket );
            $this->mysql_socket = FALSE;
        }
        if (is_resource ( $this->log_fp )) {
            @fclose ( $this->log_fp );
            $this->mysql_socket = FALSE;
        }
        unset ( $close_log );
    }
    
    /*
      Function _logging($string)
      Access: Private
    */
    function _logging($string) {
        if ($this->log) {
            if (! $this->log_fp) {
                $this->log_fp = @fopen ( $this->log_file, "a+" );
                if (! $this->log_fp) {
                    $this->error = "POP3 _logging() - Error: Can't open log file in write mode (" . $this->log_file . ") !!! -- Connection Closed !!!";
                    $this->_cleanup ();
                    return FALSE;
                }
                $open_log = "/------------------------------------------------------------------- \r\n";
                $open_log .= "/--- Log File: " . $this->log_file . " \r\n";
                $open_log .= "/--- Log Open: " . date ( 'l, d M Y @ H:i:s' ) . " \r\n";
                $open_log .= "/------------------------------------------------------------------- \r\n";
                
                if (! @fwrite ( $this->log_fp, $open_log, strlen ( $open_log ) )) {
                    $this->error = "POP3 _logging() - Error: Can't write string to file !!!";
                    $this->_cleanup ();
                    return FALSE;
                }
                unset ( $open_log );
            }
            if (substr ( $string, 0, 1 ) != "-" && substr ( $string, 0, 1 ) != "+" && substr ( $string, - 4 ) != "\r\n" && substr ( $string, - 2 ) != "\n") {
                $string = $string . "\r\n";
            }
            
            $string = date ( "H:i:s" ) . " -- " . $string;
            if (! @fwrite ( $this->log_fp, $string, strlen ( $string ) )) {
                $this->error = "POP3 _logging() - Error: Can't write string to file !!! -- Connection Closed !!!";
                $this->_cleanup ();
                return FALSE;
            }
        
        }
        return TRUE;
    }
    
    /*
      Function connect($server, $port, $timeout, $sock_timeout)
      Access: Public

      // Vars:
      - $server ( Server IP or DNS )
      - $port ( Server port default is "110" )
      - $timeout ( Connection timeout for connect to server )
      - $sock_timeout ( Socket timeout for all actions   (10 sec 500 msec) = (10,500))


      If all right you get true, when not you get false and on $this->error = msg !!!
    */
    function connect($server, $port = "110", $timeout = "25", $sock_timeout = "10,500") {
        if ($this->socket) {
            $this->error = "POP3 connect() - Error: Connection also avalible !!!";
            return FALSE;
        }
        
        if (! trim ( $server )) {
            $this->error = "POP3 connect() - Error: Please give a server address.";
            return FALSE;
        }
        
        if ($port < "1" && $port > "65535" || ! trim ( $port )) {
            $this->error = "POP3 connect() - Error: Port not set or out of range (1 - 65535)";
            return FALSE;
        }
        
        if ($timeout < 0 && $timeout > 25 || ! trim ( $timeout )) {
            $this->error = "POP3 connect() - Error: Connection Timeout not set or out of range (0 - 25)";
            return FALSE;
        }
        $sock_timeout = explode ( ",", $sock_timeout );
        if (! trim ( $sock_timeout [0] ) || ($sock_timeout [0] < 0 && $sock_timeout [0] > 25)) // || !preg_match("^[0-9]",sock_timeout[1]) )
{
            $this->error = "POP3 connect() - Error: Socket Timeout not set or out of range (0 - 25)";
            return FALSE;
        }
        /*
        if(!ereg("([0-9]{2}),([0-9]{3})",$sock_timeout))
        {
            $this->error = "POP3 connect() - Error: Socket Timeout in invalid Format (Right Format xx,xxx \"10,500\")";
            return FALSE;
        }
        */
        // Check State
        if (! $this->_checkstate ( "connect" ))
            return FALSE;
        
        if (! $this->socket = @fsockopen ( $server, $port, $errno, $errstr, $timeout )) {
            $this->error = "POP3 connect() - Error: Can't connect to Server. Error: " . $errno . " -- " . $errstr;
            return FALSE;
        }
        
        if (! $this->_logging ( "Connecting to \"" . $server . ":" . $port . "\" !!!" ))
            return FALSE;
            
        // Set Socket Timeout
        // It is valid for all other functions !!
        socket_set_timeout ( $this->socket, $sock_timeout [0], $sock_timeout [1] );
        socket_set_blocking ( $this->socket, true );
        
        $response = $this->_getnextstring ();
        
        if (! $this->_logging ( $response )) {
            $this->_cleanup ();
            return FALSE;
        }
        
        if (substr ( $response, 0, 1 ) != "+") {
            $this->_cleanup ();
            $this->error = "POP3 connect() - Error: " . $response;
            return FALSE;
        }
        
        // Get the server banner for APOP
        $this->apop_banner = $this->_parse_banner ( $response );
        
        $this->state = "AUTHORIZATION";
        if (! $this->_logging ( "STATUS: AUTHORIZATION" ))
            return FALSE;
        
        return TRUE;
    
    }
    
    /*
      Function _login($user, $pass)
      Access: Public
    */
    
    function login($user, $pass, $apop = "0") {
        if (! $this->socket) {
            $this->error = "POP3 login() - Error: No connection avalible.";
            $this->_cleanup ();
            return FALSE;
        }
        
        if ($this->_checkstate ( "login" )) {
            
            if ($this->apop_detect) {
                if ($this->apop_banner != "") {
                    $apop = "1";
                }
            }
            
            if ($apop == "0") {
                
                $response = "";
                $cmd = "USER $user";
                if (! $this->_logging ( $cmd ))
                    return FALSE;
                if (! $this->_putline ( $cmd ))
                    return FALSE;
                
                $response = $this->_getnextstring ();
                
                if (! $this->_logging ( $response ))
                    return FALSE;
                
                if (substr ( $response, 0, 1 ) == "-") {
                    $this->error = "POP3 login() - Error: " . $response;
                    $this->_cleanup ();
                    return FALSE;
                }
                
                $response = "";
                $cmd = "PASS $pass";
                if (! $this->_logging ( "PASS " . md5 ( $pass ) ))
                    return FALSE;
                if (! $this->_putline ( $cmd ))
                    return FALSE;
                $response = $this->_getnextstring ();
                if (! $this->_logging ( $response ))
                    return FALSE;
                if (substr ( $response, 0, 1 ) == "-") {
                    $this->error = "POP3 login() - Error: " . $response;
                    $this->_cleanup ();
                    return FALSE;
                }
                $this->state = "TRANSACTION";
                if (! $this->_logging ( "STATUS: TRANSACTION" ))
                    return FALSE;
                return TRUE;
            
            } elseif ($apop == "1") {
                // APOP Section
                

                // Check is Server Banner for APOP Command given !!!
                if (empty ( $this->apop_banner )) {
                    $this->error = "POP3 login() (APOP) - Error: No Server Banner -- aborted and close connection";
                    $this->_cleanup ();
                    return FALSE;
                }
                //echo $this->apop_banner;
                
                $response = "";
                
                // Send APOP Command !!!
                

                $cmd = "APOP " . $user . " " . md5 ( $this->apop_banner . $pass );
                
                if (! $this->_logging ( $cmd ))
                    return FALSE;
                if (! $this->_putline ( $cmd ))
                    return FALSE;
                $response = $this->_getnextstring ();
                
                if (! $this->_logging ( $response ))
                    return FALSE;
                    // Check the response !!!
                if (substr ( $response, 0, 1 ) != "+") {
                    $this->error = "POP3 login() (APOP) - Error: " . $response;
                    $this->_cleanup ();
                    return FALSE;
                }
                $this->state = "TRANSACTION";
                if (! $this->_logging ( "STATUS: TRANSACTION" ))
                    return FALSE;
                return TRUE;
            
            } else {
                $this->error = "POP3 login() - Error: Please set apop var !!! (1 [true] or 0 [false]).";
                $this->_cleanup ();
                return FALSE;
            }
        
        }
        
        return FALSE;
    }
    /*
      Func get_top($msg_number,$lines)
      Access: Public
    */
    function get_top($msg_number, $lines = "0") {
        if (! $this->socket) {
            $this->error = "POP3 get_top() - Error: No connection avalible.";
            return FALSE;
        }
        
        if (! $this->_checkstate ( "get_top" ))
            return FALSE;
        
        $response = "";
        $cmd = "TOP " . $msg_number . " " . $lines;
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        
        $response = $this->_getnextstring ();
        
        if (! $this->_logging ( $response ))
            return FALSE;
        
        if (substr ( $response, 0, 3 ) != "+OK") {
            $this->error = "POP3 get_top() - Error: " . $response;
            return FALSE;
        }
        // Get Header
        $i = "0";
        $response = "<HEADER> \r\n";
        while ( ! eregi ( "^\.\r\n", $response ) ) {
            if (substr ( $response, 0, 4 ) == "\r\n")
                break;
            $output [$i] = $response;
            $i ++;
            $response = $this->_getnextstring ();
        }
        if ($lines == "0") {
            $response = $this->_getnextstring ();
        }
        $output [$i ++] = "</HEADER> \r\n";
        // Get $lines
        if ($lines != "0") {
            $response = "<MESSAGE> \r\n";
            for($g = 0; $g < $lines; $g ++) {
                if (eregi ( "^\.\r\n", $response ))
                    break;
                $output [$i] = $response;
                $i ++;
                $response = $this->_getnextstring ();
            }
            $output [$i] = "</MESSAGE> \r\n";
        }
        
        if (! $this->_logging ( "Complete." ))
            return FALSE;
        
        return $output;
    }
    
    /*
      Function get_mail
      Access: Public
    */
    function get_mail($msg_number, $qmailer = FALSE) {
        if (! $this->socket) {
            $this->error = "POP3 get_mail() - Error: No connection avalible.";
            
            return FALSE;
        }
        
        if (! $this->_checkstate ( "get_mail" ))
            return FALSE;
        
        $response = "";
        $cmd = "RETR $msg_number";
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        
        $response = $this->_getnextstring ();
        
        if (! $this->_logging ( $response ))
            return FALSE;
        
        if ($qmailer == TRUE) {
            if (substr ( $response, 0, 1 ) != '.') {
                $this->error = "POP3 get_mail() - Error: " . $response;
                return FALSE;
            }
        } else {
            if (substr ( $response, 0, 3 ) != "+OK") {
                $this->error = "POP3 get_mail() - Error: " . $response;
                return FALSE;
            }
        }
        
        // Get MAIL !!!
        $i = "0";
        $response = "<HEADER> \r\n";
        while ( ! eregi ( "^\.\r\n", $response ) ) {
            if (substr ( $response, 0, 4 ) == "\r\n")
                break;
            $output [$i] = $response;
            $i ++;
            $response = $this->_getnextstring ();
        }
        $output [$i ++] = "</HEADER> \r\n";
        
        $response = "<MESSAGE> \r\n";
        
        while ( ! eregi ( "^\.\r\n", $response ) ) {
            $output [$i] = $response;
            $i ++;
            $response = $this->_getnextstring ();
        }
        
        $output [$i] = "</MESSAGE> \r\n";
        
        if (! $this->_logging ( "Complete." ))
            return FALSE;
        
        return $output;
    }
    
    /*
       Function _check_state()
       Access: Private

    */
    
    function _checkstate($string) {
        // Check for delete_mail func
        if ($string == "delete_mail" || $string == "get_office_status" || $string == "get_mail" || $string == "get_top" || $string == "noop" || $string == "reset" || $string == "uidl" || $string == "stats") {
            $state = "TRANSACTION";
            if ($this->state != $state) {
                $this->error = "POP3 $string() - Error: state must be in \"$state\" mode !!! Your state: \"$this->state\" !!!";
                return FALSE;
            }
            return TRUE;
        }
        
        // Check for connect func
        if ($string == "connect") {
            $state = "DISCONNECTED";
            $state_1 = "UPDATE";
            if ($this->state == $state or $this->state == $state_1) {
                return TRUE;
            }
            $this->error = "POP3 $string() - Error: state must be in \"$state\" or \"$state_1\" mode !!! Your state: \"$this->state\" !!!";
            return FALSE;
        
        }
        
        // Check for login func
        if ($string == "login") {
            $state = "AUTHORIZATION";
            if ($this->state != $state) {
                $this->error = "POP3 $string() - Error: state must be in \"$state\" mode !!! Your state: \"$this->state\" !!!";
                return FALSE;
            }
            return TRUE;
        }
        $this->error = "POP3 _checkstate() - Error: Not allowed string given !!!";
        return FALSE;
    }
    
    /*
      Function delete_mail($msg_number)
      Access: Public


    */
    
    function delete_mail($msg_number = "0") {
        if (! $this->socket) {
            $this->error = "POP3 delete_mail() - Error: No connection avalible.";
            return FALSE;
        }
        if (! $this->_checkstate ( "delete_mail" ))
            return FALSE;
        
        if ($msg_number == "0") {
            $this->error = "POP3 delete_mail() - Error: Please give a valid Messagenumber (Number can't be \"0\").";
            return FALSE;
        }
        // Delete Mail
        $response = "";
        $cmd = "DELE $msg_number";
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        $response = $this->_getnextstring ();
        if (! $this->_logging ( $response ))
            return FALSE;
        if (substr ( $response, 0, 1 ) != "+") {
            $this->error = "POP3 delete_mail() - Error: " . $response;
            return FALSE;
        }
        
        return TRUE;
    }
    
    /*
      Function get_office_status
      Access: Public

      Output an array

      Array
     (
        [count_mails] => 3
        [octets] => 2496
        [1] => Array
              (
                  [size] => 832
                  [uid] => 617999468
              )

        [2] => Array
              (
                  [size] => 882
                  [uid] => 617999616
              )

        [3] => Array
              (
                  [size] => 1726
                  [uid] => 617999782
              )

        [error] => No Errors
     )

    */
    
    function get_office_status() {
        
        if (! $this->socket) {
            $this->error = "POP3 get_office_status() - Error: No connection avalible.";
            $this->_cleanup ();
            return FALSE;
        }
        
        if (! $this->_checkstate ( "get_office_status" )) {
            $this->_cleanup ();
            return FALSE;
        }
        
        // Put the "STAT" Command !!!
        $response = "";
        $cmd = "STAT";
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        
        $response = $this->_getnextstring ();
        
        if (! $this->_logging ( $response ))
            return FALSE;
        
        if (substr ( $response, 0, 3 ) != "+OK") {
            $this->error = "POP3 get_office_status() - Error: " . $response;
            if (! $this->_logging ( $this->error ))
                return FALSE;
            $this->_cleanup ();
            return FALSE;
        }
        // Remove "\r\n" !!!
        $response = trim ( $response );
        
        ////////////////////////////////////////////////////////////////////////
        // Some Server send the STAT string is finished by "." (+OK 3 52422.)
        // - "Yahoo Server"
        $lastdigit = substr ( $response, - 1 );
        if (! ereg ( "(0-9)", $lastdigit )) {
            $response = substr ( $response, 0, strlen ( $response ) - 1 );
        }
        unset ( $lastdigit );
        ////////////////////////////////////////////////////////////////////////
        

        $array = explode ( " ", $response );
        $output ["count_mails"] = $array [1];
        $output ["octets"] = $array [2];
        
        unset ( $array );
        $response = "";
        
        if ($output ["count_mails"] != "0") {
            
            // List Command
            $cmd = "LIST";
            if (! $this->_logging ( $cmd ))
                return FALSE;
            if (! $this->_putline ( $cmd ))
                return FALSE;
            $response = "";
            $response = $this->_getnextstring ();
            
            if (! $this->_logging ( $response ))
                return FALSE;
            
            if (substr ( $response, 0, 3 ) != "+OK") {
                $this->error = "POP3 get_office_status() - Error: " . $response;
                $this->_cleanup ();
                return FALSE;
            }
            // Get Message Number and Size !!!
            $response = "";
            for($i = 0; $i < $output ["count_mails"]; $i ++) {
                $nr = $i + 1;
                $response = trim ( $this->_getnextstring () );
                if (! $this->_logging ( $response ))
                    return FALSE;
                $array = explode ( " ", $response );
                $output [$nr] ["size"] = $array [1];
                $response = "";
                unset ( $array );
                unset ( $nr );
            }
            // $response = $this->_getnextstring();
            // echo "<b>".$response."</b>";
            

            // Check is server send "."
            if (trim ( $this->_getnextstring () ) != ".") {
                $this->error = "POP3 get_office_status() - Error: Server does not send " . " at the end !!!";
                $this->_cleanup ();
                return FALSE;
            }
            if (! $this->_logging ( "." ))
                return FALSE;
                
            // UIDL Command
            $cmd = "UIDL";
            if (! $this->_logging ( $cmd ))
                return FALSE;
            if (! $this->_putline ( $cmd ))
                return FALSE;
            $response = "";
            $response = $this->_getnextstring ();
            if (! $this->_logging ( $response ))
                return FALSE;
            if (substr ( $response, 0, 3 ) != "+OK") {
                $this->error = "POP3 get_office_status() - Error: " . $response;
                $this->_cleanup ();
                return FALSE;
            }
            // Get UID's
            $response = "";
            for($i = 0; $i < $output ["count_mails"]; $i ++) {
                $nr = $i + 1;
                $response = trim ( $this->_getnextstring () );
                if (! $this->_logging ( $response ))
                    return FALSE;
                $array = explode ( " ", $response );
                $output [$nr] ["uid"] = $array [1];
                $response = "";
                unset ( $array );
                unset ( $nr );
            }
            
            // Check is server send "."
            if (trim ( $this->_getnextstring () ) != ".") {
                $this->error = "POP3 get_office_status() - Error: Server does not send " . " at the end !!!";
                $this->_cleanup ();
                return FALSE;
            }
            if (! $this->_logging ( "." ))
                return FALSE;
        }
        
        return $output;
    
    }
    
    /*
      Function save2file($message,$filename)
      Access: Public

      return written bytes or "false"
    */
    function save2file($message, $filename) {
        $this->file_fp = fopen ( $filename, "w+" );
        if (! $this->file_fp) {
            $this->error = "POP3 save2file() - Error: Can't open file in write mode. (" . $filename . ")";
            if (! $this->_logging ( $this->error ))
                return FALSE;
            $this->_cleanup ();
            return FALSE;
        }
        if (! $this->_logging ( "LOG FILE: File " . $filename . " created." )) {
            $this->_cleanup ();
            return FALSE;
        }
        $count_bytes = "0";
        
        for($i = 0; $i < count ( $message ); $i ++) {
            $line = $message [$i];
            $str_len = strlen ( $line );
            $count_bytes = $count_bytes + $str_len;
            if (! fputs ( $this->file_fp, $line, $str_len )) {
                $this->error = "POP3 save2file() - Error: Can't write string to file (" . $filename . ") !!!";
                if (! $this->_logging ( $this->error ))
                    return FALSE;
                $this->_cleanup ();
                return FALSE;
            }
            unset ( $line );
        }
        if (! $this->_logging ( "LOG FILE: File " . $filename . " (" . $count_bytes . " Bytes) written." )) {
            $this->_cleanup ();
            return FALSE;
        }
        
        return $count_bytes;
    }
    
    /*

      Access: Public
    */
    
    function noop() {
        if (! $this->socket) {
            $this->error = "POP3 noop() - Error: No connection avalible.";
            if (! $this->_logging ( $this->error ))
                return FALSE;
            return FALSE;
        }
        if (! $this->_checkstate ( "noop" ))
            return FALSE;
        
        $cmd = "NOOP";
        
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        
        $response = "";
        $response = $this->_getnextstring ();
        if (! $this->_logging ( $response ))
            return FALSE;
        if (substr ( $response, 0, 1 ) != "+") {
            $this->error = "POP3 noop() - Error: " . $response;
            return FALSE;
        }
        
        return TRUE;
    }
    
    /*
      Function reset()
      Access: Public
    */
    function reset() {
        if (! $this->socket) {
            $this->error = "POP3 reset() - Error: No connection avalible.";
            if (! $this->_logging ( $this->error ))
                return FALSE;
            
            return FALSE;
        }
        
        if (! $this->_checkstate ( "reset" ))
            return FALSE;
        
        $cmd = "RSET";
        
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        $response = "";
        $response = $this->_getnextstring ();
        if (! $this->_logging ( $response ))
            return FALSE;
        if (substr ( $response, 0, 1 ) != "+") {
            $this->error = "POP3 reset() - Error: " . $response;
            return FALSE;
        }
        return TRUE;
    }
    /*
      Function stats
      Access: Private
      Get only count of mails and size of maildrop !!!
    */
    
    function _stats() {
        if (! $this->socket) {
            $this->error = "POP3 _stats() - Error: No connection avalible.";
            return FALSE;
        }
        
        if (! $this->_checkstate ( "stats" ))
            return FALSE;
        $cmd = "STAT";
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        
        $response = $this->_getnextstring ();
        if (substr ( $response, 0, 1 ) != "+") {
            $this->error = "POP3 _stats() - Error: " . $response;
            return FALSE;
        }
        $response = trim ( $response );
        
        $array = explode ( " ", $response );
        
        $output ["count_mails"] = $array [1];
        $output ["octets"] = $array [2];
        
        return $output;
    }
    
    /*
      Function uidl($msg_number = "0")
      Access: Public
    */
    function uidl($msg_number = "0") {
        if (! $this->socket) {
            $this->error = "POP3 uidl() - Error: No connection avalible.";
            return FALSE;
        }
        
        if (! $this->_checkstate ( "uidl" ))
            return FALSE;
        
        if ($msg_number == "0") {
            $cmd = "UIDL";
            
            // Get count of mails
            $mails = $this->_stats ();
            if (! $mails)
                return FALSE;
            
            if (! $this->_logging ( $cmd ))
                return FALSE;
            if (! $this->_putline ( $cmd ))
                return FALSE;
            
            $response = "";
            $response = $this->_getnextstring ();
            if (! $this->_logging ( $response ))
                return FALSE;
            if (substr ( $response, 0, 1 ) != "+") {
                $this->error = "POP3 uidl() - Error: " . $response;
                return FALSE;
            }
            $response = "";
            for($i = 1; $i <= $mails ["count_mails"]; $i ++) {
                $response = $this->_getnextstring ();
                if (! $this->_logging ( $response ))
                    return FALSE;
                $response = trim ( $response );
                $array = explode ( " ", $response );
                $output [$i] = $array [1];
            }
            return $output;
        } else {
            $cmd = "UIDL $msg_number";
            
            if (! $this->_logging ( $cmd ))
                return FALSE;
            if (! $this->_putline ( $cmd ))
                return FALSE;
            
            $response = "";
            $response = $this->_getnextstring ();
            if (! $this->_logging ( $response ))
                return FALSE;
            if (substr ( $response, 0, 1 ) != "+") {
                $this->error = "POP3 uidl() - Error: " . $response;
                return FALSE;
            }
            
            $response = trim ( $response );
            
            $array = explode ( " ", $response );
            
            $output [$array [1]] = $array [2];
            
            return $output;
        }
    
    }
    
    /*
      Function close()
      Access: Public

      Close POP3 Connection
    */
    
    function close() {
        
        $response = "";
        $cmd = "QUIT";
        if (! $this->_logging ( $cmd ))
            return FALSE;
        if (! $this->_putline ( $cmd ))
            return FALSE;
        
        if ($this->state == "AUTHORIZATION") {
            $this->state = "DISCONNECTED";
        } elseif ($this->state == "TRANSACTION") {
            $this->state = "UPDATE";
        }
        
        $response = $this->_getnextstring ();
        
        if (! $this->_logging ( $response ))
            return FALSE;
        if (substr ( $response, 0, 1 ) != "+") {
            $this->error = "POP3 close() - Error: " . $response;
            return FALSE;
        }
        $this->socket = FALSE;
        
        $this->_cleanup ();
        
        return TRUE;
    }
    
    /*
      Function _getnextstring()
      Access: Private
    */
    
    function _getnextstring($buffer_size = 512) {
        $buffer = "";
        $buffer = fgets ( $this->socket, $buffer_size );
        
        $this->socket_status = socket_get_status ( $this->socket );
        
        if ($this->socket_status ["timed_out"]) {
            $this->_cleanup ();
            return "POP3 _getnextstring() - Socket_Timeout_reached.";
        }
        $this->socket_status = FALSE;
        
        return $buffer;
    }
    
    /*
      Function _putline()
      Access: Private
    */
    function _putline($string) {
        $line = "";
        $line = $string . "\r\n";
        if (! fwrite ( $this->socket, $line, strlen ( $line ) )) {
            $this->error = "POP3 _putline() - Error while send \" $string \". -- Connection closed.";
            $this->_cleanup ();
            return FALSE;
        }
        return TRUE;
    }
    
    /*
      Function _parse_banner( $server_text )
      Access: Private
    */
    function _parse_banner(&$server_text) {
        $outside = true;
        $banner = "";
        $length = strlen ( $server_text );
        for($count = 0; $count < $length; $count ++) {
            $digit = substr ( $server_text, $count, 1 );
            if ($digit != "") {
                if ((! $outside) and ($digit != '<') and ($digit != '>')) {
                    $banner .= $digit;
                    continue;
                }
                if ($digit == '<') {
                    $outside = false;
                } elseif ($digit == '>') {
                    $outside = true;
                }
            }
        }
        $banner = trim ( $banner );
        if (strlen ( $banner ) != 0) {
            return "<" . $banner . ">";
        }
        return;
    }
    
    // removed save2mysql function (ts77)
}
////////////////////////////////////////////////////////end of pop3 class/////////////////////////////////////////////////////////////////

function searchpart($sections)
{
    $returnval = "";
    foreach($sections->parts as $part){
        // look for a text/plain part
        if($part->ctype_primary=="text" && $part->ctype_secondary=="plain"){

            $body = $part->body;

            if($part->headers["content-transfer-encoding"]=="quoted-printable"){
                $body=quoted_printable_decode($body);
            }
			$returnval .= $body;
        }
        if($part->ctype_primary=="multipart") {
			$returnval .= searchpart($part);
		}
    }
    return $returnval;
}

?> 