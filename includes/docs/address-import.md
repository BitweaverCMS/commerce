# Address import and the address book page

`includes/address_import_lib.php` turns a customer's address file into
validated rows and stores rows in the address book. Two pages use it:
`/bookstore/dropship` (orders per row; see [dropship.md](dropship.md))
and `/bookstore/address_book` (`pages/address_book/address_book.php`,
`templates/page_address_book.tpl`, `css/address_book.css`). Neither page
loads the other.

## Row shape

A row is the drop ship file's delivery columns: `delivery_name`,
`delivery_company`, `delivery_street_address1`, `delivery_street_address2`,
`delivery_city`, `delivery_state`, `delivery_postcode`,
`delivery_country_iso2`, `delivery_telephone`, `comments`
(`bc_address_fields()`), plus any pass-through column a power-user file
carries (`cart`, `billing_*`, `payment_*`, `shipping_*`, `ot_*`,
`dc_redeem_code`, `deadline_date`). Meta keys start with `_`: `_line`,
`_line_label`, `_file`, `_errors`, `_book`, `_save`, `_saved`.

`bc_address_validate_row( &$row, $storeIso2 )` normalizes in place and
fills `_errors`: name present and two words; street and city present;
country resolved through `bc_address_country_alias()` ("USA", "UK", …)
then `zen_get_countries()` (ISO2, ISO3, or name; blank is the store
country) and stored as ISO2; state resolved with
`zen_get_zone_by_name()` and stored as the zone code, required when the
country has rows in `com_zones` (`bc_address_country_has_zones()`);
postcode required unless the country's `address_format_id` is 4.

`bc_address_row_key()` is name + street + postcode, lower-cased, used
for duplicate detection in a list and against the address book.

## Reading a file

- `bc_address_read_table( $file, $name, &$errors )` returns
  `array( 'name', 'kind' => 'csv'|'xlsx', 'sheets' => [ { name, headers,
  rows: [ { n: line or row number, c: cells } ] } ] )` or `NULL` with
  `$errors['error']`. CSV: delimiter sniffed from the first line (comma,
  semicolon, tab), BOM stripped, `fgetcsv`. XLSX / XLSM: `ZipArchive` +
  SimpleXML over `xl/workbook.xml`, its rels, `sharedStrings.xml`, and
  each visible sheet; shared, inline, boolean, and numeric cells (whole
  numbers printed without a decimal point, so a zip stored as a number
  survives unless Excel dropped its leading zero). A workbook renamed
  `.csv` is detected by the `PK` header; a text file named `.xlsx` is
  refused; `.xls`, `.numbers`, `.ods` are refused with a save-as message.
  The header row is the first row with two or more filled cells; empty
  rows are dropped; 5000 rows per sheet (`bc_address_row_limit()`).
- `bc_address_guess_mapping( $headers )` → `field => column index` via
  `bc_address_normalize_header()` and `bc_address_header_aliases()`.
  `bc_address_mapping_complete()` needs a name (or first or last) and a
  street. `bc_address_pick_sheet()` is the first sheet whose guess is
  complete.
- `bc_address_rows_from_table( $sheet, $mapping, $fileName, $sheetCount,
  &$errors )` builds and validates rows; `_line_label` is "line N",
  prefixed with the sheet name when the workbook has several.
- `bc_address_parse_vcard( $file, &$errors )` for `.vcf` (2.1, 3.0,
  4.0; one or many cards; `bc_address_is_vcard()` sniffs
  `BEGIN:VCARD`). It unfolds continuation lines, walks lines to split
  cards (a regex across a file with embedded photos exceeds the PCRE
  backtrack limit and fails silently), and keeps only `FN`, `N`, `ORG`,
  `ADR`, `TEL`, `NOTE`. Per card: name from `FN`, else `N`; company from
  `ORG`; the `PREF` `ADR`, else `HOME`, else the first, split as pobox /
  extended / street / city / region / postal / country with extra street
  lines and the extended part joined into line 2; the `PREF` `TEL`, else
  `CELL`, else the first; `NOTE` as the comment. Quoted-printable and a
  legacy `CHARSET` (iconv) are decoded. A card with no `ADR` is left out
  and named in a warning.

