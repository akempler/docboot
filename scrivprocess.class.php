<?php
/**
 * @file scrivprocess.class.php
 * Handle parsing and processing for exported files.
 * Convert the files to Twitter Bootstrap based site.
 *
 * @author akempler
 *
 */

class ScrivProcess {


  /**
   * The name of the original uploaded file.
   * @var string
   */
  protected $filename = '';

  /**
   * The path to the directory where converted files will be placed.
   * For example ./converted/
   * @var string
   * @access protected
   */
  protected $converted_path  = './converted/';

  /**
   * The path to the converted file.
   * The file's body has been isolated and the templates have been applied before and after the body.
   * @var string
   * @access protected
   */
  protected $converted_filepath			= '';

  /**
   * The path to the html template files.
   * These are prepended and appended to the <body> and </body> tags.
   * @var string
   * @access protected
   */
  protected $template_path	= './templates/';

  /**
   * The path to the file passed to this class. TODO change to source_filepath.
   * @var string
   * @access protected
   */
  protected $original_filepath = '';

  /**
   * The title to use for the converted document.
   * This is parsed out of the document.
   * @var string
   * @access protected
   */
  protected $doc_title = '';

  /**
   * The author to use for the converted document.
   * This is parsed out of the document if available.
   * @var string
   * @access protected
   */
  protected $doc_author = '';

  /**
   * The SimpleXML represenation of the file.
   * @var SimpleXML Object
   * @access protected
   */
  protected $sxml = NULL;

  /**
   * A DOMDocument created via loadHtml() based on the file.
   * @var DOMDocument
   * @access protected
   */
  protected $doc = NULL;

  /**
   * The type of file being parsed.
   * Allowable types are: 'word', 'scrivener'
   * @var string
   * @access protected
   */
  protected $exporttype = NULL;






  /**
   * Constructor
   * @param string $type - 'word' or 'scrivener'
   * @todo handle no type passed?
   *     Could look for it in the head:
   *     <meta name=Generator content="Microsoft Word 12 (filtered)">
   */
  function __construct($type) {
  	$this->exporttype = $type;
  }





  /**
   * Parse and convert a file to a Twitter Bootstrap based html document.
   *
   * @access public
   * @return string - The name of the file if successful. Otherwise NULL.
   */
  public function convert_file($filepath) {

  	$this->original_filepath = $filepath;
  	if (isset($_SERVER['WINDIR']) || isset($_SERVER['windir'])) {
  	  $pieces = explode('\\', $filepath);
  	} else {
  	  $pieces = explode('/', $filepath);
  	}
  	$this->filename = array_pop($pieces);
  	//print" filename=".$this->filename." ";

  	$this->import_file();

  	$this->parse_doc_title();

	$this->strip_file();

	// TODO handle failure
	// Wrap the scrivener export in the tempate
	$this->add_template();


  	 return $this->converted_filepath;
  }


  /**
   * Set the path to the directory that will contain the converted files.
   * @param string $path
   * @access public
   */
  public function set_converted_path($path) {
    if(!is_dir($path)) {
      if (PHP_SAPI === 'cli') {
        print"The target directory does not exist: ".$path."\n\n";
        die();
      }
    }
    $this->converted_path = $path;
  }


  /**
   * Get the path to the converted file.
   * This is the path to the fully converted Twiiter Boostrap-ized file.
   * @return string
   * @access public
   */
  public function get_converted_filepath() {
  	return $this->converted_filepath;
  }


  /**
   * Creates simplexml and domDocument objects based on the provided file.
   * Also does some preliminary cleaning of the file,
   * for example, it converts Windows carriage returns to /n to avoid a bunch of &#13; entities being generated.
   * @access protected
   */
  protected function import_file() {

  	// Read in the file
	if(file_exists($this->original_filepath)) {
      $html = file_get_contents($this->original_filepath);
      if (!$html) {
        ScrivMsg::set_message('error', "There was an error processing the file. The errors reported were:");
        foreach(libxml_get_errors() as $error) {
          ScrivMsg::set_message('error', $error->message);
        }
        return FALSE;
      }
    } else {
      if (PHP_SAPI === 'cli') {
        print"File not found at: ".$this->original_filepath."\n\n";
        die();
      }
      ScrivMsg::set_message('error', "Sorry. There was an error processing the file. Please try again.");
      return FALSE;
    }

    // TODO poor placement of this
    $html = $this->raw_cleanup($html);


  	$html_dom = new DOMDocument();
  	$dom->preserveWhiteSpace = false;
  	$html_dom->loadHTML($html);
  	$this->doc = $html_dom;

  	$xml = $html_dom->saveXML();
	//$doc = new DOMDocument();
	//@$doc->loadXML($xml, LIBXML_NOXMLDECL);
	//$this->sxml = simplexml_import_dom($doc);
	$this->sxml = simplexml_load_string($xml);

	if (!$this->sxml) {
      ScrivMsg::set_message('error', "There was an error processing the file. The errors reported were:");
      foreach(libxml_get_errors() as $error) {
        ScrivMsg::set_message('error', $error->message);
      }
      return FALSE;
    } else {
 		return TRUE;
    }
  }


