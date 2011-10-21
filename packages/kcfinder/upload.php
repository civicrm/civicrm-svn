<?php

/** This file is part of KCFinder project
  *
  *      @desc Upload calling script
  *   @package KCFinder
  *   @version 2.5
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

require "core/autoload.php";
$uploader = new uploader();
$uploader->upload();

?>