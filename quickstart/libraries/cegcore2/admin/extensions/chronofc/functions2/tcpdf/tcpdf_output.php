<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$config = \G2\L\Obj::getInstance($function['name'], $function);
	
	$output = $config->get('content', '');
	
	$output = $this->Parser->parse($output, true);
	//begin tcpdf code
	if(file_exists(dirname(__FILE__).DS.'tcpdf/tcpdf.php')){
		require_once('tcpdf/config/lang/eng.php');
		require_once('tcpdf/tcpdf.php');
	}else if(file_exists(\G2\Globals::get('ROOT_PATH').'libraries'.DS.'cetcpdf'.DS.'tcpdf'.DS.'tcpdf.php')){
		require_once(\G2\Globals::get('ROOT_PATH').'libraries'.DS.'cetcpdf'.DS.'tcpdf'.DS.'config/lang/eng.php');
		require_once(\G2\Globals::get('ROOT_PATH').'libraries'.DS.'cetcpdf'.DS.'tcpdf'.DS.'tcpdf.php');
	}else{
		echo 'TCPDF lib not found';
		return;
	}
	
	// create new PDF document
	$pdf = new \TCPDF($config->get('pdf_page_orientation', 'P'), PDF_UNIT, $config->get('pdf_page_format', 'A4'), true, 'UTF-8', false);
	
	//set protection if enabled
	if((bool)$config->get('enable_protection', 0) === true){
		$owner_pass = ($config->get('owner_pass', "") ? $config->get('owner_pass', "") : null);
		$perms = (count($config->get('permissions', "")) > 0) ? $config->get('permissions', "") : array();
		$pdf->SetProtection($perms, $config->get('user_pass', ""), $owner_pass, $config->get('sec_mode', ""), $pubkeys=null);
	}

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor($config->get('pdf_author', 'PDF Author.'));
	
	if($config->get('pdf_title')){
		$pdf->SetTitle($config->get('pdf_title'));
	}
	
	$pdf->SetSubject($config->get('pdf_subject', 'Powered by Chronoforms + TCPDF'));
	$pdf->SetKeywords($config->get('pdf_keywords', 'Chronoforms, PDF Plugin, TCPDF, PDF'));
	// set default header data'
	if(strlen($config->get('pdf_title')) OR strlen($config->get('pdf_header'))){
		$pdf->SetHeaderData(false, 0, $this->Parser->parse($config->get('pdf_title', ''), true), $this->Parser->parse($config->get('pdf_header', ''), true));
	}

	// set header and footer fonts
	$pdf->setHeaderFont(Array($config->get('pdf_header_font', 'helvetica'), '', (int)$config->get('pdf_header_font_size', 10)));
	$pdf->setFooterFont(Array($config->get('pdf_footer_font', 'helvetica'), '', (int)$config->get('pdf_footer_font_size', 8)));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont($config->get('pdf_monospaced_font', 'courier'));

	//set margins
	$pdf->SetMargins($config->get('pdf_margin_left', 15), $config->get('pdf_margin_top', 27), $config->get('pdf_margin_right', 15));
	$pdf->SetHeaderMargin($config->get('pdf_margin_header', 5));
	$pdf->SetFooterMargin($config->get('pdf_margin_footer', 10));

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, $config->get('pdf_margin_bottom', 25));

	//set image scale factor
	$pdf->setImageScale($config->get('pdf_image_scale_ratio', 1.25));

	//set some language-dependent strings
	//$pdf->setLanguageArray($l);

	// ---------------------------------------------------------

	// set font
	$pdf->SetFont($config->get('pdf_body_font', 'courier'), '', (int)$config->get('pdf_body_font_size', 14));

	// add a page
	$pdf->AddPage();
	// output the HTML content
	$pdf->writeHTML($output, true, false, true, false, '');
	// reset pointer to the last page
	$pdf->lastPage();
	//Close and output PDF document
	$PDF_file_name = $this->Parser->parse($config->get('file_name', 'empty_name.pdf'), true);
	
	$PDF_view = $config->get('pdf_view', 'I');
	if(($PDF_view == 'F') || ($PDF_view == 'FI') || ($PDF_view == 'FD')){
		$PDF_file_path = $this->Parser->parse($config->get('file_path'), true);
		
		$pdf->Output($PDF_file_path, $PDF_view);
		
		$this->set($function['name'], ['path' => $PDF_file_path]);
		
		$this->Parser->debug[$function['name']] = ['path' => $PDF_file_path];
	}else{
		$pdf->Output($PDF_file_name, $PDF_view);
	}
	if($PDF_view != 'F'){
		$this->Parser->end();
		
		@flush();
		@ob_flush();
		exit;
	}