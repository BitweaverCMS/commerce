<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+

if (!$gBitUser->isRegistered() ) {
	$_SESSION['navigation']->set_snapshot();
	zen_redirect(FILENAME_LOGIN);
}

require_once(DIR_FS_MODULES . 'require_languages.php');
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'address_import_lib.php' );
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

global $gBitThemes;
$gBitThemes->loadCss( BITCOMMERCE_PKG_PATH.'css/address_book.css', FALSE, 300, FALSE, FALSE, FALSE );

$redirectPage = BitBase::getParameter( $_REQUEST, 'return_page', FILENAME_ADDRESS_BOOK );
$feedback = array( 'success' => array(), 'error' => array(), 'warning' => array() );
$addressMapping = NULL;
$importProblems = array();

// error checking when updating or adding an entry
if( isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update')) || isset( $_POST['save_address'] ) ) {
	if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
		$messageStack->add_session('addressbook', tra( 'Your address book has been updated.' ), 'success');
		zen_redirect(zen_href_link( $redirectPage, '', 'SSL'));
	} else {
		if( BitBase::verifyIdParameter( $_REQUEST, 'address_book_id' ) ) {
			$_REQUEST['address_store']['address_book_id'] =  $_REQUEST['address_book_id'];
		}
		$gBitSmarty->assign( 'editAddress', $_REQUEST['address_store'] );
		$gBitSmarty->assign( 'addressErrors', $gBitCustomer->mErrors );
	}
} elseif (isset($_REQUEST['delete']) && is_numeric( $_REQUEST['delete'] ) ) {
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitCustomer->expungeAddress( $_REQUEST['delete'] );
		$messageStack->add_session('addressbook', tra( 'The address was removed from your address book.' ), 'success');
		zen_redirect( zen_href_link( $redirectPage, '', 'SSL' ) );
	} else {
		$formHash['main_page'] = 'address_book';
		$formHash['delete'] = $_REQUEST['delete'];
		if( !empty( $_REQUEST['return_page'] ) ) {
			$formHash['return_page'] = $_REQUEST['return_page'];
		}
		$msgHash = array(
			'label' => 'Delete Address',
			'confirm_item' => tra( 'Are you sure you would like to delete the selected address from your address book?' ),
			'warning' => tra('This cannot be undone!'),
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	}
} elseif (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
	if( !($editAddress = $gBitCustomer->getAddress( $_GET['edit'] )) ) {
		$messageStack->add_session('addressbook', tra( 'This address does not exist' ) );
		zen_redirect(zen_href_link( $redirectPage, '', 'SSL' ) );
	}
	$gBitSmarty->assign( 'editAddress', $editAddress );
} elseif( BitBase::getParameter( $_POST, 'import_upload' ) ) {
	// Import a CSV / XLSX / vCard: a vCard goes straight in, a sheet goes through the column mapping when its headings are not recognized
	$uploadFile = BitBase::getParameter( $_FILES, 'address_file', array() );
	$uploadName = (string)BitBase::getParameter( $uploadFile, 'name', '' );
	$uploadTmp = (string)BitBase::getParameter( $uploadFile, 'tmp_name', '' );
	$uploadExt = strtolower( pathinfo( $uploadName, PATHINFO_EXTENSION ) );
	if( empty( $uploadTmp ) || !empty( $uploadFile['error'] ) ) {
		$feedback['error'][] = tra( 'Choose an address file (.csv, .xlsx, or .vcf) to import.' );
	} elseif( in_array( $uploadExt, array( 'vcf', 'vcard' ) ) || bc_address_is_vcard( $uploadTmp ) ) {
		$parseErrors = array();
		$fileRows = bc_address_parse_vcard( $uploadTmp, $parseErrors );
		$feedback['error'] = array_merge( $feedback['error'], $parseErrors['error'] );
		$feedback['warning'] = array_merge( $feedback['warning'], $parseErrors['warning'] );
		if( $fileRows ) {
			$importProblems = bc_address_book_import( $fileRows, $feedback );
		}
	} else {
		$readErrors = array();
		if( $table = bc_address_read_table( $uploadTmp, $uploadName, $readErrors ) ) {
			$import = bc_address_import_store( $table, 'address_book' );
			$feedback['warning'] = array_merge( $feedback['warning'], $readErrors['warning'] );
			if( bc_address_mapping_complete( $import['mapping'] ) && empty( $_POST['import_check_columns'] ) ) {
				$addressMapping = bc_address_book_apply_import( $import, $feedback, $importProblems );
			} else {
				$addressMapping = bc_address_mapping_view( $table, $import['sheet'], $import['mapping'] );
				if( !bc_address_mapping_complete( $import['mapping'] ) ) {
					$feedback['warning'][] = tra( 'The column headings in' ).' '.htmlspecialchars( $uploadName ).' '.tra( 'were not recognized. Match each field to a column below.' );
				}
			}
		} else {
			$feedback['error'] = array_merge( $feedback['error'], $readErrors['error'] );
			$feedback['warning'] = array_merge( $feedback['warning'], $readErrors['warning'] );
		}
	}
} elseif( BitBase::getParameter( $_POST, 'import_map' ) ) {
	// The mapping form: a sheet change re-guesses, Import re-reads the stored upload with the posted mapping
	if( !($import = bc_address_import_get( 'address_book' )) ) {
		$feedback['error'][] = tra( 'The uploaded file is no longer available. Upload it again.' );
	} elseif( BitBase::getParameter( $_POST, 'import_map' ) === 'cancel' ) {
		bc_address_import_clear();
	} else {
		$sheetIndex = (int)BitBase::getParameter( $_POST, 'map_sheet', $import['sheet'] );
		if( !isset( $import['table']['sheets'][$sheetIndex] ) ) {
			$sheetIndex = $import['sheet'];
		}
		if( $sheetIndex !== (int)$import['sheet'] || BitBase::getParameter( $_POST, 'import_map' ) === 'sheet' ) {
			$mapping = bc_address_guess_mapping( $import['table']['sheets'][$sheetIndex]['headers'] );
			bc_address_import_update( array( 'sheet' => $sheetIndex, 'mapping' => $mapping ) );
			$addressMapping = bc_address_mapping_view( $import['table'], $sheetIndex, $mapping );
		} else {
			$mapping = bc_address_mapping_from_post( BitBase::getParameter( $_POST, 'map', array() ), $import['table']['sheets'][$sheetIndex]['headers'], $import['mapping'] );
			bc_address_import_update( array( 'sheet' => $sheetIndex, 'mapping' => $mapping ) );
			$import = bc_address_import_get( 'address_book' );
			if( !bc_address_mapping_complete( $mapping ) ) {
				$feedback['error'][] = tra( 'Pick the column that holds the name (or first and last name) and the one that holds the street.' );
				$addressMapping = bc_address_mapping_view( $import['table'], $sheetIndex, $mapping );
			} else {
				$addressMapping = bc_address_book_apply_import( $import, $feedback, $importProblems );
			}
		}
	}
}

