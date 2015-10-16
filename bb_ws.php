<?php
/*
 *  bb_ws-php - An example script accessing the Blackboard Learn 9 web services using PHP
 *  Copyright (C) 2013  Stephen P Vickers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Contact: stephen@spvsoftwareproducts.com
 *
 * Version history:
 *   1.0.00  10-Feb-13  Initial version
 *   1.1.00  27-Apr-13  Added members option to retrieve course memberships
 *                      Allow course and user options to list multiple IDs
*/

// Load configuration settings
  require_once('config.php');

// Load dependent library files
  require_once('lib.php');

// Print header
  print 'Learn 9 server: ' . SERVER_URL . "\n";
  print 'Proxy tool:     ' . VENDOR_ID . '/' . PROGRAM_ID . "\n\n";

  $ok = isset($argv[1]);
  $err = FALSE;

  if ($ok) {

// Initialise a Context SOAP client object
    try {
      $context_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Context.WS?wsdl');
    } catch (Exception $e) {
      $ok = FALSE;
      print "ERROR: {$e->getMessage()}\n";
    }

  }

  if ($ok) {

    $action = strtolower($argv[1]);

    if ($action == 'register') {
##
#### Register the tool
##
      print 'Registering tool... ';
      $register_tool = new stdClass();
      $register_tool->clientVendorId = VENDOR_ID;
      $register_tool->clientProgramId = PROGRAM_ID;
      $register_tool->registrationPassword = REGISTRATION_PASSWORD;
      $register_tool->description = TOOL_DESCRIPTION;
      $register_tool->initialSharedSecret = SHARED_SECRET;
      $register_tool->requiredToolMethods =   array('Context.WS:loginTool', 'Context.WS:getMemberships', 'User.WS:getUser', 'Course.WS:getCourse',
                                                    'CourseMembership.WS:getCourseMembership');
      try {
        $result = $context_client->registerTool($register_tool);
        $ok = $result->return->status;
        if ($ok) {
          print "Success (now make the proxy tool available in Learn 9)\n";
        } else {
          $err = TRUE;
          print "Failed (may already be registered)\n";
        }
      } catch (Exception $e) {
        $err = TRUE;
        print "ERROR: {$e->getMessage()}\n";
      }

    } else {

// Get a session ID
      try {
        $result = $context_client->initialize();
        $password = $result->return;
      } catch (Exception $e) {
        $err = TRUE;
        print "ERROR: {$e->getMessage()}\n";
      }

      if (!$err) {

// Log in as a tool
        $input = new stdClass();
        $input->password = SHARED_SECRET;
        $input->clientVendorId = VENDOR_ID;
        $input->clientProgramId = PROGRAM_ID;
        $input->loginExtraInfo = '';  // not used but must not be NULL
        $input->expectedLifeSeconds = 3600;
        try {
          $result = $context_client->loginTool($input);
        } catch (Exception $e) {
          $err = TRUE;
          print "ERROR: {$e->getMessage()}\n";
        }
      }

      if (!$err) {

        if ($action == 'courses') {
##
#### Get courses for a user
##
          $ok = isset($argv[2]);
          if ($ok) {
            print 'Retrieving courses...';

            $member = new stdClass();
            $member->userid = $argv[2];
            try {
              $result = $context_client->getMemberships($member);
              for ($i = 0; $i < count($result->return); $i++) {
                print " {$result->return[$i]->externalId}";
              }
              print "\n";
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }

          }

        } else if ($action == 'course') {
##
#### Get course details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a Course SOAP client object
            try {
              $course_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Course.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving course(s)... ';
            $ids = array();
            for ($i = 2; $i < $argc; $i++) {
              $ids[] = $argv[$i];
            }
            $course = new stdClass();
            $course->filter = new stdClass();
            if (substr($argv[2], 0, 1) == '_') {
              $course->filter->ids = $ids;
              $course->filter->filterType = 3;
            } else {
              $course->filter->courseIds = $ids;
              $course->filter->filterType = 1;
            }
            try {
              $results = $course_client->getCourse($course);
              $ok = $results->return;
              if ($ok) {
                print "\n";
                $courses = $results->return;
                if (!is_array($courses)) {
                  $courses = array();
                  $courses[] = $results->return;
                }
                for ($i = 0; $i < count($courses); $i++) {
                  $result = $courses[$i];
                  if ($course->filter->filterType == 3) {
                    print "  {$result->id}: {$result->name} ({$result->courseId})\n";
                  } else {
                    print "  {$result->courseId}: {$result->name} ({$result->id})\n";
                  }
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

        } else if ($action == 'user') {
##
#### Get user details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a User SOAP client object
            try {
              $user_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/User.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving user(s)... ';
            $ids = array();
            for ($i = 2; $i < $argc; $i++) {
              $ids[] = $argv[$i];
            }
            $user = new stdClass();
            $user->filter = new stdClass();
            if (substr($argv[2], 0, 1) == '_') {
              $user->filter->id = $ids;
              $user->filter->filterType = 2;
            } else {
              $user->filter->name = $ids;
              $user->filter->filterType = 6;
            }
            try {
              $results = $user_client->getUser($user);
              $ok = $results->return;
              if ($ok) {
                $users = $results->return;
                print "\n";
                if (!is_array($users)) {
                  $users = array();
                  $users[] = $results->return;
                }
                for ($i = 0; $i < count($users); $i++) {
                  $result = $users[$i];
                  if ($user->filter->filterType == 2) {
                    print "  {$result->id}: {$result->extendedInfo->givenName} {$result->extendedInfo->familyName} ({$result->name})\n";
                  } else {
                    print "  {$result->name}: {$result->extendedInfo->givenName} {$result->extendedInfo->familyName} ({$result->id})\n";
                  }
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

        } else if ($action == 'member') {
##
#### Get course membership details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a User SOAP client object
            try {
              $membership_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/CourseMembership.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving membership(s)... ';
            $course = new stdClass();
            $course->courseId = $argv[2];
            $ids = array();
            if ($argc >= 4) {
              for ($i = 3; $i < $argc; $i++) {
                $ids[] = $argv[$i];
              }
            } else {
              $ids[] = '';
            }
            $member = new stdClass();
            $member->courseId = $argv[2];
            $member->f = new stdClass();
            $member->f->userIds = $ids;
            $member->f->filterType = 6;
            try {
              $results = $membership_client->getCourseMembership($member);
              $ok = $results->return;
              if ($ok) {
                $memberships = $results->return;
                print "\n";
                if (!is_array($memberships)) {
                  $memberships = array();
                  $memberships[] = $results->return;
                }
                for ($i = 0; $i < count($memberships); $i++) {
                  $result = $memberships[$i];
		  		  print "  {$result->id}: userId {$result->userId}, roleId {$result->roleId}, enrolled " . date('d-M-y H:i:s', $result->enrollmentDate) . ' (';
                  if (!$result->available) {
                    print 'not ';
                  }
                  print "available)\n";
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

        } else if ($action == 'grades') {
##
#### Get course details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a gradebook SOAP client object
            try {
              $user_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/User.WS?wsdl');
              $gradebook_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Gradebook.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving userId from username... ';
            $name = $argv[2];
            
            $user = new stdClass();
            
            $user->filter = new stdClass();
            
            $user->filter->name = $name;
            $user->filter->filterType = 6;
            $user->filter->available = true;
            
            try {
              $results = $user_client->getUser($user);
              $ok = $results->return;
              if ($ok) {
                print "\n";
                $users = $results->return;
                if (!is_array($users)) {
                  $users = array();
                  $users[] = $results->return;
                }
                
                for ($i = 0; $i < count($users); $i++) {
                  $result = $users[$i];
                  print "  {$result->id}: {$result->name}\n";
                  $userId = $users[$i]->id;
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }
          
          if ($ok) {
            print 'Retrieving grade from userId and ColumnId... ';
            $columnId = $argv[3];
            
            $gradebook = new stdClass();
            
            $gradebook->filter = new stdClass();
            
            $gradebook->filter->columnId = $columnId;
            $gradebook->filter->userId = $userId;
            $gradebook->filter->filterType = 2;
            
            try {
              $grades[] = array();
			  $grade_results = $gradebook_client->getGrades($gradebook);
              $ok = $grade_results->return;
              if ($ok) {
                print "\n";
                $grades = $grade_results->return;
                if (!is_array($grades)) {
                  $grades = array();
                  $grades[] = $results->return;
                }
                for ($i = 0; $i < count($grades); $i++) {
                  $grade_result = $grades[$i];
                  //print "  {$result->id}: {$result->name}\n";
                  var_dump($grades);
                }
              } else {
                $err = TRUE;
                var_dump($grade_results);
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }
		} else {

          $ok = FALSE;

        }

      }

    }

  }

  if (!$ok && !$err) {
##
#### Display usage information
##
    print "Usage:\n";
    print "  {$argv[0]} register                    -- register the proxy tool\n";
    print "  {$argv[0]} courses {username}          -- get course list for a user\n";
    print "  {$argv[0]} course {course}+            -- get course details (id/courseId)\n";
    print "  {$argv[0]} user {user}+                -- get user details (id/username)\n";
    print "  {$argv[0]} member {courseId} {userId}* -- get course membership details\n";
    print "  {$argv[0]} grades {username} {columnId}-- get grades by columnId and UserId\n";

  }

?>
