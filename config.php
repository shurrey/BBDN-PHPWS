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
*/

  define('SERVER_URL', 'https://b2showcase.blackboard.com');   // URL to Learn 9 server (without a closing "/")
  define('REGISTRATION_PASSWORD', 'tool-register');  // Proxy Tool registration password for Learn 9 server

  define('VENDOR_ID', 'bbdn');
  define('PROGRAM_ID', 'sample-client');
  define('TOOL_DESCRIPTION', 'A proxy tool to demonstrate accessing Learn 9 web services using PHP');
  define('SHARED_SECRET', 'd0gstar2');

?>
