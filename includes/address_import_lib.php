<?php
/**
 * Address import: CSV / XLSX / vCard reading, column mapping, row validation, and address-book storage.
 *
 * Shared by the drop ship page (pages/dropship) and the address book page (pages/address_book) so neither
 * loads the other. A row is a hash of the delivery_* keys used by the drop ship file (delivery_name,
 * delivery_company, delivery_street_address1, delivery_street_address2, delivery_city, delivery_state,
 * delivery_postcode, delivery_country_iso2, delivery_telephone, comments). Meta keys start with '_'
 * (_line, _line_label, _file, _errors, _book, _save, _saved) and are stripped before a row is used as
 * an order.
 *
 * A spreadsheet goes through two steps: bc_address_read_table() turns the file into sheets of raw cells,
 * bc_address_guess_mapping() matches the header row to the fields we need, and bc_address_rows_from_table()
 * builds validated rows from a mapping. When the guess is incomplete the page shows the mapping table
 * (bc_address_mapping_view()) and the sheets wait in the session (bc_address_import_store()).
 *
 * @package bitcommerce
 */

// Delivery fields a shopper may type or edit, in display order
function bc_address_fields() {
	return array( 'delivery_name', 'delivery_company', 'delivery_street_address1', 'delivery_street_address2', 'delivery_city', 'delivery_state', 'delivery_postcode', 'delivery_country_iso2', 'delivery_telephone', 'comments' );
}

// Columns a spreadsheet can supply, in the order the mapping table lists them. 'need' marks the two the import cannot do without.
function bc_address_import_fields() {
	return array(
		'delivery_name' => array( 'label' => tra( 'Name' ), 'help' => tra( 'or First and Last below' ), 'need' => TRUE ),
		'_first_name' => array( 'label' => tra( 'First name' ), 'help' => '', 'need' => FALSE ),
		'_last_name' => array( 'label' => tra( 'Last name' ), 'help' => '', 'need' => FALSE ),
		'delivery_company' => array( 'label' => tra( 'Company' ), 'help' => '', 'need' => FALSE ),
		'delivery_street_address1' => array( 'label' => tra( 'Street' ), 'help' => '', 'need' => TRUE ),
		'delivery_street_address2' => array( 'label' => tra( 'Address line 2' ), 'help' => tra( 'apartment, suite, unit' ), 'need' => FALSE ),
		'delivery_city' => array( 'label' => tra( 'City' ), 'help' => '', 'need' => FALSE ),
		'delivery_state' => array( 'label' => tra( 'State' ), 'help' => tra( 'province or region' ), 'need' => FALSE ),
		'delivery_postcode' => array( 'label' => tra( 'Zip' ), 'help' => tra( 'postal code' ), 'need' => FALSE ),
		'delivery_country_iso2' => array( 'label' => tra( 'Country' ), 'help' => tra( 'blank rows use the store country' ), 'need' => FALSE ),
		'delivery_telephone' => array( 'label' => tra( 'Phone' ), 'help' => '', 'need' => FALSE ),
		'comments' => array( 'label' => tra( 'Comments' ), 'help' => tra( 'a note for that order' ), 'need' => FALSE ),
	);
}

// Header names a person types, mapped to the canonical column keys (see pub/drop_ship.csv)
function bc_address_header_aliases() {
	return array(
		'name' => 'delivery_name', 'full_name' => 'delivery_name', 'recipient' => 'delivery_name', 'recipient_name' => 'delivery_name',
		'ship_to' => 'delivery_name', 'ship_to_name' => 'delivery_name', 'contact' => 'delivery_name', 'contact_name' => 'delivery_name', 'attention' => 'delivery_name', 'attn' => 'delivery_name',
		'first_name' => '_first_name', 'firstname' => '_first_name', 'first' => '_first_name', 'given_name' => '_first_name',
		'last_name' => '_last_name', 'lastname' => '_last_name', 'last' => '_last_name', 'surname' => '_last_name', 'family_name' => '_last_name',
		'company' => 'delivery_company', 'organization' => 'delivery_company', 'organisation' => 'delivery_company', 'org' => 'delivery_company', 'business' => 'delivery_company', 'school' => 'delivery_company',
		'address' => 'delivery_street_address1', 'address1' => 'delivery_street_address1', 'address_1' => 'delivery_street_address1', 'address_line_1' => 'delivery_street_address1', 'address_line1' => 'delivery_street_address1',
		'street' => 'delivery_street_address1', 'street_address' => 'delivery_street_address1', 'street_1' => 'delivery_street_address1', 'street1' => 'delivery_street_address1', 'addr1' => 'delivery_street_address1', 'addr' => 'delivery_street_address1',
		'address2' => 'delivery_street_address2', 'address_2' => 'delivery_street_address2', 'address_line_2' => 'delivery_street_address2', 'address_line2' => 'delivery_street_address2',
		'street2' => 'delivery_street_address2', 'street_2' => 'delivery_street_address2', 'addr2' => 'delivery_street_address2', 'suite' => 'delivery_street_address2', 'apt' => 'delivery_street_address2', 'apartment' => 'delivery_street_address2', 'unit' => 'delivery_street_address2',
		'city' => 'delivery_city', 'town' => 'delivery_city', 'suburb' => 'delivery_city', 'locality' => 'delivery_city',
		'state' => 'delivery_state', 'province' => 'delivery_state', 'region' => 'delivery_state', 'state_province' => 'delivery_state', 'st' => 'delivery_state', 'county' => 'delivery_state', 'prefecture' => 'delivery_state',
		'zip' => 'delivery_postcode', 'zipcode' => 'delivery_postcode', 'zip_code' => 'delivery_postcode', 'postal' => 'delivery_postcode', 'postal_code' => 'delivery_postcode', 'postcode' => 'delivery_postcode', 'post_code' => 'delivery_postcode', 'zip_postal_code' => 'delivery_postcode',
		'country' => 'delivery_country_iso2', 'country_code' => 'delivery_country_iso2', 'country_iso2' => 'delivery_country_iso2', 'iso2' => 'delivery_country_iso2', 'country_region' => 'delivery_country_iso2',
		'phone' => 'delivery_telephone', 'telephone' => 'delivery_telephone', 'phone_number' => 'delivery_telephone', 'tel' => 'delivery_telephone', 'mobile' => 'delivery_telephone', 'cell' => 'delivery_telephone',
		'comment' => 'comments', 'note' => 'comments', 'notes' => 'comments', 'instructions' => 'comments', 'gift_message' => 'comments', 'message' => 'comments',
	);
}