## The mapping step

`bc_address_mapping_view( $table, $sheetIndex, $mapping )` is the data
for `templates/address_import_map_inc.tpl`: the file name, the sheets
(index, name, row count), the columns (index, heading, first-row sample),
and one entry per field in `bc_address_import_fields()` (label, help,
whether it is one of the two required, selected column, sample). The
include renders the sheet menu (when more than one), the table of
`map[<field>]` selects, and a script that updates the sample column; the
page supplies the form and the Apply / Cancel buttons and names the
submit button through `mapSubmitName` (value `sheet` re-guesses for the
chosen sheet).

`bc_address_mapping_from_post( $posted, $headers, $base )` reads the
menus, drops indexes outside the header row, and keeps from `$base`
(the guess) the columns the menus do not offer. `bc_address_mapping_ignored()`
lists headings no field uses for the warning.

Between upload and apply the table waits in `$_SESSION['address_import']`
(`bc_address_import_store( $table, $target, $extra )`, `_get( $target )`,
`_update( $changes )`, `_clear()`); `target` is `dropship` or
`address_book` so one page never picks up the other's upload. A large
sheet makes a large session; the drop ship page keeps it until the list
is cleared so "Check columns" can re-map, the address book clears it
after a successful import.

## Address book storage

- `bc_address_save_to_book( $row, &$errors, &$duplicate )` stores one
  row through `CommerceCustomer::storeAddress()` (name split on the
  first space, `country_id` from the ISO2, state as the zone code; the
  customer class resolves the zone id and validates minimum lengths). A
  book entry with the same row key counts as saved and sets
  `$duplicate`.
- `bc_address_save_rows_to_book( $rows )` saves every valid row and
  returns counts (`saved`, `duplicate`, `failed`, `invalid`) and a
  `problems` list (line label, name, reasons) for rows it could not
  save.
- `bc_address_book_rows( $ids )` turns address book entries into rows
  (`_book` set) for the drop ship list.

## Country and state lists for a form

`bc_address_country_options()` is `[ { iso2, name } ]` in name order for
a select. `bc_address_zones_by_country()` is `{ ISO2: [ [ code, name ],
… ] }` for a page script that swaps a state text input for a select when
the chosen country has zones. On this schema only a handful of countries
have zones, and `address_format_id` is 1 for nearly every country, so
neither drives field order or labels; a per-country layout table is the
planned next slice.

## The address book page

`/bookstore/address_book` uses the drop ship page's visual language:
the entries are the same bordered white cards as the drop ship address
book picker, in a wrapped grid (name and company, muted address and
phone, a `default` label, Edit and × at the foot of the card; the card
being edited has a blue outline), a tools row under the grid with
**Import a spreadsheet or vCard** that reveals the import row (file
input, "Let me check the columns first", help behind a toggle), and the
New / Update address form in a sticky `well` rail on the right
(`address_edit_inc.tpl`, unchanged: country select with the AJAX state
swap from `pages/address_book/states.php`). An empty book is one card
inviting the shopper to add or import. Edit,
Delete (with the confirm page), and Save flows are the original zen
ones; `$messageStack` class `addressbook` carries their messages across
the redirect and `{formfeedback hash=$feedback}` carries the import
results.

Import: `import_upload` with `address_file` reads a vCard straight into
the book (`bc_address_book_import()`), or a sheet through the same
mapping step as the drop ship page (`import_map=apply|sheet|cancel`,
`import_check_columns` to see the panel even when the guess matched).
Valid rows are stored at once; rows that failed validation or storage
are listed in a "Not saved" panel with the reason, and the feedback line
gives the counts (added, already there, not saved). The address
template link is the drop ship one (`pub/drop_ship_addresses.csv`).