if( $addresses = CommerceCustomer::getAddressesFromId( $_SESSION['customer_id'] ) ) {
	$gBitSmarty->assign( 'addresses', $addresses );
}

$gBitSmarty->assign( 'formTitle', (isset( $_GET['edit'] ) ? tra( 'Update address' ) : tra( 'New address' )) );
$gBitSmarty->assign( 'defaultAddressId', $gBitCustomer->getDefaultAddressId() );
$gBitSmarty->assign( 'storeCountryIso2', bc_address_store_country_iso2() );
$gBitSmarty->assignByRef( 'feedback', $feedback );
$gBitSmarty->assign( 'addressMapping', $addressMapping );
$gBitSmarty->assign( 'importProblems', $importProblems );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_address_book.tpl' );

// Save parsed rows to the book and report the counts; returns the list of rows that could not be saved
function bc_address_book_import( $pRows, &$pFeedback ) {
	$counts = bc_address_save_rows_to_book( $pRows );
	$message = $counts['saved'].' '.tra( 'addresses added to your address book.' );
	if( $counts['duplicate'] ) {
		$message .= ' '.$counts['duplicate'].' '.tra( 'already there were skipped.' );
	}
	if( $counts['saved'] || $counts['duplicate'] ) {
		$pFeedback['success'][] = $message;
	}
	if( $counts['invalid'] || $counts['failed'] ) {
		$pFeedback['warning'][] = ( $counts['invalid'] + $counts['failed'] ).' '.tra( 'addresses could not be saved. They are listed below; fix them in the file and import it again, or type them in.' );
	}
	return $counts['problems'];
}

// Rows from the stored upload with its current mapping. Returns NULL when imported, else the mapping view to show again.
function bc_address_book_apply_import( $pImport, &$pFeedback, &$pProblems ) {
	$table = $pImport['table'];
	$sheet = $table['sheets'][$pImport['sheet']];
	$rowErrors = array();
	$fileRows = bc_address_rows_from_table( $sheet, $pImport['mapping'], $table['name'], count( $table['sheets'] ), $rowErrors );
	$pFeedback['error'] = array_merge( $pFeedback['error'], $rowErrors['error'] );
	$pFeedback['warning'] = array_merge( $pFeedback['warning'], $rowErrors['warning'] );
	if( empty( $fileRows ) ) {
		return bc_address_mapping_view( $table, $pImport['sheet'], $pImport['mapping'] );
	}
	if( $ignored = bc_address_mapping_ignored( $pImport['mapping'], $sheet['headers'] ) ) {
		$pFeedback['warning'][] = tra( 'Ignored columns:' ).' '.implode( ', ', array_map( 'htmlspecialchars', $ignored ) );
	}
	$pProblems = bc_address_book_import( $fileRows, $pFeedback );
	bc_address_import_clear();
	return NULL;
}
