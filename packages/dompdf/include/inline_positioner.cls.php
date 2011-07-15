<?php
/**
 * DOMPDF - PHP5 HTML to PDF renderer
 *
 * File: $RCSfile: inline_positioner.cls.php,v $
 * Created on: 2004-06-08
 *
 * Copyright (c) 2004 - Benj Carson <benjcarson@digitaljunkies.ca>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * The latest version of DOMPDF might be available at:
 * http://www.dompdf.com/
 *
 * @link http://www.dompdf.com/
 * @copyright 2004 Benj Carson
 * @author Benj Carson <benjcarson@digitaljunkies.ca>
 * @package dompdf

 */

/* $Id: inline_positioner.cls.php 357 2011-01-30 20:56:46Z fabien.menager $ */
/**
 * Positions inline frames
 *
 * @access private
 * @package dompdf
 */
class Inline_Positioner extends Positioner {

  function __construct(Frame_Decorator $frame) { parent::__construct($frame); }

  //........................................................................

  function position() {
    // Find our nearest block level parent and access its lines property.
    $p = $this->_frame->find_block_parent();

    // Debugging code:

//     pre_r("\nPositioning:");
//     pre_r("Me: " . $this->_frame->get_node()->nodeName . " (" . spl_object_hash($this->_frame->get_node()) . ")");
//     pre_r("Parent: " . $p->get_node()->nodeName . " (" . spl_object_hash($p->get_node()) . ")");

    // End debugging

    if ( !$p )
      throw new DOMPDF_Exception("No block-level parent found.  Not good.");

    $f = $this->_frame;
    
    $cb = $f->get_containing_block();
    $style = $f->get_style();
    $line = $p->get_current_line();

    // Skip the page break if in a fixed position element
    $is_fixed = false;
    while($f = $f->get_parent()) {
      if($f->get_style()->position === "fixed") {
        $is_fixed = true;
        break;
      }
    }

    $f = $this->_frame;

    if ( !$is_fixed && $f->get_parent() &&
         $f->get_parent() instanceof Inline_Frame_Decorator &&
         $f->get_node()->nodeName === "#text" ) {
      
      $min_max = $f->get_reflower()->get_min_max_width();
      $initialcb = $f->get_root()->get_containing_block();
      $height = $style->length_in_pt($style->height, $initialcb["h"]);
      
      // If the frame doesn't fit in the current line, a line break occurs
      if ( $min_max["min"] > ($cb["w"]-$line["left"]-$line["w"]-$line["right"]) ) {
        $p->add_line();
      }
    }

    $this->_frame->set_position($cb["x"] + $line["w"], $line["y"]);

  }
}