// One header cell → canonical key, or '' when the column is not used
function bc_address_normalize_header( $pHeader ) {
	$key = strtolower( trim( (string)$pHeader ) );
	$key = preg_replace( '/^\xEF\xBB\xBF/', '', $key );
	$key = trim( $key, " \t\n\r\0\x0B\"'" );
	$key = preg_replace( '/[^a-z0-9]+/', '_', $key );
	$key = trim( $key, '_' );
	if( $key === '' ) {
		return '';
	}
	$aliases = bc_address_header_aliases();
	if( isset( $aliases[$key] ) ) {
		return $aliases[$key];
	}
	// canonical columns pass through
	if( in_array( $key, array( 'cart', 'comments', 'dc_redeem_code', 'deadline_date' ) ) ) {
		return $key;
	}
	foreach( array( 'delivery_', 'billing_', 'payment_', 'shipping_', 'ot_' ) as $prefix ) {
		if( strpos( $key, $prefix ) === 0 ) {
			return $key;
		}
	}
	return '';
}

// Extensions we read as a table, and the workbook formats we refuse with a save-as-CSV message
function bc_address_table_extensions() {
	return array( 'csv', 'txt', 'tsv', 'xlsx', 'xlsm' );
}

function bc_address_refused_extensions() {
	return array( 'xls', 'numbers', 'ods', 'pages', 'doc', 'docx', 'pdf' );
}

function bc_address_row_limit() {
	return 5000;
}

/**
 * Read an uploaded CSV or XLSX into sheets of raw cells.
 *
 * @return array|NULL array( 'name' => file name, 'kind' => 'csv'|'xlsx', 'sheets' => array( array( 'name' => sheet, 'headers' => array of header cells, 'rows' => array( array( 'n' => line/row number, 'c' => cells ) ) ) ) )
 */
function bc_address_read_table( $pFile, $pName, &$pErrors ) {
	$pErrors = array( 'error' => array(), 'warning' => array() );
	$ext = strtolower( pathinfo( (string)$pName, PATHINFO_EXTENSION ) );

	if( !file_exists( $pFile ) || !is_readable( $pFile ) ) {
		$pErrors['error'][] = tra( 'The file could not be read.' );
		return NULL;
	}
	if( in_array( $ext, bc_address_refused_extensions() ) ) {
		$pErrors['error'][] = tra( 'That spreadsheet format cannot be read. In your spreadsheet choose File → Save As (or Export) and pick CSV or Excel (.xlsx), then upload that file.' );
		return NULL;
	}

	$head = (string)file_get_contents( $pFile, FALSE, NULL, 0, 4 );
	if( $head === "PK\x03\x04" ) {
		$sheets = bc_address_read_xlsx( $pFile, $pErrors );
		$kind = 'xlsx';
	} elseif( in_array( $ext, array( 'xlsx', 'xlsm' ) ) ) {
		$pErrors['error'][] = tra( 'That is not an Excel workbook. Save the sheet again as .xlsx or as CSV and upload that file.' );
		return NULL;
	} else {
		$sheets = bc_address_read_csv( $pFile, $pErrors );
		$kind = 'csv';
	}
	if( !empty( $pErrors['error'] ) || empty( $sheets ) ) {
		return NULL;
	}
	return array( 'name' => (string)$pName, 'kind' => $kind, 'sheets' => $sheets );
}

// The header row is the first row with two or more filled cells; the rows before it (a title line) are dropped
function bc_address_split_header( $pRows, $pSheetName, &$pErrors ) {
	$headerAt = NULL;
	foreach( $pRows as $i => $row ) {
		if( count( array_filter( $row['c'], function( $v ) { return trim( (string)$v ) !== ''; } ) ) >= 2 ) {
			$headerAt = $i;
			break;
		}
	}
	if( $headerAt === NULL ) {
		return NULL;
	}
	$headers = array_map( 'trim', array_map( 'strval', $pRows[$headerAt]['c'] ) );
	// drop trailing empty header cells; a blob of control characters means a binary file
	while( count( $headers ) && end( $headers ) === '' ) {
		array_pop( $headers );
	}
	$headerBlob = implode( '', $headers );
	if( preg_match( '/[^\x09\x0A\x0D\x20-\x7E\x80-\xFF]/', $headerBlob ) ) {
		$pErrors['error'][] = tra( 'That is not a CSV text file. In your spreadsheet choose File → Save As (or Export) and pick CSV or Excel (.xlsx), then upload that file.' );
		return NULL;
	}
	$dataRows = array();
	foreach( array_slice( $pRows, $headerAt + 1 ) as $row ) {
		if( !count( array_filter( $row['c'], function( $v ) { return trim( (string)$v ) !== ''; } ) ) ) {
			continue;
		}
		$dataRows[] = $row;
	}
	return array( 'name' => (string)$pSheetName, 'headers' => $headers, 'rows' => $dataRows );
}

