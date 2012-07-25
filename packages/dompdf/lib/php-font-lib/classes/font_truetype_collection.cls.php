<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien M�nager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: font_truetype_collection.cls.php 34 2011-10-23 13:53:25Z fabien.menager $
 */

require_once dirname(__FILE__)."/font_binary_stream.cls.php";
require_once dirname(__FILE__)."/font_truetype.cls.php";

/**
 * TrueType collection font file.
 * 
 * @package php-font-lib
 */
class Font_TrueType_Collection extends Font_Binary_Stream implements Iterator, Countable {
  /**
   * Current iterator position.
   * 
   * @var integer
   */
  private $position = 0;
  
  protected $collectionOffsets = array();
  protected $collection = array();
  protected $version;
  protected $numFonts;
  
  function parse(){
    if (isset($this->numFonts)) {
      return;
    }
    
    $tag = $this->read(4);
    
    $this->version = $this->readFixed();
    $this->numFonts = $this->readUInt32();
    
    for($i = 0; $i < $this->numFonts; $i++) {
      $this->collectionOffsets[] = $this->readUInt32();
    }
  }
  
  /**
   * @param int $fontId
   * @return Font_TrueType
   */
  function getFont($fontId) {
    $this->parse();
    
    if (!isset($this->collectionOffsets[$fontId])) {
      throw new OutOfBoundsException();
    }
    
    if (isset($this->collection[$fontId])) {
      return $this->collection[$fontId];
    }
    
    $font = new Font_TrueType();
    $font->f = $this->f;
    $font->setTableOffset($this->collectionOffsets[$fontId]);
    
    return $this->collection[$fontId] = $font;
  }
  
  function current() {
    return $this->getFont($this->position);
  }
  
  function key() {
    return $this->position;
  }
  
  function next() {
    return ++$this->position;
  }
  
  function rewind() {
    $this->position = 0;
  }
  
  function valid() {
    $this->parse();
    return isset($this->collectionOffsets[$this->position]);
  }
  
  function count() {
    $this->parse();
    return $this->numFonts;
  }
}