  /**
   * Some initial cleanup of the raw html document when first imported.
   * @param string - raw html.
   */
  protected function raw_cleanup($html) {
    // Get rid of Windows style carriage returns.
    //    domdocument will convert them to &#13;
    $html = preg_replace('/\r\n/', "\n", $html);
    return $html;
  }



  /**
   * Parse out the document title.
   * For Scrivener html -> markdown export this is in the <title> element inside of the <head>
   *
   * @access protected
   * @todo separate out the author parsing.
   */
  protected function parse_doc_title() {

	$this->doc_title = $this->sxml->head->title;
	// TODO this assumes that the meta tag containing the author is the second tag which is not the case for Word.
	// Should look for the meta tag that has an attribute of 'name' with a value of 'author'
	// <meta name="author" content="Adam Kempler"/>
	// NOTE: this is not set for Word. Possibly provide a field.
	// TODO check if it is set.
	//		also need to check that that is the author! Might be set but not author.
	$this->doc_author = $this->sxml->head->meta[1]['content'];
  }



  /**
   * Load the file and resave with only the content inside the body tags.
   * @return boolean - true if the file was converted successfully, otherwise false.
   * @access protected
   * @todo possibly save to variable instead of file.
   */
  protected function strip_file() {
    // TODO this shouldn't happen here.
    $this->converted_filepath = $this->converted_path . $this->filename;

	if($this->exporttype == 'word') {
		$this->clean_word();
	}

	$this->sxml = simplexml_import_dom($this->doc);

    // Save just the content in the body. We'll add our own html head.
    $this->sxml->body->asXML($this->converted_filepath);

    $this->remove_elements();

	return TRUE;

  }


  /**
   * Remove the body tag and possibly other elements.
   *
   * @access protected
   * @todo this will fail on a body with attributes since it just looks for <body>
   *     For example: <body lang="EN-US" link="blue" vlink="purple">
   *     use a regex instead.
   */
  protected function remove_elements() {

    $source = $this->converted_filepath;

    $data = file_get_contents($source);
    $data = str_replace("<body>", "", $data);
    $data = str_replace("</body>", "", $data);
    file_put_contents($source, $data);
  }




  /**
   * Cleanup a Word file
   *
   * @access protected
   * @todo move the individual items to separate methods.
   *     That way someone could override the clean_word method and change what cleaning is done easier.
   */
  protected function clean_word() {

    $sxml = simplexml_import_dom($this->doc);

  	// Remove the table of contents
  	$nodes = $sxml->xpath('//p[@class="MsoToc1"]');
	$nodes = array_merge($nodes, $sxml->xpath('//p[@class="MsoToc2"]'));
	$nodes = array_merge($nodes, $sxml->xpath('//p[@class="MsoToc3"]'));
	$nodes = array_merge($nodes, $sxml->xpath('//p[@class="MsoTocHeading"]'));

	// NOTE, doing this updates the sxml object. Nice.
	foreach($nodes as $toc) {
		$dom = dom_import_simplexml($toc);
		if(!$dom) {
		  // TODO set appropriate msg
			echo"Error converting xml";
		} else {
			$dom->parentNode->removeChild($dom);
		}
	}

    // Add ids to the h1 tags so they can serve as navigation.
    // This is done by creating a new h1 element and replacing the existing one.
    // That way we remove all the sub elements like spans, etc that Word adds. For example:
    //    <h1><a name="_Toc320541121"><span style="font-variant:normal !important;&#13;&#10;text-transform:uppercase">Overview of Steps</span></a></h1>
    // We populate the h1 with the text value of the original h1.
    $h1s = $this->doc->getElementsByTagName('h1');
    $i = $h1s->length;
    while ($i >= 0) {

      $h1 = $h1s->item($i);
      if($h1) {
        //$newh1 = $html_dom->createTextNode($h1->nodeValue);
        $newh1 = $this->doc->createElement('h1', $h1->nodeValue);
        // manually create ids for the h1 elements since Word does not provide them.
        $h1Attribute = $this->doc->createAttribute('id');
        $h1Attribute->value = 'h1_'.$i;
        $newh1->appendChild($h1Attribute);

        $h1->parentNode->replaceChild($newh1, $h1);
      }

      $i--;
    }


    // TODO don't do this here. Just use a preg_replace instead of the str_replace currently used on <body> and create a regex.
	$bodies = $this->doc->getElementsByTagName("body");
	foreach($bodies as $body) {
		//foreach($body->attributes as $att) {
			//$body->removeAttributeNode($att);
			$body->removeAttribute('lang');
			$body->removeAttribute('link');
			$body->removeAttribute('vlink');
		//}
	}

  } // END clean_word()



  /**
   * Wrap the exported scrivener file in the template.
   * @param string $template
   * @access protected
   */
  protected function add_template($template='template1') {

  	$html = file_get_contents($this->template_path.$template.'_top.html');
  	$html = str_replace("%title%", $this->doc_title, $html);
  	$html = str_replace("%author%", '<p>'.$this->doc_author.'</p>', $html);
  	$html .= file_get_contents($this->converted_filepath);
  	$html .= file_get_contents($this->template_path.$template.'_bottom.html');

  	file_put_contents($this->converted_filepath, $html);
  }





} // END class