// A CSV is one sheet. The delimiter is whichever of comma, semicolon, or tab the header line uses most.
function bc_address_read_csv( $pFile, &$pErrors ) {
	if( !($handle = fopen( $pFile, 'r' )) ) {
		$pErrors['error'][] = tra( 'The file could not be opened.' );
		return array();
	}
	$firstLine = (string)fgets( $handle );
	rewind( $handle );
	$delimiter = ',';
	$best = substr_count( $firstLine, ',' );
	foreach( array( ';', "\t" ) as $candidate ) {
		if( substr_count( $firstLine, $candidate ) > $best ) {
			$best = substr_count( $firstLine, $candidate );
			$delimiter = $candidate;
		}
	}
	// strip a byte-order mark so the first header matches its alias
	$bom = fread( $handle, 3 );
	if( $bom !== "\xEF\xBB\xBF" ) {
		rewind( $handle );
	}
	$rows = array();
	$lineNumber = 0;
	$limit = bc_address_row_limit();
	while( ($data = fgetcsv( $handle, 0, $delimiter )) !== FALSE ) {
		$lineNumber++;
		if( !is_array( $data ) ) {
			continue;
		}
		$rows[] = array( 'n' => $lineNumber, 'c' => array_map( function( $v ) { return trim( (string)$v ); }, $data ) );
		if( count( $rows ) > $limit ) {
			$pErrors['warning'][] = tra( 'Only the first' ).' '.$limit.' '.tra( 'rows were read.' );
			break;
		}
	}
	fclose( $handle );
	if( !($sheet = bc_address_split_header( $rows, '', $pErrors )) ) {
		if( empty( $pErrors['error'] ) ) {
			$pErrors['error'][] = tra( 'The file is empty or has no header row. The first row must name the columns (Name, Street, City, State, Zip, Country, Phone).' );
		}
		return array();
	}
	return array( $sheet );
}

// Column letters of a cell reference (AB12 → 27)
function bc_address_xlsx_column( $pRef ) {
	$letters = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string)$pRef ) );
	$index = 0;
	for( $i = 0; $i < strlen( $letters ); $i++ ) {
		$index = $index * 26 + ( ord( $letters[$i] ) - 64 );
	}
	return max( 0, $index - 1 );
}

// Text of a cell: shared string, inline string, boolean, or the stored number (whole numbers without a decimal point)
function bc_address_xlsx_cell_value( $pCell, $pSharedStrings ) {
	$type = (string)$pCell['t'];
	if( $type === 's' ) {
		$index = (int)$pCell->v;
		return isset( $pSharedStrings[$index] ) ? $pSharedStrings[$index] : '';
	}
	if( $type === 'inlineStr' ) {
		$text = '';
		if( isset( $pCell->is ) ) {
			foreach( $pCell->is->t as $t ) {
				$text .= (string)$t;
			}
			foreach( $pCell->is->r as $run ) {
				$text .= (string)$run->t;
			}
		}
		return $text;
	}
	if( $type === 'b' ) {
		return ((string)$pCell->v === '1') ? '1' : '0';
	}
	if( $type === 'e' ) {
		return '';
	}
	$value = isset( $pCell->v ) ? (string)$pCell->v : '';
	if( $type !== 'str' && $value !== '' && is_numeric( $value ) ) {
		$float = (float)$value;
		if( $float == floor( $float ) && abs( $float ) < 1e15 ) {
			$value = sprintf( '%.0f', $float );
		}
	}
	return $value;
}

// Every worksheet of an .xlsx as rows of cells. Needs the zip and SimpleXML extensions; no external library.
function bc_address_read_xlsx( $pFile, &$pErrors ) {
	if( !class_exists( 'ZipArchive' ) || !function_exists( 'simplexml_load_string' ) ) {
		$pErrors['error'][] = tra( 'Excel workbooks cannot be read on this server. Save the sheet as CSV and upload that file.' );
		return array();
	}
	$zip = new ZipArchive();
	if( $zip->open( $pFile ) !== TRUE ) {
		$pErrors['error'][] = tra( 'The workbook could not be opened. Save the sheet again as .xlsx or as CSV and upload that file.' );
		return array();
	}
	libxml_use_internal_errors( TRUE );

	$sharedStrings = array();
	if( ($sharedXml = $zip->getFromName( 'xl/sharedStrings.xml' )) !== FALSE && ($shared = simplexml_load_string( $sharedXml )) ) {
		foreach( $shared->si as $si ) {
			$text = '';
			foreach( $si->t as $t ) {
				$text .= (string)$t;
			}
			foreach( $si->r as $run ) {
				$text .= (string)$run->t;
			}
			$sharedStrings[] = $text;
		}
	}

	// sheet order and names from the workbook, file paths from its relationships
	$targets = array();
	if( ($relsXml = $zip->getFromName( 'xl/_rels/workbook.xml.rels' )) !== FALSE && ($rels = simplexml_load_string( $relsXml )) ) {
		foreach( $rels->Relationship as $rel ) {
			$target = (string)$rel['Target'];
			$target = ( strpos( $target, '/' ) === 0 ) ? ltrim( $target, '/' ) : 'xl/'.$target;
			$targets[(string)$rel['Id']] = $target;
		}
	}
	$sheetFiles = array();
	if( ($workbookXml = $zip->getFromName( 'xl/workbook.xml' )) !== FALSE && ($workbook = simplexml_load_string( $workbookXml )) && isset( $workbook->sheets ) ) {
		foreach( $workbook->sheets->sheet as $sheet ) {
			$relAttrs = $sheet->attributes( 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' );
			$relId = isset( $relAttrs['id'] ) ? (string)$relAttrs['id'] : '';
			if( isset( $targets[$relId] ) && (string)$sheet['state'] !== 'hidden' ) {
				$sheetFiles[] = array( 'name' => (string)$sheet['name'], 'path' => $targets[$relId] );
			}
		}
	}
	if( empty( $sheetFiles ) ) {
		for( $i = 1; $i <= 20; $i++ ) {
			if( $zip->locateName( 'xl/worksheets/sheet'.$i.'.xml' ) !== FALSE ) {
				$sheetFiles[] = array( 'name' => tra( 'Sheet' ).' '.$i, 'path' => 'xl/worksheets/sheet'.$i.'.xml' );
			}
		}
	}

	$sheets = array();
	$limit = bc_address_row_limit();
	foreach( $sheetFiles as $sheetFile ) {
		if( ($sheetXml = $zip->getFromName( $sheetFile['path'] )) === FALSE || !($xml = simplexml_load_string( $sheetXml )) || !isset( $xml->sheetData ) ) {
			continue;
		}
		$rows = array();
		foreach( $xml->sheetData->row as $row ) {
			$cells = array();
			$maxCol = -1;
			foreach( $row->c as $cell ) {
				$col = bc_address_xlsx_column( (string)$cell['r'] );
				$cells[$col] = trim( bc_address_xlsx_cell_value( $cell, $sharedStrings ) );
				$maxCol = max( $maxCol, $col );
			}
			$line = array();
			for( $i = 0; $i <= $maxCol; $i++ ) {
				$line[] = isset( $cells[$i] ) ? $cells[$i] : '';
			}
			$rows[] = array( 'n' => (int)$row['r'], 'c' => $line );
			if( count( $rows ) > $limit ) {
				$pErrors['warning'][] = tra( 'Only the first' ).' '.$limit.' '.tra( 'rows of' ).' '.htmlspecialchars( $sheetFile['name'] ).' '.tra( 'were read.' );
				break;
			}
		}
		if( $sheet = bc_address_split_header( $rows, $sheetFile['name'], $pErrors ) ) {
			$sheets[] = $sheet;
		}
	}
	$zip->close();
	libxml_clear_errors();

	if( empty( $sheets ) && empty( $pErrors['error'] ) ) {
		$pErrors['error'][] = tra( 'No sheet in the workbook has a header row and addresses under it.' );
	}
	return $sheets;
}

// Match a header row to the import fields: field key → column index. Unknown headings are left out.
function bc_address_guess_mapping( $pHeaders ) {
	$mapping = array();
	foreach( $pHeaders as $index => $header ) {
		$key = bc_address_normalize_header( $header );
		if( $key !== '' && !isset( $mapping[$key] ) ) {
			$mapping[$key] = (int)$index;
		}
	}
	return $mapping;
}

// A mapping is usable when it names a person (name, or first or last) and a street
function bc_address_mapping_complete( $pMapping ) {
	$hasName = isset( $pMapping['delivery_name'] ) || isset( $pMapping['_first_name'] ) || isset( $pMapping['_last_name'] );
	return $hasName && isset( $pMapping['delivery_street_address1'] );
}

// Mapping from the posted map[field] menus; '' leaves a field out, an index outside the header row is ignored.
// Columns the guess matched that the menus do not offer (cart, billing_*, payment_*, shipping_*) are kept from $pBase.
function bc_address_mapping_from_post( $pPosted, $pHeaders, $pBase = array() ) {
	$mapping = array();
	$offered = bc_address_import_fields();
	if( is_array( $pBase ) ) {
		foreach( $pBase as $field => $index ) {
			if( !isset( $offered[$field] ) ) {
				$mapping[$field] = (int)$index;
			}
		}
	}
	if( !is_array( $pPosted ) ) {
		return $mapping;
	}
	foreach( array_keys( bc_address_import_fields() ) as $field ) {
		if( isset( $pPosted[$field] ) && !is_array( $pPosted[$field] ) && $pPosted[$field] !== '' && is_numeric( $pPosted[$field] ) ) {
			$index = (int)$pPosted[$field];
			if( $index >= 0 && $index < count( $pHeaders ) ) {
				$mapping[$field] = $index;
			}
		}
	}
	return $mapping;
}

// Headings the mapping does not use, for the "ignored columns" warning
function bc_address_mapping_ignored( $pMapping, $pHeaders ) {
	$used = array_flip( array_map( 'intval', $pMapping ) );
	$ignored = array();
	foreach( $pHeaders as $index => $header ) {
		if( trim( (string)$header ) !== '' && !isset( $used[(int)$index] ) ) {
			$ignored[] = trim( (string)$header );
		}
	}
	return $ignored;
}

// Validated rows from one sheet and a mapping. $pFileName fills _file; the sheet name joins the line label when the workbook has several.
function bc_address_rows_from_table( $pSheet, $pMapping, $pFileName, $pSheetCount, &$pErrors ) {
	$pErrors = array( 'error' => array(), 'warning' => array() );
	$ret = array();
	if( !bc_address_mapping_complete( $pMapping ) ) {
		$pErrors['error'][] = tra( 'The columns need at least a Name (or First and Last name) and a Street.' );
		return $ret;
	}
	$storeCountryIso2 = bc_address_store_country_iso2();
	$labelPrefix = ( $pSheetCount > 1 && $pSheet['name'] !== '' ) ? $pSheet['name'].', ' : '';
	foreach( $pSheet['rows'] as $tableRow ) {
		$row = array( '_line' => (int)$tableRow['n'], '_line_label' => $labelPrefix.tra( 'line' ).' '.(int)$tableRow['n'], '_file' => (string)$pFileName, '_errors' => array() );
		foreach( $pMapping as $field => $index ) {
			$row[$field] = isset( $tableRow['c'][$index] ) ? trim( (string)$tableRow['c'][$index] ) : '';
		}
		if( trim( (string)BitBase::getParameter( $row, 'delivery_name', '' ) ) === '' ) {
			$row['delivery_name'] = trim( BitBase::getParameter( $row, '_first_name', '' ).' '.BitBase::getParameter( $row, '_last_name', '' ) );
		}
		unset( $row['_first_name'], $row['_last_name'] );
		foreach( bc_address_fields() as $field ) {
			if( !isset( $row[$field] ) ) {
				$row[$field] = '';
			}
		}
		bc_address_validate_row( $row, $storeCountryIso2 );
		$ret[] = $row;
	}
	if( empty( $ret ) ) {
		$pErrors['error'][] = tra( 'No address rows were found under the header row.' );
	}
	return $ret;
}

// The mapping table for the template: one entry per field with the menu of headings and a sample from the first data row
function bc_address_mapping_view( $pTable, $pSheetIndex, $pMapping ) {
	$sheet = isset( $pTable['sheets'][$pSheetIndex] ) ? $pTable['sheets'][$pSheetIndex] : $pTable['sheets'][0];
	$sample = !empty( $sheet['rows'] ) ? $sheet['rows'][0]['c'] : array();
	$columns = array();
	foreach( $sheet['headers'] as $index => $header ) {
		$columns[] = array( 'index' => (int)$index, 'label' => ( trim( (string)$header ) !== '' ) ? trim( (string)$header ) : tra( 'column' ).' '.( (int)$index + 1 ), 'sample' => isset( $sample[$index] ) ? (string)$sample[$index] : '' );
	}
	$fields = array();
	foreach( bc_address_import_fields() as $key => $meta ) {
		$selected = isset( $pMapping[$key] ) ? (int)$pMapping[$key] : '';
		$fields[] = array( 'key' => $key, 'label' => $meta['label'], 'help' => $meta['help'], 'need' => $meta['need'], 'selected' => $selected, 'sample' => ( $selected !== '' && isset( $sample[$selected] ) ) ? (string)$sample[$selected] : '' );
	}
	$sheets = array();
	foreach( $pTable['sheets'] as $index => $tableSheet ) {
		$sheets[] = array( 'index' => (int)$index, 'name' => ( $tableSheet['name'] !== '' ) ? $tableSheet['name'] : tra( 'Sheet' ).' '.( (int)$index + 1 ), 'rows' => count( $tableSheet['rows'] ) );
	}
	return array( 'file' => $pTable['name'], 'sheet' => (int)$pSheetIndex, 'sheets' => $sheets, 'columns' => $columns, 'fields' => $fields, 'row_count' => count( $sheet['rows'] ) );
}

// First sheet whose guessed mapping is complete, else 0
function bc_address_pick_sheet( $pTable ) {
	foreach( $pTable['sheets'] as $index => $sheet ) {
		if( bc_address_mapping_complete( bc_address_guess_mapping( $sheet['headers'] ) ) ) {
			return (int)$index;
		}
	}
	return 0;
}

// The one import in flight waits in the session between the upload and the mapping step
function bc_address_import_store( $pTable, $pTarget, $pExtra = array() ) {
	$sheetIndex = bc_address_pick_sheet( $pTable );
	$_SESSION['address_import'] = array_merge( array(
		'table' => $pTable,
		'target' => (string)$pTarget,
		'sheet' => $sheetIndex,
		'mapping' => bc_address_guess_mapping( $pTable['sheets'][$sheetIndex]['headers'] ),
		'applied' => FALSE,
	), is_array( $pExtra ) ? $pExtra : array() );
	return $_SESSION['address_import'];
}

function bc_address_import_get( $pTarget ) {
	if( !empty( $_SESSION['address_import'] ) && is_array( $_SESSION['address_import'] ) && BitBase::getParameter( $_SESSION['address_import'], 'target' ) === (string)$pTarget ) {
		return $_SESSION['address_import'];
	}
	return NULL;
}

function bc_address_import_update( $pChanges ) {
	if( !empty( $_SESSION['address_import'] ) && is_array( $_SESSION['address_import'] ) ) {
		$_SESSION['address_import'] = array_merge( $_SESSION['address_import'], $pChanges );
	}
}

function bc_address_import_clear() {
	unset( $_SESSION['address_import'] );
}

function bc_address_store_country_iso2() {
	if( $storeCountry = zen_get_countries( STORE_COUNTRY ) ) {
		return (string)BitBase::getParameter( $storeCountry, 'countries_iso_code_2', '' );
	}
	return '';
}

// Country spellings people type that the lookup table does not have
function bc_address_country_alias( $pCountry ) {
	$key = strtoupper( trim( preg_replace( '/[^A-Za-z ]/', '', $pCountry ) ) );
	$aliases = array(
		'USA' => 'US', 'UNITED STATES' => 'US', 'UNITED STATES OF AMERICA' => 'US', 'U S A' => 'US', 'US' => 'US', 'AMERICA' => 'US',
		'UK' => 'GB', 'GREAT BRITAIN' => 'GB', 'ENGLAND' => 'GB', 'SCOTLAND' => 'GB', 'WALES' => 'GB', 'UNITED KINGDOM' => 'GB',
		'CAN' => 'CA', 'CANADA' => 'CA',
	);
	return isset( $aliases[$key] ) ? $aliases[$key] : trim( $pCountry );
}

// Normalizes and checks one address row in place: name split, country, state, postcode. Problems go in $row['_errors'].
function bc_address_validate_row( &$row, $pStoreCountryIso2 ) {
	if( !isset( $row['_errors'] ) || !is_array( $row['_errors'] ) ) {
		$row['_errors'] = array();
	}
	$row['delivery_name'] = trim( (string)BitBase::getParameter( $row, 'delivery_name', '' ) );
	if( $row['delivery_name'] === '' ) {
		$row['_errors'][] = tra( 'Missing name' );
	} elseif( strpos( $row['delivery_name'], ' ' ) === FALSE ) {
		$row['_errors'][] = tra( 'Name needs a first and last name' );
	}
	if( trim( (string)BitBase::getParameter( $row, 'delivery_street_address1', '' ) ) === '' ) {
		$row['_errors'][] = tra( 'Missing street address' );
	}
	if( trim( (string)BitBase::getParameter( $row, 'delivery_city', '' ) ) === '' ) {
		$row['_errors'][] = tra( 'Missing city' );
	}

	$countryValue = trim( (string)BitBase::getParameter( $row, 'delivery_country_iso2', '' ) );
	if( $countryValue === '' ) {
		$countryValue = $pStoreCountryIso2;
	}
	$countryValue = bc_address_country_alias( $countryValue );
	$countryHash = $countryValue !== '' ? zen_get_countries( $countryValue ) : array();
	if( empty( $countryHash['countries_id'] ) ) {
		$row['_errors'][] = tra( 'Unknown country' ).' "'.htmlspecialchars( $countryValue ).'"';
	} else {
		$row['delivery_country_iso2'] = $countryHash['countries_iso_code_2'];
		$stateValue = trim( (string)BitBase::getParameter( $row, 'delivery_state', '' ) );
		$countryHasZones = bc_address_country_has_zones( $countryHash['countries_id'] );
		if( $stateValue !== '' ) {
			if( $zone = zen_get_zone_by_name( $countryHash['countries_id'], $stateValue ) ) {
				$row['delivery_state'] = $zone['zone_code'];
			} elseif( $countryHasZones ) {
				$row['_errors'][] = tra( 'Unknown state' ).' "'.htmlspecialchars( $stateValue ).'"';
			}
		} elseif( $countryHasZones ) {
			$row['_errors'][] = tra( 'Missing state' );
		}
		if( trim( (string)BitBase::getParameter( $row, 'delivery_postcode', '' ) ) === '' && $countryHash['address_format_id'] != 4 ) {
			$row['_errors'][] = tra( 'Missing postal code' );
		}
	}
}

function bc_address_country_has_zones( $pCountryId ) {
	global $gBitDb;
	static $cache = array();
	if( !isset( $cache[$pCountryId] ) ) {
		$cache[$pCountryId] = (bool)$gBitDb->getOne( "SELECT COUNT(*) FROM ".TABLE_ZONES." WHERE `zone_country_id`=?", array( (int)$pCountryId ) );
	}
	return $cache[$pCountryId];
}

// Countries for a select: array( array( 'iso2' => 'US', 'name' => 'United States' ), … ) in name order
function bc_address_country_options() {
	$ret = array();
	foreach( zen_get_countries() as $country ) {
		if( !empty( $country['countries_iso_code_2'] ) ) {
			$ret[] = array( 'iso2' => $country['countries_iso_code_2'], 'name' => $country['countries_name'] );
		}
	}
	return $ret;
}

// State lists keyed by country ISO2, for a page script: array( 'US' => array( array( 'AL', 'Alabama' ), … ) )
function bc_address_zones_by_country() {
	global $gBitDb;
	$ret = array();
	$sql = "SELECT c.`countries_iso_code_2`, z.`zone_code`, z.`zone_name` FROM ".TABLE_ZONES." z INNER JOIN ".TABLE_COUNTRIES." c ON( c.`countries_id`=z.`zone_country_id` ) ORDER BY c.`countries_iso_code_2`, z.`zone_name`";
	if( $rs = $gBitDb->query( $sql, array(), FALSE, FALSE, BIT_QUERY_CACHE_TIME ) ) {
		while( !$rs->EOF ) {
			$ret[$rs->fields['countries_iso_code_2']][] = array( $rs->fields['zone_code'], $rs->fields['zone_name'] );
			$rs->MoveNext();
		}
	}
	return $ret;
}

// TRUE when the file starts with a vCard, whatever its extension
function bc_address_is_vcard( $pFile ) {
	if( !is_readable( $pFile ) || !($head = file_get_contents( $pFile, FALSE, NULL, 0, 64 )) ) {
		return FALSE;
	}
	$head = preg_replace( '/^\xEF\xBB\xBF/', '', $head );
	return (bool)preg_match( '/^\s*BEGIN:VCARD/i', $head );
}

function bc_address_vcard_unescape( $pValue ) {
	return strtr( (string)$pValue, array( '\\n' => "\n", '\\N' => "\n", '\\,' => ',', '\;' => ';', '\\\\' => '\\' ) );
}

// Split a compound vCard value (N, ADR) on unescaped semicolons and unescape each part
function bc_address_vcard_parts( $pValue, $pCount ) {
	$parts = preg_split( '/(?<!\\\\);/', (string)$pValue );
	$parts = array_map( 'bc_address_vcard_unescape', $parts );
	return array_pad( $parts, $pCount, '' );
}

// Pick one of several ADR / TEL entries: preferred first, then the wanted type, then the first one
function bc_address_vcard_pick( $pEntries, $pWantType ) {
	if( empty( $pEntries ) ) {
		return NULL;
	}
	foreach( $pEntries as $entry ) {
		if( in_array( 'PREF', $entry['types'] ) || isset( $entry['params']['PREF'] ) ) {
			return $entry;
		}
	}
	foreach( $pEntries as $entry ) {
		if( in_array( $pWantType, $entry['types'] ) ) {
			return $entry;
		}
	}
	return $pEntries[0];
}

// Parse a vCard file (2.1, 3.0, 4.0; one or many cards) into the same rows the table import makes
function bc_address_parse_vcard( $pFile, &$pErrors ) {
	$pErrors = array( 'error' => array(), 'warning' => array() );
	$ret = array();

	if( !is_readable( $pFile ) || ($text = file_get_contents( $pFile )) === FALSE ) {
		$pErrors['error'][] = tra( 'The file could not be read.' );
		return $ret;
	}
	$text = preg_replace( '/^\xEF\xBB\xBF/', '', $text );
	$text = str_replace( array( "\r\n", "\r" ), "\n", $text );
	// folded lines continue with a space or tab
	$text = preg_replace( "/\n[ \t]/", '', $text );
	// Walk lines rather than regex the whole file: embedded photos make a card hundreds of KB,
	// and a lazy match across that exceeds the PCRE backtrack limit and silently fails.
	$cards = array();
	$current = NULL;
	foreach( explode( "\n", $text ) as $line ) {
		$marker = strtoupper( trim( $line ) );
		if( $marker === 'BEGIN:VCARD' ) {
			$current = array();
		} elseif( $marker === 'END:VCARD' ) {
			if( is_array( $current ) ) {
				$cards[] = $current;
			}
			$current = NULL;
		} elseif( is_array( $current ) && $line !== '' ) {
			$current[] = $line;
		}
	}
	unset( $text );
	if( empty( $cards ) ) {
		$pErrors['error'][] = tra( 'No contacts were found in the file. Export the contacts again as a vCard (.vcf) file, or save your list as CSV.' );
		return $ret;
	}

	$storeCountryIso2 = bc_address_store_country_iso2();
	$noAddress = array();
	$wantedProps = array( 'FN', 'N', 'ORG', 'ADR', 'TEL', 'NOTE' );

	foreach( $cards as $cardIndex => $card ) {
		$cardNumber = $cardIndex + 1;
		$props = array();
		foreach( $card as $line ) {
			if( strpos( $line, ':' ) === FALSE ) {
				continue;
			}
			list( $head, $value ) = explode( ':', $line, 2 );
			$headParts = explode( ';', $head );
			$name = strtoupper( trim( array_shift( $headParts ) ) );
			// Apple groups properties as item1.ADR
			if( ($dot = strrpos( $name, '.' )) !== FALSE ) {
				$name = substr( $name, $dot + 1 );
			}
			if( !in_array( $name, $wantedProps ) ) {
				continue;
			}
			$params = array();
			$types = array();
			foreach( $headParts as $param ) {
				if( strpos( $param, '=' ) !== FALSE ) {
					list( $paramKey, $paramValue ) = explode( '=', $param, 2 );
					$paramKey = strtoupper( trim( $paramKey ) );
					$paramValues = array_map( 'strtoupper', array_map( 'trim', explode( ',', str_replace( '"', '', $paramValue ) ) ) );
					$params[$paramKey] = $paramValues;
					if( $paramKey == 'TYPE' ) {
						$types = array_merge( $types, $paramValues );
					}
				} else {
					// vCard 2.1 bare type words: ADR;HOME;PREF
					$types[] = strtoupper( trim( $param ) );
				}
			}
			if( !empty( $params['ENCODING'] ) && in_array( 'QUOTED-PRINTABLE', $params['ENCODING'] ) ) {
				$value = quoted_printable_decode( $value );
			}
			// vCard 2.1 exports may declare a legacy charset; everything downstream is UTF-8
			if( !empty( $params['CHARSET'][0] ) && !in_array( $params['CHARSET'][0], array( 'UTF-8', 'UTF8' ) ) && function_exists( 'iconv' ) ) {
				if( ($converted = @iconv( $params['CHARSET'][0], 'UTF-8//IGNORE', $value )) !== FALSE ) {
					$value = $converted;
				}
			}
			$props[$name][] = array( 'params' => $params, 'types' => $types, 'value' => trim( $value ) );
		}

		$fullName = !empty( $props['FN'][0]['value'] ) ? bc_address_vcard_unescape( $props['FN'][0]['value'] ) : '';
		if( !empty( $props['N'][0]['value'] ) ) {
			list( $lastName, $firstName ) = bc_address_vcard_parts( $props['N'][0]['value'], 5 );
			$structuredName = trim( $firstName.' '.$lastName );
			if( $fullName === '' || strpos( $fullName, ' ' ) === FALSE ) {
				$fullName = $structuredName !== '' ? $structuredName : $fullName;
			}
		}
		$company = !empty( $props['ORG'][0]['value'] ) ? trim( bc_address_vcard_parts( $props['ORG'][0]['value'], 1 )[0] ) : '';
		if( $fullName === '' && $company !== '' ) {
			$fullName = $company;
		}

		$row = array(
			'_line' => $cardNumber,
			'_line_label' => tra( 'contact' ).' '.$cardNumber,
			'_errors' => array(),
			'delivery_name' => $fullName,
			'delivery_company' => $company,
			'delivery_street_address1' => '',
			'delivery_street_address2' => '',
			'delivery_city' => '',
			'delivery_state' => '',
			'delivery_postcode' => '',
			'delivery_country_iso2' => '',
			'delivery_telephone' => '',
			'comments' => '',
		);

		if( $adr = bc_address_vcard_pick( BitBase::getParameter( $props, 'ADR', array() ), 'HOME' ) ) {
			list( $poBox, $extended, $street, $city, $region, $postal, $country ) = bc_address_vcard_parts( $adr['value'], 7 );
			$streetLines = array_values( array_filter( array_map( 'trim', explode( "\n", $street ) ), 'strlen' ) );
			$row['delivery_street_address1'] = isset( $streetLines[0] ) ? $streetLines[0] : trim( $poBox );
			$extra = array_slice( $streetLines, 1 );
			if( trim( $extended ) !== '' ) {
				array_unshift( $extra, trim( $extended ) );
			}
			if( isset( $streetLines[0] ) && trim( $poBox ) !== '' ) {
				$extra[] = trim( $poBox );
			}
			$row['delivery_street_address2'] = implode( ', ', $extra );
			$row['delivery_city'] = trim( $city );
			$row['delivery_state'] = trim( $region );
			$row['delivery_postcode'] = trim( $postal );
			$row['delivery_country_iso2'] = trim( $country );
		} else {
			$noAddress[] = $fullName !== '' ? $fullName : tra( 'contact' ).' '.$cardNumber;
			continue;
		}
		if( $tel = bc_address_vcard_pick( BitBase::getParameter( $props, 'TEL', array() ), 'CELL' ) ) {
			$row['delivery_telephone'] = preg_replace( '/^tel:/i', '', bc_address_vcard_unescape( $tel['value'] ) );
		}
		if( !empty( $props['NOTE'][0]['value'] ) ) {
			$row['comments'] = bc_address_vcard_unescape( $props['NOTE'][0]['value'] );
		}

		bc_address_validate_row( $row, $storeCountryIso2 );
		$ret[] = $row;
	}

	if( !empty( $noAddress ) ) {
		$pErrors['warning'][] = count( $noAddress ).' '.tra( 'contacts have no postal address and were left out:' ).' '.htmlspecialchars( implode( ', ', $noAddress ) );
	}
	if( empty( $ret ) ) {
		$pErrors['error'][] = tra( 'None of the contacts in the file has a postal address.' );
	}

	return $ret;
}

// Two rows are the same person at the same place: name, street, and postcode, case-insensitive
function bc_address_row_key( $pRow ) {
	$key = preg_replace( '/\s+/u', ' ', trim( BitBase::getParameter( $pRow, 'delivery_name', '' ).'|'.BitBase::getParameter( $pRow, 'delivery_street_address1', '' ).'|'.BitBase::getParameter( $pRow, 'delivery_postcode', '' ) ) );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $key, 'UTF-8' ) : strtolower( $key );
}

// Address-book entries (by address_book_id) as validated rows
function bc_address_book_rows( $pAddressBookIds ) {
	global $gBitCustomer, $gBitDb;
	$ret = array();
	$wanted = array_flip( array_map( 'intval', $pAddressBookIds ) );
	if( empty( $wanted ) || !($entries = $gBitCustomer->getAddresses()) ) {
		return $ret;
	}
	$storeCountryIso2 = bc_address_store_country_iso2();
	foreach( $entries as $entry ) {
		if( !isset( $wanted[(int)$entry['address_book_id']] ) ) {
			continue;
		}
		$state = trim( (string)BitBase::getParameter( $entry, 'state', '' ) );
		if( $state === '' && BitBase::verifyId( BitBase::getParameter( $entry, 'zone_id' ) ) ) {
			$state = (string)$gBitDb->getOne( "SELECT `zone_code` FROM ".TABLE_ZONES." WHERE `zone_id`=?", array( (int)$entry['zone_id'] ) );
		}
		$row = array(
			'_line' => (int)$entry['address_book_id'],
			'_line_label' => tra( 'address book' ),
			'_book' => TRUE,
			'_book_id' => (int)$entry['address_book_id'],
			'_file' => '',
			'_errors' => array(),
			'delivery_name' => trim( BitBase::getParameter( $entry, 'firstname', '' ).' '.BitBase::getParameter( $entry, 'lastname', '' ) ),
			'delivery_company' => (string)BitBase::getParameter( $entry, 'company', '' ),
			'delivery_street_address1' => (string)BitBase::getParameter( $entry, 'street_address', '' ),
			'delivery_street_address2' => (string)BitBase::getParameter( $entry, 'suburb', '' ),
			'delivery_city' => (string)BitBase::getParameter( $entry, 'city', '' ),
			'delivery_state' => $state,
			'delivery_postcode' => (string)BitBase::getParameter( $entry, 'postcode', '' ),
			'delivery_country_iso2' => (string)BitBase::getParameter( $entry, 'countries_iso_code_2', '' ),
			'delivery_telephone' => (string)BitBase::getParameter( $entry, 'telephone', '' ),
			'comments' => '',
		);
		bc_address_validate_row( $row, $storeCountryIso2 );
		$ret[] = $row;
	}
	return $ret;
}

// Store one row in the shopper's address book through CommerceCustomer::storeAddress().
// An entry with the same name, street, and postcode already there counts as saved ($pDuplicate is set TRUE).
// With $pAddressBookId (an entry the shopper owns, taken from the session row, never from the post) the entry is updated instead.
function bc_address_save_to_book( $pRow, &$pErrors, &$pDuplicate = FALSE, $pAddressBookId = NULL ) {
	global $gBitCustomer;
	$pErrors = array();
	$pDuplicate = FALSE;
	if( empty( $pAddressBookId ) && ($entries = $gBitCustomer->getAddresses()) ) {
		$rowKey = bc_address_row_key( $pRow );
		foreach( $entries as $entry ) {
			$entryRow = array( 'delivery_name' => trim( $entry['firstname'].' '.$entry['lastname'] ), 'delivery_street_address1' => $entry['street_address'], 'delivery_postcode' => $entry['postcode'] );
			if( bc_address_row_key( $entryRow ) === $rowKey ) {
				$pDuplicate = TRUE;
				return TRUE;
			}
		}
	}
	$name = trim( (string)BitBase::getParameter( $pRow, 'delivery_name', '' ) );
	$firstSpace = strpos( $name, ' ' );
	$countryHash = zen_get_countries( (string)BitBase::getParameter( $pRow, 'delivery_country_iso2', '' ) );
	$address = array(
		'firstname' => $firstSpace === FALSE ? $name : substr( $name, 0, $firstSpace ),
		'lastname' => $firstSpace === FALSE ? '' : trim( substr( $name, $firstSpace + 1 ) ),
		'company' => (string)BitBase::getParameter( $pRow, 'delivery_company', '' ),
		'street_address' => (string)BitBase::getParameter( $pRow, 'delivery_street_address1', '' ),
		'suburb' => (string)BitBase::getParameter( $pRow, 'delivery_street_address2', '' ),
		'city' => (string)BitBase::getParameter( $pRow, 'delivery_city', '' ),
		'state' => (string)BitBase::getParameter( $pRow, 'delivery_state', '' ),
		'postcode' => (string)BitBase::getParameter( $pRow, 'delivery_postcode', '' ),
		'telephone' => (string)BitBase::getParameter( $pRow, 'delivery_telephone', '' ),
		'country_id' => (int)BitBase::getParameter( $countryHash, 'countries_id', 0 ),
	);
	if( !empty( $pAddressBookId ) && $gBitCustomer->isAddressOwner( $pAddressBookId ) ) {
		$address['address_book_id'] = (int)$pAddressBookId;
	}
	$gBitCustomer->mErrors = array();
	if( $gBitCustomer->storeAddress( $address ) ) {
		return TRUE;
	}
	$pErrors = array_values( $gBitCustomer->mErrors );
	$gBitCustomer->mErrors = array();
	return FALSE;
}

/**
 * Save every valid row to the address book. Returns counts: saved, duplicate (already there), failed (with the reasons in 'problems').
 * Rows with _errors are counted under 'invalid' and listed in 'problems' with their line label.
 */
function bc_address_save_rows_to_book( $pRows ) {
	$ret = array( 'saved' => 0, 'duplicate' => 0, 'failed' => 0, 'invalid' => 0, 'problems' => array() );
	foreach( $pRows as $row ) {
		$label = trim( BitBase::getParameter( $row, '_line_label', '' ).' '.BitBase::getParameter( $row, 'delivery_name', '' ) );
		if( !empty( $row['_errors'] ) ) {
			$ret['invalid']++;
			$ret['problems'][] = htmlspecialchars( $label ).': '.implode( '. ', $row['_errors'] );
			continue;
		}
		$saveErrors = array();
		$duplicate = FALSE;
		if( bc_address_save_to_book( $row, $saveErrors, $duplicate ) ) {
			if( $duplicate ) {
				$ret['duplicate']++;
			} else {
				$ret['saved']++;
			}
		} else {
			$ret['failed']++;
			$ret['problems'][] = htmlspecialchars( $label ).': '.implode( ' ', $saveErrors );
		}
	}
	return $ret;
}
